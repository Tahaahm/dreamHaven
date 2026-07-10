<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * VoiceSearchController
 *
 * Receives a raw speech transcript (EN / AR / KU Sorani / KU Kurmanji)
 * and returns a structured property-search intent in < 200 tokens.
 *
 * Route:  POST /api/v1/search/voice-intent
 * Body:   { "transcript": "شەش دەفتەر رووکن لە ژیان" }
 * Returns same shape as SmartSearchEngine filters so Flutter can pass
 * it straight into the existing search endpoint.
 */
class VoiceSearchController extends Controller
{
    // ── Claude config ──────────────────────────────────────────────────────
    private const MODEL      = 'claude-haiku-4-5-20251001'; // fastest + cheapest
    private const MAX_TOKENS = 180;  // we only need a tiny JSON object
    private const CACHE_TTL  = 300;  // 5 min – same phrase = same intent

    // ── Kurdish number words → digits ──────────────────────────────────────
    private const KU_NUMBERS = [
        'یەک' => 1,
        'دوو' => 2,
        'سێ' => 3,
        'چوار' => 4,
        'پێنج' => 5,
        'شەش' => 6,
        'حەوت' => 7,
        'هەشت' => 8,
        'نۆ' => 9,
        'دە' => 10,
        'یازدە' => 11,
        'دوازدە' => 12,
        // Arabic numerals people mix in
        'واحد' => 1,
        'اثنين' => 2,
        'ثلاثة' => 3,
        'أربعة' => 4,
        'خمسة' => 5,
        'ستة' => 6,
    ];

    // ── Daftar aliases (all dialect variants) ─────────────────────────────
    private const DAFTAR_WORDS = [
        'دەفتەر',
        'دفتر',
        'دافتار',
        'دەفتار',
        'دافتر',
        'daftar',
        'defter',
        'daftr',
    ];

    // ── Listing type keywords ──────────────────────────────────────────────
    private const RENT_KW  = ['کرێ', 'کرایە', 'ئایجار', 'إيجار', 'rent', 'kirê', 'kiraye'];
    private const SELL_KW  = ['فرۆشتن', 'بفرۆشێت', 'للبيع', 'فروش', 'sell', 'sale', 'froshtn'];

    // ── Known areas (Erbil focus – add more as needed) ────────────────────
    private const AREA_MAP = [
        // Kurdish → canonical EN
        'ژیان'       => 'Zhyan',
        'مامۆستایان' => 'Mamostayan',
        'ئەنکاوە'    => 'Ankawa',
        'گوڵان'      => 'Gulan',
        'ئەسکان'     => 'Iskan',
        'براياتی'    => 'Brayati',
        'زانکۆ'      => 'Zanko',
        'ڕۆناکی'     => 'Ronaki',
        'باداوە'     => 'Badawa',
        'شاری خەون'  => 'Dream City',
        'ئیمپایر'    => 'Empire',
        'ناز سیتی'   => 'Naz City',
        'تایراوا'    => 'Tairawa',
        'فەرمانبەران' => 'Farmanbaran',
        'روانگە'     => 'Rwanga',
        'بنەسڵاوە'   => 'Bna Slawa',
        'خانزاد'     => 'Xanzad',
        'کوردستان'   => 'Kurdistan',
        'شاری ناز'   => 'Naz City',
        'گوندی ئیتاڵی' => 'Italian Village',
        'تانجارۆ'    => 'Tanjaro',
        'سەرچنار'    => 'Sarchnar',
        'سارای'      => 'Saray',
        'سەلیم'      => 'Salim',
        'ڕەپەڕین'    => 'Raparin',
        'ئازادی'     => 'Azadi',
        'باخچەی سامی' => 'Sami Abdulrahman Park Area',
        'کاسنەزان'   => 'Kasnazan',
        // Arabic variants
        'زيان'       => 'Zhyan',
        'ماموستايان' => 'Mamostayan',
        'عنكاوا'     => 'Ankawa',
        'اسكان'      => 'Iskan',
        'روانكة'     => 'Rwanga',
        // English fallback (already correct)
        'zhyan'      => 'Zhyan',
        'ankawa'     => 'Ankawa',
        'mamostayan' => 'Mamostayan',
        'gulan'      => 'Gulan',
        'iskan'      => 'Iskan',
        'zanko'      => 'Zanko',
    ];

    // ── Property type keywords ─────────────────────────────────────────────
    private const TYPE_MAP = [
        'apartment' => ['شوقە', 'شووقە', 'شقة', 'شقق', 'apartment'],
        'villa'     => ['ڤیلا', 'فيلا', 'villa'],
        'house'     => ['خانوو', 'خانووبار', 'منزل', 'بيت', 'دار', 'house'],
        'land'      => ['زەوی', 'ئەرازی', 'أرض', 'land'],
        'office'    => ['ئۆفیس', 'بنکە', 'مكتب', 'office'],
        'shop'      => ['دوکان', 'محل', 'shop'],
    ];

