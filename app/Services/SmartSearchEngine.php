<?php

namespace App\Services;

use App\Models\Property;
use App\Services\Concerns\AppliesSearchIntentToQuery;
use App\Services\Concerns\ParsesSearchIntent;
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
    use ParsesSearchIntent;
    use AppliesSearchIntentToQuery;

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
    //  PARSING (raw text → structured intent) and QUERY BUILDING
    //  (structured intent → Eloquent where-clauses + relevance ordering)
    //  now live in App\Services\Concerns\ParsesSearchIntent and
    //  App\Services\Concerns\AppliesSearchIntentToQuery — same methods,
    //  same behavior, only relocated. See the traits' docblocks for why
    //  this split doesn't affect this class's public API.
    // ─────────────────────────────────────────────────────────────────────────
}
