<?php

namespace App\Services;

use App\Models\Property;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

/**
 * Dream Mulk — SmartSearchEngine
 *
 * Understands natural language in English, Arabic, and Kurdish.
 * Parses intent, synonyms, price shorthand, bedroom patterns,
 * property type aliases, listing type keywords, area names,
 * and scoring — all without any third-party ML dependency.
 *
 * Usage:
 *   $engine  = new SmartSearchEngine($rawQuery, $locale);
 *   $builder = $engine->apply(Property::query()->active()->published());
 *   $intent  = $engine->getIntent();   // for debugging / analytics
 */
class SmartSearchEngine
{
    // ── Raw input ─────────────────────────────────────────────────────────────
    private string $raw;
    private string $locale;       // 'en' | 'ar' | 'ku'
    private string $normalized;  // lowercased, cleaned

    // ── Parsed intent ─────────────────────────────────────────────────────────
    private array $intent = [
        'listing_type'    => null,  // 'rent' | 'sell'
        'property_types'  => [],    // ['apartment', 'villa', ...]
        'cities'          => [],    // ['Erbil', ...]
        'areas'           => [],    // ['Ankawa', 'Mamostayan', ...]
        'min_price'       => null,
        'max_price'       => null,
        'currency'        => null,  // 'usd' | 'iqd'
        'bedrooms'        => null,
        'bedrooms_plus'   => false,
        'bathrooms'       => null,
        'min_area_m2'     => null,
        'max_area_m2'     => null,
        'features'        => [],    // ['pool','parking','gym',...]
        'keywords'        => [],    // remaining unmatched words
        'verified_only'   => false,
        'furnished_only'  => false,
        'new_listing'     => false, // "new" / "جديد" / "نوێ"
    ];

    // ── Synonym maps ─────────────────────────────────────────────────────────

    private const LISTING_TYPE_MAP = [
        // English
        'rent'       => 'rent',
        'rental'      => 'rent',
        'for rent'    => 'rent',
        'lease'      => 'rent',
        'leasing'     => 'rent',
        'monthly'     => 'rent',
        'sale'       => 'sell',
        'for sale'    => 'sell',
        'buy'         => 'sell',
        'sell'       => 'sell',
        'purchase'    => 'sell',
        'investment'  => 'sell',
        'ownership'  => 'sell',
        // Arabic
        'للإيجار'    => 'rent',
        'إيجار'       => 'rent',
        'كراء'        => 'rent',
        'ايجار'      => 'rent',
        'مؤجر'        => 'rent',
        'للبيع'      => 'sell',
        'بيع'         => 'sell',
        'شراء'        => 'sell',
        'مبيع'       => 'sell',
        'استثمار'     => 'sell',
        'تملك'        => 'sell',
        // Kurdish (Sorani)
        'بۆ كرێ'     => 'rent',
        'كرێ'         => 'rent',
        'بۆ كرێدان'  => 'rent',
        'كرێدان'     => 'rent',
        'مانگانە'     => 'rent',
        'بۆ فرۆشتن'  => 'sell',
        'فرۆشتن'     => 'sell',
        'كڕین'       => 'sell',
        'بۆ كڕین'    => 'sell',
        'وەبەرهێنان' => 'sell',
    ];

    private const PROPERTY_TYPE_MAP = [
        // English
        'house'       => 'house',
        'home'        => 'house',
        'villa'       => 'villa',
        'apartment'   => 'apartment',
        'flat'        => 'apartment',
        'studio'      => 'apartment',
        'office'      => 'office',
        'commercial'  => 'office',
        'shop'        => 'shop',
        'store'       => 'shop',
        'land'        => 'land',
        'lot'         => 'land',
        'plot'        => 'land',
        'farm'        => 'land',
        'building'    => 'building',
        'tower'       => 'building',
        'duplex'      => 'duplex',
        'penthouse'   => 'apartment',
        'chalet'      => 'chalet',
        'warehouse'   => 'warehouse',
        'clinic'      => 'office',
        // Arabic
        'بيت'         => 'house',
        'منزل'        => 'house',
        'دار'         => 'house',
        'فيلا'        => 'villa',
        'فلة'         => 'villa',
        'شقة'         => 'apartment',
        'شقق'         => 'apartment',
        'استوديو'     => 'apartment',
        'مكتب'        => 'office',
        'محل'         => 'shop',
        'دكان'        => 'shop',
        'أرض'         => 'land',
        'قطعة'        => 'land',
        'عقار'        => 'land',
        'بناية'       => 'building',
        'برج'         => 'building',
        'مستودع'      => 'warehouse',
        // Kurdish
        'خانوو'       => 'house',
        'خانووبار'    => 'house',
        'ڤیلا'        => 'villa',
        'شوقە'        => 'apartment',
        'شووقە'       => 'apartment',
        'ئەپارتمان'  => 'apartment',
        'ئۆفیس'       => 'office',
        'بنکە'        => 'office',
        'دوکان'       => 'shop',
        'زەوی'        => 'land',
        'ئەرازی'      => 'land',
        'بینا'        => 'building',
    ];

