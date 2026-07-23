<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Performance migration — adds MySQL generated (computed) columns that
 * mirror values already stored inside the `price`, `rooms`, `type`, and
 * `locations` JSON columns, then indexes those generated columns.
 *
 * Why: PropertyController/PropertyInteractionService filter on these
 * values using whereRaw("CAST(JSON_UNQUOTE(JSON_EXTRACT(...)) ...")) or
 * a correlated whereExists JSON subquery (map bounds). MySQL cannot use
 * a normal index on a JSON path expression, so every price/bedroom/
 * bathroom/type/map-bounds filter was doing a full table scan + JSON
 * parse on every single row, every request.
 *
 * This migration does NOT remove, rename, or change any existing
 * column, and does NOT change what data is returned — it only adds new
 * columns whose values are mechanically derived (STORED GENERATED)
 * from the existing JSON, using the exact same JSON path + cast that
 * the application already used. The application code is updated
 * separately to filter on these new indexed columns instead of the
 * raw JSON path, which produces identical results, just faster.
 *
 * Note: on a very large `properties` table, adding STORED generated
 * columns rewrites the table and can take a while / briefly lock it.
 * Recommend running this during low-traffic hours.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Generated columns must be added with raw SQL — Laravel's
        // Blueprint doesn't have first-class support for MySQL
        // "STORED GENERATED ALWAYS AS" on older versions.
        DB::statement("
            ALTER TABLE properties
                ADD COLUMN price_usd DECIMAL(20,2)
                    GENERATED ALWAYS AS (JSON_UNQUOTE(JSON_EXTRACT(price, '$.usd'))) STORED,
                ADD COLUMN price_iqd DECIMAL(20,2)
                    GENERATED ALWAYS AS (JSON_UNQUOTE(JSON_EXTRACT(price, '$.iqd'))) STORED,
                ADD COLUMN bedrooms_count SMALLINT UNSIGNED
                    GENERATED ALWAYS AS (JSON_UNQUOTE(JSON_EXTRACT(rooms, '$.bedroom.count'))) STORED,
                ADD COLUMN bathrooms_count SMALLINT UNSIGNED
                    GENERATED ALWAYS AS (JSON_UNQUOTE(JSON_EXTRACT(rooms, '$.bathroom.count'))) STORED,
                ADD COLUMN property_type_category VARCHAR(100)
                    GENERATED ALWAYS AS (LOWER(JSON_UNQUOTE(JSON_EXTRACT(type, '$.category')))) STORED,
                ADD COLUMN primary_lat DECIMAL(10,7)
                    GENERATED ALWAYS AS (JSON_UNQUOTE(JSON_EXTRACT(JSON_EXTRACT(locations, '$[0]'), '$.lat'))) STORED,
                ADD COLUMN primary_lng DECIMAL(10,7)
                    GENERATED ALWAYS AS (JSON_UNQUOTE(JSON_EXTRACT(JSON_EXTRACT(locations, '$[0]'), '$.lng'))) STORED
        ");

        Schema::table('properties', function ($table) {
            $table->index('price_usd');
            $table->index('price_iqd');
            $table->index('bedrooms_count');
            $table->index('bathrooms_count');
            $table->index('property_type_category');

            // Composite index for the most common combined filter:
            // "rent/sell + type + price range" used by index()/search().
            $table->index(
                ['listing_type', 'property_type_category', 'price_usd'],
                'properties_listing_type_category_price_idx'
            );

            // Composite index for the map bounds query in
            // PropertyController::getMapProperties() — replaces a
            // correlated whereExists + double JSON_EXTRACT subquery
            // with a plain indexed BETWEEN range scan.
            $table->index(['primary_lat', 'primary_lng'], 'properties_primary_lat_lng_idx');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function ($table) {
            $table->dropIndex('properties_listing_type_category_price_idx');
            $table->dropIndex('properties_primary_lat_lng_idx');
            $table->dropIndex(['price_usd']);
            $table->dropIndex(['price_iqd']);
            $table->dropIndex(['bedrooms_count']);
            $table->dropIndex(['bathrooms_count']);
            $table->dropIndex(['property_type_category']);
        });

        DB::statement("
            ALTER TABLE properties
                DROP COLUMN price_usd,
                DROP COLUMN price_iqd,
                DROP COLUMN bedrooms_count,
                DROP COLUMN bathrooms_count,
                DROP COLUMN property_type_category,
                DROP COLUMN primary_lat,
                DROP COLUMN primary_lng
        ");
    }
};
