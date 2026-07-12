<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * VoiceSearchController v5 — Self-hosted Whisper (auto-detect) + Claude Haiku
 *
 * IMPORTANT: Whisper has NO dedicated Kurdish language model.
 * We use -l auto and let Whisper transcribe using its best phonetic guess
 * (usually Arabic/Persian-like script or English approximation).
 * Claude is prompted to interpret whatever comes out and extract the
 * real Kurdish intent regardless of what script Whisper produced.
 *
 * Pipeline:
 *   1. Flutter records audio → POST /api/v1/search/voice (multipart)
 *   2. whisper.cpp (local, auto-detect language) transcribes
 *   3. Raw transcript (any script) → Claude Haiku → structured intent
 *   4. JSON intent returned to Flutter
 *
 * Cost: Whisper $0 (self-hosted) + Claude Haiku ~$0.003/call (5min cache)
 */
class VoiceSearchController extends Controller
{
    private const CLAUDE_MODEL    = 'claude-haiku-4-5-20251001';
    private const CLAUDE_TOKENS   = 280;
    private const CACHE_TTL       = 300;
    private const WHISPER_BIN     = '/usr/local/bin/dm-transcribe';
    private const WHISPER_TIMEOUT = 45; // increased — small model can be slow on CPU-only VPS

    // ─────────────────────────────────────────────────────────────────────────
    // MAIN — audio file → Whisper (local, auto-detect) → Claude → intent
    // POST /api/v1/search/voice
    // ─────────────────────────────────────────────────────────────────────────
    public function transcribeAndParse(Request $request)
    {
        if (!$request->hasFile('audio')) {
            return ApiResponse::error('No audio file provided', null, 422);
        }

        $audio = $request->file('audio');

        Log::info('🎤 VOICE: Audio received', [
            'size_kb' => round($audio->getSize() / 1024, 1),
            'mime'    => $audio->getMimeType(),
        ]);

        if ($audio->getSize() > 25 * 1024 * 1024) {
            return ApiResponse::error('Audio too large', null, 422);
        }
        if ($audio->getSize() < 500) {
            return ApiResponse::error('No audio detected', null, 422);
        }

        $transcript = $this->whisperLocal($audio);

        if (empty($transcript)) {
            Log::warning('Whisper returned empty transcript');
            return ApiResponse::error('Could not transcribe — speak clearly and try again', null, 422);
        }

        Log::info('🎙️ Whisper transcript', ['text' => $transcript]);

        return $this->parseAndRespond($transcript, 'whisper-auto');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TEXT FALLBACK — typed text → Claude → intent
    // POST /api/v1/search/voice-intent
    // ─────────────────────────────────────────────────────────────────────────
    public function parseIntent(Request $request)
    {
        $transcript = trim($request->input('transcript', ''));
        $sttLocale  = $request->input('stt_locale', 'text-input');

        if (empty($transcript)) {
            return ApiResponse::error('Empty transcript', null, 422);
        }
        if (mb_strlen($transcript) > 500) {
            $transcript = mb_substr($transcript, 0, 500);
        }

        return $this->parseAndRespond($transcript, $sttLocale);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // WHISPER LOCAL — shell out to whisper.cpp on this server
    // ─────────────────────────────────────────────────────────────────────────
    // Max simultaneous transcriptions server-wide. Beyond this, requests wait
    // briefly rather than all fighting for every CPU core at once.
    private const MAX_CONCURRENT_WHISPER = 2;
    private const CONCURRENCY_WAIT_MS    = 300;
    private const CONCURRENCY_MAX_WAIT_S = 20;

    private function whisperLocal($audioFile): string
    {
        if (!file_exists(self::WHISPER_BIN)) {
            Log::error('Whisper not installed — run setup_whisper.sh on the server');
            return '';
        }

        $lockAcquired = $this->acquireWhisperSlot();
        if (!$lockAcquired) {
            Log::warning('Whisper: server busy, all slots taken after max wait');
            return '';
        }

        try {
            $tmpName = 'dm_voice_' . Str::random(12) . '.' .
                ($audioFile->getClientOriginalExtension() ?: 'mp4');
            $audioFile->move('/tmp', $tmpName);
            $tmpPath = '/tmp/' . $tmpName;

            $cmd = sprintf(
                'timeout %d %s %s 2>>/tmp/dm-transcribe-errors.log',
                self::WHISPER_TIMEOUT,
                self::WHISPER_BIN,
                escapeshellarg($tmpPath)
            );

            $start      = microtime(true);
            $transcript = trim(shell_exec($cmd) ?? '');
            $elapsed    = round(microtime(true) - $start, 1);

            @unlink($tmpPath);

            Log::info('Whisper done', ['seconds' => $elapsed, 'chars' => mb_strlen($transcript)]);

            if (str_contains($transcript, 'BLANK_AUDIO') || mb_strlen($transcript) < 2) {
                return '';
            }

            return $transcript;
        } catch (\Throwable $e) {
            Log::error('Whisper exception', ['msg' => $e->getMessage()]);
            return '';
        } finally {
            $this->releaseWhisperSlot();
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CONCURRENCY LIMITER — simple file-based semaphore
    // Counts active whisper processes in /tmp/dm_whisper_slots/
    // Prevents 10 simultaneous voice searches from each grabbing all CPU cores
    // ─────────────────────────────────────────────────────────────────────────
    private ?string $slotFile = null;

    private function acquireWhisperSlot(): bool
    {
        $slotDir = '/tmp/dm_whisper_slots';
        if (!is_dir($slotDir)) {
            @mkdir($slotDir, 0777, true);
        }

        $waited = 0;
        while ($waited < self::CONCURRENCY_MAX_WAIT_S * 1000) {
            // Clean stale slot files older than 60s (crashed processes)
            foreach (glob($slotDir . '/*.lock') as $f) {
                if (time() - filemtime($f) > 60) @unlink($f);
            }

            $active = count(glob($slotDir . '/*.lock'));
            if ($active < self::MAX_CONCURRENT_WHISPER) {
                $this->slotFile = $slotDir . '/' . Str::random(8) . '.lock';
                touch($this->slotFile);
                return true;
            }

            usleep(self::CONCURRENCY_WAIT_MS * 1000);
            $waited += self::CONCURRENCY_WAIT_MS;
        }

        return false; // gave up after max wait
    }

    private function releaseWhisperSlot(): void
    {
        if ($this->slotFile && file_exists($this->slotFile)) {
            @unlink($this->slotFile);
        }
        $this->slotFile = null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PARSE + RESPOND
    // ─────────────────────────────────────────────────────────────────────────
    private function parseAndRespond(string $transcript, string $sttLocale)
    {
        $cacheKey = 'voice_v5_' . md5($transcript);

        $intent = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($transcript, $sttLocale) {
            return $this->claudeParse($transcript, $sttLocale);
        });

        Log::info('🎯 VOICE INTENT', [
            'transcript' => $transcript,
            'clean'      => $intent['clean_transcript'] ?? null,
            'area'       => $intent['area'] ?? null,
            'type'       => $intent['property_type'] ?? null,
            'price'      => $intent['min_price_daftar'] ?? null,
            'source'     => $intent['source'] ?? '?',
        ]);

        return ApiResponse::success('Intent parsed', $intent, 200);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CLAUDE PARSE — Haiku, handles ANY script Whisper produces
    // ─────────────────────────────────────────────────────────────────────────
    private function claudeParse(string $transcript, string $sttLocale): array
    {
        $apiKey = config('services.anthropic.api_key');
        if (!$apiKey) {
            Log::warning('VoiceSearch: No Anthropic key');
            return $this->emptyIntent('no_api_key');
        }

        $areaList = $this->getAreaList();

        // Whisper has no Kurdish model — it auto-detects and often produces
        // Arabic, Persian, Turkish, or garbled English depending on what it
        // thinks it's hearing. Claude must reverse-engineer the real Kurdish
        // meaning from whatever phonetic approximation comes out.
        $localeNote = match (true) {
            $sttLocale === 'whisper-auto' =>
            'CRITICAL CONTEXT: This transcript came from OpenAI Whisper running in auto-detect mode. '
                . 'Whisper has NO Kurdish language model, so it guessed the closest language it knows '
                . '(often Arabic, Persian/Farsi, Turkish, or English) and transcribed PHONETICALLY. '
                . 'The actual speaker was almost certainly speaking Kurdish Sorani about a real estate search. '
                . 'You must reverse-engineer the real Kurdish meaning from the phonetic sounds, even if the '
                . 'script/language looks wrong. For example, Whisper might output Persian script that SOUNDS '
                . 'like Kurdish daftar/price/area words. Think phonetically, not literally.',
            $sttLocale === 'text-input' =>
            'User typed this text manually — could be Kurdish, Arabic, or English, trust it more literally.',
            default => "STT locale: {$sttLocale}",
        };

        $system = <<<SYS
You are a real estate search parser for Dream Mulk, Kurdistan Region of Iraq.

{$localeNote}

Platform areas (match phonetically similar words to these):
{$areaList}

Cities: Erbil (Hewlêr), Sulaymaniyah (Slemani), Duhok, Soran, Rawanduz, Shaqlawa, Zakho, Halabja, Ranya, Koya, Makhmur, Kirkuk, Kalar, Kifri

Property types: apartment, villa, house, land, office, shop, building, duplex, studio, chalet, farm

Prices:
- daftar/defter/daftr (پیش-Kurdish unit, may sound like "daftar", "defter", "daftr" in any script) = 10,000 USD
  "شەش دەفتەر" or phonetic equivalent → min=6, max=6. Range "5 to 8" → min=5, max=8
- USD: "$150k" / "150 هزار دولار" → 150000
- IQD: "150 million" / "١٥٠ ملیون" → 150000000

Size: "100 meter/متر/m2" → min_area_m2=100

Features: corner/رووکن/گۆشە→has_corner | garden/باخچه→has_garden | parking/پارکینگ→has_parking | furnished/مبله→is_furnished | new/جدید→is_new | view/منظره→has_view

Kurdish core vocabulary (recognize these even through phonetic distortion):
کرێ/kirê/kire = rent
فرۆشتن/froshtn/frotin = sell
دەفتەر/daftar/defter = price unit
ئۆتاق/otaq/otax = bedroom/room
خانوو/xanû/khanu = house
شوقە/shuqa = apartment
ڤیلا/vîla = villa
زەوی/zewî = land
دوکان/dukan = shop
Numbers: yek/yak=1 dû/do=2 sê/se=3 çwar/char=4 pênc/panj=5 şeş/shash=6 heft/haft=7 heşt/hasht=8 neh/noh=9 deh/dah=10

Return ONLY valid JSON, no prose:
{
  "clean_transcript": "your best reconstruction of what they actually said, in proper Kurdish Sorani script",
  "listing_type": "rent"|"sell"|null,
  "property_type": string|null,
  "area": "English area name from list"|null,
  "city": string|null,
  "bedrooms": number|null,
  "bathrooms": number|null,
  "min_price_daftar": number|null,
  "max_price_daftar": number|null,
  "min_price_usd": number|null,
  "max_price_usd": number|null,
  "min_price_iqd": number|null,
  "currency": "daftar"|"usd"|"iqd"|null,
  "min_area_m2": number|null,
  "max_area_m2": number|null,
  "floor": number|null,
  "has_corner": boolean|null,
  "has_garden": boolean|null,
  "has_parking": boolean|null,
  "is_furnished": boolean|null,
  "is_new": boolean|null,
  "has_view": boolean|null,
  "keywords": ["remaining descriptive words"]
}
SYS;

        try {
            $response = Http::withHeaders([
                'x-api-key'         => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(10)->post('https://api.anthropic.com/v1/messages', [
                'model'      => self::CLAUDE_MODEL,
                'max_tokens' => self::CLAUDE_TOKENS,
                'system'     => $system,
                'messages'   => [['role' => 'user', 'content' => $transcript]],
            ]);

            if (!$response->successful()) {
                Log::error('Claude error', ['status' => $response->status(), 'body' => substr($response->body(), 0, 300)]);
                return $this->emptyIntent('claude_error');
            }

            $text    = preg_replace('/```json|```/', '', trim($response->json('content.0.text', '{}')));
            $decoded = json_decode($text, true);

            if (!is_array($decoded)) {
                Log::warning('Claude invalid JSON', ['raw' => $text]);
                return $this->emptyIntent('invalid_json');
            }

            return [
                'clean_transcript' => $decoded['clean_transcript']  ?? $transcript,
                'listing_type'     => $decoded['listing_type']      ?? null,
                'property_type'    => $decoded['property_type']     ?? null,
                'area'             => $decoded['area']              ?? null,
                'city'             => $decoded['city']              ?? null,
                'bedrooms'         => isset($decoded['bedrooms'])   ? (int)$decoded['bedrooms']   : null,
                'bathrooms'        => isset($decoded['bathrooms'])  ? (int)$decoded['bathrooms']  : null,
                'min_price_daftar' => isset($decoded['min_price_daftar']) ? (int)$decoded['min_price_daftar'] : null,
                'max_price_daftar' => isset($decoded['max_price_daftar']) ? (int)$decoded['max_price_daftar'] : null,
                'min_price_usd'    => isset($decoded['min_price_usd'])    ? (int)$decoded['min_price_usd']    : null,
                'max_price_usd'    => isset($decoded['max_price_usd'])    ? (int)$decoded['max_price_usd']    : null,
                'min_price_iqd'    => isset($decoded['min_price_iqd'])    ? (int)$decoded['min_price_iqd']    : null,
                'currency'         => $decoded['currency']          ?? null,
                'min_area_m2'      => isset($decoded['min_area_m2']) ? (float)$decoded['min_area_m2'] : null,
                'max_area_m2'      => isset($decoded['max_area_m2']) ? (float)$decoded['max_area_m2'] : null,
                'floor'            => isset($decoded['floor'])       ? (int)$decoded['floor']       : null,
                'has_corner'       => $decoded['has_corner']        ?? null,
                'has_garden'       => $decoded['has_garden']        ?? null,
                'has_parking'      => $decoded['has_parking']       ?? null,
                'is_furnished'     => $decoded['is_furnished']      ?? null,
                'is_new'           => $decoded['is_new']            ?? null,
                'has_view'         => $decoded['has_view']          ?? null,
                'keywords'         => (array)($decoded['keywords']  ?? []),
                'raw_transcript'   => $transcript,
                'source'           => 'whisper+claude',
            ];
        } catch (\Throwable $e) {
            Log::error('Claude exception', ['msg' => $e->getMessage()]);
            return $this->emptyIntent('exception');
        }
    }

    private function getAreaList(): string
    {
        return Cache::remember('voice_area_list_v2', 3600, function () {
            try {
                $areas = \DB::table('areas')
                    ->where('is_active', 1)
                    ->select('area_name_en', 'area_name_ku', 'area_name_ar')
                    ->orderBy('area_name_en')
                    ->get();

                if ($areas->isEmpty()) return 'Ankawa, Mamostayan, Gulan, Iskan, Zanko, Zhyan';

                return $areas->map(function ($a) {
                    $en = $a->area_name_en ?? '';
                    $alts = array_filter([$a->area_name_ku ?? '', $a->area_name_ar ?? '']);
                    return $en . ($alts ? ' (' . implode('/', $alts) . ')' : '');
                })->filter()->implode(', ');
            } catch (\Throwable $e) {
                return 'Ankawa, Mamostayan, Gulan, Iskan, Zanko, Zhyan, Ronaki';
            }
        });
    }

    private function emptyIntent(string $source): array
    {
        return [
            'clean_transcript' => null,
            'listing_type' => null,
            'property_type' => null,
            'area' => null,
            'city' => null,
            'bedrooms' => null,
            'bathrooms' => null,
            'min_price_daftar' => null,
            'max_price_daftar' => null,
            'min_price_usd' => null,
            'max_price_usd' => null,
            'min_price_iqd' => null,
            'currency' => null,
            'min_area_m2' => null,
            'max_area_m2' => null,
            'floor' => null,
            'has_corner' => null,
            'has_garden' => null,
            'has_parking' => null,
            'is_furnished' => null,
            'is_new' => null,
            'has_view' => null,
            'keywords' => [],
            'raw_transcript' => '',
            'source' => $source,
        ];
    }
}