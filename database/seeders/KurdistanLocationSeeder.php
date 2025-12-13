<?php

// database/seeders/KurdistanLocationSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\Area;

class KurdistanLocationSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌍 Starting Kurdistan Region locations seeding...');

        // ==================== ERBIL PROVINCE ====================

        $erbil = Branch::create([
            'city_name_en' => 'Erbil',
            'city_name_ar' => 'أربيل',
            'city_name_ku' => 'هەولێر',
            'latitude' => 36.1911,
            'longitude' => 44.0092,
            'is_active' => true
        ]);

        $erbilAreas = [
            // Central Erbil
            ['area_name_en' => 'Shar', 'area_name_ar' => 'شار', 'area_name_ku' => 'شار', 'latitude' => 36.1900, 'longitude' => 44.0100],
            ['area_name_en' => 'Qalat (Citadel)', 'area_name_ar' => 'قلعة أربيل', 'area_name_ku' => 'قهڵا', 'latitude' => 36.1911, 'longitude' => 44.0092],
            ['area_name_en' => 'Shorsh', 'area_name_ar' => 'شورش', 'area_name_ku' => 'شۆرش', 'latitude' => 36.1920, 'longitude' => 43.9980],
            ['area_name_en' => 'Brayati', 'area_name_ar' => 'برايتي', 'area_name_ku' => 'براياتی', 'latitude' => 36.1885, 'longitude' => 44.0050],

            // Modern Districts
            ['area_name_en' => 'Ankawa', 'area_name_ar' => 'عنكاوا', 'area_name_ku' => 'عەنکاوە', 'latitude' => 36.2167, 'longitude' => 44.0167],
            ['area_name_en' => 'Ainkawa Mall Area', 'area_name_ar' => 'منطقة عنكاوا مول', 'area_name_ku' => 'ناحيەی مۆڵی عەنکاوە', 'latitude' => 36.2180, 'longitude' => 44.0200],
            ['area_name_en' => 'Italian Village', 'area_name_ar' => 'القرية الإيطالية', 'area_name_ku' => 'گوندی ئیتاڵی', 'latitude' => 36.1780, 'longitude' => 44.0280],
            ['area_name_en' => 'English Village', 'area_name_ar' => 'القرية الإنجليزية', 'area_name_ku' => 'گوندی ئینگلیزی', 'latitude' => 36.1790, 'longitude' => 44.0290],
            ['area_name_en' => 'Dream City', 'area_name_ar' => 'مدينة الأحلام', 'area_name_ku' => 'شاری خەون', 'latitude' => 36.2100, 'longitude' => 44.0450],
            ['area_name_en' => 'Empire', 'area_name_ar' => 'إمباير', 'area_name_ku' => 'ئیمپایر', 'latitude' => 36.2050, 'longitude' => 44.0400],
            ['area_name_en' => 'Naz City', 'area_name_ar' => 'مدينة ناز', 'area_name_ku' => 'شاری ناز', 'latitude' => 36.2200, 'longitude' => 44.0500],

            // Main Streets/Districts
            ['area_name_en' => '100 Meter', 'area_name_ar' => '100 متر', 'area_name_ku' => '100 مەتر', 'latitude' => 36.1850, 'longitude' => 44.0050],
            ['area_name_en' => '60 Meter', 'area_name_ar' => '60 متر', 'area_name_ku' => '60 مەتر', 'latitude' => 36.1900, 'longitude' => 44.0100],
            ['area_name_en' => '40 Meter', 'area_name_ar' => '40 متر', 'area_name_ku' => '40 مەتر', 'latitude' => 36.1880, 'longitude' => 44.0080],
            ['area_name_en' => '30 Meter', 'area_name_ar' => '30 متر', 'area_name_ku' => '30 مەتر', 'latitude' => 36.1870, 'longitude' => 44.0070],
            ['area_name_en' => '120 Meter', 'area_name_ar' => '120 متر', 'area_name_ku' => '120 مەتر', 'latitude' => 36.1920, 'longitude' => 44.0150],

            // Residential Areas
            ['area_name_en' => 'Zhyan', 'area_name_ar' => 'زيان', 'area_name_ku' => 'ژیان', 'latitude' => 36.1825, 'longitude' => 44.0156],
            ['area_name_en' => 'Bna Slawa', 'area_name_ar' => 'بنا سلوى', 'area_name_ku' => 'بنە سڵاوە', 'latitude' => 36.1950, 'longitude' => 44.0200],
            ['area_name_en' => 'Sami Abdulrahman Park Area', 'area_name_ar' => 'منتزه سامي عبدالرحمن', 'area_name_ku' => 'ناحیەی باخچەی سامی عەبدولڕەحمان', 'latitude' => 36.1780, 'longitude' => 44.0050],
            ['area_name_en' => 'Gulan', 'area_name_ar' => 'كولان', 'area_name_ku' => 'گوڵان', 'latitude' => 36.2050, 'longitude' => 44.0250],
            ['area_name_en' => 'Iskan', 'area_name_ar' => 'إسكان', 'area_name_ku' => 'ئەسکان', 'latitude' => 36.1700, 'longitude' => 43.9950],
            ['area_name_en' => 'Ronaki', 'area_name_ar' => 'رونكي', 'area_name_ku' => 'ڕۆناکی', 'latitude' => 36.1820, 'longitude' => 44.0120],
            ['area_name_en' => 'Badawa', 'area_name_ar' => 'بداوة', 'area_name_ku' => 'باداوە', 'latitude' => 36.1720, 'longitude' => 44.0380],
            ['area_name_en' => 'Pirmam', 'area_name_ar' => 'برمم', 'area_name_ku' => 'پیرمام', 'latitude' => 36.1500, 'longitude' => 44.0800],
            ['area_name_en' => 'Kasnazan', 'area_name_ar' => 'كسنزان', 'area_name_ku' => 'کەسنەزان', 'latitude' => 36.1250, 'longitude' => 44.0600],
            ['area_name_en' => 'Masif Salahaddin', 'area_name_ar' => 'مسيف صلاح الدين', 'area_name_ku' => 'ماسیفی سەلاحەدین', 'latitude' => 36.1780, 'longitude' => 44.0300],
            ['area_name_en' => 'Koya Road', 'area_name_ar' => 'طريق كويه', 'area_name_ku' => 'ڕێگای کۆیە', 'latitude' => 36.1950, 'longitude' => 44.0500],
            ['area_name_en' => 'Mamostayan', 'area_name_ar' => 'ماموستايان', 'area_name_ku' => 'مامۆستایان', 'latitude' => 36.1830, 'longitude' => 44.0200],
            ['area_name_en' => 'Shekh Ahmed', 'area_name_ar' => 'الشيخ أحمد', 'area_name_ku' => 'شێخ ئەحمەد', 'latitude' => 36.1600, 'longitude' => 44.0100],
            ['area_name_en' => 'Rwanga', 'area_name_ar' => 'روانكة', 'area_name_ku' => 'ڕوانگە', 'latitude' => 36.1650, 'longitude' => 44.0150],
            ['area_name_en' => 'Zanko', 'area_name_ar' => 'زانكو', 'area_name_ku' => 'زانکۆ', 'latitude' => 36.1730, 'longitude' => 44.0220],
            ['area_name_en' => 'Xanzad', 'area_name_ar' => 'خانزاد', 'area_name_ku' => 'خانزاد', 'latitude' => 36.1800, 'longitude' => 44.0180],
            ['area_name_en' => 'Kurdistan', 'area_name_ar' => 'كردستان', 'area_name_ku' => 'کوردستان', 'latitude' => 36.1850, 'longitude' => 44.0230],
            ['area_name_en' => 'Kirkuk Road', 'area_name_ar' => 'طريق كركوك', 'area_name_ku' => 'ڕێگای کەرکووک', 'latitude' => 36.1650, 'longitude' => 44.0500],
            ['area_name_en' => 'Mosul Road', 'area_name_ar' => 'طريق الموصل', 'area_name_ku' => 'ڕێگای مووسڵ', 'latitude' => 36.2200, 'longitude' => 43.9800],
            ['area_name_en' => 'Rozhalat', 'area_name_ar' => 'روژالات', 'area_name_ku' => 'ڕۆژھەڵات', 'latitude' => 36.1950, 'longitude' => 44.0350],
            ['area_name_en' => 'Farmanbaran', 'area_name_ar' => 'فرمانبران', 'area_name_ku' => 'فەرمانبەران', 'latitude' => 36.1880, 'longitude' => 44.0120],
            ['area_name_en' => 'Tairawa', 'area_name_ar' => 'تايراوة', 'area_name_ku' => 'تایراوا', 'latitude' => 36.1920, 'longitude' => 44.0280],
            ['area_name_en' => 'New Erbil', 'area_name_ar' => 'أربيل الجديدة', 'area_name_ku' => 'هەولێری نوێ', 'latitude' => 36.2150, 'longitude' => 44.0600],
        ];

        foreach ($erbilAreas as $area) {
            Area::create(array_merge($area, ['branch_id' => $erbil->id, 'is_active' => true]));
        }

        // Soran District
        $soran = Branch::create([
            'city_name_en' => 'Soran',
            'city_name_ar' => 'سوران',
            'city_name_ku' => 'سۆران',
            'latitude' => 36.6544,
            'longitude' => 44.5456,
            'is_active' => true
        ]);

        $soranAreas = [
            ['area_name_en' => 'City Center', 'area_name_ar' => 'وسط المدينة', 'area_name_ku' => 'ناوەندی شاری', 'latitude' => 36.6544, 'longitude' => 44.5456],
            ['area_name_en' => 'Diana', 'area_name_ar' => 'ديانا', 'area_name_ku' => 'دیانا', 'latitude' => 36.7022, 'longitude' => 44.5956],
            ['area_name_en' => 'Khalifan', 'area_name_ar' => 'خليفان', 'area_name_ku' => 'خەلیفان', 'latitude' => 36.6800, 'longitude' => 44.5600],
            ['area_name_en' => 'Harir', 'area_name_ar' => 'حرير', 'area_name_ku' => 'حەریر', 'latitude' => 36.5500, 'longitude' => 44.5000],
            ['area_name_en' => 'Rawanduz Road', 'area_name_ar' => 'طريق رواندوز', 'area_name_ku' => 'ڕێگای ڕەواندوز', 'latitude' => 36.6700, 'longitude' => 44.5700],
        ];

        foreach ($soranAreas as $area) {
            Area::create(array_merge($area, ['branch_id' => $soran->id, 'is_active' => true]));
        }

        // Koya
        $koya = Branch::create([
            'city_name_en' => 'Koya',
            'city_name_ar' => 'كويا',
            'city_name_ku' => 'کۆیە',
            'latitude' => 36.0853,
            'longitude' => 44.6289,
            'is_active' => true
        ]);

        $koyaAreas = [
            ['area_name_en' => 'City Center', 'area_name_ar' => 'وسط المدينة', 'area_name_ku' => 'ناوەندی شاری', 'latitude' => 36.0853, 'longitude' => 44.6289],
            ['area_name_en' => 'Grda Shin', 'area_name_ar' => 'گردا شين', 'area_name_ku' => 'گردا شین', 'latitude' => 36.0900, 'longitude' => 44.6350],
            ['area_name_en' => 'Qularaisi', 'area_name_ar' => 'قلعة رايسي', 'area_name_ku' => 'قوڵاڕەيسی', 'latitude' => 36.0800, 'longitude' => 44.6200],
            ['area_name_en' => 'New Koya', 'area_name_ar' => 'كويا الجديدة', 'area_name_ku' => 'کۆیەی نوێ', 'latitude' => 36.0950, 'longitude' => 44.6400],
        ];

        foreach ($koyaAreas as $area) {
            Area::create(array_merge($area, ['branch_id' => $koya->id, 'is_active' => true]));
        }

        // Shaqlawa
        $shaqlawa = Branch::create([
            'city_name_en' => 'Shaqlawa',
            'city_name_ar' => 'شقلاوه',
            'city_name_ku' => 'شەقڵاوە',
            'latitude' => 36.4057,
            'longitude' => 44.3232,
            'is_active' => true
        ]);

        $shaqlawaAreas = [
            ['area_name_en' => 'City Center', 'area_name_ar' => 'وسط المدينة', 'area_name_ku' => 'ناوەندی شاری', 'latitude' => 36.4057, 'longitude' => 44.3232],
            ['area_name_en' => 'Batas', 'area_name_ar' => 'باتاس', 'area_name_ku' => 'باتاس', 'latitude' => 36.4100, 'longitude' => 44.3300],
            ['area_name_en' => 'Salahadin Resort', 'area_name_ar' => 'منتجع صلاح الدين', 'area_name_ku' => 'گەشتیاری سەلاحەدین', 'latitude' => 36.4200, 'longitude' => 44.3400],
            ['area_name_en' => 'Haibat Sultan', 'area_name_ar' => 'هيبة سلطان', 'area_name_ku' => 'ھەيبات سوڵتان', 'latitude' => 36.4000, 'longitude' => 44.3150],
        ];

        foreach ($shaqlawaAreas as $area) {
            Area::create(array_merge($area, ['branch_id' => $shaqlawa->id, 'is_active' => true]));
        }

        // Rawanduz
        $rawanduz = Branch::create([
            'city_name_en' => 'Rawanduz',
            'city_name_ar' => 'رواندوز',
            'city_name_ku' => 'ڕەواندوز',
            'latitude' => 36.6142,
            'longitude' => 44.5247,
            'is_active' => true
        ]);

        $rawanduzAreas = [
            ['area_name_en' => 'City Center', 'area_name_ar' => 'وسط المدينة', 'area_name_ku' => 'ناوەندی شاری', 'latitude' => 36.6142, 'longitude' => 44.5247],
            ['area_name_en' => 'Spi', 'area_name_ar' => 'سبي', 'area_name_ku' => 'سپی', 'latitude' => 36.6200, 'longitude' => 44.5300],
            ['area_name_en' => 'Bekhal', 'area_name_ar' => 'بيكهال', 'area_name_ku' => 'بێخاڵ', 'latitude' => 36.6500, 'longitude' => 44.5500],
            ['area_name_en' => 'Gali Ali Beg', 'area_name_ar' => 'كلي علي بك', 'area_name_ku' => 'گەڵی عەلی بەگ', 'latitude' => 36.6000, 'longitude' => 44.5100],
        ];

        foreach ($rawanduzAreas as $area) {
            Area::create(array_merge($area, ['branch_id' => $rawanduz->id, 'is_active' => true]));
        }

        // Makhmur
        $makhmur = Branch::create([
            'city_name_en' => 'Makhmur',
            'city_name_ar' => 'مخمور',
            'city_name_ku' => 'مەخموور',
            'latitude' => 35.7833,
            'longitude' => 43.5833,
            'is_active' => true
        ]);

        $makhmurAreas = [
            ['area_name_en' => 'City Center', 'area_name_ar' => 'وسط المدينة', 'area_name_ku' => 'ناوەندی شاری', 'latitude' => 35.7833, 'longitude' => 43.5833],
            ['area_name_en' => 'New Makhmur', 'area_name_ar' => 'مخمور الجديدة', 'area_name_ku' => 'مەخمووری نوێ', 'latitude' => 35.7900, 'longitude' => 43.5900],
            ['area_name_en' => 'Debaga', 'area_name_ar' => 'ديبكة', 'area_name_ku' => 'دێبەگە', 'latitude' => 35.8000, 'longitude' => 43.6000],
        ];

        foreach ($makhmurAreas as $area) {
            Area::create(array_merge($area, ['branch_id' => $makhmur->id, 'is_active' => true]));
        }

        $this->command->info("✅ Erbil Province: 7 cities created");

        // ==================== SULAYMANIYAH PROVINCE ====================

        $sulaymaniyah = Branch::create([
            'city_name_en' => 'Sulaymaniyah',
            'city_name_ar' => 'السليمانية',
            'city_name_ku' => 'سلێمانی',
            'latitude' => 35.5567,
            'longitude' => 45.4329,
            'is_active' => true
        ]);

        $sulaymaniyahAreas = [
            // Central Areas
            ['area_name_en' => 'Saray', 'area_name_ar' => 'سراي', 'area_name_ku' => 'سەرای', 'latitude' => 35.5550, 'longitude' => 45.4320],
            ['area_name_en' => 'Saholaka', 'area_name_ar' => 'سهولكة', 'area_name_ku' => 'سه‌وه‌ڵەکه‌', 'latitude' => 35.5580, 'longitude' => 45.4350],
            ['area_name_en' => 'Salim', 'area_name_ar' => 'سليم', 'area_name_ku' => 'سەلیم', 'latitude' => 35.5540, 'longitude' => 45.4300],

            // Modern Districts
            ['area_name_en' => 'Bakhtiyary', 'area_name_ar' => 'بختياري', 'area_name_ku' => 'بەختیاری', 'latitude' => 35.5550, 'longitude' => 45.4300],
            ['area_name_en' => 'Malik Mahmud', 'area_name_ar' => 'ملك محمود', 'area_name_ku' => 'مالیک محەممەد', 'latitude' => 35.5600, 'longitude' => 45.4400],
            ['area_name_en' => 'Sara', 'area_name_ar' => 'سارة', 'area_name_ku' => 'سارا', 'latitude' => 35.5520, 'longitude' => 45.4350],
            ['area_name_en' => 'Raparin', 'area_name_ar' => 'ربارين', 'area_name_ku' => 'ڕەپەڕین', 'latitude' => 35.5580, 'longitude' => 45.4380],
            ['area_name_en' => 'Qularaisi', 'area_name_ar' => 'قلعة رايسي', 'area_name_ku' => 'قوڵاڕەيسی', 'latitude' => 35.5540, 'longitude' => 45.4280],
            ['area_name_en' => 'Sarchnar', 'area_name_ar' => 'سارشنار', 'area_name_ku' => 'سەرچنار', 'latitude' => 35.5620, 'longitude' => 45.4450],
            ['area_name_en' => 'Kani Qrzhala', 'area_name_ar' => 'كاني قرضالا', 'area_name_ku' => 'کانی قوڕژاڵە', 'latitude' => 35.5480, 'longitude' => 45.4200],
            ['area_name_en' => 'Azadi', 'area_name_ar' => 'آزادي', 'area_name_ku' => 'ئازادی', 'latitude' => 35.5590, 'longitude' => 45.4420],
            ['area_name_en' => 'Kurdistan', 'area_name_ar' => 'كردستان', 'area_name_ku' => 'کوردستان', 'latitude' => 35.5560, 'longitude' => 45.4340],
            ['area_name_en' => 'Xabatnezar', 'area_name_ar' => 'خبات نزار', 'area_name_ku' => 'خەباتنەزار', 'latitude' => 35.5500, 'longitude' => 45.4250],
            ['area_name_en' => 'Kani Ashqanan', 'area_name_ar' => 'كاني عشقنان', 'area_name_ku' => 'کانی عەشقەنان', 'latitude' => 35.5650, 'longitude' => 45.4500],
            ['area_name_en' => 'Sewe Qeran', 'area_name_ar' => 'سوة قران', 'area_name_ku' => 'سەوە قەڕان', 'latitude' => 35.5480, 'longitude' => 45.4180],
            ['area_name_en' => 'Nazanin City', 'area_name_ar' => 'مدينة نازانين', 'area_name_ku' => 'شاری نازەنین', 'latitude' => 35.5700, 'longitude' => 45.4550],
            ['area_name_en' => 'Mamle', 'area_name_ar' => 'مامله', 'area_name_ku' => 'مامڵە', 'latitude' => 35.5450, 'longitude' => 45.4150],
            ['area_name_en' => 'Sabunkaran', 'area_name_ar' => 'صابونكاران', 'area_name_ku' => 'سابوونکاران', 'latitude' => 35.5530, 'longitude' => 45.4310],
            ['area_name_en' => 'Newshirwan', 'area_name_ar' => 'نيوشروان', 'area_name_ku' => 'نێوشیروان', 'latitude' => 35.5600, 'longitude' => 45.4450],
            ['area_name_en' => 'Shexan', 'area_name_ar' => 'شيكان', 'area_name_ku' => 'شێخان', 'latitude' => 35.5520, 'longitude' => 45.4280],
            ['area_name_en' => 'Kany Awa', 'area_name_ar' => 'كاني عوا', 'area_name_ku' => 'کانی عەوا', 'latitude' => 35.5680, 'longitude' => 45.4520],
            ['area_name_en' => 'Xaneqin Road', 'area_name_ar' => 'طريق خانقين', 'area_name_ku' => 'ڕێگای خانەقین', 'latitude' => 35.5400, 'longitude' => 45.4600],
            ['area_name_en' => 'Kirkuk Road', 'area_name_ar' => 'طريق كركوك', 'area_name_ku' => 'ڕێگای کەرکووک', 'latitude' => 35.5300, 'longitude' => 45.4100],
            ['area_name_en' => 'Piramagrun', 'area_name_ar' => 'بيرمكرون', 'area_name_ku' => 'پیرەمەگرون', 'latitude' => 35.5750, 'longitude' => 45.4600],
            ['area_name_en' => 'Shahidan', 'area_name_ar' => 'شهدان', 'area_name_ku' => 'شەهیدان', 'latitude' => 35.5620, 'longitude' => 45.4380],
            ['area_name_en' => 'Tanjaro', 'area_name_ar' => 'تنجارو', 'area_name_ku' => 'تانجارۆ', 'latitude' => 35.5500, 'longitude' => 45.4450],
        ];

        foreach ($sulaymaniyahAreas as $area) {
            Area::create(array_merge($area, ['branch_id' => $sulaymaniyah->id, 'is_active' => true]));
        }

        // Halabja
        $halabja = Branch::create([
            'city_name_en' => 'Halabja',
            'city_name_ar' => 'حلبجة',
            'city_name_ku' => 'هەڵەبجە',
            'latitude' => 35.1772,
            'longitude' => 45.9856,
            'is_active' => true
        ]);

        $halabjaAreas = [
            ['area_name_en' => 'City Center', 'area_name_ar' => 'وسط المدينة', 'area_name_ku' => 'ناوەندی شاری', 'latitude' => 35.1772, 'longitude' => 45.9856],
            ['area_name_en' => 'New Halabja', 'area_name_ar' => 'حلبجة الجديدة', 'area_name_ku' => 'هەڵەبجەی نوێ', 'latitude' => 35.1850, 'longitude' => 45.9950],
            ['area_name_en' => 'Xurmal', 'area_name_ar' => 'خرمال', 'area_name_ku' => 'خورماڵ', 'latitude' => 35.1650, 'longitude' => 45.9700],
            ['area_name_en' => 'Biara', 'area_name_ar' => 'بيارة', 'area_name_ku' => 'بیارە', 'latitude' => 35.1900, 'longitude' => 46.0000],
            ['area_name_en' => 'Sirwan', 'area_name_ar' => 'سروان', 'area_name_ku' => 'سیروان', 'latitude' => 35.1700, 'longitude' => 45.9800],
        ];

        foreach ($halabjaAreas as $area) {
            Area::create(array_merge($area, ['branch_id' => $halabja->id, 'is_active' => true]));
        }

        // Ranya
        $ranya = Branch::create([
            'city_name_en' => 'Ranya',
            'city_name_ar' => 'رانية',
            'city_name_ku' => 'ڕانیە',
            'latitude' => 36.2633,
            'longitude' => 44.8894,
            'is_active' => true
        ]);

        $ranyaAreas = [
            ['area_name_en' => 'City Center', 'area_name_ar' => 'وسط المدينة', 'area_name_ku' => 'ناوەندی شاری', 'latitude' => 36.2633, 'longitude' => 44.8894],
            ['area_name_en' => 'Dukan', 'area_name_ar' => 'دوكان', 'area_name_ku' => 'دووکان', 'latitude' => 36.0833, 'longitude' => 44.9667],
            ['area_name_en' => 'Chwarqurna', 'area_name_ar' => 'جوارقورنة', 'area_name_ku' => 'چوارقوڕنە', 'latitude' => 36.2800, 'longitude' => 44.9000],
            ['area_name_en' => 'Betwata', 'area_name_ar' => 'بتواتا', 'area_name_ku' => 'بەتواتە', 'latitude' => 36.2500, 'longitude' => 44.8700],
        ];

        foreach ($ranyaAreas as $area) {
            Area::create(array_merge($area, ['branch_id' => $ranya->id, 'is_active' => true]));
        }

        // Qaladze
        $qaladze = Branch::create([
            'city_name_en' => 'Qaladze',
            'city_name_ar' => 'قلادزة',
            'city_name_ku' => 'قەڵادزێ',
            'latitude' => 36.1333,
            'longitude' => 45.0667,
            'is_active' => true
        ]);

        $qaladzeAreas = [
            ['area_name_en' => 'City Center', 'area_name_ar' => 'وسط المدينة', 'area_name_ku' => 'ناوەندی شاری', 'latitude' => 36.1333, 'longitude' => 45.0667],
            ['area_name_en' => 'Hero Town', 'area_name_ar' => 'هيرو تاون', 'area_name_ku' => 'شارۆچکەی هیرۆ', 'latitude' => 36.1400, 'longitude' => 45.0750],
            ['area_name_en' => 'New Qaladze', 'area_name_ar' => 'قلادزة الجديدة', 'area_name_ku' => 'قەڵادزێی نوێ', 'latitude' => 36.1450, 'longitude' => 45.0800],
        ];

        foreach ($qaladzeAreas as $area) {
            Area::create(array_merge($area, ['branch_id' => $qaladze->id, 'is_active' => true]));
        }

        // Penjwin
        $penjwin = Branch::create([
            'city_name_en' => 'Penjwin',
            'city_name_ar' => 'بنجوين',
            'city_name_ku' => 'پێنجوێن',
            'latitude' => 35.6100,
            'longitude' => 45.9550,
            'is_active' => true
        ]);

        $penjwinAreas = [
            ['area_name_en' => 'City Center', 'area_name_ar' => 'وسط المدينة', 'area_name_ku' => 'ناوەندی شاری', 'latitude' => 35.6100, 'longitude' => 45.9550],
            ['area_name_en' => 'Nalparez', 'area_name_ar' => 'نالبارز', 'area_name_ku' => 'ناڵپارێز', 'latitude' => 35.6200, 'longitude' => 45.9650],
            ['area_name_en' => 'Sharazur', 'area_name_ar' => 'شرازور', 'area_name_ku' => 'شارەزوور', 'latitude' => 35.6000, 'longitude' => 45.9450],
        ];

        foreach ($penjwinAreas as $area) {
            Area::create(array_merge($area, ['branch_id' => $penjwin->id, 'is_active' => true]));
        }

        // Chamchamal
        $chamchamal = Branch::create([
            'city_name_en' => 'Chamchamal',
            'city_name_ar' => 'جمجمال',
            'city_name_ku' => 'چەمچەماڵ',
            'latitude' => 35.5167,
            'longitude' => 44.8333,
            'is_active' => true
        ]);

        $chamchamalAreas = [
            ['area_name_en' => 'City Center', 'area_name_ar' => 'وسط المدينة', 'area_name_ku' => 'ناوەندی شاری', 'latitude' => 35.5167, 'longitude' => 44.8333],
            ['area_name_en' => 'Bazian', 'area_name_ar' => 'بازيان', 'area_name_ku' => 'بازیان', 'latitude' => 35.5300, 'longitude' => 44.8500],
            ['area_name_en' => 'Qamaran', 'area_name_ar' => 'قمران', 'area_name_ku' => 'قاماران', 'latitude' => 35.5100, 'longitude' => 44.8200],
        ];

        foreach ($chamchamalAreas as $area) {
            Area::create(array_merge($area, ['branch_id' => $chamchamal->id, 'is_active' => true]));
        }

        $this->command->info("✅ Sulaymaniyah Province: 7 cities created");

        // ==================== DUHOK PROVINCE ====================

        $duhok = Branch::create([
            'city_name_en' => 'Duhok',
            'city_name_ar' => 'دهوك',
            'city_name_ku' => 'دهۆک',
            'latitude' => 36.8677,
            'longitude' => 42.9913,
            'is_active' => true
        ]);

        $duhokAreas = [
            // Central Areas
            ['area_name_en' => 'City Center', 'area_name_ar' => 'وسط المدينة', 'area_name_ku' => 'ناوەندی شاری', 'latitude' => 36.8677, 'longitude' => 42.9913],
            ['area_name_en' => 'Azadi', 'area_name_ar' => 'آزادي', 'area_name_ku' => 'ئازادی', 'latitude' => 36.8700, 'longitude' => 42.9950],
            ['area_name_en' => 'Nisibin', 'area_name_ar' => 'نسيبين', 'area_name_ku' => 'نسیبین', 'latitude' => 36.8650, 'longitude' => 42.9900],
            ['area_name_en' => 'Mazi', 'area_name_ar' => 'مازي', 'area_name_ku' => 'مازی', 'latitude' => 36.8720, 'longitude' => 42.9980],
            ['area_name_en' => 'Khanzad', 'area_name_ar' => 'خانزاد', 'area_name_ku' => 'خانزاد', 'latitude' => 36.8740, 'longitude' => 43.0000],
            ['area_name_en' => 'Shindokha', 'area_name_ar' => 'شندوخة', 'area_name_ku' => 'شەندۆخە', 'latitude' => 36.8750, 'longitude' => 43.0010],
            ['area_name_en' => 'Domiz', 'area_name_ar' => 'دوميز', 'area_name_ku' => 'دۆمێز', 'latitude' => 36.8600, 'longitude' => 42.9850],
            ['area_name_en' => 'Baroshke', 'area_name_ar' => 'باروشكي', 'area_name_ku' => 'بارۆشکە', 'latitude' => 36.8800, 'longitude' => 43.0100],
            ['area_name_en' => 'Bamarni', 'area_name_ar' => 'بامرني', 'area_name_ku' => 'بامەرنی', 'latitude' => 36.8550, 'longitude' => 42.9800],
            ['area_name_en' => 'Summel', 'area_name_ar' => 'سميل', 'area_name_ku' => 'سومێل', 'latitude' => 36.8900, 'longitude' => 43.0200],
            ['area_name_en' => 'Dawadiya', 'area_name_ar' => 'دواديا', 'area_name_ku' => 'داودیا', 'latitude' => 36.8620, 'longitude' => 42.9880],
            ['area_name_en' => 'Mahabad', 'area_name_ar' => 'مهاباد', 'area_name_ku' => 'مەھاباد', 'latitude' => 36.8680, 'longitude' => 42.9920],
            ['area_name_en' => 'Girdi Zewa', 'area_name_ar' => 'گردي زوا', 'area_name_ku' => 'گردی زێوا', 'latitude' => 36.8750, 'longitude' => 43.0050],
        ];

        foreach ($duhokAreas as $area) {
            Area::create(array_merge($area, ['branch_id' => $duhok->id, 'is_active' => true]));
        }

        // Zakho
        $zakho = Branch::create([
            'city_name_en' => 'Zakho',
            'city_name_ar' => 'زاخو',
            'city_name_ku' => 'زاخۆ',
            'latitude' => 37.1448,
            'longitude' => 42.6827,
            'is_active' => true
        ]);

        $zakhoAreas = [
            ['area_name_en' => 'City Center', 'area_name_ar' => 'وسط المدينة', 'area_name_ku' => 'ناوەندی شاری', 'latitude' => 37.1448, 'longitude' => 42.6827],
            ['area_name_en' => 'Khabur Bridge', 'area_name_ar' => 'جسر الخابور', 'area_name_ku' => 'پۆلی خابوور', 'latitude' => 37.1500, 'longitude' => 42.6900],
            ['area_name_en' => 'Duhok Road', 'area_name_ar' => 'طريق دهوك', 'area_name_ku' => 'ڕێگای دهۆک', 'latitude' => 37.1350, 'longitude' => 42.6750],
            ['area_name_en' => 'Simel', 'area_name_ar' => 'سميل', 'area_name_ku' => 'سیمێل', 'latitude' => 37.1600, 'longitude' => 42.7000],
            ['area_name_en' => 'Feshkhabur', 'area_name_ar' => 'فشخابور', 'area_name_ku' => 'فێشخابوور', 'latitude' => 37.1800, 'longitude' => 42.7200],
        ];

        foreach ($zakhoAreas as $area) {
            Area::create(array_merge($area, ['branch_id' => $zakho->id, 'is_active' => true]));
        }

        // Amedi
        $amedi = Branch::create([
            'city_name_en' => 'Amedi',
            'city_name_ar' => 'عمادية',
            'city_name_ku' => 'ئامێدی',
            'latitude' => 37.0894,
            'longitude' => 43.4903,
            'is_active' => true
        ]);

        $amediAreas = [
            ['area_name_en' => 'City Center', 'area_name_ar' => 'وسط المدينة', 'area_name_ku' => 'ناوەندی شاری', 'latitude' => 37.0894, 'longitude' => 43.4903],
            ['area_name_en' => 'Sersing', 'area_name_ar' => 'سرسينك', 'area_name_ku' => 'سەرسینگ', 'latitude' => 37.1000, 'longitude' => 43.5000],
            ['area_name_en' => 'Bamarni', 'area_name_ar' => 'بامرني', 'area_name_ku' => 'بامەرنی', 'latitude' => 37.0800, 'longitude' => 43.4800],
            ['area_name_en' => 'Duhok Road', 'area_name_ar' => 'طريق دهوك', 'area_name_ku' => 'ڕێگای دهۆک', 'latitude' => 37.0750, 'longitude' => 43.4750],
        ];

        foreach ($amediAreas as $area) {
            Area::create(array_merge($area, ['branch_id' => $amedi->id, 'is_active' => true]));
        }

        // Akre
        $akre = Branch::create([
            'city_name_en' => 'Akre',
            'city_name_ar' => 'عقرة',
            'city_name_ku' => 'عەقرە',
            'latitude' => 36.7333,
            'longitude' => 43.8833,
            'is_active' => true
        ]);

        $akreAreas = [
            ['area_name_en' => 'City Center', 'area_name_ar' => 'وسط المدينة', 'area_name_ku' => 'ناوەندی شاری', 'latitude' => 36.7333, 'longitude' => 43.8833],
            ['area_name_en' => 'Upper Akre', 'area_name_ar' => 'عقرة العليا', 'area_name_ku' => 'عەقرەی سەروو', 'latitude' => 36.7400, 'longitude' => 43.8900],
            ['area_name_en' => 'Lower Akre', 'area_name_ar' => 'عقرة السفلى', 'area_name_ku' => 'عەقرەی خواروو', 'latitude' => 36.7250, 'longitude' => 43.8750],
            ['area_name_en' => 'Gara Mountain', 'area_name_ar' => 'جبل كارة', 'area_name_ku' => 'شاخی گارە', 'latitude' => 36.7500, 'longitude' => 43.9000],
        ];

        foreach ($akreAreas as $area) {
            Area::create(array_merge($area, ['branch_id' => $akre->id, 'is_active' => true]));
        }

        // Semel
        $semel = Branch::create([
            'city_name_en' => 'Semel',
            'city_name_ar' => 'سميل',
            'city_name_ku' => 'سیمێل',
            'latitude' => 36.9333,
            'longitude' => 42.9500,
            'is_active' => true
        ]);

        $semelAreas = [
            ['area_name_en' => 'City Center', 'area_name_ar' => 'وسط المدينة', 'area_name_ku' => 'ناوەندی شاری', 'latitude' => 36.9333, 'longitude' => 42.9500],
            ['area_name_en' => 'New Semel', 'area_name_ar' => 'سميل الجديدة', 'area_name_ku' => 'سیمێلی نوێ', 'latitude' => 36.9400, 'longitude' => 42.9600],
        ];

        foreach ($semelAreas as $area) {
            Area::create(array_merge($area, ['branch_id' => $semel->id, 'is_active' => true]));
        }

        // Zawita
        $zawita = Branch::create([
            'city_name_en' => 'Zawita',
            'city_name_ar' => 'زاويتا',
            'city_name_ku' => 'زاویتە',
            'latitude' => 37.0000,
            'longitude' => 43.1167,
            'is_active' => true
        ]);

        $zawitaAreas = [
            ['area_name_en' => 'City Center', 'area_name_ar' => 'وسط المدينة', 'area_name_ku' => 'ناوەندی شاری', 'latitude' => 37.0000, 'longitude' => 43.1167],
            ['area_name_en' => 'Batas', 'area_name_ar' => 'باتاس', 'area_name_ku' => 'باتاس', 'latitude' => 37.0100, 'longitude' => 43.1250],
            ['area_name_en' => 'Sharya', 'area_name_ar' => 'شاريا', 'area_name_ku' => 'شاریا', 'latitude' => 36.9900, 'longitude' => 43.1050],
        ];

        foreach ($zawitaAreas as $area) {
            Area::create(array_merge($area, ['branch_id' => $zawita->id, 'is_active' => true]));
        }

        $this->command->info("✅ Duhok Province: 6 cities created");

        // ==================== KIRKUK (Disputed Territory) ====================

        $kirkuk = Branch::create([
            'city_name_en' => 'Kirkuk',
            'city_name_ar' => 'كركوك',
            'city_name_ku' => 'کەرکووک',
            'latitude' => 35.4681,
            'longitude' => 44.3922,
            'is_active' => true
        ]);

        $kirkukAreas = [
            ['area_name_en' => 'City Center', 'area_name_ar' => 'وسط المدينة', 'area_name_ku' => 'ناوەندی شاری', 'latitude' => 35.4681, 'longitude' => 44.3922],
            ['area_name_en' => 'Arafa', 'area_name_ar' => 'عرفة', 'area_name_ku' => 'عەرەفە', 'latitude' => 35.4700, 'longitude' => 44.3950],
            ['area_name_en' => 'Azadi', 'area_name_ar' => 'آزادي', 'area_name_ku' => 'ئازادی', 'latitude' => 35.4650, 'longitude' => 44.3900],
            ['area_name_en' => 'Shorja', 'area_name_ar' => 'شورجة', 'area_name_ku' => 'شۆرجە', 'latitude' => 35.4680, 'longitude' => 44.3920],
            ['area_name_en' => 'Rahimawa', 'area_name_ar' => 'رحيماوة', 'area_name_ku' => 'ڕەحیماوە', 'latitude' => 35.4720, 'longitude' => 44.3980],
            ['area_name_en' => 'Iskan', 'area_name_ar' => 'إسكان', 'area_name_ku' => 'ئەسکان', 'latitude' => 35.4600, 'longitude' => 44.3850],
            ['area_name_en' => 'Kornish', 'area_name_ar' => 'كورنيش', 'area_name_ku' => 'کۆرنیش', 'latitude' => 35.4750, 'longitude' => 44.4000],
            ['area_name_en' => 'Dumiz', 'area_name_ar' => 'دوميز', 'area_name_ku' => 'دۆمێز', 'latitude' => 35.4620, 'longitude' => 44.3870],
            ['area_name_en' => 'Imam Qasim', 'area_name_ar' => 'الإمام قاسم', 'area_name_ku' => 'ئیمام قاسم', 'latitude' => 35.4690, 'longitude' => 44.3940],
            ['area_name_en' => 'Qorya', 'area_name_ar' => 'قورية', 'area_name_ku' => 'قۆریە', 'latitude' => 35.4640, 'longitude' => 44.3880],
        ];

        foreach ($kirkukAreas as $area) {
            Area::create(array_merge($area, ['branch_id' => $kirkuk->id, 'is_active' => true]));
        }

        $this->command->info("✅ Kirkuk: 1 city created");

        // ==================== GARMIAN ADMINISTRATION ====================

        $kalar = Branch::create([
            'city_name_en' => 'Kalar',
            'city_name_ar' => 'كلار',
            'city_name_ku' => 'کەلار',
            'latitude' => 34.6267,
            'longitude' => 45.3197,
            'is_active' => true
        ]);

        $kalarAreas = [
            ['area_name_en' => 'City Center', 'area_name_ar' => 'وسط المدينة', 'area_name_ku' => 'ناوەندی شاری', 'latitude' => 34.6267, 'longitude' => 45.3197],
            ['area_name_en' => 'New Kalar', 'area_name_ar' => 'كلار الجديدة', 'area_name_ku' => 'کەلاری نوێ', 'latitude' => 34.6350, 'longitude' => 45.3300],
            ['area_name_en' => 'Garmiyan Street', 'area_name_ar' => 'شارع كرميان', 'area_name_ku' => 'شەقامی گەرمیان', 'latitude' => 34.6200, 'longitude' => 45.3100],
        ];

        foreach ($kalarAreas as $area) {
            Area::create(array_merge($area, ['branch_id' => $kalar->id, 'is_active' => true]));
        }

        // Kifri
        $kifri = Branch::create([
            'city_name_en' => 'Kifri',
            'city_name_ar' => 'كفري',
            'city_name_ku' => 'کفری',
            'latitude' => 34.6914,
            'longitude' => 44.9642,
            'is_active' => true
        ]);

        $kifriAreas = [
            ['area_name_en' => 'City Center', 'area_name_ar' => 'وسط المدينة', 'area_name_ku' => 'ناوەندی شاری', 'latitude' => 34.6914, 'longitude' => 44.9642],
            ['area_name_en' => 'New Kifri', 'area_name_ar' => 'كفري الجديدة', 'area_name_ku' => 'کفریی نوێ', 'latitude' => 34.7000, 'longitude' => 44.9750],
            ['area_name_en' => 'Old Town', 'area_name_ar' => 'البلدة القديمة', 'area_name_ku' => 'شاری کۆن', 'latitude' => 34.6850, 'longitude' => 44.9550],
        ];

        foreach ($kifriAreas as $area) {
            Area::create(array_merge($area, ['branch_id' => $kifri->id, 'is_active' => true]));
        }

        $this->command->info("✅ Garmian Administration: 2 cities created");

        // ==================== TOTAL STATISTICS ====================

        $totalBranches = Branch::count();
        $totalAreas = Area::count();

        $this->command->table(
            ['Province', 'Cities', 'Total Areas'],
            [
                ['Erbil Province', '7 cities', Area::whereIn('branch_id', [$erbil->id, $soran->id, $koya->id, $shaqlawa->id, $rawanduz->id, $makhmur->id])->count() . ' areas'],
                ['Sulaymaniyah Province', '7 cities', Area::whereIn('branch_id', [$sulaymaniyah->id, $halabja->id, $ranya->id, $qaladze->id, $penjwin->id, $chamchamal->id])->count() . ' areas'],
                ['Duhok Province', '6 cities', Area::whereIn('branch_id', [$duhok->id, $zakho->id, $amedi->id, $akre->id, $semel->id, $zawita->id])->count() . ' areas'],
                ['Kirkuk (Disputed)', '1 city', Area::where('branch_id', $kirkuk->id)->count() . ' areas'],
                ['Garmian Admin', '2 cities', Area::whereIn('branch_id', [$kalar->id, $kifri->id])->count() . ' areas'],
            ]
        );

        $this->command->info("📊 Total: {$totalBranches} cities and {$totalAreas} areas seeded successfully!");
    }
}
