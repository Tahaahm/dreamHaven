<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Dream Mulk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600&family=Noto+Sans+Arabic:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root{--primary:#303b97;--primary-dark:#202660;--accent:#d4af37;--text-dark:#1a1a1a;--text-light:#555;--bg-light:#f4f6fa;--white:#fff;}
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Poppins',sans-serif;background:var(--bg-light);color:var(--text-dark);overflow-x:hidden;line-height:1.7;}
        body.lang-ku,body.lang-ar{font-family:'Noto Sans Arabic',sans-serif;direction:rtl;}
        body.lang-ku .quote-box,body.lang-ar .quote-box{border-left:none;border-right:4px solid var(--accent);}
        body.lang-ku .detail-icon,body.lang-ar .detail-icon{margin-right:0;margin-left:20px;}
        body.lang-ku .narrative-text p,body.lang-ar .narrative-text p{text-align:right;}

        /* Lang switcher */
        .lang-bar{position:fixed;top:16px;right:16px;z-index:999;display:flex;gap:4px;background:rgba(255,255,255,.15);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.2);border-radius:50px;padding:4px;}
        body.lang-ku .lang-bar,body.lang-ar .lang-bar{right:auto;left:16px;}
        .lbtn{padding:6px 14px;border-radius:50px;border:none;background:transparent;color:#fff;font-size:12px;font-weight:700;cursor:pointer;transition:all .3s;letter-spacing:.5px;}
        .lbtn.active{background:var(--accent);color:var(--primary-dark);}
        .lbtn:hover:not(.active){background:rgba(255,255,255,.2);}

        @keyframes fadeInUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
        @keyframes floatShape{0%{transform:translate(0,0) rotate(0deg)}50%{transform:translate(20px,20px) rotate(5deg)}100%{transform:translate(0,0) rotate(0deg)}}

        .hero-section{position:relative;height:65vh;background:linear-gradient(135deg,var(--primary) 0%,#1a2166 100%);display:flex;align-items:center;justify-content:center;text-align:center;overflow:hidden;padding:0 20px;}
        .hero-circle{position:absolute;border-radius:50%;background:linear-gradient(45deg,rgba(255,255,255,.1),rgba(255,255,255,0));animation:floatShape 15s infinite ease-in-out;z-index:1;}
        .c1{width:500px;height:500px;top:-200px;left:-100px;}
        .c2{width:300px;height:300px;bottom:-50px;right:-50px;animation-delay:2s;}
        .hero-content{position:relative;z-index:2;max-width:900px;animation:fadeInUp 1s ease-out;}
        .hero-subtitle{color:var(--accent);text-transform:uppercase;letter-spacing:3px;font-size:13px;font-weight:600;margin-bottom:15px;display:block;}
        body.lang-ku .hero-subtitle,body.lang-ar .hero-subtitle{letter-spacing:0;}
        .hero-title{font-family:'Playfair Display',serif;font-size:4rem;color:#fff;margin-bottom:20px;line-height:1.1;}
        body.lang-ku .hero-title,body.lang-ar .hero-title{font-family:'Noto Sans Arabic',sans-serif;}
        .hero-desc{color:rgba(255,255,255,.85);font-size:1.2rem;max-width:650px;margin:0 auto;font-weight:300;}

        .main-container{max-width:1100px;margin:-120px auto 0;position:relative;z-index:10;padding:0 20px 80px;}
        .glass-card{background:rgba(255,255,255,.98);padding:70px;border-radius:20px;box-shadow:0 30px 60px rgba(48,59,151,.15);margin-bottom:60px;animation:fadeInUp 1s ease-out .3s forwards;opacity:0;position:relative;overflow:hidden;}
        .glass-card::before{content:'';position:absolute;top:0;left:0;width:100%;height:6px;background:linear-gradient(90deg,var(--primary),var(--accent));}
        .narrative-grid{display:grid;grid-template-columns:1.2fr .8fr;gap:60px;align-items:center;}
        .narrative-text h2{font-family:'Playfair Display',serif;color:var(--primary);font-size:2.5rem;margin-bottom:25px;line-height:1.2;}
        body.lang-ku .narrative-text h2,body.lang-ar .narrative-text h2{font-family:'Noto Sans Arabic',sans-serif;}
        .narrative-text p{color:var(--text-light);font-size:1.05rem;margin-bottom:20px;text-align:justify;}
        .quote-box{background:#f8f9fc;border-left:4px solid var(--accent);padding:20px 25px;margin-top:30px;font-style:italic;color:var(--primary);font-weight:500;}
        body.lang-ku .quote-box,body.lang-ar .quote-box{font-style:normal;}
        .details-list{list-style:none;}
        .detail-item{display:flex;align-items:flex-start;margin-bottom:30px;}
        .detail-icon{flex-shrink:0;width:50px;height:50px;background:rgba(48,59,151,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;color:var(--primary);font-size:20px;margin-right:20px;}
        .detail-content h4{font-size:1.1rem;color:var(--primary);margin-bottom:5px;}
        body.lang-ku .detail-content h4,body.lang-ar .detail-content h4{font-family:'Noto Sans Arabic',sans-serif;}
        .detail-content p{font-size:.9rem;color:var(--text-light);line-height:1.5;}
        body.lang-ku .detail-content p,body.lang-ar .detail-content p{font-family:'Noto Sans Arabic',sans-serif;}

        .values-section{margin-top:80px;text-align:center;}
        .values-title{font-family:'Playfair Display',serif;font-size:2.2rem;color:var(--primary);margin-bottom:50px;}
        body.lang-ku .values-title,body.lang-ar .values-title{font-family:'Noto Sans Arabic',sans-serif;}
        .values-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:30px;}
        .value-card{background:#fff;padding:40px 30px;border-radius:15px;transition:all .4s ease;box-shadow:0 10px 30px rgba(0,0,0,.03);border:1px solid rgba(0,0,0,.02);}
        .value-card:hover{transform:translateY(-10px);box-shadow:0 20px 50px rgba(48,59,151,.15);border-bottom:3px solid var(--accent);}
        .value-icon-lg{font-size:3rem;margin-bottom:25px;background:-webkit-linear-gradient(var(--primary),#667eea);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
        .value-card h3{font-size:1.3rem;color:var(--text-dark);margin-bottom:15px;}
        body.lang-ku .value-card h3,body.lang-ar .value-card h3{font-family:'Noto Sans Arabic',sans-serif;}
        .value-card p{font-size:.95rem;color:var(--text-light);}
        body.lang-ku .value-card p,body.lang-ar .value-card p{font-family:'Noto Sans Arabic',sans-serif;}
        .footer-badge{margin-top:60px;text-align:center;opacity:.6;font-size:.9rem;letter-spacing:1px;color:var(--primary);}

        @media(max-width:900px){.narrative-grid{grid-template-columns:1fr;}.hero-title{font-size:3rem;}.glass-card{padding:40px;}}
        @media(max-width:600px){.values-grid{grid-template-columns:1fr;}.hero-section{height:60vh;}.main-container{margin-top:-80px;}}
    </style>
</head>
<body>

@php $navbarStyle = 'navbar-dark'; @endphp
@include('navbar')

<!-- Lang Switcher -->
<div class="lang-bar">
    <button class="lbtn active" data-lang="ku">کو</button>
    <button class="lbtn" data-lang="en">EN</button>
    <button class="lbtn" data-lang="ar">ع</button>
</div>

<header class="hero-section">
    <div class="hero-circle c1"></div>
    <div class="hero-circle c2"></div>
    <div class="hero-content">
        <span class="hero-subtitle" data-i18n="heroSub">خانووبەرەی پریمیەم</span>
        <h1 class="hero-title">Dream Mulk</h1>
        <p class="hero-desc" data-i18n="heroDesc">ئێمە ئاستی خانووبەرە بەرز دەکەینەوە. لە هەولێر، کوردستان.</p>
    </div>
</header>

<div class="main-container">
    <div class="glass-card">
        <div class="narrative-grid">
            <div class="narrative-text">
                <h2 data-i18n="storyTitle">چیرۆکی ئێمە</h2>
                <p data-i18n="storyP1">Dream Mulk بە ئامانجێکی بەهێز دامەزراوە: بەرزکردنەوەی ئاستی خانووبەرە لە کوردستان. ئێمە تەنها ئەجێنت نین — ئێمە ئەندازیارانی چاپتەری داهاتووی تۆین.</p>
                <p data-i18n="storyP2">گەشتمان بە پابەندبوون بە تەکنەلۆژیای مۆدێرن و درووستکاری بەردەوامە. ئێمە دەزانین کە کڕینی خانوو تەنها مامەڵە نییە — بنەمای میراتەکەتە.</p>
                <div class="quote-box">
                    <span data-i18n="quote">«خانوو زەوییە، بەڵام "مولک" میراتە. یارمەتیت دەدەین کە میراتەکەت بنیادبنێیت.»</span>
                </div>
            </div>
            <div class="side-details">
                <ul class="details-list">
                    <li class="detail-item">
                        <div class="detail-icon"><i class="fas fa-calendar-check"></i></div>
                        <div class="detail-content">
                            <h4 data-i18n="d1Title">دامەزراوە</h4>
                            <p data-i18n="d1Val">نیسان ٢٠٢٦</p>
                        </div>
                    </li>
                    <li class="detail-item">
                        <div class="detail-icon"><i class="fas fa-map-marked"></i></div>
                        <div class="detail-content">
                            <h4 data-i18n="d2Title">بارەگا</h4>
                            <p data-i18n="d2Val">هەولێر، کوردستان</p>
                        </div>
                    </li>
                    <li class="detail-item">
                        <div class="detail-icon"><i class="fas fa-globe"></i></div>
                        <div class="detail-content">
                            <h4 data-i18n="d3Title">ئامانج</h4>
                            <p data-i18n="d3Val">بوونە باشترین ئیکۆسیستەمی خانووبەرەی عێراق.</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <section class="values-section">
        <h2 class="values-title" data-i18n="valTitle">ستانداردی Dream Mulk</h2>
        <div class="values-grid">
            <div class="value-card">
                <i class="fas fa-crown value-icon-lg"></i>
                <h3 data-i18n="v1Title">تایبەتمەندی</h3>
                <p data-i18n="v1Desc">دەسترسی بە پۆرتفۆلیۆیەکی هەڵبژێردراو کە پێواستەکانی کوالیتی و شوێنی ئێمە پێی دەگات.</p>
            </div>
            <div class="value-card">
                <i class="fas fa-laptop-code value-icon-lg"></i>
                <h3 data-i18n="v2Title">نوێکاری</h3>
                <p data-i18n="v2Desc">بەکارهێنانی تەکنەلۆژیای دیجیتاڵی نوێ بۆ ئەوەی گەشتی کڕین و فرۆشتنت ئاسانتر و ئاشکراتر بکات.</p>
            </div>
            <div class="value-card">
                <i class="fas fa-hand-holding-heart value-icon-lg"></i>
                <h3 data-i18n="v3Title">درووستکاری</h3>
                <p data-i18n="v3Desc">ئێمە باوەڕ بە ئاشکرایی تەواو دروست دەکەین. هیچ نهێنییەک نییە، تەنها ئامۆژگارییەکی راست و پسپۆری.</p>
            </div>
        </div>
    </section>

    <div class="footer-badge">© 2026 DREAM MULK REAL ESTATE GROUP</div>
</div>

<script>
const T = {
    ku:{
        dir:'rtl',
        heroSub:'خانووبەرەی پریمیەم',
        heroDesc:'ئێمە ئاستی خانووبەرە بەرز دەکەینەوە. لە هەولێر، کوردستان.',
        storyTitle:'چیرۆکی ئێمە',
        storyP1:'Dream Mulk بە ئامانجێکی بەهێز دامەزراوە: بەرزکردنەوەی ئاستی خانووبەرە لە کوردستان. ئێمە تەنها ئەجێنت نین — ئێمە ئەندازیارانی چاپتەری داهاتووی تۆین.',
        storyP2:'گەشتمان بە پابەندبوون بە تەکنەلۆژیای مۆدێرن و درووستکاری بەردەوامە. ئێمە دەزانین کە کڕینی خانوو تەنها مامەڵە نییە — بنەمای میراتەکەتە.',
        quote:'«خانوو زەوییە، بەڵام "مولک" میراتە. یارمەتیت دەدەین کە میراتەکەت بنیادبنێیت.»',
        d1Title:'دامەزراوە', d1Val:'نیسان ٢٠٢٦',
        d2Title:'بارەگا', d2Val:'هەولێر، کوردستان',
        d3Title:'ئامانج', d3Val:'بوونە باشترین ئیکۆسیستەمی خانووبەرەی عێراق.',
        valTitle:'ستانداردی Dream Mulk',
        v1Title:'تایبەتمەندی', v1Desc:'دەسترسی بە پۆرتفۆلیۆیەکی هەڵبژێردراو کە پێواستەکانی کوالیتی و شوێنی ئێمە پێی دەگات.',
        v2Title:'نوێکاری', v2Desc:'بەکارهێنانی تەکنەلۆژیای دیجیتاڵی نوێ بۆ ئەوەی گەشتی کڕین و فرۆشتنت ئاسانتر و ئاشکراتر بکات.',
        v3Title:'درووستکاری', v3Desc:'ئێمە باوەڕ بە ئاشکرایی تەواو دروست دەکەین. هیچ نهێنییەک نییە، تەنها ئامۆژگارییەکی راست و پسپۆری.',
    },
    en:{
        dir:'ltr',
        heroSub:'Premium Real Estate Solutions',
        heroDesc:'Redefining ownership. Where architectural brilliance meets your future legacy in Erbil.',
        storyTitle:'The Evolution of Living',
        storyP1:'Dream Mulk was established with a singular, powerful ambition: to elevate the standard of real estate in Kurdistan. We are not merely agents — we are the architects of your next chapter.',
        storyP2:'Our journey is fueled by a commitment to modern technology and timeless integrity. We understand that acquiring property is not just a transaction — it is the foundation of your heritage.',
        quote:'"Property is land, but \'Mulk\' is legacy. We help you build yours."',
        d1Title:'Established', d1Val:'April 2026',
        d2Title:'Headquarters', d2Val:'Erbil, Kurdistan Region',
        d3Title:'Vision', d3Val:'To become the most trusted real estate ecosystem in Iraq.',
        valTitle:'The Dream Mulk Standard',
        v1Title:'Exclusivity', v1Desc:'Access to a curated portfolio of properties that meet our rigorous standards for quality and location.',
        v2Title:'Innovation', v2Desc:'Utilizing state-of-the-art digital tools to make your buying or selling journey seamless and transparent.',
        v3Title:'Integrity', v3Desc:'We build trust through radical transparency. No hidden details, just honest, expert advice.',
    },
    ar:{
        dir:'rtl',
        heroSub:'حلول عقارية متميزة',
        heroDesc:'نعيد تعريف مفهوم التملك. حيث يلتقي الإبداع المعماري بإرثك المستقبلي في أربيل.',
        storyTitle:'تطور أسلوب الحياة',
        storyP1:'تأسست Dream Mulk بطموح واحد قوي: رفع مستوى سوق العقارات في كردستان. نحن لسنا مجرد وكلاء — نحن مهندسو فصلك القادم.',
        storyP2:'رحلتنا مدفوعة بالتزام بالتكنولوجيا الحديثة والنزاهة الراسخة. نفهم أن شراء العقار ليس مجرد صفقة — بل هو أساس إرثك.',
        quote:'"العقار أرض، لكن المُلك إرث. نساعدك على بنائه."',
        d1Title:'تأسست', d1Val:'أبريل 2026',
        d2Title:'المقر الرئيسي', d2Val:'أربيل، إقليم كردستان',
        d3Title:'الرؤية', d3Val:'أن نصبح أكثر منظومة عقارية موثوقة في العراق.',
        valTitle:'معيار Dream Mulk',
        v1Title:'الحصرية', v1Desc:'الوصول إلى محفظة مختارة بعناية من العقارات التي تلبي معاييرنا الصارمة للجودة والموقع.',
        v2Title:'الابتكار', v2Desc:'استخدام أحدث الأدوات الرقمية لجعل رحلة الشراء أو البيع سلسة وشفافة.',
        v3Title:'النزاهة', v3Desc:'نبني الثقة من خلال الشفافية التامة. لا تفاصيل مخفية، فقط نصائح صادقة ومتخصصة.',
    }
};

function setLang(lang){
    const L=T[lang];
    document.documentElement.dir=L.dir;
    document.body.classList.remove('lang-ku','lang-en','lang-ar','rtl');
    document.body.classList.add('lang-'+lang);
    if(L.dir==='rtl') document.body.classList.add('rtl');
    document.querySelectorAll('[data-i18n]').forEach(el=>{
        const k=el.getAttribute('data-i18n');
        if(L[k]!==undefined) el.textContent=L[k];
    });
    document.querySelectorAll('.lbtn').forEach(b=>{
        b.classList.toggle('active',b.getAttribute('data-lang')===lang);
    });
    localStorage.setItem('dm_lang',lang);
}

document.querySelectorAll('.lbtn').forEach(b=>{
    b.addEventListener('click',()=>setLang(b.getAttribute('data-lang')));
});
setLang(localStorage.getItem('dm_lang')||'ku');
</script>
</body>
</html>