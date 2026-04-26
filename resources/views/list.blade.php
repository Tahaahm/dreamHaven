<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Dream Mulk — Properties</title>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.imagesloaded/4.1.4/imagesloaded.pkgd.min.js"></script>
<script src="{{ asset('assets/vendor/isotope-layout/isotope.pkgd.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@studio-freight/lenis@1.0.42/dist/lenis.min.js"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600;700&family=Noto+Naskh+Arabic:wght@400;500;600;700&family=Noto+Sans+Arabic:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
/* ════════════════════════════════════════════════════
   DREAM MULK · PROPERTIES INDEX
   Brand: #303b97 primary · #D4A853 / #ECC97A / #B8882E gold
   Aesthetic: Restrained Luxury — clean, open, editorial
   Display: Cormorant Garamond · UI: Outfit
════════════════════════════════════════════════════ */
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; outline: none; }
html { scroll-behavior: auto; }

:root {
  /* ── Brand exact Flutter values ── */
  --P:        #303b97;   /* primary */
  --PD:       #232c78;   /* primary dark */
  --PL:       #3d49b5;   /* primary light */
  --PBG:      #eef0fb;   /* primary tint bg */
  --PBorder:  rgba(48,59,151,.15);

  /* Gold Flutter: _gold / _goldL / _goldD */
  --G:        #D4A853;
  --GL:       #ECC97A;
  --GD:       #B8882E;
  --GPale:    #fdf4e3;

  /* Neutrals */
  --ink:      #0f1422;
  --ink-2:    #374151;
  --ink-3:    #6b7280;
  --ink-4:    #9ca3af;
  --cream:    #f8f7f4;
  --warm:     #f1ede6;
  --stone:    #e4ddd2;
  --border:   #e5e0d8;
  --white:    #ffffff;

  /* Fonts */
  --f-disp:   'Cormorant Garamond', Georgia, serif;
  --f-ui:     'Outfit', system-ui, sans-serif;
  --f-ar:     'Noto Naskh Arabic', serif;
  --f-ar-ui:  'Noto Sans Arabic', sans-serif;

  --ease:     cubic-bezier(0.22, 1, 0.36, 1);
  --sw:       310px;   /* sidebar width */
}

body {
  font-family: var(--f-ui);
  background: var(--cream);
  color: var(--ink);
  overflow-x: hidden;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}
a { text-decoration: none; color: inherit; }
img { display: block; }

/* RTL */
body.lang-ku, body.lang-ar { font-family: var(--f-ar-ui); direction: rtl; }
body.lang-ku *:not(i):not([class*="fa"]),
body.lang-ar *:not(i):not([class*="fa"]) { font-family: var(--f-ar-ui); letter-spacing: 0 !important; }
body.lang-ku .pg-title, body.lang-ar .pg-title,
body.lang-ku .cb-title, body.lang-ar .cb-title { font-family: var(--f-ar) !important; }

body.lang-ku .sb, body.lang-ar .sb { left: auto; right: 0; border-right: none; border-left: 1px solid var(--border); }
body.lang-ku .main, body.lang-ar .main { margin-left: 0; margin-right: var(--sw); }
body.lang-ku .sel-wrap::after, body.lang-ar .sel-wrap::after { right: auto; left: 14px; }
body.lang-ku .cb-loc i, body.lang-ar .cb-loc i { margin-left: 4px; margin-right: 0; }
body.lang-ku .cta-arr, body.lang-ar .cta-arr { transform: scaleX(-1); }

@media(max-width:1024px){
  body.lang-ku .sb, body.lang-ar .sb { transform: translateX(100%); }
  body.lang-ku .sb.open, body.lang-ar .sb.open { transform: translateX(0); }
  body.lang-ku .main, body.lang-ar .main { margin-right: 0; }
}

