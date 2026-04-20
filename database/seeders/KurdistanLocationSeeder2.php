<?php

// database/seeders/KurdistanLocationSeeder2.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\Area;

class KurdistanLocationSeeder2 extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌍 Starting Kurdistan Location Seeder 2 — Extended Areas...');

        // ==================== SULAYMANIYAH (Extended Areas) ====================

        $sulaymaniyah = Branch::where('city_name_en', 'Sulaymaniyah')->firstOrFail();

        $sulaymaniyahAreas = [
            ['area_name_en' => 'Ali Kamal',              'area_name_ku' => 'عەلى كەمال',              'area_name_ar' => 'علي كمال',               'latitude' => 35.5560, 'longitude' => 45.4340],
            ['area_name_en' => 'Debashan',               'area_name_ku' => 'ده باشان',                'area_name_ar' => 'دباشان',                  'latitude' => 35.5530, 'longitude' => 45.4310],
            ['area_name_en' => 'Farmanbaran',            'area_name_ku' => 'فه رمانبه ران',           'area_name_ar' => 'فرمانبران',               'latitude' => 35.5600, 'longitude' => 45.4400],
            ['area_name_en' => 'Hawkari',                'area_name_ku' => 'هاوكارى',                 'area_name_ar' => 'هاوكارى',                 'latitude' => 35.5580, 'longitude' => 45.4380],
            ['area_name_en' => 'Hewari Taza',            'area_name_ku' => 'هەوارى تازە',             'area_name_ar' => 'هواري تازه',              'latitude' => 35.5620, 'longitude' => 45.4450],
            ['area_name_en' => 'Kariza Wushk',           'area_name_ku' => 'كارێزه وشك',              'area_name_ar' => 'كاريزة وشك',              'latitude' => 35.5540, 'longitude' => 45.4300],
            ['area_name_en' => 'Qazi Muhammad',          'area_name_ku' => 'قازى محەممەد',            'area_name_ar' => 'قاضي محمد',               'latitude' => 35.5500, 'longitude' => 45.4260],
            ['area_name_en' => 'Abwry Nasan',            'area_name_ku' => 'ئابوری ناسان',            'area_name_ar' => 'ابوري ناسان',             'latitude' => 35.5510, 'longitude' => 45.4270],
            ['area_name_en' => 'Ali Naje',               'area_name_ku' => 'علی ناجی',                'area_name_ar' => 'علی ناجي',                'latitude' => 35.5550, 'longitude' => 45.4330],
            ['area_name_en' => 'Andaziaran',             'area_name_ku' => 'ئەندازیاران',             'area_name_ar' => 'اندازیاران',              'latitude' => 35.5570, 'longitude' => 45.4360],
            ['area_name_en' => 'Ashti',                  'area_name_ku' => 'ئاشتی',                   'area_name_ar' => 'اشتي',                    'latitude' => 35.5490, 'longitude' => 45.4220],
            ['area_name_en' => 'Asoy Gash',              'area_name_ku' => 'ئاسۆی گەش',               'area_name_ar' => 'اسوی کش',                 'latitude' => 35.5505, 'longitude' => 45.4240],
            ['area_name_en' => 'Awal',                   'area_name_ku' => 'عەواڵ',                   'area_name_ar' => 'عوال',                    'latitude' => 35.5515, 'longitude' => 45.4255],
            ['area_name_en' => 'Azmar',                  'area_name_ku' => 'ئەزمەڕ',                  'area_name_ar' => 'ازمر',                    'latitude' => 35.5525, 'longitude' => 45.4295],
            ['area_name_en' => 'Badinan',                'area_name_ku' => 'بادینان',                 'area_name_ar' => 'بدینان',                  'latitude' => 35.5535, 'longitude' => 45.4305],
            ['area_name_en' => 'Baharan',                'area_name_ku' => 'بەهاران',                 'area_name_ar' => 'بهاران',                  'latitude' => 35.5545, 'longitude' => 45.4315],
            ['area_name_en' => 'Bahashti Shar',          'area_name_ku' => 'بەهەشتی شار',             'area_name_ar' => 'بهشتي شار',               'latitude' => 35.5555, 'longitude' => 45.4335],
            ['area_name_en' => 'Bakhan',                 'area_name_ku' => 'باخان',                   'area_name_ar' => 'باخان',                   'latitude' => 35.5565, 'longitude' => 45.4355],
            ['area_name_en' => 'Bakhtyari Kon',          'area_name_ku' => 'بەختیاری کۆن',            'area_name_ar' => 'بختیاری کون',             'latitude' => 35.5485, 'longitude' => 45.4215],
            ['area_name_en' => 'Bakhtyari Taza',         'area_name_ku' => 'بەختیاری تازە',           'area_name_ar' => 'بختیاري تازة',            'latitude' => 35.5495, 'longitude' => 45.4225],
            ['area_name_en' => 'Bakrajo',                'area_name_ku' => 'بەکرەجۆ',                 'area_name_ar' => 'بکرەجو',                  'latitude' => 35.5475, 'longitude' => 45.4195],
            ['area_name_en' => 'Bakrajoy Kon',           'area_name_ku' => 'بەکرەجۆی کۆن',            'area_name_ar' => 'بکرەجوي کون',             'latitude' => 35.5465, 'longitude' => 45.4185],
            ['area_name_en' => 'Balambo',                'area_name_ku' => 'باڵامبۆ',                  'area_name_ar' => 'بلامبو',                  'latitude' => 35.5455, 'longitude' => 45.4175],
            ['area_name_en' => 'Baranan',                'area_name_ku' => 'بەرانان',                  'area_name_ar' => 'برانان',                  'latitude' => 35.5445, 'longitude' => 45.4165],
            ['area_name_en' => 'Barez City',             'area_name_ku' => 'بەڕێز سیتی',              'area_name_ar' => 'بەریز ستی',               'latitude' => 35.5435, 'longitude' => 45.4155],
            ['area_name_en' => 'Barzaiakane Slemany',    'area_name_ku' => 'بەرزایەکانی سلێمانی',     'area_name_ar' => 'برزایةکاني سلیماني',      'latitude' => 35.5660, 'longitude' => 45.4510],
            ['area_name_en' => 'Barzaiakany Qaiwan',     'area_name_ku' => 'بەرزایەکانی قەیوان',      'area_name_ar' => 'برزایةکاني قیوان',         'latitude' => 35.5670, 'longitude' => 45.4520],
            ['area_name_en' => 'Bashoor City',           'area_name_ku' => 'باشوور سیتی',              'area_name_ar' => 'باشور ستي',               'latitude' => 35.5680, 'longitude' => 45.4530],
            ['area_name_en' => 'Bawa Mrd',               'area_name_ku' => 'باوەمرد',                  'area_name_ar' => 'باوة مرد',                'latitude' => 35.5690, 'longitude' => 45.4540],
            ['area_name_en' => 'Besarani',               'area_name_ku' => 'بێسەرانی',                 'area_name_ar' => 'بیسرانی',                 'latitude' => 35.5700, 'longitude' => 45.4550],
            ['area_name_en' => 'Chawi Slemani',          'area_name_ku' => 'چاوی سلێمانی',             'area_name_ar' => 'جاوی سلیماني',            'latitude' => 35.5710, 'longitude' => 45.4560],
            ['area_name_en' => 'Chla Zaitun',            'area_name_ku' => 'چڵە زەیتون',              'area_name_ar' => 'جلة زیتون',               'latitude' => 35.5425, 'longitude' => 45.4145],
            ['area_name_en' => 'Chwarbakh',              'area_name_ku' => 'چوارباخ',                  'area_name_ar' => 'جوارباخ',                 'latitude' => 35.5415, 'longitude' => 45.4135],
            ['area_name_en' => 'Chwarchra',              'area_name_ku' => 'چوارچرا',                  'area_name_ar' => 'جوارجرا',                 'latitude' => 35.5405, 'longitude' => 45.4125],
            ['area_name_en' => 'Chwarchray Nwe',         'area_name_ku' => 'چوارچرای نوێ',             'area_name_ar' => 'جوارجراي نوي',            'latitude' => 35.5395, 'longitude' => 45.4115],
            ['area_name_en' => 'City Towers',            'area_name_ku' => 'ستی تاوەرز',               'area_name_ar' => 'ستی تاورز',               'latitude' => 35.5720, 'longitude' => 45.4570],
            ['area_name_en' => 'Comapanyai Nawzad',      'area_name_ku' => 'کۆمپانیای نەوزاد',         'area_name_ar' => 'کومبانیاي نوزاد',         'latitude' => 35.5730, 'longitude' => 45.4580],
            ['area_name_en' => 'Dania City',             'area_name_ku' => 'دانیا ستی',                'area_name_ar' => 'دانیا ستی',               'latitude' => 35.5740, 'longitude' => 45.4590],
            ['area_name_en' => 'Darda De',               'area_name_ku' => 'دەردە دێ',                 'area_name_ar' => 'دردة دي',                 'latitude' => 35.5385, 'longitude' => 45.4105],
            ['area_name_en' => 'Dargazen',               'area_name_ku' => 'دەرگەزێن',                 'area_name_ar' => 'درکزین',                  'latitude' => 35.5375, 'longitude' => 45.4095],
            ['area_name_en' => 'Darogha',                'area_name_ku' => 'دارۆغا',                   'area_name_ar' => 'داروغا',                  'latitude' => 35.5365, 'longitude' => 45.4085],
            ['area_name_en' => 'Darw City',              'area_name_ku' => 'دەروو سیتی',               'area_name_ar' => 'درو ستي',                 'latitude' => 35.5355, 'longitude' => 45.4075],
            ['area_name_en' => 'Darwaza City',           'area_name_ku' => 'دەروازە سیتی',             'area_name_ar' => 'دروازة ستی',              'latitude' => 35.5345, 'longitude' => 45.4065],
            ['area_name_en' => 'Dilan City',             'area_name_ku' => 'دیلان سیتی',               'area_name_ar' => 'دیلان ستی',               'latitude' => 35.5750, 'longitude' => 45.4600],
            ['area_name_en' => 'Dli Shar',               'area_name_ku' => 'دڵی شار',                  'area_name_ar' => 'دلي شار',                 'latitude' => 35.5335, 'longitude' => 45.4055],
            ['area_name_en' => 'Dream Land',             'area_name_ku' => 'دریم لاند',                'area_name_ar' => 'دریم لاند',               'latitude' => 35.5760, 'longitude' => 45.4610],
            ['area_name_en' => 'Dya City',               'area_name_ku' => 'دییە سیتی',                'area_name_ar' => 'دییة ستي',                'latitude' => 35.5770, 'longitude' => 45.4620],
            ['area_name_en' => 'Dyna City',              'area_name_ku' => 'دینا سیتی',                'area_name_ar' => 'دینا ستي',                'latitude' => 35.5780, 'longitude' => 45.4630],
            ['area_name_en' => 'Farajawa',               'area_name_ku' => 'فەرەجاوا',                 'area_name_ar' => 'فرەجاوا',                 'latitude' => 35.5325, 'longitude' => 45.4045],
            ['area_name_en' => 'Gardn City',             'area_name_ku' => 'گاردن ستی',                'area_name_ar' => 'کاردن ستی',               'latitude' => 35.5790, 'longitude' => 45.4640],
            ['area_name_en' => 'Goizhay Nwe',            'area_name_ku' => 'گۆیژەی نوێ',               'area_name_ar' => 'كويزة نوي',               'latitude' => 35.5315, 'longitude' => 45.4035],
            ['area_name_en' => 'Goyja',                  'area_name_ku' => 'گۆیژە',                    'area_name_ar' => 'کویزە',                   'latitude' => 35.5305, 'longitude' => 45.4025],
            ['area_name_en' => 'Grand Bolivard',         'area_name_ku' => 'گڕاند بۆلیڤارد',           'area_name_ar' => 'کراند بولیفارد',           'latitude' => 35.5800, 'longitude' => 45.4650],
            ['area_name_en' => 'Grda Brawaka',           'area_name_ku' => 'گردەبڕاوەکە',              'area_name_ar' => 'کردة براوکة',             'latitude' => 35.5295, 'longitude' => 45.4015],
            ['area_name_en' => 'Grda Khra',              'area_name_ku' => 'گردە خڕە',                 'area_name_ar' => 'کردة خرة',                'latitude' => 35.5285, 'longitude' => 45.4005],
            ['area_name_en' => 'Grdi Joga',              'area_name_ku' => 'گردی جۆگە',                'area_name_ar' => 'کردی جوکە',               'latitude' => 35.5810, 'longitude' => 45.4660],
            ['area_name_en' => 'Grdi Sarchnar',          'area_name_ku' => 'گردی سەرچنار',             'area_name_ar' => 'کردي سرجنار',             'latitude' => 35.5820, 'longitude' => 45.4670],
            ['area_name_en' => 'Guli Shar',              'area_name_ku' => 'گوڵی شار',                 'area_name_ar' => 'کولي شار',                'latitude' => 35.5275, 'longitude' => 45.3995],
            ['area_name_en' => 'Gundi Almani',           'area_name_ku' => 'گوندی ئەڵمانی',            'area_name_ar' => 'کوندي الماني',            'latitude' => 35.5265, 'longitude' => 45.3985],
            ['area_name_en' => 'Haje Swra',              'area_name_ku' => 'حاجی سوورە',               'area_name_ar' => 'حاجي سورة',               'latitude' => 35.5830, 'longitude' => 45.4680],
            ['area_name_en' => 'Hakari',                 'area_name_ku' => 'هەکاری',                   'area_name_ar' => 'هةکاري',                  'latitude' => 35.5840, 'longitude' => 45.4690],
            ['area_name_en' => 'Handren',                'area_name_ku' => 'هەندرێن',                  'area_name_ar' => 'هندرین',                  'latitude' => 35.5255, 'longitude' => 45.3975],
            ['area_name_en' => 'Hawara Barza',           'area_name_ku' => 'ھەوارە بەرزە',             'area_name_ar' => 'هوارة برزة',              'latitude' => 35.5245, 'longitude' => 45.3965],
            ['area_name_en' => 'Hawari Jwani',           'area_name_ku' => 'هەواری جوانی',             'area_name_ar' => 'هواري جواني',             'latitude' => 35.5850, 'longitude' => 45.4700],
            ['area_name_en' => 'Hawari Shar',            'area_name_ku' => 'هەواری شار',               'area_name_ar' => 'هواری شار',               'latitude' => 35.5860, 'longitude' => 45.4710],
            ['area_name_en' => 'Hawari Zanko',           'area_name_ku' => 'هەواری زانکۆ',             'area_name_ar' => 'هواري زانکو',             'latitude' => 35.5870, 'longitude' => 45.4720],
            ['area_name_en' => 'Helan City',             'area_name_ku' => 'هێلان سیتی',               'area_name_ar' => 'هیلان ستي',               'latitude' => 35.5235, 'longitude' => 45.3955],
            ['area_name_en' => 'Ibrahim Ahmed',          'area_name_ku' => 'ئیبراهیم ئە‌‌حمەد',        'area_name_ar' => 'ابراهيم احمد',            'latitude' => 35.5880, 'longitude' => 45.4730],
            ['area_name_en' => 'Ibrahim Pasha',          'area_name_ku' => 'ئیبراهیم پاشا',            'area_name_ar' => 'ابراهیم باشا',            'latitude' => 35.5890, 'longitude' => 45.4740],
            ['area_name_en' => 'Impaiar Towers',         'area_name_ku' => 'ئیمپایەر تاوەرز',          'area_name_ar' => 'امبایر تورز',             'latitude' => 35.5225, 'longitude' => 45.3945],
            ['area_name_en' => 'Iskan',                  'area_name_ku' => 'ئیسکان',                   'area_name_ar' => 'اسکان',                   'latitude' => 35.5215, 'longitude' => 45.3935],
            ['area_name_en' => 'Jaf Towers',             'area_name_ku' => 'جاف تاوەرس',               'area_name_ar' => 'جاف تاورس',               'latitude' => 35.5900, 'longitude' => 45.4750],
            ['area_name_en' => 'Julakan',                'area_name_ku' => 'جولەکان',                  'area_name_ar' => 'جولکان',                  'latitude' => 35.5205, 'longitude' => 45.3925],
            ['area_name_en' => 'Kalakn',                 'area_name_ku' => 'کەڵەکن',                   'area_name_ar' => 'کلةکن',                   'latitude' => 35.5195, 'longitude' => 45.3915],
            ['area_name_en' => 'Kana Swra',              'area_name_ku' => 'کەنە سوورە',               'area_name_ar' => 'کةنة سورة',               'latitude' => 35.5910, 'longitude' => 45.4760],
            ['area_name_en' => 'Kani Askan',             'area_name_ku' => 'کانی ئاسکان',              'area_name_ar' => 'کانی اسکان',              'latitude' => 35.5185, 'longitude' => 45.3905],
            ['area_name_en' => 'Kani Ba',                'area_name_ku' => 'کانی با',                  'area_name_ar' => 'کانی با',                 'latitude' => 35.5175, 'longitude' => 45.3895],
            ['area_name_en' => 'Kani City',              'area_name_ku' => 'کانی سیتی',                'area_name_ar' => 'کاني ستي',                'latitude' => 35.5920, 'longitude' => 45.4770],
            ['area_name_en' => 'Kani Goma',              'area_name_ku' => 'کانی گۆمە',                'area_name_ar' => 'کاني کومة',               'latitude' => 35.5165, 'longitude' => 45.3885],
            ['area_name_en' => 'Kani Kwrda',             'area_name_ku' => 'کانی کوردە',               'area_name_ar' => 'کانی کوردة',              'latitude' => 35.5155, 'longitude' => 45.3875],
            ['area_name_en' => 'Kani Kwrday Saroo',      'area_name_ku' => 'کانی کوردەی سەروو',        'area_name_ar' => 'کاني کوردة سروو',         'latitude' => 35.5145, 'longitude' => 45.3865],
            ['area_name_en' => 'Kani Kwrday Xwarw',      'area_name_ku' => 'کانی کوردەی خواروو',       'area_name_ar' => 'کاني کوردة خوارو',        'latitude' => 35.5135, 'longitude' => 45.3855],
            ['area_name_en' => 'Kani Spika',             'area_name_ku' => 'کانی سپیکە',               'area_name_ar' => 'کانی سبیکة',              'latitude' => 35.5930, 'longitude' => 45.4780],
            ['area_name_en' => 'Kareza Wshk',            'area_name_ku' => 'کارێزە وشک',               'area_name_ar' => 'کاریزة وشک',              'latitude' => 35.5125, 'longitude' => 45.3845],
            ['area_name_en' => 'Khabat',                 'area_name_ku' => 'خەبات',                    'area_name_ar' => 'خبات',                    'latitude' => 35.5115, 'longitude' => 45.3835],
            ['area_name_en' => 'Khak',                   'area_name_ku' => 'خاک',                      'area_name_ar' => 'خاک',                     'latitude' => 35.5940, 'longitude' => 45.4790],
            ['area_name_en' => 'Kobani City',            'area_name_ku' => 'کۆبانی ستی',               'area_name_ar' => 'کوبانی ستی',              'latitude' => 35.5105, 'longitude' => 45.3825],
            ['area_name_en' => 'Kurd City',              'area_name_ku' => 'کورد سیتی',                'area_name_ar' => 'کورد ستی',                'latitude' => 35.5950, 'longitude' => 45.4800],
            ['area_name_en' => 'Lana City',              'area_name_ku' => 'لانە سیتی',                'area_name_ar' => 'لانة ستي',                'latitude' => 35.5095, 'longitude' => 45.3815],
            ['area_name_en' => 'Lebanon City',           'area_name_ku' => 'شاری لوبنانی',             'area_name_ar' => 'لبنان ستي',               'latitude' => 35.5960, 'longitude' => 45.4810],
            ['area_name_en' => 'Majid Beg',              'area_name_ku' => 'مەجيد بەگ',                'area_name_ar' => 'مجيد بك',                 'latitude' => 35.5085, 'longitude' => 45.3805],
            ['area_name_en' => 'Mala Dawd',              'area_name_ku' => 'مەلا داود',                'area_name_ar' => 'ملا داود',                'latitude' => 35.5970, 'longitude' => 45.4820],
            ['area_name_en' => 'Malkandi',               'area_name_ku' => 'مەڵکەندی',                 'area_name_ar' => 'ملکندی',                  'latitude' => 35.5075, 'longitude' => 45.3795],
            ['area_name_en' => 'Mama Yara',              'area_name_ku' => 'مامە یارە',                 'area_name_ar' => 'مامة یارة',               'latitude' => 35.5980, 'longitude' => 45.4830],
            ['area_name_en' => 'Miran City',             'area_name_ku' => 'میران سیتی',               'area_name_ar' => 'میران ستی',               'latitude' => 35.5065, 'longitude' => 45.3785],
            ['area_name_en' => 'Nali City',              'area_name_ku' => 'نالی سیتی',                'area_name_ar' => 'نالي ستي',                'latitude' => 35.5990, 'longitude' => 45.4840],
            ['area_name_en' => 'Naro City',              'area_name_ku' => 'نارۆ سیتی',                'area_name_ar' => 'نارو ستي',                'latitude' => 35.5055, 'longitude' => 45.3775],
            ['area_name_en' => 'Nawroz City',            'area_name_ku' => 'نەورۆز سیتی',              'area_name_ar' => 'نوروز ستي',               'latitude' => 35.5045, 'longitude' => 45.3765],
            ['area_name_en' => 'Pak City',               'area_name_ku' => 'پاک سیتی',                 'area_name_ar' => 'باک ستی',                 'latitude' => 35.6000, 'longitude' => 45.4850],
            ['area_name_en' => 'Park77',                 'area_name_ku' => 'پارک ٧٧',                  'area_name_ar' => 'بارک ٧٧',                 'latitude' => 35.5035, 'longitude' => 45.3755],
            ['area_name_en' => 'Pasha City',             'area_name_ku' => 'پاشا سیتی',                'area_name_ar' => 'باشا ستي',                'latitude' => 35.6010, 'longitude' => 45.4860],
            ['area_name_en' => 'Qaiwan City',            'area_name_ku' => 'قەیوان سیتی',              'area_name_ar' => 'قیوان ستی',               'latitude' => 35.5025, 'longitude' => 45.3745],
            ['area_name_en' => 'Qalawa',                 'area_name_ku' => 'قالاوە',                   'area_name_ar' => 'قلاوة',                   'latitude' => 35.6020, 'longitude' => 45.4870],
            ['area_name_en' => 'Qaratoghan',             'area_name_ku' => 'قەرەتۆغان',                'area_name_ar' => 'قرتوغان',                 'latitude' => 35.5015, 'longitude' => 45.3735],
            ['area_name_en' => 'Qlyasan',                'area_name_ku' => 'قلیاسان',                  'area_name_ar' => 'قلیاسان',                 'latitude' => 35.6030, 'longitude' => 45.4880],
            ['area_name_en' => 'Qula Raisy',             'area_name_ku' => 'قولە ڕەیسی',               'area_name_ar' => 'قولة ریسي',               'latitude' => 35.5005, 'longitude' => 45.3725],
            ['area_name_en' => 'Rozh City',              'area_name_ku' => 'ڕۆژ سیتی',                 'area_name_ar' => 'روز سیتی',                'latitude' => 35.6040, 'longitude' => 45.4890],
            ['area_name_en' => 'Rzgar City',             'area_name_ku' => 'ڕزگار سیتی',               'area_name_ar' => 'رزکار ستي',               'latitude' => 35.4995, 'longitude' => 45.3715],
            ['area_name_en' => 'Rzgari Kon',             'area_name_ku' => 'ڕزگاری کۆن',               'area_name_ar' => 'رزکاري کون',              'latitude' => 35.6050, 'longitude' => 45.4900],
            ['area_name_en' => 'Rzgari Taza',            'area_name_ku' => 'ڕزگاری تازە',              'area_name_ar' => 'رزکاري تازة',             'latitude' => 35.4985, 'longitude' => 45.3705],
            ['area_name_en' => 'Saib City',              'area_name_ku' => 'سائیب سیتی',               'area_name_ar' => 'سائیب ستی',               'latitude' => 35.6060, 'longitude' => 45.4910],
            ['area_name_en' => 'Salim Bag',              'area_name_ku' => 'سەلیم بەگ',                'area_name_ar' => 'سلیم بک',                 'latitude' => 35.4975, 'longitude' => 45.3695],
            ['area_name_en' => 'Sardaw',                 'area_name_ku' => 'سارداو',                   'area_name_ar' => 'سارداو',                  'latitude' => 35.6070, 'longitude' => 45.4920],
            ['area_name_en' => 'Sarshaqam',              'area_name_ku' => 'سەرشەقام',                 'area_name_ar' => 'سر شقام',                 'latitude' => 35.4965, 'longitude' => 45.3685],
            ['area_name_en' => 'Sarwarin',               'area_name_ku' => 'سه روەرین',                'area_name_ar' => 'سرورين',                  'latitude' => 35.6080, 'longitude' => 45.4930],
            ['area_name_en' => 'Saya City',              'area_name_ku' => 'سایە سیتی',                'area_name_ar' => 'سایة ستي',                'latitude' => 35.4955, 'longitude' => 45.3675],
            ['area_name_en' => 'Shahen City',            'area_name_ku' => 'شاهێن سیتی',               'area_name_ar' => 'شاهین ستي',               'latitude' => 35.6090, 'longitude' => 45.4940],
            ['area_name_en' => 'Shahidani Zargata',      'area_name_ku' => 'شەهیدانی زەرگەتە',         'area_name_ar' => 'شهیداني زرکةتة',          'latitude' => 35.4945, 'longitude' => 45.3665],
            ['area_name_en' => 'Shahidany Azadi',        'area_name_ku' => 'شەهیدانی ئازادی',          'area_name_ar' => 'شهیدانی ازادي',           'latitude' => 35.6100, 'longitude' => 45.4950],
            ['area_name_en' => 'Shari Aram',             'area_name_ku' => 'شاری ئارام',               'area_name_ar' => 'شاري ارام',               'latitude' => 35.4935, 'longitude' => 45.3655],
            ['area_name_en' => 'Shari Aween',            'area_name_ku' => 'شاری ئەوین',               'area_name_ar' => 'شاری اوین',               'latitude' => 35.6110, 'longitude' => 45.4960],
            ['area_name_en' => 'Shari Daik',             'area_name_ku' => 'شاری دایک',                'area_name_ar' => 'شاري دایک',               'latitude' => 35.4925, 'longitude' => 45.3645],
            ['area_name_en' => 'Shari Mamostayan',       'area_name_ku' => 'شاری مامۆستایان',          'area_name_ar' => 'شاري ماموستایان',         'latitude' => 35.6120, 'longitude' => 45.4970],
            ['area_name_en' => 'Shari Nmunaiy',          'area_name_ku' => 'شاری نمونەی',              'area_name_ar' => 'شاري نمونەي',             'latitude' => 35.4915, 'longitude' => 45.3635],
            ['area_name_en' => 'Shari Pzishkan',         'area_name_ku' => 'شاری پزیشکان',             'area_name_ar' => 'شاري بزیشکان',            'latitude' => 35.6130, 'longitude' => 45.4980],
            ['area_name_en' => 'Shari Roshnbiran',       'area_name_ku' => 'شاری ڕۆشنبیران',           'area_name_ar' => 'شاري روشنبیران',          'latitude' => 35.4905, 'longitude' => 45.3625],
            ['area_name_en' => 'Shari Spi',              'area_name_ku' => 'شاری سپی',                 'area_name_ar' => 'شاري سبي',                'latitude' => 35.6140, 'longitude' => 45.4990],
            ['area_name_en' => 'Shari Zaitun',           'area_name_ku' => 'شاری زەیتون',              'area_name_ar' => 'شاری زیتون',              'latitude' => 35.4895, 'longitude' => 45.3615],
            ['area_name_en' => 'Shekh Waisawa',          'area_name_ku' => 'شێخ وەیساوە',              'area_name_ar' => 'شیخ ویساوة',              'latitude' => 35.6150, 'longitude' => 45.5000],
            ['area_name_en' => 'Sherwana',               'area_name_ku' => 'شێروانە',                  'area_name_ar' => 'شیروانە',                 'latitude' => 35.4885, 'longitude' => 45.3605],
            ['area_name_en' => 'Shnyar City',            'area_name_ku' => 'شنیار سیتی',               'area_name_ar' => 'شنیار ستي',               'latitude' => 35.6160, 'longitude' => 45.5010],
            ['area_name_en' => 'Shorsh',                 'area_name_ku' => 'شۆڕش',                    'area_name_ar' => 'شورش',                    'latitude' => 35.4875, 'longitude' => 45.3595],
            ['area_name_en' => 'Shuqakani Kareza Wshk',  'area_name_ku' => 'شوقەکانی کارێزە وشک',      'area_name_ar' => 'شوقةکاني کاریزة وشک',     'latitude' => 35.6170, 'longitude' => 45.5020],
            ['area_name_en' => 'Shuqakani Yakgrtw',      'area_name_ku' => 'شوقەکانی یەکگرتوو',        'area_name_ar' => 'شوقەکاني یکرتوو',         'latitude' => 35.4865, 'longitude' => 45.3585],
            ['area_name_en' => 'Shwqakani Ashti',        'area_name_ku' => 'شوقەکانی ئاشتی',           'area_name_ar' => 'شوقةکاني اشتي',           'latitude' => 35.6180, 'longitude' => 45.5030],
            ['area_name_en' => 'Shwqakani Raparin',      'area_name_ku' => 'شوقەکانی ڕاپەڕین',         'area_name_ar' => 'شوقةکاني رابرین',         'latitude' => 35.4855, 'longitude' => 45.3575],
            ['area_name_en' => 'Slemani Taza',           'area_name_ku' => 'سلێمانی تازە',             'area_name_ar' => 'سلیمانی تازة',            'latitude' => 35.6190, 'longitude' => 45.5040],
            ['area_name_en' => 'Slemany Nwe',            'area_name_ku' => 'سلێمانی نوێ',              'area_name_ar' => 'سلیمانی نوي',             'latitude' => 35.4845, 'longitude' => 45.3565],
            ['area_name_en' => 'Suli Sky',               'area_name_ku' => 'سولی سکای',                'area_name_ar' => 'سولي سکاي',               'latitude' => 35.6200, 'longitude' => 45.5050],
            ['area_name_en' => 'Swren',                  'area_name_ku' => 'سورێن',                    'area_name_ar' => 'سورین',                   'latitude' => 35.4835, 'longitude' => 45.3555],
            ['area_name_en' => 'Tavga',                  'area_name_ku' => 'تاڤگە',                    'area_name_ar' => 'تافکة',                   'latitude' => 35.6210, 'longitude' => 45.5060],
            ['area_name_en' => 'Twi Malik',              'area_name_ku' => 'تووى مەليك',               'area_name_ar' => 'توي ملك',                 'latitude' => 35.4825, 'longitude' => 45.3545],
            ['area_name_en' => 'Xanwa Qwrakan',          'area_name_ku' => 'خانووە قورەکان',            'area_name_ar' => 'خانووة قورکان',           'latitude' => 35.6220, 'longitude' => 45.5070],
            ['area_name_en' => 'Zargata',                'area_name_ku' => 'زەرگەتە',                  'area_name_ar' => 'زرگةتة',                  'latitude' => 35.4815, 'longitude' => 45.3535],
            ['area_name_en' => 'Zargatay Kon',           'area_name_ku' => 'زەرگەتەی کۆن',             'area_name_ar' => 'زرکةتةي کون',             'latitude' => 35.6230, 'longitude' => 45.5080],
            ['area_name_en' => 'Zargatay Taza',          'area_name_ku' => 'زەرگەتەی تازە',            'area_name_ar' => 'زرکةتةي تازة',            'latitude' => 35.4805, 'longitude' => 45.3525],
            ['area_name_en' => 'Zhalay Sarw',            'area_name_ku' => 'ژاڵەی سەروو',              'area_name_ar' => 'زالةي سرو',               'latitude' => 35.6240, 'longitude' => 45.5090],
            ['area_name_en' => 'Zhalay Xwarw',           'area_name_ku' => 'ژاڵەی خواروو',             'area_name_ar' => 'زالة خوارو',              'latitude' => 35.4795, 'longitude' => 45.3515],
        ];

        foreach ($sulaymaniyahAreas as $area) {
            Area::create(array_merge($area, ['branch_id' => $sulaymaniyah->id, 'is_active' => true]));
        }

        $this->command->info('✅ Sulaymaniyah extended areas seeded: ' . count($sulaymaniyahAreas) . ' areas');

        // ==================== SORAN (Extended Areas) ====================

        $soran = Branch::where('city_name_en', 'Soran')->firstOrFail();

        $soranAreas = [
            ['area_name_en' => 'Ailul',              'area_name_ku' => 'ئەیلول',              'area_name_ar' => 'أيلول',         'latitude' => 36.6540, 'longitude' => 44.5450],
            ['area_name_en' => 'Airport',            'area_name_ku' => 'مەتاڕ',               'area_name_ar' => 'مطار',           'latitude' => 36.6480, 'longitude' => 44.5380],
            ['area_name_en' => 'Awaran',             'area_name_ku' => 'ئاواران',             'area_name_ar' => 'ئاواران',        'latitude' => 36.6550, 'longitude' => 44.5460],
            ['area_name_en' => 'Azadi',              'area_name_ku' => 'ئازادی',              'area_name_ar' => 'آزادي',          'latitude' => 36.6560, 'longitude' => 44.5470],
            ['area_name_en' => 'Bakhtyari',          'area_name_ku' => 'بەختياری',            'area_name_ar' => 'بختياري',        'latitude' => 36.6570, 'longitude' => 44.5480],
            ['area_name_en' => 'Bana',               'area_name_ku' => 'بانە',                'area_name_ar' => 'بانة',           'latitude' => 36.6580, 'longitude' => 44.5490],
            ['area_name_en' => 'Bapshtian',          'area_name_ku' => 'باپشتیان',            'area_name_ar' => 'بابشتیان',       'latitude' => 36.6590, 'longitude' => 44.5500],
            ['area_name_en' => 'Barkhudan',          'area_name_ku' => 'بەرخودان',            'area_name_ar' => 'برخودان',        'latitude' => 36.6600, 'longitude' => 44.5510],
            ['area_name_en' => 'Barzan',             'area_name_ku' => 'بارزان',              'area_name_ar' => 'برزان',          'latitude' => 36.6610, 'longitude' => 44.5520],
            ['area_name_en' => 'Bergwan',            'area_name_ku' => 'بەرگوان',             'area_name_ar' => 'بركوان',         'latitude' => 36.6620, 'longitude' => 44.5530],
            ['area_name_en' => 'Brayati',            'area_name_ku' => 'برايەتی',             'area_name_ar' => 'برايتي',         'latitude' => 36.6630, 'longitude' => 44.5540],
            ['area_name_en' => 'Delzian',            'area_name_ku' => 'دێڵزیان',             'area_name_ar' => 'ديلزيان',        'latitude' => 36.6640, 'longitude' => 44.5550],
            ['area_name_en' => 'Dilman',             'area_name_ku' => 'دیلمان',              'area_name_ar' => 'دیلمان',         'latitude' => 36.6650, 'longitude' => 44.5560],
            ['area_name_en' => 'Galala',             'area_name_ku' => 'گەڵاڵە',              'area_name_ar' => 'کلالة',          'latitude' => 36.6660, 'longitude' => 44.5570],
            ['area_name_en' => 'Goraz',              'area_name_ku' => 'گۆرەز',               'area_name_ar' => 'كورةز',          'latitude' => 36.6670, 'longitude' => 44.5580],
            ['area_name_en' => 'Gulan',              'area_name_ku' => 'گوڵان',               'area_name_ar' => 'کولان',          'latitude' => 36.6680, 'longitude' => 44.5590],
            ['area_name_en' => 'Handren',            'area_name_ku' => 'هەندرێن',             'area_name_ar' => 'هندرين',         'latitude' => 36.6690, 'longitude' => 44.5600],
            ['area_name_en' => 'Harem',              'area_name_ku' => 'ھەرێم',               'area_name_ar' => 'ھریم',           'latitude' => 36.6700, 'longitude' => 44.5610],
            ['area_name_en' => 'Ich Qala',           'area_name_ku' => 'ئیچ قەلا',            'area_name_ar' => 'ئیج قلا',        'latitude' => 36.6710, 'longitude' => 44.5620],
            ['area_name_en' => 'Jundian',            'area_name_ku' => 'جوندیان',             'area_name_ar' => 'جوندیان',        'latitude' => 36.6720, 'longitude' => 44.5630],
            ['area_name_en' => 'Kandi Haji Yusuf',  'area_name_ku' => 'کەندی حاجی یوسف',     'area_name_ar' => 'کندي حجي یوسف', 'latitude' => 36.6730, 'longitude' => 44.5640],
            ['area_name_en' => 'Khabat',             'area_name_ku' => 'خەبات',               'area_name_ar' => 'خبات',           'latitude' => 36.6740, 'longitude' => 44.5650],
            ['area_name_en' => 'Korek',              'area_name_ku' => 'کۆڕەک',               'area_name_ar' => 'كوريك',          'latitude' => 36.6750, 'longitude' => 44.5660],
            ['area_name_en' => 'Kurdistan',          'area_name_ku' => 'کوردستان',            'area_name_ar' => 'كردستان',        'latitude' => 36.6760, 'longitude' => 44.5670],
            ['area_name_en' => 'Nahri',              'area_name_ku' => 'نەهری',               'area_name_ar' => 'نهري',           'latitude' => 36.6770, 'longitude' => 44.5680],
            ['area_name_en' => 'Nawroz',             'area_name_ku' => 'نەورۆز',              'area_name_ar' => 'نوروز',          'latitude' => 36.6780, 'longitude' => 44.5690],
            ['area_name_en' => 'Raparin',            'area_name_ku' => 'ڕاپەڕین',             'area_name_ar' => 'رابرين',         'latitude' => 36.6790, 'longitude' => 44.5700],
            ['area_name_en' => 'Rezan',              'area_name_ku' => 'ڕێزان',               'area_name_ar' => 'ريزان',          'latitude' => 36.6800, 'longitude' => 44.5710],
            ['area_name_en' => 'Sarcham',            'area_name_ku' => 'سەرچەم',              'area_name_ar' => 'سرجم',           'latitude' => 36.6810, 'longitude' => 44.5720],
            ['area_name_en' => 'Sarwaran',           'area_name_ku' => 'سەروەران',            'area_name_ar' => 'سروران',         'latitude' => 36.6820, 'longitude' => 44.5730],
            ['area_name_en' => 'Sarwchawa',          'area_name_ku' => 'سەروچاوە',            'area_name_ar' => 'سروجاوة',        'latitude' => 36.6830, 'longitude' => 44.5740],
            ['area_name_en' => 'Shorsh',             'area_name_ku' => 'شۆرش',               'area_name_ar' => 'شورش',           'latitude' => 36.6840, 'longitude' => 44.5750],
            ['area_name_en' => 'Srwa',               'area_name_ku' => 'سـروە',               'area_name_ar' => 'سـروة',          'latitude' => 36.6850, 'longitude' => 44.5760],
            ['area_name_en' => 'Wasta Rajab',        'area_name_ku' => 'وەستا ڕەجەب',         'area_name_ar' => 'وستا ڕجب',       'latitude' => 36.6860, 'longitude' => 44.5770],
            ['area_name_en' => 'Zanyari',            'area_name_ku' => 'زانیاری',             'area_name_ar' => 'زانیاري',        'latitude' => 36.6870, 'longitude' => 44.5780],
            ['area_name_en' => 'Zozk',               'area_name_ku' => 'زۆزک',               'area_name_ar' => 'زوزك',           'latitude' => 36.6880, 'longitude' => 44.5790],
        ];

        foreach ($soranAreas as $area) {
            Area::create(array_merge($area, ['branch_id' => $soran->id, 'is_active' => true]));
        }

        $this->command->info('✅ Soran extended areas seeded: ' . count($soranAreas) . ' areas');

        // ==================== RAWANDUZ (Extended Areas) ====================

        $rawanduz = Branch::where('city_name_en', 'Rawanduz')->firstOrFail();

        $rawanduzAreas = [
            ['area_name_en' => 'Azadi',        'area_name_ku' => 'ئازادی',      'area_name_ar' => 'آزادي',        'latitude' => 36.6142, 'longitude' => 44.5247],
            ['area_name_en' => 'Banezewk',     'area_name_ku' => 'بانەزێوك',    'area_name_ar' => 'بانةزيوك',     'latitude' => 36.6150, 'longitude' => 44.5260],
            ['area_name_en' => 'Bazar',        'area_name_ku' => 'بازاڕ',       'area_name_ar' => 'بازار',         'latitude' => 36.6160, 'longitude' => 44.5270],
            ['area_name_en' => 'Gardagard',    'area_name_ku' => 'گەردەگەرد',   'area_name_ar' => 'كردةكرد',       'latitude' => 36.6170, 'longitude' => 44.5280],
            ['area_name_en' => 'Gulan',        'area_name_ku' => 'گوڵان',       'area_name_ar' => 'كولان',          'latitude' => 36.6180, 'longitude' => 44.5290],
            ['area_name_en' => 'Kani',         'area_name_ku' => 'کانی',        'area_name_ar' => 'كاني',           'latitude' => 36.6190, 'longitude' => 44.5300],
            ['area_name_en' => 'Karox',        'area_name_ku' => 'کارۆخ',       'area_name_ar' => 'كاروخ',          'latitude' => 36.6200, 'longitude' => 44.5310],
            ['area_name_en' => 'Khabat',       'area_name_ku' => 'خەبات',       'area_name_ar' => 'خبات',           'latitude' => 36.6210, 'longitude' => 44.5320],
            ['area_name_en' => 'Lawan',        'area_name_ku' => 'لاوان',       'area_name_ar' => 'لاوان',          'latitude' => 36.6220, 'longitude' => 44.5330],
            ['area_name_en' => 'Nawroz',       'area_name_ku' => 'نەورۆز',      'area_name_ar' => 'نوروز',          'latitude' => 36.6230, 'longitude' => 44.5340],
            ['area_name_en' => 'Pashai Gawra', 'area_name_ku' => 'پاشای گەورە', 'area_name_ar' => 'باشاي كةورة',   'latitude' => 36.6240, 'longitude' => 44.5350],
            ['area_name_en' => 'Raparin',      'area_name_ku' => 'ڕاپەڕین',     'area_name_ar' => 'رابةرين',        'latitude' => 36.6250, 'longitude' => 44.5360],
            ['area_name_en' => 'Shahidan',     'area_name_ku' => 'شەهیدان',     'area_name_ar' => 'شهیدان',         'latitude' => 36.6260, 'longitude' => 44.5370],
            ['area_name_en' => 'Shorsh',       'area_name_ku' => 'شۆرش',       'area_name_ar' => 'شورش',           'latitude' => 36.6270, 'longitude' => 44.5380],
        ];

        foreach ($rawanduzAreas as $area) {
            Area::create(array_merge($area, ['branch_id' => $rawanduz->id, 'is_active' => true]));
        }

        $this->command->info('✅ Rawanduz extended areas seeded: ' . count($rawanduzAreas) . ' areas');

        // ==================== SUMMARY ====================

        $totalNew = count($sulaymaniyahAreas) + count($soranAreas) + count($rawanduzAreas);
        $this->command->info("📊 KurdistanLocationSeeder2 complete — {$totalNew} new areas inserted.");
    }
}