    private const FEATURE_MAP = [
        // English
        'pool'       => 'pool',
        'swimming'    => 'pool',
        'gym'         => 'gym',
        'garden'     => 'garden',
        'yard'        => 'garden',
        'parking'     => 'parking',
        'garage'     => 'parking',
        'balcony'     => 'balcony',
        'terrace'     => 'balcony',
        'elevator'   => 'elevator',
        'lift'        => 'elevator',
        'security'    => 'security',
        'guard'      => 'security',
        'basement'    => 'basement',
        'storage'     => 'storage',
        'furnished'  => 'furnished',
        // Arabic
        'مسبح'       => 'pool',
        'حوض سباحة'   => 'pool',
        'صالة رياضية' => 'gym',
        'حديقة'      => 'garden',
        'موقف'        => 'parking',
        'كراج'        => 'parking',
        'بلكونة'     => 'balcony',
        'شرفة'        => 'balcony',
        'مصعد'        => 'elevator',
        'حارس'       => 'security',
        'مخزن'        => 'storage',
        'مفروش'       => 'furnished',
        'مؤثث'       => 'furnished',
        // Kurdish
        'مەلەوانگە'  => 'pool',
        'جیمناستیک'  => 'gym',
        'باخچە'       => 'garden',
        'پارکینگ'    => 'parking',
        'بالکۆن'     => 'balcony',
        'ئەسانسێر'   => 'elevator',
        'داڕێژراو'   => 'furnished',
        'خوانراو'    => 'furnished',
    ];

    // Kurdistan cities (EN/AR/KU aliases all map to canonical EN)
    private const CITY_MAP = [
        'erbil'         => 'Erbil',
        'hewler'      => 'Erbil',
        'hewlêr'     => 'Erbil',
        'هەولێر'        => 'Erbil',
        'أربيل'       => 'Erbil',
        'اربيل'      => 'Erbil',
        'sulaymaniyah'  => 'Sulaymaniyah',
        'slemani' => 'Sulaymaniyah',
        'سلێمانی'       => 'Sulaymaniyah',
        'السليمانية' => 'Sulaymaniyah',
        'duhok'         => 'Duhok',
        'دهۆک'        => 'Duhok',
        'دهوك'       => 'Duhok',
        'zakho'         => 'Zakho',
        'زاخۆ'        => 'Zakho',
        'زاخو'       => 'Zakho',
        'halabja'       => 'Halabja',
        'هەڵەبجە'     => 'Halabja',
        'حلبجة'      => 'Halabja',
        'koya'          => 'Koya',
        'کۆیە'        => 'Koya',
        'كويا'       => 'Koya',
        'shaqlawa'      => 'Shaqlawa',
        'شەقڵاوە'     => 'Shaqlawa',
        'شقلاوة'     => 'Shaqlawa',
        'soran'         => 'Soran',
        'سۆران'       => 'Soran',
        'سوران'      => 'Soran',
        'rawanduz'      => 'Rawanduz',
        'ڕەواندوز'    => 'Rawanduz',
        'رواندوز'    => 'Rawanduz',
        'chamchamal'    => 'Chamchamal',
        'چەمچەماڵ'  => 'Chamchamal',
        'ranya'         => 'Ranya',
        'ڕانیە'       => 'Ranya',
        'kirkuk'        => 'Kirkuk',
        'کەرکووک'     => 'Kirkuk',
        'كركوك'      => 'Kirkuk',
        'amedi'         => 'Amedi',
        'ئامێدی'      => 'Amedi',
        'akre'          => 'Akre',
        'عەقرە'       => 'Akre',
        'kalar'         => 'Kalar',
        'کەلار'       => 'Kalar',
        'semel'         => 'Semel',
        'سیمێل'       => 'Semel',
        'zawita'        => 'Zawita',
        'زاویتە'      => 'Zawita',
    ];