/* ══════════════════════════════════════
   LANG SWITCHER
══════════════════════════════════════ */
.lang-sw {
  display: flex; gap: 3px;
  background: var(--white); border: 1px solid var(--border);
  border-radius: 100px; padding: 3px;
  box-shadow: 0 1px 4px rgba(0,0,0,.05);
}
.lang-btn {
  height: 30px; padding: 0 13px; border-radius: 100px; border: none;
  background: transparent; color: var(--ink-3);
  font-size: 11.5px; font-weight: 700; cursor: pointer;
  transition: all .25s var(--ease);
  font-family: var(--f-ui) !important; letter-spacing: .3px;
}
.lang-btn.active { background: var(--P); color: #fff; }
.lang-btn:hover:not(.active) { background: var(--PBG); color: var(--P); }

/* ══════════════════════════════════════
   LAYOUT
══════════════════════════════════════ */
.wrap { display: flex; min-height: 100vh; padding-top: 80px; }

/* ══════════════════════════════════════
   SIDEBAR
══════════════════════════════════════ */
.sb {
  width: var(--sw); position: fixed; top: 80px; left: 0; bottom: 0;
  background: var(--white); border-right: 1px solid var(--border);
  z-index: 50; overflow-y: auto; overflow-x: hidden;
  transition: transform .4s var(--ease); scrollbar-width: none;
}
.sb::-webkit-scrollbar { display: none; }

/* Gold top accent line */
.sb-accent {
  height: 3px;
  background: linear-gradient(90deg, var(--P) 0%, var(--G) 60%, var(--GL) 100%);
}
body.lang-ku .sb-accent, body.lang-ar .sb-accent {
  background: linear-gradient(270deg, var(--P) 0%, var(--G) 60%, var(--GL) 100%);
}

.sb-hd {
  padding: 24px 22px 18px;
  border-bottom: 1px solid var(--border);
  display: flex; align-items: center; gap: 13px;
}
.sb-hd-ico {
  width: 40px; height: 40px; border-radius: 11px;
  background: var(--PBG); border: 1px solid var(--PBorder);
  display: flex; align-items: center; justify-content: center;
  color: var(--P); font-size: 14px; flex-shrink: 0;
}
.sb-hd-title { font-size: 16px; font-weight: 700; color: var(--ink); }
.sb-hd-sub { font-size: 11.5px; color: var(--ink-3); margin-top: 3px; }

.sb-body { padding: 22px; }

.fg { margin-bottom: 18px; }
.fg-lbl {
  display: block; font-size: 10px; font-weight: 700;
  letter-spacing: 1.5px; text-transform: uppercase;
  color: var(--P); margin-bottom: 7px;
}
body.lang-ku .fg-lbl, body.lang-ar .fg-lbl { letter-spacing: 0; font-size: 12px; text-transform: none; }

.fc {
  width: 100%; padding: 12px 14px;
  font-family: var(--f-ui); font-size: 13.5px; color: var(--ink);
  background: var(--cream); border: 1px solid var(--border);
  border-radius: 11px; transition: all .25s var(--ease);
  appearance: none; -webkit-appearance: none;
}
body.lang-ku .fc, body.lang-ar .fc { font-family: var(--f-ar-ui); font-size: 14px; }
.fc:focus { border-color: var(--P); background: var(--white); box-shadow: 0 0 0 3px rgba(48,59,151,.08); }
.fc::placeholder { color: var(--ink-4); }

.sel-wrap { position: relative; }
.sel-wrap::after {
  content: '\f107'; font-family: 'Font Awesome 6 Free'; font-weight: 900;
  position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
  color: var(--P); font-size: 11px; pointer-events: none;
}
.sel-wrap select { padding-right: 34px; cursor: pointer; }

.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.price-hint { font-size: 11px; color: var(--ink-4); margin-top: 7px; display: flex; align-items: center; gap: 5px; }
.price-hint i { color: var(--G); font-size: 10px; }

.sb-divider { height: 1px; background: var(--border); margin: 18px 0; }

/* Active filter tags */
.active-filters { display: flex; flex-wrap: wrap; gap: 7px; margin-bottom: 14px; min-height: 0; }
.af-tag {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 5px 12px; background: var(--PBG);
  border: 1px solid var(--PBorder); border-radius: 100px;
  font-size: 11.5px; font-weight: 600; color: var(--PD);
}
.af-tag button { background: none; border: none; color: var(--PD); cursor: pointer; font-size: 10px; opacity: .55; padding: 0; }
.af-tag button:hover { opacity: 1; }

.btn-apply {
  width: 100%; padding: 13px; border-radius: 12px; border: none;
  background: var(--P); color: #fff;
  font-size: 13.5px; font-weight: 700; font-family: var(--f-ui);
  cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;
  transition: all .3s var(--ease); margin-bottom: 10px;
  box-shadow: 0 4px 16px rgba(48,59,151,.22);
  letter-spacing: .2px;
}
body.lang-ku .btn-apply, body.lang-ar .btn-apply { font-family: var(--f-ar-ui); font-size: 14.5px; }
.btn-apply:hover { background: var(--PD); transform: translateY(-2px); box-shadow: 0 8px 22px rgba(48,59,151,.32); }

.btn-reset {
  width: 100%; padding: 12px; border-radius: 12px;
  background: var(--white); border: 1px solid var(--border);
  font-size: 13.5px; font-weight: 600; font-family: var(--f-ui); color: var(--ink-3);
  cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 7px;
  transition: all .25s var(--ease);
}
body.lang-ku .btn-reset, body.lang-ar .btn-reset { font-family: var(--f-ar-ui); font-size: 14.5px; }
.btn-reset:hover { border-color: var(--P); color: var(--P); background: var(--PBG); }

/* ══════════════════════════════════════
   MAIN CONTENT
══════════════════════════════════════ */
.main { flex: 1; margin-left: var(--sw); padding: 36px 36px 80px; min-width: 0; }

/* Page header */
.pg-head {
  display: flex; align-items: flex-start; justify-content: space-between;
  flex-wrap: wrap; gap: 16px; margin-bottom: 36px;
}
.pg-tag {
  font-size: 10.5px; font-weight: 700; letter-spacing: 3px; text-transform: uppercase;
  color: var(--G); display: flex; align-items: center; gap: 10px; margin-bottom: 8px;
}
.pg-tag::before { content: ''; width: 26px; height: 1.5px; background: var(--G); border-radius: 2px; }
body.lang-ku .pg-tag::before, body.lang-ar .pg-tag::before { display: none; }
body.lang-ku .pg-tag, body.lang-ar .pg-tag { letter-spacing: 0; font-size: 12px; }

.pg-title {
  font-family: var(--f-disp);
  font-size: clamp(28px, 3.5vw, 44px);
  font-weight: 500; color: var(--ink); line-height: 1.1;
  letter-spacing: -.3px;
}
.pg-title em { font-style: italic; color: var(--P); font-weight: 400; }

.pg-head-right { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

.count-pill {
  display: flex; align-items: center; gap: 9px;
  background: var(--white); border: 1px solid var(--border);
  border-radius: 100px; padding: 9px 20px;
  font-size: 13px; color: var(--ink-3); font-weight: 500;
  box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.count-pill strong { color: var(--P); font-weight: 700; font-size: 15px; }
.count-dot {
  width: 7px; height: 7px; border-radius: 50%;
  background: var(--G); flex-shrink: 0;
  box-shadow: 0 0 0 3px rgba(212,168,83,.2);
}

/* ══════════════════════════════════════
   PROPERTY GRID
══════════════════════════════════════ */
.grid { display: block; width: 100%; min-height: 200px; margin: 0 -10px; position: relative; }
.grid::after { content: ''; display: table; clear: both; }

.pc { width: 33.333%; padding: 0 10px; margin-bottom: 24px; float: left; box-sizing: border-box; }

/* ══════════════════════════════════════
   PROPERTY CARD
══════════════════════════════════════ */
.card {
  background: var(--white);
  border-radius: 20px;
  border: 1px solid var(--border);
  overflow: hidden;
  display: flex; flex-direction: column;
  transition: transform .38s var(--ease), box-shadow .38s var(--ease), border-color .38s;
  position: relative; cursor: pointer; height: 100%;
  box-shadow: 0 2px 12px rgba(15,20,34,.04);
  text-decoration: none; color: inherit;
}
.card:hover {
  transform: translateY(-8px);
  border-color: var(--PBorder);
  box-shadow: 0 16px 40px rgba(48,59,151,.11);
}

/* Gold bottom reveal */
.card::after {
  content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 3px; z-index: 4;
  background: linear-gradient(90deg, var(--P), var(--G));
  transform: scaleX(0); transform-origin: left;
  transition: transform .45s var(--ease);
}
.card:hover::after { transform: scaleX(1); }

/* Image section */
.ci { position: relative; height: 248px; overflow: hidden; flex-shrink: 0; }
.ci-bg {
  position: absolute; inset: 0;
  background-size: cover; background-position: center;
  transition: transform .8s var(--ease);
}
.card:hover .ci-bg { transform: scale(1.06); }
.ci::after {
  content: ''; position: absolute; inset: 0;
  background: linear-gradient(to top, rgba(15,20,34,.88) 0%, rgba(15,20,34,.15) 40%, transparent 100%);
  z-index: 1;
}

/* Badges */
.ci-badges {
  position: absolute; top: 14px; left: 14px; right: 14px;
  display: flex; justify-content: space-between; align-items: flex-start; z-index: 3;
}
.badge {
  font-size: 9.5px; font-weight: 700; letter-spacing: 1px;
  text-transform: uppercase; padding: 5px 12px; border-radius: 100px;
  backdrop-filter: blur(10px);
  font-family: var(--f-ui) !important;
}
.badge-type { background: rgba(48,59,151,.85); color: #fff; border: 1px solid rgba(255,255,255,.2); }
.badge-sell { background: rgba(255,255,255,.93); color: var(--PD); }
.badge-rent { background: var(--G); color: var(--ink); }

/* Price overlay */
.ci-price {
  position: absolute; bottom: 14px; left: 14px; right: 14px; z-index: 3;
  display: flex; flex-direction: column;
}
.ci-price-cur {
  font-size: 9.5px; font-weight: 700; letter-spacing: 2px;
  text-transform: uppercase; color: rgba(255,255,255,.6);
  margin-bottom: 2px;
}
.ci-price-num {
  font-family: var(--f-disp);
  font-size: 26px; font-weight: 600; color: #fff; line-height: 1;
  text-shadow: 0 3px 12px rgba(0,0,0,.45);
  direction: ltr; display: inline-block;
}

/* Card body */
.cb { padding: 22px 20px 0; flex: 1; display: flex; flex-direction: column; }
.cb-title {
  font-family: var(--f-disp);
  font-size: 18px; font-weight: 500; color: var(--ink);
  line-height: 1.35; margin-bottom: 8px;
  overflow: hidden; display: -webkit-box;
  -webkit-line-clamp: 1; -webkit-box-orient: vertical;
  transition: color .25s;
}
.card:hover .cb-title { color: var(--P); }

.cb-loc {
  font-size: 12.5px; color: var(--ink-3); font-weight: 500;
  display: flex; align-items: center; gap: 6px; margin-bottom: 18px;
}
.cb-loc i { color: var(--P); font-size: 11px; }

/* Feature pills */
.cb-feats { display: flex; gap: 6px; margin-top: auto; padding-bottom: 18px; }
.feat {
  flex: 1; display: flex; flex-direction: column; align-items: center; gap: 5px;
  padding: 11px 6px; background: var(--cream); border-radius: 11px;
  border: 1px solid var(--border);
  transition: all .25s var(--ease);
}
.card:hover .feat { background: var(--PBG); border-color: var(--PBorder); }
.feat i { font-size: 13px; color: var(--P); }
.feat-v {
  font-family: var(--f-disp);
  font-size: 17px; font-weight: 600; color: var(--ink); line-height: 1;
}
.feat-l {
  font-size: 9.5px; font-weight: 700; letter-spacing: 1px;
  text-transform: uppercase; color: var(--ink-3);
}
body.lang-ku .feat-l, body.lang-ar .feat-l { letter-spacing: 0; font-size: 11px; text-transform: none; }

/* CTA row */
.cta { padding: 0 20px 18px; }
.cta-btn {
  display: flex; align-items: center; justify-content: space-between;
  padding: 11px 16px; border-radius: 11px;
  background: var(--cream); border: 1px solid var(--border);
  font-size: 13.5px; font-weight: 600; color: var(--P);
  transition: all .28s var(--ease);
}
.card:hover .cta-btn {
  background: var(--P); color: #fff; border-color: var(--P);
  box-shadow: 0 6px 18px rgba(48,59,151,.22);
}
.cta-arr {
  width: 28px; height: 28px; border-radius: 8px;
  background: var(--PBG); display: flex; align-items: center;
  justify-content: center; font-size: 11px;
  transition: all .28s var(--ease);
}
.card:hover .cta-arr { background: rgba(255,255,255,.22); }

/* ══════════════════════════════════════
   EMPTY STATE
══════════════════════════════════════ */
.empty-state {
  clear: both; display: none; width: 100%; padding: 72px 40px;
  text-align: center; background: var(--white);
  border-radius: 20px; border: 1.5px dashed var(--border);
  margin-top: 8px;
}
.empty-state.visible { display: block; }
.empty-ic {
  width: 72px; height: 72px; border-radius: 50%; background: var(--PBG);
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 18px; font-size: 26px; color: var(--P);
}
.empty-state h3 {
  font-family: var(--f-disp);
  font-size: 22px; font-weight: 500; color: var(--ink); margin-bottom: 8px;
}
.empty-state p { font-size: 14px; color: var(--ink-3); margin-bottom: 22px; }
.empty-reset {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 11px 26px; background: var(--P); color: #fff;
  border-radius: 100px; font-size: 13.5px; font-weight: 700;
  border: none; cursor: pointer; transition: all .25s; font-family: var(--f-ui);
}
.empty-reset:hover { background: var(--PD); transform: translateY(-2px); }

/* ══════════════════════════════════════
   PAGINATION
══════════════════════════════════════ */
.pgn { clear: both; padding-top: 52px; display: flex; flex-direction: column; align-items: center; gap: 14px; }
.pgn-info { font-size: 13px; color: var(--ink-3); font-weight: 500; }
.pgn-info strong { color: var(--P); font-weight: 700; }

.pgn nav > div.sm\:hidden { display: none !important; }
.pgn nav > div > div:first-child { display: none !important; }
.pgn nav > div > div:last-child > span,
.pgn nav ul.pagination {
  display: inline-flex !important; align-items: center;
  justify-content: center; gap: 8px !important;
  box-shadow: none !important; margin: 0 !important; padding: 0 !important;
}
.pgn nav a.relative,
.pgn nav span[aria-current] > span,
.pgn nav span[aria-disabled] > span,
.pgn nav li > a,
.pgn nav li > span {
  display: flex !important; align-items: center !important; justify-content: center !important;
  width: 42px !important; height: 42px !important; border-radius: 11px !important;
  background: var(--white) !important; border: 1px solid var(--border) !important;
  color: var(--ink-3) !important; font-size: 14px !important; font-weight: 600 !important;
  text-decoration: none !important; transition: all .25s !important;
}
.pgn nav a.relative:hover,
.pgn nav li > a:hover {
  border-color: var(--P) !important; color: var(--P) !important;
  background: var(--PBG) !important; transform: translateY(-2px) !important;
}
.pgn nav span[aria-current="page"] > span {
  background: var(--P) !important; color: #fff !important;
  border-color: transparent !important;
  box-shadow: 0 6px 18px rgba(48,59,151,.28) !important;
  transform: scale(1.04) !important;
}
.pgn nav span[aria-disabled="true"] > span {
  background: var(--cream) !important; color: var(--ink-4) !important;
  cursor: not-allowed !important; border-color: transparent !important;
}
.pgn nav svg { width: 16px !important; height: 16px !important; display: block !important; }

/* ══════════════════════════════════════
   MOBILE FILTER BUTTON
══════════════════════════════════════ */
.mob-btn {
  display: none; position: fixed; bottom: 24px; left: 50%;
  transform: translateX(-50%);
  padding: 13px 28px; background: var(--ink); color: #fff;
  border-radius: 100px; font-size: 13.5px; font-weight: 700;
  align-items: center; gap: 10px; z-index: 100;
  border: none; cursor: pointer;
  box-shadow: 0 8px 28px rgba(15,20,34,.4);
  font-family: var(--f-ui);
  transition: background .25s, transform .25s;
}
.mob-btn:hover { background: var(--P); transform: translateX(-50%) translateY(-2px); }
.mob-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--G); flex-shrink: 0; }

.sb-overlay {
  position: fixed; inset: 0;
  background: rgba(15,20,34,.55); z-index: 30; display: none;
  backdrop-filter: blur(5px);
}

/* ══════════════════════════════════════
   RESPONSIVE
══════════════════════════════════════ */
@media(max-width:1280px) { .pc { width: 50%; } }
@media(max-width:1024px) {
  .sb { transform: translateX(-100%); }
  .sb.open { transform: translateX(0); box-shadow: 0 0 60px rgba(0,0,0,.35); }
  .main { margin-left: 0; padding: 24px 20px 100px; }
  .mob-btn { display: flex; }
}
@media(max-width:768px) {
  .pg-head { margin-bottom: 24px; }
  .pg-title { font-size: clamp(24px, 6vw, 32px); }
}
@media(max-width:600px) {
  .pc { width: 100%; padding: 0; }
  .grid { margin: 0; }
}
@media(max-width:380px) {
  .pg-head-right { flex-direction: column; align-items: flex-end; }
}
</style>
</head>
<body>

@php $navbarStyle = 'navbar-light'; @endphp
@include('navbar')

<div class="wrap">

{{-- ═══════ SIDEBAR ═══════ --}}
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

    {{-- Listing type --}}
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

    {{-- City --}}
    <div class="fg">
      <label class="fg-lbl" data-i18n="lbCity">شار</label>
      <div class="sel-wrap">
        <select id="city-dropdown" class="fc"><option value="">...</option></select>
      </div>
    </div>

    {{-- Area --}}
    <div class="fg">
      <label class="fg-lbl" data-i18n="lbArea">ناوچە</label>
      <div class="sel-wrap">
        <select id="area-dropdown" class="fc" disabled>
          <option value="" data-i18n="optAreaFirst">یەکەم شار هەڵبژێرە</option>
        </select>
      </div>
    </div>

    {{-- Property type --}}
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

    {{-- Keywords --}}
    <div class="fg">
      <label class="fg-lbl" data-i18n="lbKeyword">کلیلەوشە</label>
      <input type="text" id="search-keywords-input" class="fc"
        placeholder="پووڵ، باخچە..." data-ph-i18n="phKeyword"/>
    </div>

    {{-- Price --}}
    <div class="fg">
      <label class="fg-lbl" data-i18n="lbPrice">نرخ (USD)</label>
      <div class="two-col">
        <input type="number" id="min-price-input" class="fc" placeholder="$ کەمترین" data-ph-i18n="phMin"/>
        <input type="number" id="max-price-input" class="fc" placeholder="$ زۆرترین" data-ph-i18n="phMax"/>
      </div>
      <div class="price-hint">
        <i class="fas fa-dollar-sign"></i>
        <span data-i18n="priceHint">نرخەکان بە دۆلاری ئەمریکی</span>
      </div>
    </div>

    <div class="sb-divider"></div>

    <div class="active-filters" id="activeFilterTags"></div>

    <button class="btn-apply" id="search-button">
      <i class="fas fa-search"></i>&nbsp;<span data-i18n="btnApply">فلتەر جێبەجێ بکە</span>
    </button>
    <button class="btn-reset" id="clear-filters">
      <i class="fas fa-rotate-left"></i>&nbsp;<span data-i18n="btnReset">پاکی بکەرەوە</span>
    </button>

  </div>
</aside>

{{-- ═══════ MAIN ═══════ --}}
<main class="main">

  <div class="pg-head">
    <div>
      <div class="pg-tag" data-i18n="pgTag">خانووبەرەی کوردستان</div>
      <div class="pg-title">Dream Mulk &mdash; <em data-i18n="pgTitleEm">خانووەکان</em></div>
    </div>
    <div class="pg-head-right">
      <div class="lang-sw">
        <button class="lang-btn active" data-lang="ku">کو</button>
        <button class="lang-btn" data-lang="en">EN</button>
        <button class="lang-btn" data-lang="ar">ع</button>
      </div>
      <div class="count-pill">
        <div class="count-dot"></div>
        <span>
          <strong id="results-counter">{{ $properties->total() }}</strong>&nbsp;<span data-i18n="propLabel">خانوو</span>
        </span>
      </div>
    </div>
  </div>

  {{-- GRID --}}
  <div class="grid" id="propertiesGrid">
    @foreach($properties as $property)
    @php
      $priceUsd = $property->price['usd'] ?? 0;
      $priceIqd = $property->price['iqd'] ?? ($priceUsd * 1300);
      $lt = strtolower($property->listing_type ?? '');
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
              <span class="badge {{ $lt === 'rent' ? 'badge-rent' : 'badge-sell' }}">
                {{ ucfirst($lt ?: 'N/A') }}
              </span>
            </div>
            <div class="ci-price">
              <div class="ci-price-cur">USD</div>
              <div class="ci-price-num price-display" data-usd="{{ $priceUsd }}" data-iqd="{{ $priceIqd }}">
                ${{ number_format($priceUsd) }}
              </div>
            </div>
          </div>

          <div class="cb">
            <div class="cb-title">
              {{ $property->name['en'] ?? $property->name ?? 'Exclusive Property' }}
            </div>
            <div class="cb-loc">
              <i class="fas fa-location-dot"></i>
              {{ $property->address ?? ($property->address_details['city']['en'] ?? 'Kurdistan Region') }}
            </div>
            <div class="cb-feats">
              <div class="feat">
                <i class="fas fa-bed"></i>
                <span class="feat-v">{{ $property->rooms['bedroom']['count'] ?? 0 }}</span>
                <span class="feat-l" data-i18n="featBeds">جێخەو</span>
              </div>
              <div class="feat">
                <i class="fas fa-bath"></i>
                <span class="feat-v">{{ $property->rooms['bathroom']['count'] ?? 0 }}</span>
                <span class="feat-l" data-i18n="featBaths">حەمام</span>
              </div>
              <div class="feat">
                <i class="fas fa-vector-square"></i>
                <span class="feat-v">{{ $property->area ?? '—' }}</span>
                <span class="feat-l">m²</span>
              </div>
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
    </div>
    @endforeach
  </div>

  {{-- Empty state --}}
  <div class="empty-state" id="emptyState">
    <div class="empty-ic"><i class="fas fa-house-circle-xmark"></i></div>
    <h3 data-i18n="emptyTitle">هیچ خانوویەک نەدۆزراوەتەوە</h3>
    <p data-i18n="emptyDesc">فلتەرەکانت بگۆڕە بۆ دۆزینەوەی خانووی گونجاو.</p>
    <button class="empty-reset" id="emptyResetBtn">
      <i class="fas fa-rotate-left"></i>&nbsp;<span data-i18n="btnReset">پاکی بکەرەوە</span>
    </button>
  </div>

  @if($properties->count() === 0)
  <script>document.getElementById('emptyState').classList.add('visible');</script>
  @endif

  {{-- Pagination --}}
  <div class="pgn">
    <div class="pgn-info">
      <span data-i18n="pgPage">لاپەڕە</span>&nbsp;<strong>{{ $properties->currentPage() }}</strong>&nbsp;<span data-i18n="pgOf">لە</span>&nbsp;<strong>{{ $properties->lastPage() }}</strong>
      &nbsp;·&nbsp;<strong>{{ $properties->total() }}</strong>&nbsp;<span data-i18n="pgTotal">خانوو بەکۆی هەموو</span>
    </div>
    {{ $properties->links() }}
  </div>

</main>

{{-- Mobile filter button --}}
<button class="mob-btn" id="mobBtn">
  <span class="mob-dot"></span>
  <i class="fas fa-sliders-h"></i>&nbsp;<span data-i18n="mobFilter">فلتەرەکان</span>
</button>
<div class="sb-overlay" id="sbOverlay"></div>

</div>{{-- end .wrap --}}


{{-- ═══════════════════════════════════════════
     I18N CLASS
═══════════════════════════════════════════ --}}
<script>
class DreamMulkI18n {
  constructor(o = {}) {
    this.storageKey  = o.storageKey  || 'dm_lang';
    this.defaultLang = o.defaultLang || 'ku';
    this.onLangChange = o.onLangChange || null;
    this._current = this.defaultLang;
  }

  init() {
    const saved = localStorage.getItem(this.storageKey) || this.defaultLang;
    this.setLang(saved);
  }

  setLang(lang) {
    if (!this.translations[lang]) return;
    this._current = lang;
    localStorage.setItem(this.storageKey, lang);
    const T = this.translations[lang];

    document.body.dir = T.dir;
    document.documentElement.lang = lang;
    document.body.classList.remove('lang-ku', 'lang-en', 'lang-ar', 'rtl');
    document.body.classList.add('lang-' + lang);
    if (T.dir === 'rtl') document.body.classList.add('rtl');

    document.querySelectorAll('.lang-btn').forEach(b =>
      b.classList.toggle('active', b.getAttribute('data-lang') === lang)
    );
    document.querySelectorAll('[data-i18n]').forEach(el => {
      const k = el.getAttribute('data-i18n');
      if (T[k] !== undefined) el.textContent = T[k];
    });
    document.querySelectorAll('[data-ph-i18n]').forEach(el => {
      const k = el.getAttribute('data-ph-i18n');
      if (T[k] !== undefined) el.placeholder = T[k];
    });

    const typeSel = document.getElementById('property-type-dropdown');
    if (typeSel) {
      const map = { '': 'optAll', 'sell': 'optBuy', 'rent': 'optRent' };
      typeSel.querySelectorAll('option').forEach(o => {
        const k = map[o.value];
        if (k && T[k]) o.textContent = T[k];
      });
    }

    if (typeof this.onLangChange === 'function') this.onLangChange(lang, T);
  }

  getCurrentLang() { return this._current; }
  t(key) { return (this.translations[this._current] || {})[key] || key; }

  translations = {
    ku: {
      dir: 'rtl',
      sbTitle: 'فلتەر', sbSub: 'گەڕانەکەت باشتر بکە',
      lbType: 'جۆری لیست', lbCity: 'شار', lbArea: 'ناوچە',
      lbPurpose: 'جۆری خانوو', lbKeyword: 'کلیلەوشە', lbPrice: 'نرخ (USD)',
      optAll: 'کڕین یان کرێ', optBuy: 'کڕین', optRent: 'کرێ',
      optAreaFirst: 'یەکەم شار هەڵبژێرە', optAllTypes: 'هەموو جۆرەکان',
      optVilla: 'ڤیلا', optHouse: 'خانوو', optApart: 'ئەپارتمان', optComm: 'بازرگانی',
      phKeyword: 'پووڵ، باخچە...', phMin: '$ کەمترین', phMax: '$ زۆرترین',
      priceHint: 'نرخەکان بە دۆلاری ئەمریکی',
      btnApply: 'فلتەر جێبەجێ بکە', btnReset: 'پاکی بکەرەوە',
      pgTag: 'خانووبەرەی کوردستان', pgTitleEm: 'خانووەکان',
      propLabel: 'خانوو',
      featBeds: 'جێخەو', featBaths: 'حەمام', ctaView: 'وردەکاریەکان',
      emptyTitle: 'هیچ خانوویەک نەدۆزراوەتەوە',
      emptyDesc: 'فلتەرەکانت بگۆڕە بۆ دۆزینەوەی خانووی گونجاو.',
      pgPage: 'لاپەڕە', pgOf: 'لە', pgTotal: 'خانوو بەکۆی هەموو',
      mobFilter: 'فلتەرەکان',
    },
    en: {
      dir: 'ltr',
      sbTitle: 'Filters', sbSub: 'Refine your search',
      lbType: 'Listing Type', lbCity: 'City', lbArea: 'Area',
      lbPurpose: 'Property Type', lbKeyword: 'Keywords', lbPrice: 'Price (USD)',
      optAll: 'Buy or Rent', optBuy: 'Buy', optRent: 'Rent',
      optAreaFirst: 'Select city first', optAllTypes: 'All Types',
      optVilla: 'Villa', optHouse: 'House', optApart: 'Apartment', optComm: 'Commercial',
      phKeyword: 'Pool, Garden...', phMin: '$ Min', phMax: '$ Max',
      priceHint: 'All prices in US Dollars',
      btnApply: 'Apply Filters', btnReset: 'Reset Filters',
      pgTag: 'Kurdistan Real Estate', pgTitleEm: 'Properties',
      propLabel: 'Properties',
      featBeds: 'Beds', featBaths: 'Baths', ctaView: 'View Details',
      emptyTitle: 'No Properties Found',
      emptyDesc: 'Try adjusting your filters to find matching properties.',
      pgPage: 'Page', pgOf: 'of', pgTotal: 'total listings',
      mobFilter: 'Filters',
    },
    ar: {
      dir: 'rtl',
      sbTitle: 'الفلاتر', sbSub: 'حسّن بحثك',
      lbType: 'نوع الإدراج', lbCity: 'المدينة', lbArea: 'المنطقة',
      lbPurpose: 'نوع العقار', lbKeyword: 'كلمات مفتاحية', lbPrice: 'السعر (USD)',
      optAll: 'شراء أو إيجار', optBuy: 'شراء', optRent: 'إيجار',
      optAreaFirst: 'اختر المدينة أولاً', optAllTypes: 'جميع الأنواع',
      optVilla: 'فيلا', optHouse: 'منزل', optApart: 'شقة', optComm: 'تجاري',
      phKeyword: 'مسبح، حديقة...', phMin: '$ الأدنى', phMax: '$ الأقصى',
      priceHint: 'جميع الأسعار بالدولار الأمريكي',
      btnApply: 'تطبيق الفلاتر', btnReset: 'مسح الكل',
      pgTag: 'عقارات كردستان', pgTitleEm: 'العقارات',
      propLabel: 'عقار',
      featBeds: 'غرف', featBaths: 'حمامات', ctaView: 'عرض التفاصيل',
      emptyTitle: 'لا توجد عقارات',
      emptyDesc: 'جرّب تعديل الفلاتر للعثور على عقارات مناسبة.',
      pgPage: 'صفحة', pgOf: 'من', pgTotal: 'إجمالي العقارات',
      mobFilter: 'الفلاتر',
    },
  };
}
</script>


{{-- ═══════════════════════════════════════════
     LOCATION SELECTOR CLASS
═══════════════════════════════════════════ --}}
<script>
class LocationSelector {
  constructor(o = {}) {
    this.cId   = o.citySelectId || 'city-dropdown';
    this.aId   = o.areaSelectId || 'area-dropdown';
    this.onC   = o.onCityChange || null;
    this.onA   = o.onAreaChange || null;
    this.cities = [];
    this.curC  = o.selectedCityId  || null;
    this.curA  = o.selectedAreaId  || null;
  }

  async init() {
    try {
      await this.loadCities();
      this.bind();
      if (this.curC) await this.loadAreas(this.curC);
    } catch (e) { console.error(e); }
  }

  async loadCities() {
    const el = document.getElementById(this.cId);
    try {
      const r = await fetch('/v1/api/location/branches', { headers: { 'Accept-Language': 'en' } });
      const d = await r.json();
      if (d.success && Array.isArray(d.data)) {
        this.cities = d.data;
        this.fillCities();
      }
    } catch (e) {
      if (el) el.innerHTML = '<option value="">All Cities</option>';
    }
  }

  fillCities() {
    const el = document.getElementById(this.cId);
    if (!el) return;
    el.innerHTML = '<option value="">All Cities</option>';
    [...this.cities]
      .sort((a, b) => a.city_name_en.localeCompare(b.city_name_en))
      .forEach(c => {
        const o = document.createElement('option');
        o.value = c.id;
        o.textContent = c.city_name_en;
        o.dataset.nameEn = c.city_name_en;
        if (c.id == this.curC) o.selected = true;
        el.appendChild(o);
      });
  }

  async loadAreas(id) {
    const el = document.getElementById(this.aId);
    if (!el) return;
    el.innerHTML = '<option value="">Loading…</option>';
    el.disabled = true;
    try {
      const r = await fetch(`/v1/api/location/branches/${id}/areas`, { headers: { 'Accept-Language': 'en' } });
      const d = await r.json();
      if (d.success && d.data) this.fillAreas(d.data);
    } catch (e) {}
    finally { el.disabled = false; }
  }

  fillAreas(areas) {
    const el = document.getElementById(this.aId);
    if (!el) return;
    el.innerHTML = '<option value="">All Areas</option>';
    [...areas]
      .sort((a, b) => a.area_name_en.localeCompare(b.area_name_en))
      .forEach(a => {
        const o = document.createElement('option');
        o.value = a.id;
        o.textContent = a.area_name_en;
        o.dataset.nameEn = a.area_name_en;
        if (a.id == this.curA) o.selected = true;
        el.appendChild(o);
      });
  }

  bind() {
    const cEl = document.getElementById(this.cId);
    const aEl = document.getElementById(this.aId);
    if (cEl) cEl.addEventListener('change', async e => {
      if (e.target.value) {
        await this.loadAreas(e.target.value);
        if (this.onC) this.onC(e.target.value);
      } else {
        if (aEl) { aEl.innerHTML = '<option value="">Select City First</option>'; aEl.disabled = true; }
        if (this.onC) this.onC(null);
      }
    });
    if (aEl) aEl.addEventListener('change', e => { if (this.onA) this.onA(e.target.value); });
  }
}
</script>


{{-- ═══════════════════════════════════════════
     MAIN APP LOGIC
═══════════════════════════════════════════ --}}
<script>
document.addEventListener('DOMContentLoaded', () => {

  /* ── Lenis smooth scroll ── */
  const lenis = new Lenis({
    duration: 1.2,
    easing: t => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
    smoothWheel: true,
    smoothTouch: false,
  });
  lenis.on('scroll', ScrollTrigger.update);
  gsap.ticker.add(time => lenis.raf(time * 1000));
  gsap.ticker.lagSmoothing(0);

  $(function () {

    /* ── i18n ── */
    const i18n = new DreamMulkI18n({ defaultLang: 'ku' });
    document.querySelectorAll('.lang-btn').forEach(btn =>
      btn.addEventListener('click', () => i18n.setLang(btn.getAttribute('data-lang')))
    );
    i18n.init();

    /* ── USD price display ── */
    document.querySelectorAll('.price-display').forEach(el => {
      el.textContent = '$' + Number(el.dataset.usd).toLocaleString();
    });

    /* ── Isotope + GSAP stagger ── */
    var $g = $('#propertiesGrid'), isoReady = false;

    function initIso() {
      if ($('.pc').length === 0) { showEmpty(); return; }
      $g.imagesLoaded(function () {
        $g.isotope({
          itemSelector: '.pc',
          percentPosition: true,
          layoutMode: 'fitRows',
          transitionDuration: '0.38s',
        });
        isoReady = true;
        setTimeout(() => { $g.isotope('layout'); ScrollTrigger.refresh(); }, 100);
        applyUrlParams();

        gsap.fromTo('.pc-inner',
          { y: 28, opacity: 0 },
          { y: 0, opacity: 1, duration: 0.75, stagger: 0.05, ease: 'power2.out', clearProps: 'all' }
        );
      });
    }
    initIso();

    /* ── Empty state ── */
    function showEmpty() {
      document.getElementById('emptyState').classList.add('visible');
      $('#results-counter').text(0);
    }
    function hideEmpty() {
      document.getElementById('emptyState').classList.remove('visible');
    }

    /* ── Filter logic ── */
    function runFilter() {
      if (!isoReady) return;
      var kw  = $('#search-keywords-input').val().toLowerCase().trim();
      var mn  = parseFloat($('#min-price-input').val()) || 0;
      var mx  = parseFloat($('#max-price-input').val()) || Infinity;
      var tp  = $('#purpose-dropdown').val().toLowerCase();
      var ls  = $('#property-type-dropdown').val().toLowerCase();
      var cy  = ($('#city-dropdown option:selected').data('nameEn') || '').toLowerCase();
      var ar  = ($('#area-dropdown option:selected').data('nameEn') || '').toLowerCase();

      $g.isotope({ filter: function () {
        var $t = $(this), tx = $t.text().toLowerCase();
        var price = parseFloat($t.attr('data-price-usd'));
        return (!kw || tx.includes(kw))
          && price >= mn && price <= mx
          && (!tp || $t.attr('data-type') === tp)
          && (!ls || $t.attr('data-listing') === ls)
          && (!cy || tx.includes(cy))
          && (!ar || tx.includes(ar));
      }});

      setTimeout(() => {
        const iso   = $g.data('isotope');
        const count = iso ? iso.filteredItems.length : 0;
        $('#results-counter').text(count);
        if (count === 0) showEmpty(); else hideEmpty();
        ScrollTrigger.refresh();
      }, 400);

      buildFilterTags();
    }

    /* ── URL params ── */
    function applyUrlParams() {
      const p      = new URLSearchParams(window.location.search);
      const type   = p.get('type')   || '';
      const search = p.get('search') || '';
      if (type)   $('#property-type-dropdown').val(type);
      if (search) $('#search-keywords-input').val(search);
      if (type || search) runFilter();
    }

    /* ── Active filter tags ── */
    function buildFilterTags() {
      const tags = [];
      const type = $('#property-type-dropdown').val();
      const kw   = $('#search-keywords-input').val();
      const city = $('#city-dropdown option:selected').text();
      const area = $('#area-dropdown option:selected').text();
      const mn   = $('#min-price-input').val();
      const mx   = $('#max-price-input').val();

      if (type) tags.push({
        label: type === 'sell' ? i18n.t('optBuy') : i18n.t('optRent'),
        clear: () => $('#property-type-dropdown').val(''),
      });
      if (kw) tags.push({ label: kw, clear: () => $('#search-keywords-input').val('') });
      if (city && city !== 'All Cities' && city !== '...') tags.push({
        label: city,
        clear: () => {
          $('#city-dropdown').val('');
          $('#area-dropdown').html('<option value="">Select City First</option>').prop('disabled', true);
        },
      });
      if (area && area !== 'All Areas' && area !== 'Select City First') tags.push({
        label: area, clear: () => $('#area-dropdown').val(''),
      });
      if (mn) tags.push({ label: '≥ $' + Number(mn).toLocaleString(), clear: () => $('#min-price-input').val('') });
      if (mx) tags.push({ label: '≤ $' + Number(mx).toLocaleString(), clear: () => $('#max-price-input').val('') });

      const $wrap = $('#activeFilterTags').empty();
      tags.forEach(t => {
        const $tag = $('<div class="af-tag"></div>').text(t.label);
        const $x   = $('<button title="remove">✕</button>').on('click', () => { t.clear(); runFilter(); });
        $tag.append($x);
        $wrap.append($tag);
      });
    }

    /* ── Location selector ── */
    const urlCity = new URLSearchParams(window.location.search).get('city') || '';
    const locSel  = new LocationSelector({
      citySelectId: 'city-dropdown',
      areaSelectId: 'area-dropdown',
      onCityChange: () => runFilter(),
      onAreaChange: () => runFilter(),
    });
    locSel.init().then(() => {
      if (urlCity) {
        $('#city-dropdown option').each(function () {
          if (($(this).data('nameEn') || '').toLowerCase() === urlCity.toLowerCase()) {
            $('#city-dropdown').val($(this).val()).trigger('change');
          }
        });
      }
      if (new URLSearchParams(window.location.search).toString()) runFilter();
    });

    /* ── Button bindings ── */
    $('#search-button').on('click', runFilter);

    function resetAll() {
      $('input.fc').val('');
      $('select.fc').prop('selectedIndex', 0);
      $('#area-dropdown').html('<option value="">Select City First</option>').prop('disabled', true);
      hideEmpty();
      if (isoReady) $g.isotope({ filter: '*' });
      $('#activeFilterTags').empty();
      setTimeout(() => {
        $('#results-counter').text($('.pc').length);
        ScrollTrigger.refresh();
      }, 400);
      history.replaceState(null, '', window.location.pathname);
    }

    $('#clear-filters, #emptyResetBtn').on('click', resetAll);

    /* ── Mobile sidebar ── */
    const openSb  = () => { $('#sb').addClass('open'); $('#sbOverlay').fadeIn(180); $('body').css('overflow', 'hidden'); };
    const closeSb = () => { $('#sb').removeClass('open'); $('#sbOverlay').fadeOut(180); $('body').css('overflow', ''); };
    $('#mobBtn').on('click', openSb);
    $('#sbOverlay').on('click', closeSb);

    $(window).on('resize', function () {
      if (window.innerWidth > 1024) closeSb();
      if (isoReady) $g.isotope('layout');
    });

  }); // end $()
}); // end DOMContentLoaded
</script>

</body>
</html>