    // ─────────────────────────────────────────────────────────────────────
    // ENDPOINT
    // ─────────────────────────────────────────────────────────────────────
    public function parseIntent(Request $request)
    {
        $transcript = trim($request->input('transcript', ''));

        if (empty($transcript)) {
            return ApiResponse::error('Empty transcript', null, 422);
        }

        if (mb_strlen($transcript) > 300) {
            $transcript = mb_substr($transcript, 0, 300);
        }

        $cacheKey = 'voice_intent_' . md5($transcript);

        $intent = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($transcript) {
            // 1. Try fast local parse first (zero cost)
            $local = $this->localParse($transcript);

            // 2. If local parse got enough signal, skip Claude
            if ($this->isConfident($local)) {
                $local['source'] = 'local';
                return $local;
            }

            // 3. Call Claude for ambiguous / complex phrases
            $claude = $this->claudeParse($transcript);
            if ($claude) {
                // Merge: local wins on fields it found (more reliable for numerics)
                return array_merge($claude, array_filter($local, fn($v) => $v !== null));
            }

            // 4. Fallback: return whatever local found
            $local['source'] = 'local_fallback';
            return $local;
        });

        Log::info('🎙️ VOICE INTENT', [
            'transcript' => $transcript,
            'source'     => $intent['source'] ?? 'unknown',
            'intent'     => $intent,
        ]);

