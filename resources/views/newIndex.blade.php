<!DOCTYPE html>
<html lang="ku">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width,initial-scale=1.0,viewport-fit=cover" name="viewport"/>
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Dream Mulk — خانوو و زەوی پریمیەم</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,800;1,700&family=Amiri:ital,wght@0,400;0,700;1,400;1,700&family=DM+Sans:wght@300;400;500;600&family=Noto+Sans+Arabic:wght@300;400;500;600;700&family=Cinzel:wght@400;600&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@studio-freight/lenis@1.0.42/dist/lenis.min.js"></script>

<style>
/* ═══════════════════════════════════════
   CSS VARIABLES
═══════════════════════════════════════ */
:root {
  --P: #303b97;
  --PD: #060814;
  --PM: #131836;
  --deep: #03040a;
  --G: #d4af37;
  --GL: #ebd37a;
  --GP: #f5e8c0;
  --dim: rgba(255,255,255,.75);
  --glass-bg: rgba(10, 13, 39, 0.45);
  --glass-border: rgba(255, 255, 255, 0.08);
  --E: cubic-bezier(0.25, 1, 0.15, 1);

  --f-en: 'DM Sans', sans-serif;
  --f-en-disp: 'Playfair Display', serif;
  --f-ar: 'Amiri', serif;
  --f-ar-ui: 'Noto Sans Arabic', sans-serif;
  --f-cinzel: 'Cinzel', serif;
}

/* ═══════════════════════════════════════
   RESET
═══════════════════════════════════════ */
*,*::before,*::after { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color:transparent; }
html { overflow-x:clip; text-size-adjust:100%; scroll-behavior: auto; }
body {
  background: var(--deep); color: #fff;
  font-family: var(--f-en);
  overflow-x: clip;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  direction: rtl;
  overscroll-behavior-y: none;
}
::-webkit-scrollbar { display:none; }
img { display:block; max-width:100%; height:auto; }
a,button { touch-action: manipulation; }

/* ═══════════════════════════════════════
   LANGUAGE
═══════════════════════════════════════ */
body.lang-ku, body.lang-ar { font-family: var(--f-ar-ui); }
body.lang-ku *:not(i):not([class*="fa"]),
body.lang-ar *:not(i):not([class*="fa"]) {
  font-family: var(--f-ar-ui);
  letter-spacing: 0 !important;
}
i[class*="fa"] {
  font-family: "Font Awesome 6 Free", "Font Awesome 6 Brands" !important;
  font-style: normal !important; letter-spacing: 0 !important;
}
body.lang-ku h1, body.lang-ku h2, body.lang-ku h3, body.lang-ku h4,
body.lang-ar h1, body.lang-ar h2, body.lang-ar h3, body.lang-ar h4 {
  font-family: var(--f-ar) !important;
  font-weight: 700;
  line-height: 1.4 !important;
}
body.lang-ku h1, body.lang-ar h1 { font-size: clamp(48px, 9vw, 96px) !important; line-height: 1.25 !important; }
body.lang-ku .logo-name, body.lang-ar .logo-name,
body.lang-ku .ft-logo-name, body.lang-ar .ft-logo-name { font-family: var(--f-en-disp) !important; }
body.lang-ku p, body.lang-ar p { font-size: 1.05em; line-height: 1.95 !important; }
body.lang-ku .kurd-sub, body.lang-ar .kurd-sub {
  font-family: var(--f-ar) !important; line-height: 1.85 !important; font-size: clamp(17px, 2.2vw, 22px) !important;
}

