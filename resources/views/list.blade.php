<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.imagesloaded/4.1.4/imagesloaded.pkgd.min.js"></script>
<script src="{{ asset('assets/vendor/isotope-layout/isotope.pkgd.min.js') }}"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Outfit:wght@300;400;500;600;700&family=Noto+Sans+Arabic:wght@300;400;500;600&display=swap" rel="stylesheet">
<title>Dream Mulk — Properties</title>
<style>
:root{
  --B:#303b97;--BD:#1a225a;--BL:#eef0fb;
  --G:#d4af37;--GL:#f5e9b0;
  --ink:#0d1117;--mid:#52596e;--dim:#9aa0b8;
  --hr:#e4e6f0;--bg:#f4f6fa;--card:#ffffff;
  --sw:300px;
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;outline:none;}
body{font-family:'Outfit',sans-serif;background:var(--bg);color:var(--ink);overflow-x:hidden;}
body.lang-ku,body.lang-ar{font-family:'Noto Sans Arabic',sans-serif;direction:rtl;}
body.lang-ku .sb,body.lang-ar .sb{left:auto;right:0;border-right:none;border-left:1px solid var(--hr);}
body.lang-ku .main,body.lang-ar .main{margin-left:0;margin-right:var(--sw);}
body.lang-ku .sel-wrap::after,body.lang-ar .sel-wrap::after{right:auto;left:16px;}
@media(max-width:1024px){
  body.lang-ku .sb,body.lang-ar .sb{transform:translateX(100%);}
  body.lang-ku .sb.open,body.lang-ar .sb.open{transform:translateX(0);}
  body.lang-ku .main,body.lang-ar .main{margin-right:0;}
}
a{text-decoration:none;color:inherit;}
img{display:block;}

