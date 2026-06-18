<!DOCTYPE html>
<html lang="ku">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Dream Mulk — خانووەکان</title>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.imagesloaded/4.1.4/imagesloaded.pkgd.min.js"></script>
<script src="{{ asset('assets/vendor/isotope-layout/isotope.pkgd.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@studio-freight/lenis@1.0.42/dist/lenis.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
:root{
  --blue:#1A225A; --blue-mid:#2a3298; --blue-light:#eef0f9; --blue-pale:#f4f5fb;
  --gold:#C0A062; --gold-lt:#d4b97a; --gold-pale:#fdf6e8;
  --white:#fff; --bg:#f5f6fa; --border:#e8eaf2;
  --text:#0f1225; --text-2:#4a5080; --text-3:#8b91b8;
  --shadow-sm:0 2px 8px rgba(26,34,90,0.07);
  --shadow-md:0 8px 28px rgba(26,34,90,0.10);
  --shadow-lg:0 20px 60px rgba(26,34,90,0.13);
  --ease:cubic-bezier(0.22,1,0.36,1);
  --font:'Vazirmatn',sans-serif;
  --sw:300px;
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;outline:none;}
html{scroll-behavior:auto;}
body{font-family:var(--font);background:var(--bg);color:var(--text);overflow-x:hidden;-webkit-font-smoothing:antialiased;direction:rtl;}
a{text-decoration:none;color:inherit;}
img{display:block;}
i[class*="fa"]{font-family:"Font Awesome 6 Free","Font Awesome 6 Brands"!important;font-style:normal!important;}
::-webkit-scrollbar{width:5px;}::-webkit-scrollbar-track{background:var(--bg);}::-webkit-scrollbar-thumb{background:var(--border);border-radius:3px;}

/* RTL / LTR */
body.lang-en{direction:ltr;}
body.lang-ku .sb,body.lang-ar .sb{left:auto;right:0;border-right:none;border-left:1px solid var(--border);}
body.lang-ku .main,body.lang-ar .main{margin-left:0;margin-right:var(--sw);}
body.lang-en .sb{left:0;right:auto;border-left:none;border-right:1px solid var(--border);}
body.lang-en .main{margin-left:var(--sw);margin-right:0;}
body.lang-ku .sel-wrap::after,body.lang-ar .sel-wrap::after{right:auto;left:14px;}
body.lang-en .cta-arr i{transform:scaleX(-1);}
@media(max-width:1024px){
  body.lang-ku .sb,body.lang-ar .sb{transform:translateX(100%);}
  body.lang-ku .sb.open,body.lang-ar .sb.open{transform:translateX(0);}
  body.lang-ku .main,body.lang-ar .main{margin-right:0;}
  body.lang-en .sb{transform:translateX(-100%);}
  body.lang-en .sb.open{transform:translateX(0);}
  body.lang-en .main{margin-left:0;}
}

