<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>{{ $property->name['en'] ?? 'Property Details' }} — Dream Mulk</title>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,800;1,400&family=DM+Sans:wght@300;400;500;600&family=Cinzel:wght@400;600&family=Noto+Naskh+Arabic:wght@400;500;600;700&family=Noto+Sans+Arabic:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<style>
/* ═══════════════════════════════
   RESET & VARIABLES
═══════════════════════════════ */
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
:root{
  --deep:#06091e;--P:#303b97;--PD:#1a225a;--PM:#232d7a;
  --G:#d4af37;--GL:#e8cb6a;--GP:#f5e8c0;
  --surf:rgba(255,255,255,.035);--brd:rgba(255,255,255,.07);
  --txt:rgba(255,255,255,.88);--mu:rgba(255,255,255,.42);
  --E:cubic-bezier(.16,1,.3,1);
  --font-ar: 'Noto Naskh Arabic', 'Noto Sans Arabic', serif;
  --font-ar-ui: 'Noto Sans Arabic', sans-serif;
}
html{scroll-behavior:smooth;}
body{font-family:'DM Sans',sans-serif;background:var(--deep);color:var(--txt);line-height:1.6;overflow-x:hidden; transition: background 0.3s;}
a{text-decoration:none;color:inherit;}
img{display:block;}

/* RTL & Arabic/Kurdish Fonts overrides */
body.lang-ku, body.lang-ar { font-family: var(--font-ar-ui); }
body.lang-ku *:not(i):not([class*="fa-"]), body.lang-ar *:not(i):not([class*="fa-"]) {
  font-family: var(--font-ar-ui); line-height: 1.8;
}
body.lang-ku .ptitle, body.lang-ar .ptitle,
body.lang-ku .sh, body.lang-ar .sh,
body.lang-ku .ctitle, body.lang-ar .ctitle {
  font-family: var(--font-ar) !important;
}