/* ─── LANG SWITCHER ─── */
.list-lang-sw{display:flex;gap:4px;background:#fff;border:1px solid var(--hr);border-radius:50px;padding:4px;box-shadow:0 2px 8px rgba(0,0,0,.04);}
.list-lang-btn{padding:5px 13px;border-radius:50px;border:none;background:transparent;color:var(--mid);font-size:12px;font-weight:700;cursor:pointer;transition:all .3s;font-family:'Outfit',sans-serif;letter-spacing:.5px;}
.list-lang-btn.active{background:var(--B);color:#fff;}
.list-lang-btn:hover:not(.active){background:var(--BL);color:var(--B);}

/* ─── CURRENCY TOGGLE ─── */
.currency-sw{display:flex;gap:3px;background:#fff;border:1px solid var(--hr);border-radius:50px;padding:4px;box-shadow:0 2px 8px rgba(0,0,0,.04);}
.currency-btn{padding:5px 14px;border-radius:50px;border:none;background:transparent;font-size:12px;font-weight:700;color:var(--mid);cursor:pointer;transition:all .3s;font-family:'Outfit',sans-serif;}
.currency-btn.active{background:var(--G);color:var(--BD);}
.currency-btn:hover:not(.active){background:var(--GL);color:var(--BD);}

/* ─── LAYOUT ─── */
.wrap{display:flex;min-height:100vh;padding-top:80px;}

/* ─── SIDEBAR ─── */
.sb{width:var(--sw);position:fixed;top:80px;left:0;bottom:0;background:#fff;border-right:1px solid var(--hr);z-index:50;overflow-y:auto;overflow-x:hidden;transition:transform .4s cubic-bezier(0.16,1,0.3,1);scrollbar-width:none;}
.sb::-webkit-scrollbar{display:none;}
.sb-line{height:4px;background:linear-gradient(90deg,var(--B),var(--G));}
body.lang-ku .sb-line,body.lang-ar .sb-line{background:linear-gradient(270deg,var(--B),var(--G));}
.sb-hd{padding:28px 24px 20px;border-bottom:1px solid var(--hr);display:flex;align-items:center;gap:14px;}
.sb-hd-icon{width:42px;height:42px;border-radius:12px;background:var(--BL);display:flex;align-items:center;justify-content:center;color:var(--B);font-size:16px;flex-shrink:0;}
.sb-hd-title{font-size:17px;font-weight:700;color:var(--ink);}
.sb-hd-sub{font-size:12px;color:var(--dim);margin-top:2px;}
.sb-inner{padding:24px;}
.fg{margin-bottom:20px;}
.fg-label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:var(--B);margin-bottom:8px;}
body.lang-ku .fg-label,body.lang-ar .fg-label{letter-spacing:0;font-family:'Noto Sans Arabic',sans-serif;}
.fc{width:100%;padding:12px 16px;font-family:'Outfit',sans-serif;font-size:14px;color:var(--ink);background:var(--bg);border:1px solid var(--hr);border-radius:12px;transition:all .3s ease;appearance:none;-webkit-appearance:none;}
body.lang-ku .fc,body.lang-ar .fc{font-family:'Noto Sans Arabic',sans-serif;text-align:right;}
.fc:focus{border-color:var(--B);background:#fff;box-shadow:0 0 0 4px rgba(48,59,151,.08);}
.fc::placeholder{color:var(--dim);}
.sel-wrap{position:relative;}
.sel-wrap::after{content:'\f107';font-family:'Font Awesome 6 Free';font-weight:900;position:absolute;right:16px;top:50%;transform:translateY(-50%);color:var(--B);font-size:12px;pointer-events:none;}
.sel-wrap select{padding-right:36px;cursor:pointer;}
.two-col{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
.divider{height:1px;background:var(--hr);margin:20px 0;}

/* Active filter tags */
.active-filters{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px;min-height:0;}
.af-tag{display:inline-flex;align-items:center;gap:6px;padding:5px 12px;background:var(--BL);border:1px solid rgba(48,59,151,.15);border-radius:20px;font-size:12px;font-weight:600;color:var(--BD);}
.af-tag button{background:none;border:none;color:var(--BD);cursor:pointer;font-size:11px;padding:0;line-height:1;opacity:.55;}
.af-tag button:hover{opacity:1;}

.btn-apply{width:100%;padding:14px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--B),var(--BD));color:#fff;font-size:14px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:all .3s ease;margin-bottom:12px;box-shadow:0 6px 20px rgba(48,59,151,.2);}
body.lang-ku .btn-apply,body.lang-ar .btn-apply{font-family:'Noto Sans Arabic',sans-serif;}
.btn-apply:hover{transform:translateY(-2px);box-shadow:0 10px 25px rgba(48,59,151,.35);}
.btn-reset{width:100%;padding:13px;border-radius:12px;background:#fff;border:1px solid var(--hr);font-size:14px;font-weight:600;color:var(--mid);cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:all .3s ease;}
body.lang-ku .btn-reset,body.lang-ar .btn-reset{font-family:'Noto Sans Arabic',sans-serif;}
.btn-reset:hover{border-color:var(--B);color:var(--B);background:var(--BL);}

/* ─── MAIN ─── */
.main{flex:1;margin-left:var(--sw);padding:40px 32px 80px;min-width:0;}
.pg-head{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:36px;}
.pg-tag{font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--G);display:flex;align-items:center;gap:10px;margin-bottom:8px;}
.pg-tag::before{content:'';width:30px;height:2px;background:var(--G);border-radius:2px;}
body.lang-ku .pg-tag,body.lang-ar .pg-tag{letter-spacing:0;}
.pg-title{font-family:'Libre Baskerville',serif;font-size:clamp(28px,4vw,44px);font-weight:700;color:var(--BD);line-height:1.1;letter-spacing:-.5px;}
body.lang-ku .pg-title,body.lang-ar .pg-title{font-family:'Noto Sans Arabic',sans-serif;letter-spacing:0;}
.pg-title em{font-style:italic;color:var(--B);font-weight:400;}
body.lang-ku .pg-title em,body.lang-ar .pg-title em{font-style:normal;}
.pg-head-right{display:flex;align-items:center;gap:12px;flex-wrap:wrap;}
.count-badge{display:flex;align-items:center;gap:10px;background:#fff;border:1px solid var(--hr);border-radius:50px;padding:10px 22px;font-size:14px;color:var(--mid);font-weight:500;box-shadow:0 4px 15px rgba(0,0,0,.03);}
.count-badge strong{color:var(--B);font-weight:700;font-size:16px;}
.count-dot{width:10px;height:10px;border-radius:50%;background:var(--G);flex-shrink:0;box-shadow:0 0 0 3px var(--GL);}

/* ─── GRID ─── */
.grid{display:block;width:100%;min-height:200px;margin:0 -12px;position:relative;}
/* Clearfix so grid has height */
.grid::after{content:'';display:table;clear:both;}

.pc{width:33.333%;padding:0 12px;margin-bottom:28px;float:left;box-sizing:border-box;}

/* ─── CARD ─── */
.card{background:var(--card);border-radius:20px;border:1px solid rgba(228,230,240,.8);overflow:hidden;display:flex;flex-direction:column;box-shadow:0 8px 24px rgba(13,17,39,.04);transition:all .4s cubic-bezier(.16,1,.3,1);position:relative;text-decoration:none;color:inherit;cursor:pointer;height:100%;}
.card:hover{transform:translateY(-10px);box-shadow:0 20px 40px rgba(48,59,151,.12);border-color:rgba(48,59,151,.15);}
.card::after{content:'';position:absolute;bottom:0;left:0;right:0;height:4px;z-index:4;background:linear-gradient(90deg,var(--B),var(--G));transform:scaleX(0);transform-origin:left;transition:transform .5s cubic-bezier(.16,1,.3,1);}
.card:hover::after{transform:scaleX(1);}

.ci{position:relative;height:240px;overflow:hidden;flex-shrink:0;}
.ci-bg{position:absolute;inset:0;background-size:cover;background-position:center;transition:transform .8s cubic-bezier(.16,1,.3,1);}
.card:hover .ci-bg{transform:scale(1.08);}
.ci::after{content:'';position:absolute;inset:0;background:linear-gradient(to top,rgba(13,17,39,.9) 0%,rgba(13,17,39,.2) 40%,transparent 100%);z-index:1;}
.ci-badges{position:absolute;top:16px;left:16px;right:16px;display:flex;justify-content:space-between;z-index:3;}
.badge{font-size:10px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;padding:6px 14px;border-radius:30px;backdrop-filter:blur(12px);font-family:'Outfit',sans-serif;}
.badge-type{background:rgba(48,59,151,.85);color:#fff;border:1px solid rgba(255,255,255,.2);}
.badge-sell{background:rgba(255,255,255,.95);color:var(--BD);}
.badge-rent{background:var(--G);color:var(--BD);}
.ci-price{position:absolute;bottom:16px;left:16px;right:16px;z-index:3;}
.ci-price-main{font-family:'Libre Baskerville',serif;font-size:20px;font-weight:700;color:#fff;line-height:1;text-shadow:0 4px 15px rgba(0,0,0,.5);}
.ci-price-sub{font-size:11px;font-weight:600;letter-spacing:1.5px;color:rgba(255,255,255,.65);margin-bottom:4px;text-transform:uppercase;}

.cb{padding:20px 20px 0;flex:1;display:flex;flex-direction:column;}
.cb-title{font-family:'Libre Baskerville',serif;font-size:17px;font-weight:700;color:var(--ink);line-height:1.4;margin-bottom:8px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical;transition:color .3s;}
.card:hover .cb-title{color:var(--B);}
.cb-loc{font-size:13px;color:var(--dim);font-weight:500;display:flex;align-items:center;gap:6px;margin-bottom:18px;}
.cb-loc i{color:var(--B);font-size:12px;}
.cb-feats{display:flex;gap:8px;margin-top:auto;padding-bottom:18px;}
.feat{flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;padding:12px 6px;background:var(--bg);border-radius:12px;transition:all .3s ease;}
.card:hover .feat{background:var(--BL);transform:translateY(-2px);}
.feat i{font-size:14px;color:var(--B);}
.feat-v{font-family:'Libre Baskerville',serif;font-size:16px;font-weight:700;color:var(--BD);line-height:1;}
.feat-l{font-size:10px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:var(--dim);}
body.lang-ku .feat-l,body.lang-ar .feat-l{letter-spacing:0;font-size:11px;font-family:'Noto Sans Arabic',sans-serif;}

.cta{padding:0 20px 20px;}
.cta-btn{display:flex;align-items:center;justify-content:space-between;padding:12px 18px;border-radius:12px;background:#fff;border:1px solid var(--hr);font-size:14px;font-weight:600;color:var(--B);transition:all .3s ease;}
body.lang-ku .cta-btn,body.lang-ar .cta-btn{font-family:'Noto Sans Arabic',sans-serif;}
.card:hover .cta-btn{background:var(--B);color:#fff;border-color:var(--B);box-shadow:0 8px 20px rgba(48,59,151,.25);}
.cta-arr{width:30px;height:30px;border-radius:8px;background:var(--BL);display:flex;align-items:center;justify-content:center;font-size:12px;transition:all .3s;}
.card:hover .cta-arr{background:rgba(255,255,255,.2);}

/* ─── EMPTY STATE ─── */
.empty-state{
  clear:both;
  display:none; /* hidden by default — shown via JS */
  width:100%;
  padding:80px 40px;
  text-align:center;
  background:#fff;
  border-radius:24px;
  border:2px dashed var(--hr);
  margin-top:8px;
}
.empty-state.visible{display:block;}
.empty-ic{width:80px;height:80px;border-radius:50%;background:var(--BL);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:28px;color:var(--B);}
.empty-state h3{font-family:'Libre Baskerville',serif;font-size:22px;color:var(--BD);margin-bottom:10px;}
body.lang-ku .empty-state h3,body.lang-ar .empty-state h3{font-family:'Noto Sans Arabic',sans-serif;}
.empty-state p{font-size:14px;color:var(--dim);margin-bottom:24px;}
body.lang-ku .empty-state p,body.lang-ar .empty-state p{font-family:'Noto Sans Arabic',sans-serif;}
.empty-reset-btn{display:inline-flex;align-items:center;gap:8px;padding:12px 28px;background:var(--B);color:#fff;border-radius:50px;font-size:14px;font-weight:600;border:none;cursor:pointer;transition:all .3s;}
.empty-reset-btn:hover{background:var(--BD);transform:translateY(-2px);}
body.lang-ku .empty-reset-btn,body.lang-ar .empty-reset-btn{font-family:'Noto Sans Arabic',sans-serif;}

/* ─── PAGINATION ─── */
.pgn{clear:both;padding-top:50px;display:flex;flex-direction:column;align-items:center;gap:16px;}
.pgn-info{font-size:14px;color:var(--mid);font-weight:500;}
.pgn-info strong{color:var(--B);font-weight:700;}
body.lang-ku .pgn-info,body.lang-ar .pgn-info{font-family:'Noto Sans Arabic',sans-serif;}
.pgn nav>div.sm\:hidden{display:none !important;}
.pgn nav>div>div:first-child{display:none !important;}
.pgn nav>div>div:last-child>span,.pgn nav ul.pagination{display:inline-flex !important;align-items:center;justify-content:center;gap:10px !important;box-shadow:none !important;margin:0 !important;padding:0 !important;}
.pgn nav a.relative,.pgn nav span[aria-current]>span,.pgn nav span[aria-disabled]>span,.pgn nav li>a,.pgn nav li>span{display:flex !important;align-items:center !important;justify-content:center !important;width:44px !important;height:44px !important;min-width:44px !important;padding:0 !important;margin:0 !important;border-radius:12px !important;background:#fff !important;border:1.5px solid var(--hr) !important;color:var(--mid) !important;font-size:15px !important;font-weight:600 !important;text-decoration:none !important;box-shadow:0 4px 10px rgba(13,17,39,.03) !important;transition:all .3s !important;z-index:1 !important;}
.pgn nav a.relative:hover,.pgn nav li>a:hover{border-color:var(--B) !important;color:var(--B) !important;background:var(--BL) !important;transform:translateY(-3px) !important;}
.pgn nav span[aria-current="page"]>span{background:linear-gradient(135deg,var(--B),var(--BD)) !important;color:#fff !important;border-color:transparent !important;box-shadow:0 8px 24px rgba(48,59,151,.3) !important;transform:scale(1.05) !important;}
.pgn nav span[aria-disabled="true"]>span{background:var(--bg) !important;color:var(--dim) !important;cursor:not-allowed !important;}
.pgn nav svg{width:18px !important;height:18px !important;display:block !important;}

/* ─── MOBILE ─── */
.mob-btn{display:none;position:fixed;bottom:24px;left:50%;transform:translateX(-50%);padding:14px 28px;background:var(--BD);color:#fff;border-radius:50px;font-size:14px;font-weight:600;align-items:center;gap:10px;z-index:100;border:none;cursor:pointer;box-shadow:0 10px 35px rgba(26,34,90,.5);}
.mob-btn span.dot{width:8px;height:8px;border-radius:50%;background:var(--G);display:block;}
.sb-overlay{position:fixed;inset:0;background:rgba(13,17,39,.6);z-index:30;display:none;backdrop-filter:blur(6px);}

@media(max-width:1280px){.pc{width:50%;}}
@media(max-width:1024px){
  .sb{transform:translateX(-100%);}
  .sb.open{transform:translateX(0);box-shadow:0 0 80px rgba(0,0,0,.4);}
  .main{margin-left:0;padding:24px 20px 100px;}
  .mob-btn{display:flex;}
}
@media(max-width:600px){.pc{width:100%;padding:0;}}
</style>
</head>
<body>

@php $navbarStyle = 'navbar-light'; @endphp
@include('navbar')

<div class="wrap">

{{-- SIDEBAR --}}
<aside class="sb" id="sb">
  <div class="sb-line"></div>
  <div class="sb-hd">
    <div class="sb-hd-icon"><i class="fas fa-sliders-h"></i></div>
    <div>
      <div class="sb-hd-title" data-i18n="sbTitle">فلتەر</div>
      <div class="sb-hd-sub" data-i18n="sbSub">گەڕانەکەت باشتر بکە</div>
    </div>
  </div>
  <div class="sb-inner">
    <div class="fg">
      <label class="fg-label" data-i18n="lbType">جۆری لیست</label>
      <div class="sel-wrap">
        <select id="property-type-dropdown" class="fc">
          <option value="" data-i18n="optAll">کڕین یان کرێ</option>
          <option value="sell" data-i18n="optBuy">کڕین</option>
          <option value="rent" data-i18n="optRent">کرێ</option>
        </select>
      </div>
    </div>
    <div class="fg">
      <label class="fg-label" data-i18n="lbCity">شار</label>
      <div class="sel-wrap">
        <select id="city-dropdown" class="fc"><option value="">...</option></select>
      </div>
    </div>
    <div class="fg">
      <label class="fg-label" data-i18n="lbArea">ناوچە</label>
      <div class="sel-wrap">
        <select id="area-dropdown" class="fc" disabled>
          <option value="" data-i18n="optAreaFirst">یەکەم شار هەڵبژێرە</option>
        </select>
      </div>
    </div>
    <div class="fg">
      <label class="fg-label" data-i18n="lbPurpose">جۆری خانوو</label>
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
      <label class="fg-label" data-i18n="lbKeyword">کلیلەوشە</label>
      <input type="text" id="search-keywords-input" class="fc" placeholder="پووڵ، باخچە..." data-ph-i18n="phKeyword">
    </div>
    <div class="fg">
      <label class="fg-label" data-i18n="lbPrice">نرخ</label>
      <div class="two-col">
        <input type="number" id="min-price-input" class="fc" placeholder="کەمترین" data-ph-i18n="phMin">
        <input type="number" id="max-price-input" class="fc" placeholder="زۆرترین" data-ph-i18n="phMax">
      </div>
    </div>
    <div class="divider"></div>
    <div class="active-filters" id="activeFilterTags"></div>
    <button class="btn-apply" id="search-button">
      <i class="fas fa-search"></i>&nbsp;<span data-i18n="btnApply">فلتەر جێبەجێ بکە</span>
    </button>
    <button class="btn-reset" id="clear-filters">
      <i class="fas fa-rotate-left"></i>&nbsp;<span data-i18n="btnReset">پاکی بکەرەوە</span>
    </button>
  </div>
</aside>

{{-- MAIN --}}
<main class="main">
  <div class="pg-head">
    <div class="pg-head-left">
      <div class="pg-tag" data-i18n="pgTag">خانووبەرەی کوردستان</div>
      <div class="pg-title">Dream Mulk &mdash; <em data-i18n="pgTitleEm">خانووەکان</em></div>
    </div>
    <div class="pg-head-right">
      <div class="currency-sw">
        <button class="currency-btn active" data-currency="iqd">IQD</button>
        <button class="currency-btn" data-currency="usd">USD</button>
      </div>
      <div class="list-lang-sw">
        <button class="list-lang-btn active" data-lang="ku">کو</button>
        <button class="list-lang-btn" data-lang="en">EN</button>
        <button class="list-lang-btn" data-lang="ar">ع</button>
      </div>
      <div class="count-badge">
        <div class="count-dot"></div>
        <span><strong id="results-counter">{{ $properties->total() }}</strong>&nbsp;<span data-i18n="propLabel">خانوو</span></span>
      </div>
    </div>
  </div>

  {{-- GRID --}}
  <div class="grid" id="propertiesGrid">
    @foreach($properties as $property)
    @php
      $priceUsd = $property->price['usd'] ?? 0;
      $priceIqd = isset($property->price['iqd']) ? $property->price['iqd'] : ($priceUsd * 1300);
      $lt = strtolower($property->listing_type ?? '');
    @endphp
    <div class="pc"
      data-type="{{ strtolower($property->type['category'] ?? '') }}"
      data-listing="{{ $lt }}"
      data-price-usd="{{ $priceUsd }}"
      data-price-iqd="{{ $priceIqd }}"
      data-date="{{ $property->created_at->timestamp }}">
      <a href="{{ route('property.PropertyDetail', ['property_id' => $property->id]) }}" class="card">
        <div class="ci">
          <div class="ci-bg" style="background-image:url('{{ !empty($property->images) ? $property->images[0] : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=700&q=80' }}')"></div>
          <div class="ci-badges">
            <span class="badge badge-type">{{ $property->type['category'] ?? 'Property' }}</span>
            <span class="badge {{ $lt === 'rent' ? 'badge-rent' : 'badge-sell' }}">{{ ucfirst($lt ?: 'N/A') }}</span>
          </div>
          <div class="ci-price">
            <div class="ci-price-sub price-label">IQD</div>
            <div class="ci-price-main price-display" data-usd="{{ $priceUsd }}" data-iqd="{{ $priceIqd }}">
              {{ number_format($priceIqd) }}
            </div>
          </div>
        </div>
        <div class="cb">
          <div class="cb-title">{{ $property->name['en'] ?? 'Exclusive Property' }}</div>
          <div class="cb-loc"><i class="fas fa-location-dot"></i> {{ $property->address ?? 'Kurdistan Region' }}</div>
          <div class="cb-feats">
            <div class="feat"><i class="fas fa-bed"></i><span class="feat-v">{{ $property->rooms['bedroom']['count'] ?? 0 }}</span><span class="feat-l" data-i18n="featBeds">جێخەو</span></div>
            <div class="feat"><i class="fas fa-bath"></i><span class="feat-v">{{ $property->rooms['bathroom']['count'] ?? 0 }}</span><span class="feat-l" data-i18n="featBaths">حەمام</span></div>
            <div class="feat"><i class="fas fa-ruler-combined"></i><span class="feat-v">{{ $property->area ?? '—' }}</span><span class="feat-l">m²</span></div>
          </div>
        </div>
        <div class="cta">
          <div class="cta-btn">
            <span data-i18n="ctaView">وردەکاریەکان</span>
            <span class="cta-arr"><i class="fas fa-arrow-right"></i></span>
          </div>
        </div>
      </a>
    </div>
    @endforeach
  </div>

  {{-- EMPTY STATE — shown when filter returns 0 results --}}
  <div class="empty-state" id="emptyState">
    <div class="empty-ic"><i class="fas fa-house-circle-xmark"></i></div>
    <h3 data-i18n="emptyTitle">هیچ خانوویەک نەدۆزراوەتەوە</h3>
    <p data-i18n="emptyDesc">فلتەرەکانت بگۆڕە بۆ دۆزینەوەی خانووی گونجاو.</p>
    <button class="empty-reset-btn" id="emptyResetBtn">
      <i class="fas fa-rotate-left"></i>&nbsp;<span data-i18n="btnReset">پاکی بکەرەوە</span>
    </button>
  </div>

  @if($properties->count() === 0)
  {{-- Server-side empty (no results from DB at all) --}}
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

<button class="mob-btn" id="mobBtn">
  <span class="dot"></span><i class="fas fa-sliders-h"></i>&nbsp;<span data-i18n="mobFilter">فلتەرەکان</span>
</button>
<div class="sb-overlay" id="sbOverlay"></div>
</div>

<!-- ══ i18n CLASS ══ -->
<script>
class DreamMulkI18n {
  constructor(o={}){
    this.storageKey=o.storageKey||'dm_lang';
    this.defaultLang=o.defaultLang||'ku';
    this.onLangChange=o.onLangChange||null;
    this._current=this.defaultLang;
  }
  init(){
    const saved=localStorage.getItem(this.storageKey)||this.defaultLang;
    this.setLang(saved);
  }
  setLang(lang){
    if(!this.translations[lang])return;
    this._current=lang;
    localStorage.setItem(this.storageKey,lang);
    const T=this.translations[lang];
    document.documentElement.dir=T.dir;
    document.body.classList.remove('lang-ku','lang-en','lang-ar','rtl');
    document.body.classList.add('lang-'+lang);
    if(T.dir==='rtl')document.body.classList.add('rtl');
    document.querySelectorAll('.list-lang-btn').forEach(b=>{
      b.classList.toggle('active',b.getAttribute('data-lang')===lang);
    });
    document.querySelectorAll('[data-i18n]').forEach(el=>{
      const k=el.getAttribute('data-i18n');
      if(T[k]!==undefined)el.textContent=T[k];
    });
    document.querySelectorAll('[data-ph-i18n]').forEach(el=>{
      const k=el.getAttribute('data-ph-i18n');
      if(T[k]!==undefined)el.placeholder=T[k];
    });
    // Translate select options
    const typeSel=document.getElementById('property-type-dropdown');
    if(typeSel){
      const map={'':'optAll','sell':'optBuy','rent':'optRent'};
      typeSel.querySelectorAll('option').forEach(o=>{
        const k=map[o.value];
        if(k&&T[k])o.textContent=T[k];
      });
    }
    if(typeof this.onLangChange==='function')this.onLangChange(lang,T);
  }
  getCurrentLang(){return this._current;}
  t(key){return(this.translations[this._current]||{})[key]||key;}

  translations={
    ku:{
      dir:'rtl',
      sbTitle:'فلتەر', sbSub:'گەڕانەکەت باشتر بکە',
      lbType:'جۆری لیست', lbCity:'شار', lbArea:'ناوچە',
      lbPurpose:'جۆری خانوو', lbKeyword:'کلیلەوشە', lbPrice:'نرخ',
      optAll:'کڕین یان کرێ', optBuy:'کڕین', optRent:'کرێ',
      optAreaFirst:'یەکەم شار هەڵبژێرە',
      optAllTypes:'هەموو جۆرەکان',
      optVilla:'ڤیلا', optHouse:'خانوو', optApart:'ئەپارتمان', optComm:'بازرگانی',
      phKeyword:'پووڵ، باخچە...', phMin:'کەمترین', phMax:'زۆرترین',
      btnApply:'فلتەر جێبەجێ بکە', btnReset:'پاکی بکەرەوە',
      pgTag:'خانووبەرەی کوردستان', pgTitleEm:'خانووەکان',
      propLabel:'خانوو',
      featBeds:'جێخەو', featBaths:'حەمام', ctaView:'وردەکاریەکان',
      emptyTitle:'هیچ خانوویەک نەدۆزراوەتەوە',
      emptyDesc:'فلتەرەکانت بگۆڕە بۆ دۆزینەوەی خانووی گونجاو.',
      pgPage:'لاپەڕە', pgOf:'لە', pgTotal:'خانوو بەکۆی هەموو',
      mobFilter:'فلتەرەکان',
    },
    en:{
      dir:'ltr',
      sbTitle:'Filters', sbSub:'Refine your search',
      lbType:'Listing Type', lbCity:'City', lbArea:'Area',
      lbPurpose:'Property Type', lbKeyword:'Keywords', lbPrice:'Price',
      optAll:'Buy or Rent', optBuy:'Buy', optRent:'Rent',
      optAreaFirst:'Select city first',
      optAllTypes:'All Types',
      optVilla:'Villa', optHouse:'House', optApart:'Apartment', optComm:'Commercial',
      phKeyword:'Pool, Garden...', phMin:'Min', phMax:'Max',
      btnApply:'Apply Filters', btnReset:'Reset Filters',
      pgTag:'Kurdistan Real Estate', pgTitleEm:'Properties',
      propLabel:'Properties',
      featBeds:'Beds', featBaths:'Baths', ctaView:'View Details',
      emptyTitle:'No Properties Found',
      emptyDesc:'Try adjusting your filters to find matching properties.',
      pgPage:'Page', pgOf:'of', pgTotal:'total listings',
      mobFilter:'Filters',
    },
    ar:{
      dir:'rtl',
      sbTitle:'الفلاتر', sbSub:'حسّن بحثك',
      lbType:'نوع الإدراج', lbCity:'المدينة', lbArea:'المنطقة',
      lbPurpose:'نوع العقار', lbKeyword:'كلمات مفتاحية', lbPrice:'السعر',
      optAll:'شراء أو إيجار', optBuy:'شراء', optRent:'إيجار',
      optAreaFirst:'اختر المدينة أولاً',
      optAllTypes:'جميع الأنواع',
      optVilla:'فيلا', optHouse:'منزل', optApart:'شقة', optComm:'تجاري',
      phKeyword:'مسبح، حديقة...', phMin:'الأدنى', phMax:'الأقصى',
      btnApply:'تطبيق الفلاتر', btnReset:'مسح الكل',
      pgTag:'عقارات كردستان', pgTitleEm:'العقارات',
      propLabel:'عقار',
      featBeds:'غرف', featBaths:'حمامات', ctaView:'عرض التفاصيل',
      emptyTitle:'لا توجد عقارات',
      emptyDesc:'جرّب تعديل الفلاتر للعثور على عقارات مناسبة.',
      pgPage:'صفحة', pgOf:'من', pgTotal:'إجمالي العقارات',
      mobFilter:'الفلاتر',
    },
  };
}
</script>

<!-- ══ LocationSelector CLASS ══ -->
<script>
class LocationSelector{
  constructor(o={}){
    this.cId=o.citySelectId||'c';this.aId=o.areaSelectId||'a';
    this.onC=o.onCityChange||null;this.onA=o.onAreaChange||null;
    this.cities=[];this.curC=o.selectedCityId||null;this.curA=o.selectedAreaId||null;
  }
  async init(){
    try{await this.loadCities();this.bind();if(this.curC)await this.loadAreas(this.curC);}
    catch(e){console.error(e);}
  }
  async loadCities(){
    const el=document.getElementById(this.cId);
    try{
      const r=await fetch('/v1/api/location/branches',{headers:{'Accept-Language':'en'}});
      const d=await r.json();
      if(d.success&&Array.isArray(d.data)){this.cities=d.data;this.fillCities();}
    }catch(e){if(el)el.innerHTML='<option value="">All Cities</option>';}
  }
  fillCities(){
    const el=document.getElementById(this.cId);if(!el)return;
    el.innerHTML='<option value="">All Cities</option>';
    [...this.cities].sort((a,b)=>a.city_name_en.localeCompare(b.city_name_en)).forEach(c=>{
      const o=document.createElement('option');
      o.value=c.id;o.textContent=c.city_name_en;o.dataset.nameEn=c.city_name_en;
      if(c.id==this.curC)o.selected=true;
      el.appendChild(o);
    });
  }
  async loadAreas(id){
    const el=document.getElementById(this.aId);if(!el)return;
    el.innerHTML='<option value="">Loading…</option>';el.disabled=true;
    try{
      const r=await fetch(`/v1/api/location/branches/${id}/areas`,{headers:{'Accept-Language':'en'}});
      const d=await r.json();if(d.success&&d.data)this.fillAreas(d.data);
    }catch(e){}finally{el.disabled=false;}
  }
  fillAreas(areas){
    const el=document.getElementById(this.aId);if(!el)return;
    el.innerHTML='<option value="">All Areas</option>';
    [...areas].sort((a,b)=>a.area_name_en.localeCompare(b.area_name_en)).forEach(a=>{
      const o=document.createElement('option');
      o.value=a.id;o.textContent=a.area_name_en;o.dataset.nameEn=a.area_name_en;
      if(a.id==this.curA)o.selected=true;
      el.appendChild(o);
    });
  }
  bind(){
    const c=document.getElementById(this.cId),a=document.getElementById(this.aId);
    if(c)c.addEventListener('change',async e=>{
      if(e.target.value){await this.loadAreas(e.target.value);if(this.onC)this.onC(e.target.value);}
      else{if(a){a.innerHTML='<option value="">Select City First</option>';a.disabled=true;}if(this.onC)this.onC(null);}
    });
    if(a)a.addEventListener('change',e=>{if(this.onA)this.onA(e.target.value);});
  }
}
</script>

<!-- ══ MAIN LOGIC ══ -->
<script>
$(function(){

  // ── i18n ──
  const i18n = new DreamMulkI18n({defaultLang:'ku'});
  document.querySelectorAll('.list-lang-btn').forEach(btn=>{
    btn.addEventListener('click',()=>i18n.setLang(btn.getAttribute('data-lang')));
  });
  i18n.init();

  // ── Currency ──
  let currency = 'iqd';
  function applyCurrency(cur){
    currency = cur;
    document.querySelectorAll('.currency-btn').forEach(b=>{
      b.classList.toggle('active',b.getAttribute('data-currency')===cur);
    });
    document.querySelectorAll('.price-display').forEach(el=>{
      const v = cur==='usd'
        ? '$'+Number(el.dataset.usd).toLocaleString()
        : Number(el.dataset.iqd).toLocaleString()+' IQD';
      el.textContent=v;
    });
    document.querySelectorAll('.price-label').forEach(el=>{
      el.textContent = cur==='usd' ? 'USD' : 'IQD';
    });
  }
  document.querySelectorAll('.currency-btn').forEach(btn=>{
    btn.addEventListener('click',()=>applyCurrency(btn.getAttribute('data-currency')));
  });

  // ── Isotope ──
  var $g=$('#propertiesGrid'), isoReady=false;

  // Use a simple float layout fallback if no cards or Isotope fails
  function initIso(){
    if($('.pc').length===0){
      showEmpty(); return;
    }
    $g.imagesLoaded(function(){
      $g.isotope({
        itemSelector:'.pc',
        percentPosition:true,
        layoutMode:'fitRows'
      });
      isoReady=true;
      // Force a layout refresh after short delay
      setTimeout(()=>{ $g.isotope('layout'); },100);
      applyUrlParams();
    });
  }
  initIso();

  // ── Empty state toggle ──
  function showEmpty(){
    document.getElementById('emptyState').classList.add('visible');
    $('#results-counter').text(0);
  }
  function hideEmpty(){
    document.getElementById('emptyState').classList.remove('visible');
  }

  // ── Filter ──
  function runFilter(){
    if(!isoReady) return;
    var kw  = $('#search-keywords-input').val().toLowerCase().trim();
    var mn  = parseFloat($('#min-price-input').val()) || 0;
    var mx  = parseFloat($('#max-price-input').val()) || Infinity;
    var tp  = $('#purpose-dropdown').val().toLowerCase();
    var ls  = $('#property-type-dropdown').val().toLowerCase();
    var cy  = ($('#city-dropdown option:selected').data('nameEn')||'').toLowerCase();
    var ar  = ($('#area-dropdown option:selected').data('nameEn')||'').toLowerCase();

    $g.isotope({filter:function(){
      var $t=$(this), tx=$t.text().toLowerCase();
      var price = currency==='usd'
        ? parseFloat($t.attr('data-price-usd'))
        : parseFloat($t.attr('data-price-iqd'));
      return (!kw||tx.includes(kw))
        && price>=mn && price<=mx
        && (!tp||$t.attr('data-type')===tp)
        && (!ls||$t.attr('data-listing')===ls)
        && (!cy||tx.includes(cy))
        && (!ar||tx.includes(ar));
    }});

    setTimeout(()=>{
      const iso = $g.data('isotope');
      const count = iso ? iso.filteredItems.length : 0;
      $('#results-counter').text(count);
      if(count===0) showEmpty(); else hideEmpty();
    },150);

    buildFilterTags();
  }

  // ── URL params auto-apply ──
  function applyUrlParams(){
    const p = new URLSearchParams(window.location.search);
    const type   = p.get('type')   || '';
    const search = p.get('search') || '';
    const city   = p.get('city')   || '';

    if(type)   $('#property-type-dropdown').val(type);
    if(search) $('#search-keywords-input').val(search);

    // City matching happens after LocationSelector loads
    // (handled in locSel.init().then())
    if(type||search||city) runFilter();
  }

  // ── Active filter tags ──
  function buildFilterTags(){
    const tags=[];
    const type=$('#property-type-dropdown').val();
    const kw=$('#search-keywords-input').val();
    const city=$('#city-dropdown option:selected').text();
    const area=$('#area-dropdown option:selected').text();
    const mn=$('#min-price-input').val();
    const mx=$('#max-price-input').val();

    if(type) tags.push({
      label: type==='sell' ? i18n.t('optBuy') : i18n.t('optRent'),
      clear:()=>$('#property-type-dropdown').val('')
    });
    if(kw) tags.push({label:kw, clear:()=>$('#search-keywords-input').val('')});
    if(city && city!=='All Cities' && city!=='...') tags.push({
      label:city, clear:()=>{$('#city-dropdown').val('');$('#area-dropdown').html('<option value="">Select City First</option>').prop('disabled',true);}
    });
    if(area && area!=='All Areas' && area!=='Select City First') tags.push({
      label:area, clear:()=>$('#area-dropdown').val('')
    });
    if(mn) tags.push({label:'≥ '+Number(mn).toLocaleString(), clear:()=>$('#min-price-input').val('')});
    if(mx) tags.push({label:'≤ '+Number(mx).toLocaleString(), clear:()=>$('#max-price-input').val('')});

    const $wrap=$('#activeFilterTags').empty();
    tags.forEach(t=>{
      const $tag=$('<div class="af-tag"></div>').text(t.label);
      const $x=$('<button title="remove">✕</button>').on('click',()=>{t.clear();runFilter();});
      $tag.append($x);
      $wrap.append($tag);
    });
  }

  // ── LocationSelector ──
  const urlCity = new URLSearchParams(window.location.search).get('city') || '';
  const locSel = new LocationSelector({
    citySelectId:'city-dropdown',
    areaSelectId:'area-dropdown',
    onCityChange:()=>{ runFilter(); },
    onAreaChange:()=>{ runFilter(); },
  });
  locSel.init().then(()=>{
    if(urlCity){
      $('#city-dropdown option').each(function(){
        if(($(this).data('nameEn')||'').toLowerCase()===urlCity.toLowerCase()){
          $('#city-dropdown').val($(this).val()).trigger('change');
        }
      });
    }
    if(new URLSearchParams(window.location.search).toString()) runFilter();
  });

  // ── Buttons ──
  $('#search-button').on('click', runFilter);
  $('#clear-filters, #emptyResetBtn').on('click',function(){
    $('input.fc').val('');
    $('select.fc').prop('selectedIndex',0);
    $('#area-dropdown').html('<option value="">Select City First</option>').prop('disabled',true);
    hideEmpty();
    if(isoReady) $g.isotope({filter:'*'});
    $('#activeFilterTags').empty();
    setTimeout(()=>$('#results-counter').text($('.pc').length),150);
    history.replaceState(null,'',window.location.pathname);
  });

  // ── Mobile sidebar ──
  const open=()=>{$('#sb').addClass('open');$('#sbOverlay').fadeIn(200);$('body').css('overflow','hidden');};
  const close=()=>{$('#sb').removeClass('open');$('#sbOverlay').fadeOut(200);$('body').css('overflow','');};
  $('#mobBtn').on('click',open);
  $('#sbOverlay').on('click',close);
  $(window).on('resize',function(){
    if(window.innerWidth>1024) close();
    if(isoReady) $g.isotope('layout');
  });

});
</script>
</body>
</html>