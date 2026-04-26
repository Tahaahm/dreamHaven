<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>{{ $property->name['en'] ?? 'Property' }} — Dream Mulk</title>
<meta property="og:title" content="{{ $property->name['en'] ?? 'Property' }} — Dream Mulk"/>
<meta property="og:description" content="{{ Str::limit($property->description['en'] ?? '', 120) }}"/>
<meta property="og:image" content="{{ $property->images[0] ?? '' }}"/>
<meta property="og:url" content="{{ url()->current() }}"/>

<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600;700&family=Noto+Naskh+Arabic:wght@400;500;600;700&family=Noto+Sans+Arabic:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<style>
/* ════════════════════════════════════════════════════════
   DREAM MULK · PROPERTY DETAIL
   Aesthetic: Restrained Luxury — Generous space, refined type
   Display: Cormorant Garamond · UI: Outfit
════════════════════════════════════════════════════════ */
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

:root {
  /* ── Brand ── */
  /* Primary:  Flutter 0xff303b97 */
  --primary:    #303b97;
  --primary-d:  #232c78;   /* darkened ~15% for hover/pressed */
  --primary-l:  #3d49b5;   /* lightened for hover states */
  --primary-bg: #eef0fb;   /* 8% tint — icon backgrounds, input focus fill */
  --primary-border: rgba(48,59,151,.18);

  /* Gold:  Flutter 0xFFD4A853 / 0xFFECC97A / 0xFFB8882E */
  --gold:       #D4A853;   /* base  */
  --gold-lt:    #ECC97A;   /* light */
  --gold-dk:    #B8882E;   /* dark  */
  --gold-pale:  #fdf4e3;   /* very pale tint for backgrounds */

  /* Neutrals */
  --navy:     #0f1422;     /* near-black for display text */
  --cream:    #f8f7f4;     /* page background */
  --warm:     #f1ede6;     /* subtle card/input bg */
  --stone:    #e4ddd2;     /* borders on warm surfaces */
  --ink:      #111827;
  --ink-2:    #374151;
  --ink-3:    #6b7280;
  --ink-4:    #9ca3af;
  --border:   #e5e0d8;
  --white:    #ffffff;
  --green:    #059669;
  --red:      #dc2626;

  /* Fonts */
  --f-disp:   'Cormorant Garamond', Georgia, serif;
  --f-ui:     'Outfit', system-ui, sans-serif;
  --f-ar:     'Noto Naskh Arabic', serif;
  --f-ar-ui:  'Noto Sans Arabic', sans-serif;

  /* Motion & Shadow */
  --ease:      cubic-bezier(0.22, 1, 0.36, 1);
  --shadow-sm: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
  --shadow-md: 0 4px 16px rgba(0,0,0,0.07), 0 1px 4px rgba(0,0,0,0.04);
  --shadow-lg: 0 12px 40px rgba(0,0,0,0.1), 0 4px 12px rgba(0,0,0,0.05);
}

html { scroll-behavior: smooth; }
body {
  font-family: var(--f-ui);
  background: var(--cream);
  color: var(--ink);
  line-height: 1.6;
  overflow-x: hidden;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}
a { text-decoration: none; color: inherit; }
img { display: block; }
button { font-family: var(--f-ui); }

/* RTL */
body.lang-ku, body.lang-ar { font-family: var(--f-ar-ui); direction: rtl; }
body.lang-ku *:not(i):not([class*="fa-"]),
body.lang-ar *:not(i):not([class*="fa-"]) { font-family: var(--f-ar-ui); }
body.lang-ku .disp-font, body.lang-ar .disp-font,
body.lang-ku .prop-title, body.lang-ar .prop-title,
body.lang-ku .card-title, body.lang-ar .card-title { font-family: var(--f-ar) !important; }

/* ══════════ HERO ══════════ */
.hero {
  position: relative;
  height: 100vh; min-height: 580px; max-height: 860px;
  overflow: hidden; background: var(--navy);
  direction: ltr !important;
}

.swiper-hero { width: 100%; height: 100%; }
.swiper-hero .swiper-slide img {
  width: 100%; height: 100%; object-fit: cover;
  transform: scale(1.06);
  transition: transform 9s ease;
  filter: brightness(0.75) saturate(0.9);
}
.swiper-hero .swiper-slide-active img { transform: scale(1.0); }

.hero-grad {
  position: absolute; inset: 0; z-index: 2; pointer-events: none;
  background:
    linear-gradient(to top,  rgba(15,18,34,.95) 0%,  rgba(15,18,34,.5) 30%, transparent 60%),
    linear-gradient(to right, rgba(15,18,34,.55) 0%, transparent 55%);
}

/* — Navbar — */
.hero-nav {
  position: absolute; top: 0; left: 0; right: 0; z-index: 20;
  display: flex; align-items: center; justify-content: space-between;
  padding: 24px 48px;
  background: linear-gradient(to bottom, rgba(15,18,34,.65), transparent);
}
.hn-logo {
  font-family: var(--f-disp);
  font-size: 20px; font-weight: 500; letter-spacing: 3px;
  color: #fff; text-transform: uppercase; display: flex; align-items: center; gap: 6px;
}
.hn-logo-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--gold); }
.hn-right { display: flex; align-items: center; gap: 10px; }
.lang-btn {
  height: 34px; padding: 0 14px; border-radius: 100px;
  font-size: 11px; font-weight: 600; letter-spacing: 0.5px;
  cursor: pointer; transition: all .25s var(--ease); border: none;
  background: rgba(255,255,255,.1); color: rgba(255,255,255,.8);
  border: 1px solid rgba(255,255,255,.15);
}
.lang-btn:hover { background: rgba(255,255,255,.18); }
.lang-btn.active { background: var(--gold); color: var(--navy); border-color: var(--gold); }

