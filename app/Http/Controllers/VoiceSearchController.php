<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * VoiceSearchController
 *
 * Cost: claude-haiku = ~$0.003 per call (not $0.30)
 * Cached: same query never calls Claude twice (5 min cache)
 *
 * POST /api/v1/search/voice-intent
 * Body: { "transcript": "...", "stt_locale": "en-US" }
 */
class VoiceSearchController extends Controller
{
    // haiku is 40x cheaper than opus — use it here
    private const CLAUDE_MODEL  = 'claude-haiku-4-5-20251001';
    private const CLAUDE_TOKENS = 250;
    private const CACHE_TTL     = 300;

    public function parseIntent(Request $request)
    {
        $transcript = trim($request->input('transcript', ''));
        $sttLocale  = $request->input('stt_locale', 'unknown');

        if (empty($transcript)) {
            return ApiResponse::error('Empty transcript', null, 422);
        }
        if (mb_strlen($transcript) > 500) {
            $transcript = mb_substr($transcript, 0, 500);
        }

        $cacheKey = 'voice_v3_' . md5($transcript);

        $intent = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($transcript, $sttLocale) {
            return $this->claudeParse($transcript, $sttLocale);
        });

        Log::info('🎯 VOICE', [
            'transcript' => $transcript,
            'locale'     => $sttLocale,
            'area'       => $intent['area'] ?? null,
            'price'      => $intent['min_price_daftar'] ?? null,
            'source'     => $intent['source'] ?? '?',
            'cached'     => isset($intent['_cached']),
        ]);

        return ApiResponse::success('Intent parsed', $intent, 200);
    }

    private function claudeParse(string $transcript, string $sttLocale): array
    {
        $apiKey = config('services.anthropic.api_key');
        if (!$apiKey) {
            Log::warning('VoiceSearch: No Anthropic key');
            return $this->emptyIntent('no_api_key');
        }

        // Load areas from DB (1 hour cache)
        $areaList = $this->getAreaList();

        // Tell Claude if the STT was English (meaning Kurdish words
        // may have been transliterated/translated to English)
        $localeNote = str_starts_with(strtolower($sttLocale), 'en')
            ? 'IMPORTANT: STT used English locale so Kurdish words may appear in English transliteration. Examples: "daftar"=دەفتەر, "zhyan"=ژیان, "ankawa"=ئەنکاوە, "house"=خانوو, "rent"=کرێ, "sell/sale"=فرۆشتن, "bedroom/room"=ئۆتاق, "corner"=رووکن/گۆشە, "meter/m2"=متر, "garden"=باخچە'
            : 'STT locale: ' . $sttLocale;

        $system = <<<SYS
You are a real estate search parser for Dream Mulk, Kurdistan Region of Iraq.

{$localeNote}

Platform areas (match spoken words to these):
{$areaList}

Cities: Erbil (Hewlêr), Sulaymaniyah (Slemani), Duhok, Soran, Rawanduz, Shaqlawa, Zakho, Halabja, Ranya, Koya, Makhmur, Kirkuk, Kalar, Kifri

Property types: apartment, villa, house, land, office, shop, building, duplex, studio, chalet, farm

Price units:
- daftar/defter = 10,000 USD (Kurdish price unit)
- If user says "8 daftar" → min_price_daftar=8, max_price_daftar=8
- If range "5 to 8 daftar" → min=5, max=8
- USD prices: "$150,000" or "150k" → min_price_usd=150000
- IQD prices: "150 million" → min_price_iqd=150000000

Size: "100 meter/m2/sqm" → min_area_m2=100

Features to extract:
- corner=has_corner (corner plot/unit)
- garden/yard=has_garden
- parking=has_parking
- furnished=is_furnished
- new/newly built=is_new
- floor number: "3rd floor" → floor=3
- view: "city view" → has_view

Kurdish/English word mapping (STT may produce either):
- کرێ/kirê/rent/for rent → listing_type=rent
- فرۆشتن/froshtn/sell/sale/for sale → listing_type=sell
- دەفتەر/daftar/defter → price unit
- ئۆتاق/otaq/room/bedroom/bed → bedrooms
- خانوو/xanuu/house → property_type=house
- شوقە/shuqa/apartment/flat → property_type=apartment
- زەوی/zawî/land/plot → property_type=land
- رووکن/rukon/corner → has_corner=true

Return ONLY valid JSON:
{
  "clean_transcript": "what they said, corrected to proper Kurdish or clear English",
  "listing_type": "rent"|"sell"|null,
  "property_type": "apartment"|"villa"|"house"|"land"|"office"|"shop"|"building"|"duplex"|"studio"|"chalet"|"farm"|null,
  "area": "English area name from list"|null,
  "city": "city name"|null,
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
  "has_corner": true|false|null,
  "has_garden": true|false|null,
  "has_parking": true|false|null,
  "is_furnished": true|false|null,
  "is_new": true|false|null,
  "has_view": true|false|null,
  "keywords": ["any remaining descriptive words for full-text search"]
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
                Log::error('Claude error', ['status' => $response->status(), 'body' => substr($response->body(), 0, 200)]);
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
                'source'           => 'claude_haiku',
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
                    $ku = $a->area_name_ku ?? '';
                    $ar = $a->area_name_ar ?? '';
                    $alts = array_filter([$ku, $ar]);
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