    // Top area names across all Kurdistan cities (EN/AR/KU → canonical EN)
    private const AREA_MAP = [
        // Erbil areas
        'ankawa'        => 'Ankawa',
        'عنكاوا'      => 'Ankawa',
        'عەنکاوە'    => 'Ankawa',
        'mamostayan'    => 'Mamostayan',
        'ماموستايان'  => 'Mamostayan',
        'مامۆستایان' => 'Mamostayan',
        'gulan'         => 'Gulan',
        'كولان'       => 'Gulan',
        'گوڵان'      => 'Gulan',
        'iskan'         => 'Iskan',
        'إسكان'       => 'Iskan',
        'ئەسکان'     => 'Iskan',
        'dream city'    => 'Dream City',
        'مدينة الأحلام' => 'Dream City',
        'شاری خەون' => 'Dream City',
        'empire'        => 'Empire',
        'إمباير'      => 'Empire',
        'ئیمپایر'    => 'Empire',
        'brayati'       => 'Brayati',
        'برايتي'      => 'Brayati',
        'براياتی'    => 'Brayati',
        'english village' => 'English Village',
        'القرية الإنجليزية' => 'English Village',
        'italian village' => 'Italian Village',
        'القرية الإيطالية' => 'Italian Village',
        'naz city'      => 'Naz City',
        'مدينة ناز'   => 'Naz City',
        'شاری ناز'   => 'Naz City',
        'ronaki'        => 'Ronaki',
        'رونكي'       => 'Ronaki',
        'ڕۆناکی'     => 'Ronaki',
        'kasnazan'      => 'Kasnazan',
        'كسنزان'      => 'Kasnazan',
        'کەسنەزان'   => 'Kasnazan',
        'zanko'         => 'Zanko',
        'زانكو'       => 'Zanko',
        'زانکۆ'      => 'Zanko',
        'rwanga'        => 'Rwanga',
        'روانكة'      => 'Rwanga',
        'ڕوانگە'     => 'Rwanga',
        'badawa'        => 'Badawa',
        'بداوة'       => 'Badawa',
        'باداوە'     => 'Badawa',
        'sami park'     => 'Sami Abdulrahman Park Area',
        'farmanbaran'   => 'Farmanbaran',
        'فرمانبران'   => 'Farmanbaran',
        'rozhalat'      => 'Rozhalat',
        'روژالات'     => 'Rozhalat',
        'kirkuk road'   => 'Kirkuk Road',
        'طريق كركوك'  => 'Kirkuk Road',
        'mosul road'    => 'Mosul Road',
        'طريق الموصل' => 'Mosul Road',
        'xanzad'        => 'Xanzad',
        'خانزاد'      => 'Xanzad',
        'خانزاد'     => 'Xanzad',
        'bna slawa'     => 'Bna Slawa',
        'بنسلاوة'     => 'Bna Slawa',
        'koya road'     => 'Koya Road',
        'طريق كويه'   => 'Koya Road',
        'zhyan'         => 'Zhyan',
        'زيان'        => 'Zhyan',
        'ژیان'       => 'Zhyan',
        // Sulaymaniyah areas
        'bakhtiyary'    => 'Bakhtiyary',
        'بختياري'     => 'Bakhtiyary',
        'saholaka'      => 'Saholaka',
        'سهولكة'      => 'Saholaka',
        'raparin'       => 'Raparin',
        'ربارين'      => 'Raparin',
        'tanjaro'       => 'Tanjaro',
        'تنجارو'      => 'Tanjaro',
        'piramagrun'    => 'Piramagrun',
        'بيرمكرون'    => 'Piramagrun',
        'malik mahmud'  => 'Malik Mahmud',
        'ملك محمود'  => 'Malik Mahmud',
        'azadi'         => 'Azadi',
        'آزادي'       => 'Azadi',
        'ئازادی'     => 'Azadi',
        'kani qrzhala'  => 'Kani Qrzhala',
        'debashan'      => 'Debashan',
        'saray'         => 'Saray',
        'سراي'        => 'Saray',
        // Duhok areas
        'domiz'         => 'Domiz',
        'دوميز'       => 'Domiz',
        'دۆمێز'      => 'Domiz',
        'summel'        => 'Summel',
        'سميل'        => 'Summel',
        'سومێل'      => 'Summel',
        'baroshke'      => 'Baroshke',
        'باروشكي'     => 'Baroshke',
        'بارۆشکە'    => 'Baroshke',
        'nisibin'       => 'Nisibin',
        'نسيبين'      => 'Nisibin',
        'mazi'          => 'Mazi',
        'مازي'        => 'Mazi',
        'مازی'       => 'Mazi',
        'mahabad'       => 'Mahabad',
        'مهاباد'      => 'Mahabad',
        'مەهاباد'    => 'Mahabad',
    ];

    // ── Price unit patterns ──────────────────────────────────────────────────
    // Matches: 150k, 150,000, 150.5m, 200 million, 50 ملیون, etc.
    private const PRICE_PATTERNS = [
        // "under 200k" / "below 150k"
        '/(?:under|below|less than|max|أقل من|حداكثر|کەمتر لە|ژێر)\s*\$?\s*([\d,\.]+)\s*(k|m|thousand|million|ألف|مليون|هەزار|ملیون)?/iu' => ['max', null],
        // "above 200k" / "over 150k" / "more than"
        '/(?:above|over|more than|min|أكثر من|بیش از|زیاتر لە|سەروو)\s*\$?\s*([\d,\.]+)\s*(k|m|thousand|million|ألف|مليون|هەزار|ملیون)?/iu' => ['min', null],
        // "between 100k and 300k"
        '/(?:between|بين|نێوان)\s*\$?\s*([\d,\.]+)\s*(k|m|thousand|million|ألف|مليون|هەزار|ملیون)?\s*(?:and|to|و|تا|و)\s*\$?\s*([\d,\.]+)\s*(k|m|thousand|million|ألف|مليون|هەزار|ملیون)?/iu' => ['between', null],
        // Bare price with unit: "$200k", "200k", "100 million IQD"
        '/\$?\s*([\d,\.]+)\s*(k|m|thousand|million|ألف|مليون|هەزار|ملیون)/iu' => ['bare', null],
    ];

