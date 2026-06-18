<!DOCTYPE html>
<html lang="ku">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $property->name['ku'] ?? $property->name['en'] ?? 'خانوو' }} — Dream Mulk</title>
<meta property="og:title" content="{{ $property->name['en'] ?? 'Property' }} — Dream Mulk"/>
<meta property="og:image" content="{{ $property->images[0] ?? '' }}"/>
<meta property="og:url" content="{{ url()->current() }}"/>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
:root{
  --blue:#1A225A; --blue-mid:#2a3298; --blue-light:#eef0f9; --blue-pale:#f4f5fb;
  --gold:#C0A062; --gold-lt:#d4b97a; --gold-pale:#fdf6e8;
  --white:#fff; --bg:#f5f6fa; --surface:#fff;
  --border:#e8eaf2; --text:#0f1225; --text-2:#4a5080; --text-3:#8b91b8;
  --green:#059669; --red:#dc2626;
  --shadow-sm:0 2px 8px rgba(26,34,90,0.07);
  --shadow-md:0 8px 28px rgba(26,34,90,0.10);
  --shadow-lg:0 20px 60px rgba(26,34,90,0.13);
  --radius:14px; --ease:cubic-bezier(0.22,1,0.36,1);
  --font:'Vazirmatn',sans-serif;
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
html{scroll-behavior:smooth;}
body{font-family:var(--font);background:var(--bg);color:var(--text);-webkit-font-smoothing:antialiased;overflow-x:hidden;direction:rtl;}
body.lang-en{direction:ltr;}
a{text-decoration:none;color:inherit;}
img{display:block;}
::-webkit-scrollbar{width:5px;}
::-webkit-scrollbar-track{background:var(--bg);}
::-webkit-scrollbar-thumb{background:var(--border);border-radius:3px;}
i[class*="fa"]{font-family:"Font Awesome 6 Free","Font Awesome 6 Brands"!important;font-style:normal!important;}

/* ── NAVBAR ── */
.navbar{
  position:sticky;top:0;z-index:200;
  background:rgba(255,255,255,0.96);backdrop-filter:blur(20px);
  border-bottom:1px solid var(--border);
  height:68px;display:flex;align-items:center;padding:0 40px;
  box-shadow:var(--shadow-sm);
}
.nb-inner{max-width:1400px;width:100%;margin:0 auto;display:flex;align-items:center;justify-content:space-between;gap:16px;}
.logo{display:flex;align-items:center;gap:10px;text-decoration:none;}
.logo-img{width:36px;height:36px;border-radius:10px;object-fit:contain;background:var(--white);border:1.5px solid var(--border);padding:3px;}
.logo-mark{width:36px;height:36px;border-radius:10px;background:var(--blue);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:900;color:var(--gold);flex-shrink:0;}
.logo-name{font-size:17px;font-weight:800;color:var(--blue);}
.nb-nav{display:flex;gap:2px;list-style:none;}
.nb-nav a{font-size:13.5px;font-weight:500;color:var(--text-2);padding:7px 13px;border-radius:9px;transition:all .22s;}
.nb-nav a:hover,.nb-nav a.ac{color:var(--blue);background:var(--blue-pale);}
.nb-right{display:flex;align-items:center;gap:10px;flex-shrink:0;}
.lang-sw{display:flex;gap:2px;background:var(--blue-pale);border:1px solid var(--border);border-radius:10px;padding:3px;}
.lang-btn{padding:5px 11px;border-radius:7px;border:none;background:transparent;color:var(--text-3);font-size:12px;font-weight:700;cursor:pointer;transition:all .22s;font-family:var(--font)!important;}
.lang-btn.active{background:var(--blue);color:#fff;}
.nb-browse{padding:8px 18px;background:var(--blue);color:#fff;border-radius:9px;font-size:13px;font-weight:700;display:flex;align-items:center;gap:7px;transition:all .25s;white-space:nowrap;}
.nb-browse:hover{background:var(--blue-mid);transform:translateY(-1px);}
.nb-back{display:flex;align-items:center;gap:6px;font-size:13px;font-weight:600;color:var(--text-2);padding:7px 14px;border-radius:9px;border:1px solid var(--border);transition:all .22s;white-space:nowrap;}
.nb-back:hover{color:var(--blue);border-color:var(--blue);background:var(--blue-pale);}
.hbtn{display:none;background:none;border:none;color:var(--blue);font-size:20px;cursor:pointer;padding:6px;}

/* ── GALLERY ── */
.gallery-wrap{max-width:1400px;margin:0 auto;padding:24px 40px 0;}
.gallery{display:grid;grid-template-columns:1fr 2fr;gap:10px;border-radius:20px;overflow:hidden;height:480px;}
.gallery-thumbs{display:grid;grid-template-rows:repeat(3,1fr);gap:10px;}
.gthumb{overflow:hidden;border-radius:12px;cursor:pointer;position:relative;}
.gthumb img{width:100%;height:100%;object-fit:cover;transition:transform .5s var(--ease);}
.gthumb:hover img{transform:scale(1.06);}
.gmain{position:relative;border-radius:16px;overflow:hidden;cursor:pointer;}
.gmain img{width:100%;height:100%;object-fit:cover;transition:transform .6s var(--ease);}
.gmain:hover img{transform:scale(1.03);}
.gallery-more{position:absolute;bottom:16px;left:16px;background:rgba(15,20,50,0.75);backdrop-filter:blur(8px);color:#fff;padding:8px 16px;border-radius:100px;font-size:12px;font-weight:700;display:flex;align-items:center;gap:6px;border:1px solid rgba(255,255,255,0.15);}
.gallery-more i{color:var(--gold);font-size:11px;}
.badge-overlay{position:absolute;top:16px;right:16px;display:flex;gap:8px;}
.gbadge{font-size:10px;font-weight:700;padding:5px 13px;border-radius:100px;backdrop-filter:blur(8px);}
.gbadge-verified{background:rgba(5,150,105,0.9);color:#fff;}
.gbadge-type{background:rgba(26,34,90,0.85);color:#fff;border:1px solid rgba(255,255,255,0.2);}
.gbadge-rent{background:var(--gold);color:var(--blue);}

/* ── LIGHTBOX ── */
.lightbox{position:fixed;inset:0;z-index:999;background:rgba(5,8,20,0.95);display:none;align-items:center;justify-content:center;flex-direction:column;gap:12px;}
.lightbox.open{display:flex;}
.lb-img{max-width:90vw;max-height:80vh;border-radius:16px;object-fit:contain;}
.lb-close{position:absolute;top:20px;right:20px;width:44px;height:44px;border-radius:12px;background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);color:#fff;font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .25s;}
.lb-close:hover{background:rgba(255,255,255,0.2);}
.lb-nav{display:flex;gap:12px;}
.lb-btn{width:46px;height:46px;border-radius:12px;background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);color:#fff;font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .25s;}
.lb-btn:hover{background:rgba(255,255,255,0.2);}
.lb-counter{color:rgba(255,255,255,0.5);font-size:13px;}

/* ── PAGE LAYOUT ── */
.page-wrap{max-width:1400px;margin:0 auto;padding:28px 40px 80px;}
.page-grid{display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start;}

/* ── MAIN COLUMN ── */
.main-col{display:flex;flex-direction:column;gap:16px;}

/* ── SECTION CARD ── */
.scard{background:var(--white);border:1px solid var(--border);border-radius:20px;overflow:hidden;box-shadow:var(--shadow-sm);}
.scard-pad{padding:28px;}
.scard-hd{display:flex;align-items:center;gap:11px;margin-bottom:22px;padding-bottom:16px;border-bottom:1px solid var(--border);}
.scard-ico{width:36px;height:36px;border-radius:10px;background:var(--blue-light);border:1px solid rgba(26,34,90,0.12);display:flex;align-items:center;justify-content:center;font-size:13px;color:var(--blue-mid);flex-shrink:0;}
.scard-title{font-size:17px;font-weight:700;color:var(--text);}

/* ── HEADER ROW (title + stats) ── */
.prop-header{padding:28px;display:flex;flex-direction:column;gap:14px;}
.prop-top{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;flex-wrap:wrap;}
.prop-badges{display:flex;gap:7px;flex-wrap:wrap;margin-bottom:8px;}
.pbadge{font-size:10px;font-weight:700;padding:4px 12px;border-radius:100px;}
.pb-verified{background:#d1fae5;color:#047857;border:1px solid #a7f3d0;}
.pb-featured{background:var(--gold-pale);color:var(--gold);border:1px solid rgba(192,160,98,0.3);}
.pb-type{background:var(--blue-light);color:var(--blue-mid);border:1px solid rgba(26,34,90,0.12);}
.prop-title{font-size:clamp(22px,3.5vw,34px);font-weight:900;color:var(--text);line-height:1.2;}
.prop-loc{display:flex;align-items:center;gap:6px;font-size:13px;color:var(--text-3);font-weight:500;margin-top:6px;}
.prop-loc i{color:var(--gold);font-size:11px;}

/* ── BENTO STATS ── */
.bento{display:grid;grid-template-columns:repeat(4,1fr);gap:0;border-top:1px solid var(--border);}
.bc{padding:22px 16px;text-align:center;border-left:1px solid var(--border);transition:background .25s;}
.bc:first-child{border-left:none;}
body.lang-en .bc{border-left:none;border-right:1px solid var(--border);}
body.lang-en .bc:first-child{border-right:none;}
.bc:hover{background:var(--blue-pale);}
.bc-ico{font-size:18px;color:var(--blue-mid);margin-bottom:10px;}
.bc-v{font-size:24px;font-weight:900;color:var(--text);display:block;line-height:1;}
.bc-l{font-size:10px;font-weight:600;color:var(--text-3);margin-top:5px;display:block;}

/* ── DESCRIPTION ── */
.desc-body{font-size:15px;line-height:2;color:var(--text-2);}

/* ── SPECS ── */
.specs-grid{display:grid;grid-template-columns:1fr 1fr;gap:9px;}
.spec-row{display:flex;align-items:center;justify-content:space-between;padding:12px 15px;background:var(--bg);border:1px solid var(--border);border-radius:11px;transition:all .22s;}
.spec-row:hover{border-color:var(--blue-mid);background:var(--white);}
.spec-key{font-size:12px;color:var(--text-3);font-weight:500;display:flex;align-items:center;gap:7px;}
.spec-key i{color:var(--blue-mid);font-size:11px;width:12px;text-align:center;}
.spec-val{font-size:13px;font-weight:700;color:var(--text);}

/* ── AMENITIES ── */
.amenity-wrap{display:flex;flex-wrap:wrap;gap:8px;}
.atag{display:inline-flex;align-items:center;gap:6px;padding:8px 15px;border-radius:100px;font-size:12.5px;font-weight:500;color:var(--text-2);background:var(--bg);border:1px solid var(--border);transition:all .22s;}
.atag:hover{background:var(--blue);color:#fff;border-color:var(--blue);}
.atag i{color:var(--gold);font-size:10px;}
.atag:hover i{color:var(--gold-lt);}

/* ── MAP ── */
.map-wrap{height:340px;direction:ltr;}
#prop-map{width:100%;height:100%;}
.leaflet-control-zoom a{border-radius:8px!important;border:1px solid var(--border)!important;color:var(--blue)!important;background:var(--white)!important;}
.leaflet-popup-content-wrapper{border-radius:14px!important;box-shadow:var(--shadow-lg)!important;}

/* ── SIDEBAR ── */
.sidebar{display:flex;flex-direction:column;gap:16px;position:sticky;top:80px;}

/* Price card */
.price-card{background:var(--white);border:1px solid var(--border);border-radius:20px;overflow:hidden;box-shadow:var(--shadow-sm);}
.price-card::before{content:'';display:block;height:3px;background:linear-gradient(90deg,var(--blue),var(--gold));}
.price-inner{padding:24px;}
.price-num{font-size:34px;font-weight:900;color:var(--blue);line-height:1;direction:ltr;display:inline-block;}
.price-sub{font-size:13px;color:var(--text-3);margin-top:4px;margin-bottom:20px;}

/* Agent row */
.agent-row{display:flex;align-items:center;gap:12px;padding:14px;background:var(--bg);border:1px solid var(--border);border-radius:var(--radius);margin-bottom:14px;}
.ag-av{position:relative;flex-shrink:0;}
.ag-av img{width:46px;height:46px;border-radius:50%;object-fit:cover;border:2px solid var(--white);box-shadow:0 0 0 2px var(--gold);}
.ag-online{position:absolute;bottom:1px;right:1px;width:12px;height:12px;border-radius:50%;background:var(--green);border:2px solid var(--white);}
.ag-name{font-size:14px;font-weight:700;color:var(--text);}
.ag-meta{font-size:12px;color:var(--text-3);display:flex;align-items:center;gap:5px;margin-top:2px;}
.ag-meta i{color:var(--gold);font-size:10px;}

/* Action buttons */
.sb-btns{display:flex;flex-direction:column;gap:9px;}
.sb-btn{width:100%;padding:13px;border-radius:12px;font-size:14px;font-weight:700;font-family:var(--font);cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:all .25s var(--ease);border:none;}
.sb-btn-primary{background:var(--blue);color:#fff;box-shadow:0 4px 16px rgba(26,34,90,0.22);}
.sb-btn-primary:hover{background:var(--blue-mid);transform:translateY(-2px);}
.sb-btn-outline{background:transparent;color:var(--text-2);border:1.5px solid var(--border);}
.sb-btn-outline:hover{border-color:var(--blue);color:var(--blue);background:var(--blue-pale);}
.sb-btn-ghost{background:transparent;color:var(--text-3);border:1.5px solid var(--border);}
.sb-btn-ghost:hover{border-color:var(--text-2);color:var(--text);}

/* Quick info card */
.qi-row{display:flex;align-items:center;justify-content:space-between;padding:11px 14px;background:var(--bg);border:1px solid var(--border);border-radius:10px;margin-bottom:8px;transition:all .22s;}
.qi-row:last-child{margin-bottom:0;}
.qi-row:hover{border-color:var(--blue-mid);background:var(--white);}
.qi-key{font-size:12px;font-weight:600;color:var(--text-3);display:flex;align-items:center;gap:7px;}
.qi-key i{color:var(--blue-mid);font-size:11px;width:12px;}
.qi-val{font-size:13px;font-weight:700;color:var(--text);direction:ltr;}

/* Share */
.share-row{display:flex;align-items:center;gap:8px;}
.share-lbl{font-size:11px;font-weight:700;color:var(--text-3);flex:1;}
.share-btn{width:36px;height:36px;border-radius:9px;background:var(--bg);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:13px;color:var(--text-2);text-decoration:none;cursor:pointer;transition:all .25s;}
.share-btn:hover{background:var(--blue);color:#fff;border-color:var(--blue);transform:translateY(-2px);}

/* ── CONTACT FORM ── */
.fg{margin-bottom:13px;}
.fg label{display:block;font-size:10.5px;font-weight:700;letter-spacing:.3px;text-transform:uppercase;color:var(--blue-mid);margin-bottom:6px;}
body.lang-ku .fg label,body.lang-ar .fg label{letter-spacing:0;font-size:12px;text-transform:none;}
.fg input,.fg textarea{width:100%;background:var(--bg);border:1px solid var(--border);color:var(--text);border-radius:10px;padding:11px 13px;font-family:var(--font);font-size:13.5px;outline:none;transition:all .22s;}
.fg input::placeholder,.fg textarea::placeholder{color:var(--text-3);}
.fg input:focus,.fg textarea:focus{border-color:var(--blue-mid);background:var(--white);box-shadow:0 0 0 3px rgba(26,34,90,0.07);}
.fg textarea{resize:none;}
.send-btn{width:100%;padding:13px;border:none;border-radius:12px;cursor:pointer;background:var(--blue);color:#fff;font-family:var(--font);font-size:14px;font-weight:700;display:flex;align-items:center;justify-content:center;gap:9px;transition:all .3s var(--ease);box-shadow:0 4px 16px rgba(26,34,90,0.2);}
.send-btn:hover{background:var(--blue-mid);transform:translateY(-2px);}
.resp-note{display:flex;align-items:center;justify-content:center;gap:6px;font-size:12px;color:var(--text-3);margin-top:12px;}
.resp-note i{color:var(--gold);font-size:11px;}

/* ── TOAST ── */
.toast{position:fixed;bottom:28px;left:50%;transform:translateX(-50%) translateY(16px);background:var(--blue);color:#fff;padding:11px 22px;border-radius:100px;font-size:13px;font-weight:600;z-index:9999;display:flex;align-items:center;gap:8px;box-shadow:var(--shadow-lg);opacity:0;pointer-events:none;transition:all .3s var(--ease);}
.toast.show{opacity:1;transform:translateX(-50%) translateY(0);}
.toast i{color:var(--gold-lt);}

/* ── FOOTER ── */
footer{background:var(--blue);border-top:1px solid rgba(255,255,255,0.08);padding:52px 40px 28px;}
.ft-inner{max-width:1400px;margin:0 auto;}
.ft-top{display:flex;justify-content:space-between;align-items:flex-start;gap:36px;flex-wrap:wrap;padding-bottom:34px;border-bottom:1px solid rgba(255,255,255,0.08);margin-bottom:20px;}
.ft-logo{display:flex;align-items:center;gap:10px;text-decoration:none;margin-bottom:10px;}
.ft-logo-img{width:32px;height:32px;border-radius:9px;object-fit:contain;background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.18);padding:3px;}
.ft-logo-mark{width:32px;height:32px;border-radius:9px;background:var(--gold);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:900;color:var(--blue);}
.ft-logo-name{font-size:16px;font-weight:800;color:#fff;}
.ft-tagline{font-size:13px;color:rgba(255,255,255,0.4);line-height:1.9;max-width:220px;}
.ft-col h5{font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--gold);margin-bottom:14px;font-weight:700;}
body.lang-ku .ft-col h5,body.lang-ar .ft-col h5{letter-spacing:0;font-size:13px;text-transform:none;}
.ft-col ul{list-style:none;display:flex;flex-direction:column;gap:9px;}
.ft-col a{font-size:13.5px;color:rgba(255,255,255,0.45);text-decoration:none;transition:color .22s;}
.ft-col a:hover{color:#fff;}
.ft-bottom{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;}
.ft-copy{font-size:12px;color:rgba(255,255,255,0.3);}
.ft-copy span{color:var(--gold);}
.ft-social{display:flex;gap:7px;}
.soc-link{width:36px;height:36px;border-radius:9px;border:1px solid rgba(255,255,255,0.1);display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,0.4);font-size:13px;text-decoration:none;transition:all .25s;}
.soc-link:hover{border-color:var(--gold);color:var(--gold);transform:translateY(-2px);}

/* ── RESPONSIVE ── */
@media(max-width:1100px){.page-grid{grid-template-columns:1fr;} .sidebar{position:static;} .bento{grid-template-columns:repeat(2,1fr);}}
@media(max-width:900px){.gallery{grid-template-columns:1fr;height:auto;} .gallery-thumbs{grid-template-rows:none;grid-template-columns:repeat(3,1fr);height:100px;} .gmain{height:320px;}}
@media(max-width:768px){.nb-nav,.lang-sw,.nb-browse{display:none;} .hbtn{display:block;} .gallery-wrap,.page-wrap,footer{padding-left:16px;padding-right:16px;} .navbar{padding:0 16px;} .specs-grid{grid-template-columns:1fr;}}
@media(max-width:600px){.gallery-thumbs{display:none;} .gallery{display:block;} .gmain{height:260px;border-radius:16px;}}
</style>
</head>
<body class="lang-ku">

<!-- NAVBAR -->
<nav class="navbar">
  <div class="nb-inner">
    <a href="{{ route('newindex') }}" class="logo">
      <img src="{{ asset('favicon.ico') }}" alt="Dream Mulk" class="logo-img" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
      <div class="logo-mark" style="display:none;">M</div>
      <span class="logo-name">Dream Mulk</span>
    </a>
    <ul class="nb-nav">
      <li><a href="{{ route('newindex') }}"      id="nb-home"    data-i18n="navHome">سەرەتا</a></li>
      <li><a href="{{ route('property.list') }}" id="nb-props"   data-i18n="navProps" class="ac">خانووەکان</a></li>
      <li><a href="{{ route('about-us') }}"      id="nb-about"   data-i18n="navAbout">دەربارەمان</a></li>
      <li><a href="{{ route('contact-us') }}"    id="nb-contact" data-i18n="navContact">پەیوەندی</a></li>
    </ul>
    <div class="nb-right">
      <div class="lang-sw">
        <button class="lang-btn active" data-lang="ku">کو</button>
        <button class="lang-btn" data-lang="en">EN</button>
        <button class="lang-btn" data-lang="ar">ع</button>
      </div>
      <a href="{{ route('property.list') }}" class="nb-back" id="nb-back">
        <i class="fas fa-arrow-right"></i>
        <span data-i18n="backList">گەڕانەوە بۆ لیست</span>
      </a>
      <a href="{{ route('property.list') }}" class="nb-browse">
        <i class="fas fa-search"></i>
        <span data-i18n="browseBtn">خانووەکان ببینە</span>
      </a>
    </div>
    <button class="hbtn" id="ham"><i class="fas fa-bars"></i></button>
  </div>
</nav>

<!-- GALLERY -->
<div class="gallery-wrap">
  <div class="gallery" id="gallery">
    <div class="gallery-thumbs" id="gallery-thumbs">
      @foreach(array_slice($property->images ?? [], 1, 3) as $i => $img)
      <div class="gthumb" onclick="openLightbox({{ $i + 1 }})">
        <img src="{{ $img }}" alt="Property" loading="lazy" onerror="this.src='{{ asset('property_images/default-property.jpg') }}'">
      </div>
      @endforeach
    </div>
    <div class="gmain" onclick="openLightbox(0)">
      <img src="{{ $property->images[0] ?? asset('property_images/default-property.jpg') }}" alt="{{ $property->name['en'] ?? '' }}" id="main-img" onerror="this.src='{{ asset('property_images/default-property.jpg') }}'">
      <div class="badge-overlay">
        @if($property->verified ?? false)
        <span class="gbadge gbadge-verified"><i class="fas fa-shield-check"></i> <span data-i18n="verified">پشتڕاستکراوە</span></span>
        @endif
        <span class="gbadge {{ strtolower($property->listing_type ?? '') === 'rent' ? 'gbadge-rent' : 'gbadge-type' }}">
          <span data-i18n="{{ strtolower($property->listing_type ?? 'sell') }}">{{ $property->listing_type === 'rent' ? 'کرێ' : 'فرۆشتن' }}</span>
        </span>
      </div>
      @if(count($property->images ?? []) > 4)
      <div class="gallery-more">
        <i class="far fa-images"></i>
        <span>+{{ count($property->images) - 4 }} <span data-i18n="photos">وێنە</span></span>
      </div>
      @endif
    </div>
  </div>
</div>

<!-- LIGHTBOX -->
<div class="lightbox" id="lightbox">
  <button class="lb-close" onclick="closeLightbox()"><i class="fas fa-times"></i></button>
  <img src="" alt="" class="lb-img" id="lb-img">
  <div class="lb-nav">
    <button class="lb-btn" onclick="lbPrev()"><i class="fas fa-arrow-right"></i></button>
    <span class="lb-counter" id="lb-counter">1 / 1</span>
    <button class="lb-btn" onclick="lbNext()"><i class="fas fa-arrow-left"></i></button>
  </div>
</div>

<!-- PAGE -->
<div class="page-wrap">
<div class="page-grid">

<!-- MAIN COLUMN -->
<div class="main-col">

  <!-- Title + Bento -->
  <div class="scard">
    <div class="prop-header">
      <div class="prop-badges">
        @if($property->verified ?? false)
        <span class="pbadge pb-verified"><i class="fas fa-shield-check"></i> <span data-i18n="verified">پشتڕاستکراوە</span></span>
        @endif
        @if($property->is_boosted ?? false)
        <span class="pbadge pb-featured"><i class="fas fa-crown"></i> <span data-i18n="featured">تایبەت</span></span>
        @endif
        <span class="pbadge pb-type">
          <span data-i18n="{{ strtolower($property->listing_type ?? 'sell') }}">{{ $property->listing_type === 'rent' ? 'کرێ' : 'فرۆشتن' }}</span>
        </span>
      </div>
      <h1 class="prop-title"
        id="dyn-title"
        data-ku="{{ $property->name['ku'] ?? $property->name['en'] ?? '' }}"
        data-en="{{ $property->name['en'] ?? '' }}"
        data-ar="{{ $property->name['ar'] ?? $property->name['en'] ?? '' }}"
      >{{ $property->name['ku'] ?? $property->name['en'] ?? 'خانوو' }}</h1>
      <div class="prop-loc"
        id="dyn-addr"
        data-ku="{{ ($property->address_details['area']['ku'] ?? '') . ' · ' . ($property->address_details['city']['ku'] ?? '') }}"
        data-en="{{ ($property->address_details['area']['en'] ?? '') . ' · ' . ($property->address_details['city']['en'] ?? '') }}"
        data-ar="{{ ($property->address_details['area']['ar'] ?? '') . ' · ' . ($property->address_details['city']['ar'] ?? '') }}"
      >
        <i class="fas fa-location-dot"></i>
        <span>{{ ($property->address_details['area']['ku'] ?? '') . ' · ' . ($property->address_details['city']['ku'] ?? 'کوردستان') }}</span>
      </div>
    </div>
    <div class="bento">
      <div class="bc">
        <div class="bc-ico"><i class="fas fa-bed"></i></div>
        <span class="bc-v">{{ $property->rooms['bedroom']['count'] ?? 0 }}</span>
        <span class="bc-l" data-i18n="beds">جێخەو</span>
      </div>
      <div class="bc">
        <div class="bc-ico"><i class="fas fa-bath"></i></div>
        <span class="bc-v">{{ $property->rooms['bathroom']['count'] ?? 0 }}</span>
        <span class="bc-l" data-i18n="baths">حەمام</span>
      </div>
      <div class="bc">
        <div class="bc-ico"><i class="fas fa-vector-square"></i></div>
        <span class="bc-v">{{ number_format($property->area ?? 0) }}</span>
        <span class="bc-l">m²</span>
      </div>
      <div class="bc">
        <div class="bc-ico"><i class="fas fa-calendar-check"></i></div>
        <span class="bc-v">{{ $property->year_built ?? '—' }}</span>
        <span class="bc-l" data-i18n="yearBuilt">ساڵی بنیات</span>
      </div>
    </div>
  </div>

  <!-- Description -->
  <div class="scard">
    <div class="scard-pad">
      <div class="scard-hd">
        <div class="scard-ico"><i class="fas fa-align-left"></i></div>
        <span class="scard-title" data-i18n="aboutProp">دەربارەی خانووەکە</span>
      </div>
      <p class="desc-body"
        id="dyn-desc"
        data-ku="{{ $property->description['ku'] ?? $property->description['en'] ?? '' }}"
        data-en="{{ $property->description['en'] ?? '' }}"
        data-ar="{{ $property->description['ar'] ?? $property->description['en'] ?? '' }}"
      >{{ $property->description['ku'] ?? $property->description['en'] ?? 'زانیاری نەنووسراوە.' }}</p>
    </div>
  </div>

  <!-- Specs -->
  <div class="scard">
    <div class="scard-pad">
      <div class="scard-hd">
        <div class="scard-ico"><i class="fas fa-list-check"></i></div>
        <span class="scard-title" data-i18n="specs">تایبەتمەندییەکان</span>
      </div>
      <div class="specs-grid">
        @php $specs = [
          ['key'=>'propType',   'icon'=>'fa-home',          'label'=>'جۆری خانوو',  'val'=> ucfirst($property->type['category'] ?? 'N/A')],
          ['key'=>'floorNum',   'icon'=>'fa-layer-group',   'label'=>'نهۆم',         'val'=> $property->floor_number ?? '—'],
          ['key'=>'electricity','icon'=>'fa-bolt',           'label'=>'کارەبا',       'val'=> ($property->electricity ?? false) ? 'بەردەستە' : '—'],
          ['key'=>'water',      'icon'=>'fa-droplet',        'label'=>'ئاو',          'val'=> ($property->water ?? false)       ? 'بەردەستە' : '—'],
          ['key'=>'internet',   'icon'=>'fa-wifi',           'label'=>'ئینتەرنێت',   'val'=> ($property->internet ?? false)    ? 'فایبەر' : '—'],
          ['key'=>'furnished',  'icon'=>'fa-couch',          'label'=>'مۆبیلیات',    'val'=> ($property->furnished ?? false)   ? 'بەڵێ' : 'نەخێر'],
        ]; @endphp
        @foreach($specs as $s)
        <div class="spec-row">
          <span class="spec-key"><i class="fas {{ $s['icon'] }}"></i><span data-i18n="{{ $s['key'] }}">{{ $s['label'] }}</span></span>
          <span class="spec-val">{{ $s['val'] }}</span>
        </div>
        @endforeach
      </div>
    </div>
  </div>

  <!-- Amenities -->
  @if(!empty($property->features) || !empty($property->amenities))
  <div class="scard">
    <div class="scard-pad">
      <div class="scard-hd">
        <div class="scard-ico"><i class="fas fa-sparkles"></i></div>
        <span class="scard-title" data-i18n="amenities">خزمەتگوزارییەکان</span>
      </div>
      <div class="amenity-wrap">
        @foreach(array_merge($property->features ?? [], $property->amenities ?? []) as $item)
        <span class="atag"><i class="fas fa-check"></i>{{ ucfirst($item) }}</span>
        @endforeach
      </div>
    </div>
  </div>
  @endif

  <!-- Map -->
  <div class="scard" style="overflow:hidden;">
    <div class="scard-pad" style="padding-bottom:0;">
      <div class="scard-hd">
        <div class="scard-ico"><i class="fas fa-map-location-dot"></i></div>
        <span class="scard-title" data-i18n="location">ناونیشان</span>
      </div>
    </div>
    <div class="map-wrap"><div id="prop-map"></div></div>
  </div>

</div><!-- end main col -->

<!-- SIDEBAR -->
<div class="sidebar">

  <!-- Price Card -->
  <div class="price-card">
    <div class="price-inner">
      <div class="price-num">${{ number_format($property->price['usd'] ?? 0) }}</div>
      <div class="price-sub">
        @if(strtolower($property->listing_type ?? '') === 'rent')
          <span data-i18n="perMonth">مانگانە</span> · <span data-i18n="noComm">بێ کۆمیسیۆن</span>
        @else
          <span data-i18n="totalPrice">نرخی گشتی</span> · <span data-i18n="noComm">بێ کۆمیسیۆن</span>
        @endif
      </div>

      <!-- Agent row -->
      @php
        $owner = $property->owner ?? null;
        $ownerPhone = null;
        $ownerName = 'Dream Mulk';
        $ownerImg = null;
        $isOffice = false;
        if($owner){
          $oc = get_class($owner);
          $isOffice = $oc === 'App\\Models\\RealEstateOffice';
          $ownerPhone = $owner->phone ?? $owner->phone_number ?? null;
          $ownerName = $owner->agent_name ?? $owner->name ?? $owner->company_name ?? 'Agent';
          $ownerImg = $owner->profile_image ?? $owner->image ?? null;
        }
      @endphp
      @if($owner)
      <div class="agent-row">
        <div class="ag-av">
          <img src="{{ $ownerImg ?? 'https://ui-avatars.com/api/?name='.urlencode($ownerName).'&background=1A225A&color=C0A062&size=80&bold=true' }}"
            alt="{{ $ownerName }}"
            onerror="this.src='https://ui-avatars.com/api/?name=Agent&background=1A225A&color=C0A062&size=80&bold=true'">
          <div class="ag-online"></div>
        </div>
        <div>
          <div class="ag-name">{{ $ownerName }}</div>
          <div class="ag-meta">
            <i class="fas fa-star"></i> 4.9 · Dream Mulk ·
            <span data-i18n="{{ $isOffice ? 'office' : 'agent' }}">{{ $isOffice ? 'ئۆفیس' : 'ئەجێنت' }}</span>
          </div>
        </div>
      </div>
      @endif

      <div class="sb-btns">
        <button class="sb-btn sb-btn-primary" onclick="document.getElementById('contact-form').scrollIntoView({behavior:'smooth'})">
          <i class="fas fa-calendar-check"></i>
          <span data-i18n="bookViewing">کاتی سەردان دابنێ</span>
        </button>
        <button class="sb-btn sb-btn-outline" onclick="document.getElementById('msg-area').focus();document.getElementById('contact-form').scrollIntoView({behavior:'smooth'})">
          <i class="far fa-comment"></i>
          <span data-i18n="sendMsg">نامە بنێرە</span>
        </button>
        @if($ownerPhone)
        <a href="tel:{{ $ownerPhone }}" class="sb-btn sb-btn-ghost" style="text-decoration:none;">
          <i class="fas fa-phone"></i>
          <span data-i18n="call">پەیوەندی بکە</span>
        </a>
        @endif
        <button class="sb-btn sb-btn-ghost" id="fav-btn">
          <i class="far fa-heart"></i>
          <span data-i18n="save">پاشەکەوتی بکە</span>
        </button>
      </div>
    </div>
  </div>

  <!-- Quick Info -->
  <div class="scard">
    <div class="scard-pad">
      <div class="scard-hd" style="margin-bottom:16px;padding-bottom:14px;">
        <div class="scard-ico"><i class="fas fa-circle-info"></i></div>
        <span class="scard-title" data-i18n="quickInfo">زانیاری خێرا</span>
      </div>
      <div class="qi-row">
        <span class="qi-key"><i class="fas fa-hashtag"></i><span data-i18n="propId">ژمارەی خانوو</span></span>
        <span class="qi-val">#{{ str_pad($property->id, 5, '0', STR_PAD_LEFT) }}</span>
      </div>
      <div class="qi-row">
        <span class="qi-key"><i class="fas fa-circle-dot"></i><span data-i18n="status">دۆخ</span></span>
        <span class="qi-val" style="color:var(--green);"><i class="fas fa-circle" style="font-size:7px;"></i> <span data-i18n="active">چالاک</span></span>
      </div>
      <div class="qi-row">
        <span class="qi-key"><i class="fas fa-calendar"></i><span data-i18n="listed">کاتی دانان</span></span>
        <span class="qi-val">{{ optional($property->created_at)->diffForHumans() ?? '—' }}</span>
      </div>
      <div class="qi-row">
        <span class="qi-key"><i class="fas fa-eye"></i><span data-i18n="views">بینینەکان</span></span>
        <span class="qi-val">{{ number_format($property->views ?? 0) }}</span>
      </div>
    </div>
  </div>

  <!-- Contact Form -->
  <div class="scard" id="contact-form">
    <div class="scard-pad">
      <div class="scard-hd">
        <div class="scard-ico"><i class="fas fa-paper-plane"></i></div>
        <span class="scard-title" data-i18n="inquiryTitle">ناردنی پرسیار</span>
      </div>
      <p style="font-size:13px;color:var(--text-3);margin-bottom:16px;line-height:1.7;" data-i18n="inquirySub">ئارەزووی ئەم خانووت هەیە؟ نامەیەک بنێرە.</p>
      <form action="/submit-contact" method="POST">
        @csrf
        <div class="fg">
          <label data-i18n="fullName">ناوی تەواو</label>
          <input type="text" name="name" placeholder="ناوی خۆت بنووسە" required/>
        </div>
        <div class="fg">
          <label data-i18n="phone">ژمارەی تەلەفۆن</label>
          <input type="tel" name="phone-number" placeholder="07XX XXX XXXX" required style="direction:ltr;text-align:start;"/>
        </div>
        <div class="fg">
          <label data-i18n="message">نامە</label>
          <textarea name="message" rows="4" id="msg-area" required>سڵاو، ئارەزووی ئەم خانووەم هەیە. تکایە پەیوەندیم پێوە بکە.</textarea>
        </div>
        <button type="submit" class="send-btn">
          <i class="fas fa-paper-plane"></i>
          <span data-i18n="sendInquiry">ناردن</span>
        </button>
      </form>
      <div class="resp-note">
        <i class="fas fa-clock"></i>
        <span data-i18n="respNote">زۆرجار لە ٢٤ کاتژمێردا وەڵام دەداتەوە</span>
      </div>
    </div>
  </div>

  <!-- Share -->
  <div class="scard">
    <div class="scard-pad" style="padding:18px 20px;">
      <div class="share-row">
        <span class="share-lbl" data-i18n="share">هاوبەشکردن</span>
        <div class="share-btn" id="copy-link-btn" title="Copy"><i class="fas fa-link"></i></div>
        <a class="share-btn" href="https://wa.me/?text={{ urlencode(($property->name['en'] ?? 'Property').' — '.url()->current()) }}" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i></a>
        <a class="share-btn" href="https://t.me/share/url?url={{ urlencode(url()->current()) }}" target="_blank" rel="noopener"><i class="fab fa-telegram"></i></a>
        <a class="share-btn" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a>
      </div>
    </div>
  </div>

</div><!-- end sidebar -->
</div><!-- end grid -->
</div><!-- end page-wrap -->

<!-- FOOTER -->
<footer>
  <div class="ft-inner">
    <div class="ft-top">
      <div>
        <a href="{{ route('newindex') }}" class="ft-logo">
          <img src="{{ asset('favicon.ico') }}" alt="Dream Mulk" class="ft-logo-img" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
          <div class="ft-logo-mark" style="display:none;">M</div>
          <span class="ft-logo-name">Dream Mulk</span>
        </a>
        <p class="ft-tagline" data-i18n="ftTag">باشترین پلاتفۆرمی خانووبەرەی کوردستان. بێ کارمزد. بێ کۆمیسیۆن. بێ نهێنی.</p>
      </div>
      <div class="ft-col">
        <h5 data-i18n="ftCol1">پلاتفۆرم</h5>
        <ul>
          <li><a href="{{ route('property.list') }}"  data-i18n="ftLink1">خانووەکان ببینە</a></li>
          <li><a href="{{ route('login-page') }}"     data-i18n="ftLink2">چوونەژوورەوەی کڕیار</a></li>
          <li><a href="{{ route('agent.login') }}"    data-i18n="ftLink3">پۆرتاڵی ئەجێنت</a></li>
          <li><a href="{{ route('about-us') }}"       data-i18n="ftLink4">دەربارەی ئێمە</a></li>
        </ul>
      </div>
      <div class="ft-col">
        <h5 data-i18n="ftCol2">خزمەتگوزاری</h5>
        <ul>
          <li><a href="{{ route('property.list') }}"                  data-i18n="ftLink5">کڕینی خانوو</a></li>
          <li><a href="{{ route('login-page') }}"                     data-i18n="ftLink6">فرۆشتنی خانوو</a></li>
          <li><a href="{{ route('property.list',['type'=>'rent']) }}" data-i18n="ftLink7">کرێدانی خانوو</a></li>
          <li><a href="{{ route('agents.list') }}"                    data-i18n="ftLink8">ئەجێنت بدۆزەرەوە</a></li>
        </ul>
      </div>
      <div class="ft-col">
        <h5 data-i18n="ftCol3">ئەپەکە دابەزێنە</h5>
        <ul>
          <li><a href="https://apps.apple.com/us/app/dream-mulk/id6756894199" target="_blank"><i class="fab fa-apple"></i> App Store</a></li>
          <li><a href="https://play.google.com/store/apps/details?id=com.dreammulk.dreamhaven" target="_blank"><i class="fab fa-google-play"></i> Google Play</a></li>
          <li><a href="{{ route('contact-us') }}" data-i18n="ftLink9">پەیوەندیمان پێوە بکە</a></li>
        </ul>
      </div>
    </div>
    <div class="ft-bottom">
      <div class="ft-copy">© {{ date('Y') }} <span>Dream Mulk</span>. Erbil, Kurdistan Region of Iraq.</div>
      <div class="ft-social">
        <a href="https://www.facebook.com/share/1CGLEbK7qh/" target="_blank" class="soc-link"><i class="fab fa-facebook-f"></i></a>
        <a href="https://www.instagram.com/dream_mulk?igsh=MWt4YXd1eTN4NW5j" target="_blank" class="soc-link"><i class="fab fa-instagram"></i></a>
      </div>
    </div>
  </div>
</footer>

<!-- TOAST -->
<div class="toast" id="toast"><i class="fas fa-check-circle"></i><span id="toast-msg">لینک کۆپی کرا</span></div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
/* ── IMAGES ── */
const IMAGES = @json($property->images ?? []);

/* ── LIGHTBOX ── */
let lbIdx = 0;
function openLightbox(i){lbIdx=i;updateLb();document.getElementById('lightbox').classList.add('open');document.body.style.overflow='hidden';}
function closeLightbox(){document.getElementById('lightbox').classList.remove('open');document.body.style.overflow='';}
function updateLb(){if(!IMAGES.length)return;document.getElementById('lb-img').src=IMAGES[lbIdx];document.getElementById('lb-counter').textContent=(lbIdx+1)+' / '+IMAGES.length;}
function lbNext(){lbIdx=(lbIdx+1)%IMAGES.length;updateLb();}
function lbPrev(){lbIdx=(lbIdx-1+IMAGES.length)%IMAGES.length;updateLb();}
document.getElementById('lightbox').addEventListener('click',function(e){if(e.target===this)closeLightbox();});
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeLightbox();if(e.key==='ArrowLeft')lbNext();if(e.key==='ArrowRight')lbPrev();});

/* ── TOAST ── */
function showToast(msg){const t=document.getElementById('toast');document.getElementById('toast-msg').textContent=msg;t.classList.add('show');setTimeout(()=>t.classList.remove('show'),2800);}

/* ── COPY LINK ── */
document.getElementById('copy-link-btn').addEventListener('click',()=>{
  navigator.clipboard.writeText(window.location.href).then(()=>showToast(I18N[currentLang]?.linkCopied||'لینک کۆپی کرا')).catch(()=>{});
});

/* ── FAV ── */
document.getElementById('fav-btn').addEventListener('click',function(){
  const faved=this.querySelector('i').className.includes('far');
  this.querySelector('i').className=faved?'fas fa-heart':'far fa-heart';
  if(faved)this.querySelector('i').style.color='#ef4444'; else this.querySelector('i').style.color='';
});

/* ── I18N ── */
var currentLang='ku';
const I18N={
  ku:{dir:'rtl',navHome:'سەرەتا',navProps:'خانووەکان',navAbout:'دەربارەمان',navContact:'پەیوەندی',browseBtn:'خانووەکان ببینە',backList:'گەڕانەوە بۆ لیست',verified:'پشتڕاستکراوە',featured:'تایبەت',rent:'کرێ',sell:'فرۆشتن',perMonth:'مانگانە',totalPrice:'نرخی گشتی',noComm:'بێ کۆمیسیۆن',beds:'جێخەو',baths:'حەمام',yearBuilt:'ساڵی بنیات',aboutProp:'دەربارەی خانووەکە',specs:'تایبەتمەندییەکان',amenities:'خزمەتگوزارییەکان',location:'ناونیشان',bookViewing:'کاتی سەردان دابنێ',sendMsg:'نامە بنێرە',call:'پەیوەندی بکە',save:'پاشەکەوتی بکە',inquiryTitle:'ناردنی پرسیار',inquirySub:'ئارەزووی ئەم خانووت هەیە؟ نامەیەک بنێرە.',fullName:'ناوی تەواو',phone:'ژمارەی تەلەفۆن',message:'نامە',sendInquiry:'ناردن',respNote:'زۆرجار لە ٢٤ کاتژمێردا وەڵام دەداتەوە',quickInfo:'زانیاری خێرا',propId:'ژمارەی خانوو',status:'دۆخ',active:'چالاک',listed:'کاتی دانان',views:'بینینەکان',share:'هاوبەشکردن',photos:'وێنە',agent:'ئەجێنتی پشتڕاستکراوە',office:'ئۆفیسی خانووبەرە',propType:'جۆری خانوو',floorNum:'نهۆم',electricity:'کارەبا',water:'ئاو',internet:'ئینتەرنێت',furnished:'مۆبیلیات',linkCopied:'لینک کۆپی کرا',msgDefault:'سڵاو، ئارەزووی ئەم خانووەم هەیە. تکایە پەیوەندیم پێوە بکە.',ftTag:'باشترین پلاتفۆرمی خانووبەرەی کوردستان. بێ کارمزد. بێ کۆمیسیۆن.',ftCol1:'پلاتفۆرم',ftCol2:'خزمەتگوزاری',ftCol3:'ئەپەکە دابەزێنە',ftLink1:'خانووەکان ببینە',ftLink2:'چوونەژوورەوەی کڕیار',ftLink3:'پۆرتاڵی ئەجێنت',ftLink4:'دەربارەی ئێمە',ftLink5:'کڕینی خانوو',ftLink6:'فرۆشتنی خانوو',ftLink7:'کرێدانی خانوو',ftLink8:'ئەجێنت بدۆزەرەوە',ftLink9:'پەیوەندیمان پێوە بکە'},
  en:{dir:'ltr',navHome:'Home',navProps:'Properties',navAbout:'About Us',navContact:'Contact',browseBtn:'Browse Properties',backList:'Back to Listings',verified:'Verified',featured:'Featured',rent:'For Rent',sell:'For Sale',perMonth:'per month',totalPrice:'total price',noComm:'No Commission',beds:'Bedrooms',baths:'Bathrooms',yearBuilt:'Year Built',aboutProp:'About This Property',specs:'Specifications',amenities:'Amenities & Features',location:'Location',bookViewing:'Book Viewing',sendMsg:'Send Message',call:'Call Agent',save:'Save Property',inquiryTitle:'Send Inquiry',inquirySub:"Interested? We'll connect you directly.",fullName:'Full Name',phone:'Phone Number',message:'Message',sendInquiry:'Send Inquiry',respNote:'Typically responds within 24 hours',quickInfo:'Quick Info',propId:'Property ID',status:'Status',active:'Active',listed:'Listed',views:'Views',share:'Share',photos:'Photos',agent:'Verified Agent',office:'Real Estate Office',propType:'Property Type',floorNum:'Floor',electricity:'Electricity',water:'Water Supply',internet:'Internet',furnished:'Furnished',linkCopied:'Link copied!',msgDefault:'I am interested in this property. Please contact me.',ftTag:"Kurdistan's #1 real estate platform. No fees. No commission.",ftCol1:'Platform',ftCol2:'Services',ftCol3:'Download App',ftLink1:'Browse Properties',ftLink2:'Client Login',ftLink3:'Agent Portal',ftLink4:'About Us',ftLink5:'Buy Property',ftLink6:'Sell Property',ftLink7:'Rent Property',ftLink8:'Find an Agent',ftLink9:'Contact Us'},
  ar:{dir:'rtl',navHome:'الرئيسية',navProps:'العقارات',navAbout:'من نحن',navContact:'تواصل',browseBtn:'استعرض العقارات',backList:'العودة للقائمة',verified:'موثق',featured:'مميز',rent:'للإيجار',sell:'للبيع',perMonth:'شهرياً',totalPrice:'السعر الإجمالي',noComm:'بدون عمولة',beds:'غرف النوم',baths:'الحمامات',yearBuilt:'سنة البناء',aboutProp:'عن هذا العقار',specs:'المواصفات',amenities:'المرافق والمميزات',location:'الموقع',bookViewing:'حجز موعد',sendMsg:'إرسال رسالة',call:'اتصال',save:'حفظ',inquiryTitle:'إرسال استفسار',inquirySub:'مهتم بهذا العقار؟ أرسل رسالة للتواصل.',fullName:'الاسم الكامل',phone:'رقم الهاتف',message:'رسالة',sendInquiry:'إرسال',respNote:'عادة ما يرد خلال ٢٤ ساعة',quickInfo:'معلومات سريعة',propId:'رقم العقار',status:'الحالة',active:'نشط',listed:'تاريخ النشر',views:'المشاهدات',share:'مشاركة',photos:'صورة',agent:'وكيل موثق',office:'مكتب عقارات',propType:'نوع العقار',floorNum:'الطابق',electricity:'الكهرباء',water:'الماء',internet:'الإنترنت',furnished:'مفروش',linkCopied:'تم نسخ الرابط!',msgDefault:'مرحباً، أنا مهتم بهذا العقار. يرجى التواصل معي.',ftTag:'منصة العقارات الأولى في كردستان. بدون رسوم.',ftCol1:'المنصة',ftCol2:'الخدمات',ftCol3:'حمّل التطبيق',ftLink1:'استعرض العقارات',ftLink2:'دخول العملاء',ftLink3:'بوابة الوكلاء',ftLink4:'من نحن',ftLink5:'شراء عقار',ftLink6:'بيع عقار',ftLink7:'إيجار عقار',ftLink8:'ابحث عن وكيل',ftLink9:'تواصل معنا'}
};

function setLang(lang){
  const T=I18N[lang]; if(!T)return;
  currentLang=lang; localStorage.setItem('dm_lang',lang);
  document.body.dir=T.dir;
  document.documentElement.lang=lang;
  document.body.classList.remove('lang-ku','lang-en','lang-ar');
  document.body.classList.add('lang-'+lang);
  document.querySelectorAll('.lang-btn').forEach(b=>b.classList.toggle('active',b.dataset.lang===lang));
  document.querySelectorAll('[data-i18n]').forEach(el=>{const k=el.getAttribute('data-i18n');if(T[k]!==undefined)el.textContent=T[k];});

  // Dynamic multilingual fields
  ['dyn-title','dyn-desc','dyn-addr'].forEach(id=>{
    const el=document.getElementById(id); if(!el)return;
    const v=el.getAttribute('data-'+lang);
    if(!v||!v.trim())return;
    if(id==='dyn-addr'){const s=el.querySelector('span');if(s)s.textContent=v;}
    else el.textContent=v;
  });

  // Arrow direction
  const backArrow=document.querySelector('#nb-back i');
  if(backArrow)backArrow.className=T.dir==='rtl'?'fas fa-arrow-right':'fas fa-arrow-left';

  // Default message
  const msgArea=document.getElementById('msg-area');
  if(msgArea){const defs=[I18N.ku.msgDefault,I18N.en.msgDefault,I18N.ar.msgDefault];if(defs.includes(msgArea.value))msgArea.value=T.msgDefault;}
}
document.querySelectorAll('.lang-btn').forEach(b=>b.addEventListener('click',()=>setLang(b.dataset.lang)));
setLang(localStorage.getItem('dm_lang')||'ku');

/* ── MAP ── */
@php
  $lat=36.1911; $lng=44.0091;
  if(!empty($property->locations)&&is_array($property->locations)&&isset($property->locations[0])){
    $lat=$property->locations[0]['lat']??$lat;
    $lng=$property->locations[0]['lng']??$lng;
  }
@endphp
(function(){
  const map=L.map('prop-map',{center:[{{ $lat }},{{ $lng }}],zoom:15,zoomControl:false,scrollWheelZoom:false});
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19,attribution:'© OpenStreetMap'}).addTo(map);
  L.control.zoom({position:'bottomright'}).addTo(map);
  const icon=L.divIcon({className:'',html:`<div style="width:40px;height:40px;border-radius:50% 50% 50% 0;background:var(--blue);transform:rotate(-45deg);border:3px solid #fff;box-shadow:0 6px 20px rgba(26,34,90,0.4);display:flex;align-items:center;justify-content:center;"><i class='fas fa-building' style='transform:rotate(45deg);color:var(--gold);font-size:13px;'></i></div>`,iconSize:[40,40],iconAnchor:[20,40],popupAnchor:[0,-46]});
  L.marker([{{ $lat }},{{ $lng }}],{icon}).addTo(map)
    .bindPopup(`<div style="font-family:'Vazirmatn',sans-serif;min-width:180px;direction:rtl;"><div style="font-weight:800;font-size:15px;color:var(--text);margin-bottom:4px;">{{ $property->name['en'] ?? '' }}</div><div style="font-size:12px;color:var(--text-3);margin-bottom:6px;"><i class='fas fa-location-dot' style='color:var(--blue);'></i> {{ $property->address_details['city']['en'] ?? '' }}</div><div style="font-size:20px;font-weight:900;color:var(--gold);">${{ number_format($property->price['usd']??0) }}</div></div>`,{maxWidth:220}).openPopup();
  map.on('click',()=>map.scrollWheelZoom.enable());
  map.on('mouseout',()=>map.scrollWheelZoom.disable());
})();
</script>
</body>
</html>
