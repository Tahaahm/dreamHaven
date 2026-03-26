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
   PREMIUM LIGHT THEME VARIABLES
═══════════════════════════════ */
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
:root{
  --B: #303b97;      /* Primary Navy Blue */
  --BD: #1a225a;     /* Deep Navy */
  --BL: #eef0fb;     /* Very Light Blue */
  --G: #d4af37;      /* Gold */
  --GL: #f5e9b0;     /* Light Gold */
  --ink: #0d1117;    /* Almost Black Text */
  --mid: #52596e;    /* Gray Text */
  --dim: #9aa0b8;    /* Light Gray Text */
  --hr: #e4e6f0;     /* Border Color */
  --bg: #f8f9fc;     /* Soft Page Background */
  --card: #ffffff;   /* Pure White Cards */
  --E: cubic-bezier(0.25, 1, 0.15, 1);
  --font-ar: 'Noto Naskh Arabic', 'Noto Sans Arabic', serif;
  --font-ar-ui: 'Noto Sans Arabic', sans-serif;
}
html{scroll-behavior:smooth;}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--ink);line-height:1.6;overflow-x:hidden; transition: background 0.3s;}
a{text-decoration:none;color:inherit;}
img{display:block;}

/* RTL & Arabic/Kurdish Fonts overrides */
body.lang-ku, body.lang-ar { font-family: var(--font-ar-ui); direction: rtl; }
body.lang-ku *:not(i):not([class*="fa-"]), body.lang-ar *:not(i):not([class*="fa-"]) {
  font-family: var(--font-ar-ui); line-height: 1.8;
}
body.lang-ku .ptitle, body.lang-ar .ptitle,
body.lang-ku .sh, body.lang-ar .sh,
body.lang-ku .ctitle, body.lang-ar .ctitle,
body.lang-ku .ag-name, body.lang-ar .ag-name {
  font-family: var(--font-ar) !important;
}

/* ═══════════════════════════════
   HERO GALLERY
═══════════════════════════════ */
.hero {
  position: relative; height: 72vh; min-height: 520px;
  background: #000; overflow: hidden; direction: ltr !important;
}
.swiper-main { width: 100%; height: 100%; }
.swiper-main .swiper-slide img {
  width: 100%; height: 100%; object-fit: cover;
  transition: transform 8s ease;
}
.swiper-main .swiper-slide-active img { transform: scale(1.05); }

/* Shimmering Gradient Overlay */
.hero-grad {
  position: absolute; inset: 0; z-index: 2; pointer-events: none;
  background:
    linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.3) 25%, transparent 50%),
    linear-gradient(to right, rgba(255,255,255,0.05) 0%, transparent 40%),
    linear-gradient(to bottom, rgba(255,255,255,0.1) 0%, transparent 20%);
  overflow: hidden;
}
.hero-grad::after {
  content: ''; position: absolute; top: 0; left: -100%; width: 200%; height: 100%;
  background: linear-gradient(90deg, transparent 20%, rgba(255,255,255,0.05) 50%, transparent 80%);
  animation: shimmerWave 3.5s infinite var(--E);
}
@keyframes shimmerWave {
  0% { left: -100%; }
  100% { left: 100%; }
}

