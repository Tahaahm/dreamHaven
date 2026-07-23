<?php

namespace App\Services\Concerns;

/**
 * Turns the raw, normalized search string into the structured $intent
 * array (listing type, property types, cities, areas, price range,
 * bedrooms/bathrooms, area in m², features, verified/new/furnished flags,
 * and leftover free-text keywords). Extracted from SmartSearchEngine.php
 * as-is — no behavior changed, only relocated. PHP compiles trait methods
 * directly into the class that uses them, and `self::` inside a trait
 * resolves against the composing class, so these methods still see
 * SmartSearchEngine's private $intent property and its private const
 * synonym maps (LISTING_TYPE_MAP, PROPERTY_TYPE_MAP, CITY_MAP, AREA_MAP,
 * FEATURE_MAP, VERIFIED_KEYWORDS, NEW_KEYWORDS, FURNISHED_KEYWORDS)
 * exactly as before. SmartSearchEngine's public API (constructor, apply(),
 * getIntent(), hasStructuredIntent()) — and the one place it's directly
 * instantiated, PropertyController::search() — is completely unaffected.
 */
trait ParsesSearchIntent
{
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
}