    // ── Bedroom patterns ────────────────────────────────────────────────────
    private const BEDROOM_PATTERNS = [
        // "3 bedroom", "3br", "3 bed", "3 غرفة", "3 ئۆتاق"
        '/(\d+)\s*(?:\+)?\s*(?:bedroom|bed|br|room|غرفة|غرف|ئۆتاق|ژووری خەو|خوابگاه)/iu',
        // "studio" maps to 0 bedrooms
        '/\b(studio|استوديو|ستودیۆ)\b/iu',
    ];

    // ── Bathroom patterns ───────────────────────────────────────────────────
    private const BATHROOM_PATTERNS = [
        '/(\d+)\s*(?:bathroom|bath|wc|حمام|دورەخانە)/iu',
    ];

    // ── Area m² patterns ────────────────────────────────────────────────────
    private const AREA_PATTERNS = [
        // "under 200m2" / "200 sq" / "200 square meter"
        '/(?:under|below|less than|max|أقل من)\s*(\d+)\s*(?:m2|m²|sqm|sq\.?m\.?|متر مربع|متر|متر مكعب|مەترە)/iu' => 'max_area',
        '/(?:over|above|more than|min|أكثر من)\s*(\d+)\s*(?:m2|m²|sqm|sq\.?m\.?|متر مربع|متر|مەترە)/iu'           => 'min_area',
        '/(\d+)\s*(?:to|-)\s*(\d+)\s*(?:m2|m²|sqm|sq\.?m\.?|متر مربع|متر|مەترە)/iu'                                 => 'area_range',
        '/(\d+)\s*(?:m2|m²|sqm|sq\.?m\.?|متر مربع|مەترە)/iu'                                                         => 'approx_area',
    ];

    // ── Verified / New ──────────────────────────────────────────────────────
    private const VERIFIED_KEYWORDS = [
        'verified',
        'authentic',
        'trusted',
        'مؤكد',
        'موثق',
        'راستکراوەتەوە',
        'پشتڕاستکراو',
    ];
    private const NEW_KEYWORDS = [
        'new',
        'latest',
        'recently',
        'fresh',
        'جديد',
        'حديث',
        'نوێ',
        'تازە',
    ];
    private const FURNISHED_KEYWORDS = [
        'furnished',
        'fully furnished',
        'مفروش',
        'مؤثث',
        'داڕێژراو',
        'خوانراو',
    ];

    // ─────────────────────────────────────────────────────────────────────────
    //  Constructor
    // ─────────────────────────────────────────────────────────────────────────