/* ═══════════════════════════════
   HERO GALLERY
═══════════════════════════════ */
.hero{position:relative;height:72vh;min-height:520px;background:#000;overflow:hidden; direction: ltr !important;}
.swiper-hero{width:100%;height:100%;}
.swiper-hero .swiper-slide img{
  width:100%;height:100%;object-fit:cover;
  filter:brightness(.78);transition:transform 8s ease, filter .6s;
}
.swiper-hero .swiper-slide-active img{transform:scale(1.05);filter:brightness(.72);}

.hero-grad{
  position:absolute;inset:0;z-index:2;pointer-events:none;
  background:
    linear-gradient(to top, rgba(6,9,30,1) 0%, rgba(6,9,30,.5) 28%, transparent 55%),
    linear-gradient(to right, rgba(6,9,30,.5) 0%, transparent 45%),
    linear-gradient(to bottom, rgba(6,9,30,.35) 0%, transparent 25%);
}

.hero-badges{
  position:absolute;top:28px;inset-inline-start:28px;z-index:10;
  display:flex;gap:10px;flex-wrap:wrap;
}
.hbadge{
  display:inline-flex;align-items:center;gap:7px;
  padding:8px 18px;border-radius:50px;
  font-size:10.5px;font-weight:700;letter-spacing:2px;text-transform:uppercase;
  backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);
}
body.lang-ku .hbadge, body.lang-ar .hbadge { letter-spacing: 0; font-size: 11.5px; }
.hbadge-v{background:rgba(16,185,129,.82);color:#fff;}
.hbadge-f{background:rgba(212,175,55,.9);color:#1a225a;}
.hbadge-t{background:rgba(48,59,151,.8);color:#fff;border:1px solid rgba(212,175,55,.4);}

.hero-acts{position:absolute;top:28px;inset-inline-end:28px;z-index:10;display:flex;gap:10px;}
.hact{
  width:44px;height:44px;border-radius:50%;cursor:pointer;
  background:rgba(6,9,30,.55);border:1px solid rgba(255,255,255,.15);
  display:flex;align-items:center;justify-content:center;
  color:#fff;font-size:15px;transition:all .35s var(--E);
}
.hact:hover{background:var(--G);border-color:var(--G);color:var(--PD);transform:scale(1.1);}

.photo-count{
  position:absolute;bottom:28px;inset-inline-start:28px;z-index:10;
  display:inline-flex;align-items:center;gap:8px;
  padding:8px 18px;border-radius:50px;
  background:rgba(6,9,30,.7);backdrop-filter:blur(12px);
  border:1px solid rgba(255,255,255,.12);font-size:12px;color:rgba(255,255,255,.7);
}
.photo-count i{color:var(--G);}

.thumb-strip{
  position:absolute;bottom:28px;inset-inline-end:28px;z-index:10;
  display:flex;gap:8px;
}
.th{
  width:64px;height:46px;border-radius:10px;overflow:hidden;
  border:2px solid transparent;cursor:pointer;opacity:.55;
  transition:all .3s var(--E);flex-shrink:0;
}
.th img{width:100%;height:100%;object-fit:cover;}
.th:hover,.th.active{border-color:var(--G);opacity:1;}

.swiper-button-prev,.swiper-button-next{
  width:46px;height:46px;border-radius:50%;
  background:rgba(6,9,30,.7);border:1px solid rgba(255,255,255,.14);
  backdrop-filter:blur(8px);
}
.swiper-button-prev::after,.swiper-button-next::after{font-size:13px;color:#fff;font-weight:900;}
.swiper-button-prev:hover,.swiper-button-next:hover{background:var(--G);}
.swiper-button-prev:hover::after,.swiper-button-next:hover::after{color:var(--PD);}
.swiper-pagination-bullet{background:rgba(255,255,255,.45);opacity:1;transition:all .3s;}
.swiper-pagination-bullet-active{background:var(--G);width:24px;border-radius:4px;}

/* ═══════════════════════════════
   PAGE LAYOUT
═══════════════════════════════ */
.outer{max-width:1400px;margin:0 auto;padding:0 24px 100px;}
.grid{
  display:grid;
  grid-template-columns:1fr 380px;
  gap:24px;
  margin-top:-90px;
  position:relative;z-index:10;
}

/* ─── GLASS CARD ─── */
.gc{
  background:rgba(9,13,42,.9);
  border:1px solid var(--brd);
  border-radius:24px;
  backdrop-filter:blur(24px);
  -webkit-backdrop-filter:blur(24px);
  overflow:hidden;
}
.gcp{padding:36px;}

/* ─── SECTION HEADING ─── */
.sh{
  display:flex;align-items:center;gap:12px;
  font-family:'Playfair Display',serif;font-size:19px;font-weight:700;color:#fff;
  margin-bottom:22px;padding-bottom:16px;border-bottom:1px solid var(--brd);
}
.sh-ico{
  width:38px;height:38px;border-radius:11px;flex-shrink:0;
  background:rgba(212,175,55,.1);border:1px solid rgba(212,175,55,.22);
  display:flex;align-items:center;justify-content:center;font-size:15px;color:var(--G);
}

/* ─── TITLE CARD ─── */
.top-accent{border-top:3px solid var(--G);}
.eyebrow{
  font-family:'Cinzel',serif;font-size:9.5px;letter-spacing:5px;
  text-transform:uppercase;color:var(--G);
  display:flex;align-items:center;gap:10px;margin-bottom:14px;
}
body.lang-ku .eyebrow, body.lang-ar .eyebrow { font-family: var(--font-ar-ui); letter-spacing: 1px; font-size: 11px; }
.eyebrow::before{content:'';width:28px;height:1px;background:var(--G);}
.ptitle{
  font-family:'Playfair Display',serif;
  font-size:clamp(24px,3.5vw,40px);font-weight:800;line-height:1.2;
  color:#fff;margin-bottom:10px;
}
.paddr{display:flex;align-items:center;gap:8px;font-size:13.5px;color:var(--mu);}
.paddr i{color:var(--G);font-size:12px;}
.price-row{display:flex;align-items:flex-end;gap:14px;margin:24px 0 0;}
.price{
  font-family:'Playfair Display',serif;
  font-size:clamp(28px,4vw,46px);font-weight:800;
  color:var(--G);letter-spacing:-1.5px;line-height:1; direction: ltr;
}
.price-note{font-size:13px;color:var(--mu);margin-bottom:5px;}

/* ─── BENTO STATS ─── */
.bento{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin:26px 0 0;}
.bcard{
  background:var(--surf);border:1px solid var(--brd);border-radius:16px;
  padding:20px 12px;text-align:center;transition:all .4s var(--E);cursor:default;
}
.bcard:hover{
  background:rgba(212,175,55,.08);border-color:rgba(212,175,55,.32);
  transform:translateY(-5px);
  box-shadow:0 12px 32px rgba(212,175,55,.08);
}
.bcard i{font-size:20px;color:var(--G);margin-bottom:10px;display:block;}
.bv{font-family:'Playfair Display',serif;font-size:22px;font-weight:700;color:#fff;display:block;line-height:1;}
.bl{font-size:9.5px;letter-spacing:2px;text-transform:uppercase;color:var(--mu);margin-top:5px;display:block;}
body.lang-ku .bl, body.lang-ar .bl { font-family: var(--font-ar-ui); letter-spacing: 0; font-size: 11.5px; text-transform: none; }

/* ─── VIEW PROFILE BTN ─── */
.prof-btn{
  display:inline-flex;align-items:center;gap:10px;
  padding:13px 26px;margin-top:22px;
  background:rgba(212,175,55,.1);border:1px solid rgba(212,175,55,.28);
  color:var(--G);border-radius:14px;font-size:13.5px;font-weight:600;
  transition:all .4s var(--E);
}
.prof-btn:hover{background:rgba(212,175,55,.22);transform:translateX(5px);}
body.rtl .prof-btn:hover{transform:translateX(-5px);}
.prof-btn i{font-size:12px;}
body.rtl .prof-btn i.fa-arrow-right { transform: rotate(180deg); }

/* ─── DESCRIPTION ─── */
.desc{font-size:15px;line-height:2;color:var(--mu);font-weight:300;}

/* ─── SPECS ─── */
.specs{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
.srow{
  display:flex;align-items:center;justify-content:space-between;
  padding:13px 16px;
  background:var(--surf);border:1px solid var(--brd);border-radius:12px;
  transition:all .3s var(--E);
}
.srow:hover{border-color:rgba(212,175,55,.28);background:rgba(212,175,55,.05);}
.slbl{font-size:11.5px;color:var(--mu);font-weight:500;}
body.lang-ku .slbl, body.lang-ar .slbl { font-size: 13px; }
.sval{font-size:13px;font-weight:600;color:#fff;display:flex;align-items:center;gap:7px;}
.sval i{color:var(--G);font-size:11px;}

/* ─── AMENITIES ─── */
.pills{display:flex;flex-wrap:wrap;gap:10px;}
.pill{
  display:inline-flex;align-items:center;gap:8px;
  padding:9px 18px;border-radius:50px;font-size:13px;font-weight:500;
  background:var(--surf);border:1px solid var(--brd);color:var(--txt);
  transition:all .35s var(--E);
}
.pill:hover{background:rgba(212,175,55,.1);border-color:rgba(212,175,55,.38);color:var(--G);}
.pill i{color:var(--G);font-size:11px;}

/* ─── MAP ─── */
.map-wrap{height:370px;overflow:hidden; direction: ltr;} /* Keep map LTR to prevent tile breaking */
#prop-map{width:100%;height:100%;}
.leaflet-tile{filter:brightness(.55) saturate(.45) hue-rotate(200deg);}
.leaflet-container{background:var(--deep);}
.leaflet-attribution-flag{display:none!important;}
.leaflet-control-attribution{
  background:rgba(6,9,30,.8)!important;color:rgba(255,255,255,.4)!important;
  font-size:10px;border-radius:6px;padding:3px 8px;
}
.leaflet-control-attribution a{color:var(--G)!important;}
.leaflet-control-zoom a{
  background:rgba(9,13,42,.95)!important;color:rgba(255,255,255,.7)!important;
  border:1px solid var(--brd)!important;
}
.leaflet-control-zoom a:hover{background:var(--G)!important;color:var(--PD)!important;}
.leaflet-popup-content-wrapper{
  background:rgba(9,13,42,.98);border:1px solid rgba(212,175,55,.3);
  border-radius:16px;color:#fff;box-shadow:0 8px 32px rgba(0,0,0,.5);
}
.leaflet-popup-tip{background:rgba(9,13,42,.98);}
.leaflet-popup-content{margin:16px 18px;font-family:'DM Sans',sans-serif;}

/* ─── REPORT ─── */
.report-card{border-inline-start:3px solid rgba(239,68,68,.6);}
.report-card textarea{
  width:100%;background:var(--surf);border:1px solid var(--brd);
  color:#fff;border-radius:12px;padding:14px 16px;
  font-family:'DM Sans',sans-serif;font-size:14px;
  resize:none;outline:none;transition:border .3s;
}
body.lang-ku .report-card textarea, body.lang-ar .report-card textarea { font-family: var(--font-ar-ui); }
.report-card textarea:focus{border-color:rgba(239,68,68,.5);}
.report-card textarea::placeholder{color:rgba(255,255,255,.2);}
.report-card textarea:focus{box-shadow:0 0 0 3px rgba(239,68,68,.07);}
.btn-report{
  display:inline-flex;align-items:center;gap:8px;
  padding:11px 24px;border-radius:11px;font-size:13px;font-weight:600;
  background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.3);
  color:#fca5a5;cursor:pointer;transition:all .3s;
}
.btn-report:hover{background:rgba(239,68,68,.22);}
.ok-box{
  display:flex;align-items:center;gap:8px;
  padding:12px 16px;border-radius:11px;font-size:13px;margin-top:14px;
  background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);color:#6ee7b7;
}

/* ═══════════════════════════════
   SIDEBAR
═══════════════════════════════ */
.sticky-col{position:sticky;top:24px;display:flex;flex-direction:column;gap:20px;}

/* ─── AGENT CARD ─── */
.agent-card{padding:30px;text-align:center;}
.ag-avatar{
  width:76px;height:76px;border-radius:50%;overflow:hidden;
  border:2.5px solid var(--G);margin:0 auto 14px;
  box-shadow:0 0 0 5px rgba(212,175,55,.12),0 8px 24px rgba(0,0,0,.4);
}
.ag-avatar img{width:100%;height:100%;object-fit:cover;}
.ag-name{font-family:'Playfair Display',serif;font-size:18px;font-weight:700;color:#fff;margin-bottom:3px;}
.ag-role{
  font-size:10.5px;letter-spacing:2px;text-transform:uppercase;color:var(--mu);
  display:flex;align-items:center;justify-content:center;gap:6px;
}
body.lang-ku .ag-role, body.lang-ar .ag-role { font-size: 11.5px; letter-spacing: 0; text-transform: none; }
.ag-role i{color:var(--G);}
.ag-rating{display:flex;align-items:center;justify-content:center;gap:4px;margin-top:10px; direction: ltr;}
.ag-rating i{color:var(--G);font-size:12px;}
.ag-rating span{font-size:12px;color:var(--mu);margin-left:4px;}
.ag-acts{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:18px;}
.ag-btn{
  padding:12px;border-radius:13px;font-size:13px;font-weight:600;
  display:flex;align-items:center;justify-content:center;gap:7px;
  transition:all .35s var(--E);cursor:pointer;border:none;text-decoration:none;
}
.ag-call{background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.28);color:#6ee7b7;}
.ag-call:hover{background:rgba(16,185,129,.25);}
.ag-view{background:rgba(212,175,55,.1);border:1px solid rgba(212,175,55,.28);color:var(--G);}
.ag-view:hover{background:rgba(212,175,55,.22);}
.ag-sep{width:100%;height:1px;background:var(--brd);margin:18px 0;}
.ag-stat-row{display:flex;justify-content:center;gap:28px;}
.ag-stat-n{font-family:'Playfair Display',serif;font-size:20px;font-weight:700;color:var(--G); direction: ltr;}
.ag-stat-l{font-size:10px;letter-spacing:1.5px;text-transform:uppercase;color:var(--mu);margin-top:2px;}
body.lang-ku .ag-stat-l, body.lang-ar .ag-stat-l { font-size: 11.5px; letter-spacing: 0; text-transform: none; }

/* ─── CONTACT CARD ─── */
.contact-card{padding:30px;}
.ctitle{font-family:'Playfair Display',serif;font-size:21px;font-weight:700;color:#fff;margin-bottom:5px;}
.csub{font-size:13px;color:var(--mu);margin-bottom:24px;line-height:1.6;}
.fg{margin-bottom:16px;}
.fg label{display:block;font-size:11px;font-weight:600;letter-spacing:.6px;text-transform:uppercase;color:var(--mu);margin-bottom:8px;}
body.lang-ku .fg label, body.lang-ar .fg label { font-size: 12.5px; letter-spacing: 0; text-transform: none; }
.fg input,.fg textarea{
  width:100%;background:var(--surf);border:1px solid var(--brd);
  color:#fff;border-radius:12px;padding:13px 16px;
  font-family:'DM Sans',sans-serif;font-size:14px;
  outline:none;transition:all .3s var(--E);
}
body.lang-ku .fg input, body.lang-ar .fg input,
body.lang-ku .fg textarea, body.lang-ar .fg textarea { font-family: var(--font-ar-ui); }
.fg input::placeholder,.fg textarea::placeholder{color:rgba(255,255,255,.18);}
.fg input:focus,.fg textarea:focus{
  border-color:rgba(212,175,55,.5);
  background:rgba(212,175,55,.05);
  box-shadow:0 0 0 3px rgba(212,175,55,.07);
}
.fg textarea{resize:none;}
.send-btn{
  width:100%;height:54px;border:none;border-radius:13px;cursor:pointer;
  background:linear-gradient(135deg,var(--G) 0%,var(--GL) 100%);
  color:var(--PD);font-family:'DM Sans',sans-serif;
  font-size:13.5px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;
  display:flex;align-items:center;justify-content:center;gap:10px;
  box-shadow:0 4px 24px rgba(212,175,55,.35);
  transition:all .4s var(--E);position:relative;overflow:hidden;
}
body.lang-ku .send-btn, body.lang-ar .send-btn { font-family: var(--font-ar-ui); letter-spacing: 0; font-size: 14.5px; text-transform: none; }
.send-btn::before{
  content:'';position:absolute;inset:0;
  background:linear-gradient(135deg,rgba(255,255,255,.18),transparent);
  opacity:0;transition:opacity .3s;
}
.send-btn:hover{transform:translateY(-2px);box-shadow:0 14px 36px rgba(212,175,55,.5);}
.send-btn:hover::before{opacity:1;}
.send-btn:active{transform:translateY(0);}
.resp-note{
  display:flex;align-items:center;justify-content:center;gap:7px;
  font-size:12px;color:var(--mu);margin-top:16px;
}
.resp-note i{color:var(--G);}

/* ─── QUICK INFO CARD ─── */
.quick-card{padding:26px;}
.qi-row{
  display:flex;align-items:center;justify-content:space-between;
  padding:11px 14px;background:var(--surf);border:1px solid var(--brd);
  border-radius:11px;margin-bottom:9px;transition:all .3s var(--E);
}
.qi-row:last-child{margin-bottom:0;}
.qi-row:hover{border-color:rgba(212,175,55,.22);background:rgba(212,175,55,.04);}
.qi-lbl{font-size:12px;color:var(--mu);display:flex;align-items:center;gap:7px;}
.qi-lbl i{color:var(--G);width:14px; text-align: center;}
.qi-val{font-size:13px;font-weight:600;color:#fff;}

/* ─── SECTION SPACING ─── */
.sb{margin-bottom:22px;}
.sb:last-child{margin-bottom:0;}

/* ─── RESPONSIVE ─── */
@media(max-width:1100px){
  .grid{grid-template-columns:1fr;}
  .sticky-col{position:static;}
}
@media(max-width:768px){
  .bento{grid-template-columns:1fr 1fr;}
  .specs{grid-template-columns:1fr;}
  .hero{height:55vh;min-height:380px;}
  .gcp{padding:22px;}
  .outer{padding:0 14px 80px;}
  .grid{margin-top:0;}
  .hero-badges{top:14px;inset-inline-start:14px;}
  .hero-acts{top:14px;inset-inline-end:14px;}
  .thumb-strip,.photo-count{display:none;}
}
@media(max-width:480px){.ag-acts{grid-template-columns:1fr;}}
</style>
</head>
<body>
@php $navbarStyle = 'dark'; @endphp
@include('navbar')

<div class="hero">
  <div class="swiper swiper-hero" dir="ltr">
    <div class="swiper-wrapper">
      @foreach($property->images as $photo)
      <div class="swiper-slide">
        <img src="{{ $photo }}" alt="Property"
             onerror="this.src='{{ asset('property_images/default-property.jpg') }}'"/>
      </div>
      @endforeach
    </div>
    <div class="swiper-pagination"></div>
    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>
  </div>

  <div class="hero-grad"></div>

  <div class="hero-badges">
    @if($property->verified)
      <span class="hbadge hbadge-v"><i class="fas fa-shield-check"></i> <span data-i18n="verified">Verified</span></span>
    @endif
    @if($property->is_boosted)
      <span class="hbadge hbadge-f"><i class="fas fa-crown"></i> <span data-i18n="featured">Featured</span></span>
    @endif
    <span class="hbadge hbadge-t"><i class="fas fa-tag"></i> <span data-i18n="{{ strtolower($property->listing_type) }}">{{ ucfirst($property->listing_type) }}</span></span>
  </div>

  <div class="hero-acts">
    <div class="hact" id="share-btn" title="Share"><i class="fas fa-share-nodes"></i></div>
    <div class="hact" id="fav-btn" title="Save"><i class="far fa-heart"></i></div>
  </div>

  <div class="photo-count" id="photo-count">
    <i class="fas fa-camera"></i>
    <span id="pc-text" style="direction: ltr;">1 / {{ count($property->images) }}</span>
  </div>

  <div class="thumb-strip" id="thumb-strip"></div>
</div>

<div class="outer">
<div class="grid">

<div>

  <div class="gc gcp top-accent sb">
    <div class="eyebrow" data-i18n="eyebrowText">Dream Mulk Property</div>
    <h1 class="ptitle" id="dyn-title"
        data-en="{{ $property->name['en'] ?? $property->name ?? 'Untitled Property' }}"
        data-ku="{{ $property->name['ku'] ?? $property->name ?? 'Untitled Property' }}"
        data-ar="{{ $property->name['ar'] ?? $property->name ?? 'Untitled Property' }}">
        {{ $property->name['en'] ?? $property->name ?? 'Untitled Property' }}
    </h1>

    <div class="paddr" id="dyn-addr"
         data-en="{{ $property->address_details['city']['en'] ?? $property->address ?? 'Kurdistan, Iraq' }}"
         data-ku="{{ $property->address_details['city']['ku'] ?? $property->address ?? 'Kurdistan, Iraq' }}"
         data-ar="{{ $property->address_details['city']['ar'] ?? $property->address ?? 'Kurdistan, Iraq' }}">
      <i class="fas fa-location-dot"></i>
      <span>{{ $property->address_details['city']['en'] ?? $property->address ?? 'Kurdistan, Iraq' }}</span>
    </div>

    <div class="price-row">
      <div>
        <div class="price">${{ number_format($property->price['usd'] ?? 0) }}</div>
        <div class="price-note" data-i18n="{{ $property->listing_type === 'rent' ? 'perMonth' : 'totalPrice' }}">
          {{ $property->listing_type === 'rent' ? 'per month' : 'total price' }}
        </div>
      </div>
    </div>

    <div class="bento">
      <div class="bcard">
        <i class="fas fa-bed"></i>
        <span class="bv">{{ $property->rooms['bedroom']['count'] ?? 0 }}</span>
        <span class="bl" data-i18n="beds">Bedrooms</span>
      </div>
      <div class="bcard">
        <i class="fas fa-bath"></i>
        <span class="bv">{{ $property->rooms['bathroom']['count'] ?? 0 }}</span>
        <span class="bl" data-i18n="baths">Bathrooms</span>
      </div>
      <div class="bcard">
        <i class="fas fa-vector-square"></i>
        <span class="bv">{{ number_format($property->area ?? 0) }}</span>
        <span class="bl" data-i18n="area">m² Area</span>
      </div>
      <div class="bcard">
        <i class="fas fa-couch"></i>
        <span class="bv" data-i18n="{{ $property->furnished ? 'yes' : 'no' }}">{{ $property->furnished ? 'Yes' : 'No' }}</span>
        <span class="bl" data-i18n="furnished">Furnished</span>
      </div>
    </div>

    @php
      $owner = $property->owner;
      $canAgent = false; $agentUrl = '#';
      if($owner){
        $oc = get_class($owner);
        if($oc==='App\\Models\\Agent'){$canAgent=true;$agentUrl=route('agent.profile',$owner->id);}
        elseif($oc==='App\\Models\\RealEstateOffice'){$canAgent=true;$agentUrl=route('office.profile',$owner->id);}
      }
    @endphp
    @if($canAgent)
      <a href="{{ $agentUrl }}" class="prof-btn">
        <i class="fas fa-user-tie"></i> <span data-i18n="viewProfile">View Professional Profile</span> <i class="fas fa-arrow-right"></i>
      </a>
    @endif
  </div>

  <div class="gc gcp sb">
    <div class="sh"><div class="sh-ico"><i class="fas fa-align-left"></i></div> <span data-i18n="aboutProp">About This Property</span></div>
    <p class="desc" id="dyn-desc"
       data-en="{{ $property->description['en'] ?? 'No description has been provided for this property.' }}"
       data-ku="{{ $property->description['ku'] ?? $property->description['en'] ?? 'زانیاری نەنووسراوە' }}"
       data-ar="{{ $property->description['ar'] ?? $property->description['en'] ?? 'لم يتم تقديم وصف' }}">
       {{ $property->description['en'] ?? 'No description has been provided for this property.' }}
    </p>
  </div>

  <div class="gc gcp sb">
    <div class="sh"><div class="sh-ico"><i class="fas fa-list-check"></i></div> <span data-i18n="specs">Specifications</span></div>
    <div class="specs">
      @php $specs = [
        ['l'=>'Property Type','lk'=>'propType','v'=>ucfirst($property->type['category'] ?? 'N/A'),'i'=>'fa-home'],
        ['l'=>'Year Built','lk'=>'yearBuilt','v'=>$property->year_built ?? 'N/A','i'=>'fa-calendar-check'],
        ['l'=>'Floor Number','lk'=>'floorNum','v'=>$property->floor_number ?? 'N/A','i'=>'fa-layer-group'],
        ['l'=>'Electricity','lk'=>'electricity','v'=>$property->electricity ? 'Available' : 'N/A','i'=>'fa-bolt'],
        ['l'=>'Water Supply','lk'=>'water','v'=>$property->water ? 'Available' : 'N/A','i'=>'fa-droplet'],
        ['l'=>'Internet','lk'=>'internet','v'=>$property->internet ? 'Fiber Optic' : 'N/A','i'=>'fa-wifi'],
      ]; @endphp
      @foreach($specs as $s)
      <div class="srow">
        <span class="slbl" data-i18n="{{ $s['lk'] }}">{{ $s['l'] }}</span>
        <span class="sval"><i class="fas {{ $s['i'] }}"></i>
            <span data-i18n-val="{{ strtolower($s['v']) }}">{{ $s['v'] }}</span>
        </span>
      </div>
      @endforeach
    </div>
  </div>

  @if(!empty($property->features) || !empty($property->amenities))
  <div class="gc gcp sb">
    <div class="sh"><div class="sh-ico"><i class="fas fa-sparkles"></i></div> <span data-i18n="amenities">Amenities & Features</span></div>
    <div class="pills">
      @foreach(array_merge($property->features ?? [], $property->amenities ?? []) as $item)
        <span class="pill"><i class="fas fa-check"></i> <span data-i18n-item="{{ strtolower($item) }}">{{ ucfirst($item) }}</span></span>
      @endforeach
    </div>
  </div>
  @endif

  <div class="gc sb" style="overflow:hidden;">
    <div class="gcp" style="padding-bottom:0;">
      <div class="sh" style="margin-bottom:20px;">
        <div class="sh-ico"><i class="fas fa-map-location-dot"></i></div> <span data-i18n="location">Location</span>
      </div>
    </div>
    <div class="map-wrap">
      <div id="prop-map"></div>
    </div>
  </div>

  <div class="gc gcp sb report-card">
    <div class="sh" style="border-color:rgba(239,68,68,.15);">
      <div class="sh-ico" style="background:rgba(239,68,68,.1);border-color:rgba(239,68,68,.22);color:#f87171;">
        <i class="fas fa-triangle-exclamation"></i>
      </div>
      <span style="color:#fca5a5;" data-i18n="reportTitle">Report This Listing</span>
    </div>
    <form method="POST" action="{{ route('report.store') }}">
      @csrf
      <input type="hidden" name="property_id" value="{{ $property->id }}"/>
      <div style="margin-bottom:14px;">
        <textarea name="report" rows="3" data-placeholder-i18n="reportDesc" placeholder="Describe the issue with this listing…" required></textarea>
      </div>
      <button type="submit" class="btn-report"><i class="fas fa-flag"></i> <span data-i18n="submitReport">Submit Report</span></button>
    </form>
    @if(session('success'))
      <div class="ok-box"><i class="fas fa-check-circle"></i>{{ session('success') }}</div>
    @endif
  </div>

</div><div class="sticky-col">

  @if($canAgent && $owner)
  <div class="gc agent-card">
    <div class="ag-avatar">
      <img
        src="{{ $owner->profile_image ?? $owner->image ?? 'https://ui-avatars.com/api/?name='.urlencode($owner->agent_name ?? $owner->name ?? 'Agent').'&background=1a225a&color=d4af37&size=80' }}"
        alt="Agent"
        onerror="this.src='https://ui-avatars.com/api/?name=Agent&background=1a225a&color=d4af37&size=80'"
      />
    </div>
    <div class="ag-name">{{ $owner->agent_name ?? $owner->name ?? 'Agent' }}</div>
    <div class="ag-role">
      <i class="fas fa-circle-check"></i>
      <span data-i18n="{{ get_class($owner)==='App\\Models\\RealEstateOffice' ? 'office' : 'agent' }}">
        {{ get_class($owner)==='App\\Models\\RealEstateOffice' ? 'Real Estate Office' : 'Verified Agent' }}
      </span>
    </div>
    <div class="ag-rating">
      <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
      <i class="fas fa-star"></i><i class="fas fa-star-half-stroke"></i>
      <span>4.8 (124)</span>
    </div>
    <div class="ag-acts">
      @if($owner->phone ?? $owner->phone_number)
      <a href="tel:{{ $owner->phone ?? $owner->phone_number }}" class="ag-btn ag-call">
        <i class="fas fa-phone"></i> <span data-i18n="call">Call</span>
      </a>
      @endif
      <a href="{{ $agentUrl }}" class="ag-btn ag-view">
        <i class="fas fa-user"></i> <span data-i18n="profile">Profile</span>
      </a>
    </div>
    <div class="ag-sep"></div>
    <div class="ag-stat-row">
      <div style="text-align:center;">
        <div class="ag-stat-n">47</div>
        <div class="ag-stat-l" data-i18n="listings">Listings</div>
      </div>
      <div style="text-align:center;">
        <div class="ag-stat-n">5yr</div>
        <div class="ag-stat-l" data-i18n="experience">Experience</div>
      </div>
      <div style="text-align:center;">
        <div class="ag-stat-n">98%</div>
        <div class="ag-stat-l" data-i18n="response">Response</div>
      </div>
    </div>
  </div>
  @endif

  <div class="gc contact-card">
    <div class="ctitle" data-i18n="inquiryTitle">Send Inquiry</div>
    <div class="csub" data-i18n="inquirySub">Interested in this property? Send a message and we'll connect you with the agent directly.</div>
    <form action="/submit-contact" method="POST">
      @csrf
      <div class="fg">
        <label data-i18n="fullName">Full Name</label>
        <input type="text" name="name" data-placeholder-i18n="fullNamePh" placeholder="Your full name" required/>
      </div>
      <div class="fg">
        <label data-i18n="phone">Phone Number</label>
        <input type="tel" name="phone-number" placeholder="07XX XXX XXXX" required style="direction: ltr; text-align: start;"/>
      </div>
      <div class="fg">
        <label data-i18n="message">Message</label>
        <textarea name="message" rows="4" id="inquiry-msg" required>I am interested in this property. Please contact me.</textarea>
      </div>
      <button type="submit" class="send-btn"><i class="fas fa-paper-plane"></i> <span data-i18n="sendInquiry">Send Inquiry</span></button>
    </form>
    <div class="resp-note"><i class="fas fa-clock"></i> <span data-i18n="respNote">Typically responds within 24 hours</span></div>
  </div>

  <div class="gc quick-card">
    <div class="sh" style="font-size:16px;margin-bottom:16px;padding-bottom:14px;">
      <div class="sh-ico"><i class="fas fa-circle-info"></i></div> <span data-i18n="quickInfo">Quick Info</span>
    </div>
    @php $qi=[
      ['l'=>'Property ID','lk'=>'propId','v'=>'#'.str_pad($property->id,5,'0',STR_PAD_LEFT),'i'=>'fa-hashtag'],
      ['l'=>'Status','lk'=>'status','v'=>ucfirst($property->status ?? 'Active'),'i'=>'fa-circle-dot'],
      ['l'=>'Listed','lk'=>'listed','v'=>optional($property->created_at)->diffForHumans() ?? 'Recently','i'=>'fa-calendar'],
      ['l'=>'Views','lk'=>'views','v'=>number_format($property->views ?? 0),'i'=>'fa-eye'],
    ]; @endphp
    @foreach($qi as $q)
    <div class="qi-row">
      <span class="qi-lbl"><i class="fas {{ $q['i'] }}"></i><span data-i18n="{{ $q['lk'] }}">{{ $q['l'] }}</span></span>
      <span class="qi-val" style="direction:ltr;">
        @if($q['lk'] === 'status')
            <span data-i18n-val="{{ strtolower($q['v']) }}">{{ $q['v'] }}</span>
        @else
            {{ $q['v'] }}
        @endif
      </span>
    </div>
    @endforeach
  </div>

</div></div></div><script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
/* ── I18N SYSTEM ── */
class PropertyI18n {
  constructor() {
    this.storageKey = 'dm_lang';
    this.defaultLang = 'ku';
    this.translations = {
      ku: {
        dir: 'rtl',
        eyebrowText: 'موڵکی دریم موڵک', verified: 'پشتڕاستکراوە', featured: 'تایبەت',
        rent: 'کرێ', sell: 'فرۆشتن', perMonth: 'مانگانە', totalPrice: 'نرخی گشتی',
        beds: 'نووستن', baths: 'گەرماو', area: 'ڕووبەر (م٢)', furnished: 'مۆبیلیات',
        yes: 'بەڵێ', no: 'نەخێر', viewProfile: 'بینینی پڕۆفایلی کارمەند',
        aboutProp: 'دەربارەی ئەم خانوو', specs: 'تایبەتمەندییەکان', amenities: 'خزمەتگوزارییەکان',
        location: 'ناونیشان', reportTitle: 'ڕاپۆرتکردنی ئەم خانوو', submitReport: 'ناردنی ڕاپۆرت',
        agent: 'ئەجێنتی پشتڕاستکراوە', office: 'ئۆفیسی خانووبەرە', call: 'پەیوەندی', profile: 'پڕۆفایل',
        listings: 'خانووەکان', experience: 'ئەزموون', response: 'وەڵامدانەوە',
        inquiryTitle: 'ناردنی پرسیار', inquirySub: 'ئارەزووی ئەم خانووت هەیە؟ نامەیەک بنێرە.',
        fullName: 'ناوی تەواو', phone: 'ژمارەی تەلەفۆن', message: 'نامە', sendInquiry: 'ناردن',
        respNote: 'زۆرجار لە ماوەی ٢٤ کاتژمێردا وەڵام دەداتەوە', quickInfo: 'زانیاری خێرا',
        propId: 'ژمارەی خانوو', status: 'دۆخ', listed: 'کاتی دانان', views: 'بینینەکان',

        // Form & Textarea
        reportDesc: 'کێشەی ئەم خانوو لێرە بنووسە...', fullNamePh: 'ناوی خۆت بنووسە',
        msgDefault: 'سڵاو، ئارەزووی ئەم خانووەم هەیە. تکایە پەیوەندیم پێوە بکە.',

        // Dynamic Values translation mapping
        val_available: 'بەردەستە', val_n_a: 'نەزانراو', val_fiber_optic: 'فایبەر ئۆپتیک', val_active: 'چالاک',
        propType: 'جۆری خانوو', yearBuilt: 'ساڵی درووستکردن', floorNum: 'نهۆم', electricity: 'کارەبا', water: 'ئاو', internet: 'ئینتەرنێت'
      },
      en: {
        dir: 'ltr',
        eyebrowText: 'Dream Mulk Property', verified: 'Verified', featured: 'Featured',
        rent: 'Rent', sell: 'Sale', perMonth: 'per month', totalPrice: 'total price',
        beds: 'Bedrooms', baths: 'Bathrooms', area: 'm² Area', furnished: 'Furnished',
        yes: 'Yes', no: 'No', viewProfile: 'View Professional Profile',
        aboutProp: 'About This Property', specs: 'Specifications', amenities: 'Amenities & Features',
        location: 'Location', reportTitle: 'Report This Listing', submitReport: 'Submit Report',
        agent: 'Verified Agent', office: 'Real Estate Office', call: 'Call', profile: 'Profile',
        listings: 'Listings', experience: 'Experience', response: 'Response',
        inquiryTitle: 'Send Inquiry', inquirySub: 'Interested in this property? Send a message.',
        fullName: 'Full Name', phone: 'Phone Number', message: 'Message', sendInquiry: 'Send Inquiry',
        respNote: 'Typically responds within 24 hours', quickInfo: 'Quick Info',
        propId: 'Property ID', status: 'Status', listed: 'Listed', views: 'Views',

        reportDesc: 'Describe the issue with this listing...', fullNamePh: 'Your full name',
        msgDefault: 'I am interested in this property. Please contact me.',

        val_available: 'Available', val_n_a: 'N/A', val_fiber_optic: 'Fiber Optic', val_active: 'Active',
        propType: 'Property Type', yearBuilt: 'Year Built', floorNum: 'Floor Number', electricity: 'Electricity', water: 'Water Supply', internet: 'Internet'
      },
      ar: {
        dir: 'rtl',
        eyebrowText: 'عقار دريم مُلك', verified: 'موثق', featured: 'مميز',
        rent: 'للإيجار', sell: 'للبيع', perMonth: 'شهرياً', totalPrice: 'السعر الإجمالي',
        beds: 'غرف النوم', baths: 'الحمامات', area: 'المساحة (م٢)', furnished: 'مفروش',
        yes: 'نعم', no: 'لا', viewProfile: 'عرض الملف الشخصي',
        aboutProp: 'عن هذا العقار', specs: 'المواصفات', amenities: 'المميزات والمرافق',
        location: 'الموقع', reportTitle: 'الإبلاغ عن هذا الإعلان', submitReport: 'إرسال البلاغ',
        agent: 'وكيل موثق', office: 'مكتب عقارات', call: 'اتصال', profile: 'الملف الشخصي',
        listings: 'إعلانات', experience: 'خبرة', response: 'استجابة',
        inquiryTitle: 'إرسال استفسار', inquirySub: 'مهتم بهذا العقار؟ أرسل رسالة للتواصل.',
        fullName: 'الاسم الكامل', phone: 'رقم الهاتف', message: 'رسالة', sendInquiry: 'إرسال',
        respNote: 'عادة ما يرد خلال ٢٤ ساعة', quickInfo: 'معلومات سريعة',
        propId: 'رقم العقار', status: 'الحالة', listed: 'تاريخ النشر', views: 'المشاهدات',

        reportDesc: 'صف المشكلة في هذا الإعلان...', fullNamePh: 'اسمك الكامل',
        msgDefault: 'مرحباً، أنا مهتم بهذا العقار. يرجى التواصل معي.',

        val_available: 'متوفر', val_n_a: 'غير متوفر', val_fiber_optic: 'ألياف ضوئية', val_active: 'نشط',
        propType: 'نوع العقار', yearBuilt: 'سنة البناء', floorNum: 'الطابق', electricity: 'الكهرباء', water: 'الماء', internet: 'الإنترنت'
      }
    };
  }

  init() {
    const saved = localStorage.getItem(this.storageKey) || this.defaultLang;
    this.setLang(saved);

    // Listen for language buttons from the navbar (assuming they have class .lang-btn and data-lang attr)
    document.querySelectorAll('.lang-btn').forEach(btn => {
      btn.addEventListener('click', () => this.setLang(btn.getAttribute('data-lang')));
    });
  }

  setLang(lang) {
    if (!this.translations[lang]) return;
    localStorage.setItem(this.storageKey, lang);
    const T = this.translations[lang];

    document.body.dir = T.dir;
    document.documentElement.lang = lang === 'ar' ? 'ar' : lang === 'ku' ? 'ku' : 'en';
    document.body.classList.remove('lang-ku', 'lang-en', 'lang-ar', 'rtl');
    document.body.classList.add('lang-' + lang);
    if (T.dir === 'rtl') document.body.classList.add('rtl');

    // Update Active Button States
    document.querySelectorAll('.lang-btn').forEach(b => {
        b.classList.toggle('active', b.getAttribute('data-lang') === lang);
    });

    // 1. Translate Static Texts
    document.querySelectorAll('[data-i18n]').forEach(el => {
      const key = el.getAttribute('data-i18n');
      if (T[key] !== undefined) el.textContent = T[key];
    });

    // 2. Translate Static Values (Specs/Amenities)
    document.querySelectorAll('[data-i18n-val]').forEach(el => {
      const key = 'val_' + el.getAttribute('data-i18n-val').replace(/\s+/g, '_').replace(/[^a-z0-9_]/gi, '');
      if (T[key] !== undefined) el.textContent = T[key];
    });

    // 3. Translate Placeholders
    document.querySelectorAll('[data-placeholder-i18n]').forEach(el => {
      const key = el.getAttribute('data-placeholder-i18n');
      if (T[key] !== undefined) el.placeholder = T[key];
    });

    // 4. Translate Dynamic Blade Data (Title, Description, Address)
    const dynEls = ['dyn-title', 'dyn-desc', 'dyn-addr'];
    dynEls.forEach(id => {
      const el = document.getElementById(id);
      if(el) {
          const transTxt = el.getAttribute('data-' + lang);
          if(transTxt && transTxt.trim() !== '') {
              // for addr we need to keep the icon
              if(id === 'dyn-addr') {
                  el.querySelector('span').textContent = transTxt;
              } else {
                  el.textContent = transTxt;
              }
          }
      }
    });

    // 5. Default Textarea Message
    const msgArea = document.getElementById('inquiry-msg');
    if(msgArea && (msgArea.value === this.translations['en'].msgDefault ||
                   msgArea.value === this.translations['ku'].msgDefault ||
                   msgArea.value === this.translations['ar'].msgDefault)) {
        msgArea.value = T.msgDefault;
    }
  }
}

// Initialize I18n
const propI18n = new PropertyI18n();
propI18n.init();

/* ── SWIPER ── */
const heroSwiper = new Swiper('.swiper-hero',{
  loop:true,
  autoplay:{delay:5000,disableOnInteraction:false,pauseOnMouseEnter:true},
  pagination:{el:'.swiper-pagination',clickable:true},
  navigation:{nextEl:'.swiper-button-next',prevEl:'.swiper-button-prev'},
  effect:'fade',fadeEffect:{crossFade:true},speed:900,
  on:{
    slideChange:function(){
      const total = this.slides.length - (this.loopedSlides||0)*2 || this.slides.length;
      document.getElementById('pc-text').textContent = (this.realIndex+1)+' / '+{{ count($property->images) }};
      document.querySelectorAll('.th').forEach((t,i)=>t.classList.toggle('active',i===this.realIndex));
    }
  }
});

/* Thumbnail strip — max 5 */
const slides = document.querySelectorAll('.swiper-hero .swiper-slide:not(.swiper-slide-duplicate) img');
const strip  = document.getElementById('thumb-strip');
slides.forEach((img,i)=>{
  if(i>4)return;
  const d=document.createElement('div');
  d.className='th'+(i===0?' active':'');
  d.innerHTML=`<img src="${img.src}" loading="lazy"/>`;
  d.addEventListener('click',()=>heroSwiper.slideToLoop(i));
  strip.appendChild(d);
});

/* ── FAV TOGGLE ── */
document.getElementById('fav-btn').addEventListener('click',function(){
  const i=this.querySelector('i');
  const isFav=i.classList.toggle('fas');
  i.classList.toggle('far');
  this.style.background=isFav?'rgba(239,68,68,.8)':'rgba(6,9,30,.55)';
  this.style.borderColor=isFav?'rgba(239,68,68,.8)':'rgba(255,255,255,.15)';
});

/* ── SHARE ── */
document.getElementById('share-btn').addEventListener('click',function(){
  if(navigator.share){
    navigator.share({title:document.title,url:location.href});
  }else{
    navigator.clipboard.writeText(location.href).then(()=>{
      this.innerHTML='<i class="fas fa-check"></i>';
      this.style.background='rgba(16,185,129,.8)';
      setTimeout(()=>{
        this.innerHTML='<i class="fas fa-share-nodes"></i>';
        this.style.background='rgba(6,9,30,.55)';
      },2200);
    });
  }
});

/* ══ LEAFLET MAP — OpenStreetMap (NO API KEY) ══ */
@php
  $lat = 36.1911; $lng = 44.0091;
  if(!empty($property->locations)&&is_array($property->locations)&&isset($property->locations[0])){
    $lat = $property->locations[0]['lat'] ?? $lat;
    $lng = $property->locations[0]['lng'] ?? $lng;
  }
@endphp
(function(){
  const map = L.map('prop-map',{
    center:[{{ $lat }},{{ $lng }}],
    zoom:15,
    zoomControl:false,
    scrollWheelZoom:false,
  });

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
    maxZoom:19,
    attribution:'&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a>',
  }).addTo(map);

  L.control.zoom({position:'bottomright'}).addTo(map);

  const markerHTML=`
    <div style="position:relative;width:42px;height:52px;">
      <div style="
        width:42px;height:42px;border-radius:50% 50% 50% 0;
        background:linear-gradient(135deg,#d4af37,#e8cb6a);
        transform:rotate(-45deg);border:3px solid #fff;
        box-shadow:0 6px 20px rgba(212,175,55,.65);
        display:flex;align-items:center;justify-content:center;
      ">
        <i class='fas fa-building' style='transform:rotate(45deg);color:#1a225a;font-size:15px;'></i>
      </div>
    </div>`;

  const icon=L.divIcon({
    className:'',html:markerHTML,
    iconSize:[42,52],iconAnchor:[21,52],popupAnchor:[0,-56],
  });

  L.marker([{{ $lat }},{{ $lng }}],{icon})
    .addTo(map)
    .bindPopup(`
      <div style="min-width:190px;" dir="ltr">
        <div style="font-weight:800;font-size:14px;margin-bottom:5px;">
          {{ $property->name['en'] ?? 'Property' }}
        </div>
        <div style="font-size:12px;color:rgba(255,255,255,.5);margin-bottom:8px;">
          <i class='fas fa-location-dot' style='color:#d4af37;margin-right:4px;'></i>
          {{ $property->address_details['city']['en'] ?? 'Kurdistan, Iraq' }}
        </div>
        <div style="font-family:'Playfair Display',serif;font-size:18px;font-weight:800;color:#d4af37;">
          ${{ number_format($property->price['usd'] ?? 0) }}
        </div>
      </div>
    `,{maxWidth:240})
    .openPopup();

  map.on('click',()=>map.scrollWheelZoom.enable());
  map.on('mouseout',()=>map.scrollWheelZoom.disable());
})();
</script>
</body>
</html>
