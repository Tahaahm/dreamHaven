<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * VoiceSearchController v4 — Self-hosted Whisper + Claude Haiku
 *
 * Pipeline:
 *   1. Flutter records audio → POST /api/v1/search/voice (multipart)
 *   2. whisper.cpp on THIS server transcribes Kurdish (FREE, local, no API)
 *   3. Kurdish transcript → Claude Haiku → rich structured intent
 *   4. JSON intent returned to Flutter
 *
 * Costs:
 *   Whisper: $0 (runs on your VPS)
 *   Claude Haiku: ~$0.003/call, cached 5 min
 *
 * Setup required on server (one time):
 *   bash setup_whisper.sh   (installs whisper.cpp + Kurdish model)
 */
class VoiceSearchController extends Controller
{
    private const CLAUDE_MODEL  = 'claude-haiku-4-5-20251001';
    private const CLAUDE_TOKENS = 250;
    private const CACHE_TTL     = 300;
    private const WHISPER_BIN   = '/usr/local/bin/dm-transcribe';
    private const WHISPER_TIMEOUT = 25; // seconds

    // ─────────────────────────────────────────────────────────────────────────
    // MAIN — audio file → Whisper (local) → Claude → intent
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

        // Step 1: Local Whisper transcription (FREE)
        $transcript = $this->whisperLocal($audio);

        if (empty($transcript)) {
            Log::warning('Whisper returned empty transcript');
            return ApiResponse::error('Could not transcribe — speak clearly and try again', null, 422);
        }

        Log::info('🎙️ Whisper transcript', ['text' => $transcript]);

        // Step 2: Claude intent extraction
        return $this->parseAndRespond($transcript, 'whisper-local');
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
    private function whisperLocal($audioFile): string
    {
        if (!file_exists(self::WHISPER_BIN)) {
            Log::error('Whisper not installed — run setup_whisper.sh on the server');
            return '';
        }

        try {
            // Move to a temp location whisper can read
            $tmpPath = '/tmp/dm_voice_' . Str::random(12) . '.' .
                ($audioFile->getClientOriginalExtension() ?: 'mp4');
            $audioFile->move('/tmp', basename($tmpPath));

            // Run whisper with timeout
            $cmd = sprintf(
                'timeout %d %s %s 2>/dev/null',
                self::WHISPER_TIMEOUT,
                self::WHISPER_BIN,
                escapeshellarg($tmpPath)
            );

            $start      = microtime(true);
            $transcript = trim(shell_exec($cmd) ?? '');
            $elapsed    = round(microtime(true) - $start, 1);

            @unlink($tmpPath);

            Log::info('Whisper done', ['seconds' => $elapsed, 'chars' => mb_strlen($transcript)]);

            // Whisper outputs "[BLANK_AUDIO]" or similar for silence
            if (str_contains($transcript, 'BLANK_AUDIO') || mb_strlen($transcript) < 2) {
                return '';
            }

            return $transcript;
        } catch (\Throwable $e) {
            Log::error('Whisper exception', ['msg' => $e->getMessage()]);
            return '';
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PARSE + RESPOND
    // ─────────────────────────────────────────────────────────────────────────
    private function parseAndRespond(string $transcript, string $sttLocale)
    {
        $cacheKey = 'voice_v4_' . md5($transcript);

        $intent = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($transcript, $sttLocale) {
            return $this->claudeParse($transcript, $sttLocale);
        });

        Log::info('🎯 VOICE INTENT', [
            'transcript' => $transcript,
            'area'       => $intent['area'] ?? null,
            'type'       => $intent['property_type'] ?? null,
            'price'      => $intent['min_price_daftar'] ?? null,
            'source'     => $intent['source'] ?? '?',
        ]);

        return ApiResponse::success('Intent parsed', $intent, 200);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CLAUDE PARSE — Haiku, rich intent extraction, all filters
    // ─────────────────────────────────────────────────────────────────────────
    private function claudeParse(string $transcript, string $sttLocale): array
    {
        $apiKey = config('services.anthropic.api_key');
        if (!$apiKey) {
            Log::warning('VoiceSearch: No Anthropic key');
            return $this->emptyIntent('no_api_key');
        }

        $areaList = $this->getAreaList();

        $localeNote = match (true) {
            str_starts_with($sttLocale, 'whisper') =>
            'Transcript from Whisper (Kurdish language mode) — text is in Kurdish Sorani/Arabic script.',
            str_starts_with(strtolower($sttLocale), 'en') =>
            'CAUTION: Transcript from English STT — Kurdish speech may be mangled into random English words. Try to guess the real Kurdish meaning phonetically. E.g. "step 30" might be "sê sed" (300), "La Jolla" might be "la zhyan" (in Zhyan).',
            $sttLocale === 'text-input' =>
            'User typed this text manually — could be Kurdish, Arabic, or English.',
            default => "STT locale: {$sttLocale}",
        };

        $system = <<<SYS
You are a real estate search parser for Dream Mulk, Kurdistan Region of Iraq.

{$localeNote}

Platform areas (match to these English names):
{$areaList}

Cities: Erbil (Hewlêr/هەولێر), Sulaymaniyah (Slemani/سلێمانی), Duhok (دهۆک), Soran, Rawanduz, Shaqlawa, Zakho, Halabja, Ranya, Koya, Makhmur, Kirkuk, Kalar, Kifri

Property types: apartment, villa, house, land, office, shop, building, duplex, studio, chalet, farm

Prices:
- daftar (دەفتەر) = 10,000 USD. "شەش دەفتەر"=6 daftar. "6 daftar"→min=6,max=6. Range "5 بۆ 8 دەفتەر"→min=5,max=8
- USD: "\$150k"→150000. IQD: "١٥٠ ملیۆن"→150000000

Sizes: "100 متر/meter/m2"→min_area_m2=100

Features: رووکن/corner→has_corner | باخچە/garden→has_garden | پارکینگ/parking→has_parking | مۆبیلیا/furnished→is_furnished | نوێ/new→is_new | دیمەن/view→has_view

Kurdish core vocab:
کرێ=rent فرۆشتن=sell ئۆتاق=bedroom حەمام=bathroom خانوو=house شوقە=apartment ڤیلا=villa زەوی=land دوکان=shop نهۆم=floor
Numbers: یەک=1 دوو=2 سێ=3 چوار=4 پێنج=5 شەش=6 حەوت=7 هەشت=8 نۆ=9 دە=10 سەد=100

Return ONLY valid JSON:
{
  "clean_transcript": "corrected Kurdish Sorani or clear English of what they meant",
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
  "keywords": ["remaining descriptive words for full-text search on title/description"]
}
SYS;

        try {
            $response = Http::withHeaders([
                'x-api-key'         => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(8)->post('https://api.anthropic.com/v1/messages', [
                'model'      => self::CLAUDE_MODEL,
                'max_tokens' => self::CLAUDE_TOKENS,
                'system'     => $system,
                'messages'   => [['role' => 'user', 'content' => $transcript]],
            ]);

            if (!$response->successful()) {
                Log::error('Claude error', ['status' => $response->status()]);
                return $this->emptyIntent('claude_error');
            }

            $text    = preg_replace('/```json|```/', '', trim($response->json('content.0.text', '{}')));
            $decoded = json_decode($text, true);

            if (!is_array($decoded)) {
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