    public function __construct(string $raw, string $locale = 'en')
    {
        $this->raw        = trim($raw);
        $this->locale     = in_array($locale, ['en', 'ar', 'ku']) ? $locale : 'en';
        $this->normalized = mb_strtolower($this->raw, 'UTF-8');
        $this->parse();
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Public API
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Apply all parsed intent to an Eloquent builder.
     * Returns the builder so callers can chain pagination/sorting.
     */
    public function apply(Builder $query): Builder
    {
        // 1. Full-text + keyword search across all three languages
        $this->applyKeywordSearch($query);

        // 2. Structured filters from parsed intent
        $this->applyListingType($query);
        $this->applyPropertyTypes($query);
        $this->applyCities($query);
        $this->applyAreas($query);
        $this->applyPrice($query);
        $this->applyBedrooms($query);
        $this->applyBathrooms($query);
        $this->applyAreaM2($query);
        $this->applyFeatures($query);
        $this->applyFlags($query);

        // 3. Relevance scoring (boosts more relevant results to top)
        $this->applyRelevanceScore($query);

        return $query;
    }

    /** Return the parsed intent array (useful for analytics / debug). */
    public function getIntent(): array
    {
        return $this->intent;
    }

    /** Return true if the engine extracted ANY structured intent. */
    public function hasStructuredIntent(): bool
    {
        foreach ($this->intent as $key => $val) {
            if ($key === 'keywords') continue;
            if (is_array($val) && count($val) > 0) return true;
            if (!is_array($val) && $val !== null && $val !== false) return true;
        }
        return false;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  PARSING — converts raw text into structured intent
    // ─────────────────────────────────────────────────────────────────────────

    private function parse(): void
    {
        $text = $this->normalized;

        $this->parseListingType($text);
        $this->parsePropertyTypes($text);
        $this->parseCities($text);
        $this->parseAreas($text);
        $this->parsePrices($text);
        $this->parseBedrooms($text);
        $this->parseBathrooms($text);
        $this->parseAreaM2($text);
        $this->parseFeatures($text);
        $this->parseFlags($text);
        $this->extractRemainingKeywords($text);
    }

    // ── Listing type ─────────────────────────────────────────────────────────

    private function parseListingType(string $text): void
    {
        // Try longest phrases first to avoid partial matches
        $sorted = array_keys(self::LISTING_TYPE_MAP);
        usort($sorted, fn($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        foreach ($sorted as $phrase) {
            if (mb_strpos($text, mb_strtolower($phrase, 'UTF-8')) !== false) {
                $this->intent['listing_type'] = self::LISTING_TYPE_MAP[$phrase];
                return;
            }
        }
    }

    // ── Property types ───────────────────────────────────────────────────────

    private function parsePropertyTypes(string $text): void
    {
        $found = [];
        $sorted = array_keys(self::PROPERTY_TYPE_MAP);
        usort($sorted, fn($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        foreach ($sorted as $alias) {
            if (mb_strpos($text, mb_strtolower($alias, 'UTF-8')) !== false) {
                $canonical = self::PROPERTY_TYPE_MAP[$alias];
                if (!in_array($canonical, $found)) {
                    $found[] = $canonical;
                }
            }
        }

        $this->intent['property_types'] = $found;
    }

    // ── Cities ───────────────────────────────────────────────────────────────

    private function parseCities(string $text): void
    {
        $found = [];
        $sorted = array_keys(self::CITY_MAP);
        usort($sorted, fn($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        foreach ($sorted as $alias) {
            if (mb_strpos($text, mb_strtolower($alias, 'UTF-8')) !== false) {
                $canonical = self::CITY_MAP[$alias];
                if (!in_array($canonical, $found)) {
                    $found[] = $canonical;
                }
            }
        }

        $this->intent['cities'] = $found;
    }

    // ── Areas ────────────────────────────────────────────────────────────────

    private function parseAreas(string $text): void
    {
        $found = [];
        $sorted = array_keys(self::AREA_MAP);
        usort($sorted, fn($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        foreach ($sorted as $alias) {
            if (mb_strpos($text, mb_strtolower($alias, 'UTF-8')) !== false) {
                $canonical = self::AREA_MAP[$alias];
                if (!in_array($canonical, $found)) {
                    $found[] = $canonical;
                }
            }
        }

        $this->intent['areas'] = $found;
    }

    // ── Prices ───────────────────────────────────────────────────────────────

    private function parsePrices(string $text): void
    {
        // Detect currency first
        if (preg_match('/\b(iqd|دينار|دینار|IQD)\b/iu', $text)) {
            $this->intent['currency'] = 'iqd';
        } elseif (preg_match('/\b(\$|usd|دولار|دۆلار)\b/iu', $text)) {
            $this->intent['currency'] = 'usd';
        }

        // "between X and Y"
        if (preg_match(
            '/(?:between|بين|نێوان)\s*\$?\s*([\d,\.]+)\s*(k|m|thousand|million|ألف|مليون|هەزار|ملیون)?\s*(?:and|to|و|تا)\s*\$?\s*([\d,\.]+)\s*(k|m|thousand|million|ألف|مليون|هەزار|ملیون)?/iu',
            $text,
            $m
        )) {
            $this->intent['min_price'] = $this->parseAmount($m[1], $m[2] ?? '');
            $this->intent['max_price'] = $this->parseAmount($m[3], $m[4] ?? '');
            return;
        }

        // "under / below / less than X"
        if (preg_match(
            '/(?:under|below|less than|max|أقل من|حداكثر|کەمتر لە|ژێر)\s*\$?\s*([\d,\.]+)\s*(k|m|thousand|million|ألف|مليون|هەزار|ملیون)?/iu',
            $text,
            $m
        )) {
            $this->intent['max_price'] = $this->parseAmount($m[1], $m[2] ?? '');
        }

        // "over / above / more than X"
        if (preg_match(
            '/(?:above|over|more than|min|أكثر من|بیشتر لە|زیاتر لە|سەروو)\s*\$?\s*([\d,\.]+)\s*(k|m|thousand|million|ألف|مليون|هەزار|ملیون)?/iu',
            $text,
            $m
        )) {
            $this->intent['min_price'] = $this->parseAmount($m[1], $m[2] ?? '');
        }

        // Bare: "$200k", "200k", "100m"
        if ($this->intent['min_price'] === null && $this->intent['max_price'] === null) {
            if (preg_match('/\$?\s*([\d,\.]+)\s*(k|m|thousand|million|ألف|مليون|هەزار|ملیون)/iu', $text, $m)) {
                // Treat bare price as "around this value" — ±30% range
                $amount = $this->parseAmount($m[1], $m[2]);
                $this->intent['min_price'] = $amount * 0.70;
                $this->intent['max_price'] = $amount * 1.30;
            }
        }
    }

    private function parseAmount(string $number, string $unit): float
    {
        $number = str_replace(',', '', $number);
        $val    = (float) $number;
        $u      = mb_strtolower(trim($unit), 'UTF-8');

        return match (true) {
            in_array($u, ['m', 'million', 'مليون', 'ملیون'])       => $val * 1_000_000,
            in_array($u, ['k', 'thousand', 'ألف', 'هەزار'])        => $val * 1_000,
            default                                                   => $val,
        };
    }

    // ── Bedrooms ─────────────────────────────────────────────────────────────

    private function parseBedrooms(string $text): void
    {
        // Studio → 0 bedrooms
        if (preg_match('/\b(studio|استوديو|ستودیۆ)\b/iu', $text)) {
            $this->intent['bedrooms']      = 0;
            $this->intent['bedrooms_plus'] = false;
            return;
        }

        // "3 bedroom", "3+", "3br", "3 غرفة", "3 ئۆتاق"
        if (preg_match(
            '/(\d+)\s*(\+)?\s*(?:bedroom|bed|br|rooms?|غرف(?:ة)?|ئۆتاق|ژووری خەو|خوابگاه)?/iu',
            $text,
            $m
        )) {
            $num = (int) $m[1];
            if ($num >= 1 && $num <= 20) {
                $this->intent['bedrooms']      = $num;
                $this->intent['bedrooms_plus'] = isset($m[2]) && $m[2] === '+';
            }
        }
    }

    // ── Bathrooms ────────────────────────────────────────────────────────────

    private function parseBathrooms(string $text): void
    {
        if (preg_match('/(\d+)\s*(?:bathroom|bath|wc|حمام|دورەخانە)/iu', $text, $m)) {
            $this->intent['bathrooms'] = (int) $m[1];
        }
    }

    // ── Area m² ──────────────────────────────────────────────────────────────

    private function parseAreaM2(string $text): void
    {
        // Range: "150 to 250 m2"
        if (preg_match('/(\d+)\s*(?:to|-)\s*(\d+)\s*(?:m2|m²|sqm|sq\.?m\.?|متر مربع|مەترە)/iu', $text, $m)) {
            $this->intent['min_area_m2'] = (float) $m[1];
            $this->intent['max_area_m2'] = (float) $m[2];
            return;
        }
        if (preg_match('/(?:under|below|أقل من)\s*(\d+)\s*(?:m2|m²|sqm|sq\.?m\.?|متر مربع|مەترە)/iu', $text, $m)) {
            $this->intent['max_area_m2'] = (float) $m[1];
        }
        if (preg_match('/(?:over|above|أكثر من)\s*(\d+)\s*(?:m2|m²|sqm|sq\.?m\.?|متر مربع|مەترە)/iu', $text, $m)) {
            $this->intent['min_area_m2'] = (float) $m[1];
        }
        // Approximate: "200m2" → ±20%
        if ($this->intent['min_area_m2'] === null && $this->intent['max_area_m2'] === null) {
            if (preg_match('/(\d+)\s*(?:m2|m²|sqm|sq\.?m\.?|متر مربع|مەترە)/iu', $text, $m)) {
                $val = (float) $m[1];
                $this->intent['min_area_m2'] = $val * 0.80;
                $this->intent['max_area_m2'] = $val * 1.20;
            }
        }
    }

    // ── Features ─────────────────────────────────────────────────────────────

    private function parseFeatures(string $text): void
    {
        $found = [];
        foreach (self::FEATURE_MAP as $alias => $canonical) {
            if (mb_strpos($text, mb_strtolower($alias, 'UTF-8')) !== false) {
                if (!in_array($canonical, $found)) {
                    $found[] = $canonical;
                }
            }
        }
        // Furnished is also a standalone flag
        if (in_array('furnished', $found)) {
            $this->intent['furnished_only'] = true;
        }
        $this->intent['features'] = array_filter($found, fn($f) => $f !== 'furnished');
    }

    // ── Flags ────────────────────────────────────────────────────────────────

    private function parseFlags(string $text): void
    {
        foreach (self::VERIFIED_KEYWORDS as $kw) {
            if (mb_strpos($text, mb_strtolower($kw, 'UTF-8')) !== false) {
                $this->intent['verified_only'] = true;
                break;
            }
        }
        foreach (self::NEW_KEYWORDS as $kw) {
            if (mb_strpos($text, mb_strtolower($kw, 'UTF-8')) !== false) {
                $this->intent['new_listing'] = true;
                break;
            }
        }
        foreach (self::FURNISHED_KEYWORDS as $kw) {
            if (mb_strpos($text, mb_strtolower($kw, 'UTF-8')) !== false) {
                $this->intent['furnished_only'] = true;
                break;
            }
        }
    }

    // ── Remaining keywords ───────────────────────────────────────────────────

    private function extractRemainingKeywords(string $text): void
    {
        // Strip known phrases so we get truly unmatched words
        $strip = array_merge(
            array_keys(self::LISTING_TYPE_MAP),
            array_keys(self::PROPERTY_TYPE_MAP),
            array_keys(self::CITY_MAP),
            array_keys(self::AREA_MAP),
            array_keys(self::FEATURE_MAP),
            self::VERIFIED_KEYWORDS,
            self::NEW_KEYWORDS,
            self::FURNISHED_KEYWORDS,
            [
                'between',
                'and',
                'to',
                'under',
                'above',
                'over',
                'below',
                'less than',
                'more than',
                'min',
                'max',
                'usd',
                'iqd',
                '$',
                'bedroom',
                'bed',
                'br',
                'bathroom',
                'bath',
                'm2',
                'm²',
                'sqm',
                'studio',
                'بين',
                'و',
                'أقل من',
                'أكثر من',
                'دينار',
                'دولار',
                'غرفة',
                'حمام',
                'متر مربع',
                'نێوان',
                'کەمتر لە',
                'زیاتر لە',
                'ئۆتاق',
                'مەترە',
            ]
        );

        $remaining = $text;
        foreach ($strip as $phrase) {
            $remaining = str_ireplace(mb_strtolower($phrase, 'UTF-8'), ' ', $remaining);
        }

        // Remove numbers and punctuation
        $remaining = preg_replace('/[\d\+\-\$\.,\?!]/u', ' ', $remaining);
        $remaining = preg_replace('/\s+/u', ' ', trim($remaining));

        $words = array_filter(explode(' ', $remaining), fn($w) => mb_strlen(trim($w), 'UTF-8') > 2);
        $this->intent['keywords'] = array_values($words);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  QUERY BUILDING
    // ─────────────────────────────────────────────────────────────────────────

    private function applyKeywordSearch(Builder $query): void
    {
        $raw = $this->raw;
        if (empty($raw)) return;

        $query->where(function (Builder $q) use ($raw) {
            // ── Name (all 3 languages) ────────────────────────────────────
            $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))) LIKE LOWER(?)", ["%{$raw}%"])
                ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.ar')) LIKE ?", ["%{$raw}%"])
                ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.ku')) LIKE ?", ["%{$raw}%"])

                // ── Description ───────────────────────────────────────────────
                ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(description, '$.en'))) LIKE LOWER(?)", ["%{$raw}%"])
                ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(description, '$.ar')) LIKE ?", ["%{$raw}%"])
                ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(description, '$.ku')) LIKE ?", ["%{$raw}%"])

                // ── Address — city ────────────────────────────────────────────
                ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.en'))) LIKE LOWER(?)", ["%{$raw}%"])
                ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.ar')) LIKE ?", ["%{$raw}%"])
                ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.ku')) LIKE ?", ["%{$raw}%"])

                // ── Address — neighborhood / area ─────────────────────────────
                ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.neighborhood.en'))) LIKE LOWER(?)", ["%{$raw}%"])
                ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.neighborhood.ar')) LIKE ?", ["%{$raw}%"])
                ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.neighborhood.ku')) LIKE ?", ["%{$raw}%"])

                // ── Address — district ────────────────────────────────────────
                ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.district.en'))) LIKE LOWER(?)", ["%{$raw}%"])
                ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.district.ar')) LIKE ?", ["%{$raw}%"])
                ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.district.ku')) LIKE ?", ["%{$raw}%"])

                // ── Plain address string ───────────────────────────────────────
                ->orWhere('address', 'LIKE', "%{$raw}%");

            // ── Also search each remaining keyword independently ───────────
            foreach ($this->intent['keywords'] as $kw) {
                if (mb_strlen($kw, 'UTF-8') < 2) continue;
                $q->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))) LIKE LOWER(?)", ["%{$kw}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.ar')) LIKE ?", ["%{$kw}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.ku')) LIKE ?", ["%{$kw}%"])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.neighborhood.en'))) LIKE LOWER(?)", ["%{$kw}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.neighborhood.ar')) LIKE ?", ["%{$kw}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.neighborhood.ku')) LIKE ?", ["%{$kw}%"]);
            }
        });
    }

