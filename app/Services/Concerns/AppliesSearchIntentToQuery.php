<?php

namespace App\Services\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Turns the parsed $intent array (see ParsesSearchIntent) into Eloquent
 * where-clauses and the final relevance-score ordering. Extracted from
 * SmartSearchEngine.php as-is — no behavior changed, only relocated.
 * See ParsesSearchIntent.php for why splitting SmartSearchEngine's
 * internals into traits is safe: `self::` / `$this->` inside a trait
 * method resolve against the composing class, so these methods still see
 * SmartSearchEngine's private $raw/$intent properties exactly as before,
 * and the class's public API (constructor, apply(), getIntent(),
 * hasStructuredIntent()) and its one instantiation point
 * (PropertyController::search()) are completely unaffected.
 */
trait AppliesSearchIntentToQuery
{
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