/* ── NAVBAR ── */
.navbar{
  position:fixed;top:0;left:0;right:0;z-index:200;height:68px;
  background:rgba(255,255,255,0.96);backdrop-filter:blur(20px);
  border-bottom:1px solid var(--border);box-shadow:var(--shadow-sm);
  display:flex;align-items:center;padding:0 36px;
}
.nb-inner{max-width:1600px;width:100%;margin:0 auto;display:flex;align-items:center;justify-content:space-between;gap:14px;}
.logo{display:flex;align-items:center;gap:10px;text-decoration:none;}
.logo-img{width:34px;height:34px;border-radius:9px;object-fit:contain;background:var(--white);border:1.5px solid var(--border);padding:3px;}
.logo-mark{width:34px;height:34px;border-radius:9px;background:var(--blue);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:900;color:var(--gold);flex-shrink:0;}
.logo-name{font-size:16px;font-weight:800;color:var(--blue);}
.nb-right{display:flex;align-items:center;gap:10px;flex-shrink:0;}
.lang-sw{display:flex;gap:2px;background:var(--blue-pale);border:1px solid var(--border);border-radius:10px;padding:3px;}
.lang-btn{padding:5px 11px;border-radius:7px;border:none;background:transparent;color:var(--text-3);font-size:12px;font-weight:700;cursor:pointer;transition:all .22s;font-family:var(--font)!important;}
.lang-btn.active{background:var(--blue);color:#fff;}
.lang-btn:hover:not(.active){color:var(--blue);}
.count-pill{display:flex;align-items:center;gap:8px;background:var(--white);border:1px solid var(--border);border-radius:100px;padding:7px 18px;font-size:13px;color:var(--text-3);font-weight:500;box-shadow:var(--shadow-sm);}
.count-pill strong{color:var(--blue);font-weight:800;font-size:15px;}
.count-dot{width:7px;height:7px;border-radius:50%;background:var(--gold);flex-shrink:0;box-shadow:0 0 0 3px rgba(192,160,98,0.2);}

/* ── LAYOUT ── */
.wrap{display:flex;min-height:100vh;padding-top:68px;}

/* ── SIDEBAR ── */
.sb{
  width:var(--sw);position:fixed;top:68px;right:0;bottom:0;
  background:var(--white);border-left:1px solid var(--border);
  z-index:50;overflow-y:auto;overflow-x:hidden;
  transition:transform .4s var(--ease);scrollbar-width:none;
}
.sb::-webkit-scrollbar{display:none;}
.sb-accent{height:3px;background:linear-gradient(270deg,var(--blue) 0%,var(--gold) 60%,var(--gold-lt) 100%);}
body.lang-en .sb-accent{background:linear-gradient(90deg,var(--blue) 0%,var(--gold) 60%,var(--gold-lt) 100%);}
.sb-hd{padding:22px 20px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px;}
.sb-hd-ico{width:38px;height:38px;border-radius:10px;background:var(--blue-light);border:1px solid rgba(26,34,90,0.12);display:flex;align-items:center;justify-content:center;color:var(--blue-mid);font-size:14px;flex-shrink:0;}
.sb-hd-title{font-size:15px;font-weight:700;color:var(--text);}
.sb-hd-sub{font-size:11.5px;color:var(--text-3);margin-top:2px;}
.sb-body{padding:20px;}
.fg{margin-bottom:16px;}
.fg-lbl{display:block;font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--blue-mid);margin-bottom:6px;}
body.lang-ku .fg-lbl,body.lang-ar .fg-lbl{letter-spacing:0;font-size:12px;text-transform:none;}
.fc{width:100%;padding:11px 13px;font-family:var(--font);font-size:13.5px;color:var(--text);background:var(--bg);border:1px solid var(--border);border-radius:10px;transition:all .22s var(--ease);appearance:none;-webkit-appearance:none;}
.fc:focus{border-color:var(--blue-mid);background:var(--white);box-shadow:0 0 0 3px rgba(26,34,90,0.07);}
.fc::placeholder{color:var(--text-3);}
.sel-wrap{position:relative;}
.sel-wrap::after{content:'\f107';font-family:'Font Awesome 6 Free';font-weight:900;position:absolute;right:13px;top:50%;transform:translateY(-50%);color:var(--blue-mid);font-size:11px;pointer-events:none;}
body.lang-en .sel-wrap::after{right:auto;left:13px;}
.sel-wrap select{padding-right:32px;cursor:pointer;}
body.lang-en .sel-wrap select{padding-right:13px;padding-left:32px;}
.two-col{display:grid;grid-template-columns:1fr 1fr;gap:8px;}
.price-hint{font-size:11px;color:var(--text-3);margin-top:6px;display:flex;align-items:center;gap:5px;}
.price-hint i{color:var(--gold);font-size:10px;}
.sb-divider{height:1px;background:var(--border);margin:16px 0;}
.active-filters{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px;min-height:0;}
.af-tag{display:inline-flex;align-items:center;gap:6px;padding:5px 11px;background:var(--blue-light);border:1px solid rgba(26,34,90,0.12);border-radius:100px;font-size:11.5px;font-weight:600;color:var(--blue-mid);}
.af-tag button{background:none;border:none;color:var(--blue-mid);cursor:pointer;font-size:10px;opacity:.55;padding:0;}
.af-tag button:hover{opacity:1;}
.btn-apply{width:100%;padding:12px;border-radius:11px;border:none;background:var(--blue);color:#fff;font-size:13.5px;font-weight:700;font-family:var(--font);cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:all .28s var(--ease);margin-bottom:9px;box-shadow:0 4px 16px rgba(26,34,90,0.22);}
.btn-apply:hover{background:var(--blue-mid);transform:translateY(-2px);box-shadow:0 8px 22px rgba(26,34,90,0.32);}
.btn-reset{width:100%;padding:11px;border-radius:11px;background:var(--white);border:1.5px solid var(--border);font-size:13.5px;font-weight:600;font-family:var(--font);color:var(--text-3);cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px;transition:all .22s var(--ease);}
.btn-reset:hover{border-color:var(--blue);color:var(--blue);background:var(--blue-pale);}

/* ── MAIN ── */
.main{flex:1;margin-right:var(--sw);padding:32px 32px 80px;min-width:0;}

/* Page header */
.pg-head{display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:14px;margin-bottom:32px;}
.pg-tag{font-size:11px;font-weight:700;color:var(--gold);display:inline-block;background:var(--gold-pale);border:1px solid rgba(192,160,98,0.25);padding:4px 13px;border-radius:100px;margin-bottom:8px;}
body.lang-ku .pg-tag,body.lang-ar .pg-tag,body.lang-en .pg-tag{letter-spacing:0;}
.pg-title{font-size:clamp(22px,3vw,36px);font-weight:900;color:var(--text);line-height:1.15;}
.pg-title em{font-style:normal;color:var(--blue-mid);}

/* ── GRID ── */
.grid{display:block;width:100%;min-height:200px;margin:0 -9px;position:relative;}
.grid::after{content:'';display:table;clear:both;}
.pc{width:33.333%;padding:0 9px;margin-bottom:20px;float:right;box-sizing:border-box;}
body.lang-en .pc{float:left;}

/* ── CARD ── */
.card{
  background:var(--white);border-radius:18px;border:1px solid var(--border);
  overflow:hidden;display:flex;flex-direction:column;
  transition:transform .35s var(--ease),box-shadow .35s var(--ease),border-color .35s;
  position:relative;cursor:pointer;height:100%;
  box-shadow:var(--shadow-sm);text-decoration:none;color:inherit;
}
.card:hover{transform:translateY(-7px);border-color:rgba(26,34,90,0.15);box-shadow:var(--shadow-lg);}
.card::after{content:'';position:absolute;bottom:0;left:0;right:0;height:3px;z-index:4;background:linear-gradient(90deg,var(--blue),var(--gold));transform:scaleX(0);transform-origin:right;transition:transform .4s var(--ease);}
body.lang-en .card::after{transform-origin:left;}
.card:hover::after{transform:scaleX(1);}

/* Card image */
.ci{position:relative;height:220px;overflow:hidden;flex-shrink:0;}
.ci-bg{position:absolute;inset:0;background-size:cover;background-position:center;transition:transform .7s var(--ease);}
.card:hover .ci-bg{transform:scale(1.06);}
.ci::after{content:'';position:absolute;inset:0;background:linear-gradient(to top,rgba(10,14,45,0.85) 0%,rgba(10,14,45,0.1) 40%,transparent 100%);z-index:1;}
.ci-badges{position:absolute;top:12px;right:12px;left:12px;display:flex;justify-content:space-between;align-items:flex-start;z-index:3;}
body.lang-en .ci-badges{right:12px;left:12px;}
.badge{font-size:9.5px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;padding:5px 12px;border-radius:100px;backdrop-filter:blur(10px);font-family:var(--font)!important;}
.badge-type{background:rgba(26,34,90,0.85);color:#fff;border:1px solid rgba(255,255,255,0.18);}
.badge-sell{background:rgba(255,255,255,0.93);color:var(--blue);}
.badge-rent{background:var(--gold);color:var(--blue);}
.ci-price{position:absolute;bottom:12px;right:12px;left:12px;z-index:3;direction:ltr;}
.ci-price-cur{font-size:9px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,0.6);margin-bottom:1px;}
.ci-price-num{font-size:24px;font-weight:900;color:#fff;line-height:1;text-shadow:0 2px 10px rgba(0,0,0,0.4);display:inline-block;font-family:var(--font)!important;}

/* Card body */
.cb{padding:18px 18px 0;flex:1;display:flex;flex-direction:column;}
.cb-title{font-size:15px;font-weight:700;color:var(--text);line-height:1.35;margin-bottom:6px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical;transition:color .22s;}
.card:hover .cb-title{color:var(--blue);}
.cb-loc{font-size:12px;color:var(--text-3);font-weight:500;display:flex;align-items:center;gap:5px;margin-bottom:14px;}
.cb-loc i{color:var(--gold);font-size:10px;}
.cb-feats{display:flex;gap:6px;margin-top:auto;padding-bottom:16px;}
.feat{flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;padding:10px 5px;background:var(--bg);border-radius:10px;border:1px solid var(--border);transition:all .22s var(--ease);}
.card:hover .feat{background:var(--blue-light);border-color:rgba(26,34,90,0.12);}
.feat i{font-size:12px;color:var(--blue-mid);}
.feat-v{font-size:15px;font-weight:800;color:var(--text);line-height:1;font-family:var(--font)!important;}
.feat-l{font-size:9px;font-weight:600;color:var(--text-3);letter-spacing:.3px;}
body.lang-ku .feat-l,body.lang-ar .feat-l{font-size:10.5px;}

/* CTA row */
.cta{padding:0 18px 16px;}
.cta-btn{display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-radius:10px;background:var(--bg);border:1px solid var(--border);font-size:13px;font-weight:600;color:var(--blue);transition:all .25s var(--ease);}
.card:hover .cta-btn{background:var(--blue);color:#fff;border-color:var(--blue);box-shadow:0 4px 14px rgba(26,34,90,0.2);}
.cta-arr{width:26px;height:26px;border-radius:7px;background:var(--blue-light);display:flex;align-items:center;justify-content:center;font-size:10px;transition:all .25s;}
.card:hover .cta-arr{background:rgba(255,255,255,0.2);}

/* ── EMPTY STATE ── */
.empty-state{clear:both;display:none;width:100%;padding:64px 40px;text-align:center;background:var(--white);border-radius:18px;border:1.5px dashed var(--border);margin-top:8px;}
.empty-state.visible{display:block;}
.empty-ic{width:68px;height:68px;border-radius:50%;background:var(--blue-light);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:24px;color:var(--blue-mid);}
.empty-state h3{font-size:20px;font-weight:800;color:var(--text);margin-bottom:7px;}
.empty-state p{font-size:13.5px;color:var(--text-3);margin-bottom:20px;}
.empty-reset{display:inline-flex;align-items:center;gap:7px;padding:10px 24px;background:var(--blue);color:#fff;border-radius:100px;font-size:13.5px;font-weight:700;border:none;cursor:pointer;transition:all .25s;font-family:var(--font);}
.empty-reset:hover{background:var(--blue-mid);transform:translateY(-2px);}

/* ── PAGINATION ── */
.pgn{clear:both;padding-top:48px;display:flex;flex-direction:column;align-items:center;gap:12px;}
.pgn-info{font-size:13px;color:var(--text-3);font-weight:500;}
.pgn-info strong{color:var(--blue);font-weight:700;}
.pgn nav>div.sm\:hidden{display:none!important;}
.pgn nav>div>div:first-child{display:none!important;}
.pgn nav>div>div:last-child>span,.pgn nav ul.pagination{display:inline-flex!important;align-items:center;justify-content:center;gap:7px!important;box-shadow:none!important;margin:0!important;padding:0!important;}
.pgn nav a.relative,.pgn nav span[aria-current]>span,.pgn nav span[aria-disabled]>span,.pgn nav li>a,.pgn nav li>span{display:flex!important;align-items:center!important;justify-content:center!important;width:40px!important;height:40px!important;border-radius:10px!important;background:var(--white)!important;border:1px solid var(--border)!important;color:var(--text-3)!important;font-size:14px!important;font-weight:600!important;text-decoration:none!important;transition:all .22s!important;}
.pgn nav a.relative:hover,.pgn nav li>a:hover{border-color:var(--blue)!important;color:var(--blue)!important;background:var(--blue-pale)!important;transform:translateY(-2px)!important;}
.pgn nav span[aria-current="page"]>span{background:var(--blue)!important;color:#fff!important;border-color:transparent!important;box-shadow:0 4px 14px rgba(26,34,90,0.25)!important;}
.pgn nav span[aria-disabled="true"]>span{background:var(--bg)!important;color:var(--text-3)!important;cursor:not-allowed!important;border-color:transparent!important;}
.pgn nav svg{width:15px!important;height:15px!important;display:block!important;}

/* ── MOBILE FILTER BTN ── */
.mob-btn{display:none;position:fixed;bottom:22px;left:50%;transform:translateX(-50%);padding:12px 26px;background:var(--blue);color:#fff;border-radius:100px;font-size:13.5px;font-weight:700;align-items:center;gap:9px;z-index:100;border:none;cursor:pointer;box-shadow:0 8px 28px rgba(26,34,90,0.35);font-family:var(--font);transition:background .25s,transform .25s;}
.mob-btn:hover{background:var(--blue-mid);transform:translateX(-50%) translateY(-2px);}
.mob-dot{width:7px;height:7px;border-radius:50%;background:var(--gold);flex-shrink:0;}
.sb-overlay{position:fixed;inset:0;background:rgba(26,34,90,0.4);z-index:30;display:none;backdrop-filter:blur(5px);}

/* ── RESPONSIVE ── */
@media(max-width:1280px){.pc{width:50%;}}
@media(max-width:1024px){
  .sb{transform:translateX(100%);}
  .sb.open{transform:translateX(0);box-shadow:0 0 60px rgba(0,0,0,.3);}
  .main{margin-right:0;margin-left:0;padding:24px 18px 100px;}
  .mob-btn{display:flex;}
  .navbar{padding:0 18px;}
}
@media(max-width:768px){.pg-head{margin-bottom:20px;}}
@media(max-width:600px){.pc{width:100%;padding:0;}.grid{margin:0;}}
@media(max-width:380px){.nb-right .count-pill{display:none;}}
</style>
</head>
<body class="lang-ku">

<!-- NAVBAR -->
<nav class="navbar">
  <div class="nb-inner">
    <a href="{{ route('newindex') }}" class="logo">
      <img src="{{ asset('favicon.ico') }}" alt="Dream Mulk" class="logo-img"
        onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
      <div class="logo-mark" style="display:none;">M</div>
      <span class="logo-name">Dream Mulk</span>
    </a>
    <div class="nb-right">
      <div class="lang-sw">
        <button class="lang-btn active" data-lang="ku">کو</button>
        <button class="lang-btn" data-lang="en">EN</button>
        <button class="lang-btn" data-lang="ar">ع</button>
      </div>
      <div class="count-pill">
        <div class="count-dot"></div>
        <strong id="results-counter">{{ $properties->total() }}</strong>
        &nbsp;<span data-i18n="propLabel">خانوو</span>
      </div>
    </div>
  </div>
</nav>

<div class="wrap">

<!-- SIDEBAR -->
<aside class="sb" id="sb">
  <div class="sb-accent"></div>
  <div class="sb-hd">
    <div class="sb-hd-ico"><i class="fas fa-sliders-h"></i></div>
    <div>
      <div class="sb-hd-title" data-i18n="sbTitle">فلتەر</div>
      <div class="sb-hd-sub" data-i18n="sbSub">گەڕانەکەت باشتر بکە</div>
    </div>
  </div>
  <div class="sb-body">
    <div class="fg">
      <label class="fg-lbl" data-i18n="lbType">جۆری لیست</label>
      <div class="sel-wrap">
        <select id="property-type-dropdown" class="fc">
          <option value="" data-i18n="optAll">کڕین یان کرێ</option>
          <option value="sell" data-i18n="optBuy">کڕین</option>
          <option value="rent" data-i18n="optRent">کرێ</option>
        </select>
      </div>
    </div>
    <div class="fg">
      <label class="fg-lbl" data-i18n="lbCity">شار</label>
      <div class="sel-wrap">
        <select id="city-dropdown" class="fc"><option value="">...</option></select>
      </div>
    </div>
    <div class="fg">
      <label class="fg-lbl" data-i18n="lbArea">ناوچە</label>
      <div class="sel-wrap">
        <select id="area-dropdown" class="fc" disabled>
          <option value="" data-i18n="optAreaFirst">یەکەم شار هەڵبژێرە</option>
        </select>
      </div>
    </div>
    <div class="fg">
      <label class="fg-lbl" data-i18n="lbPurpose">جۆری خانوو</label>
      <div class="sel-wrap">
        <select id="purpose-dropdown" class="fc">
          <option value="" data-i18n="optAllTypes">هەموو جۆرەکان</option>
          <option value="villa" data-i18n="optVilla">ڤیلا</option>
          <option value="house" data-i18n="optHouse">خانوو</option>
          <option value="apartment" data-i18n="optApart">ئەپارتمان</option>
          <option value="commercial" data-i18n="optComm">بازرگانی</option>
        </select>
      </div>
    </div>
    <div class="fg">
      <label class="fg-lbl" data-i18n="lbKeyword">کلیلەوشە</label>
      <input type="text" id="search-keywords-input" class="fc" placeholder="پووڵ، باخچە..." data-ph-i18n="phKeyword"/>
    </div>
    <div class="fg">
      <label class="fg-lbl" data-i18n="lbPrice">نرخ (USD)</label>
      <div class="two-col">
        <input type="number" id="min-price-input" class="fc" placeholder="$ کەمترین" data-ph-i18n="phMin"/>
        <input type="number" id="max-price-input" class="fc" placeholder="$ زۆرترین" data-ph-i18n="phMax"/>
      </div>
      <div class="price-hint"><i class="fas fa-dollar-sign"></i><span data-i18n="priceHint">نرخەکان بە دۆلاری ئەمریکی</span></div>
    </div>
    <div class="sb-divider"></div>
    <div class="active-filters" id="activeFilterTags"></div>
    <button class="btn-apply" id="search-button">
      <i class="fas fa-search"></i><span data-i18n="btnApply">فلتەر جێبەجێ بکە</span>
    </button>
    <button class="btn-reset" id="clear-filters">
      <i class="fas fa-rotate-left"></i><span data-i18n="btnReset">پاکی بکەرەوە</span>
    </button>
  </div>
</aside>

<!-- MAIN -->
<main class="main">
  <div class="pg-head">
    <div>
      <div class="pg-tag" data-i18n="pgTag">خانووبەرەی کوردستان</div>
      <div class="pg-title">Dream Mulk — <em data-i18n="pgTitleEm">خانووەکان</em></div>
    </div>
  </div>

  <!-- GRID -->
  <div class="grid" id="propertiesGrid">
    @foreach($properties as $property)
    @php
      $priceUsd = $property->price['usd'] ?? 0;
      $priceIqd = $property->price['iqd'] ?? ($priceUsd * 1300);
      $lt  = strtolower($property->listing_type ?? '');
      $cat = strtolower($property->type['category'] ?? '');
    @endphp
    <div class="pc"
      data-type="{{ $cat }}"
      data-listing="{{ $lt }}"
      data-price-usd="{{ $priceUsd }}"
      data-price-iqd="{{ $priceIqd }}"
      data-date="{{ $property->created_at->timestamp }}"
    >
      <div class="pc-inner">
        <a href="{{ route('property.PropertyDetail', ['property_id' => $property->id]) }}" class="card">
          <div class="ci">
            <div class="ci-bg" style="background-image:url('{{ !empty($property->images) ? $property->images[0] : asset('property_images/default-property.jpg') }}')"></div>
            <div class="ci-badges">
              <span class="badge badge-type">{{ ucfirst($cat ?: 'Property') }}</span>
              <span class="badge {{ $lt === 'rent' ? 'badge-rent' : 'badge-sell' }}">{{ ucfirst($lt ?: 'N/A') }}</span>
            </div>
            <div class="ci-price">
              <div class="ci-price-cur">USD</div>
              <div class="ci-price-num price-display" data-usd="{{ $priceUsd }}" data-iqd="{{ $priceIqd }}">${{ number_format($priceUsd) }}</div>
            </div>
          </div>
          <div class="cb">
            <div class="cb-title">{{ $property->name['ku'] ?? $property->name['en'] ?? $property->name ?? 'خانوو' }}</div>
            <div class="cb-loc">
              <i class="fas fa-location-dot"></i>
              {{ $property->address ?? ($property->address_details['city']['ku'] ?? $property->address_details['city']['en'] ?? 'کوردستان') }}
            </div>
            <div class="cb-feats">
              <div class="feat"><i class="fas fa-bed"></i><span class="feat-v">{{ $property->rooms['bedroom']['count'] ?? 0 }}</span><span class="feat-l" data-i18n="featBeds">جێخەو</span></div>
              <div class="feat"><i class="fas fa-bath"></i><span class="feat-v">{{ $property->rooms['bathroom']['count'] ?? 0 }}</span><span class="feat-l" data-i18n="featBaths">حەمام</span></div>
              <div class="feat"><i class="fas fa-ruler-combined"></i><span class="feat-v">{{ $property->area ?? '—' }}</span><span class="feat-l">m²</span></div>
            </div>
          </div>
          <div class="cta">
            <div class="cta-btn">
              <span data-i18n="ctaView">وردەکاریەکان</span>
              <span class="cta-arr"><i class="fas fa-arrow-left"></i></span>
            </div>
          </div>
        </a>
      </div>
    </div>
    @endforeach
  </div>

  <div class="empty-state" id="emptyState">
    <div class="empty-ic"><i class="fas fa-house-circle-xmark"></i></div>
    <h3 data-i18n="emptyTitle">هیچ خانوویەک نەدۆزراوەتەوە</h3>
    <p data-i18n="emptyDesc">فلتەرەکانت بگۆڕە بۆ دۆزینەوەی خانووی گونجاو.</p>
    <button class="empty-reset" id="emptyResetBtn"><i class="fas fa-rotate-left"></i><span data-i18n="btnReset">پاکی بکەرەوە</span></button>
  </div>
  @if($properties->count() === 0)
  <script>document.getElementById('emptyState').classList.add('visible');</script>
  @endif

  <div class="pgn">
    <div class="pgn-info">
      <span data-i18n="pgPage">لاپەڕە</span>&nbsp;<strong>{{ $properties->currentPage() }}</strong>&nbsp;<span data-i18n="pgOf">لە</span>&nbsp;<strong>{{ $properties->lastPage() }}</strong>
      &nbsp;·&nbsp;<strong>{{ $properties->total() }}</strong>&nbsp;<span data-i18n="pgTotal">خانوو بەکۆی هەموو</span>
    </div>
    {{ $properties->links() }}
  </div>
</main>

<button class="mob-btn" id="mobBtn"><span class="mob-dot"></span><i class="fas fa-sliders-h"></i><span data-i18n="mobFilter">فلتەرەکان</span></button>
<div class="sb-overlay" id="sbOverlay"></div>
</div>

<!-- I18N -->
<script>
class DreamMulkI18n {
  constructor(o={}){this.storageKey=o.storageKey||'dm_lang';this.defaultLang=o.defaultLang||'ku';this.onLangChange=o.onLangChange||null;this._current=this.defaultLang;}
  init(){const s=localStorage.getItem(this.storageKey)||this.defaultLang;this.setLang(s);}
  setLang(lang){
    if(!this.translations[lang])return;
    this._current=lang;localStorage.setItem(this.storageKey,lang);
    const T=this.translations[lang];
    document.body.dir=T.dir;document.documentElement.lang=lang;
    document.body.classList.remove('lang-ku','lang-en','lang-ar','rtl');
    document.body.classList.add('lang-'+lang);
    if(T.dir==='rtl')document.body.classList.add('rtl');
    document.querySelectorAll('.lang-btn').forEach(b=>b.classList.toggle('active',b.getAttribute('data-lang')===lang));
    document.querySelectorAll('[data-i18n]').forEach(el=>{const k=el.getAttribute('data-i18n');if(T[k]!==undefined)el.textContent=T[k];});
    document.querySelectorAll('[data-ph-i18n]').forEach(el=>{const k=el.getAttribute('data-ph-i18n');if(T[k]!==undefined)el.placeholder=T[k];});
    const typeSel=document.getElementById('property-type-dropdown');
    if(typeSel){const map={'':'optAll','sell':'optBuy','rent':'optRent'};typeSel.querySelectorAll('option').forEach(o=>{const k=map[o.value];if(k&&T[k])o.textContent=T[k];});}
    // Arrow direction
    document.querySelectorAll('.cta-arr i').forEach(i=>{i.className=T.dir==='rtl'?'fas fa-arrow-left':'fas fa-arrow-right';});
    // Card titles — show correct language
    document.querySelectorAll('.cb-title[data-ku]').forEach(el=>{const v=el.getAttribute('data-'+lang)||el.getAttribute('data-en')||el.getAttribute('data-ku');if(v)el.textContent=v;});
    document.querySelectorAll('.cb-loc[data-ku]').forEach(el=>{const span=el.querySelector('span');if(span){const v=el.getAttribute('data-'+lang)||el.getAttribute('data-en')||el.getAttribute('data-ku');if(v)span.textContent=v;}});
    if(typeof this.onLangChange==='function')this.onLangChange(lang,T);
  }
  getCurrentLang(){return this._current;}
  t(key){return(this.translations[this._current]||{})[key]||key;}
  translations={
    ku:{dir:'rtl',sbTitle:'فلتەر',sbSub:'گەڕانەکەت باشتر بکە',lbType:'جۆری لیست',lbCity:'شار',lbArea:'ناوچە',lbPurpose:'جۆری خانوو',lbKeyword:'کلیلەوشە',lbPrice:'نرخ (USD)',optAll:'کڕین یان کرێ',optBuy:'کڕین',optRent:'کرێ',optAreaFirst:'یەکەم شار هەڵبژێرە',optAllTypes:'هەموو جۆرەکان',optVilla:'ڤیلا',optHouse:'خانوو',optApart:'ئەپارتمان',optComm:'بازرگانی',phKeyword:'پووڵ، باخچە...',phMin:'$ کەمترین',phMax:'$ زۆرترین',priceHint:'نرخەکان بە دۆلاری ئەمریکی',btnApply:'فلتەر جێبەجێ بکە',btnReset:'پاکی بکەرەوە',pgTag:'خانووبەرەی کوردستان',pgTitleEm:'خانووەکان',propLabel:'خانوو',featBeds:'جێخەو',featBaths:'حەمام',ctaView:'وردەکاریەکان',emptyTitle:'هیچ خانوویەک نەدۆزراوەتەوە',emptyDesc:'فلتەرەکانت بگۆڕە بۆ دۆزینەوەی خانووی گونجاو.',pgPage:'لاپەڕە',pgOf:'لە',pgTotal:'خانوو بەکۆی هەموو',mobFilter:'فلتەرەکان'},
    en:{dir:'ltr',sbTitle:'Filters',sbSub:'Refine your search',lbType:'Listing Type',lbCity:'City',lbArea:'Area',lbPurpose:'Property Type',lbKeyword:'Keywords',lbPrice:'Price (USD)',optAll:'Buy or Rent',optBuy:'Buy',optRent:'Rent',optAreaFirst:'Select city first',optAllTypes:'All Types',optVilla:'Villa',optHouse:'House',optApart:'Apartment',optComm:'Commercial',phKeyword:'Pool, Garden...',phMin:'$ Min',phMax:'$ Max',priceHint:'All prices in US Dollars',btnApply:'Apply Filters',btnReset:'Reset Filters',pgTag:'Kurdistan Real Estate',pgTitleEm:'Properties',propLabel:'Properties',featBeds:'Beds',featBaths:'Baths',ctaView:'View Details',emptyTitle:'No Properties Found',emptyDesc:'Try adjusting your filters to find matching properties.',pgPage:'Page',pgOf:'of',pgTotal:'total listings',mobFilter:'Filters'},
    ar:{dir:'rtl',sbTitle:'الفلاتر',sbSub:'حسّن بحثك',lbType:'نوع الإدراج',lbCity:'المدينة',lbArea:'المنطقة',lbPurpose:'نوع العقار',lbKeyword:'كلمات مفتاحية',lbPrice:'السعر (USD)',optAll:'شراء أو إيجار',optBuy:'شراء',optRent:'إيجار',optAreaFirst:'اختر المدينة أولاً',optAllTypes:'جميع الأنواع',optVilla:'فيلا',optHouse:'منزل',optApart:'شقة',optComm:'تجاري',phKeyword:'مسبح، حديقة...',phMin:'$ الأدنى',phMax:'$ الأقصى',priceHint:'جميع الأسعار بالدولار الأمريكي',btnApply:'تطبيق الفلاتر',btnReset:'مسح الكل',pgTag:'عقارات كردستان',pgTitleEm:'العقارات',propLabel:'عقار',featBeds:'غرف',featBaths:'حمامات',ctaView:'عرض التفاصيل',emptyTitle:'لا توجد عقارات',emptyDesc:'جرّب تعديل الفلاتر للعثور على عقارات مناسبة.',pgPage:'صفحة',pgOf:'من',pgTotal:'إجمالي العقارات',mobFilter:'الفلاتر'},
  };
}
</script>

<!-- LOCATION SELECTOR -->
<script>
class LocationSelector{
  constructor(o={}){this.cId=o.citySelectId||'city-dropdown';this.aId=o.areaSelectId||'area-dropdown';this.onC=o.onCityChange||null;this.onA=o.onAreaChange||null;this.cities=[];this.curC=o.selectedCityId||null;this.curA=o.selectedAreaId||null;}
  async init(){try{await this.loadCities();this.bind();if(this.curC)await this.loadAreas(this.curC);}catch(e){console.error(e);}}
  async loadCities(){const el=document.getElementById(this.cId);try{const r=await fetch('/v1/api/location/branches',{headers:{'Accept-Language':'en'}});const d=await r.json();if(d.success&&Array.isArray(d.data)){this.cities=d.data;this.fillCities();}}catch(e){if(el)el.innerHTML='<option value="">All Cities</option>';}}
  fillCities(){const el=document.getElementById(this.cId);if(!el)return;el.innerHTML='<option value="">All Cities</option>';[...this.cities].sort((a,b)=>a.city_name_en.localeCompare(b.city_name_en)).forEach(c=>{const o=document.createElement('option');o.value=c.id;o.textContent=c.city_name_en;o.dataset.nameEn=c.city_name_en;if(c.id==this.curC)o.selected=true;el.appendChild(o);});}
  async loadAreas(id){const el=document.getElementById(this.aId);if(!el)return;el.innerHTML='<option value="">Loading…</option>';el.disabled=true;try{const r=await fetch(`/v1/api/location/branches/${id}/areas`,{headers:{'Accept-Language':'en'}});const d=await r.json();if(d.success&&d.data)this.fillAreas(d.data);}catch(e){}finally{el.disabled=false;}}
  fillAreas(areas){const el=document.getElementById(this.aId);if(!el)return;el.innerHTML='<option value="">All Areas</option>';[...areas].sort((a,b)=>a.area_name_en.localeCompare(b.area_name_en)).forEach(a=>{const o=document.createElement('option');o.value=a.id;o.textContent=a.area_name_en;o.dataset.nameEn=a.area_name_en;if(a.id==this.curA)o.selected=true;el.appendChild(o);});}
  bind(){const cEl=document.getElementById(this.cId);const aEl=document.getElementById(this.aId);if(cEl)cEl.addEventListener('change',async e=>{if(e.target.value){await this.loadAreas(e.target.value);if(this.onC)this.onC(e.target.value);}else{if(aEl){aEl.innerHTML='<option value="">Select City First</option>';aEl.disabled=true;}if(this.onC)this.onC(null);}});if(aEl)aEl.addEventListener('change',e=>{if(this.onA)this.onA(e.target.value);});}
}
</script>

<!-- MAIN APP LOGIC — unchanged from original -->
<script>
document.addEventListener('DOMContentLoaded',()=>{
  const lenis=new Lenis({duration:1.2,easing:t=>Math.min(1,1.001-Math.pow(2,-10*t)),smoothWheel:true,smoothTouch:false});
  lenis.on('scroll',ScrollTrigger.update);
  gsap.ticker.add(time=>lenis.raf(time*1000));
  gsap.ticker.lagSmoothing(0);

  $(function(){
    const i18n=new DreamMulkI18n({defaultLang:'ku'});
    document.querySelectorAll('.lang-btn').forEach(btn=>btn.addEventListener('click',()=>i18n.setLang(btn.getAttribute('data-lang'))));
    i18n.init();

    document.querySelectorAll('.price-display').forEach(el=>{el.textContent='$'+Number(el.dataset.usd).toLocaleString();});

    var $g=$('#propertiesGrid'),isoReady=false;
    function initIso(){
      if($('.pc').length===0){showEmpty();return;}
      $g.imagesLoaded(function(){
        $g.isotope({itemSelector:'.pc',percentPosition:true,layoutMode:'fitRows',transitionDuration:'0.38s'});
        isoReady=true;
        setTimeout(()=>{$g.isotope('layout');ScrollTrigger.refresh();},100);
        applyUrlParams();
        gsap.fromTo('.pc-inner',{y:24,opacity:0},{y:0,opacity:1,duration:0.7,stagger:0.05,ease:'power2.out',clearProps:'all'});
      });
    }
    initIso();

    function showEmpty(){document.getElementById('emptyState').classList.add('visible');$('#results-counter').text(0);}
    function hideEmpty(){document.getElementById('emptyState').classList.remove('visible');}

    function runFilter(){
      if(!isoReady)return;
      var kw=$('#search-keywords-input').val().toLowerCase().trim();
      var mn=parseFloat($('#min-price-input').val())||0;
      var mx=parseFloat($('#max-price-input').val())||Infinity;
      var tp=$('#purpose-dropdown').val().toLowerCase();
      var ls=$('#property-type-dropdown').val().toLowerCase();
      var cy=($('#city-dropdown option:selected').data('nameEn')||'').toLowerCase();
      var ar=($('#area-dropdown option:selected').data('nameEn')||'').toLowerCase();
      $g.isotope({filter:function(){
        var $t=$(this),tx=$t.text().toLowerCase();
        var price=parseFloat($t.attr('data-price-usd'));
        return(!kw||tx.includes(kw))&&price>=mn&&price<=mx&&(!tp||$t.attr('data-type')===tp)&&(!ls||$t.attr('data-listing')===ls)&&(!cy||tx.includes(cy))&&(!ar||tx.includes(ar));
      }});
      setTimeout(()=>{const iso=$g.data('isotope');const count=iso?iso.filteredItems.length:0;$('#results-counter').text(count);if(count===0)showEmpty();else hideEmpty();ScrollTrigger.refresh();},400);
      buildFilterTags();
    }

    function applyUrlParams(){
      const p=new URLSearchParams(window.location.search);
      const type=p.get('type')||'';const search=p.get('search')||'';
      if(type)$('#property-type-dropdown').val(type);
      if(search)$('#search-keywords-input').val(search);
      if(type||search)runFilter();
    }

    function buildFilterTags(){
      const tags=[];
      const type=$('#property-type-dropdown').val();const kw=$('#search-keywords-input').val();
      const city=$('#city-dropdown option:selected').text();const area=$('#area-dropdown option:selected').text();
      const mn=$('#min-price-input').val();const mx=$('#max-price-input').val();
      if(type)tags.push({label:type==='sell'?i18n.t('optBuy'):i18n.t('optRent'),clear:()=>$('#property-type-dropdown').val('')});
      if(kw)tags.push({label:kw,clear:()=>$('#search-keywords-input').val('')});
      if(city&&city!=='All Cities'&&city!=='...')tags.push({label:city,clear:()=>{$('#city-dropdown').val('');$('#area-dropdown').html('<option value="">Select City First</option>').prop('disabled',true);}});
      if(area&&area!=='All Areas'&&area!=='Select City First')tags.push({label:area,clear:()=>$('#area-dropdown').val('')});
      if(mn)tags.push({label:'≥ $'+Number(mn).toLocaleString(),clear:()=>$('#min-price-input').val('')});
      if(mx)tags.push({label:'≤ $'+Number(mx).toLocaleString(),clear:()=>$('#max-price-input').val('')});
      const $wrap=$('#activeFilterTags').empty();
      tags.forEach(t=>{const $tag=$('<div class="af-tag"></div>').text(t.label);const $x=$('<button title="remove">✕</button>').on('click',()=>{t.clear();runFilter();});$tag.append($x);$wrap.append($tag);});
    }

    const urlCity=new URLSearchParams(window.location.search).get('city')||'';
    const locSel=new LocationSelector({citySelectId:'city-dropdown',areaSelectId:'area-dropdown',onCityChange:()=>runFilter(),onAreaChange:()=>runFilter()});
    locSel.init().then(()=>{
      if(urlCity){$('#city-dropdown option').each(function(){if(($(this).data('nameEn')||'').toLowerCase()===urlCity.toLowerCase()){$('#city-dropdown').val($(this).val()).trigger('change');}});}
      if(new URLSearchParams(window.location.search).toString())runFilter();
    });

    $('#search-button').on('click',runFilter);

    function resetAll(){
      $('input.fc').val('');$('select.fc').prop('selectedIndex',0);
      $('#area-dropdown').html('<option value="">Select City First</option>').prop('disabled',true);
      hideEmpty();if(isoReady)$g.isotope({filter:'*'});$('#activeFilterTags').empty();
      setTimeout(()=>{$('#results-counter').text($('.pc').length);ScrollTrigger.refresh();},400);
      history.replaceState(null,'',window.location.pathname);
    }
    $('#clear-filters,#emptyResetBtn').on('click',resetAll);

    const openSb=()=>{$('#sb').addClass('open');$('#sbOverlay').fadeIn(180);$('body').css('overflow','hidden');};
    const closeSb=()=>{$('#sb').removeClass('open');$('#sbOverlay').fadeOut(180);$('body').css('overflow','');};
    $('#mobBtn').on('click',openSb);$('#sbOverlay').on('click',closeSb);
    $(window).on('resize',function(){if(window.innerWidth>1024)closeSb();if(isoReady)$g.isotope('layout');});
  });
});
</script>
</body>
</html>