    private function applyListingType(Builder $query): void
    {
        if ($this->intent['listing_type']) {
            $query->where('listing_type', $this->intent['listing_type']);
        }
    }

    private function applyPropertyTypes(Builder $query): void
    {
        $types = $this->intent['property_types'];
        if (empty($types)) return;

        $query->where(function (Builder $q) use ($types) {
            foreach ($types as $type) {
                $q->orWhereRaw(
                    "LOWER(JSON_UNQUOTE(JSON_EXTRACT(type, '$.category'))) = ?",
                    [strtolower($type)]
                );
            }
        });
    }

    private function applyCities(Builder $query): void
    {
        $cities = $this->intent['cities'];
        if (empty($cities)) return;

        $query->where(function (Builder $q) use ($cities) {
            foreach ($cities as $city) {
                $q->orWhereRaw(
                    "LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.en'))) LIKE LOWER(?)",
                    ["%{$city}%"]
                )->orWhereRaw(
                    "JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.ar')) LIKE ?",
                    ["%{$city}%"]
                )->orWhereRaw(
                    "JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.ku')) LIKE ?",
                    ["%{$city}%"]
                );
            }
        });
    }

    private function applyAreas(Builder $query): void
    {
        $areas = $this->intent['areas'];
        if (empty($areas)) return;

        $query->where(function (Builder $q) use ($areas) {
            foreach ($areas as $area) {
                $q->orWhereRaw(
                    "LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.neighborhood.en'))) LIKE LOWER(?)",
                    ["%{$area}%"]
                )->orWhereRaw(
                    "JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.neighborhood.ar')) LIKE ?",
                    ["%{$area}%"]
                )->orWhereRaw(
                    "JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.neighborhood.ku')) LIKE ?",
                    ["%{$area}%"]
                )->orWhereRaw(
                    "LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.district.en'))) LIKE LOWER(?)",
                    ["%{$area}%"]
                )->orWhere('address', 'LIKE', "%{$area}%");
            }
        });
    }