/* — Action icons — */
.hero-actions {
  position: absolute; top: 82px; right: 48px; z-index: 20;
  display: flex; flex-direction: column; gap: 8px;
}
.ha-btn {
  width: 44px; height: 44px; border-radius: 12px;
  background: rgba(255,255,255,.1); backdrop-filter: blur(12px);
  border: 1px solid rgba(255,255,255,.18);
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 15px; cursor: pointer;
  transition: all .3s var(--ease);
}
.ha-btn:hover { background: rgba(255,255,255,.22); transform: scale(1.06); }
.ha-btn.faved { background: #ef4444; border-color: #ef4444; }
.ha-btn.copied { background: var(--green); border-color: var(--green); }

/* — Bottom content — */
.hero-content {
  position: absolute; bottom: 0; left: 0; right: 0; z-index: 10;
  padding: 0 48px 48px;
  display: flex; align-items: flex-end; gap: 28px;
}
.hc-left { flex: 1; max-width: 680px; }
.hc-right { flex-shrink: 0; }

.hero-badges { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
.hbadge {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 5px 14px; border-radius: 100px;
  font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
}
body.lang-ku .hbadge, body.lang-ar .hbadge { letter-spacing: 0; font-size: 11px; }
.hb-verified { background: rgba(5,150,105,.88); color: #fff; }
.hb-featured { background: var(--gold); color: var(--navy); }
.hb-type {
  background: rgba(255,255,255,.12); backdrop-filter: blur(8px);
  color: rgba(255,255,255,.9); border: 1px solid rgba(255,255,255,.25);
}

.hero-eyebrow {
  font-size: 10px; font-weight: 600; letter-spacing: 4px; text-transform: uppercase;
  color: var(--gold-lt); display: flex; align-items: center; gap: 10px; margin-bottom: 12px;
}
body.lang-ku .hero-eyebrow, body.lang-ar .hero-eyebrow { letter-spacing: 0; font-size: 12px; }
.hero-eyebrow::before { content: ''; width: 28px; height: 1px; background: var(--gold-lt); display: block; }

.prop-title {
  font-family: var(--f-disp);
  font-size: clamp(32px, 5vw, 62px);
  font-weight: 500; line-height: 1.1;
  color: #fff; margin-bottom: 14px;
  letter-spacing: -0.3px;
}
.hero-location {
  display: flex; align-items: center; gap: 8px;
  font-size: 13px; font-weight: 500; color: rgba(255,255,255,.65);
  margin-bottom: 28px;
}
.hero-location i { color: var(--gold-lt); font-size: 12px; }

.price-glass {
  display: inline-flex; align-items: flex-end; gap: 8px;
  background: rgba(255,255,255,.08); backdrop-filter: blur(16px);
  border: 1px solid rgba(255,255,255,.14); border-radius: 16px;
  padding: 18px 26px; position: relative; overflow: hidden;
}
.price-glass::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0; height: 1px;
  background: linear-gradient(90deg, transparent, rgba(212,170,90,.6), transparent);
}
.price-cur { font-size: 13px; font-weight: 600; color: var(--gold-lt); align-self: flex-start; margin-top: 5px; }
.price-num {
  font-family: var(--f-disp);
  font-size: clamp(32px, 4vw, 48px);
  font-weight: 600; color: #fff; line-height: 1; direction: ltr;
}
.price-per { font-size: 12px; color: rgba(255,255,255,.5); font-weight: 400; margin-bottom: 4px; }

/* Quick stats glass pill */
.hero-stats {
  display: flex;
  background: rgba(255,255,255,.08); backdrop-filter: blur(16px);
  border: 1px solid rgba(255,255,255,.14); border-radius: 16px; overflow: hidden;
}
.hs-item { padding: 18px 22px; text-align: center; border-right: 1px solid rgba(255,255,255,.1); }
.hs-item:last-child { border-right: none; }
.hs-v {
  font-family: var(--f-disp);
  font-size: 26px; font-weight: 500;
  color: #fff; display: block; line-height: 1;
}
.hs-l {
  font-size: 9px; font-weight: 700; letter-spacing: 1.8px;
  text-transform: uppercase; color: rgba(255,255,255,.45);
  display: block; margin-top: 6px;
}
body.lang-ku .hs-l, body.lang-ar .hs-l { letter-spacing: 0; font-size: 11px; }

/* Swiper arrows */
.swiper-button-prev, .swiper-button-next {
  width: 46px; height: 46px; border-radius: 12px; z-index: 20;
  background: rgba(255,255,255,.1); backdrop-filter: blur(10px);
  border: 1px solid rgba(255,255,255,.18); color: #fff;
  top: auto !important; bottom: 54px;
  transition: background .25s;
}
.swiper-button-prev { right: 110px !important; left: auto !important; }
.swiper-button-next { right: 48px !important; left: auto !important; }
body.rtl .swiper-button-prev { left: 110px !important; right: auto !important; }
body.rtl .swiper-button-next { left: 48px !important; right: auto !important; }
.swiper-button-prev::after, .swiper-button-next::after { font-size: 13px; font-weight: 900; }
.swiper-button-prev:hover, .swiper-button-next:hover { background: rgba(255,255,255,.2); }

.slide-dot-wrap {
  position: absolute; bottom: 64px; left: 50%; transform: translateX(-50%); z-index: 20;
}
.swiper-pagination-bullet { background: rgba(255,255,255,.4) !important; opacity: 1 !important; width: 6px !important; height: 6px !important; }
.swiper-pagination-bullet-active { background: var(--gold) !important; width: 20px !important; border-radius: 3px !important; }

/* ══════════ THUMBNAIL STRIP ══════════ */
.thumb-bar {
  position: sticky; top: 0; z-index: 100;
  background: var(--white); border-bottom: 1px solid var(--border);
  box-shadow: var(--shadow-sm);
}
.thumb-bar-inner {
  max-width: 1380px; margin: 0 auto;
  display: flex; align-items: center; gap: 12px;
  padding: 12px 48px;
}
.swiper-thumbs { flex: 1; height: 58px; }
.swiper-thumbs .swiper-slide {
  width: 82px; height: 100%; border-radius: 8px; overflow: hidden;
  opacity: .4; cursor: pointer;
  border: 2px solid transparent; transition: all .25s var(--ease);
}
.swiper-thumbs .swiper-slide img { width: 100%; height: 100%; object-fit: cover; }
.swiper-thumbs .swiper-slide-thumb-active { opacity: 1; border-color: var(--gold); }
.swiper-thumbs .swiper-slide:hover { opacity: .8; }
.thumb-total {
  font-size: 11.5px; font-weight: 600; color: var(--ink-3);
  white-space: nowrap; display: flex; align-items: center; gap: 6px;
}
.thumb-total i { color: var(--gold); font-size: 11px; }

/* ══════════ PAGE LAYOUT ══════════ */
.page-wrap { max-width: 1380px; margin: 0 auto; padding: 40px 48px 100px; }
.page-grid { display: grid; grid-template-columns: 1fr 380px; gap: 24px; align-items: start; }

/* ══════════ CARDS ══════════ */
.card {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: 20px;
  overflow: hidden;
  box-shadow: var(--shadow-sm);
  margin-bottom: 20px;
  transition: box-shadow .3s;
}
.card:hover { box-shadow: var(--shadow-md); }
.card:last-child { margin-bottom: 0; }
.cp { padding: 32px; }

.sh {
  display: flex; align-items: center; gap: 12px;
  margin-bottom: 24px; padding-bottom: 18px; border-bottom: 1px solid var(--border);
}
.sh-icon {
  width: 36px; height: 36px; border-radius: 10px;
  background: var(--warm); border: 1px solid var(--stone);
  display: flex; align-items: center; justify-content: center;
  font-size: 13px; color: var(--primary); flex-shrink: 0;
}
.sh-title {
  font-family: var(--f-disp);
  font-size: 18px; font-weight: 500; color: var(--navy);
}

/* ══════════ BENTO STATS ══════════ */
.bento {
  display: grid; grid-template-columns: repeat(4,1fr);
  gap: 1px; background: var(--border); border-radius: 0;
}
.bc {
  background: var(--white); padding: 28px 16px; text-align: center;
  transition: background .25s; position: relative;
}
.bc::after {
  content: ''; position: absolute; bottom: 0; left: 50%;
  transform: translateX(-50%); width: 0; height: 2px;
  background: var(--gold); transition: width .3s var(--ease);
}
.bc:hover { background: var(--cream); }
.bc:hover::after { width: 50%; }
.bc-ico {
  width: 40px; height: 40px; border-radius: 10px;
  background: var(--warm); border: 1px solid var(--stone);
  display: flex; align-items: center; justify-content: center;
  font-size: 16px; color: var(--primary); margin: 0 auto 14px;
}
.bc-v {
  font-family: var(--f-disp);
  font-size: 28px; font-weight: 500; color: var(--navy);
  display: block; line-height: 1;
}
.bc-l {
  font-size: 9.5px; font-weight: 700; letter-spacing: 1.5px;
  text-transform: uppercase; color: var(--ink-3);
  display: block; margin-top: 7px;
}
body.lang-ku .bc-l, body.lang-ar .bc-l { letter-spacing: 0; font-size: 11px; text-transform: none; }

/* ══════════ DESCRIPTION ══════════ */
.desc-body { font-size: 14.5px; line-height: 1.92; color: var(--ink-2); font-weight: 400; }

/* ══════════ SPECS ══════════ */
.specs-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.spec-row {
  display: flex; align-items: center; justify-content: space-between;
  padding: 13px 16px; background: var(--cream); border: 1px solid var(--border);
  border-radius: 12px; transition: border-color .25s, background .25s;
}
.spec-row:hover { border-color: var(--primary-l); background: var(--white); }
.spec-key {
  font-size: 12px; color: var(--ink-3); font-weight: 500;
  display: flex; align-items: center; gap: 8px;
}
.spec-key i { color: var(--primary); font-size: 11px; width: 12px; text-align: center; }
.spec-val { font-size: 13px; font-weight: 700; color: var(--ink); }

/* ══════════ AMENITIES ══════════ */
.amenity-wrap { display: flex; flex-wrap: wrap; gap: 8px; }
.amenity-tag {
  display: inline-flex; align-items: center; gap: 7px;
  padding: 9px 16px; border-radius: 100px;
  font-size: 12.5px; font-weight: 500; color: var(--ink-2);
  background: var(--cream); border: 1px solid var(--border);
  transition: all .25s var(--ease);
}
.amenity-tag:hover { background: var(--navy); color: #fff; border-color: var(--navy); }
.amenity-tag i { color: var(--gold); font-size: 10px; }
.amenity-tag:hover i { color: var(--gold-lt); }

/* ══════════ MAP ══════════ */
.map-container { height: 360px; direction: ltr; }
#prop-map { width: 100%; height: 100%; }
.leaflet-control-attribution { font-size: 9px !important; }
.leaflet-control-zoom a { border-radius: 8px !important; border: 1px solid var(--border) !important; color: var(--primary) !important; background: var(--white) !important; }
.leaflet-popup-content-wrapper { border-radius: 14px !important; box-shadow: var(--shadow-lg) !important; }
.leaflet-popup-tip { background: var(--white) !important; }

/* ══════════ REPORT ══════════ */
.report-card { border-top: 2px solid #fca5a5 !important; }
.report-ta {
  width: 100%; background: var(--white); border: 1px solid #fecaca;
  color: var(--ink); border-radius: 12px; padding: 13px 15px;
  font-family: var(--f-ui); font-size: 13.5px; resize: none; outline: none;
  transition: border .25s; margin-bottom: 14px;
}
body.lang-ku .report-ta, body.lang-ar .report-ta { font-family: var(--f-ar-ui); }
.report-ta:focus { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,.08); }
.btn-report {
  display: inline-flex; align-items: center; gap: 7px;
  padding: 11px 20px; border-radius: 10px;
  font-size: 13px; font-weight: 600; font-family: var(--f-ui);
  background: #fee2e2; border: 1px solid #fca5a5; color: #b91c1c; cursor: pointer;
  transition: all .25s;
}
.btn-report:hover { background: #ef4444; color: #fff; border-color: #ef4444; }
.success-msg {
  display: flex; align-items: center; gap: 7px; margin-top: 12px;
  padding: 11px 15px; border-radius: 10px;
  background: #d1fae5; border: 1px solid #6ee7b7; color: #047857; font-size: 13px; font-weight: 500;
}

/* ══════════ SIDEBAR ══════════ */
.sidebar { display: flex; flex-direction: column; gap: 20px; position: sticky; top: 82px; }

/* — Agent card — */
.agent-card { border-top: 2px solid var(--gold) !important; }
.agent-inner { padding: 28px 24px; text-align: center; }
.ag-av-wrap { position: relative; width: 80px; height: 80px; margin: 0 auto 16px; }
.ag-av-wrap img {
  width: 100%; height: 100%; border-radius: 50%; object-fit: cover;
  border: 3px solid var(--white);
  box-shadow: 0 0 0 2px var(--gold), var(--shadow-md);
}
.ag-badge {
  position: absolute; bottom: 2px; right: 2px;
  width: 20px; height: 20px; border-radius: 50%;
  background: var(--green); border: 2px solid var(--white);
  display: flex; align-items: center; justify-content: center;
  font-size: 8px; color: #fff;
}
.ag-name {
  font-family: var(--f-disp);
  font-size: 20px; font-weight: 500; color: var(--navy); margin-bottom: 3px;
}
.ag-role { font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: var(--ink-3); margin-bottom: 12px; }
body.lang-ku .ag-role, body.lang-ar .ag-role { letter-spacing: 0; font-size: 11.5px; text-transform: none; }
.ag-stars { display: flex; align-items: center; justify-content: center; gap: 3px; direction: ltr; margin-bottom: 20px; }
.ag-stars i { font-size: 12px; color: var(--gold); }
.ag-stars span { font-size: 12px; color: var(--ink-3); font-weight: 600; margin-left: 5px; }

.ag-btns { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 20px; }
.ag-btn {
  padding: 11px; border-radius: 12px; font-size: 13px; font-weight: 600;
  display: flex; align-items: center; justify-content: center; gap: 7px;
  transition: all .25s var(--ease); cursor: pointer;
  border: none; text-decoration: none;
}
.btn-call { background: #d1fae5; border: 1px solid #a7f3d0; color: #047857; }
.btn-call:hover { background: #10b981; color: #fff; border-color: #10b981; }
.btn-view {
  background: var(--navy); color: #fff; border: 1px solid var(--navy);
}
.btn-view:hover { background: var(--primary-d); }

.ag-divider { height: 1px; background: var(--border); margin-bottom: 16px; }
.ag-stats { display: flex; }
.ag-stat { flex: 1; text-align: center; padding: 0 8px; border-right: 1px solid var(--border); }
.ag-stat:last-child { border-right: none; }
.ag-stat-n { font-family: var(--f-disp); font-size: 20px; font-weight: 500; color: var(--primary); display: block; }
.ag-stat-l { font-size: 9px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: var(--ink-3); }
body.lang-ku .ag-stat-l, body.lang-ar .ag-stat-l { letter-spacing: 0; font-size: 11px; text-transform: none; }

/* — Contact form — */
.contact-inner { padding: 28px 24px; }
.card-title {
  font-family: var(--f-disp);
  font-size: 20px; font-weight: 500; color: var(--navy); margin-bottom: 4px;
}
.card-sub { font-size: 12.5px; color: var(--ink-3); line-height: 1.65; margin-bottom: 20px; }
.fg { margin-bottom: 14px; }
.fg label { display: block; font-size: 10.5px; font-weight: 700; letter-spacing: 0.4px; text-transform: uppercase; color: var(--primary); margin-bottom: 7px; }
body.lang-ku .fg label, body.lang-ar .fg label { font-size: 12px; letter-spacing: 0; text-transform: none; }
.fg input, .fg textarea {
  width: 100%; background: var(--cream); border: 1px solid var(--border);
  color: var(--ink); border-radius: 11px; padding: 12px 14px;
  font-family: var(--f-ui); font-size: 13.5px; outline: none;
  transition: all .25s var(--ease);
}
body.lang-ku .fg input, body.lang-ar .fg input,
body.lang-ku .fg textarea, body.lang-ar .fg textarea { font-family: var(--f-ar-ui); }
.fg input::placeholder, .fg textarea::placeholder { color: var(--ink-4); }
.fg input:focus, .fg textarea:focus { border-color: var(--primary-l); background: var(--white); box-shadow: 0 0 0 3px rgba(48,59,151,.08); }
.fg textarea { resize: none; }
.send-btn {
  width: 100%; height: 50px; border: none; border-radius: 13px; cursor: pointer;
  background: var(--navy); color: #fff;
  font-family: var(--f-ui); font-size: 14px; font-weight: 600;
  display: flex; align-items: center; justify-content: center; gap: 9px;
  box-shadow: 0 4px 16px rgba(15,18,34,.25);
  transition: all .3s var(--ease); letter-spacing: 0.2px;
}
body.lang-ku .send-btn, body.lang-ar .send-btn { font-family: var(--f-ar-ui); }
.send-btn:hover { background: var(--primary-d); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(15,18,34,.3); }
.send-btn:active { transform: translateY(0); }
.resp-note { display: flex; align-items: center; justify-content: center; gap: 7px; font-size: 12px; color: var(--ink-3); margin-top: 14px; }
.resp-note i { color: var(--gold); font-size: 11px; }

/* — Quick info — */
.qi-inner { padding: 22px 24px; }
.qi-row {
  display: flex; align-items: center; justify-content: space-between;
  padding: 11px 14px; background: var(--cream); border: 1px solid var(--border);
  border-radius: 10px; margin-bottom: 8px; transition: all .25s;
}
.qi-row:last-child { margin-bottom: 0; }
.qi-row:hover { border-color: var(--primary-l); background: var(--white); }
.qi-key { font-size: 12px; font-weight: 600; color: var(--ink-3); display: flex; align-items: center; gap: 8px; }
.qi-key i { color: var(--primary); font-size: 11px; width: 12px; text-align: center; }
.qi-val { font-size: 13px; font-weight: 700; color: var(--ink); }

/* — Share strip — */
.share-inner {
  padding: 18px 24px; display: flex; align-items: center; gap: 10px;
}
.share-lbl { font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: var(--ink-3); flex: 1; }
body.lang-ku .share-lbl, body.lang-ar .share-lbl { letter-spacing: 0; }
.share-btn {
  width: 36px; height: 36px; border-radius: 9px; cursor: pointer;
  background: var(--cream); border: 1px solid var(--border);
  display: flex; align-items: center; justify-content: center;
  font-size: 13px; color: var(--ink-2); text-decoration: none;
  transition: all .25s var(--ease);
}
.share-btn:hover { background: var(--navy); color: #fff; border-color: var(--navy); transform: translateY(-2px); }

/* ══════════ LOAD ANIMATIONS ══════════ */
@keyframes rise {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: translateY(0); }
}
.hc-left > * { animation: rise .8s var(--ease) both; }
.hero-badges  { animation-delay: .05s; }
.hero-eyebrow { animation-delay: .15s; }
.prop-title   { animation-delay: .22s; }
.hero-location{ animation-delay: .28s; }
.price-glass  { animation-delay: .34s; }

.card { animation: rise .65s var(--ease) both; }
.main-col .card:nth-child(1) { animation-delay: .05s; }
.main-col .card:nth-child(2) { animation-delay: .1s; }
.main-col .card:nth-child(3) { animation-delay: .15s; }
.main-col .card:nth-child(4) { animation-delay: .2s; }
.main-col .card:nth-child(5) { animation-delay: .25s; }
.main-col .card:nth-child(6) { animation-delay: .3s; }
.sidebar .card:nth-child(1)  { animation-delay: .08s; }
.sidebar .card:nth-child(2)  { animation-delay: .14s; }
.sidebar .card:nth-child(3)  { animation-delay: .2s; }
.sidebar .card:nth-child(4)  { animation-delay: .26s; }

/* ══════════ TOAST ══════════ */
.toast {
  position: fixed; bottom: 32px; left: 50%; transform: translateX(-50%) translateY(20px);
  background: var(--navy); color: #fff; padding: 12px 22px; border-radius: 100px;
  font-size: 13px; font-weight: 500; z-index: 9999;
  display: flex; align-items: center; gap: 8px;
  box-shadow: var(--shadow-lg); opacity: 0; pointer-events: none;
  transition: all .35s var(--ease);
}
.toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
.toast i { color: var(--gold-lt); }

/* ══════════ RESPONSIVE ══════════ */
@media (max-width: 1200px) {
  .page-grid { grid-template-columns: 1fr 340px; }
}
@media (max-width: 1024px) {
  .page-grid { grid-template-columns: 1fr; }
  .sidebar { position: static; }
  .hero-content { padding: 0 28px 36px; flex-direction: column; align-items: flex-start; gap: 16px; }
  .hc-right { display: none; }
  .hero-nav { padding: 20px 28px; }
  .hero-actions { right: 28px; top: 74px; }
  .swiper-button-prev { right: 86px !important; bottom: 42px; }
  .swiper-button-next { right: 28px !important; bottom: 42px; }
  body.rtl .swiper-button-prev { left: 86px !important; right: auto !important; }
  body.rtl .swiper-button-next { left: 28px !important; right: auto !important; }
  .thumb-bar-inner { padding: 10px 28px; }
  .page-wrap { padding: 28px 28px 80px; }
}
@media (max-width: 768px) {
  .hero { max-height: 680px; }
  .bento { grid-template-columns: 1fr 1fr; }
  .specs-grid { grid-template-columns: 1fr; }
  .cp { padding: 22px; }
  .prop-title { font-size: clamp(28px, 7vw, 38px); }
  .price-num { font-size: 34px; }
  .page-wrap { padding: 18px 16px 80px; }
  .thumb-bar-inner { padding: 10px 16px; }
  .hero-nav { padding: 16px 20px; }
  .hero-content { padding: 0 20px 26px; }
  .hero-actions { right: 20px; top: 66px; }
  .swiper-button-prev { right: 76px !important; bottom: 36px; }
  .swiper-button-next { right: 20px !important; bottom: 36px; }
  body.rtl .swiper-button-prev { left: 76px !important; right: auto !important; }
  body.rtl .swiper-button-next { left: 20px !important; right: auto !important; }
}
@media (max-width: 480px) {
  .ag-btns { grid-template-columns: 1fr; }
  .hero-badges { gap: 6px; }
  .hn-right .lang-btn:first-child { display: none; }
}
</style>
</head>
<body>

<!-- ══════════════════════════════════════
     HERO
══════════════════════════════════════ -->
<div class="hero">

  <div class="swiper swiper-hero" dir="ltr">
    <div class="swiper-wrapper">
      @foreach($property->images as $img)
      <div class="swiper-slide">
        <img
          src="{{ $img }}"
          alt="Property image"
          onerror="this.src='{{ asset('property_images/default-property.jpg') }}'"
        />
      </div>
      @endforeach
    </div>
    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>
    <div class="swiper-pagination slide-dot-wrap"></div>
  </div>

  <div class="hero-grad"></div>

  <!-- Navbar -->
  <nav class="hero-nav">
    <div class="hn-logo">
      Dream<div class="hn-logo-dot"></div>Mulk
    </div>
    <div class="hn-right">
      <button class="lang-btn" data-lang="ku">کوردی</button>
      <button class="lang-btn" data-lang="ar">عربی</button>
      <button class="lang-btn" data-lang="en">EN</button>
    </div>
  </nav>

  <!-- Action buttons -->
  <div class="hero-actions">
    <div class="ha-btn" id="fav-btn" title="Save property">
      <i class="far fa-heart"></i>
    </div>
    <div class="ha-btn" id="share-hero-btn" title="Copy link">
      <i class="fas fa-link"></i>
    </div>
  </div>

  <!-- Hero content -->
  <div class="hero-content">
    <div class="hc-left">
      <div class="hero-badges">
        @if($property->verified)
          <span class="hbadge hb-verified">
            <i class="fas fa-shield-check"></i>
            <span data-i18n="verified">Verified</span>
          </span>
        @endif
        @if($property->is_boosted)
          <span class="hbadge hb-featured">
            <i class="fas fa-crown"></i>
            <span data-i18n="featured">Featured</span>
          </span>
        @endif
        <span class="hbadge hb-type">
          <i class="fas fa-tag"></i>
          <span data-i18n="{{ strtolower($property->listing_type) }}">{{ ucfirst($property->listing_type) }}</span>
        </span>
      </div>

      <div class="hero-eyebrow" data-i18n="eyebrow">Dream Mulk Property</div>

      <h1
        class="prop-title"
        id="dyn-title"
        data-en="{{ $property->name['en'] ?? 'Untitled Property' }}"
        data-ku="{{ $property->name['ku'] ?? $property->name ?? 'Untitled Property' }}"
        data-ar="{{ $property->name['ar'] ?? $property->name ?? 'Untitled Property' }}"
      >{{ $property->name['en'] ?? 'Untitled Property' }}</h1>

      <div
        class="hero-location"
        id="dyn-addr"
        data-en="{{ $property->address_details['city']['en'] ?? 'Kurdistan, Iraq' }}"
        data-ku="{{ $property->address_details['city']['ku'] ?? 'Kurdistan, Iraq' }}"
        data-ar="{{ $property->address_details['city']['ar'] ?? 'Kurdistan, Iraq' }}"
      >
        <i class="fas fa-location-dot"></i>
        <span>{{ $property->address_details['city']['en'] ?? 'Kurdistan, Iraq' }}</span>
      </div>

      <div class="price-glass">
        <span class="price-cur">USD</span>
        <span class="price-num price-display" data-usd="{{ $property->price['usd'] ?? 0 }}">
          ${{ number_format($property->price['usd'] ?? 0) }}
        </span>
        <span class="price-per" data-i18n="{{ $property->listing_type === 'rent' ? 'perMonth' : 'totalPrice' }}">
          {{ $property->listing_type === 'rent' ? 'per month' : 'total price' }}
        </span>
      </div>
    </div>

    <!-- Quick stats panel — desktop only -->
    <div class="hc-right">
      <div class="hero-stats">
        <div class="hs-item">
          <span class="hs-v">{{ $property->rooms['bedroom']['count'] ?? 0 }}</span>
          <span class="hs-l" data-i18n="beds">Beds</span>
        </div>
        <div class="hs-item">
          <span class="hs-v">{{ $property->rooms['bathroom']['count'] ?? 0 }}</span>
          <span class="hs-l" data-i18n="baths">Baths</span>
        </div>
        <div class="hs-item">
          <span class="hs-v">{{ number_format($property->area ?? 0) }}</span>
          <span class="hs-l" data-i18n="area">m²</span>
        </div>
        <div class="hs-item">
          <span class="hs-v" data-i18n="{{ $property->furnished ? 'yes' : 'no' }}">
            {{ $property->furnished ? 'Yes' : 'No' }}
          </span>
          <span class="hs-l" data-i18n="furnished">Furnished</span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════
     THUMBNAIL STRIP
══════════════════════════════════════ -->
<div class="thumb-bar">
  <div class="thumb-bar-inner">
    <div class="swiper swiper-thumbs" dir="ltr">
      <div class="swiper-wrapper">
        @foreach($property->images as $img)
        <div class="swiper-slide">
          <img src="{{ $img }}" alt="Thumb" onerror="this.src='{{ asset('property_images/default-property.jpg') }}'"/>
        </div>
        @endforeach
      </div>
    </div>
    <div class="thumb-total">
      <i class="far fa-images"></i>
      <span id="total-photos">{{ count($property->images) }} <span data-i18n="photos">Photos</span></span>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════
     MAIN PAGE
══════════════════════════════════════ -->
<div class="page-wrap">
<div class="page-grid">

<!-- ─── MAIN COLUMN ─── -->
<div class="main-col">

  {{-- Bento Stats --}}
  <div class="card">
    <div class="bento">
      <div class="bc">
        <div class="bc-ico"><i class="fas fa-bed"></i></div>
        <span class="bc-v">{{ $property->rooms['bedroom']['count'] ?? 0 }}</span>
        <span class="bc-l" data-i18n="beds">Bedrooms</span>
      </div>
      <div class="bc">
        <div class="bc-ico"><i class="fas fa-bath"></i></div>
        <span class="bc-v">{{ $property->rooms['bathroom']['count'] ?? 0 }}</span>
        <span class="bc-l" data-i18n="baths">Bathrooms</span>
      </div>
      <div class="bc">
        <div class="bc-ico"><i class="fas fa-vector-square"></i></div>
        <span class="bc-v">{{ number_format($property->area ?? 0) }}</span>
        <span class="bc-l" data-i18n="area">m² Area</span>
      </div>
      <div class="bc">
        <div class="bc-ico"><i class="fas fa-couch"></i></div>
        <span class="bc-v" data-i18n="{{ $property->furnished ? 'yes' : 'no' }}">
          {{ $property->furnished ? 'Yes' : 'No' }}
        </span>
        <span class="bc-l" data-i18n="furnished">Furnished</span>
      </div>
    </div>
  </div>

  {{-- Description --}}
  <div class="card">
    <div class="cp">
      <div class="sh">
        <div class="sh-icon"><i class="fas fa-align-left"></i></div>
        <span class="sh-title" data-i18n="aboutProp">About This Property</span>
      </div>
      <p
        class="desc-body"
        id="dyn-desc"
        data-en="{{ $property->description['en'] ?? 'No description provided.' }}"
        data-ku="{{ $property->description['ku'] ?? $property->description['en'] ?? 'زانیاری نەنووسراوە.' }}"
        data-ar="{{ $property->description['ar'] ?? $property->description['en'] ?? 'لم يتم تقديم وصف.' }}"
      >{{ $property->description['en'] ?? 'No description provided.' }}</p>
    </div>
  </div>

  {{-- Specifications --}}
  <div class="card">
    <div class="cp">
      <div class="sh">
        <div class="sh-icon"><i class="fas fa-list-check"></i></div>
        <span class="sh-title" data-i18n="specs">Specifications</span>
      </div>
      <div class="specs-grid">
        @php
          $specs = [
            ['key'=>'propType',    'label'=>'Property Type', 'val'=>ucfirst($property->type['category'] ?? 'N/A'),       'icon'=>'fa-home'],
            ['key'=>'yearBuilt',   'label'=>'Year Built',    'val'=>$property->year_built ?? 'N/A',                      'icon'=>'fa-calendar-check'],
            ['key'=>'floorNum',    'label'=>'Floor',         'val'=>$property->floor_number ?? 'N/A',                    'icon'=>'fa-layer-group'],
            ['key'=>'electricity', 'label'=>'Electricity',   'val'=>$property->electricity ? 'Available' : 'N/A',        'icon'=>'fa-bolt'],
            ['key'=>'water',       'label'=>'Water Supply',  'val'=>$property->water ? 'Available' : 'N/A',              'icon'=>'fa-droplet'],
            ['key'=>'internet',    'label'=>'Internet',      'val'=>$property->internet ? 'Fiber Optic' : 'N/A',         'icon'=>'fa-wifi'],
          ];
        @endphp
        @foreach($specs as $s)
        <div class="spec-row">
          <span class="spec-key">
            <i class="fas {{ $s['icon'] }}"></i>
            <span data-i18n="{{ $s['key'] }}">{{ $s['label'] }}</span>
          </span>
          <span class="spec-val">
            <span data-i18n-val="{{ strtolower(str_replace(' ','_',$s['val'])) }}">{{ $s['val'] }}</span>
          </span>
        </div>
        @endforeach
      </div>
    </div>
  </div>

  {{-- Amenities --}}
  @if(!empty($property->features) || !empty($property->amenities))
  <div class="card">
    <div class="cp">
      <div class="sh">
        <div class="sh-icon"><i class="fas fa-sparkles"></i></div>
        <span class="sh-title" data-i18n="amenities">Amenities & Features</span>
      </div>
      <div class="amenity-wrap">
        @foreach(array_merge($property->features ?? [], $property->amenities ?? []) as $item)
        <span class="amenity-tag">
          <i class="fas fa-check"></i>
          <span data-i18n-item="{{ strtolower($item) }}">{{ ucfirst($item) }}</span>
        </span>
        @endforeach
      </div>
    </div>
  </div>
  @endif

  {{-- Map --}}
  <div class="card" style="overflow:hidden;">
    <div class="cp" style="padding-bottom:0;">
      <div class="sh">
        <div class="sh-icon"><i class="fas fa-map-location-dot"></i></div>
        <span class="sh-title" data-i18n="location">Location</span>
      </div>
    </div>
    <div class="map-container"><div id="prop-map"></div></div>
  </div>

  {{-- Report --}}
  <div class="card report-card">
    <div class="cp">
      <div class="sh" style="border-color:#fecaca;">
        <div class="sh-icon" style="background:#fee2e2;border-color:#fca5a5;color:#dc2626;">
          <i class="fas fa-triangle-exclamation"></i>
        </div>
        <span class="sh-title" style="color:#b91c1c;" data-i18n="reportTitle">Report This Listing</span>
      </div>
      <form method="POST" action="{{ route('report.store') }}">
        @csrf
        <input type="hidden" name="property_id" value="{{ $property->id }}"/>
        <textarea
          name="report"
          class="report-ta"
          rows="3"
          data-placeholder-i18n="reportPh"
          placeholder="Describe the issue with this listing…"
          required
        ></textarea>
        <button type="submit" class="btn-report">
          <i class="fas fa-flag"></i>
          <span data-i18n="submitReport">Submit Report</span>
        </button>
      </form>
      @if(session('success'))
        <div class="success-msg"><i class="fas fa-circle-check"></i>{{ session('success') }}</div>
      @endif
    </div>
  </div>

</div>{{-- end main col --}}

<!-- ─── SIDEBAR ─── -->
<div class="sidebar">

  @php
    $owner    = $property->owner ?? null;
    $canOwner = false;
    $ownerPhone = null;
    if ($owner) {
      $oc = get_class($owner);
      $canOwner   = in_array($oc, ['App\\Models\\Agent','App\\Models\\RealEstateOffice']);
      $ownerPhone = $owner->phone ?? $owner->phone_number ?? null;
      $isOffice   = $oc === 'App\\Models\\RealEstateOffice';
    }
  @endphp

  {{-- Agent / Office Card --}}
  @if($canOwner && $owner)
  <div class="card agent-card">
    <div class="agent-inner">
      <div class="ag-av-wrap">
        <img
          src="{{ $owner->profile_image ?? $owner->image ?? 'https://ui-avatars.com/api/?name='.urlencode($owner->agent_name ?? $owner->name ?? 'Agent').'&background=303b97&color=D4A853&size=80&bold=true' }}"
          alt="{{ $owner->agent_name ?? $owner->name ?? 'Agent' }}"
          onerror="this.src='https://ui-avatars.com/api/?name=Agent&background=303b97&color=D4A853&size=80&bold=true'"
        />
        <div class="ag-badge"><i class="fas fa-check" style="font-size:7px;"></i></div>
      </div>
      <div class="ag-name">{{ $owner->agent_name ?? $owner->name ?? 'Agent' }}</div>
      <div class="ag-role">
        <span data-i18n="{{ $isOffice ? 'office' : 'agent' }}">
          {{ $isOffice ? 'Real Estate Office' : 'Verified Agent' }}
        </span>
      </div>
      <div class="ag-stars">
        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
        <i class="fas fa-star"></i><i class="fas fa-star-half-stroke"></i>
        <span>4.8 (124)</span>
      </div>
      <div class="ag-btns">
        @if($ownerPhone)
        <a href="tel:{{ $ownerPhone }}" class="ag-btn btn-call">
          <i class="fas fa-phone"></i>
          <span data-i18n="call">Call</span>
        </a>
        @endif
        {{-- View agent/office → redirect to download page --}}
        <a href="https://dreammulk.com/download" class="ag-btn btn-view">
          <i class="fas fa-user"></i>
          <span data-i18n="viewAgent">View Agent</span>
        </a>
      </div>
      <div class="ag-divider"></div>
      <div class="ag-stats">
        <div class="ag-stat">
          <span class="ag-stat-n">47</span>
          <span class="ag-stat-l" data-i18n="listings">Listings</span>
        </div>
        <div class="ag-stat">
          <span class="ag-stat-n">5yr</span>
          <span class="ag-stat-l" data-i18n="experience">Experience</span>
        </div>
        <div class="ag-stat">
          <span class="ag-stat-n">98%</span>
          <span class="ag-stat-l" data-i18n="response">Response</span>
        </div>
      </div>
    </div>
  </div>
  @endif

  {{-- Contact Form --}}
  <div class="card">
    <div class="contact-inner">
      <div class="card-title" data-i18n="inquiryTitle">Send Inquiry</div>
      <div class="card-sub" data-i18n="inquirySub">Interested in this property? We'll connect you directly.</div>
      <form action="/submit-contact" method="POST">
        @csrf
        <div class="fg">
          <label data-i18n="fullName">Full Name</label>
          <input type="text" name="name" data-placeholder-i18n="fullNamePh" placeholder="Your full name" required/>
        </div>
        <div class="fg">
          <label data-i18n="phone">Phone</label>
          <input type="tel" name="phone-number" placeholder="07XX XXX XXXX" required style="direction:ltr;text-align:start;"/>
        </div>
        <div class="fg">
          <label data-i18n="message">Message</label>
          <textarea name="message" rows="4" id="inquiry-msg" required>I am interested in this property. Please contact me.</textarea>
        </div>
        <button type="submit" class="send-btn">
          <i class="fas fa-paper-plane"></i>
          <span data-i18n="sendInquiry">Send Inquiry</span>
        </button>
      </form>
      <div class="resp-note">
        <i class="fas fa-clock"></i>
        <span data-i18n="respNote">Typically responds within 24 hours</span>
      </div>
    </div>
  </div>

  {{-- Quick Info --}}
  <div class="card">
    <div class="qi-inner">
      <div class="sh" style="margin-bottom:16px;padding-bottom:14px;">
        <div class="sh-icon"><i class="fas fa-circle-info"></i></div>
        <span class="sh-title" data-i18n="quickInfo">Quick Info</span>
      </div>
      @php
        $qi = [
          ['icon'=>'fa-hashtag',    'key'=>'propId',  'label'=>'Property ID', 'val'=>'#'.str_pad($property->id,5,'0',STR_PAD_LEFT)],
          ['icon'=>'fa-circle-dot', 'key'=>'status',  'label'=>'Status',      'val'=>ucfirst($property->status ?? 'Active')],
          ['icon'=>'fa-calendar',   'key'=>'listed',  'label'=>'Listed',      'val'=>optional($property->created_at)->diffForHumans() ?? 'Recently'],
          ['icon'=>'fa-eye',        'key'=>'views',   'label'=>'Views',       'val'=>number_format($property->views ?? 0)],
        ];
      @endphp
      @foreach($qi as $q)
      <div class="qi-row">
        <span class="qi-key">
          <i class="fas {{ $q['icon'] }}"></i>
          <span data-i18n="{{ $q['key'] }}">{{ $q['label'] }}</span>
        </span>
        <span class="qi-val" style="direction:ltr;">
          @if($q['key']==='status')
            <span data-i18n-val="{{ strtolower($q['val']) }}">{{ $q['val'] }}</span>
          @else
            {{ $q['val'] }}
          @endif
        </span>
      </div>
      @endforeach
    </div>
  </div>

  {{-- Share --}}
  <div class="card">
    <div class="share-inner">
      <span class="share-lbl" data-i18n="share">Share</span>
      {{-- Copy link — uses property URL format --}}
      <div class="share-btn" id="copy-link-btn" title="Copy link">
        <i class="fas fa-link"></i>
      </div>
      <a
        class="share-btn"
        href="https://wa.me/?text={{ urlencode(($property->name['en'] ?? 'Property').' — '.url()->current()) }}"
        target="_blank" rel="noopener" title="WhatsApp"
      ><i class="fab fa-whatsapp"></i></a>
      <a
        class="share-btn"
        href="https://t.me/share/url?url={{ urlencode(url()->current()) }}&text={{ urlencode($property->name['en'] ?? 'Property') }}"
        target="_blank" rel="noopener" title="Telegram"
      ><i class="fab fa-telegram"></i></a>
      <a
        class="share-btn"
        href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
        target="_blank" rel="noopener" title="Facebook"
      ><i class="fab fa-facebook-f"></i></a>
    </div>
  </div>

</div>{{-- end sidebar --}}
</div>{{-- end grid --}}
</div>{{-- end page-wrap --}}

<!-- Toast notification -->
<div class="toast" id="toast">
  <i class="fas fa-check-circle"></i>
  <span id="toast-msg">Link copied!</span>
</div>

<!-- ══════════════════════════════════════
     SCRIPTS
══════════════════════════════════════ -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
/* ═══════════════════════════════════════
   PROPERTY URL FORMAT
   https://dreammulk.com/PropertyDetail/prop_YYYY_MM_DD_NNNNN
   Built from: id, created_at
═══════════════════════════════════════ */
(function() {
  const rawId  = '{{ $property->id }}';
  const rawDate = '{{ optional($property->created_at)->format("Y_m_d") ?? date("Y_m_d") }}';
  const slug   = 'prop_' + rawDate + '_' + String(rawId).padStart(5, '0');
  window.PROPERTY_SHARE_URL = 'https://dreammulk.com/PropertyDetail/' + slug;
})();


/* ═══════════════════════════════════════
   I18N
═══════════════════════════════════════ */
const I18N = {
  key: 'dm_lang',
  T: {
    ku: {
      dir:'rtl',
      eyebrow:'موڵکی دریم موڵک', verified:'پشتڕاستکراوە', featured:'تایبەت',
      rent:'کرێ', sell:'فرۆشتن', perMonth:'مانگانە', totalPrice:'نرخی گشتی',
      beds:'نووستن', baths:'گەرماو', area:'ڕووبەر', furnished:'مۆبیلیات',
      yes:'بەڵێ', no:'نەخێر',
      aboutProp:'دەربارەی ئەم خانوو', specs:'تایبەتمەندییەکان', amenities:'خزمەتگوزارییەکان',
      location:'ناونیشان', reportTitle:'ڕاپۆرتکردن', submitReport:'ناردنی ڕاپۆرت',
      agent:'ئەجێنتی پشتڕاستکراوە', office:'ئۆفیسی خانووبەرە',
      call:'پەیوەندی', viewAgent:'بینینی ئەجێنت',
      listings:'خانووەکان', experience:'ئەزموون', response:'وەڵامدانەوە',
      inquiryTitle:'ناردنی پرسیار', inquirySub:'ئارەزووی ئەم خانووت هەیە؟ نامەیەک بنێرە.',
      fullName:'ناوی تەواو', phone:'ژمارەی تەلەفۆن', message:'نامە', sendInquiry:'ناردن',
      respNote:'زۆرجار لە ٢٤ کاتژمێردا وەڵام دەداتەوە',
      quickInfo:'زانیاری خێرا', propId:'ژمارەی خانوو', status:'دۆخ', listed:'کاتی دانان', views:'بینینەکان',
      share:'هاوبەشکردن', photos:'وێنە',
      reportPh:'کێشەی ئەم خانوو لێرە بنووسە...', fullNamePh:'ناوی خۆت بنووسە',
      msgDefault:'سڵاو، ئارەزووی ئەم خانووەم هەیە. تکایە پەیوەندیم پێوە بکە.',
      linkCopied:'لینک کۆپی کرا',
      propType:'جۆری خانوو', yearBuilt:'ساڵی درووستکردن', floorNum:'نهۆم',
      electricity:'کارەبا', water:'ئاو', internet:'ئینتەرنێت',
      val_available:'بەردەستە', val_n_a:'نەزانراو', val_fiber_optic:'فایبەر ئۆپتیک',
      val_active:'چالاک', val_villa:'ڤیلا', val_apartment:'شوقە', val_house:'خانوو',
    },
    en: {
      dir:'ltr',
      eyebrow:'Dream Mulk Property', verified:'Verified', featured:'Featured',
      rent:'For Rent', sell:'For Sale', perMonth:'per month', totalPrice:'total price',
      beds:'Bedrooms', baths:'Bathrooms', area:'m² Area', furnished:'Furnished',
      yes:'Yes', no:'No',
      aboutProp:'About This Property', specs:'Specifications', amenities:'Amenities & Features',
      location:'Location', reportTitle:'Report This Listing', submitReport:'Submit Report',
      agent:'Verified Agent', office:'Real Estate Office',
      call:'Call', viewAgent:'View Agent',
      listings:'Listings', experience:'Experience', response:'Response',
      inquiryTitle:'Send Inquiry', inquirySub:"Interested in this property? We'll connect you directly.",
      fullName:'Full Name', phone:'Phone Number', message:'Message', sendInquiry:'Send Inquiry',
      respNote:'Typically responds within 24 hours',
      quickInfo:'Quick Info', propId:'Property ID', status:'Status', listed:'Listed', views:'Views',
      share:'Share', photos:'Photos',
      reportPh:'Describe the issue with this listing...', fullNamePh:'Your full name',
      msgDefault:'I am interested in this property. Please contact me.',
      linkCopied:'Link copied to clipboard!',
      propType:'Property Type', yearBuilt:'Year Built', floorNum:'Floor',
      electricity:'Electricity', water:'Water Supply', internet:'Internet',
      val_available:'Available', val_n_a:'N/A', val_fiber_optic:'Fiber Optic',
      val_active:'Active', val_villa:'Villa', val_apartment:'Apartment', val_house:'House',
    },
    ar: {
      dir:'rtl',
      eyebrow:'عقار دريم مُلك', verified:'موثق', featured:'مميز',
      rent:'للإيجار', sell:'للبيع', perMonth:'شهرياً', totalPrice:'السعر الإجمالي',
      beds:'غرف النوم', baths:'الحمامات', area:'المساحة', furnished:'مفروش',
      yes:'نعم', no:'لا',
      aboutProp:'عن هذا العقار', specs:'المواصفات', amenities:'المميزات والمرافق',
      location:'الموقع', reportTitle:'الإبلاغ عن الإعلان', submitReport:'إرسال البلاغ',
      agent:'وكيل موثق', office:'مكتب عقارات',
      call:'اتصال', viewAgent:'عرض الوكيل',
      listings:'إعلانات', experience:'خبرة', response:'استجابة',
      inquiryTitle:'إرسال استفسار', inquirySub:'مهتم بهذا العقار؟ أرسل رسالة للتواصل.',
      fullName:'الاسم الكامل', phone:'رقم الهاتف', message:'رسالة', sendInquiry:'إرسال',
      respNote:'عادة ما يرد خلال ٢٤ ساعة',
      quickInfo:'معلومات سريعة', propId:'رقم العقار', status:'الحالة', listed:'تاريخ النشر', views:'المشاهدات',
      share:'مشاركة', photos:'صورة',
      reportPh:'صف المشكلة في هذا الإعلان...', fullNamePh:'اسمك الكامل',
      msgDefault:'مرحباً، أنا مهتم بهذا العقار. يرجى التواصل معي.',
      linkCopied:'تم نسخ الرابط!',
      propType:'نوع العقار', yearBuilt:'سنة البناء', floorNum:'الطابق',
      electricity:'الكهرباء', water:'الماء', internet:'الإنترنت',
      val_available:'متوفر', val_n_a:'غير متوفر', val_fiber_optic:'ألياف ضوئية',
      val_active:'نشط', val_villa:'فيلا', val_apartment:'شقة', val_house:'بيت',
    }
  },

  init() {
    const saved = localStorage.getItem(this.key) || 'ku';
    this.apply(saved);
    document.querySelectorAll('.lang-btn').forEach(b =>
      b.addEventListener('click', () => this.apply(b.dataset.lang))
    );
  },

  apply(lang) {
    if (!this.T[lang]) return;
    localStorage.setItem(this.key, lang);
    const T = this.T[lang];

    document.body.dir = T.dir;
    document.documentElement.lang = lang;
    document.body.classList.remove('lang-ku','lang-en','lang-ar','rtl');
    document.body.classList.add('lang-' + lang);
    if (T.dir === 'rtl') document.body.classList.add('rtl');

    document.querySelectorAll('.lang-btn').forEach(b =>
      b.classList.toggle('active', b.dataset.lang === lang)
    );

    document.querySelectorAll('[data-i18n]').forEach(el => {
      const k = el.getAttribute('data-i18n');
      if (T[k] !== undefined) el.textContent = T[k];
    });

    document.querySelectorAll('[data-i18n-val]').forEach(el => {
      const raw = el.getAttribute('data-i18n-val');
      const k = 'val_' + raw.replace(/\s+/g,'_').replace(/[^a-z0-9_]/gi,'').toLowerCase();
      if (T[k] !== undefined) el.textContent = T[k];
    });

    document.querySelectorAll('[data-placeholder-i18n]').forEach(el => {
      const k = el.getAttribute('data-placeholder-i18n');
      if (T[k] !== undefined) el.placeholder = T[k];
    });

    /* Dynamic multilingual fields */
    const fields = [
      { id: 'dyn-title', type: 'text' },
      { id: 'dyn-desc',  type: 'text' },
      { id: 'dyn-addr',  type: 'span' },
    ];
    fields.forEach(({ id, type }) => {
      const el = document.getElementById(id);
      if (!el) return;
      const v = el.getAttribute('data-' + lang);
      if (!v || !v.trim()) return;
      if (type === 'span') el.querySelector('span').textContent = v;
      else el.textContent = v;
    });

    /* Default inquiry message */
    const msgArea = document.getElementById('inquiry-msg');
    if (msgArea) {
      const defaults = [this.T.en.msgDefault, this.T.ku.msgDefault, this.T.ar.msgDefault];
      if (defaults.includes(msgArea.value)) msgArea.value = T.msgDefault;
    }

    /* Photo count label */
    const tp = document.getElementById('total-photos');
    if (tp) {
      const n = document.querySelectorAll('.swiper-hero .swiper-slide').length;
      tp.textContent = n + ' ' + T.photos;
    }
  }
};
I18N.init();


/* ═══════════════════════════════════════
   TOAST
═══════════════════════════════════════ */
function showToast(msg) {
  const t = document.getElementById('toast');
  document.getElementById('toast-msg').textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2800);
}


/* ═══════════════════════════════════════
   COPY LINK — uses dreammulk.com/PropertyDetail/prop_YYYY_MM_DD_NNNNN
═══════════════════════════════════════ */
function copyPropertyLink() {
  const url = window.PROPERTY_SHARE_URL;
  navigator.clipboard.writeText(url).then(() => {
    const lang = localStorage.getItem('dm_lang') || 'ku';
    const msg  = I18N.T[lang]?.linkCopied ?? 'Link copied!';
    showToast(msg);
  }).catch(() => {
    /* fallback for older browsers */
    const ta = document.createElement('textarea');
    ta.value = window.PROPERTY_SHARE_URL;
    ta.style.position = 'fixed'; ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.focus(); ta.select();
    document.execCommand('copy');
    document.body.removeChild(ta);
    const lang = localStorage.getItem('dm_lang') || 'ku';
    showToast(I18N.T[lang]?.linkCopied ?? 'Link copied!');
  });
}

document.getElementById('copy-link-btn').addEventListener('click', copyPropertyLink);


/* ═══════════════════════════════════════
   HERO ACTIONS
═══════════════════════════════════════ */
const favBtn = document.getElementById('fav-btn');
favBtn.addEventListener('click', function() {
  const faved = this.classList.toggle('faved');
  this.querySelector('i').className = faved ? 'fas fa-heart' : 'far fa-heart';
});

document.getElementById('share-hero-btn').addEventListener('click', function() {
  const url  = window.PROPERTY_SHARE_URL;
  const lang = localStorage.getItem('dm_lang') || 'ku';
  if (navigator.share) {
    navigator.share({ title: document.title, url });
  } else {
    navigator.clipboard.writeText(url).then(() => {
      this.classList.add('copied');
      this.querySelector('i').className = 'fas fa-check';
      showToast(I18N.T[lang]?.linkCopied ?? 'Link copied!');
      setTimeout(() => {
        this.classList.remove('copied');
        this.querySelector('i').className = 'fas fa-link';
      }, 2500);
    });
  }
});


/* ═══════════════════════════════════════
   SWIPER
═══════════════════════════════════════ */
const thumbsSw = new Swiper('.swiper-thumbs', {
  spaceBetween: 8,
  slidesPerView: 'auto',
  freeMode: true,
  watchSlidesProgress: true,
});

new Swiper('.swiper-hero', {
  loop: true,
  effect: 'fade',
  fadeEffect: { crossFade: true },
  speed: 900,
  autoplay: { delay: 5000, disableOnInteraction: true },
  navigation: {
    nextEl: '.swiper-button-next',
    prevEl: '.swiper-button-prev',
  },
  pagination: {
    el: '.swiper-pagination',
    clickable: true,
  },
  thumbs: { swiper: thumbsSw },
  keyboard: { enabled: true },
});


/* ═══════════════════════════════════════
   LEAFLET MAP
═══════════════════════════════════════ */
@php
  $lat = 36.1911;
  $lng = 44.0091;
  if (!empty($property->locations) && is_array($property->locations) && isset($property->locations[0])) {
    $lat = $property->locations[0]['lat'] ?? $lat;
    $lng = $property->locations[0]['lng'] ?? $lng;
  }
@endphp
(function() {
  const LAT = {{ $lat }};
  const LNG = {{ $lng }};

  const map = L.map('prop-map', {
    center: [LAT, LNG],
    zoom: 15,
    zoomControl: false,
    scrollWheelZoom: false,
  });

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© <a href="https://openstreetmap.org/copyright">OpenStreetMap</a>',
  }).addTo(map);

  L.control.zoom({ position: 'bottomright' }).addTo(map);

  const icon = L.divIcon({
    className: '',
    html: `<div style="
      width:42px;height:42px;border-radius:50% 50% 50% 0;
      background:linear-gradient(135deg,#303b97,#232c78);
      transform:rotate(-45deg);
      border:3px solid #fff;
      box-shadow:0 6px 20px rgba(15,18,34,.4);
      display:flex;align-items:center;justify-content:center;">
      <i class='fas fa-building' style='transform:rotate(45deg);color:#D4A853;font-size:14px;'></i>
    </div>`,
    iconSize: [42, 42],
    iconAnchor: [21, 42],
    popupAnchor: [0, -48],
  });

  L.marker([LAT, LNG], { icon })
    .addTo(map)
    .bindPopup(`
      <div style="font-family:'Outfit',sans-serif;min-width:190px;" dir="ltr">
        <div style="font-family:'Cormorant Garamond',serif;font-weight:600;font-size:16px;color:#0f1422;margin-bottom:4px;">
          {{ $property->name['en'] ?? 'Property' }}
        </div>
        <div style="font-size:12px;color:#6b7280;margin-bottom:8px;display:flex;align-items:center;gap:5px;">
          <i class='fas fa-location-dot' style='color:#303b97;'></i>
          {{ $property->address_details['city']['en'] ?? 'Kurdistan, Iraq' }}
        </div>
        <div style="font-family:'Cormorant Garamond',serif;font-size:20px;font-weight:600;color:#D4A853;">
          ${{ number_format($property->price['usd'] ?? 0) }}
        </div>
      </div>
    `, { maxWidth: 240 })
    .openPopup();

  map.on('click', () => map.scrollWheelZoom.enable());
  map.on('mouseout', () => map.scrollWheelZoom.disable());
})();
</script>
</body>
</html>
