<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * VoiceSearchController
 *
 * Architecture (Claude API does NOT support audio input):
 *
 *   Flutter device STT → Kurdish/Arabic text transcript
 *        ↓
 *   POST /api/v1/search/voice-intent  { "transcript": "شەش دەفتەر لە ژیان" }
 *        ↓
 *   Claude Haiku → structured JSON intent
 *        ↓
 *   Flutter applies filters and searches
 *
 * The audio endpoint is kept but just does text extraction from the
 * transcript field — audio bytes are ignored since Claude can't process them.
 */
class VoiceSearchController extends Controller
{
    private const CLAUDE_MODEL  = 'claude-haiku-4-5-20251001'; // fast + cheap
    private const CLAUDE_TOKENS = 220;
    private const CACHE_TTL     = 300; // 5 min cache per transcript

    // ─────────────────────────────────────────────────────────────────────────
    // AUDIO ENDPOINT — kept for compatibility but audio is ignored
    // Flutter should send transcript in the 'transcript' field alongside audio
    // POST /api/v1/search/voice
    // ─────────────────────────────────────────────────────────────────────────
    public function transcribeAndParse(Request $request)
    {
        // Claude cannot process audio — use the transcript text field instead
        $transcript = trim($request->input('transcript', ''));

        if (empty($transcript) && $request->hasFile('audio')) {
            Log::info('🎤 VOICE: Audio received but Claude cannot process audio directly', [
                'size_kb' => round($request->file('audio')->getSize() / 1024, 1),
            ]);
            // Return empty intent — Flutter will fallback to text search
            return ApiResponse::success('No transcript provided', $this->emptyIntent('no_transcript'), 200);
        }

        if (empty($transcript)) {
            return ApiResponse::error('No transcript provided', null, 422);
        }

        return $this->parseAndRespond($transcript);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MAIN ENDPOINT — text transcript → Claude → intent
    // POST /api/v1/search/voice-intent
    // Body: { "transcript": "شەش دەفتەر لە ژیان" }
    // ─────────────────────────────────────────────────────────────────────────
    public function parseIntent(Request $request)
    {
        $transcript = trim($request->input('transcript', ''));

        if (empty($transcript)) {
            return ApiResponse::error('Empty transcript', null, 422);
        }
        if (mb_strlen($transcript) > 400) {
            $transcript = mb_substr($transcript, 0, 400);
        }

        return $this->parseAndRespond($transcript);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PARSE + RESPOND
    // ─────────────────────────────────────────────────────────────────────────
    private function parseAndRespond(string $transcript)
    {
        $cacheKey = 'voice_intent_v2_' . md5($transcript);

        $intent = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($transcript) {
            return $this->claudeParse($transcript);
        });

        Log::info('🎯 VOICE INTENT', [
            'transcript'   => $transcript,
            'source'       => $intent['source'] ?? '?',
            'area'         => $intent['area'] ?? null,
            'listing_type' => $intent['listing_type'] ?? null,
            'price_daftar' => $intent['min_price_daftar'] ?? null,
        ]);

        return ApiResponse::success('Intent parsed', $intent, 200);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CLAUDE TEXT PARSE
    // Loads real area names from DB so Claude knows all 568 areas
    // ─────────────────────────────────────────────────────────────────────────
    private function claudeParse(string $transcript): array
    {
        $apiKey = config('services.anthropic.api_key');
        if (!$apiKey) {
            Log::warning('VoiceSearch: No Anthropic key configured');
            return $this->emptyIntent('no_api_key');
        }

        // Load area names from DB (cached 1 hour)
        $areaList = $this->getAreaList();

        $system = <<<SYS
You are a real estate search intent parser for Dream Mulk, Kurdistan Region of Iraq.

The user spoke in Kurdish Sorani, Arabic, or English. The text below is what they said.

Platform areas (match user speech to these English names):
{$areaList}

Property types: apartment, villa, house, land, office, shop, building, duplex, studio, chalet, farm

Kurdish vocabulary:
کرێ/کرایە=rent | فرۆشتن/بفرۆشێت=sell
دەفتەر/دافتار/دافتر=daftar (1 daftar = \$10,000 USD)
ئۆتاق=bedroom | خانوو/خانو=house | شوقە=apartment | ڤیلا=villa
دوکان=shop | زەوی/ئەرازی=land | ئۆفیس=office
شەش=6 | پێنج=5 | چوار=4 | سێ=3 | دوو=2 | یەک=1 | حەوت=7 | هەشت=8 | نۆ=9 | دە=10

Arabic: کرئ/للإيجار=rent | للبيع=sell | غرفة=bedroom | شقة=apartment | فيلا=villa

Return ONLY valid JSON, no markdown, no explanation:
{
  "listing_type": "rent"|"sell"|null,
  "property_type": "apartment"|"villa"|"house"|"land"|"office"|"shop"|"building"|"duplex"|"studio"|"chalet"|"farm"|null,
  "area": "English area name from list above"|null,
  "city": "city name"|null,
  "bedrooms": number|null,
  "min_price_daftar": number|null,
  "max_price_daftar": number|null,
  "min_price_usd": number|null,
  "currency": "daftar"|"usd"|"iqd"|null
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
                Log::error('Claude parse error', [
                    'status' => $response->status(),
                    'body'   => substr($response->body(), 0, 300),
                ]);
                return $this->emptyIntent('claude_error');
            }

            $text    = preg_replace('/```json|```/', '', trim($response->json('content.0.text', '{}')));
            $decoded = json_decode($text, true);

            if (!is_array($decoded)) {
                Log::warning('Claude invalid JSON', ['raw' => $text]);
                return $this->emptyIntent('invalid_json');
            }

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
        } catch (\Throwable $e) {
            Log::error('Claude parse exception', ['msg' => $e->getMessage()]);
            return $this->emptyIntent('exception');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AREA LIST — from your areas table, cached 1 hour
    // ─────────────────────────────────────────────────────────────────────────
    private function getAreaList(): string
    {
        return Cache::remember('voice_area_list', 3600, function () {
            try {
                $areas = DB::table('areas')
                    ->where('is_active', 1)
                    ->select('area_name_en', 'area_name_ku', 'area_name_ar')
                    ->orderBy('area_name_en')
                    ->get();

                if ($areas->isEmpty()) {
                    return 'Ankawa, Mamostayan, Gulan, Iskan, Zanko, Zhyan, Ronaki, Badawa, Dream City, Empire';
                }

                // Format: "English (Kurdish, Arabic)" — helps Claude match
                return $areas->map(function ($a) {
                    $en = $a->area_name_en ?? '';
                    $ku = $a->area_name_ku ?? '';
                    $ar = $a->area_name_ar ?? '';
                    $alts = array_filter([$ku, $ar]);
                    return $en . ($alts ? ' (' . implode('/', $alts) . ')' : '');
                })->filter()->implode(', ');
            } catch (\Throwable $e) {
                Log::warning('getAreaList DB error', ['msg' => $e->getMessage()]);
                return 'Ankawa, Mamostayan, Gulan, Iskan, Zanko, Zhyan, Ronaki';
            }
        });
    }

    private function emptyIntent(string $source): array
    {
        return [
            'listing_type'     => null,
            'property_type'    => null,
            'area'             => null,
            'city'             => null,
            'bedrooms'         => null,
            'min_price_daftar' => null,
            'max_price_daftar' => null,
            'min_price_usd'    => null,
            'max_price_usd'    => null,
            'currency'         => null,
            'raw_transcript'   => '',
            'source'           => $source,
        ];
    }
}