    private function applyPrice(Builder $query): void
    {
        $currency = $this->intent['currency'] ?? 'usd';
        $min      = $this->intent['min_price'];
        $max      = $this->intent['max_price'];

        if ($min !== null) {
            $query->whereRaw(
                "CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.{$currency}')) AS DECIMAL(20,2)) >= ?",
                [$min]
            )->whereRaw(
                "CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.{$currency}')) AS DECIMAL(20,2)) > 0"
            );
        }

        if ($max !== null) {
            $query->whereRaw(
                "CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.{$currency}')) AS DECIMAL(20,2)) <= ?",
                [$max]
            )->whereRaw(
                "CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.{$currency}')) AS DECIMAL(20,2)) > 0"
            );
        }
    }

    private function applyBedrooms(Builder $query): void
    {
        if ($this->intent['bedrooms'] === null) return;

        $count = $this->intent['bedrooms'];

        if ($this->intent['bedrooms_plus']) {
            $query->whereRaw(
                "CAST(JSON_UNQUOTE(JSON_EXTRACT(rooms, '$.bedroom.count')) AS UNSIGNED) >= ?",
                [$count]
            );
        } else {
            $query->whereRaw(
                "CAST(JSON_UNQUOTE(JSON_EXTRACT(rooms, '$.bedroom.count')) AS UNSIGNED) = ?",
                [$count]
            );
        }
    }

