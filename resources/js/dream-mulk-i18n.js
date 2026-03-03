class DreamMulkI18n {
  constructor(o={}){
    this.storageKey  = o.storageKey  || 'dm_lang';
    this.defaultLang = o.defaultLang || 'ku';
    this.onLangChange = o.onLangChange || null;
    this._current = this.defaultLang;
  }
  init(){
    const saved = localStorage.getItem(this.storageKey) || this.defaultLang;
    this.setLang(saved);
  }
  setLang(lang){
    if(!this.translations[lang]) return;
    this._current = lang;
    localStorage.setItem(this.storageKey, lang);
    const T = this.translations[lang];
    // Direction
    document.documentElement.dir = T.dir;
    document.body.classList.remove('lang-ku','lang-en','lang-ar','rtl');
    document.body.classList.add('lang-'+lang);
    if(T.dir==='rtl') document.body.classList.add('rtl');
    // Switcher buttons
    document.querySelectorAll('.lang-btn').forEach(b=>{
      b.classList.toggle('active', b.getAttribute('data-lang')===lang);
    });
    // Apply all data-i18n elements
    document.querySelectorAll('[data-i18n]').forEach(el=>{
      const key = el.getAttribute('data-i18n');
      if(T[key]===undefined) return;
      if(el.children.length===0){
        el.textContent = T[key];
      } else {
        for(let n of el.childNodes){
          if(n.nodeType===Node.TEXT_NODE && n.textContent.trim()){
            n.textContent = T[key]; break;
          }
        }
      }
    });
    // Special IDs
    const ids = {
      't-eyebrow':'eyebrow','t-sub1':'sub1','t-sub2':'sub2',
      'tab-buy':'tabBuy','tab-rent':'tabRent','tab-sell':'tabSell',
      'hs-btn':'searchBtn','t-popular':'popular',
      'q-erbil':'erbil','q-suli':'suli','q-duhok':'duhok',
      't-explore':'explore','t-app':'appBtn','t-scroll':'scroll',
      'nav-home':'navHome','nav-props':'navProps','nav-app':'navApp',
      'nav-about':'navAbout','nav-contact':'navContact'
    };
    Object.entries(ids).forEach(([id,key])=>{
      const el=document.getElementById(id);
      if(el && T[key]) el.textContent=T[key];
    });
    if(typeof this.onLangChange==='function') this.onLangChange(lang,T);
  }
  getCurrentLang(){ return this._current; }
  t(key){ return (this.translations[this._current]||{})[key]||key; }

  translations = {

    // ══ KURDISH (سۆرانی) — زمانی بنەڕەت ══
    ku:{
      dir:'rtl',
      eyebrow:       'خانوو و زەوی',
      sub1:          'بۆ کڕین، فرۆشتن و کرێی خانوو لە کوردستان — بێ کارمزد',
      sub2:          'کوردستان • هەولێر • ٢٠٢٦',
      tabBuy:        '🏠 کڕین',
      tabRent:       '🔑 کرێ',
      tabSell:       '💰 فرۆشتن',
      searchBtn:     'بگەڕێ',
      placeholderBuy:  'بگەڕێ... هەولێر، سلێمانی، دهۆک',
      placeholderRent: 'خانوی کرێ بدۆزەرەوە...',
      placeholderSell: 'خانووەکەت بفرۆشە...',
      popular:       'شارەکان:',
      erbil:         'هەولێر',
      suli:          'سلێمانی',
      duhok:         'دهۆک',
      explore:       'خانووەکان ببینە',
      appBtn:        'ئەپەکە دابەزێنە',
      scroll:        'دابەزە',

      navHome:       'سەرەتا',
      navProps:      'خانووەکان',
      navApp:        'ئەپ',
      navAbout:      'دەربارەمان',
      navContact:    'پەیوەندی',
      loginBtn:      'چوونەژوورەوە',
      browseBtn:     'خانووەکان ببینە',

      statLabel1:    'خانووی تۆمارکراو',
      statLabel2:    'ئەجێنتی پشتڕاستکراو',

      svcTag:        'خزمەتگوزاریەکان',
      svcTitle:      'چی',
      svcTitleEm:    'پێشکەش دەکەین',
      svc1Title:     'کڕینی خانوو',
      svc1Desc:      'خانووی خەونەکەت بدۆزەرەوە. لیستی تایبەت لە سەرانسەری کوردستان بە نرخی ئاشکرا.',
      svc1Cta:       'بگەڕێ',
      svc2Title:     'فرۆشتنی خانوو',
      svc2Desc:      'خانووەکەت لیست بکە و بگەیە کڕیارە راستەقینەکان. بێ کۆمیسیۆن، بێ نهێنی.',
      svc2Cta:       'لیست بکە',
      svc3Title:     'کرێی خانوو',
      svc3Desc:      'خانوی کرێی گونجاو بدۆزەرەوە. نرخی ئاشکرا و لیستی پشتڕاستکراو.',
      svc3Cta:       'بگەڕێ',

      appTag:        'ئەپی مۆبایل',
      appTitle:      'خانووەکان',
      appTitleEm:    'لە گەڵت',
      appDesc:       'ئەپی Dream Mulk هەموو خانووەکانی کوردستانت دەخاتە پێش دەستت. بگەڕێ، کاتی سەردان دابنێ، و ڕاستەوخۆ پەیوەندی بکە بە فرۆشیارەکان.',
      appF1:         'لیستی خانووی نوێ و ئاگادارکردنەوەی خێرا',
      appF2:         'کاتی سەردانی خانوو دابنێ',
      appF3:         'کوردی، عەرەبی و ئینگلیزی',
      appF4:         'پەیامی پارێزراو و بەڵگەنامەکان',
      appF5:         'بێ کۆمیسیۆن — بەخۆڕایی بگەڕێ',
      appStoreLabel: 'دابەزێنە لە',
      playStoreLabel:'وەربگرە لە',
      qrSub:         'بەخۆڕایی',
      qrHint:        'کامێرا بخەرە سەر کودەکە',
      qrBtn:         'بکرەوە لە App Store',

      abtTag:        'دەربارەمان',
      abtTitle:      'Dream Mulk',
      abtTitleEm:    'کێیە؟',
      abtP1:         'Dream Mulk دامەزراوە بۆ ئەوەی بازاڕی خانووبەرەی کوردستان ئاسانتر و ئاشکراتر بکات. ئێمە نیازمان بۆ ئەوەیە کە کڕیار و فرۆشیار ڕاستەوخۆ پەیوەندی بکەن — بێ ناوەڕاست، بێ کۆمیسیۆن.',
      abtP2:         'لە بازاڕێک کە زۆرجار ئاڵۆزە و پر لە نهێنی، ئێمە شەفافیەت و ئاسانی دەهێنینەوە. تەکنەلۆژیامان لە خزمەتت دایە.',
      abtQuote:      '«خانوو زەویە، بەڵام "مولک" مێژوویە. یارمەتیت دەدەین کە مێژووی خۆت بنووسیت.»',
      val1Title:     'تایبەتی',
      val1Sub:       'خانووی هەڵبژێردراو',
      val2Title:     'پاکی و ئامانج',
      val2Sub:       'ئاشکرایی تەواو',
      val3Title:     'تەکنەلۆژیا',
      val3Sub:       'ئەپ و وێبسایتی مۆدێرن',
      val4Title:     'هەولێر',
      val4Sub:       'دامەزراوە ٢٠٢٦',

      rdrTag:        'بۆ ئۆفیسی خانووبەرە',
      rdrTitle:      'بزنسەکەت گەشە بکە',
      rdrDesc:       'ئۆفیسەکەت تۆمار بکە و بگەیە بە هەزاران کڕیار و کرێیار. خانوو لیست بکە، ئەجێنت بەڕێوەبەرە، مامەڵەکان ببەستە.',
      rdrBtn1:       'چوونەژوورەوەی ئۆفیس',
      rdrBtn2:       'بگەڕێ بەبێ تۆمارکردن',

      ftTag:         'باشترین پلاتفۆرمی خانووبەرەی کوردستان. بێ کارمزد. بێ کۆمیسیۆن.',
      ftCol1:        'پلاتفۆرم',
      ftCol2:        'خزمەتگوزاری',
      ftCol3:        'ئەپەکە دابەزێنە',
      ftLink1:       'خانووەکان ببینە',
      ftLink2:       'چوونەژوورەوەی کڕیار',
      ftLink3:       'پۆرتاڵی ئەجێنت',
      ftLink4:       'دەربارەمان',
      ftLink5:       'کڕینی خانوو',
      ftLink6:       'فرۆشتنی خانوو',
      ftLink7:       'کرێی خانوو',
      ftLink8:       'ئەجێنت بدۆزەرەوە',
      ftLink9:       'پەیوەندیمان پێوە بکە',
      fabAgent:      'پۆرتاڵی ئەجێنت',
      fabOffice:     'چوونەژوورەوەی ئۆفیس',
    },

    // ══ ENGLISH ══
    en:{
      dir:'ltr',
      eyebrow:       'Premium Real Estate',
      sub1:          'Buy, sell & rent properties across Kurdistan — zero commission',
      sub2:          'Kurdistan • Erbil • Est. 2026',
      tabBuy:        '🏠 Buy',
      tabRent:       '🔑 Rent',
      tabSell:       '💰 Sell',
      searchBtn:     'Search',
      placeholderBuy:  'Search in Erbil, Sulaymaniyah...',
      placeholderRent: 'Find rentals in Kurdistan...',
      placeholderSell: 'List your property...',
      popular:       'Popular:',
      erbil:         'Erbil',
      suli:          'Sulaymaniyah',
      duhok:         'Duhok',
      explore:       'Explore Properties',
      appBtn:        'Download App',
      scroll:        'Scroll',

      navHome:       'Home',
      navProps:      'Properties',
      navApp:        'App',
      navAbout:      'About Us',
      navContact:    'Contact',
      loginBtn:      'Client Login',
      browseBtn:     'Browse Properties',

      statLabel1:    'Listed Properties',
      statLabel2:    'Verified Agents',

      svcTag:        'Our Services',
      svcTitle:      'What We',
      svcTitleEm:    'Offer',
      svc1Title:     'Buy a Property',
      svc1Desc:      'Find your dream home with advanced filters. Exclusive listings across Kurdistan with full price transparency.',
      svc1Cta:       'Explore',
      svc2Title:     'Sell a Property',
      svc2Desc:      'List your property and reach serious buyers directly. No middlemen, no commissions, no hidden fees.',
      svc2Cta:       'List Now',
      svc3Title:     'Rent a Property',
      svc3Desc:      'Find the right rental at the right price. Verified listings with transparent terms.',
      svc3Cta:       'Find Rentals',

      appTag:        'Mobile App',
      appTitle:      'Properties',
      appTitleEm:    'With You',
      appDesc:       'The Dream Mulk app puts Kurdistan\'s real estate market in your pocket. Search, book viewings, and contact sellers directly.',
      appF1:         'Live property listings & instant alerts',
      appF2:         'Book property viewings instantly',
      appF3:         'Kurdish, Arabic & English',
      appF4:         'Secure messaging & documents',
      appF5:         'Zero commission — always free',
      appStoreLabel: 'Download on the',
      playStoreLabel:'Get it on',
      qrSub:         'Free Download',
      qrHint:        'Point camera to scan',
      qrBtn:         'Open in App Store',

      abtTag:        'About Us',
      abtTitle:      'Dream Mulk',
      abtTitleEm:    'Story',
      abtP1:         'Dream Mulk was built to make Kurdistan\'s property market simpler and more transparent. We connect buyers and sellers directly — no middlemen, no commissions.',
      abtP2:         'In a market full of complexity, we bring clarity and technology to every transaction.',
      abtQuote:      '"Property is land, but Mulk is legacy. We help you write yours."',
      val1Title:     'Exclusive',
      val1Sub:       'Curated listings',
      val2Title:     'Integrity',
      val2Sub:       'Full transparency',
      val3Title:     'Technology',
      val3Sub:       'Modern app & platform',
      val4Title:     'Erbil Based',
      val4Sub:       'Est. 2026',

      rdrTag:        'For Real Estate Offices',
      rdrTitle:      'Grow Your Business',
      rdrDesc:       'Register your office and reach thousands of buyers and renters across Kurdistan. Manage listings, agents, and deals in one place.',
      rdrBtn1:       'Office Login',
      rdrBtn2:       'Browse Without Login',

      ftTag:         'Kurdistan\'s real estate platform. No fees. No commissions. Always transparent.',
      ftCol1:        'Platform',
      ftCol2:        'Services',
      ftCol3:        'Download App',
      ftLink1:       'Browse Properties',
      ftLink2:       'Client Login',
      ftLink3:       'Agent Portal',
      ftLink4:       'About Us',
      ftLink5:       'Buy Property',
      ftLink6:       'Sell Property',
      ftLink7:       'Rent Property',
      ftLink8:       'Find an Agent',
      ftLink9:       'Contact Us',
      fabAgent:      'Agent Portal',
      fabOffice:     'Office Login',
    },

    // ══ ARABIC ══
    ar:{
      dir:'rtl',
      eyebrow:       'عقارات كردستان',
      sub1:          'شراء وبيع وإيجار العقارات في كردستان — بدون عمولة',
      sub2:          'كردستان • أربيل • ٢٠٢٦',
      tabBuy:        '🏠 شراء',
      tabRent:       '🔑 إيجار',
      tabSell:       '💰 بيع',
      searchBtn:     'ابحث',
      placeholderBuy:  'ابحث في أربيل، السليمانية...',
      placeholderRent: 'ابحث عن شقق للإيجار...',
      placeholderSell: 'أضف عقارك...',
      popular:       'المدن:',
      erbil:         'أربيل',
      suli:          'السليمانية',
      duhok:         'دهوك',
      explore:       'استعرض العقارات',
      appBtn:        'تحميل التطبيق',
      scroll:        'انزل',

      navHome:       'الرئيسية',
      navProps:      'العقارات',
      navApp:        'التطبيق',
      navAbout:      'من نحن',
      navContact:    'تواصل',
      loginBtn:      'تسجيل الدخول',
      browseBtn:     'استعرض العقارات',

      statLabel1:    'عقارات مدرجة',
      statLabel2:    'وكلاء موثقون',

      svcTag:        'خدماتنا',
      svcTitle:      'ماذا',
      svcTitleEm:    'نقدم',
      svc1Title:     'شراء عقار',
      svc1Desc:      'اعثر على منزل أحلامك بفلاتر متطورة. قوائم حصرية في كردستان بأسعار واضحة.',
      svc1Cta:       'استعرض',
      svc2Title:     'بيع عقار',
      svc2Desc:      'أدرج عقارك وتواصل مع مشترين مباشرة. بدون وسطاء وبدون عمولات.',
      svc2Cta:       'أضف الآن',
      svc3Title:     'إيجار عقار',
      svc3Desc:      'ابحث عن إيجار مناسب بسعر واضح. قوائم موثقة وشروط شفافة.',
      svc3Cta:       'ابحث عن إيجار',

      appTag:        'تطبيق الجوال',
      appTitle:      'العقارات',
      appTitleEm:    'معك دائماً',
      appDesc:       'تطبيق Dream Mulk يضع سوق العقارات في كردستان بين يديك. ابحث وحدد مواعيد وتواصل مع البائعين مباشرة.',
      appF1:         'قوائم فورية وتنبيهات لحظية',
      appF2:         'احجز موعد معاينة بسهولة',
      appF3:         'الكردية والعربية والإنجليزية',
      appF4:         'مراسلة آمنة ووثائق',
      appF5:         'بدون عمولة — مجاني دائماً',
      appStoreLabel: 'حمّل من',
      playStoreLabel:'احصل عليه من',
      qrSub:         'تحميل مجاني',
      qrHint:        'وجّه الكاميرا للمسح',
      qrBtn:         'افتح في App Store',

      abtTag:        'قصتنا',
      abtTitle:      'Dream Mulk',
      abtTitleEm:    'من نحن',
      abtP1:         'بنينا Dream Mulk لجعل سوق العقارات في كردستان أبسط وأكثر شفافية. نربط المشترين بالبائعين مباشرة — بدون وسطاء وبدون عمولات.',
      abtP2:         'في سوق يتسم بالتعقيد، نجلب الوضوح والتكنولوجيا لكل صفقة.',
      abtQuote:      '"العقار أرض، لكن المُلك تاريخ. نساعدك على كتابة تاريخك."',
      val1Title:     'الحصرية',
      val1Sub:       'قوائم مختارة بعناية',
      val2Title:     'النزاهة',
      val2Sub:       'شفافية كاملة',
      val3Title:     'التكنولوجيا',
      val3Sub:       'منصة وتطبيق حديث',
      val4Title:     'مقرنا أربيل',
      val4Sub:       'تأسست ٢٠٢٦',

      rdrTag:        'لمكاتب العقارات',
      rdrTitle:      'نمّ أعمالك',
      rdrDesc:       'سجّل مكتبك وتواصل مع آلاف المشترين والمستأجرين في كردستان. إدارة القوائح والوكلاء والصفقات في مكان واحد.',
      rdrBtn1:       'دخول المكتب',
      rdrBtn2:       'تصفح بدون تسجيل',

      ftTag:         'منصة العقارات في كردستان. بدون رسوم. بدون عمولات.',
      ftCol1:        'المنصة',
      ftCol2:        'الخدمات',
      ftCol3:        'تحميل التطبيق',
      ftLink1:       'استعرض العقارات',
      ftLink2:       'دخول العملاء',
      ftLink3:       'بوابة الوكلاء',
      ftLink4:       'من نحن',
      ftLink5:       'شراء عقار',
      ftLink6:       'بيع عقار',
      ftLink7:       'إيجار عقار',
      ftLink8:       'ابحث عن وكيل',
      ftLink9:       'تواصل معنا',
      fabAgent:      'بوابة الوكيل',
      fabOffice:     'دخول المكتب',
    },
  };
}