/* ═══════════════════════════════════════
   HEADER
═══════════════════════════════════════ */
header {
  position: fixed; top: 0; left: 0; right: 0; height: 90px; z-index: 1000;
  padding: 0 50px; display: flex; align-items: center;
  background: linear-gradient(to bottom, rgba(3,4,10,0.9), transparent);
  transition: height 0.4s var(--E), background 0.4s ease, backdrop-filter 0.4s ease;
  will-change: height, background;
}
header.sc {
  height: 70px; background: rgba(3, 4, 10, 0.75);
  backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
  border-bottom: 1px solid rgba(212,175,55,0.1);
}
nav { max-width: 1400px; width: 100%; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; }
.logo { display: flex; align-items: center; gap: 12px; text-decoration: none; }
.logo img { width: 44px; height: 44px; border-radius: 50%; border: 2px solid var(--G); transition: transform 0.4s var(--E); background:#fff; object-fit:contain; }
.logo:hover img { transform: scale(1.08) rotate(5deg); }
.logo-name { font-family: var(--f-en-disp) !important; font-size: 22px; font-weight: 700; color: #fff; white-space:nowrap; }

.nav-ul { display: flex; gap: 32px; list-style: none; }
.nav-ul a {
  font-size: 14.5px; color: rgba(255,255,255,.8); text-decoration: none; position: relative;
  padding: 5px 0; transition: color 0.3s; white-space:nowrap;
}
.nav-ul a::after {
  content: ''; position: absolute; bottom: 0; left: 0; width: 0; height: 1.5px;
  background: var(--G); transition: width 0.4s var(--E);
}
.nav-ul a:hover, .nav-ul a.ac { color: #fff; }
.nav-ul a:hover::after, .nav-ul a.ac::after { width: 100%; }

.nav-right { display: flex; align-items: center; gap: 14px; flex-shrink:0; }
.lang-sw { display: flex; gap: 4px; background: rgba(255,255,255,0.05); border-radius: 50px; padding: 4px; border: 1px solid var(--glass-border); }
.lang-btn {
  padding: 6px 12px; border-radius: 50px; border: none; background: transparent;
  color: rgba(255,255,255,.6); font-size: 12px; font-weight: 600; cursor: pointer;
  transition: all 0.3s; font-family: var(--f-en) !important;
}
.lang-btn.active { background: var(--G); color: var(--PD); }
.lang-btn:hover:not(.active) { color: #fff; background: rgba(255,255,255,0.1); }

.bell-wrap{position:relative;color:var(--G);font-size:19px;text-decoration:none;line-height:1;}
.bell-badge{position:absolute;top:-4px;right:-6px;background:#e74c3c;color:#fff;font-size:9px;font-weight:700;padding:2px 4px;border-radius:5px;line-height:1;}
.av-btn{width:36px;height:36px;border-radius:50%;background:var(--G);color:var(--PD);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;text-decoration:none;transition:transform .3s;font-family:var(--f-en)!important;flex-shrink:0;}
.av-btn:hover{transform:scale(1.1);}
.btn-o{ padding:8px 20px;border:1px solid rgba(212,175,55,.45);border-radius:50px; color:var(--G);font-size:13px;text-decoration:none;transition:all .3s; white-space:nowrap; }
.btn-o:hover{background:rgba(212,175,55,.1);}
.btn-s {
  padding: 10px 24px; border-radius: 50px; background: var(--G); color: var(--PD);
  font-size: 13.5px; font-weight: 700; text-decoration: none;
  transition: all 0.3s var(--E); display: flex; align-items: center; gap: 8px; white-space:nowrap;
}
.btn-s:hover { background: var(--GL); transform: translateY(-2px); box-shadow: 0 8px 20px rgba(212,175,55,0.25); }
.hbtn { display: none; background: none; border: none; color: var(--G); font-size: 24px; cursor: pointer; padding:8px; line-height:1; }

/* ═══════════════════════════════════════
   DRAWER
═══════════════════════════════════════ */
.bkdp{position:fixed;inset:0;background:rgba(0,0,0,.8);opacity:0;pointer-events:none;transition:opacity .4s;z-index:1090;backdrop-filter:blur(8px);}
.bkdp.on{opacity:1;pointer-events:auto;}
.drw{
  position:fixed;top:0;right:-110%; height:100dvh;width:min(340px,86%);
  background:var(--PD);z-index:1200; padding:36px 26px;
  display:flex;flex-direction:column;gap:0; transition:right .4s var(--E);
  box-shadow:-8px 0 40px rgba(0,0,0,.7); overflow-y:auto;visibility:hidden;
  -webkit-overflow-scrolling:touch;
}
.drw.on{right:0;visibility:visible;}
.drw::before{content:'';position:absolute;top:0;bottom:0;left:0;width:3px;background:var(--G);}
body.rtl .drw{right:auto;left:-110%;border-left:none;}
body.rtl .drw.on{left:0;right:auto;}
body.rtl .drw::before{left:auto;right:0;}
.drw-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;}
.drw-hd span{font-family:var(--f-en-disp)!important;font-size:22px;color:#fff;}
.drw-x{background:none;border:none;color:var(--G);font-size:20px;cursor:pointer;padding:6px;line-height:1;}
.drw-lang{display:flex;gap:4px;margin-bottom:18px;background:rgba(255,255,255,.06);border-radius:50px;padding:3px;}
.drw-lang .lang-btn{flex:1;text-align:center;padding:8px 0;font-size:13px;}
.drw-nav{display:flex;flex-direction:column;}
.drw-nav a{ display:block;padding:14px 0; font-size:16px;color:rgba(255,255,255,.8); border-bottom:1px solid rgba(255,255,255,.06); text-decoration:none;transition:all .3s; }
.drw-nav a:hover{color:var(--G);padding-left:10px;}
body.rtl .drw-nav a:hover{padding-left:0;padding-right:10px;}
.drw-ft{margin-top:auto;padding-top:22px;display:flex;flex-direction:column;gap:10px;}

/* ═══════════════════════════════════════
   CINEMATIC HERO (Zero-JS CSS Slider)
═══════════════════════════════════════ */
.hero-sec {
  position: relative; height: 100vh; min-height: 700px;
  display: flex; align-items: center; justify-content: center;
  background: var(--deep); overflow: hidden;
}

.hero-slider {
  position: absolute; inset: 0; z-index: 0; pointer-events: none;
}
.slide {
  position: absolute; inset: 0; background-size: cover; background-position: center;
  opacity: 0; transform: scale(1.1) translateZ(0); will-change: transform, opacity;
  animation: cinematicFade 18s infinite linear;
}
.slide:nth-child(1) { background-image: url('https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1920&q=80'); animation-delay: 0s; }
.slide:nth-child(2) { background-image: url('https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=1920&q=80'); animation-delay: 6s; }
.slide:nth-child(3) { background-image: url('https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=1920&q=80'); animation-delay: 12s; }

@keyframes cinematicFade {
  0% { opacity: 0; transform: scale(1.1) translateZ(0); }
  10% { opacity: 0.85; }
  28% { opacity: 0.85; }
  38% { opacity: 0; transform: scale(1) translateZ(0); }
  100% { opacity: 0; }
}

.hero-overlay {
  position: absolute; inset: 0; z-index: 1; pointer-events: none;
  background:
    radial-gradient(circle at center, transparent 0%, rgba(3,4,10,0.8) 100%),
    linear-gradient(to bottom, rgba(3,4,10,0.4) 0%, rgba(3,4,10,0.2) 50%, var(--deep) 100%);
}

.hc {
  position: relative; z-index: 10; text-align: center; max-width: 900px;
  padding: 0 24px; margin: 0 auto; direction: ltr;
  will-change: transform, opacity;
}
.hc .kurd-sub, .hc .eyebrow, .hc .hs-quick, .hc .hs-input-wrap { direction: rtl; }

.eyebrow {
  display: inline-flex; align-items: center; gap: 14px;
  font-size: 13px; font-weight: 700; color: var(--G); margin-bottom: 20px;
}
.eyebrow::before, .eyebrow::after { content: ''; width: 40px; height: 1px; flex-shrink: 0; }
.eyebrow::before { background: linear-gradient(90deg, transparent, var(--G)); }
.eyebrow::after { background: linear-gradient(90deg, var(--G), transparent); }

h1 {
  font-family: var(--f-en-disp) !important; font-size: clamp(50px, 10vw, 110px);
  font-weight: 800; line-height: 1.1; color: #fff; margin-bottom: 16px;
  text-shadow: 0 10px 30px rgba(0,0,0,0.5);
}
h1 .g { color: var(--G); }

.kurd-sub {
  font-family: var(--f-ar) !important; font-size: clamp(18px, 2.5vw, 24px);
  color: var(--dim); margin-bottom: 30px; line-height: 1.8;
}
.sub{
  font-family:var(--f-cinzel); font-size:clamp(10px,1.4vw,13px);
  letter-spacing:7px;text-transform:uppercase; color:rgba(255,255,255,.58);
}
body.lang-ku .sub,body.lang-ar .sub{ font-family:var(--f-ar-ui)!important; letter-spacing:1px!important; font-size:12px; }

/* ═══════════════════════════════════════
   PREMIUM SEARCH UI (Floating Pill Design)
═══════════════════════════════════════ */
.hero-search { width: 100%; max-width: 680px; margin: 0 auto 40px; }

/* Floating Tabs container */
.hs-tabs-wrap { display: flex; justify-content: center; margin-bottom: 16px; }
.hs-tabs {
  display: inline-flex; align-items: center; gap: 4px;
  background: rgba(255, 255, 255, 0.08); padding: 5px;
  border-radius: 50px; border: 1px solid rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
}
.hs-tab {
  padding: 8px 24px; border-radius: 40px; border: none; background: transparent;
  color: rgba(255, 255, 255, 0.7); font-size: 14px; font-family: var(--f-ar-ui);
  cursor: pointer; transition: all 0.3s ease; white-space: nowrap;
}
.hs-tab:hover { color: #fff; background: rgba(255, 255, 255, 0.05); }
.hs-tab.active { background: #fff; color: var(--PD); font-weight: 700; box-shadow: 0 4px 12px rgba(0,0,0,0.2); }

/* The Sleek Search Bar */
.hs-bar {
  display: flex; align-items: center; justify-content: space-between;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 60px;
  padding: 6px;
  backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px);
  box-shadow: 0 20px 40px rgba(0,0,0,0.3);
  transition: background 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
  will-change: transform, clip-path, opacity;
}
.hs-bar:focus-within {
  background: rgba(255, 255, 255, 0.12);
  border-color: rgba(212, 175, 55, 0.6);
  box-shadow: 0 25px 50px rgba(0,0,0,0.4), 0 0 25px rgba(212, 175, 55, 0.25);
}
.hs-input-wrap { flex: 1; display: flex; align-items: center; gap: 14px; padding: 0 24px; direction: rtl; }
.hs-input-wrap i { color: var(--G); font-size: 16px; flex-shrink: 0; }
.hs-input-wrap input {
  flex: 1; border: none; outline: none; background: transparent;
  font-size: 15px; color: #fff; font-family: var(--f-ar-ui); padding: 12px 0; width: 100%;
}
.hs-input-wrap input::placeholder { color: rgba(255,255,255,0.45); font-weight: 400; }

/* The Inner Button */
.hs-go {
  padding: 12px 32px; background: var(--G); color: var(--PD);
  font-size: 14px; font-weight: 700; border: none; cursor: pointer;
  border-radius: 50px;
  transition: all 0.3s ease; font-family: var(--f-ar-ui);
  box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
  flex-shrink: 0;
}
.hs-go:hover { background: var(--GL); transform: scale(1.03); }

/* Quick Links */
.hs-quick { display: flex; align-items: center; gap: 10px; margin-top: 16px; justify-content: center; }
.hs-quick span { font-size: 13px; color: rgba(255,255,255,.5); font-family: var(--f-ar-ui); }
.hs-quick a {
  font-size: 12px; color: #fff; padding: 6px 16px; border-radius: 50px;
  background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
  transition: all 0.3s; cursor: pointer; text-decoration: none; font-family: var(--f-ar-ui);
}
.hs-quick a:hover { background: var(--G); color: var(--PD); border-color: var(--G); }

.hbtns{ display:flex;align-items:center;justify-content:center; gap:14px;flex-wrap:wrap; }
.hb1{
  padding:16px 46px;background:var(--G);color:var(--PD);
  font-weight:700;font-size:13px;letter-spacing:2px;text-transform:uppercase;
  border-radius:50px;text-decoration:none; transition:all .3s ease;
  box-shadow:0 8px 20px rgba(212,175,55,.3); font-family:var(--f-ar-ui);
}
body.lang-en .hb1{font-family:var(--f-en);letter-spacing:2.5px;}
.hb1:hover{background:var(--GL);transform:translateY(-2px);box-shadow:0 10px 25px rgba(212,175,55,.5);}
.hb2{
  padding:15px 46px;border:1px solid rgba(255,255,255,.38);
  color:#fff;font-size:13px;letter-spacing:2px;text-transform:uppercase;
  border-radius:50px;text-decoration:none; transition:all .3s ease;
  backdrop-filter:blur(6px);background:rgba(0,0,0,.3); font-family:var(--f-ar-ui);
}
body.lang-en .hb2{font-family:var(--f-en);}
.hb2:hover{border-color:var(--G);color:var(--G);transform:translateY(-2px);background:rgba(212,175,55,.12);}

.scrl{ position:absolute;bottom:32px;left:50%;transform:translateX(-50%); z-index:10;display:flex;flex-direction:column;align-items:center;gap:8px; }
.scrl span{font-size:9px;letter-spacing:4px;text-transform:uppercase;color:rgba(255,255,255,.58);font-family:var(--f-en);}
body.lang-ku .scrl span,body.lang-ar .scrl span{font-family:var(--f-ar-ui)!important;letter-spacing:1px;font-size:11px;}
.mouse{width:22px;height:34px;border:1.5px solid rgba(212,175,55,.75);border-radius:11px;display:flex;justify-content:center;padding-top:6px;}
.mouse::after{content:'';width:3px;height:7px;border-radius:2px;background:var(--G);animation:sp 2.2s ease-in-out infinite;}
@keyframes sp{0%,100%{transform:translateY(0);opacity:1;}50%{transform:translateY(8px);opacity:0;}}

/* ═══════════════════════════════════════
   PAGE SECTIONS
═══════════════════════════════════════ */
.page-sections { position: relative; z-index: 20; background: var(--PD); }

/* STATS */
.stats-bar { border-bottom: 1px solid rgba(255,255,255,0.05); padding: 50px 60px; background: var(--PM); }
.stats-inner { max-width: 1000px; margin: 0 auto; display: flex; justify-content: center; gap: 80px; align-items:center; }
.stat-item { text-align: center; }
.stat-num { font-family: var(--f-en-disp) !important; font-size: clamp(40px, 5vw, 64px); font-weight: 700; color: var(--G); line-height: 1; text-shadow:0 0 30px rgba(212,175,55,.2); }
.stat-label { font-size: 14px; color: rgba(255,255,255,.6); margin-top: 8px; font-family: var(--f-ar-ui); }
.stat-div{width:1px;height:56px;background:rgba(212,175,55,.28);}

/* SERVICES */
.svc-sec { padding: 120px 60px; background: var(--deep); }
.sec-wrap { max-width: 1320px; margin: 0 auto; }
.sec-hd { text-align: center; margin-bottom: 60px; }
.stag { font-size: 14px; color: var(--G); margin-bottom: 14px; font-weight: 600; font-family: var(--f-ar-ui); }
.stitle { font-family: var(--f-ar) !important; font-size: clamp(32px, 4.5vw, 56px); font-weight: 700; line-height: 1.3; }
.stitle em{font-style:italic;color:var(--G);}
body.lang-ku .stitle em,body.lang-ar .stitle em{font-style:normal;}

.svc-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
.svc-card {
  position: relative; height: 520px; border-radius: 20px; overflow: hidden;
  text-decoration: none; color: inherit; display: block;
  transform: translateZ(0); will-change: transform; transition: transform 0.5s var(--E);
}
.svc-card::after {
  content: ''; position: absolute; inset: 0;
  background: linear-gradient(to top, rgba(3,4,10,0.95) 0%, rgba(3,4,10,0.3) 60%, transparent 100%);
  z-index: 1; transition: opacity 0.5s ease;
}
.svc-img {
  position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover;
  transition: transform 0.8s var(--E); will-change: transform;
}
.svc-card:hover { transform: translateY(-10px); }
.svc-card:hover .svc-img { transform: scale(1.08); }

.svc-body { position: absolute; bottom: 0; left: 0; right: 0; padding: 40px; z-index: 2; }
.svc-ico {
  width: 52px; height: 52px; border-radius: 50%; background: rgba(255,255,255,0.05);
  backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1);
  display: flex; align-items: center; justify-content: center; color: var(--G);
  font-size: 20px; margin-bottom: 20px; transition: all 0.4s ease;
}
.svc-card:hover .svc-ico { background: var(--G); color: var(--PD); border-color: var(--G); }
.svc-t { font-family: var(--f-ar) !important; font-size: 28px; font-weight: 700; margin-bottom: 12px; }
.svc-p { font-size: 15px; line-height: 1.8; color: rgba(255,255,255,0.7); font-family: var(--f-ar-ui); margin-bottom: 20px; }
.svc-cta { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 700; color: var(--G); font-family: var(--f-ar-ui); }

/* APP */
.app-sec{padding:120px 60px;background:linear-gradient(135deg,var(--PM) 0%,var(--P) 50%,var(--PD) 100%);position:relative;overflow:hidden;}
.app-g{display:grid;grid-template-columns:1fr 1fr;gap:90px;align-items:center;max-width:1280px;margin:0 auto;position:relative;z-index:1;}
.app-desc{font-size:16px;line-height:1.9;color:var(--dim);margin-bottom:38px;}
body.lang-ku .app-desc,body.lang-ar .app-desc{ font-family:var(--f-ar-ui)!important; font-size:16px!important;line-height:2!important; }
.app-feats{display:flex;flex-direction:column;gap:14px;margin-bottom:44px;}
.af{display:flex;align-items:flex-start;gap:14px;font-size:15px;color:rgba(255,255,255,.9);}
body.lang-ku .af,body.lang-ar .af{font-family:var(--f-ar-ui)!important;font-size:15px!important;line-height:1.75!important;}
.af i{color:var(--G);font-size:18px;flex-shrink:0;margin-top:2px;}
.sbtns{display:flex;gap:14px;flex-wrap:wrap;}
.sbtn{display:flex;align-items:center;gap:14px;padding:15px 24px;border:1px solid rgba(212,175,55,.4);border-radius:14px;text-decoration:none;background:rgba(212,175,55,.07);transition:all .4s var(--E);box-shadow:0 8px 24px rgba(0,0,0,.18);}
.sbtn:hover{background:rgba(212,175,55,.18);border-color:var(--G);transform:translateY(-4px);box-shadow:0 14px 36px rgba(0,0,0,.35);}
.sbtn i{font-size:28px;color:var(--G);}
.sbtn-sm{font-size:11px;letter-spacing:1px;text-transform:uppercase;color:rgba(255,255,255,.6);font-family:var(--f-en)!important;}
body.lang-ku .sbtn-sm,body.lang-ar .sbtn-sm{font-family:var(--f-ar-ui)!important;letter-spacing:0;font-size:12px;text-transform:none;}
.sbtn-nm{font-family:var(--f-en-disp)!important;font-size:18px;font-weight:700;color:#fff;}

.qr-card{background:#fff;border-radius:24px;padding:28px;display:flex;flex-direction:column;align-items:center;gap:18px;max-width:280px;margin:0 auto;box-shadow:0 0 0 2px rgba(212,175,55,.6),0 48px 96px rgba(0,0,0,.8);position:relative;overflow:hidden;transition:transform .7s var(--E);}
.qr-card:hover{transform:translateY(-14px) scale(1.02);}
.qr-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--P),var(--G),var(--P));}
.qr-brand{display:flex;align-items:center;gap:12px;width:100%;}
.qr-ico{width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,var(--PD),var(--P));display:flex;align-items:center;justify-content:center;color:#fff;font-size:20px;flex-shrink:0;}
.qr-t{font-family:var(--f-en-disp)!important;font-size:17px;font-weight:700;color:var(--PD);}
.qr-s{font-size:10px;letter-spacing:1px;text-transform:uppercase;color:#888;font-family:var(--f-en)!important;}
body.lang-ku .qr-s,body.lang-ar .qr-s{font-family:var(--f-ar-ui)!important;letter-spacing:0;font-size:11px;text-transform:none;}
.qr-div{width:100%;height:1px;background:rgba(48,59,151,.12);}
.qr-img{width:196px;height:196px;border-radius:12px;display:block;}
.qr-hint{display:flex;align-items:center;gap:8px;font-size:11px;color:#666;font-family:var(--f-en)!important;}
body.lang-ku .qr-hint,body.lang-ar .qr-hint{font-family:var(--f-ar-ui)!important;font-size:12.5px;}
.qr-lnk{display:flex;align-items:center;justify-content:center;gap:9px;width:100%;padding:13px;background:linear-gradient(135deg,var(--PD),var(--PM));color:#fff;border-radius:14px;font-size:14px;font-weight:700;text-decoration:none;transition:all .4s var(--E);font-family:var(--f-en)!important;}
body.lang-ku .qr-lnk,body.lang-ar .qr-lnk{font-family:var(--f-ar-ui)!important;}
.qr-lnk:hover{box-shadow:0 8px 28px rgba(48,59,151,.55);transform:translateY(-2px);color:#fff;}

/* ABOUT */
.abt-sec{padding:140px 60px;background:linear-gradient(180deg,var(--PD) 0%,var(--deep) 100%);}
.abt-g{display:grid;grid-template-columns:1.35fr 1fr;gap:100px;align-items:start;max-width:1280px;margin:0 auto;}
.abt-p{font-size:16px;line-height:2;color:var(--dim);margin-bottom:22px;}
body.lang-ku .abt-p,body.lang-ar .abt-p{ font-family:var(--f-ar-ui)!important; font-size:16px!important;line-height:2.1!important; }
.qbar{margin-top:38px;padding:28px 32px;border-left:4px solid var(--G);background:rgba(212,175,55,.08);border-radius:0 14px 14px 0;}
body.rtl .qbar{border-left:none;border-right:4px solid var(--G);border-radius:14px 0 0 14px;}
.qbar p{ font-family:var(--f-en-disp)!important; font-size:22px;font-style:italic;color:var(--GP);line-height:1.65; }
body.lang-ku .qbar p,body.lang-ar .qbar p{ font-family:var(--f-ar)!important; font-size:19px!important;font-style:normal!important; line-height:1.85!important; }
.vals{display:flex;flex-direction:column;gap:3px;}
.vi{display:flex;align-items:center;gap:22px;padding:26px 30px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);transition:all .5s var(--E);cursor:default;}
.vi:hover{background:rgba(212,175,55,.09);border-color:rgba(212,175,55,.38);transform:translateX(14px);}
body.rtl .vi:hover{transform:translateX(-14px);}
.vico{width:60px;height:60px;background:rgba(212,175,55,.13);border:2px solid rgba(212,175,55,.38);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--G);font-size:22px;flex-shrink:0;transition:all .4s var(--E);}
.vi:hover .vico{background:var(--G);color:var(--PD);transform:scale(1.12) rotate(10deg);}
.vinfo h4{ font-family:var(--f-en-disp)!important; font-size:22px;font-weight:700;color:#fff;margin-bottom:4px; }
body.lang-ku .vinfo h4,body.lang-ar .vinfo h4{ font-family:var(--f-ar)!important; font-size:19px!important;line-height:1.4!important; }
.vinfo span{font-size:12px;letter-spacing:1px;text-transform:uppercase;color:rgba(255,255,255,.5);}
body.lang-ku .vinfo span,body.lang-ar .vinfo span{ font-family:var(--f-ar-ui)!important;letter-spacing:0;font-size:13px;text-transform:none; }

/* CTA */
.rdr-sec{padding:120px 60px;background:var(--PM);position:relative;overflow:hidden;}
.rdr-sec::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 60% 80% at 85% 50%,rgba(212,175,55,.18) 0%,transparent 70%),radial-gradient(ellipse 40% 60% at 15% 50%,rgba(6,8,20,.75) 0%,transparent 70%);}
.rdr-in{max-width:1120px;margin:0 auto;position:relative;z-index:1;display:grid;grid-template-columns:1fr auto;gap:80px;align-items:center;}
.rdr-ey{font-size:11px;letter-spacing:4px;text-transform:uppercase;color:var(--G);display:block;margin-bottom:14px;font-weight:700;}
body.lang-ku .rdr-ey,body.lang-ar .rdr-ey{font-family:var(--f-ar-ui)!important;letter-spacing:0;font-size:14px;text-transform:none;}
.rdr-t{ font-family:var(--f-en-disp)!important; font-size:clamp(32px,4vw,55px);font-weight:700; color:#fff;line-height:1.2;margin-bottom:16px; }
body.lang-ku .rdr-t,body.lang-ar .rdr-t{ font-family:var(--f-ar)!important; line-height:1.45!important; font-size:clamp(28px,3.5vw,48px)!important; }
.rdr-t strong{color:var(--G);}
.rdr-d{font-size:16px;color:rgba(255,255,255,.75);line-height:1.85;}
body.lang-ku .rdr-d,body.lang-ar .rdr-d{font-family:var(--f-ar-ui)!important;font-size:16px!important;line-height:2!important;}
.rdr-bs{display:flex;flex-direction:column;gap:14px;min-width:270px;}
.rdr-b1{display:flex;align-items:center;justify-content:center;gap:11px;padding:17px 36px;background:var(--G);color:var(--PD);font-weight:700;font-size:13px;letter-spacing:2px;text-transform:uppercase;border-radius:50px;text-decoration:none;transition:all .4s var(--E);box-shadow:0 8px 24px rgba(212,175,55,.48);font-family:var(--f-ar-ui);white-space:nowrap;}
body.lang-en .rdr-b1{font-family:var(--f-en);}
.rdr-b1:hover{background:var(--GL);transform:translateY(-4px);}
.rdr-b2{display:flex;align-items:center;justify-content:center;gap:11px;padding:16px 36px;border:2px solid rgba(255,255,255,.28);color:#fff;font-size:13px;letter-spacing:1.5px;text-transform:uppercase;border-radius:50px;text-decoration:none;transition:all .4s var(--E);font-family:var(--f-ar-ui);white-space:nowrap;background:rgba(0,0,0,.18);}
body.lang-en .rdr-b2{font-family:var(--f-en);}
.rdr-b2:hover{border-color:var(--G);color:var(--G);transform:translateY(-4px);background:rgba(212,175,55,.12);}

/* FOOTER */
footer{background:var(--deep);border-top:1px solid rgba(212,175,55,.18);padding:68px 60px 38px;}
.ft-in{max-width:1280px;margin:0 auto;}
.ft-top{display:flex;justify-content:space-between;align-items:flex-start;padding-bottom:46px;border-bottom:1px solid rgba(255,255,255,.08);margin-bottom:30px;gap:44px;flex-wrap:wrap;}
.ft-logo{display:flex;align-items:center;gap:10px;margin-bottom:14px;}
.ft-logo img{width:34px;height:34px;border-radius:50%;object-fit:contain;background:#fff;}
.ft-logo-name{font-family:var(--f-en-disp)!important;font-size:22px;font-weight:700;color:#fff;}
.ft-tag{font-size:14px;color:rgba(255,255,255,.55);line-height:1.85;max-width:250px;}
body.lang-ku .ft-tag,body.lang-ar .ft-tag{font-family:var(--f-ar-ui)!important;font-size:14.5px;line-height:2;}
.ft-col h5{font-size:11px;letter-spacing:2.5px;text-transform:uppercase;color:var(--G);margin-bottom:18px;font-weight:700;}
body.lang-ku .ft-col h5,body.lang-ar .ft-col h5{font-family:var(--f-ar-ui)!important;letter-spacing:0;font-size:13.5px;text-transform:none;}
.ft-col ul{list-style:none;display:flex;flex-direction:column;gap:11px;}
.ft-col a{font-size:14.5px;color:rgba(255,255,255,.62);text-decoration:none;transition:color .3s;}
body.lang-ku .ft-col a,body.lang-ar .ft-col a{font-family:var(--f-ar-ui)!important;font-size:14.5px;}
.ft-col a:hover{color:var(--G);}
.ft-bot{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;}
.ft-copy{font-size:13px;color:rgba(255,255,255,.42);font-family:var(--f-en)!important;}
.ft-copy span{color:var(--G);}
.ft-soc{display:flex;gap:10px;}
.soa{width:40px;height:40px;border-radius:50%;border:1px solid rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.58);font-size:15px;text-decoration:none;transition:all .4s var(--E);}
.soa:hover{border-color:var(--G);color:var(--G);transform:translateY(-4px);}

/* FAB */
.fab-w{position:fixed;bottom:32px;right:32px;z-index:900;display:flex;flex-direction:column;align-items:flex-end;gap:11px;}
body.rtl .fab-w{right:auto;left:32px;align-items:flex-start;}
.ftop{width:48px;height:48px;border-radius:50%;background:rgba(28,36,99,.92);border:1.5px solid rgba(212,175,55,.4);display:flex;align-items:center;justify-content:center;color:var(--G);font-size:17px;cursor:pointer;opacity:0;pointer-events:none;transition:all .4s var(--E);backdrop-filter:blur(10px);}
.ftop.show{opacity:1;pointer-events:auto;}
.ftop:hover{background:var(--G);color:var(--PD);transform:translateY(-5px);}

/* ═══════════════════════════════════════
   RESPONSIVE
═══════════════════════════════════════ */
@media(max-width: 1100px) {
  .svc-grid { grid-template-columns: 1fr 1fr; }
  .svc-card:last-child { grid-column: span 2; height: 380px; }
  .abt-g, .app-g { grid-template-columns: 1fr; gap: 60px; }
  .qr-card { max-width: 100%; }
}
@media(max-width: 1024px) {
  .rdr-in { grid-template-columns: 1fr; }
  .rdr-bs { flex-direction: row; flex-wrap: wrap; min-width: unset; }
  header { padding: 0 24px; }
  .nav-ul, .lang-sw, #nav-browse-btn { display: none; }
  .hbtn { display: flex; align-items: center; justify-content: center; }
  .stats-bar, .svc-sec, .app-sec, .abt-sec, .rdr-sec, footer { padding-left: 20px; padding-right: 20px; }
}
@media(max-width: 768px) {
  .hero-sec { min-height: 600px; }
  h1 { font-size: clamp(42px, 12vw, 76px); letter-spacing: -2px; }
  body.lang-ku h1, body.lang-ar h1 { font-size: clamp(38px, 10vw, 68px) !important; }

  /* Mobile Search Bar Restyling */
  .hs-bar { flex-direction: column; border-radius: 24px; padding: 10px; gap: 8px; }
  .hs-input-wrap { width: 100%; padding: 6px 14px; }
  .hs-go { width: 100%; padding: 14px; border-radius: 16px; }

  .hbtns { gap: 10px; }
  .hb1, .hb2 { padding: 13px 26px; font-size: 12px; }
  .stats-inner { flex-direction: column; gap: 30px; }
  .stat-div { width: 56px; height: 1px; }
  .svc-grid { grid-template-columns: 1fr; gap: 15px; }
  .svc-card:last-child { grid-column: span 1; }
  .svc-card { height: 380px; }
  .rdr-bs { flex-direction: column; min-width: unset; width: 100%; }
  .ft-top { flex-direction: column; gap: 28px; }
  .ft-bot { flex-direction: column; align-items: flex-start; }
  .fab-w { bottom: 20px; right: 18px; }
  body.rtl .fab-w { left: 18px; right: auto; }
}
@media(max-width: 480px) {
  .hbtns { flex-direction: column; width: 100%; max-width: 290px; margin: 0 auto; }
  .hb1, .hb2 { width: 100%; text-align: center; justify-content: center; }
  .hs-tab { padding: 9px 11px; font-size: 11.5px; }
  body.lang-ku h1, body.lang-ar h1 { font-size: clamp(34px, 11vw, 58px) !important; }
}
@supports(padding:max(0px)){
  header{padding-left:max(18px,env(safe-area-inset-left));padding-right:max(18px,env(safe-area-inset-right));}
  .fab-w{right:max(32px,env(safe-area-inset-right));}
  footer{padding-bottom:max(38px,env(safe-area-inset-bottom));}
}
</style>
</head>
<body class="lang-ku">

<header id="hdr">
<nav>
  <a href="{{ route('newindex') }}" class="logo">
    <img src="{{ asset('favicon.ico') }}" alt="Dream Mulk" onerror="this.src='https://cdn-icons-png.flaticon.com/512/2111/2111307.png'"/>
    <span class="logo-name">Dream Mulk</span>
  </a>
  <ul class="nav-ul">
    <li><a href="{{ route('newindex') }}"       class="{{ request()->routeIs('newindex') ? 'ac':'' }}"        id="nav-home" data-i18n="navHome">سەرەتا</a></li>
    <li><a href="{{ route('property.list') }}"  class="{{ request()->routeIs('property.list') ? 'ac':'' }}"  id="nav-props" data-i18n="navProps">خانووەکان</a></li>
    <li><a href="#app"     id="nav-app" data-i18n="navApp">ئەپ</a></li>
    <li><a href="{{ route('about-us') }}"       class="{{ request()->routeIs('about-us') ? 'ac':'' }}"        id="nav-about" data-i18n="navAbout">دەربارەی ئێمە</a></li>
    <li><a href="{{ route('contact-us') }}"     class="{{ request()->routeIs('contact-us') ? 'ac':'' }}"      id="nav-contact" data-i18n="navContact">پەیوەندی</a></li>
  </ul>
  <div class="nav-right">
    <div class="lang-sw">
      <button class="lang-btn active" data-lang="ku">کو</button>
      <button class="lang-btn" data-lang="en">EN</button>
      <button class="lang-btn" data-lang="ar">ع</button>
    </div>
    @php
      $user=\Illuminate\Support\Facades\Auth::guard('web')->user();
      $agent=\Illuminate\Support\Facades\Auth::guard('agent')->user();
      $office=\Illuminate\Support\Facades\Auth::guard('office')->user();
      $anyAuth=$user||$agent||$office;
      $unreadCount=0;$dashRoute='#';$logoutRoute='#';$displayName='';
      if($user){$dashRoute=route('user.profile');$logoutRoute=route('logout');$displayName=$user->username??$user->name??'U';$unreadCount=\DB::table('notifications')->where('user_id',$user->id)->where('is_read',false)->where(function($q){$q->whereNull('expires_at')->orWhere('expires_at','>',now());})->count();}
      elseif($agent){$dashRoute=route('agent.dashboard');$logoutRoute=route('agent.logout');$displayName=$agent->agent_name??$agent->name??'A';}
      elseif($office){$dashRoute=route('office.dashboard');$logoutRoute=route('office.logout');$displayName=$office->company_name??$office->name??'O';}
    @endphp
    @if($anyAuth)
      @if($user)
      <a href="{{ route('user.notifications') }}" class="bell-wrap">
        <i class="far fa-bell"></i>
        @if($unreadCount>0)<span class="bell-badge">{{ $unreadCount>99?'99+':$unreadCount }}</span>@endif
      </a>
      @endif
      <a href="{{ $dashRoute }}" class="av-btn">{{ strtoupper(substr($displayName,0,1)) }}</a>
      <form action="{{ $logoutRoute }}" method="POST" style="display:inline;">@csrf
        <button type="submit" class="btn-o" style="padding:8px 14px;cursor:pointer;border:none;background:none;color:var(--G);"><i class="fas fa-sign-out-alt"></i></button>
      </form>
    @else
      <a href="{{ route('property.list') }}" class="btn-o" id="nav-browse-btn" data-i18n="browseBtn">خانووەکان ببینە</a>
      <a href="{{ route('login-page') }}"    class="btn-s" id="nav-login-btn">
        <i class="fas fa-user"></i>
        <span data-i18n="loginBtn">چوونەژوورەوە</span>
      </a>
    @endif
  </div>
  <button class="hbtn" id="ham"><i class="fas fa-bars"></i></button>
</nav>
</header>

<div class="bkdp" id="bdp"></div>

<aside class="drw" id="drw">
  <div class="drw-hd">
    <span>Dream Mulk</span>
    <button class="drw-x" id="dx"><i class="fas fa-times"></i></button>
  </div>
  <div class="drw-lang">
    <button class="lang-btn active" data-lang="ku">کو</button>
    <button class="lang-btn" data-lang="en">EN</button>
    <button class="lang-btn" data-lang="ar">ع</button>
  </div>
  <nav class="drw-nav">
    <a href="{{ route('newindex') }}"      id="drw-home" data-i18n="navHome">سەرەتا</a>
    <a href="{{ route('property.list') }}" id="drw-props" data-i18n="navProps">خانووەکان</a>
    <a href="#app"                         id="drw-app" data-i18n="navApp">ئەپ</a>
    <a href="{{ route('about-us') }}"      id="drw-about" data-i18n="navAbout">دەربارەمان</a>
    <a href="{{ route('contact-us') }}"    id="drw-contact" data-i18n="navContact">پەیوەندی</a>
  </nav>
  <div class="drw-ft">
    @if(isset($anyAuth)&&$anyAuth)
      <a href="{{ $dashRoute }}" class="btn-s" style="text-align:center;justify-content:center;">Dashboard</a>
      <form action="{{ $logoutRoute }}" method="POST" style="width:100%;">@csrf
        <button type="submit" class="btn-o" style="width:100%;text-align:center;cursor:pointer;">Logout</button>
      </form>
    @else
      <a href="{{ route('login-page') }}"    class="btn-s" style="text-align:center;justify-content:center;" id="drw-login" data-i18n="loginBtn">چوونەژوورەوە</a>
      <a href="{{ route('property.list') }}" class="btn-o" style="text-align:center;"                         id="drw-browse" data-i18n="browseBtn">خانووەکان ببینە</a>
    @endif
  </div>
</aside>

<section class="hero-sec" id="home">
  <div class="hero-slider">
    <div class="slide"></div>
    <div class="slide"></div>
    <div class="slide"></div>
  </div>
  <div class="hero-overlay"></div>

  <div class="hc" id="hc">
    <div class="eyebrow gsap-reveal" id="t-eyebrow" data-i18n="eyebrow">خانوو و زەوی پریمیەم لە کوردستان</div>

    <h1 class="gsap-reveal" id="hero-title" dir="ltr" style="direction:ltr;unicode-bidi:embed;">
      DREAM <span class="g">MULK</span>
    </h1>

    <div class="kurd-sub gsap-reveal" id="t-sub1" data-i18n="sub1">بۆ کڕین، فرۆشتن و کرێدانی خانوو لە کوردستان — بێ کارمزد</div>
    <div class="sub gsap-reveal"      id="t-sub2" data-i18n="sub2">کوردستان &bull; هەولێر &bull; ٢٠٢٦</div>

    <div class="hero-search" id="hero-search-anim">
      <div class="hs-tabs-wrap">
        <div class="hs-tabs">
          <button class="hs-tab active" id="tab-buy"  onclick="setTab(this,'buy')" data-i18n="tabBuy">🏠 کڕین</button>
          <button class="hs-tab"        id="tab-rent" onclick="setTab(this,'rent')" data-i18n="tabRent">🔑 کرێ</button>
          <button class="hs-tab"        id="tab-sell" onclick="setTab(this,'sell')" data-i18n="tabSell">💰 فرۆشتن</button>
        </div>
      </div>
      <div class="hs-bar">
        <div class="hs-input-wrap">
          <i class="fas fa-search"></i>
          <input type="text" id="hs-input" placeholder="گەڕان لە هەولێر، سلێمانی..." autocomplete="off" inputmode="search"/>
        </div>
        <button id="hs-btn" class="hs-go" data-i18n="searchBtn">بگەڕێ</button>
      </div>
      <div class="hs-quick">
        <span id="t-popular" data-i18n="popular">شارەکان:</span>
        <a data-city="erbil"        id="q-erbil" data-i18n="erbil">هەولێر</a>
        <a data-city="sulaymaniyah" id="q-suli" data-i18n="suli">سلێمانی</a>
        <a data-city="duhok"        id="q-duhok" data-i18n="duhok">دهۆک</a>
      </div>
    </div>

    <div class="hbtns gsap-reveal">
      <a href="{{ route('property.list') }}" class="hb1" id="t-explore" data-i18n="explore">خانووەکان ببینە</a>
      <a href="#app"                         class="hb2" id="t-app" data-i18n="appBtn">ئەپەکە دابەزێنە</a>
    </div>
  </div>

  <div class="scrl gsap-reveal" id="scrl">
    <div class="mouse"></div>
    <span id="t-scroll" data-i18n="scroll">دابەزە</span>
  </div>
</section>

<div class="page-sections">
  <div class="stats-bar">
    <div class="stats-inner">
      <div class="stat-item gsap-scroll">
        <div class="stat-num" data-t="500" data-s="+">0+</div>
        <div class="stat-label" data-i18n="statLabel1">خانووی تۆمارکراو</div>
      </div>
      <div class="stat-div"></div>
      <div class="stat-item gsap-scroll" style="transition-delay:.1s">
        <div class="stat-num" data-t="150" data-s="+">0+</div>
        <div class="stat-label" data-i18n="statLabel2">ئەجێنتی پشتڕاستکراو</div>
      </div>
    </div>
  </div>

  <section class="svc-sec">
    <div class="sec-wrap">
      <div class="sec-hd gsap-scroll">
        <span class="stag" data-i18n="svcTag">خزمەتگوزاریەکانمان</span>
        <h2 class="stitle">
          <span data-i18n="svcTitle">چی</span> <em data-i18n="svcTitleEm">پێشکەش دەکەین</em>
        </h2>
      </div>
      <div class="svc-grid">
        <a href="{{ route('property.list') }}" class="svc-card gsap-scroll">
          <img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800&q=80" alt="" class="svc-img" loading="lazy">
          <div class="svc-body">
            <div class="svc-ico"><i class="fas fa-key"></i></div>
            <h3 class="svc-t" data-i18n="svc1Title">کڕینی خانوو</h3>
            <p class="svc-p" data-i18n="svc1Desc">خانووی خەونەکەت بدۆزەرەوە. لیستی تایبەت لە سەرانسەری کوردستان بە نرخی ئاشکرا و بێ نهێنی.</p>
            <div class="svc-cta"><span data-i18n="svc1Cta">بگەڕێ</span> <i class="fas fa-arrow-right"></i></div>
          </div>
        </a>
        <a href="{{ route('login-page') }}" class="svc-card gsap-scroll" style="transition-delay:.1s">
          <img src="https://images.unsplash.com/photo-1582407947304-fd86f028f716?w=800&q=80" alt="" class="svc-img" loading="lazy">
          <div class="svc-body">
            <div class="svc-ico"><i class="fas fa-tags"></i></div>
            <h3 class="svc-t" data-i18n="svc2Title">فرۆشتنی خانوو</h3>
            <p class="svc-p" data-i18n="svc2Desc">خانووەکەت تۆمار بکە و ڕاستەوخۆ بگەیە کڕیارە راستەقینەکان. بێ ناوەڕاست، بێ کۆمیسیۆن.</p>
            <div class="svc-cta"><span data-i18n="svc2Cta">تۆمار بکە</span> <i class="fas fa-arrow-right"></i></div>
          </div>
        </a>
        <a href="{{ route('property.list',['type'=>'rent']) }}" class="svc-card gsap-scroll" style="transition-delay:.2s">
          <img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&q=80" alt="" class="svc-img" loading="lazy">
          <div class="svc-body">
            <div class="svc-ico"><i class="fas fa-home"></i></div>
            <h3 class="svc-t" data-i18n="svc3Title">کرێدانی خانوو</h3>
            <p class="svc-p" data-i18n="svc3Desc">کرێی گونجاو بدۆزەرەوە بۆ ژیانت. لیستی پشتڕاستکراو بە نرخی ئاشکرا و مەرجی ڕوون.</p>
            <div class="svc-cta"><span data-i18n="svc3Cta">کرێ بدۆزەرەوە</span> <i class="fas fa-arrow-right"></i></div>
          </div>
        </a>
      </div>
    </div>
  </section>

  <section class="app-sec" id="app">
    <div class="app-g">
      <div class="gsap-scroll">
        <span class="stag" data-i18n="appTag">ئەپی مۆبایل</span>
        <h2 class="stitle" style="margin-bottom:18px;">
          <span data-i18n="appTitle">خانووەکان</span><br>
          <em data-i18n="appTitleEm">لەگەڵ تۆ</em>
        </h2>
        <p class="app-desc" data-i18n="appDesc">ئەپی Dream Mulk بازاڕی خانووبەرەی کوردستان دەهێنێتە نووکی پەنجەکانت. بگەڕێ، کاتی سەردانی خانوو دابنێ، و ڕاستەوخۆ پەیوەندی بکە بە فرۆشیارەکان.</p>
        <div class="app-feats">
          <div class="af"><i class="fas fa-check-circle"></i><span data-i18n="appF1">لیستی خانووی نوێ و ئاگادارکردنەوەی خێرا</span></div>
          <div class="af"><i class="fas fa-check-circle"></i><span data-i18n="appF2">کاتی بینینی خانوو بە ئەجێنت دابنێ</span></div>
          <div class="af"><i class="fas fa-check-circle"></i><span data-i18n="appF3">بە کوردی، عەرەبی و ئینگلیزی</span></div>
          <div class="af"><i class="fas fa-check-circle"></i><span data-i18n="appF4">پەیامرێنی پارێزراو و بەڵگەنامەکان</span></div>
          <div class="af"><i class="fas fa-check-circle"></i><span data-i18n="appF5">بێ کۆمیسیۆن — هەمیشە بەخۆڕایی</span></div>
        </div>
        <div class="sbtns">
          <a href="https://apps.apple.com/us/app/dream-mulk/id6756894199" target="_blank" rel="noopener" class="sbtn">
            <i class="fab fa-apple"></i>
            <div><div class="sbtn-sm" data-i18n="appStoreLabel">دابەزێنە لە</div><div class="sbtn-nm">App Store</div></div>
          </a>
          <a href="https://play.google.com/store/apps/details?id=com.dreammulk" target="_blank" rel="noopener" class="sbtn">
            <i class="fab fa-google-play"></i>
            <div><div class="sbtn-sm" data-i18n="playStoreLabel">وەربگرە لە</div><div class="sbtn-nm">Google Play</div></div>
          </a>
        </div>
      </div>
      <div class="gsap-scroll" style="transition-delay: 0.2s">
        <div class="qr-card">
          <div class="qr-brand">
            <div class="qr-ico"><i class="fab fa-apple"></i></div>
            <div><div class="qr-t">Dream Mulk</div><div class="qr-s" data-i18n="qrSub">بەخۆڕایی</div></div>
          </div>
          <div class="qr-div"></div>
          <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=https://apps.apple.com/us/app/dream-mulk/id6756894199&bgcolor=ffffff&color=1a225a&margin=10&format=png&qzone=1&ecc=M" alt="QR Code" class="qr-img" loading="lazy"/>
          <div class="qr-hint"><i class="fas fa-mobile-alt"></i><span data-i18n="qrHint">کامێراکەت بەرەو ئەمە بگرە</span></div>
          <div class="qr-div"></div>
          <a href="https://apps.apple.com/us/app/dream-mulk/id6756894199" target="_blank" rel="noopener" class="qr-lnk">
            <i class="fab fa-apple"></i> <span data-i18n="qrBtn">بکرەوە لە App Store</span> <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>
  </section>

  <section class="abt-sec" id="about">
    <div class="abt-g">
      <div class="gsap-scroll">
        <span class="stag" data-i18n="abtTag">چیرۆکی ئێمە</span>
        <h2 class="stitle">
          <span data-i18n="abtTitle">Dream Mulk</span><br>
          <em data-i18n="abtTitleEm">کێیە؟</em>
        </h2>
        <p class="abt-p" style="margin-top:18px;" data-i18n="abtP1">Dream Mulk بە ئامانجێکی بەهێز دامەزراوە: ئاسانکردن و ئاشکراکردنی بازاڕی خانووبەرەی کوردستان. ئێمە کڕیار و فرۆشیار ڕاستەوخۆ پەیوەند دەکەین — بێ ناوەڕاست، بێ کۆمیسیۆن.</p>
        <p class="abt-p" data-i18n="abtP2">لە بازاڕێکدا کە زۆرجار ئاڵۆز و تاریکە، ئێمە ڕووناکی و شەفافیەت دەهێنینەوە بۆ هەر مامەڵەیەک.</p>
        <div class="qbar"><p data-i18n="abtQuote">«خانوو زەوییە، بەڵام "مولک" مێژوویە. یارمەتیت دەدەین کە مێژووی خۆت بنووسیت.»</p></div>
      </div>
      <div class="vals gsap-scroll" style="transition-delay: 0.2s">
        <div class="vi"><div class="vico"><i class="fas fa-crown"></i></div><div class="vinfo"><h4 data-i18n="val1Title">تایبەتمەندی</h4><span data-i18n="val1Sub">خانووی هەڵبژێردراو</span></div></div>
        <div class="vi"><div class="vico"><i class="fas fa-handshake"></i></div><div class="vinfo"><h4 data-i18n="val2Title">ئامانجداری</h4><span data-i18n="val2Sub">ئاشکرایی تەواو</span></div></div>
        <div class="vi"><div class="vico"><i class="fas fa-mobile-alt"></i></div><div class="vinfo"><h4 data-i18n="val3Title">تەکنەلۆژیا</h4><span data-i18n="val3Sub">ئەپ و وێبگەی مۆدێرن</span></div></div>
        <div class="vi"><div class="vico"><i class="fas fa-map-marked-alt"></i></div><div class="vinfo"><h4 data-i18n="val4Title">لە هەولێر</h4><span data-i18n="val4Sub">دامەزراوە ٢٠٢٦</span></div></div>
      </div>
    </div>
  </section>

  <section class="rdr-sec" id="contact">
    <div class="rdr-in">
      <div class="gsap-scroll">
        <span class="rdr-ey" data-i18n="rdrTag">بۆ کۆمپانیاکانی خانووبەرە</span>
        <h2 class="rdr-t">
          <span data-i18n="rdrTitle">کاروبارەکەت بگەشێنە</span><br>
          <span data-i18n="rdrWith">لەگەڵ</span> <strong>Dream Mulk</strong>
        </h2>
        <p class="rdr-d" data-i18n="rdrDesc">کۆمپانیاکەت تۆمار بکە و بگەیە بە هەزاران کڕیار و کرێیار لە سەرانسەری کوردستان. خانوو لیست بکە، ئەجێنت بەڕێوەبەرە، مامەڵەکان تەواو بکە.</p>
      </div>
      <div class="rdr-bs gsap-scroll" style="transition-delay: 0.2s">
        <a href="{{ route('office.login') }}"  class="rdr-b1"><i class="fas fa-building"></i> <span data-i18n="rdrBtn1">چوونەژوورەوەی کۆمپانیا</span></a>
        <a href="{{ route('property.list') }}" class="rdr-b2"><i class="fas fa-search"></i>   <span data-i18n="rdrBtn2">بگەڕێ بەبێ تۆمارکردن</span></a>
      </div>
    </div>
  </section>

  <footer>
    <div class="ft-in">
      <div class="ft-top">
        <div>
          <div class="ft-logo">
            <img src="{{ asset('favicon.ico') }}" alt="Dream Mulk" onerror="this.src='https://cdn-icons-png.flaticon.com/512/2111/2111307.png'"/>
            <span class="ft-logo-name">Dream Mulk</span>
          </div>
          <p class="ft-tag" data-i18n="ftTag">باشترین پلاتفۆرمی خانووبەرەی کوردستان. بێ کارمزد. بێ کۆمیسیۆن. بێ نهێنی.</p>
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
            <li><a href="{{ route('property.list') }}"                   data-i18n="ftLink5">کڕینی خانوو</a></li>
            <li><a href="{{ route('login-page') }}"                      data-i18n="ftLink6">فرۆشتنی خانوو</a></li>
            <li><a href="{{ route('property.list',['type'=>'rent']) }}"  data-i18n="ftLink7">کرێدانی خانوو</a></li>
            <li><a href="{{ route('agents.list') }}"                     data-i18n="ftLink8">ئەجێنت بدۆزەرەوە</a></li>
          </ul>
        </div>
        <div class="ft-col">
          <h5 data-i18n="ftCol3">ئەپەکە دابەزێنە</h5>
          <ul>
            <li><a href="https://apps.apple.com/us/app/dream-mulk/id6756894199" target="_blank" rel="noopener"><i class="fab fa-apple"></i> App Store</a></li>
            <li><a href="https://play.google.com/store/apps/details?id=com.dreammulk" target="_blank" rel="noopener"><i class="fab fa-google-play"></i> Google Play</a></li>
            <li><a href="{{ route('contact-us') }}" data-i18n="ftLink9">پەیوەندیمان پێوە بکە</a></li>
          </ul>
        </div>
      </div>
      <div class="ft-bot">
        <div class="ft-copy">© {{ date('Y') }} <span>Dream Mulk</span>. Erbil, Kurdistan Region of Iraq.</div>
        <div class="ft-soc">
          <a href="https://www.facebook.com/share/1CGLEbK7qh/"                  target="_blank" rel="noopener" class="soa"><i class="fab fa-facebook-f"></i></a>
          <a href="https://www.instagram.com/dream_mulk?igsh=MWt4YXd1eTN4NW5j"  target="_blank" rel="noopener" class="soa"><i class="fab fa-instagram"></i></a>
        </div>
      </div>
    </div>
  </footer>
</div>

<div class="fab-w">
  <div class="ftop" id="btt"><i class="fas fa-arrow-up"></i></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

  /* ── 1. LENIS SMOOTH SCROLL ── */
  const lenis = new Lenis({
    duration: 1.2,
    easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
    smoothWheel: true,
    smoothTouch: false,
  });
  lenis.on('scroll', ScrollTrigger.update);
  gsap.ticker.add((time) => { lenis.raf(time * 1000); });
  gsap.ticker.lagSmoothing(0);

  /* ── 2. SCROLL ANCHORS ── */
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
      const id = a.getAttribute('href');
      if(id && id.length > 1){
        const target = document.querySelector(id);
        if(target){ e.preventDefault(); lenis.scrollTo(target, {duration:1.2}); }
      }
    });
  });

  /* ── 3. HEADER + BTT ── */
  const hdr = document.getElementById('hdr');
  const btt = document.getElementById('btt');
  lenis.on('scroll', (e) => {
    hdr.classList.toggle('sc', e.animatedScroll > 60);
    btt.classList.toggle('show', e.animatedScroll > 400);
  });
  btt.addEventListener('click', () => lenis.scrollTo(0, {duration:1.2}));

  /* ── 4. DRAWER ── */
  const ham=document.getElementById('ham'), drw=document.getElementById('drw'),
        bdp=document.getElementById('bdp'), dx=document.getElementById('dx');
  const openD  = () => { drw.classList.add('on'); bdp.classList.add('on'); document.body.style.overflow='hidden'; };
  const closeD = () => { drw.classList.remove('on'); bdp.classList.remove('on'); document.body.style.overflow=''; };
  ham.addEventListener('click', openD);
  dx.addEventListener('click', closeD);
  bdp.addEventListener('click', closeD);
  drw.querySelectorAll('a').forEach(a => a.addEventListener('click', closeD));
  window.addEventListener('resize', () => { if(window.innerWidth > 1000) closeD(); });

  /* ── 5. OPTIMIZED GSAP ANIMATIONS ── */

  // Set initial invisible states to prevent FOUC
  gsap.set('.gsap-reveal', { y: 30, opacity: 0 });
  gsap.set('#hero-search-anim .hs-tabs-wrap', { y: 20, opacity: 0 });
  // Clip-path inset(top right bottom left) -> 100% on the right means it is fully clipped (hidden)
  gsap.set('#hero-search-anim .hs-bar', { clipPath: 'inset(0% 100% 0% 0%)', x: -30, opacity: 0 });
  gsap.set('#hero-search-anim .hs-quick', { y: 15, opacity: 0 });

  const heroTl = gsap.timeline({ delay: 0.1 });

  // 1. Reveal header text
  heroTl.to('.gsap-reveal', {
    y: 0, opacity: 1, duration: 1, stagger: 0.1,
    ease: "power3.out", clearProps: "transform"
  })
  // 2. Fade in search tabs
  .to('#hero-search-anim .hs-tabs-wrap', {
    y: 0, opacity: 1, duration: 0.8, ease: "power3.out", clearProps: "transform"
  }, "-=0.6")
  // 3. The Left-to-Right "Easy In, Hard Out" cinematic wipe for the search pill
  .to('#hero-search-anim .hs-bar', {
    clipPath: 'inset(0% 0% 0% 0%)', // Expands to show the whole element
    x: 0, opacity: 1,
    duration: 1.4,
    ease: "expo.inOut", // This gives the luxurious slow-start, fast-middle, smooth-end feel
    clearProps: "transform,clipPath" // Clears so hover/focus effects aren't broken by GSAP styles
  }, "-=0.6")
  // 4. Fade in quick links underneath
  .to('#hero-search-anim .hs-quick', {
    y: 0, opacity: 1, duration: 0.8, ease: "power3.out", clearProps: "transform"
  }, "-=0.9");

  // Scroll triggers for the rest of the page
  const scrollElements = document.querySelectorAll('.gsap-scroll');
  scrollElements.forEach(el => {
    gsap.fromTo(el,
      { y: 40, opacity: 0 },
      {
        y: 0, opacity: 1, duration: 0.8, ease: "power2.out",
        scrollTrigger: {
          trigger: el,
          start: "top 85%",
          toggleActions: "play none none none"
        }
      }
    );
  });

  /* ── 6. STAT COUNTERS ── */
  let statsDone = false;
  ScrollTrigger.create({
    trigger:'.stats-bar', start:'top 85%',
    onEnter:() => {
      if(statsDone) return; statsDone = true;
      document.querySelectorAll('.stat-num[data-t]').forEach(el => {
        const target = parseInt(el.dataset.t), suffix = el.dataset.s||'';
        const obj = {v:0};
        gsap.to(obj, { v:target, duration:2, ease:'power3.out',
          onUpdate(){ el.textContent = Math.round(obj.v) + suffix; }
        });
      });
    }
  });

});

/* ══════════════════════════════════════
   SEARCH
══════════════════════════════════════ */
var currentTab = 'buy';
window.setTab = function(el, type){
  document.querySelectorAll('.hs-tab').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
  currentTab = type;
  const T = I18N[currentLang] || I18N['ku'];
  const inp = document.getElementById('hs-input');
  if(inp) inp.placeholder = {buy:T.placeholderBuy,rent:T.placeholderRent,sell:T.placeholderSell}[type] || T.placeholderBuy;
};
function doSearch(){
  const kw = document.getElementById('hs-input').value.trim();
  const base = '{{ route("property.list") }}';
  const p = new URLSearchParams();
  if(currentTab === 'rent') p.set('type','rent');
  else if(currentTab === 'buy') p.set('type','sell');
  if(kw) p.set('search', kw);
  window.location.href = base + (p.toString() ? '?'+p.toString() : '');
}
document.getElementById('hs-btn').addEventListener('click', doSearch);
document.getElementById('hs-input').addEventListener('keydown', e => { if(e.key==='Enter'){ e.preventDefault(); doSearch(); }});
document.querySelectorAll('.hs-quick a[data-city]').forEach(a => {
  a.addEventListener('click', e => {
    e.preventDefault();
    const p = new URLSearchParams();
    if(currentTab === 'rent') p.set('type','rent');
    else if(currentTab === 'buy') p.set('type','sell');
    p.set('city', a.dataset.city);
    window.location.href = '{{ route("property.list") }}?' + p.toString();
  });
});

/* ══════════════════════════════════════
   I18N FULL DICTIONARY
══════════════════════════════════════ */
var currentLang = 'ku';
const I18N = {
  ku:{
    dir:'rtl',
    eyebrow:'خانوو و زەوی پریمیەم لە کوردستان',
    sub1:'بۆ کڕین، فرۆشتن و کرێدانی خانوو لە کوردستان — بێ کارمزد',
    sub2:'کوردستان • هەولێر • ٢٠٢٦',
    tabBuy:'🏠 کڕین',tabRent:'🔑 کرێ',tabSell:'💰 فرۆشتن',
    searchBtn:'بگەڕێ',
    placeholderBuy:'بگەڕێ... هەولێر، سلێمانی، دهۆک',
    placeholderRent:'خانوی کرێ بدۆزەرەوە...',
    placeholderSell:'خانووەکەت بفرۆشە...',
    popular:'شارەکان:',erbil:'هەولێر',suli:'سلێمانی',duhok:'دهۆک',
    explore:'خانووەکان ببینە',appBtn:'ئەپەکە دابەزێنە',scroll:'دابەزە',
    navHome:'سەرەتا',navProps:'خانووەکان',navApp:'ئەپ',navAbout:'دەربارەمان',navContact:'پەیوەندی',
    loginBtn:'چوونەژوورەوە',browseBtn:'خانووەکان ببینە',
    statLabel1:'خانووی تۆمارکراو',statLabel2:'ئەجێنتی پشتڕاستکراو',
    svcTag:'خزمەتگوزاریەکانمان',svcTitle:'چی',svcTitleEm:'پێشکەش دەکەین',
    svc1Title:'کڕینی خانوو',svc1Desc:'خانووی خەونەکەت بدۆزەرەوە. لیستی تایبەت لە سەرانسەری کوردستان بە نرخی ئاشکرا و بێ نهێنی.',svc1Cta:'بگەڕێ',
    svc2Title:'فرۆشتنی خانوو',svc2Desc:'خانووەکەت تۆمار بکە و ڕاستەوخۆ بگەیە کڕیارە راستەقینەکان. بێ ناوەڕاست، بێ کۆمیسیۆن.',svc2Cta:'تۆمار بکە',
    svc3Title:'کرێدانی خانوو',svc3Desc:'کرێی گونجاو بدۆزەرەوە بۆ ژیانت. لیستی پشتڕاستکراو بە نرخی ئاشکرا و مەرجی ڕوون.',svc3Cta:'کرێ بدۆزەرەوە',
    appTag:'ئەپی مۆبایل',appTitle:'خانووەکان',appTitleEm:'لەگەڵ تۆ',
    appDesc:'ئەپی Dream Mulk بازاڕی خانووبەرەی کوردستان دەهێنێتە نووکی پەنجەکانت. بگەڕێ، کاتی سەردانی خانوو دابنێ، و ڕاستەوخۆ پەیوەندی بکە بە فرۆشیارەکان.',
    appF1:'لیستی خانووی نوێ و ئاگادارکردنەوەی خێرا',appF2:'کاتی بینینی خانوو بە ئەجێنت دابنێ',
    appF3:'بە کوردی، عەرەبی و ئینگلیزی',appF4:'پەیامرێنی پارێزراو و بەڵگەنامەکان',appF5:'بێ کۆمیسیۆن — هەمیشە بەخۆڕایی',
    appStoreLabel:'دابەزێنە لە',playStoreLabel:'وەربگرە لە',
    qrSub:'بەخۆڕایی',qrHint:'کامێراکەت بەرەو ئەمە بگرە',qrBtn:'بکرەوە لە App Store',
    abtTag:'چیرۆکی ئێمە',abtTitle:'Dream Mulk',abtTitleEm:'کێیە؟',
    abtP1:'Dream Mulk بە ئامانجێکی بەهێز دامەزراوە: ئاسانکردن و ئاشکراکردنی بازاڕی خانووبەرەی کوردستان. ئێمە کڕیار و فرۆشیار ڕاستەوخۆ پەیوەند دەکەین — بێ ناوەڕاست، بێ کۆمیسیۆن.',
    abtP2:'لە بازاڕێکدا کە زۆرجار ئاڵۆز و تاریکە، ئێمە ڕووناکی و شەفافیەت دەهێنینەوە بۆ هەر مامەڵەیەک.',
    abtQuote:'«خانوو زەوییە، بەڵام "مولک" مێژوویە. یارمەتیت دەدەین کە مێژووی خۆت بنووسیت.»',
    val1Title:'تایبەتمەندی',val1Sub:'خانووی هەڵبژێردراو',val2Title:'ئامانجداری',val2Sub:'ئاشکرایی تەواو',val3Title:'تەکنەلۆژیا',val3Sub:'ئەپ و وێبگەی مۆدێرن',val4Title:'لە هەولێر',val4Sub:'دامەزراوە ٢٠٢٦',
    rdrTag:'بۆ کۆمپانیاکانی خانووبەرە',rdrTitle:'کاروبارەکەت بگەشێنە',rdrWith:'لەگەڵ',
    rdrDesc:'کۆمپانیاکەت تۆمار بکە و بگەیە بە هەزاران کڕیار و کرێیار لە سەرانسەری کوردستان. خانوو لیست بکە، ئەجێنت بەڕێوەبەرە، مامەڵەکان تەواو بکە.',
    rdrBtn1:'چوونەژوورەوەی کۆمپانیا',rdrBtn2:'بگەڕێ بەبێ تۆمارکردن',
    ftTag:'باشترین پلاتفۆرمی خانووبەرەی کوردستان. بێ کارمزد. بێ کۆمیسیۆن. بێ نهێنی.',
    ftCol1:'پلاتفۆرم',ftCol2:'خزمەتگوزاری',ftCol3:'ئەپەکە دابەزێنە',
    ftLink1:'خانووەکان ببینە',ftLink2:'چوونەژوورەوەی کڕیار',ftLink3:'پۆرتاڵی ئەجێنت',ftLink4:'دەربارەی ئێمە',
    ftLink5:'کڕینی خانوو',ftLink6:'فرۆشتنی خانوو',ftLink7:'کرێدانی خانوو',ftLink8:'ئەجێنت بدۆزەرەوە',ftLink9:'پەیوەندیمان پێوە بکە',
  },
  en:{
    dir:'ltr',
    eyebrow:'Premium Real Estate',
    sub1:'Buy, sell & rent properties across Kurdistan — zero commission',
    sub2:'Kurdistan • Erbil • Est. 2026',
    tabBuy:'🏠 Buy',tabRent:'🔑 Rent',tabSell:'💰 Sell',
    searchBtn:'Search',
    placeholderBuy:'Search in Erbil, Sulaymaniyah...',
    placeholderRent:'Find rentals in Kurdistan...',
    placeholderSell:'List your property...',
    popular:'Popular:',erbil:'Erbil',suli:'Sulaymaniyah',duhok:'Duhok',
    explore:'Explore Properties',appBtn:'Download App',scroll:'Scroll',
    navHome:'Home',navProps:'Properties',navApp:'App',navAbout:'About Us',navContact:'Contact',
    loginBtn:'Client Login',browseBtn:'Browse Properties',
    statLabel1:'Listed Properties',statLabel2:'Verified Agents',
    svcTag:'Our Services',svcTitle:'What We',svcTitleEm:'Offer',
    svc1Title:'Buy a Property',svc1Desc:'Find your dream home with advanced filters. Exclusive listings across Kurdistan.',svc1Cta:'Explore',
    svc2Title:'Sell a Property',svc2Desc:'List your property and reach serious buyers. No middlemen, no commissions.',svc2Cta:'List Now',
    svc3Title:'Rent a Property',svc3Desc:'Find the right rental at the right price. Verified listings with transparent terms.',svc3Cta:'Find Rentals',
    appTag:'Mobile App',appTitle:'Properties',appTitleEm:'With You',
    appDesc:'The Dream Mulk app puts Kurdistan\'s real estate market in your pocket. Search, book viewings, and contact sellers directly.',
    appF1:'Live property listings & instant alerts',appF2:'Book property viewings instantly',
    appF3:'Kurdish, Arabic & English',appF4:'Secure messaging & documents',appF5:'Zero commission — always free',
    appStoreLabel:'Download on the',playStoreLabel:'Get it on',
    qrSub:'Free Download',qrHint:'Point camera to scan',qrBtn:'Open in App Store',
    abtTag:'About Us',abtTitle:'Dream Mulk',abtTitleEm:'Story',
    abtP1:'Dream Mulk was built to make Kurdistan\'s property market simpler and more transparent. We connect buyers and sellers directly — no middlemen, no commissions.',
    abtP2:'In a market full of complexity, we bring clarity and technology to every transaction.',
    abtQuote:'"Property is land, but Mulk is legacy. We help you write yours."',
    val1Title:'Exclusive',val1Sub:'Curated listings',val2Title:'Integrity',val2Sub:'Full transparency',val3Title:'Technology',val3Sub:'Modern app & platform',val4Title:'Erbil Based',val4Sub:'Est. 2026',
    rdrTag:'For Real Estate Offices',rdrTitle:'Grow Your Business',rdrWith:'With',
    rdrDesc:'Register your office and reach thousands of buyers and renters across Kurdistan.',
    rdrBtn1:'Office Login',rdrBtn2:'Browse Without Login',
    ftTag:'Kurdistan\'s real estate platform. No fees. No commissions. Always transparent.',
    ftCol1:'Platform',ftCol2:'Services',ftCol3:'Download App',
    ftLink1:'Browse Properties',ftLink2:'Client Login',ftLink3:'Agent Portal',ftLink4:'About Us',
    ftLink5:'Buy Property',ftLink6:'Sell Property',ftLink7:'Rent Property',ftLink8:'Find an Agent',ftLink9:'Contact Us',
  },
  ar:{
    dir:'rtl',
    eyebrow:'عقارات كردستان المتميزة',
    sub1:'شراء وبيع وإيجار العقارات في كردستان — بدون عمولة',
    sub2:'كردستان • أربيل • ٢٠٢٦',
    tabBuy:'🏠 شراء',tabRent:'🔑 إيجار',tabSell:'💰 بيع',
    searchBtn:'ابحث الآن',
    placeholderBuy:'ابحث في أربيل، السليمانية، دهوك...',
    placeholderRent:'ابحث عن شقق وبيوت للإيجار...',
    placeholderSell:'أضف عقارك وابدأ البيع...',
    popular:'أشهر المدن:',erbil:'أربيل',suli:'السليمانية',duhok:'دهوك',
    explore:'استعرض العقارات',appBtn:'حمّل التطبيق',scroll:'اكتشف المزيد',
    navHome:'الرئيسية',navProps:'العقارات',navApp:'التطبيق',navAbout:'من نحن',navContact:'تواصل معنا',
    loginBtn:'تسجيل الدخول',browseBtn:'استعرض العقارات',
    statLabel1:'عقارات مدرجة',statLabel2:'وكلاء موثقون',
    svcTag:'خدماتنا العقارية',svcTitle:'ماذا',svcTitleEm:'نقدم لك',
    svc1Title:'شراء عقار',svc1Desc:'اعثر على منزل أحلامك بفلاتر ذكية. قوائم حصرية في كردستان بأسعار شفافة وموثوقة.',svc1Cta:'ابحث الآن',
    svc2Title:'بيع عقار',svc2Desc:'أدرج عقارك وتواصل مع مشترين جادين مباشرة دون وسطاء أو عمولات مخفية.',svc2Cta:'أضف عقارك',
    svc3Title:'إيجار عقار',svc3Desc:'ابحث عن إيجار يناسبك بسعر واضح. قوائم موثقة بشروط شفافة وضمانات حقيقية.',svc3Cta:'ابحث عن إيجار',
    appTag:'تطبيق الجوال',appTitle:'العقارات',appTitleEm:'في متناول يدك',
    appDesc:'تطبيق Dream Mulk يضع سوق عقارات كردستان بأكمله بين يديك. تصفح، احجز مواعيد المعاينة، وتواصل مع البائعين مباشرة.',
    appF1:'قوائم عقارات فورية وإشعارات لحظية',appF2:'احجز موعد معاينة بخطوة واحدة',
    appF3:'يدعم العربية والكردية والإنجليزية',appF4:'تراسل آمن ووثائق رسمية',appF5:'بدون عمولة — مجاني تماماً',
    appStoreLabel:'حمّل من',playStoreLabel:'احصل عليه من',
    qrSub:'تحميل مجاني',qrHint:'وجّه الكاميرا للمسح',qrBtn:'افتح في App Store',
    abtTag:'قصتنا',abtTitle:'Dream Mulk',abtTitleEm:'من نحن',
    abtP1:'أسسنا Dream Mulk لجعل سوق العقارات في كردستان أكثر شفافية وسهولة. نربط المشترين بالبائعين مباشرة — دون وسطاء ودون عمولات.',
    abtP2:'في سوق يكتنفه التعقيد، نجلب الوضوح والتقنية الحديثة لكل صفقة عقارية.',
    abtQuote:'«العقار أرض، لكن المُلك إرث. نساعدك على بناء إرثك.»',
    val1Title:'الحصرية',val1Sub:'عقارات مختارة بعناية',val2Title:'النزاهة',val2Sub:'شفافية تامة وموثوقية',val3Title:'التقنية',val3Sub:'تطبيق ومنصة حديثة',val4Title:'مقرنا أربيل',val4Sub:'تأسست عام ٢٠٢٦',
    rdrTag:'لشركات العقارات والمكاتب',rdrTitle:'طوّر أعمالك العقارية',rdrWith:'مع',
    rdrDesc:'سجّل شركتك وتواصل مع آلاف المشترين والمستأجرين في كردستان. أدر قوائمك ووكلاءك وصفقاتك من مكان واحد.',
    rdrBtn1:'دخول المكتب العقاري',rdrBtn2:'تصفح بدون تسجيل',
    ftTag:'منصة العقارات الأولى في كردستان. بدون رسوم. بدون عمولات. شفافية تامة.',
    ftCol1:'المنصة',ftCol2:'الخدمات',ftCol3:'حمّل التطبيق',
    ftLink1:'استعرض العقارات',ftLink2:'دخول العملاء',ftLink3:'بوابة الوكلاء',ftLink4:'من نحن',
    ftLink5:'شراء عقار',ftLink6:'بيع عقار',ftLink7:'إيجار عقار',ftLink8:'ابحث عن وكيل',ftLink9:'تواصل معنا',
  }
};

function setLang(lang){
  const T = I18N[lang]; if(!T) return;
  currentLang = lang;
  localStorage.setItem('dm_lang', lang);

  document.body.dir = T.dir;
  document.documentElement.lang = lang === 'ar' ? 'ar' : lang === 'ku' ? 'ku' : 'en';
  document.body.classList.remove('lang-ku','lang-en','lang-ar','rtl');
  document.body.classList.add('lang-' + lang);
  if(T.dir === 'rtl') document.body.classList.add('rtl');

  const htEl = document.getElementById('hero-title');
  if(htEl){ htEl.dir = 'ltr'; htEl.style.direction = 'ltr'; }

  document.querySelectorAll('.lang-btn').forEach(b => b.classList.toggle('active', b.dataset.lang === lang));

  document.querySelectorAll('[data-i18n]').forEach(el => {
    const k = el.getAttribute('data-i18n');
    if(T[k] === undefined) return;
    if(!el.children.length){ el.textContent = T[k]; }
    else { for(let n of el.childNodes){ if(n.nodeType===Node.TEXT_NODE&&n.textContent.trim()){ n.textContent=T[k]; break; } } }
  });

  const idMap = {
    't-eyebrow':'eyebrow','t-sub1':'sub1','t-sub2':'sub2',
    'tab-buy':'tabBuy','tab-rent':'tabRent','tab-sell':'tabSell',
    'hs-btn':'searchBtn','t-popular':'popular',
    'q-erbil':'erbil','q-suli':'suli','q-duhok':'duhok',
    't-explore':'explore','t-app':'appBtn','t-scroll':'scroll',
    'nav-home':'navHome','nav-props':'navProps','nav-app':'navApp','nav-about':'navAbout','nav-contact':'navContact',
    'drw-home':'navHome','drw-props':'navProps','drw-app':'navApp','drw-about':'navAbout','drw-contact':'navContact',
    'drw-login':'loginBtn','drw-browse':'browseBtn',
    'nav-login-btn':'loginBtn','nav-browse-btn':'browseBtn',
  };
  Object.entries(idMap).forEach(([id,key]) => {
    const el = document.getElementById(id);
    if(el && T[key] !== undefined) el.textContent = T[key];
  });

  const inp = document.getElementById('hs-input');
  if(inp) inp.placeholder = {buy:T.placeholderBuy,rent:T.placeholderRent,sell:T.placeholderSell}[currentTab] || T.placeholderBuy;
}

document.querySelectorAll('.lang-btn').forEach(b => b.addEventListener('click', () => setLang(b.dataset.lang)));
setLang(localStorage.getItem('dm_lang') || 'ku');
</script>
</body>
</html>
