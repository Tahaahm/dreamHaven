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
 * Pipeline:
 *   1. Flutter records audio → sends as multipart file to POST /api/v1/search/voice
 *   2. Laravel sends audio to OpenAI Whisper (language: Kurdish)
 *   3. Whisper returns accurate Kurdish transcript
 *   4. Laravel sends transcript to Claude Haiku for intent extraction
 *   5. Returns structured JSON intent to Flutter
 *
 * Routes needed:
 *   POST /api/v1/search/voice         ← main endpoint (audio file)
 *   POST /api/v1/search/voice-intent  ← text-only fallback (transcript string)
 */
class VoiceSearchController extends Controller
{
    // ── Config ────────────────────────────────────────────────────────────────
    private const WHISPER_MODEL   = 'whisper-1';
    private const CLAUDE_MODEL    = 'claude-haiku-4-5-20251001';
    private const CLAUDE_TOKENS   = 180;
    private const CACHE_TTL       = 300; // 5 min

    // ── Kurdish number words → digits ─────────────────────────────────────────
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
        'واحد' => 1,
        'اثنين' => 2,
        'ثلاثة' => 3,
        'أربعة' => 4,
        'خمسة' => 5,
        'ستة' => 6,
    ];

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

    private const RENT_KW  = ['کرێ', 'کرایە', 'ئایجار', 'إيجار', 'rent', 'kirê'];
    private const SELL_KW  = ['فرۆشتن', 'بفرۆشێت', 'للبيع', 'فروش', 'sell', 'sale'];

    private const AREA_MAP = [
        'ژیان' => 'Zhyan',
        'مامۆستایان' => 'Mamostayan',
        'ئەنکاوە' => 'Ankawa',
        'گوڵان' => 'Gulan',
        'ئەسکان' => 'Iskan',
        'براياتی' => 'Brayati',
        'زانکۆ' => 'Zanko',
        'ڕۆناکی' => 'Ronaki',
        'باداوە' => 'Badawa',
        'شاری خەون' => 'Dream City',
        'ئیمپایر' => 'Empire',
        'ناز سیتی' => 'Naz City',
        'تایراوا' => 'Tairawa',
        'فەرمانبەران' => 'Farmanbaran',
        'روانگە' => 'Rwanga',
        'بنەسڵاوە' => 'Bna Slawa',
        'خانزاد' => 'Xanzad',
        'کوردستان' => 'Kurdistan',
        'گوندی ئیتاڵی' => 'Italian Village',
        'تانجارۆ' => 'Tanjaro',
        'سەرچنار' => 'Sarchnar',
        'سارای' => 'Saray',
        'سەلیم' => 'Salim',
        'ڕەپەڕین' => 'Raparin',
        'ئازادی' => 'Azadi',
        'کاسنەزان' => 'Kasnazan',
        'شار' => 'Shar',
        'بازاڕ' => 'Bazar',
        'رووکن' => 'Rukn',
        // Arabic variants
        'زيان' => 'Zhyan',
        'ماموستايان' => 'Mamostayan',
        'عنكاوا' => 'Ankawa',
        'اسكان' => 'Iskan',
        // English
        'zhyan' => 'Zhyan',
        'ankawa' => 'Ankawa',
        'mamostayan' => 'Mamostayan',
        'gulan' => 'Gulan',
        'iskan' => 'Iskan',
        'zanko' => 'Zanko',
        'rukn' => 'Rukn',
    ];

    private const TYPE_MAP = [
        'apartment' => ['شوقە', 'شووقە', 'شقة', 'شقق', 'apartment', 'ئاپارتمێنت'],
        'villa'     => ['ڤیلا', 'فيلا', 'villa'],
        'house'     => ['خانوو', 'خانووبار', 'منزل', 'بيت', 'دار', 'house', 'خانو'],
        'land'      => ['زەوی', 'ئەرازی', 'أرض', 'land'],
        'office'    => ['ئۆفیس', 'بنکە', 'مكتب', 'office'],
        'shop'      => ['دوکان', 'محل', 'shop'],
    ];

    // ─────────────────────────────────────────────────────────────────────────
    // MAIN ENDPOINT — receives audio file from Flutter
    // POST /api/v1/search/voice
    // Content-Type: multipart/form-data
    // Field: audio (file) — WAV, M4A, MP4, WEBM, OGG
    // ─────────────────────────────────────────────────────────────────────────
    public function transcribeAndParse(Request $request)
    {
        if (!$request->hasFile('audio')) {
            return ApiResponse::error('No audio file provided', null, 422);
        }

        $audio = $request->file('audio');

        // Validate file
        $maxSizeMb = 10;
        if ($audio->getSize() > $maxSizeMb * 1024 * 1024) {
            return ApiResponse::error('Audio too large (max 10MB)', null, 422);
        }

        $allowedMimes = [
            'audio/wav',
            'audio/x-wav',
            'audio/mp4',
            'audio/m4a',
            'audio/mpeg',
            'audio/webm',
            'audio/ogg',
            'video/mp4',
            'audio/mp3',
            'application/octet-stream'
        ];

        Log::info('🎤 VOICE: Audio received', [
            'size_kb' => round($audio->getSize() / 1024, 1),
            'mime'    => $audio->getMimeType(),
            'ext'     => $audio->getClientOriginalExtension(),
        ]);

        // Step 1: Whisper transcription
        $transcript = $this->whisperTranscribe($audio);

        if (empty($transcript)) {
            return ApiResponse::error('Could not transcribe audio', null, 422);
        }

        Log::info('🎤 VOICE: Whisper transcript', ['text' => $transcript]);

        // Step 2: Parse intent
        return $this->parseTranscript($transcript);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TEXT-ONLY FALLBACK — for when Flutter already has a transcript
    // POST /api/v1/search/voice-intent
    // Body: { "transcript": "شەش دەفتەر لە ژیان" }
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
        return $this->parseTranscript($transcript);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // WHISPER — sends audio to OpenAI, gets Kurdish transcript back
    // ─────────────────────────────────────────────────────────────────────────
    private function whisperTranscribe($audioFile): string
    {
        $apiKey = config('services.openai.api_key');
        if (!$apiKey) {
            Log::warning('VoiceSearch: No OpenAI key configured');
            return '';
        }

        try {
            // We explicitly tell Whisper the language is Kurdish
            // This prevents it from auto-detecting as Arabic/Persian
            // and ensures Kurdish script output
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
            ])->timeout(30)->attach(
                'file',
                file_get_contents($audioFile->getRealPath()),
                'audio.' . ($audioFile->getClientOriginalExtension() ?: 'wav')
            )->post('https://api.openai.com/v1/audio/transcriptions', [
                'model'    => self::WHISPER_MODEL,
                'language' => 'ku',         // Kurdish — critical!
                'response_format' => 'json',
                'prompt'   => 'خانوو، شوقە، ڤیلا، دەفتەر، کرێ، فرۆشتن، ئەنکاوە، ژیان، مامۆستایان',
                // ↑ Kurdish real estate prompt helps Whisper stay in Kurdish mode
            ]);

            if (!$response->successful()) {
                Log::error('Whisper API error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return '';
            }

            $text = trim($response->json('text', ''));
            Log::info('Whisper returned', ['text' => $text, 'lang' => 'ku']);
            return $text;
        } catch (\Throwable $e) {
            Log::error('Whisper exception', ['msg' => $e->getMessage()]);
            return '';
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PARSE — local parser first, Claude for complex/ambiguous phrases
    // ─────────────────────────────────────────────────────────────────────────
    private function parseTranscript(string $transcript)
    {
        $cacheKey = 'voice_intent_' . md5($transcript);

        $intent = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($transcript) {
            // 1. Fast local parse (free, ~1ms)
            $local = $this->localParse($transcript);

            // 2. If confident, skip Claude
            if ($this->isConfident($local)) {
                $local['source'] = 'local';
                return $local;
            }

            // 3. Claude for complex/ambiguous phrases
            $claude = $this->claudeParse($transcript);
            if ($claude) {
                return array_merge($claude, array_filter($local, fn($v) => $v !== null && $v !== [] && $v !== ''));
            }

            $local['source'] = 'local_fallback';
            return $local;
        });

        Log::info('🎯 VOICE INTENT', [
            'transcript' => $transcript,
            'source'     => $intent['source'] ?? '?',
            'area'       => $intent['area'] ?? null,
            'price'      => $intent['min_price_daftar'] ?? null,
        ]);

        return ApiResponse::success('Intent parsed', $intent, 200);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // LOCAL PARSER — regex + keyword matching, zero cost
    // ─────────────────────────────────────────────────────────────────────────
    private function localParse(string $text): array
    {
        $lower = mb_strtolower($text);

        $intent = [
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
            'raw_transcript'   => $text,
            'source'           => 'local',
        ];

        // Listing type
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

        // Area
        foreach (self::AREA_MAP as $ku => $en) {
            if (mb_strpos($lower, mb_strtolower($ku)) !== false) {
                $intent['area'] = $en;
                break;
            }
        }

        // Property type
        foreach (self::TYPE_MAP as $type => $keywords) {
            foreach ($keywords as $kw) {
                if (mb_strpos($lower, mb_strtolower($kw)) !== false) {
                    $intent['property_type'] = $type;
                    break 2;
                }
            }
        }

        // Bedrooms — "٣ ئۆتاق" / "3 room" / "سێ ئۆتاق"
        if (preg_match('/([٠-٩0-9]+)\s*(ئۆتاق|ئۆتاقی|room|rooms?|bed|beds?|bedroom|غرفة)/u', $text, $m)) {
            $intent['bedrooms'] = (int) $this->arabicToAscii($m[1]);
        } else {
            foreach (self::KU_NUMBERS as $word => $digit) {
                if (preg_match('/' . preg_quote($word, '/') . '\s*(ئۆتاق|room|bed)/u', $text)) {
                    $intent['bedrooms'] = $digit;
                    break;
                }
            }
        }

        // Daftar price — "شەش دەفتەر" / "6 daftar" / "٦ دەفتار"
        $daftarPat = implode('|', array_map('preg_quote', self::DAFTAR_WORDS));
        $numPat    = '([٠-٩0-9]+|' . implode('|', array_map('preg_quote', array_keys(self::KU_NUMBERS))) . ')';

        if (preg_match('/' . $numPat . '\s*(?:' . $daftarPat . ')/u', $text, $m)) {
            $val = $this->resolveNumber($m[1]);
            if ($val !== null) {
                $intent['min_price_daftar'] = $val;
                $intent['max_price_daftar'] = $val;
                $intent['currency']         = 'daftar';
            }
        }

        // USD price
        if (preg_match('/\$?\s*([٠-٩0-9][٠-٩0-9,]*)\s*(k|هەزار|ألف|m|ملیۆن|مليون)?/u', $lower, $m)) {
            $num = (float) str_replace(',', '', $this->arabicToAscii($m[1]));
            $suffix = mb_strtolower($m[2] ?? '');
            if ($suffix === 'k' || str_contains($suffix, 'هەزار') || str_contains($suffix, 'ألف')) $num *= 1000;
            if ($suffix === 'm' || str_contains($suffix, 'ملیۆن') || str_contains($suffix, 'مليون')) $num *= 1_000_000;
            if ($num >= 10_000) {
                $intent['min_price_usd'] = $num;
                $intent['currency']      = 'usd';
            }
        }

        return $intent;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CONFIDENCE CHECK
    // ─────────────────────────────────────────────────────────────────────────
    private function isConfident(array $intent): bool
    {
        $found = 0;
        if ($intent['listing_type'])               $found++;
        if ($intent['area'])                       $found++;
        if ($intent['property_type'])              $found++;
        if ($intent['bedrooms'])                   $found++;
        if ($intent['min_price_daftar'] || $intent['min_price_usd']) $found++;
        return $found >= 2;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CLAUDE PARSER — only called for ambiguous phrases
    // Ultra-compact prompt: ~120 input tokens, ~80 output tokens
    // ─────────────────────────────────────────────────────────────────────────
    private function claudeParse(string $transcript): ?array
    {
        $apiKey = config('services.anthropic.api_key');
        if (!$apiKey) return null;

        $system = <<<'SYS'
Kurdistan real estate voice search. Extract JSON only. No prose.
Fields: listing_type("rent"|"sell"|null), property_type("apartment"|"villa"|"house"|"land"|"office"|"shop"|null), area(string|null), city(string|null), bedrooms(int|null), min_price_daftar(int|null), max_price_daftar(int|null), min_price_usd(int|null), currency("daftar"|"usd"|"iqd"|null).
Daftar=10,000 USD. Kurdish: کرێ=rent فرۆشتن=sell دەفتەر=daftar ئۆتاق=bedroom خانوو=house شوقە=apartment.
Sorani dialect: رووکن=Rukn ژیان=Zhyan مامۆستایان=Mamostayan ئەنکاوە=Ankawa.
Return {} if truly unclear.
SYS;

        try {
            $response = Http::withHeaders([
                'x-api-key'         => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(6)->post('https://api.anthropic.com/v1/messages', [
                'model'      => self::CLAUDE_MODEL,
                'max_tokens' => self::CLAUDE_TOKENS,
                'system'     => $system,
                'messages'   => [['role' => 'user', 'content' => $transcript]],
            ]);

            if (!$response->successful()) return null;

            $text    = $response->json('content.0.text', '{}');
            $text    = preg_replace('/```json|```/', '', trim($text));
            $decoded = json_decode($text, true);
            if (!is_array($decoded)) return null;

            return [
                'listing_type'     => $decoded['listing_type']     ?? null,
                'property_type'    => $decoded['property_type']    ?? null,
                'area'             => $decoded['area']             ?? null,
                'city'             => $decoded['city']             ?? null,
                'bedrooms'         => isset($decoded['bedrooms'])  ? (int)$decoded['bedrooms'] : null,
                'min_price_daftar' => isset($decoded['min_price_daftar']) ? (int)$decoded['min_price_daftar'] : null,
                'max_price_daftar' => isset($decoded['max_price_daftar']) ? (int)$decoded['max_price_daftar'] : null,
                'min_price_usd'    => isset($decoded['min_price_usd'])    ? (int)$decoded['min_price_usd']    : null,
                'max_price_usd'    => isset($decoded['max_price_usd'])    ? (int)$decoded['max_price_usd']    : null,
                'currency'         => $decoded['currency']         ?? null,
                'raw_transcript'   => $transcript,
                'source'           => 'claude',
            ];
        } catch (\Throwable $e) {
            Log::error('Claude voice parse exception', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────
    private function resolveNumber(string $token): ?int
    {
        $ascii = $this->arabicToAscii($token);
        if (is_numeric($ascii)) return (int) $ascii;
        foreach (self::KU_NUMBERS as $word => $digit) {
            if (mb_strtolower(trim($token)) === mb_strtolower($word)) return $digit;
        }
        return null;
    }

    private function arabicToAscii(string $s): string
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