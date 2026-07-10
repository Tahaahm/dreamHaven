<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * VoiceSearchController — Claude only, generic area/type data from DB
 */
class VoiceSearchController extends Controller
{
    private const CLAUDE_MODEL  = 'claude-opus-4-6';
    private const CLAUDE_TOKENS = 300;
    private const CACHE_TTL     = 300;

    // ─────────────────────────────────────────────────────────────────────────
    // MAIN ENDPOINT — audio → Claude → intent
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

        $result = $this->claudeTranscribeAndParse($audio);

        if (!$result) {
            return ApiResponse::error('Could not process audio', null, 422);
        }

        Log::info('🎯 VOICE INTENT', [
            'transcript' => $result['raw_transcript'] ?? '',
            'source'     => $result['source'] ?? '?',
            'area'       => $result['area'] ?? null,
        ]);

        return ApiResponse::success('Intent parsed', $result, 200);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TEXT FALLBACK — transcript → Claude → intent
    // POST /api/v1/search/voice-intent
    // ─────────────────────────────────────────────────────────────────────────
    public function parseIntent(Request $request)
    {
        $transcript = trim($request->input('transcript', ''));
        if (empty($transcript)) {
            return ApiResponse::error('Empty transcript', null, 422);
        }
        if (mb_strlen($transcript) > 300) {
            $transcript = mb_substr($transcript, 0, 300);
        }

        $intent = $this->claudeTextParse($transcript);

        if (!$intent) {
            return ApiResponse::error('Could not parse intent', null, 422);
        }

        return ApiResponse::success('Intent parsed', $intent, 200);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CLAUDE AUDIO — base64 audio → transcript + intent in one call
    // ─────────────────────────────────────────────────────────────────────────
    private function claudeTranscribeAndParse($audioFile): ?array
    {
        $apiKey = config('services.anthropic.api_key');
        if (!$apiKey) {
            Log::warning('VoiceSearch: No Anthropic key configured');
            return null;
        }

        try {
            $base64Audio = base64_encode(file_get_contents($audioFile->getRealPath()));

            // Load area names from DB (cached 1 hour)
            $areaContext = $this->buildAreaContext();

            $system = <<<SYS
You are a voice search assistant for Dream Mulk, a real estate platform in Kurdistan Region of Iraq.

The user is speaking in Kurdish Sorani, Arabic, or English.

Your job:
1. TRANSCRIBE the audio accurately in the original language
2. EXTRACT the search intent as JSON

Known areas in the platform:
{$areaContext}

Property types: apartment, villa, house, land, office, shop, building, duplex, studio, chalet, farm

Kurdish vocabulary:
- کرێ/کرایە = rent | فرۆشتن/بفرۆشێت = sell
- دەفتەر/دافتار = daftar (price unit, 1 daftar = \$10,000 USD)
- ئۆتاق/ئۆتاقی = bedroom | خانوو/خانو = house | شوقە = apartment
- ڤیلا = villa | دوکان = shop | زەوی/ئەرازی = land | ئۆفیس = office
- شەش=6 پێنج=5 چوار=4 سێ=3 دوو=2 یەک=1 حەوت=7 هەشت=8 نۆ=9 دە=10

Return ONLY valid JSON, no prose, no markdown:
{
  "transcript": "exact words spoken",
  "listing_type": "rent"|"sell"|null,
  "property_type": "apartment"|"villa"|"house"|"land"|"office"|"shop"|"building"|"duplex"|"studio"|"chalet"|"farm"|null,
  "area": "area name in English from the platform"|null,
  "city": "city name"|null,
  "bedrooms": number|null,
  "min_price_daftar": number|null,
  "max_price_daftar": number|null,
  "min_price_usd": number|null,
  "currency": "daftar"|"usd"|"iqd"|null
}
SYS;

            $response = Http::withHeaders([
                'x-api-key'         => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(20)->post('https://api.anthropic.com/v1/messages', [
                'model'      => self::CLAUDE_MODEL,
                'max_tokens' => self::CLAUDE_TOKENS,
                'system'     => $system,
                'messages'   => [[
                    'role'    => 'user',
                    'content' => [
                        [
                            'type'   => 'document',
                            'source' => [
                                'type'       => 'base64',
                                'media_type' => 'audio/mp4',
                                'data'       => $base64Audio,
                            ],
                        ],
                        [
                            'type' => 'text',
                            'text' => 'Transcribe and extract real estate search intent from this audio.',
                        ],
                    ],
                ]],
            ]);

            if (!$response->successful()) {
                Log::error('Claude audio error', [
                    'status' => $response->status(),
                    'body'   => substr($response->body(), 0, 500),
                ]);
                return $this->emptyIntent('claude_error');
            }

            $text    = $response->json('content.0.text', '{}');
            $text    = preg_replace('/```json|```/', '', trim($text));
            $decoded = json_decode($text, true);

            if (!is_array($decoded)) {
                Log::warning('Claude invalid JSON', ['raw' => $text]);
                return $this->emptyIntent('invalid_json');
            }

            Log::info('✅ Claude voice result', ['transcript' => $decoded['transcript'] ?? '']);

            return $this->normalizeIntent($decoded, $decoded['transcript'] ?? '');
        } catch (\Throwable $e) {
            Log::error('Claude audio exception', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CLAUDE TEXT — text transcript → intent
    // ─────────────────────────────────────────────────────────────────────────
    private function claudeTextParse(string $transcript): ?array
    {
        $apiKey = config('services.anthropic.api_key');
        if (!$apiKey) return null;

        $areaContext = $this->buildAreaContext();

        $system = <<<SYS
Kurdistan real estate search parser. Return JSON only. No prose.

Known areas: {$areaContext}

Fields: listing_type("rent"|"sell"|null), property_type("apartment"|"villa"|"house"|"land"|"office"|"shop"|null), area(English name from list|null), city(string|null), bedrooms(int|null), min_price_daftar(int|null), min_price_usd(int|null), currency("daftar"|"usd"|"iqd"|null).

Kurdish: کرێ=rent فرۆشتن=sell دەفتەر=daftar ئۆتاق=bedroom خانوو=house شوقە=apartment.
SYS;

        try {
            $response = Http::withHeaders([
                'x-api-key'         => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(8)->post('https://api.anthropic.com/v1/messages', [
                'model'      => 'claude-haiku-4-5-20251001',
                'max_tokens' => 200,
                'system'     => $system,
                'messages'   => [['role' => 'user', 'content' => $transcript]],
            ]);

            if (!$response->successful()) return null;

            $text    = preg_replace('/```json|```/', '', trim($response->json('content.0.text', '{}')));
            $decoded = json_decode($text, true);

            if (!is_array($decoded)) return null;

            return $this->normalizeIntent($decoded, $transcript);
        } catch (\Throwable $e) {
            Log::error('Claude text parse exception', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BUILD AREA CONTEXT — load from DB, cache 1 hour
    // Gives Claude the real area names from your 568 areas table
    // ─────────────────────────────────────────────────────────────────────────
    private function buildAreaContext(): string
    {
        return Cache::remember('voice_area_context', 3600, function () {
            try {
                // Load all active areas with their trilingual names
                $areas = \DB::table('areas')
                    ->where('is_active', 1)
                    ->select('area_name_en', 'area_name_ar', 'area_name_ku')
                    ->get();

                if ($areas->isEmpty()) {
                    return 'Erbil areas: Ankawa, Mamostayan, Gulan, Iskan, Zanko, Zhyan, Ronaki, Badawa, Dream City, Empire, Naz City';
                }

                // Build compact context: "EN (AR, KU)" per area, grouped
                $lines = $areas->map(function ($a) {
                    $parts = array_filter([
                        $a->area_name_en,
                        $a->area_name_ku ?: null,
                        $a->area_name_ar ?: null,
                    ]);
                    return implode('/', array_unique($parts));
                })->filter()->values()->toArray();

                // Keep it compact — Claude doesn't need all 568 on every call
                // Just give EN names in a comma list (Claude knows the Kurdish)
                $enNames = $areas->pluck('area_name_en')->filter()->unique()->values()->toArray();

                return implode(', ', $enNames);
            } catch (\Throwable $e) {
                Log::warning('buildAreaContext failed', ['msg' => $e->getMessage()]);
                return 'Ankawa, Mamostayan, Gulan, Iskan, Zanko, Zhyan, Ronaki';
            }
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // NORMALIZE INTENT — clean and type-cast Claude's response
    // ─────────────────────────────────────────────────────────────────────────
    private function normalizeIntent(array $decoded, string $transcript): array
    {
        return [
            'listing_type'     => $decoded['listing_type']     ?? null,
            'property_type'    => $decoded['property_type']    ?? null,
            'area'             => $decoded['area']             ?? null,
            'city'             => $decoded['city']             ?? null,
            'bedrooms'         => isset($decoded['bedrooms'])         ? (int)$decoded['bedrooms']         : null,
            'min_price_daftar' => isset($decoded['min_price_daftar']) ? (int)$decoded['min_price_daftar'] : null,
            'max_price_daftar' => isset($decoded['max_price_daftar']) ? (int)$decoded['max_price_daftar'] : null,
            'min_price_usd'    => isset($decoded['min_price_usd'])    ? (int)$decoded['min_price_usd']    : null,
            'max_price_usd'    => isset($decoded['max_price_usd'])    ? (int)$decoded['max_price_usd']    : null,
            'currency'         => $decoded['currency']         ?? null,
            'raw_transcript'   => $transcript,
            'source'           => 'claude',
        ];
    }

    private function emptyIntent(string $source): array
    {
        return [
            'listing_type' => null,
            'property_type' => null,
            'area' => null,
            'city' => null,
            'bedrooms' => null,
            'min_price_daftar' => null,
            'max_price_daftar' => null,
            'min_price_usd' => null,
            'max_price_usd' => null,
            'currency' => null,
            'raw_transcript' => '',
            'source' => $source,
        ];
    }
}