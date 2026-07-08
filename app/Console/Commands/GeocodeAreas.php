 <?php

    // app/Console/Commands/GeocodeAreas.php
    //
    // Re-geocodes the `areas` table coordinates from scratch using a real
    // geocoding API, scoped to each area's own city so names like "Azadi"
    // (which exist in many cities) can only resolve inside the correct branch.
    //
    // WHY: the current lat/lng are unreliable — 220 are blank, ~79 are fake
    // 0.001-step sequences, and many "real-looking" ones (e.g. Zhyan, Empire)
    // are eyeballed near city center and physically wrong.
    //
    // SAFETY:
    //   * DRY-RUN by default. Nothing is written unless you pass --commit.
    //   * Every result is bounds-checked against the city box + max radius;
    //     anything outside is treated as UNRESOLVED and NEVER guessed.
    //   * Writes happen inside a DB transaction.
    //   * --only-blank / --only-fake let you limit scope.
    //
    // USAGE (on the server, in /var/www/Dream-haven):
    //   php artisan areas:geocode                         # dry run, OSM
    //   php artisan areas:geocode --provider=google --key=XXXX
    //   php artisan areas:geocode --provider=google --key=XXXX --commit
    //   php artisan areas:geocode --branch=1 --commit     # one city only
    //
    // After committing, spot-check in DBeaver.

    namespace App\Console\Commands;

    use App\Models\Area;
    use Illuminate\Console\Command;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Http;

    class GeocodeAreas extends Command
    {
        protected $signature = 'areas:geocode
        {--provider=nominatim : nominatim (free) or google}
        {--key= : Google Maps API key (required for --provider=google)}
        {--branch= : Only geocode a single branch_id}
        {--only-blank : Only rows with empty latitude/longitude}
        {--only-fake : Only rows detected as fake 0.001-step sequences}
        {--commit : Actually write to the database (otherwise dry run)}
        {--sleep=1100 : ms to wait between requests (Nominatim needs >=1000)}';

        protected $description = 'Re-geocode area coordinates using a real geocoding API, scoped per city.';

        /**
         * branch_id => [city_name, center_lat, center_lng, half_box_deg, max_radius_km]
         * Centers are authoritative; box + radius reject bad matches.
         */
        private array $cities = [
            1  => ['Erbil',        36.19110, 44.00920, 0.28, 30],
            2  => ['Soran',        36.65340, 44.54560, 0.18, 18],
            3  => ['Koya',         36.08530, 44.62890, 0.15, 15],
            4  => ['Shaqlawa',     36.40570, 44.32320, 0.15, 15],
            5  => ['Rawanduz',     36.61420, 44.52470, 0.15, 15],
            6  => ['Makhmur',      35.76830, 43.58330, 0.18, 20],
            7  => ['Sulaymaniyah', 35.55560, 45.43290, 0.28, 30],
            8  => ['Halabja',      35.17720, 45.98560, 0.18, 18],
            9  => ['Ranya',        36.26330, 44.88940, 0.20, 22],
            10 => ['Qaladze',      36.13330, 45.06670, 0.15, 15],
            11 => ['Sharazur',     35.61000, 45.95500, 0.18, 18],
            12 => ['Chamchamal',   35.51670, 44.83330, 0.18, 18],
            13 => ['Duhok',        36.86770, 42.99130, 0.22, 25],
            14 => ['Zakho',        37.14480, 42.68270, 0.18, 18],
            15 => ['Amedi',        37.08940, 43.49030, 0.22, 25],
            16 => ['Akre',         36.73330, 43.88330, 0.18, 18],
            17 => ['Semel',        36.93330, 42.95000, 0.15, 15],
            18 => ['Bardarash',    37.00000, 43.11670, 0.18, 18],
            19 => ['Kirkuk',       35.46810, 44.39220, 0.22, 25],
            20 => ['Kalar',        34.62670, 45.31970, 0.18, 18],
            21 => ['Kifri',        34.69140, 44.96420, 0.18, 18],
        ];

        public function handle(): int
        {
            $provider = $this->option('provider');
            $key      = $this->option('key');
            $commit   = (bool) $this->option('commit');
            $sleepMs  = (int) $this->option('sleep');

            if ($provider === 'google' && !$key) {
                $this->error('--provider=google requires --key=YOUR_API_KEY');
                return self::FAILURE;
            }

            // Build the query
            $query = Area::query()->with('branch');
            if ($b = $this->option('branch')) {
                $query->where('branch_id', (int) $b);
            }

            $all = $query->orderBy('id')->get();

            // Optional narrowing by classification
            if ($this->option('only-blank')) {
                $all = $all->filter(fn($a) => empty($a->latitude) || empty($a->longitude))->values();
            } elseif ($this->option('only-fake')) {
                $fakeIds = $this->detectFakeSequenceIds($query->get());
                $all = $all->filter(fn($a) => in_array($a->id, $fakeIds, true))->values();
            }

            $total = $all->count();
            if ($total === 0) {
                $this->warn('No matching areas found.');
                return self::SUCCESS;
            }

            $this->info(($commit ? 'COMMIT' : 'DRY RUN') . " — geocoding {$total} areas via {$provider}");
            $this->newLine();

            $resolved   = [];
            $unresolved = [];
            $bar = $this->output->createProgressBar($total);
            $bar->start();

            foreach ($all as $area) {
                $bid = (int) $area->branch_id;
                if (!isset($this->cities[$bid])) {
                    $unresolved[] = [$area, 'unknown branch'];
                    $bar->advance();
                    continue;
                }
                [$city, $clat, $clng, $half, $maxr] = $this->cities[$bid];
                $box = [$clat - $half, $clat + $half, $clng - $half, $clng + $half];

                $hit = $provider === 'google'
                    ? $this->geocodeGoogle($area, $city, $box, $key)
                    : $this->geocodeNominatim($area, $city, $box);

                if ($hit) {
                    [$la, $lo] = $hit;
                    $d = $this->haversineKm($la, $lo, $clat, $clng);
                    $inBox = $la >= $box[0] && $la <= $box[1] && $lo >= $box[2] && $lo <= $box[3];
                    if ($inBox && $d <= $maxr) {
                        $resolved[] = [$area, $la, $lo, $city, $d];
                        $bar->advance();
                        if ($provider === 'nominatim') usleep($sleepMs * 1000);
                        continue;
                    }
                }
                $unresolved[] = [$area, 'no confident match'];
                if ($provider === 'nominatim') usleep($sleepMs * 1000);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);

            // Report
            $this->line("Resolved:   " . count($resolved));
            $this->line("Unresolved: " . count($unresolved));
            $this->newLine();

            // Show a sample so you can eyeball before committing
            $this->line('Sample of resolved changes:');
            foreach (array_slice($resolved, 0, 12) as [$area, $la, $lo, $city, $d]) {
                $old = ($area->latitude ?: '—') . ',' . ($area->longitude ?: '—');
                $this->line(sprintf(
                    '  #%-4d %-26s %-13s  %s -> %.6f,%.6f  (%.1f km)',
                    $area->id,
                    mb_substr($area->area_name_en, 0, 26),
                    "($city)",
                    $old,
                    $la,
                    $lo,
                    $d
                ));
            }
            $this->newLine();

            if ($unresolved) {
                $this->warn('Unresolved (left untouched — review manually):');
                foreach (array_slice($unresolved, 0, 20) as [$area, $why]) {
                    $this->line("  #{$area->id} {$area->area_name_en} (branch {$area->branch_id}) [$why]");
                }
                if (count($unresolved) > 20) {
                    $this->line('  ... and ' . (count($unresolved) - 20) . ' more.');
                }
                $this->newLine();
            }

            if (!$commit) {
                $this->info('DRY RUN complete. Nothing written. Re-run with --commit to apply.');
                return self::SUCCESS;
            }

            // Write inside a transaction
            DB::transaction(function () use ($resolved) {
                foreach ($resolved as [$area, $la, $lo]) {
                    $area->latitude  = round($la, 8);
                    $area->longitude = round($lo, 8);
                    $area->save();
                }
            });

            $this->info('Committed ' . count($resolved) . ' updates to the areas table.');
            if ($unresolved) {
                $this->warn(count($unresolved) . ' rows left unresolved — send me the list to finish manually.');
            }
            return self::SUCCESS;
        }

        private function geocodeNominatim(Area $area, string $city, array $box): ?array
        {
            [$laMin, $laMax, $loMin, $loMax] = $box;
            $viewbox = "{$loMin},{$laMax},{$loMax},{$laMin}";
            $candidates = array_filter([
                "{$area->area_name_en}, {$city}, Kurdistan, Iraq",
                "{$area->area_name_en}, {$city}, Iraq",
                "{$area->area_name_ar}, {$city}, Iraq",
            ], fn($q) => trim(explode(',', $q)[0]) !== '');

            foreach ($candidates as $q) {
                try {
                    $resp = Http::withHeaders([
                        'User-Agent' => 'DreamMulk-area-geocoder/1.0 (taha@dreammulk.com)',
                    ])->timeout(25)->get('https://nominatim.openstreetmap.org/search', [
                        'q' => $q,
                        'format' => 'json',
                        'limit' => 3,
                        'viewbox' => $viewbox,
                        'bounded' => 1,
                        'countrycodes' => 'iq',
                    ]);
                    $data = $resp->json();
                    if (is_array($data) && count($data)) {
                        return [(float) $data[0]['lat'], (float) $data[0]['lon']];
                    }
                } catch (\Throwable $e) {
                    // try next candidate
                }
            }
            return null;
        }

        private function geocodeGoogle(Area $area, string $city, array $box, string $key): ?array
        {
            [$laMin, $laMax, $loMin, $loMax] = $box;
            $candidates = array_filter([
                "{$area->area_name_en}, {$city}, Kurdistan Region, Iraq",
                "{$area->area_name_ar}, {$city}, Iraq",
            ], fn($q) => trim(explode(',', $q)[0]) !== '');

            foreach ($candidates as $q) {
                try {
                    $resp = Http::timeout(25)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                        'address' => $q,
                        'bounds'  => "{$laMin},{$loMin}|{$laMax},{$loMax}",
                        'region'  => 'iq',
                        'key'     => $key,
                    ]);
                    $data = $resp->json();
                    if (($data['status'] ?? '') === 'OK' && !empty($data['results'])) {
                        $loc = $data['results'][0]['geometry']['location'];
                        return [(float) $loc['lat'], (float) $loc['lng']];
                    }
                } catch (\Throwable $e) {
                    // try next candidate
                }
            }
            return null;
        }

        /** Detect fake 0.001-step arithmetic sequences per branch. */
        private function detectFakeSequenceIds($areas): array
        {
            $byBranch = [];
            foreach ($areas as $a) {
                $byBranch[$a->branch_id][] = $a;
            }
            $fake = [];
            foreach ($byBranch as $rows) {
                usort($rows, fn($x, $y) => $x->id <=> $y->id);
                $prev = null;
                $prevId = null;
                $run = [];
                foreach ($rows as $a) {
                    if (empty($a->latitude)) {
                        $prev = null;
                        if (count($run) >= 4) $fake = array_merge($fake, $run);
                        $run = [];
                        continue;
                    }
                    $la = (float) $a->latitude;
                    if ($prev !== null) {
                        $step = abs($la - $prev);
                        if ($step >= 0.0008 && $step <= 0.0012) {
                            if (!$run) $run = [$prevId];
                            $run[] = $a->id;
                        } else {
                            if (count($run) >= 4) $fake = array_merge($fake, $run);
                            $run = [];
                        }
                    }
                    $prev = $la;
                    $prevId = $a->id;
                }
                if (count($run) >= 4) $fake = array_merge($fake, $run);
            }
            return array_values(array_unique($fake));
        }

        private function haversineKm(float $la1, float $lo1, float $la2, float $lo2): float
        {
            $R = 6371.0;
            $dLa = deg2rad($la2 - $la1);
            $dLo = deg2rad($lo2 - $lo1);
            $a = sin($dLa / 2) ** 2 + cos(deg2rad($la1)) * cos(deg2rad($la2)) * sin($dLo / 2) ** 2;
            return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
        }
    }