    private function applyBathrooms(Builder $query): void
    {
        if ($this->intent['bathrooms'] === null) return;

        $query->whereRaw(
            "CAST(JSON_UNQUOTE(JSON_EXTRACT(rooms, '$.bathroom.count')) AS UNSIGNED) = ?",
            [$this->intent['bathrooms']]
        );
    }

    private function applyAreaM2(Builder $query): void
    {
        if ($this->intent['min_area_m2'] !== null) {
            $query->where('area', '>=', $this->intent['min_area_m2']);
        }
        if ($this->intent['max_area_m2'] !== null) {
            $query->where('area', '<=', $this->intent['max_area_m2']);
        }
    }

    private function applyFeatures(Builder $query): void
    {
        foreach ($this->intent['features'] as $feature) {
            $query->whereRaw("JSON_CONTAINS(LOWER(features), '\"" . addslashes($feature) . "\"')");
        }
    }

    private function applyFlags(Builder $query): void
    {
        if ($this->intent['verified_only']) {
            $query->where('verified', true);
        }
        if ($this->intent['furnished_only']) {
            $query->where('furnished', true);
        }
        if ($this->intent['new_listing']) {
            $query->where('created_at', '>=', now()->subDays(14));
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  RELEVANCE SCORING
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Adds a computed `search_score` column and orders by it descending.
     * Higher = more relevant.
     *
     * Score components:
     *   +50   exact match in name (any language)
     *   +30   match in neighborhood/area
     *   +20   match in city
     *   +20   match in description
     *   +15   is_boosted
     *   +10   verified
     *   +5    fresh (≤7 days)
     *   +2    has images ≥ 3
     */
    private function applyRelevanceScore(Builder $query): void
    {
        $raw   = addslashes($this->raw);
        $query->selectRaw("
            properties.*,
            (
                -- Name exact / partial match
                (CASE
                    WHEN LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))) LIKE LOWER('{$raw}') THEN 100
                    WHEN LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))) LIKE LOWER('%{$raw}%') THEN 50
                    WHEN JSON_UNQUOTE(JSON_EXTRACT(name, '$.ar'))        LIKE '%{$raw}%'        THEN 50
                    WHEN JSON_UNQUOTE(JSON_EXTRACT(name, '$.ku'))        LIKE '%{$raw}%'        THEN 50
                    ELSE 0
                END) +

                -- Neighborhood / area match
                (CASE
                    WHEN LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.neighborhood.en'))) LIKE LOWER('%{$raw}%') THEN 30
                    WHEN JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.neighborhood.ar'))        LIKE '%{$raw}%'        THEN 30
                    WHEN JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.neighborhood.ku'))        LIKE '%{$raw}%'        THEN 30
                    ELSE 0
                END) +

                -- City match
                (CASE
                    WHEN LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.en'))) LIKE LOWER('%{$raw}%') THEN 20
                    WHEN JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.ar'))        LIKE '%{$raw}%'        THEN 20
                    WHEN JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.ku'))        LIKE '%{$raw}%'        THEN 20
                    ELSE 0
                END) +

                -- Description match
                (CASE
                    WHEN LOWER(JSON_UNQUOTE(JSON_EXTRACT(description, '$.en'))) LIKE LOWER('%{$raw}%') THEN 20
                    ELSE 0
                END) +

                -- Quality boosters
                (CASE WHEN is_boosted = 1 THEN 15 ELSE 0 END) +
                (CASE WHEN verified   = 1 THEN 10 ELSE 0 END) +
                (CASE WHEN DATEDIFF(NOW(), created_at) <= 7 THEN 5 ELSE 0 END) +
                (CASE WHEN JSON_LENGTH(images) >= 3        THEN 2 ELSE 0 END)

            ) AS search_score
        ")->orderByDesc('search_score')
            ->orderByDesc('is_boosted')
            ->orderByDesc('created_at');
    }
}