        return ApiResponse::success('Intent parsed', $intent, 200);
    }

    // ─────────────────────────────────────────────────────────────────────
    // LOCAL PARSER — regex + keyword, zero cost, ~1ms
    // ─────────────────────────────────────────────────────────────────────
    private function localParse(string $text): array
    {
        $lower = mb_strtolower($text);

        $intent = [
            'listing_type'   => null,
            'property_type'  => null,
            'area'           => null,
            'city'           => null,
            'bedrooms'       => null,
            'min_price_daftar' => null,
            'max_price_daftar' => null,
            'min_price_usd'  => null,
            'max_price_usd'  => null,
            'currency'       => null,
            'raw_transcript' => $text,
            'source'         => 'local',
        ];

        // ── Listing type ──
        foreach (self::RENT_KW as $kw) {
            if (mb_strpos($lower, mb_strtolower($kw)) !== false) {
                $intent['listing_type'] = 'rent';
                break;
            }
        }
        if (!$intent['listing_type']) {
            foreach (self::SELL_KW as $kw) {
                if (mb_strpos($lower, mb_strtolower($kw)) !== false) {
                    $intent['listing_type'] = 'sell';
                    break;
                }
            }
        }

        // ── Area ──
        foreach (self::AREA_MAP as $ku => $en) {
            if (mb_strpos($lower, mb_strtolower($ku)) !== false) {
                $intent['area'] = $en;
                break;
            }
        }

        // ── Property type ──
        foreach (self::TYPE_MAP as $type => $keywords) {
            foreach ($keywords as $kw) {
                if (mb_strpos($lower, mb_strtolower($kw)) !== false) {
                    $intent['property_type'] = $type;
                    break 2;
                }
            }
        }

        // ── Bedrooms — digit or word ──
        // "٣ ئۆتاق" / "3 room" / "سێ ئۆتاق" / "3 bed"
        if (preg_match('/([٠-٩0-9]+)\s*(ئۆتاق|ئۆتاقی|room|rooms?|bed|beds?|bedroom|غرفة)/u', $text, $m)) {
            $intent['bedrooms'] = (int) $this->arabicToInt($m[1]);
        } else {
            foreach (self::KU_NUMBERS as $word => $digit) {
                if (preg_match('/' . preg_quote($word, '/') . '\s*(ئۆتاق|room|bed)/u', $text)) {
                    $intent['bedrooms'] = $digit;
                    break;
                }
            }
        }

        // ── Daftar price ──
        // Matches: "شەش دەفتەر" / "6 daftar" / "٦ دەفتار"
        $daftarPattern = implode('|', array_map(fn($d) => preg_quote($d, '/'), self::DAFTAR_WORDS));
        $numPart = '([٠-٩0-9]+|' . implode('|', array_map(fn($w) => preg_quote($w, '/'), array_keys(self::KU_NUMBERS))) . ')';

        if (preg_match('/' . $numPart . '\s*(?:' . $daftarPattern . ')/u', $text, $m)) {
            $val = $this->resolveNumber($m[1]);
            if ($val !== null) {
                $intent['min_price_daftar'] = $val;
                $intent['max_price_daftar'] = $val;
                $intent['currency'] = 'daftar';
            }
        }

        // ── USD price — "150 هەزار دۆلار" / "$150k" / "150,000" ──
        if (preg_match('/\$?\s*([٠-٩0-9][٠-٩0-9,]*)\s*k?\b/u', $lower, $m)) {
            $num = (float) str_replace(',', '', $this->arabicToInt($m[1]));
            if (str_contains($lower, 'k') || str_contains($lower, 'هەزار') || str_contains($lower, 'ألف')) {
                $num *= 1000;
            } elseif (str_contains($lower, 'm') || str_contains($lower, 'ملیۆن') || str_contains($lower, 'مليون')) {
                $num *= 1_000_000;
            }
            if ($num >= 10_000) { // ignore tiny numbers like "3 bedroom"
                $intent['min_price_usd'] = $num;
                $intent['currency'] = 'usd';
            }
        }

        return $intent;
    }

    // ─────────────────────────────────────────────────────────────────────
    // CONFIDENCE CHECK — skip Claude if local found key fields
    // ─────────────────────────────────────────────────────────────────────
    private function isConfident(array $intent): bool
    {
        $found = 0;
        if ($intent['listing_type'])  $found++;
        if ($intent['area'])          $found++;
        if ($intent['property_type']) $found++;
        if ($intent['bedrooms'])      $found++;
        if ($intent['min_price_daftar'] || $intent['min_price_usd']) $found++;
        return $found >= 2; // 2+ signals = confident enough
    }

    // ─────────────────────────────────────────────────────────────────────
    // CLAUDE PARSER — called only when local parse isn't confident
    // Ultra-compact prompt → ~120 input tokens, ~80 output tokens
    // ─────────────────────────────────────────────────────────────────────
    private function claudeParse(string $transcript): ?array
    {
        $apiKey = config('services.anthropic.api_key');
        if (!$apiKey) return null;

        // Minimal system prompt — every word costs money
        $system = <<<'SYS'
Kurdistan real estate voice search parser. Extract JSON only. No prose.
Fields: listing_type("rent"|"sell"|null), property_type("apartment"|"villa"|"house"|"land"|"office"|"shop"|null), area(string|null), city(string|null), bedrooms(int|null), min_price_daftar(int|null), max_price_daftar(int|null), min_price_usd(int|null), max_price_usd(int|null), currency("daftar"|"usd"|"iqd"|null).
Daftar=10,000 USD. Kurdish: کرێ=rent فرۆشتن=sell دەفتەر=daftar ئۆتاق=bedroom. Dialect variants: رووکن≈رووکەن ژیان≈zhyan. Return {} if unsure.
SYS;

        try {
            $response = Http::withHeaders([
                'x-api-key'         => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(6)->post('https://api.anthropic.com/v1/messages', [
                'model'      => self::MODEL,
                'max_tokens' => self::MAX_TOKENS,
                'system'     => $system,
                'messages'   => [
                    ['role' => 'user', 'content' => $transcript],
                ],
            ]);

            if (!$response->successful()) {
                Log::warning('VoiceSearch: Claude API error', ['status' => $response->status()]);
                return null;
            }

            $text = $response->json('content.0.text', '{}');
            $text = preg_replace('/```json|```/', '', trim($text));
            $decoded = json_decode($text, true);

            if (!is_array($decoded)) return null;

            return [
                'listing_type'      => $decoded['listing_type'] ?? null,
                'property_type'     => $decoded['property_type'] ?? null,
                'area'              => $decoded['area'] ?? null,
                'city'              => $decoded['city'] ?? null,
                'bedrooms'          => isset($decoded['bedrooms']) ? (int)$decoded['bedrooms'] : null,
                'min_price_daftar'  => isset($decoded['min_price_daftar']) ? (int)$decoded['min_price_daftar'] : null,
                'max_price_daftar'  => isset($decoded['max_price_daftar']) ? (int)$decoded['max_price_daftar'] : null,
                'min_price_usd'     => isset($decoded['min_price_usd']) ? (int)$decoded['min_price_usd'] : null,
                'max_price_usd'     => isset($decoded['max_price_usd']) ? (int)$decoded['max_price_usd'] : null,
                'currency'          => $decoded['currency'] ?? null,
                'raw_transcript'    => $transcript,
                'source'            => 'claude',
            ];
        } catch (\Throwable $e) {
            Log::error('VoiceSearch: exception', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────

    /** Resolve a token that could be a Kurdish number word or a digit string */
    private function resolveNumber(string $token): ?int
    {
        // Arabic-Indic → ASCII
        $ascii = $this->arabicToInt($token);
        if (is_numeric($ascii)) return (int)$ascii;

        // Kurdish number word
        $clean = trim($token);
        foreach (self::KU_NUMBERS as $word => $digit) {
            if (mb_strtolower($clean) === mb_strtolower($word)) return $digit;
        }
        return null;
    }

    /** Convert Arabic-Indic numerals (٠١٢٣٤٥٦٧٨٩) to ASCII */
    private function arabicToInt(string $s): string
    {
        return strtr($s, [
            '٠' => '0',
            '١' => '1',
            '٢' => '2',
            '٣' => '3',
            '٤' => '4',
            '٥' => '5',
            '٦' => '6',
            '٧' => '7',
            '٨' => '8',
            '٩' => '9',
        ]);
    }
}