.hero-badges {
  position: absolute; top: 28px; left: 28px; z-index: 10;
  display: flex; gap: 10px; flex-wrap: wrap;
}
.hbadge {
  display: inline-flex; align-items: center; gap: 7px;
  padding: 8px 18px; border-radius: 50px;
  font-size: 10.5px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
  backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px);
}
body.lang-ku .hbadge, body.lang-ar .hbadge { letter-spacing: 0; font-size: 11.5px; }
.hbadge-v { background: rgba(16,185,129,.9); color: #fff; }
.hbadge-f { background: rgba(212,175,55,.95); color: var(--BD); }
.hbadge-t { background: #fff; color: var(--BD); border: 1px solid rgba(255,255,255,.4); }

.hero-acts { position: absolute; top: 28px; right: 28px; z-index: 10; display: flex; gap: 10px; }
.hact {
  width: 44px; height: 44px; border-radius: 50%; cursor: pointer;
  background: rgba(255,255,255,.9); box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  display: flex; align-items: center; justify-content: center;
  color: var(--BD); font-size: 16px; transition: all .35s var(--E);
}
.hact:hover { background: var(--G); color: #fff; transform: scale(1.1); }

/* Glassmorphism Price on Bottom Left */
.hero-price {
  position: absolute; bottom: 24px; left: 24px; z-index: 15;
  background: rgba(10, 13, 39, 0.65);
  backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(255, 255, 255, 0.15);
  padding: 16px 26px; border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.3);
  display: flex; flex-direction: column; align-items: flex-start;
  overflow: hidden;
}
.hero-price::before {
  content: ''; position: absolute; top: 0; left: -150%; width: 150%; height: 100%;
  background: linear-gradient(120deg, transparent 20%, rgba(255,255,255,0.15) 50%, transparent 80%);
  animation: priceShimmer 4s infinite linear;
  pointer-events: none;
}
@keyframes priceShimmer {
  0% { left: -150%; }
  100% { left: 150%; }
}
.hero-price-sub { font-size: 11px; font-weight: 700; text-transform: uppercase; color: rgba(255,255,255,0.7); letter-spacing: 1.5px; position: relative; z-index: 2; }
.hero-price-main { font-family: 'Playfair Display', serif; font-size: 34px; font-weight: 800; color: #fff; line-height: 1; direction: ltr; margin-bottom: 4px; position: relative; z-index: 2; }
.hero-price-note { font-size: 13px; font-weight: 500; color: var(--G); position: relative; z-index: 2; }

.swiper-button-prev, .swiper-button-next {
  width: 46px; height: 46px; border-radius: 50%;
  background: rgba(255,255,255,.9); box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  color: var(--BD); transition: all 0.3s;
}
.swiper-button-prev::after, .swiper-button-next::after { font-size: 16px; font-weight: 900; }
.swiper-button-prev:hover, .swiper-button-next:hover { background: var(--G); color: #fff; }

/* ═══════════════════════════════
   PAGE LAYOUT
═══════════════════════════════ */
.outer { max-width: 1400px; margin: 0 auto; padding: 0 24px 100px; }
.grid {
  display: grid; grid-template-columns: 1fr 380px; gap: 24px;
  position: relative; z-index: 10;
}

/* ─── OVERLAPPING THUMBNAILS CARD ─── */
.thumbs-overlap {
  margin: -45px auto 24px; position: relative; z-index: 20;
  background: #fff; padding: 10px; border-radius: 16px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 1px solid var(--hr);
  width: max-content; max-width: 100%; display: flex; justify-content: center;
}
.swiper-thumbs { width: auto; max-width: 100%; height: 64px; }
.swiper-thumbs .swiper-slide {
  width: 90px; height: 100%; border-radius: 10px; overflow: hidden;
  opacity: 0.55; cursor: pointer; border: 2px solid transparent; transition: all 0.3s var(--E);
}
.swiper-thumbs .swiper-slide img { width: 100%; height: 100%; object-fit: cover; }
.swiper-thumbs .swiper-slide-thumb-active { opacity: 1; border-color: var(--G); box-shadow: 0 4px 10px rgba(212,175,55,0.3); }
.swiper-thumbs .swiper-slide:hover { opacity: 1; border-color: rgba(212,175,55,0.4); }

/* ─── LIGHT CARD ─── */
.gc {
  background: var(--card); border: 1px solid var(--hr); border-radius: 24px;
  box-shadow: 0 8px 24px rgba(13,17,39,0.03); overflow: hidden;
}
.gcp { padding: 36px; }

/* ─── SECTION HEADING ─── */
.sh {
  display: flex; align-items: center; gap: 12px;
  font-family: var(--f-en-disp); font-size: 20px; font-weight: 700; color: var(--BD);
  margin-bottom: 22px; padding-bottom: 16px; border-bottom: 1px solid var(--hr);
}
.sh-ico {
  width: 38px; height: 38px; border-radius: 11px; flex-shrink: 0;
  background: var(--BL); border: 1px solid rgba(48,59,151,.1);
  display: flex; align-items: center; justify-content: center; font-size: 15px; color: var(--B);
}

/* ─── PRIMARY COLOR TITLE CARD ─── */
.title-card {
  background: var(--B); border: none; border-radius: 24px;
  padding: 36px; color: #fff; margin-bottom: 24px;
  box-shadow: 0 15px 35px rgba(48,59,151,0.2);
}
.title-card .eyebrow {
  font-family: var(--f-cinzel); font-size: 10px; letter-spacing: 4px;
  text-transform: uppercase; color: rgba(255,255,255,0.7);
  display: flex; align-items: center; gap: 10px; margin-bottom: 14px;
}
body.lang-ku .title-card .eyebrow, body.lang-ar .title-card .eyebrow { font-family: var(--font-ar-ui); letter-spacing: 1px; font-size: 11.5px; }
.title-card .eyebrow::before { content: ''; width: 28px; height: 2px; background: rgba(255,255,255,0.7); border-radius: 2px; }
.title-card .ptitle {
  font-family: var(--f-en-disp); font-size: clamp(26px, 3.5vw, 42px);
  font-weight: 800; line-height: 1.3; color: #fff; margin-bottom: 10px;
}
.title-card .paddr { display: flex; align-items: center; gap: 8px; font-size: 14px; color: rgba(255,255,255,0.8); font-weight: 600; }
.title-card .paddr i { color: var(--G); font-size: 14px; }

/* View Profile Button inside Title Card */
.prof-btn {
  display: inline-flex; align-items: center; gap: 10px; padding: 12px 24px; margin-top: 24px;
  background: #fff; color: var(--B); border: none;
  border-radius: 12px; font-size: 14px; font-weight: 700; transition: all .4s var(--E);
  box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-decoration: none;
}
.prof-btn:hover { background: var(--BL); transform: translateX(5px); }
body.rtl .prof-btn:hover { transform: translateX(-5px); }
.prof-btn i { font-size: 13px; }
body.rtl .prof-btn i.fa-arrow-right { transform: rotate(180deg); }

/* ─── BENTO STATS ─── */
.bento { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin: 0; }
.bcard {
  background: var(--card); border: 1px solid var(--hr); border-radius: 18px;
  padding: 24px 16px; text-align: center;
  box-shadow: 0 6px 16px rgba(13,17,39,0.02);
  transition: all .4s var(--E); cursor: default;
}
.bcard:hover {
  border-color: var(--G); transform: translateY(-7px);
  box-shadow: inset 0 0 15px rgba(212,175,55,0.05), 0 12px 28px rgba(48,59,151,0.08);
}
.bcard i { font-size: 24px; color: var(--B); margin-bottom: 14px; display: block; }
.bv { font-family: var(--f-en-disp); font-size: 28px; font-weight: 700; color: var(--BD); display: block; line-height: 1; }
.bl { font-size: 10px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; color: var(--mid); margin-top: 6px; display: block; }
body.lang-ku .bl, body.lang-ar .bl { font-family: var(--font-ar-ui); letter-spacing: 0; font-size: 12px; text-transform: none; }

/* ─── DESCRIPTION ─── */
.desc { font-size: 15px; line-height: 1.9; color: var(--mid); font-weight: 400; }

/* ─── SPECS ─── */
.specs { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.srow {
  display: flex; align-items: center; justify-content: space-between; padding: 14px 18px;
  background: var(--bg); border: 1px solid var(--hr); border-radius: 12px; transition: all .3s var(--E);
}
.srow:hover { border-color: var(--B); background: #fff; box-shadow: 0 4px 15px rgba(48,59,151,0.05); }
.slbl { font-size: 13px; color: var(--mid); font-weight: 500; }
.sval { font-size: 14px; font-weight: 700; color: var(--ink); display: flex; align-items: center; gap: 8px; }
.sval i { color: var(--G); font-size: 12px; }

/* ─── AMENITIES ─── */
.pills { display: flex; flex-wrap: wrap; gap: 10px; }
.pill {
  display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px;
  border-radius: 50px; font-size: 13.5px; font-weight: 600;
  background: #fff; border: 1px solid var(--hr); color: var(--mid); transition: all .3s var(--E);
}
.pill:hover { background: var(--B); border-color: var(--B); color: #fff; }
.pill:hover i { color: var(--GL); }
.pill i { color: var(--G); font-size: 12px; }

/* ─── MAP ─── */
.map-wrap { height: 380px; overflow: hidden; direction: ltr; border-radius: 0 0 24px 24px; }
#prop-map { width: 100%; height: 100%; }
.leaflet-control-attribution { font-size: 9px; color: #7a7a7a !important; background: rgba(255,255,255,0.7) !important; border-radius: 4px; }
.leaflet-control-attribution a { color: var(--B) !important; }
.leaflet-control-zoom a { border-radius: 8px !important; border: 1px solid var(--hr) !important; color: var(--B) !important; background: #fff !important; }
.leaflet-control-zoom a:hover { background: var(--BL) !important; }
.leaflet-popup-content-wrapper { background: #fff; border-radius: 16px; color: var(--ink); box-shadow: 0 10px 30px rgba(0,0,0,0.15); }
.leaflet-popup-tip { background: #fff; }

/* ─── REPORT ─── */
.report-card { border-inline-start: 4px solid #ef4444; background: #fffafaf5; }
.report-card textarea {
  width: 100%; background: #fff; border: 1px solid #fecaca; color: var(--ink);
  border-radius: 12px; padding: 14px 16px; font-family: var(--f-en); font-size: 14px;
  resize: none; outline: none; transition: border .3s;
}
body.lang-ku .report-card textarea, body.lang-ar .report-card textarea { font-family: var(--font-ar-ui); }
.report-card textarea:focus { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,.1); }
.report-card textarea::placeholder { color: #9ca3af; }
.btn-report {
  display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; border-radius: 12px;
  font-size: 14px; font-weight: 600; background: #fee2e2; border: 1px solid #fca5a5;
  color: #b91c1c; cursor: pointer; transition: all .3s;
}
.btn-report:hover { background: #fecaca; }
.ok-box {
  display: flex; align-items: center; gap: 8px; padding: 12px 16px; border-radius: 12px;
  font-size: 13px; margin-top: 14px; background: #d1fae5; border: 1px solid #6ee7b7; color: #047857; font-weight: 500;
}

/* ═══════════════════════════════
   SIDEBAR
═══════════════════════════════ */
.sticky-col { position: sticky; top: 24px; display: flex; flex-direction: column; gap: 24px; }
.sb { margin-bottom: 24px; }
.sb:last-child { margin-bottom: 0; }

/* ─── AGENT CARD ─── */
.agent-card { padding: 36px 30px; text-align: center; border-top: 4px solid var(--B); }
.ag-avatar {
  width: 84px; height: 84px; border-radius: 50%; overflow: hidden; border: 3px solid var(--G);
  margin: 0 auto 16px; box-shadow: 0 0 0 6px rgba(212,175,55,.15), 0 10px 20px rgba(0,0,0,0.05);
}
.ag-avatar img { width: 100%; height: 100%; object-fit: cover; }
.ag-name { font-family: var(--f-en-disp); font-size: 22px; font-weight: 700; color: var(--BD); margin-bottom: 4px; }
.ag-role { font-size: 11px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: var(--mid); display: flex; align-items: center; justify-content: center; gap: 6px; }
body.lang-ku .ag-role, body.lang-ar .ag-role { font-size: 12.5px; letter-spacing: 0; text-transform: none; }
.ag-role i { color: var(--B); }
.ag-rating { display: flex; align-items: center; justify-content: center; gap: 4px; margin-top: 12px; direction: ltr; }
.ag-rating i { color: var(--G); font-size: 14px; }
.ag-rating span { font-size: 13px; font-weight: 600; color: var(--mid); margin-left: 6px; }
.ag-acts { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 24px; }
.ag-btn {
  padding: 12px; border-radius: 12px; font-size: 14px; font-weight: 700;
  display: flex; align-items: center; justify-content: center; gap: 8px;
  transition: all .3s var(--E); cursor: pointer; border: none; text-decoration: none;
}
.ag-call { background: #d1fae5; border: 1px solid #6ee7b7; color: #047857; }
.ag-call:hover { background: #10b981; color: #fff; }
.ag-view { background: #fff; border: 1px solid var(--hr); color: var(--B); }
.ag-view:hover { background: var(--B); color: #fff; border-color: var(--B); }
.ag-sep { width: 100%; height: 1px; background: var(--hr); margin: 24px 0; }
.ag-stat-row { display: flex; justify-content: center; gap: 32px; }
.ag-stat-n { font-family: var(--f-en-disp); font-size: 22px; font-weight: 800; color: var(--B); direction: ltr; }
.ag-stat-l { font-size: 10px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; color: var(--mid); margin-top: 4px; }
body.lang-ku .ag-stat-l, body.lang-ar .ag-stat-l { font-size: 11.5px; letter-spacing: 0; text-transform: none; }

/* ─── CONTACT CARD ─── */
.contact-card { padding: 32px; }
.ctitle { font-family: var(--f-en-disp); font-size: 22px; font-weight: 700; color: var(--BD); margin-bottom: 8px; }
.csub { font-size: 14px; color: var(--mid); margin-bottom: 24px; line-height: 1.6; }
.fg { margin-bottom: 18px; }
.fg label { display: block; font-size: 12px; font-weight: 700; color: var(--B); margin-bottom: 8px; }
body.lang-ku .fg label, body.lang-ar .fg label { font-size: 13.5px; }
.fg input, .fg textarea {
  width: 100%; background: var(--bg); border: 1px solid var(--hr); color: var(--ink);
  border-radius: 12px; padding: 14px 16px; font-family: var(--f-en); font-size: 14px;
  outline: none; transition: all .3s var(--E);
}
body.lang-ku .fg input, body.lang-ar .fg input,
body.lang-ku .fg textarea, body.lang-ar .fg textarea { font-family: var(--font-ar-ui); }
.fg input::placeholder, .fg textarea::placeholder { color: var(--dim); }
.fg input:focus, .fg textarea:focus { border-color: var(--B); background: #fff; box-shadow: 0 0 0 4px rgba(48,59,151,0.08); }
.fg textarea { resize: none; }
.send-btn {
  width: 100%; height: 54px; border: none; border-radius: 14px; cursor: pointer;
  background: linear-gradient(135deg, var(--B), var(--BD)); color: #fff;
  font-family: var(--f-en); font-size: 15px; font-weight: 700;
  display: flex; align-items: center; justify-content: center; gap: 10px;
  box-shadow: 0 6px 20px rgba(48,59,151,.25); transition: all .4s var(--E);
}
body.lang-ku .send-btn, body.lang-ar .send-btn { font-family: var(--font-ar-ui); font-size: 16px; }
.send-btn:hover { transform: translateY(-3px); box-shadow: 0 12px 28px rgba(48,59,151,.4); background: linear-gradient(135deg, var(--PD), var(--B)); }
.send-btn:active { transform: translateY(0); }
.resp-note { display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 13px; color: var(--mid); margin-top: 18px; font-weight: 500; }
.resp-note i { color: var(--G); }

/* ─── QUICK INFO CARD ─── */
.quick-card { padding: 26px; }
.qi-row {
  display: flex; align-items: center; justify-content: space-between;
  padding: 12px 16px; background: var(--bg); border: 1px solid var(--hr);
  border-radius: 12px; margin-bottom: 10px; transition: all .3s var(--E);
}
.qi-row:last-child { margin-bottom: 0; }
.qi-row:hover { border-color: var(--B); background: #fff; box-shadow: 0 4px 12px rgba(48,59,151,0.05); }
.qi-lbl { font-size: 13px; font-weight: 600; color: var(--mid); display: flex; align-items: center; gap: 8px; }
.qi-lbl i { color: var(--B); width: 16px; text-align: center; }
.qi-val { font-size: 14px; font-weight: 700; color: var(--ink); }

/* ─── RESPONSIVE ─── */
@media(max-width:1100px){ .grid{grid-template-columns:1fr;} .sticky-col{position:static;} }
@media(max-width:768px){
  .bento{grid-template-columns:1fr 1fr;}
  .specs{grid-template-columns:1fr;}
  .hero{height:55vh;min-height:480px;}
  .gcp{padding:24px;}
  .outer{padding:0 16px 80px;}
  .hero-badges{top:16px;inset-inline-start:16px;}
  .hero-acts{top:16px;inset-inline-end:16px;}
  .thumbs-overlap { margin: -30px auto 20px; }
  .hero-price { bottom: 16px; left: 16px; padding: 12px 18px; }
  .hero-price-main { font-size: 26px; }
}
@media(max-width:480px){ .ag-acts{grid-template-columns:1fr;} }
</style>
</head>
<body>

@php $navbarStyle = 'light'; @endphp
<div style="background:#fff; border-bottom: 1px solid var(--hr); position:relative; z-index:1000;">
  @include('navbar')
</div>

<div class="hero">
  <div class="swiper swiper-main" dir="ltr">
    <div class="swiper-wrapper">
      @foreach($property->images as $photo)
      <div class="swiper-slide">
        <img src="{{ $photo }}" alt="Property" onerror="this.src='{{ asset('property_images/default-property.jpg') }}'"/>
      </div>
      @endforeach
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

    <div class="hero-price">
      <div class="hero-price-sub">USD</div>
      <div class="hero-price-main price-display" data-usd="{{ $property->price['usd'] ?? 0 }}">
        ${{ number_format($property->price['usd'] ?? 0) }}
      </div>
      <div class="hero-price-note" data-i18n="{{ $property->listing_type === 'rent' ? 'perMonth' : 'totalPrice' }}">
        {{ $property->listing_type === 'rent' ? 'per month' : 'total price' }}
      </div>
    </div>

    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>
  </div>
</div>

<div class="outer">

  <div class="thumbs-overlap">
    <div class="swiper swiper-thumbs" dir="ltr">
      <div class="swiper-wrapper">
        @foreach($property->images as $photo)
        <div class="swiper-slide">
          <img src="{{ $photo }}" alt="Thumbnail" onerror="this.src='{{ asset('property_images/default-property.jpg') }}'"/>
        </div>
        @endforeach
      </div>
    </div>
  </div>

  @php
    $owner = $property->owner ?? null;
    $canAgent = false;
    $agentUrl = '#';
    if ($owner) {
      $oc = get_class($owner);
      if ($oc === 'App\\Models\\Agent') {
          $canAgent = true;
          $agentUrl = route('agent.profile', $owner->id);
      } elseif ($oc === 'App\\Models\\RealEstateOffice') {
          $canAgent = true;
          $agentUrl = route('office.profile', $owner->id);
      }
    }
  @endphp

<div class="grid">
<div>
  <div class="title-card">
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

    @if($canAgent)
      <a href="{{ $agentUrl }}" class="prof-btn">
        <i class="fas fa-user-tie"></i> <span data-i18n="viewProfile">View Professional Profile</span> <i class="fas fa-arrow-left"></i>
      </a>
    @endif
  </div>

  <div class="gc gcp sb">
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
    <div class="sh" style="border-color:#fecaca;">
      <div class="sh-ico" style="background:#fee2e2;border-color:#fca5a5;color:#dc2626;">
        <i class="fas fa-triangle-exclamation"></i>
      </div>
      <span style="color:#b91c1c;" data-i18n="reportTitle">Report This Listing</span>
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

</div>
<div class="sticky-col">

  @if($canAgent && $owner)
  <div class="gc agent-card top-accent">
    <div class="ag-avatar">
      <img
        src="{{ $owner->profile_image ?? $owner->image ?? 'https://ui-avatars.com/api/?name='.urlencode($owner->agent_name ?? $owner->name ?? 'Agent').'&background=eef0fb&color=303b97&size=80' }}"
        alt="Agent"
        onerror="this.src='https://ui-avatars.com/api/?name=Agent&background=eef0fb&color=303b97&size=80'"
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
    <div class="sh" style="font-size:17px;margin-bottom:18px;padding-bottom:16px;">
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

</div></div></div>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
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
        reportDesc: 'کێشەی ئەم خانوو لێرە بنووسە...', fullNamePh: 'ناوی خۆت بنووسە',
        msgDefault: 'سڵاو، ئارەزووی ئەم خانووەم هەیە. تکایە پەیوەندیم پێوە بکە.',
        val_available: 'بەردەستە', val_n_a: 'نەزانراو', val_fiber_optic: 'فایبەر ئۆپتیک', val_active: 'چالاک',
        propType: 'جۆري خانوو', yearBuilt: 'ساڵی درووستکردن', floorNum: 'نهۆم', electricity: 'کارەبا', water: 'ئاو', internet: 'ئینتەرنێت'
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

    document.querySelectorAll('.lang-btn').forEach(b => {
        b.classList.toggle('active', b.getAttribute('data-lang') === lang);
    });

    document.querySelectorAll('[data-i18n]').forEach(el => {
      const key = el.getAttribute('data-i18n');
      if (T[key] !== undefined) el.textContent = T[key];
    });

    document.querySelectorAll('[data-i18n-val]').forEach(el => {
      const key = 'val_' + el.getAttribute('data-i18n-val').replace(/\s+/g, '_').replace(/[^a-z0-9_]/gi, '');
      if (T[key] !== undefined) el.textContent = T[key];
    });

    document.querySelectorAll('[data-placeholder-i18n]').forEach(el => {
      const key = el.getAttribute('data-placeholder-i18n');
      if (T[key] !== undefined) el.placeholder = T[key];
    });

    const dynEls = ['dyn-title', 'dyn-desc', 'dyn-addr'];
    dynEls.forEach(id => {
      const el = document.getElementById(id);
      if(el) {
          const transTxt = el.getAttribute('data-' + lang);
          if(transTxt && transTxt.trim() !== '') {
              if(id === 'dyn-addr') {
                  el.querySelector('span').textContent = transTxt;
              } else {
                  el.textContent = transTxt;
              }
          }
      }
    });

    const msgArea = document.getElementById('inquiry-msg');
    if(msgArea && (msgArea.value === this.translations['en'].msgDefault ||
                   msgArea.value === this.translations['ku'].msgDefault ||
                   msgArea.value === this.translations['ar'].msgDefault)) {
        msgArea.value = T.msgDefault;
    }
  }
}
const propI18n = new PropertyI18n();
propI18n.init();

/* ── SWIPER GALLERY SETUP ── */
// 1. Initialize Thumbnails Swiper
const thumbsSwiper = new Swiper('.swiper-thumbs', {
  spaceBetween: 12,
  slidesPerView: 'auto',
  freeMode: true,
  watchSlidesProgress: true,
});

// 2. Initialize Main Swiper and link it to Thumbnails
const mainSwiper = new Swiper('.swiper-main', {
  loop: true,
  effect: 'fade',
  fadeEffect: { crossFade: true },
  speed: 800,
  navigation: {
    nextEl: '.swiper-button-next',
    prevEl: '.swiper-button-prev',
  },
  thumbs: {
    swiper: thumbsSwiper
  }
});

/* ── GSAP ANIMATIONS (REMOVED OPACITY 0 FOR SAFETY) ── */
document.addEventListener("DOMContentLoaded", (event) => {
  // Animates elements up gently, without initially hiding them in CSS
  gsap.from('.gc, .thumbs-overlap', {
    y: 30, opacity: 0, duration: 0.8, stagger: 0.1, ease: "power2.out", clearProps: "all"
  });
});

/* ── FAV & SHARE TOGGLE ── */
document.getElementById('fav-btn').addEventListener('click',function(){
  const i=this.querySelector('i');
  const isFav=i.classList.toggle('fas');
  i.classList.toggle('far');
  this.style.background=isFav?'#ef4444':'rgba(255,255,255,.9)';
  this.style.color=isFav?'#fff':'#1a225a';
  this.style.borderColor=isFav?'#ef4444':'transparent';
});

document.getElementById('share-btn').addEventListener('click',function(){
  if(navigator.share){
    navigator.share({title:document.title,url:location.href});
  }else{
    navigator.clipboard.writeText(location.href).then(()=>{
      this.innerHTML='<i class="fas fa-check"></i>';
      this.style.background='#10b981';
      this.style.color='#fff';
      setTimeout(()=>{
        this.innerHTML='<i class="fas fa-share-nodes"></i>';
        this.style.background='rgba(255,255,255,.9)';
        this.style.color='#1a225a';
      },2000);
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
        background:linear-gradient(135deg,#303b97,#1a225a);
        transform:rotate(-45deg);border:3px solid #fff;
        box-shadow:0 6px 20px rgba(48,59,151,.4);
        display:flex;align-items:center;justify-content:center;
      ">
        <i class='fas fa-building' style='transform:rotate(45deg);color:#d4af37;font-size:15px;'></i>
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
        <div style="font-family:'Playfair Display',serif;font-weight:800;font-size:16px;margin-bottom:5px;color:#1a225a;">
          {{ $property->name['en'] ?? 'Property' }}
        </div>
        <div style="font-size:12px;color:#52596e;margin-bottom:8px;font-weight:500;">
          <i class='fas fa-location-dot' style='color:#303b97;margin-right:4px;'></i>
          {{ $property->address_details['city']['en'] ?? 'Kurdistan, Iraq' }}
        </div>
        <div style="font-family:'Playfair Display',serif;font-size:18px;font-weight:800;color:#d4af37;">
          $${{ number_format($property->price['usd'] ?? 0) }}
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
