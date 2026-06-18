<!DOCTYPE html>
<html lang="ku">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width,initial-scale=1.0,viewport-fit=cover" name="viewport"/>
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Dream Mulk — خانوو و زەوی پریمیەم</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@studio-freight/lenis@1.0.42/dist/lenis.min.js"></script>
<style>
:root {
  --blue:      #1A225A;
  --blue-mid:  #2a3298;
  --blue-light:#eef0f9;
  --blue-pale: #f4f5fb;
  --gold:      #C0A062;
  --gold-lt:   #d4b97a;
  --gold-pale: #fdf6e8;
  --white:     #ffffff;
  --bg:        #f8f9fc;
  --surface:   #ffffff;
  --border:    #e8eaf2;
  --text:      #0f1225;
  --text-2:    #4a5080;
  --text-3:    #8b91b8;
  --radius-sm: 12px;
  --radius-md: 20px;
  --radius-lg: 28px;
  --shadow-sm: 0 2px 8px rgba(26,34,90,0.07);
  --shadow-md: 0 8px 28px rgba(26,34,90,0.10);
  --shadow-lg: 0 20px 60px rgba(26,34,90,0.13);
  --ease:      cubic-bezier(0.22,1,0.36,1);
  --font:      'Vazirmatn', sans-serif;
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;-webkit-tap-highlight-color:transparent;}
html{overflow-x:clip;scroll-behavior:auto;}
body{background:var(--bg);color:var(--text);font-family:var(--font);overflow-x:clip;-webkit-font-smoothing:antialiased;direction:rtl;overscroll-behavior-y:none;}
::-webkit-scrollbar{width:6px;}
::-webkit-scrollbar-track{background:var(--bg);}
::-webkit-scrollbar-thumb{background:var(--border);border-radius:3px;}
img{display:block;max-width:100%;height:auto;}
a,button{touch-action:manipulation;}
i[class*="fa"]{font-family:"Font Awesome 6 Free","Font Awesome 6 Brands"!important;font-style:normal!important;}

/* ── HEADER ── */
header{
  position:fixed;top:0;left:0;right:0;z-index:1000;
  height:72px;padding:0 48px;
  background:rgba(255,255,255,0.95);
  backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
  border-bottom:1px solid var(--border);
  display:flex;align-items:center;
  transition:all 0.35s var(--ease);
  box-shadow:var(--shadow-sm);
}
header.sc{height:62px;box-shadow:var(--shadow-md);}
nav{max-width:1380px;width:100%;margin:0 auto;display:flex;align-items:center;justify-content:space-between;}

.logo{display:flex;align-items:center;gap:10px;text-decoration:none;}
.logo-mark{
  width:36px;height:36px;border-radius:10px;
  background:var(--blue);
  display:flex;align-items:center;justify-content:center;
  font-size:15px;font-weight:900;color:var(--gold);
  flex-shrink:0;transition:transform 0.3s var(--ease);
  font-family:var(--font)!important;
}
.logo-img{
  width:36px;height:36px;border-radius:10px;
  object-fit:contain;flex-shrink:0;
  background:var(--white);
  border:1.5px solid var(--border);
  padding:3px;
  transition:transform 0.3s var(--ease);
}
.logo:hover .logo-img,.logo:hover .logo-mark{transform:rotate(-8deg) scale(1.08);}
.ft-logo-img{
  width:32px;height:32px;border-radius:9px;
  object-fit:contain;flex-shrink:0;
  background:rgba(255,255,255,0.12);
  border:1px solid rgba(255,255,255,0.18);
  padding:3px;
}
.logo-name{font-size:17px;font-weight:800;color:var(--blue);letter-spacing:-0.3px;}

.nav-ul{display:flex;gap:2px;list-style:none;}
.nav-ul a{
  font-size:14px;font-weight:500;color:var(--text-2);
  text-decoration:none;padding:7px 14px;border-radius:9px;
  transition:all 0.22s;white-space:nowrap;
}
.nav-ul a:hover,.nav-ul a.ac{color:var(--blue);background:var(--blue-pale);}

.nav-right{display:flex;align-items:center;gap:10px;flex-shrink:0;}
.lang-sw{
  display:flex;gap:2px;
  background:var(--blue-pale);
  border:1px solid var(--border);
  border-radius:10px;padding:3px;
}
.lang-btn{
  padding:5px 11px;border-radius:7px;border:none;
  background:transparent;color:var(--text-3);
  font-size:12px;font-weight:700;cursor:pointer;
  transition:all 0.22s;font-family:var(--font)!important;
}
.lang-btn.active{background:var(--blue);color:#fff;}
.lang-btn:hover:not(.active){color:var(--blue);}

.bell-wrap{position:relative;color:var(--text-3);font-size:18px;text-decoration:none;line-height:1;transition:color 0.22s;}
.bell-wrap:hover{color:var(--blue);}
.bell-badge{position:absolute;top:-4px;right:-5px;background:#e74c3c;color:#fff;font-size:9px;font-weight:700;padding:2px 4px;border-radius:5px;line-height:1;}
.av-btn{
  width:34px;height:34px;border-radius:9px;
  background:var(--blue);color:var(--gold);
  display:flex;align-items:center;justify-content:center;
  font-weight:800;font-size:13px;text-decoration:none;
  transition:transform 0.25s;font-family:var(--font)!important;flex-shrink:0;
}
.av-btn:hover{transform:scale(1.08);}
.btn-ghost{
  padding:7px 16px;border:1.5px solid var(--border);
  border-radius:9px;color:var(--text-2);font-size:13px;
  text-decoration:none;transition:all 0.22s;white-space:nowrap;background:transparent;cursor:pointer;
}
.btn-ghost:hover{border-color:var(--blue-mid);color:var(--blue);}
.btn-primary{
  padding:8px 20px;border-radius:9px;
  background:var(--blue);color:#fff;
  font-size:13px;font-weight:700;text-decoration:none;
  display:flex;align-items:center;gap:7px;white-space:nowrap;
  transition:all 0.3s var(--ease);border:none;cursor:pointer;font-family:var(--font)!important;
}
.btn-primary:hover{background:var(--blue-mid);transform:translateY(-1px);box-shadow:0 6px 20px rgba(26,34,90,0.25);}
.hbtn{display:none;background:none;border:none;color:var(--blue);font-size:20px;cursor:pointer;padding:8px;line-height:1;}

/* ── DRAWER ── */
.bkdp{position:fixed;inset:0;background:rgba(26,34,90,0.35);opacity:0;pointer-events:none;transition:opacity 0.3s;z-index:1090;backdrop-filter:blur(4px);}
.bkdp.on{opacity:1;pointer-events:auto;}
.drw{
  position:fixed;top:0;right:-110%;height:100dvh;width:min(310px,86%);
  background:var(--white);z-index:1200;padding:26px 20px;
  display:flex;flex-direction:column;
  transition:right 0.38s var(--ease);
  border-left:1px solid var(--border);
  box-shadow:var(--shadow-lg);
  overflow-y:auto;visibility:hidden;
}
.drw.on{right:0;visibility:visible;}
.drw-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;}
.drw-x{background:none;border:none;color:var(--text-3);font-size:18px;cursor:pointer;padding:6px;line-height:1;}
.drw-lang{display:flex;gap:3px;margin-bottom:18px;background:var(--blue-pale);border-radius:10px;padding:3px;}
.drw-lang .lang-btn{flex:1;text-align:center;padding:8px 0;font-size:13px;border-radius:8px;}
.drw-nav{display:flex;flex-direction:column;}
.drw-nav a{display:block;padding:13px 4px;font-size:15px;font-weight:500;color:var(--text-2);border-bottom:1px solid var(--border);text-decoration:none;transition:all 0.22s;}
.drw-nav a:hover{color:var(--blue);padding-right:10px;}
body[dir="ltr"] .drw-nav a:hover{padding-right:4px;padding-left:10px;}
.drw-ft{margin-top:auto;padding-top:20px;display:flex;flex-direction:column;gap:9px;}

/* ── HERO ── */
.hero{
  position:relative;min-height:100vh;
  display:flex;align-items:center;justify-content:center;
  overflow:hidden;background:var(--blue);
  padding-top:72px;
}
.hero-slides{position:absolute;inset:0;z-index:0;}
.slide{
  position:absolute;inset:0;background-size:cover;background-position:center;
  opacity:0;animation:slideShow 18s infinite linear;
}
.slide:nth-child(1){background-image:url('https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1920&q=80');animation-delay:0s;}
.slide:nth-child(2){background-image:url('https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=1920&q=80');animation-delay:6s;}
.slide:nth-child(3){background-image:url('https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=1920&q=80');animation-delay:12s;}
@keyframes slideShow{
  0%{opacity:0;transform:scale(1.06);}
  8%{opacity:1;}
  30%{opacity:1;}
  38%{opacity:0;transform:scale(1);}
  100%{opacity:0;}
}
.hero-overlay{
  position:absolute;inset:0;z-index:1;
  background:linear-gradient(to bottom,rgba(26,34,90,0.72) 0%,rgba(26,34,90,0.45) 40%,rgba(26,34,90,0.75) 100%);
}
.hero-content{
  position:relative;z-index:10;text-align:center;
  max-width:820px;padding:0 24px;margin:0 auto;
}
.hero-badge{
  display:inline-flex;align-items:center;gap:8px;
  background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.2);
  border-radius:100px;padding:7px 18px;margin-bottom:26px;
  font-size:13px;font-weight:500;color:rgba(255,255,255,0.9);
  backdrop-filter:blur(10px);
}
.badge-dot{width:6px;height:6px;border-radius:50%;background:var(--gold);flex-shrink:0;animation:bdpulse 2s ease-in-out infinite;}
@keyframes bdpulse{0%,100%{opacity:1;transform:scale(1);}50%{opacity:0.4;transform:scale(0.6);}}
.hero-title{
  font-size:clamp(52px,10vw,108px);font-weight:900;
  line-height:1.0;color:#fff;margin-bottom:18px;
  letter-spacing:-2px;direction:ltr;
  text-shadow:0 4px 24px rgba(0,0,0,0.25);
}
.hero-title .accent{color:var(--gold);}
.hero-sub{font-size:clamp(16px,2vw,20px);color:rgba(255,255,255,0.8);line-height:1.75;margin-bottom:8px;}
.hero-meta{font-size:12px;font-weight:600;letter-spacing:3px;text-transform:uppercase;color:rgba(255,255,255,0.5);margin-bottom:36px;}
body.lang-ku .hero-meta,body.lang-ar .hero-meta{letter-spacing:0;font-size:13px;}

/* ── SEARCH BOX ── */
.search-box{
  background:rgba(255,255,255,0.97);
  border-radius:var(--radius-lg);
  padding:20px;margin-bottom:28px;
  box-shadow:0 24px 60px rgba(0,0,0,0.3);
  text-align:right;
}
.search-tabs{display:flex;gap:4px;margin-bottom:16px;background:var(--blue-pale);border-radius:var(--radius-sm);padding:4px;}
.s-tab{
  flex:1;padding:9px 16px;border-radius:9px;border:none;
  background:transparent;color:var(--text-2);
  font-size:14px;font-weight:600;cursor:pointer;
  transition:all 0.22s;white-space:nowrap;font-family:var(--font);
}
.s-tab:hover{color:var(--blue);}
.s-tab.active{background:var(--blue);color:#fff;box-shadow:0 2px 8px rgba(26,34,90,0.2);}
.search-row{
  display:flex;align-items:center;gap:10px;
  background:var(--bg);border:1.5px solid var(--border);
  border-radius:var(--radius-md);padding:6px 6px 6px 20px;
  transition:all 0.25s;
}
.search-row:focus-within{border-color:var(--blue-mid);background:#fff;box-shadow:0 0 0 3px rgba(26,34,90,0.08);}
.search-row i{color:var(--gold);font-size:15px;flex-shrink:0;}
.search-input{
  flex:1;border:none;outline:none;background:transparent;
  font-size:15px;color:var(--text);font-family:var(--font);padding:10px 0;
}
.search-input::placeholder{color:var(--text-3);}
.search-btn{
  padding:12px 28px;background:var(--blue);color:#fff;
  border:none;border-radius:14px;font-size:14px;font-weight:700;
  cursor:pointer;font-family:var(--font);flex-shrink:0;
  transition:all 0.28s var(--ease);
}
.search-btn:hover{background:var(--blue-mid);transform:scale(1.02);box-shadow:0 4px 14px rgba(26,34,90,0.3);}
.search-chips{display:flex;align-items:center;gap:8px;margin-top:14px;flex-wrap:wrap;}
.chips-label{font-size:12px;color:var(--text-3);font-weight:500;}
.chip{
  font-size:12px;color:var(--text-2);padding:5px 14px;
  border-radius:100px;background:var(--white);
  border:1.5px solid var(--border);cursor:pointer;
  text-decoration:none;transition:all 0.22s;font-family:var(--font);
}
.chip:hover{background:var(--blue);color:#fff;border-color:var(--blue);}
.hero-ctas{display:flex;align-items:center;justify-content:center;gap:12px;flex-wrap:wrap;}
.cta-primary{
  padding:14px 36px;background:var(--gold);color:var(--blue);
  font-weight:700;font-size:14px;border-radius:var(--radius-sm);
  text-decoration:none;transition:all 0.3s var(--ease);
  box-shadow:0 4px 20px rgba(192,160,98,0.4);
}
.cta-primary:hover{background:var(--gold-lt);transform:translateY(-2px);box-shadow:0 8px 28px rgba(192,160,98,0.5);}
.cta-secondary{
  padding:13px 36px;border:2px solid rgba(255,255,255,0.4);
  color:#fff;font-size:14px;font-weight:600;border-radius:var(--radius-sm);
  text-decoration:none;transition:all 0.3s var(--ease);
  backdrop-filter:blur(8px);background:rgba(255,255,255,0.08);
}
.cta-secondary:hover{border-color:rgba(255,255,255,0.8);background:rgba(255,255,255,0.15);transform:translateY(-2px);}
.scroll-hint{position:absolute;bottom:28px;left:50%;transform:translateX(-50%);z-index:10;display:flex;flex-direction:column;align-items:center;gap:7px;}
.scroll-hint span{font-size:10px;letter-spacing:3px;text-transform:uppercase;color:rgba(255,255,255,0.45);}
body.lang-ku .scroll-hint span,body.lang-ar .scroll-hint span{letter-spacing:0;font-size:12px;}
.scroll-line{width:1.5px;height:40px;background:linear-gradient(to bottom,rgba(192,160,98,0.9),transparent);animation:scrlpulse 2s ease-in-out infinite;}
@keyframes scrlpulse{0%,100%{opacity:1;}50%{opacity:0.3;}}

/* ── PAGE BODY ── */
.page-body{background:var(--bg);}

/* ── STATS ── */
.stats-sec{padding:0 48px;}
.stats-wrap{
  max-width:960px;margin:0 auto;
  display:flex;justify-content:center;
  background:var(--white);border:1px solid var(--border);
  border-radius:var(--radius-md);
  box-shadow:var(--shadow-md);
  transform:translateY(-44px);
  overflow:hidden;
}
.stat-item{flex:1;text-align:center;padding:30px 40px;}
.stat-item+.stat-item{border-right:1px solid var(--border);}
body[dir="ltr"] .stat-item+.stat-item{border-right:none;border-left:1px solid var(--border);}
.stat-num{font-size:clamp(36px,5vw,56px);font-weight:900;color:var(--blue);line-height:1;font-family:var(--font)!important;}
.stat-label{font-size:13px;color:var(--text-3);margin-top:6px;font-weight:500;}

/* ── SHARED SECTION ── */
.sec{padding:90px 48px;}
.sec-tag{
  display:inline-block;font-size:12px;font-weight:700;letter-spacing:1.5px;
  text-transform:uppercase;color:var(--blue-mid);
  background:var(--blue-light);border:1px solid rgba(42,50,152,0.15);
  padding:5px 14px;border-radius:100px;margin-bottom:12px;
}
body.lang-ku .sec-tag,body.lang-ar .sec-tag{letter-spacing:0;font-size:13px;}
.sec-title{font-size:clamp(26px,4vw,44px);font-weight:800;line-height:1.25;color:var(--text);}
.sec-title em{font-style:normal;color:var(--blue-mid);}
.sec-desc{font-size:15.5px;color:var(--text-2);line-height:1.85;margin-top:10px;}

/* ── SERVICES ── */
.svc-sec{background:var(--white);}
.svc-wrap{max-width:1320px;margin:0 auto;}
.svc-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:48px;}
.svc-card{
  border-radius:var(--radius-md);overflow:hidden;position:relative;
  height:460px;text-decoration:none;color:inherit;display:block;
  transition:transform 0.45s var(--ease),box-shadow 0.45s;
  box-shadow:var(--shadow-sm);
}
.svc-card::after{
  content:'';position:absolute;inset:0;
  background:linear-gradient(to top,rgba(10,14,48,0.93) 0%,rgba(10,14,48,0.25) 55%,transparent 100%);
  z-index:1;transition:opacity 0.4s;
}
.svc-img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;transition:transform 0.7s var(--ease);}
.svc-card:hover{transform:translateY(-8px);box-shadow:var(--shadow-lg);}
.svc-card:hover .svc-img{transform:scale(1.06);}
.svc-body{position:absolute;bottom:0;left:0;right:0;padding:30px;z-index:2;}
.svc-badge{
  display:inline-block;font-size:11px;font-weight:700;padding:4px 12px;
  background:rgba(192,160,98,0.18);border:1px solid rgba(192,160,98,0.35);
  border-radius:100px;color:var(--gold-lt);margin-bottom:12px;
}
.svc-title{font-size:22px;font-weight:800;margin-bottom:9px;color:#fff;}
.svc-desc{font-size:14px;line-height:1.7;color:rgba(255,255,255,0.65);margin-bottom:16px;}
.svc-link{
  display:inline-flex;align-items:center;gap:7px;
  font-size:13px;font-weight:700;color:var(--gold-lt);
  padding:8px 16px;border-radius:9px;
  background:rgba(192,160,98,0.12);border:1px solid rgba(192,160,98,0.25);
  transition:all 0.22s;
}
.svc-card:hover .svc-link{background:var(--gold);color:var(--blue);border-color:var(--gold);}

/* ── PROPERTY PREVIEW ── */
.props-sec{background:var(--bg);}
.props-wrap{max-width:1320px;margin:0 auto;}
.props-header{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;margin-bottom:38px;}
.view-all{
  display:inline-flex;align-items:center;gap:6px;
  font-size:13px;font-weight:700;color:var(--blue);
  text-decoration:none;padding:9px 20px;
  border:1.5px solid rgba(26,34,90,0.2);border-radius:10px;
  transition:all 0.22s;flex-shrink:0;white-space:nowrap;
}
.view-all:hover{background:var(--blue);color:#fff;border-color:var(--blue);}
/* ── PROPERTY CARDS ── */
.prop-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;}
.prop-card{
  background:var(--white);border:1px solid var(--border);
  border-radius:18px;overflow:hidden;
  text-decoration:none;color:inherit;
  transition:transform 0.38s var(--ease),box-shadow 0.38s;
  box-shadow:var(--shadow-sm);
}
.prop-card:hover{transform:translateY(-6px);box-shadow:var(--shadow-lg);border-color:rgba(26,34,90,0.12);}
.prop-img-wrap{position:relative;height:200px;overflow:hidden;}
.prop-img{width:100%;height:100%;object-fit:cover;transition:transform 0.55s var(--ease);}
.prop-card:hover .prop-img{transform:scale(1.05);}
/* Heart — top left */
.prop-heart{
  position:absolute;top:12px;left:12px;z-index:2;
  width:32px;height:32px;border-radius:50%;
  background:rgba(255,255,255,0.92);border:1px solid rgba(255,255,255,0.5);
  display:flex;align-items:center;justify-content:center;
  font-size:13px;color:var(--text-3);backdrop-filter:blur(8px);
}
/* Status badge — top right like Flutter app */
.prop-status{
  position:absolute;top:12px;right:12px;z-index:2;
  font-size:10px;font-weight:700;padding:4px 10px;border-radius:100px;
  display:flex;align-items:center;gap:5px;backdrop-filter:blur(8px);
  font-family:var(--font)!important;background:rgba(5,150,105,0.9);color:#fff;
}
.prop-status.rent{background:rgba(59,130,246,0.9);}
.prop-status-dot{width:5px;height:5px;border-radius:50%;background:rgba(255,255,255,0.7);}
.prop-body{padding:16px 18px;}
.prop-price-row{display:flex;align-items:baseline;gap:5px;margin-bottom:4px;}
.prop-price-num{font-size:22px;font-weight:900;color:var(--blue);line-height:1;font-family:var(--font)!important;}
.prop-price-suffix{font-size:12px;font-weight:500;color:var(--text-3);}
.prop-name{font-size:14px;font-weight:700;color:var(--text);margin-bottom:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.prop-loc{font-size:12px;color:var(--text-3);display:flex;align-items:center;gap:5px;margin-bottom:13px;}
.prop-loc i{font-size:10px;color:var(--gold);}
.prop-meta{display:flex;gap:14px;padding-top:12px;border-top:1px solid var(--border);}
.prop-meta-item{font-size:12px;color:var(--text-3);display:flex;align-items:center;gap:4px;font-weight:500;}
.prop-meta-item i{color:var(--blue-mid);font-size:11px;}

/* ── PHONE MOCKUP ── */
.app-phone-wrap{display:flex;align-items:center;justify-content:center;}
.phone-frame{
  width:250px;height:500px;border-radius:40px;
  background:#111;border:7px solid #222;
  box-shadow:0 0 0 2px rgba(255,255,255,0.07),0 40px 80px rgba(0,0,0,0.55);
  overflow:hidden;position:relative;
}
.phone-notch{position:absolute;top:10px;left:50%;transform:translateX(-50%);width:72px;height:20px;background:#000;border-radius:10px;z-index:10;}
.phone-screen{position:absolute;inset:0;overflow:hidden;}
.phone-img{width:100%;height:100%;object-fit:cover;}
.phone-overlay{position:absolute;bottom:0;left:0;right:0;background:linear-gradient(to top,rgba(10,14,45,0.96) 0%,transparent 60%);padding:22px 18px 26px;}
.phone-app-name{display:block;font-size:15px;font-weight:800;color:#fff;margin-bottom:3px;}
.phone-app-sub{display:block;font-size:11px;color:rgba(255,255,255,0.55);}

/* ── APP SECTION ── */
.app-sec{background:var(--blue);position:relative;overflow:hidden;}
.app-sec::before{
  content:'';position:absolute;inset:0;pointer-events:none;
  background:radial-gradient(ellipse 60% 80% at 80% 50%,rgba(192,160,98,0.12) 0%,transparent 70%);
}
.app-sec::after{
  content:'';position:absolute;top:-120px;left:-120px;
  width:400px;height:400px;border-radius:50%;
  border:1px solid rgba(255,255,255,0.05);pointer-events:none;
}
.app-grid{display:grid;grid-template-columns:1fr 1fr;gap:80px;align-items:center;max-width:1280px;margin:0 auto;position:relative;z-index:1;direction:ltr;}
.app-sec .sec-tag{background:rgba(192,160,98,0.15);border-color:rgba(192,160,98,0.3);color:var(--gold-lt);}
.app-sec .sec-title{color:#fff;}
.app-sec .sec-title em{color:var(--gold);}
.app-desc{font-size:15.5px;line-height:2;color:rgba(255,255,255,0.72);margin-bottom:30px;}
.app-feats{display:flex;flex-direction:column;gap:11px;margin-bottom:34px;}
.app-feat{display:flex;align-items:flex-start;gap:11px;font-size:14px;color:rgba(255,255,255,0.82);font-weight:500;line-height:1.6;}
.app-feat i{color:var(--gold);font-size:15px;flex-shrink:0;margin-top:2px;}
.store-btns{display:flex;gap:12px;flex-wrap:wrap;}
.store-btn{
  display:flex;align-items:center;gap:12px;padding:13px 20px;
  border:1.5px solid rgba(192,160,98,0.3);border-radius:var(--radius-sm);
  background:rgba(192,160,98,0.07);text-decoration:none;
  transition:all 0.32s var(--ease);
}
.store-btn:hover{background:rgba(192,160,98,0.18);border-color:var(--gold);transform:translateY(-3px);box-shadow:0 8px 24px rgba(0,0,0,0.25);}
.store-btn i{font-size:26px;color:var(--gold);}
.store-btn-sm{font-size:10px;letter-spacing:1px;text-transform:uppercase;color:rgba(255,255,255,0.5);font-family:var(--font)!important;}
body.lang-ku .store-btn-sm,body.lang-ar .store-btn-sm{letter-spacing:0;font-size:12px;text-transform:none;}
.store-btn-nm{font-size:17px;font-weight:700;color:#fff;font-family:var(--font)!important;}
.qr-stack{display:flex;flex-direction:column;gap:18px;}
.qr-card{
  background:var(--white);border-radius:var(--radius-md);padding:22px;
  display:flex;flex-direction:column;align-items:center;gap:14px;
  box-shadow:0 32px 64px rgba(0,0,0,0.5);position:relative;overflow:hidden;
  transition:transform 0.5s var(--ease);
}
.qr-card:hover{transform:translateY(-8px);}
.qr-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--blue),var(--gold),var(--blue));}
.qr-brand{display:flex;align-items:center;gap:10px;width:100%;}
.qr-icon{width:38px;height:38px;border-radius:10px;flex-shrink:0;background:var(--blue);display:flex;align-items:center;justify-content:center;color:var(--gold);font-size:17px;}
.qr-brand-name{font-size:14px;font-weight:800;color:var(--blue);font-family:var(--font)!important;}
.qr-brand-sub{font-size:10px;text-transform:uppercase;letter-spacing:1px;color:#999;font-family:var(--font)!important;}
.qr-div{width:100%;height:1px;background:var(--border);}
.qr-img{width:170px;height:170px;border-radius:10px;display:block;}
.qr-hint{display:flex;align-items:center;gap:6px;font-size:11px;color:#888;font-family:var(--font)!important;}
.qr-link{
  width:100%;display:flex;align-items:center;justify-content:center;gap:8px;
  padding:11px;border-radius:11px;
  background:var(--blue);color:#fff;font-size:13px;font-weight:700;
  text-decoration:none;transition:all 0.3s var(--ease);font-family:var(--font)!important;
}
.qr-link:hover{background:var(--blue-mid);transform:translateY(-2px);box-shadow:0 6px 18px rgba(26,34,90,0.4);color:#fff;}

/* ── ABOUT ── */
.abt-sec{background:var(--white);}
.abt-grid{display:grid;grid-template-columns:1.3fr 1fr;gap:90px;align-items:start;max-width:1280px;margin:0 auto;}
.abt-p{font-size:15.5px;line-height:2;color:var(--text-2);margin-bottom:18px;}
.abt-quote{
  margin-top:30px;padding:22px 26px;
  border-right:3px solid var(--gold);
  background:var(--gold-pale);border-radius:0 var(--radius-sm) var(--radius-sm) 0;
}
body[dir="ltr"] .abt-quote{border-right:none;border-left:3px solid var(--gold);border-radius:var(--radius-sm) 0 0 var(--radius-sm);}
.abt-quote p{font-size:17px;font-style:italic;color:var(--blue);line-height:1.75;font-weight:600;}
body.lang-ku .abt-quote p,body.lang-ar .abt-quote p{font-style:normal;font-size:16px;}
.vals{display:flex;flex-direction:column;border-radius:var(--radius-md);overflow:hidden;border:1px solid var(--border);}
.val-item{
  display:flex;align-items:center;gap:18px;
  padding:22px 24px;background:var(--white);
  border-bottom:1px solid var(--border);
  transition:all 0.3s var(--ease);cursor:default;
}
.val-item:last-child{border-bottom:none;}
.val-item:hover{background:var(--blue-pale);transform:translateX(-6px);}
body[dir="ltr"] .val-item:hover{transform:translateX(6px);}
.val-icon{
  width:48px;height:48px;border-radius:13px;flex-shrink:0;
  background:var(--blue-light);border:1.5px solid rgba(26,34,90,0.12);
  display:flex;align-items:center;justify-content:center;
  color:var(--blue-mid);font-size:19px;transition:all 0.3s var(--ease);
}
.val-item:hover .val-icon{background:var(--blue);color:#fff;border-color:var(--blue);}
.val-title{font-size:15px;font-weight:700;color:var(--text);}
.val-sub{font-size:12px;color:var(--text-3);margin-top:2px;}

/* ── CTA ── */
.cta-sec{background:var(--bg);padding:80px 48px;}
.cta-inner{
  max-width:1280px;margin:0 auto;
  display:grid;grid-template-columns:1fr auto;gap:60px;align-items:center;
  background:var(--blue);border-radius:var(--radius-lg);padding:56px 60px;
  position:relative;overflow:hidden;
  box-shadow:0 24px 60px rgba(26,34,90,0.28);
}
.cta-inner::after{
  content:'';position:absolute;top:-80px;right:-80px;
  width:280px;height:280px;border-radius:50%;
  background:rgba(192,160,98,0.1);pointer-events:none;
}
.cta-inner::before{
  content:'';position:absolute;bottom:-60px;left:60px;
  width:200px;height:200px;border-radius:50%;
  background:rgba(255,255,255,0.04);pointer-events:none;
}
.cta-tag{font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gold);display:block;margin-bottom:10px;}
body.lang-ku .cta-tag,body.lang-ar .cta-tag{letter-spacing:0;font-size:14px;text-transform:none;}
.cta-title{font-size:clamp(26px,3.5vw,44px);font-weight:800;color:#fff;line-height:1.25;margin-bottom:12px;}
.cta-title strong{color:var(--gold);}
.cta-desc{font-size:15px;color:rgba(255,255,255,0.7);line-height:1.85;}
.cta-btns{display:flex;flex-direction:column;gap:11px;min-width:230px;position:relative;z-index:1;}
.cta-btn1{
  display:flex;align-items:center;justify-content:center;gap:10px;
  padding:14px 30px;background:var(--gold);color:var(--blue);
  font-weight:700;font-size:14px;border-radius:var(--radius-sm);
  text-decoration:none;transition:all 0.3s var(--ease);
  box-shadow:0 4px 16px rgba(192,160,98,0.4);white-space:nowrap;
}
.cta-btn1:hover{background:var(--gold-lt);transform:translateY(-3px);}
.cta-btn2{
  display:flex;align-items:center;justify-content:center;gap:10px;
  padding:13px 30px;border:1.5px solid rgba(255,255,255,0.25);
  color:#fff;font-size:14px;font-weight:600;border-radius:var(--radius-sm);
  text-decoration:none;transition:all 0.3s var(--ease);white-space:nowrap;
  background:rgba(255,255,255,0.06);
}
.cta-btn2:hover{border-color:rgba(192,160,98,0.6);color:var(--gold-lt);transform:translateY(-3px);}

/* ── FOOTER ── */
footer{background:var(--blue);border-top:1px solid rgba(255,255,255,0.08);padding:56px 48px 30px;}
.ft-inner{max-width:1280px;margin:0 auto;}
.ft-top{display:flex;justify-content:space-between;align-items:flex-start;gap:40px;flex-wrap:wrap;padding-bottom:38px;border-bottom:1px solid rgba(255,255,255,0.1);margin-bottom:22px;}
.ft-logo{display:flex;align-items:center;gap:10px;text-decoration:none;margin-bottom:12px;}
.ft-logo-mark{width:34px;height:34px;border-radius:9px;background:var(--gold);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:900;color:var(--blue);font-family:var(--font)!important;}
.ft-logo-name{font-size:17px;font-weight:800;color:#fff;}
.ft-tagline{font-size:13.5px;color:rgba(255,255,255,0.45);line-height:1.9;max-width:220px;}
.ft-col h5{font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--gold);margin-bottom:16px;font-weight:700;}
body.lang-ku .ft-col h5,body.lang-ar .ft-col h5{letter-spacing:0;font-size:13px;text-transform:none;}
.ft-col ul{list-style:none;display:flex;flex-direction:column;gap:10px;}
.ft-col a{font-size:13.5px;color:rgba(255,255,255,0.5);text-decoration:none;transition:color 0.22s;}
.ft-col a:hover{color:#fff;}
.ft-bottom{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;}
.ft-copy{font-size:12px;color:rgba(255,255,255,0.3);}
.ft-copy span{color:var(--gold);}
.ft-social{display:flex;gap:8px;}
.soc-link{width:36px;height:36px;border-radius:9px;border:1px solid rgba(255,255,255,0.12);display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,0.45);font-size:14px;text-decoration:none;transition:all 0.28s var(--ease);}
.soc-link:hover{border-color:var(--gold);color:var(--gold);transform:translateY(-3px);}

/* ── FAB ── */
.fab{position:fixed;bottom:28px;right:28px;z-index:900;}
body[dir="ltr"] .fab{right:auto;left:28px;}
.btt{
  width:44px;height:44px;border-radius:12px;
  background:var(--white);border:1.5px solid var(--border);
  display:flex;align-items:center;justify-content:center;
  color:var(--blue);font-size:15px;cursor:pointer;
  opacity:0;pointer-events:none;
  transition:all 0.38s var(--ease);
  box-shadow:var(--shadow-md);
}
.btt.show{opacity:1;pointer-events:auto;}
.btt:hover{background:var(--blue);color:#fff;border-color:var(--blue);transform:translateY(-4px);}

/* ── RESPONSIVE ── */
@media(max-width:1100px){
  .svc-grid{grid-template-columns:1fr 1fr;}
  .svc-card:last-child{grid-column:span 2;height:360px;}
  .prop-grid{grid-template-columns:1fr 1fr;}
  .prop-card:last-child{display:none;}
  .app-grid,.abt-grid{grid-template-columns:1fr;gap:50px;}
  .qr-stack{flex-direction:row;}
  .qr-card{flex:1;}
}
@media(max-width:1024px){
  header{padding:0 20px;}
  .nav-ul,.lang-sw,#nav-browse-btn{display:none;}
  .hbtn{display:flex;align-items:center;justify-content:center;}
  .sec,.cta-sec{padding-left:20px;padding-right:20px;}
  footer{padding-left:20px;padding-right:20px;}
  .stats-sec{padding-left:20px;padding-right:20px;}
  .cta-inner{grid-template-columns:1fr;padding:32px 24px;}
  .cta-btns{flex-direction:row;flex-wrap:wrap;min-width:unset;}
}
@media(max-width:768px){
  .stats-wrap{transform:translateY(-20px);flex-direction:column;}
  .stat-item{border-right:none!important;border-bottom:1px solid var(--border);padding:20px;}
  .stat-item:last-child{border-bottom:none;}
  .svc-grid{grid-template-columns:1fr;}
  .svc-card:last-child{grid-column:span 1;}
  .prop-grid{grid-template-columns:1fr;}
  .prop-card:last-child{display:block;}
  .prop-card:nth-child(3){display:none;}
  .qr-stack{flex-direction:column;}
  .ft-top{flex-direction:column;}
  .cta-btns{flex-direction:column;}
  .search-row{flex-direction:column;border-radius:var(--radius-md);padding:12px;}
  .search-btn{width:100%;border-radius:12px;}
  .search-row i{display:none;}
}
@media(max-width:480px){
  .hero-ctas{flex-direction:column;width:100%;max-width:280px;margin:0 auto;}
  .cta-primary,.cta-secondary{width:100%;text-align:center;justify-content:center;}
  .s-tab{padding:8px 8px;font-size:12px;}
  .hero-title{letter-spacing:-1px;}
}
@supports(padding:max(0px)){
  header{padding-left:max(20px,env(safe-area-inset-left));padding-right:max(20px,env(safe-area-inset-right));}
  footer{padding-bottom:max(30px,env(safe-area-inset-bottom));}
}

/* ── SKELETON LOADER ── */
@keyframes shimmer {
  0%   { background-position: -600px 0; }
  100% { background-position:  600px 0; }
}
.prop-skeleton { pointer-events:none; }
.skel-img {
  height:196px;
  background: linear-gradient(90deg, #eef0f7 25%, #e0e3f0 50%, #eef0f7 75%);
  background-size: 600px 100%;
  animation: shimmer 1.4s infinite linear;
}
.skel-line {
  border-radius:6px;
  background: linear-gradient(90deg, #eef0f7 25%, #e0e3f0 50%, #eef0f7 75%);
  background-size: 600px 100%;
  animation: shimmer 1.4s infinite linear;
  margin-bottom:10px;
}
.skel-price { height:22px; width:55%; }
.skel-name  { height:16px; width:80%; }
.skel-loc   { height:14px; width:65%; }
.skel-pill  {
  height:28px; flex:1; border-radius:8px;
  background: linear-gradient(90deg, #eef0f7 25%, #e0e3f0 50%, #eef0f7 75%);
  background-size: 600px 100%;
  animation: shimmer 1.4s infinite linear;
}

/* ── REAL PROPERTY CARD ── */
/* Card image overlay stays clean — badge top-right of image */
.prop-img-wrap { position:relative; }
.prop-badge-img {
  position:absolute; top:12px; right:12px;
  font-size:11px; font-weight:700; padding:4px 12px;
  border-radius:100px; backdrop-filter:blur(6px);
}
.prop-badge-img.sell { background:var(--blue); color:#fff; }
.prop-badge-img.rent { background:#3b82f6; color:#fff; }
/* Price inside card body, bold blue */
.prop-price-row { display:flex; align-items:baseline; gap:5px; margin-bottom:5px; }
.prop-price-num { font-size:22px; font-weight:900; color:var(--blue); font-family:var(--font)!important; line-height:1; }
.prop-price-suffix { font-size:13px; font-weight:500; color:var(--text-3); }
</style>
</head>
<body class="lang-ku">

<!-- HEADER -->
<header id="hdr">
  <nav>
    <a href="{{ route('newindex') }}" class="logo">
      <img src="{{ asset('favicon.ico') }}" alt="Dream Mulk" class="logo-img" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
      <div class="logo-mark" style="display:none;">M</div>
      <span class="logo-name">Dream Mulk</span>
    </a>
    <ul class="nav-ul">
      <li><a href="{{ route('newindex') }}"      class="{{ request()->routeIs('newindex') ? 'ac':'' }}"       id="nav-home"    data-i18n="navHome">سەرەتا</a></li>
      <li><a href="{{ route('property.list') }}" class="{{ request()->routeIs('property.list') ? 'ac':'' }}" id="nav-props"   data-i18n="navProps">خانووەکان</a></li>
      <li><a href="#app"                                                                                       id="nav-app"     data-i18n="navApp">ئەپ</a></li>
      <li><a href="{{ route('about-us') }}"      class="{{ request()->routeIs('about-us') ? 'ac':'' }}"       id="nav-about"   data-i18n="navAbout">دەربارەمان</a></li>
      <li><a href="{{ route('contact-us') }}"    class="{{ request()->routeIs('contact-us') ? 'ac':'' }}"     id="nav-contact" data-i18n="navContact">پەیوەندی</a></li>
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
          <button type="submit" class="btn-ghost" style="cursor:pointer;font-family:var(--font);"><i class="fas fa-sign-out-alt"></i></button>
        </form>
      @else
        <a href="{{ route('property.list') }}" class="btn-ghost" id="nav-browse-btn" data-i18n="browseBtn">خانووەکان ببینە</a>
        <a href="{{ route('login-page') }}" class="btn-primary" id="nav-login-btn">
          <i class="fas fa-user"></i>
          <span data-i18n="loginBtn">چوونەژوورەوە</span>
        </a>
      @endif
    </div>
    <button class="hbtn" id="ham"><i class="fas fa-bars"></i></button>
  </nav>
</header>

<div class="bkdp" id="bdp"></div>

<!-- DRAWER -->
<aside class="drw" id="drw">
  <div class="drw-hd">
    <a href="{{ route('newindex') }}" class="logo">
      <img src="{{ asset('favicon.ico') }}" alt="Dream Mulk" class="logo-img" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
      <div class="logo-mark" style="display:none;">M</div>
      <span class="logo-name">Dream Mulk</span>
    </a>
    <button class="drw-x" id="dx"><i class="fas fa-times"></i></button>
  </div>
  <div class="drw-lang">
    <button class="lang-btn active" data-lang="ku">کو</button>
    <button class="lang-btn" data-lang="en">EN</button>
    <button class="lang-btn" data-lang="ar">ع</button>
  </div>
  <nav class="drw-nav">
    <a href="{{ route('newindex') }}"      id="drw-home"    data-i18n="navHome">سەرەتا</a>
    <a href="{{ route('property.list') }}" id="drw-props"   data-i18n="navProps">خانووەکان</a>
    <a href="#app"                         id="drw-app"     data-i18n="navApp">ئەپ</a>
    <a href="{{ route('about-us') }}"      id="drw-about"   data-i18n="navAbout">دەربارەمان</a>
    <a href="{{ route('contact-us') }}"    id="drw-contact" data-i18n="navContact">پەیوەندی</a>
  </nav>
  <div class="drw-ft">
    @if(isset($anyAuth)&&$anyAuth)
      <a href="{{ $dashRoute }}" class="btn-primary" style="text-align:center;justify-content:center;">Dashboard</a>
      <form action="{{ $logoutRoute }}" method="POST" style="width:100%;">@csrf
        <button type="submit" class="btn-ghost" style="width:100%;text-align:center;cursor:pointer;">Logout</button>
      </form>
    @else
      <a href="{{ route('login-page') }}"    class="btn-primary" style="text-align:center;justify-content:center;" id="drw-login"  data-i18n="loginBtn">چوونەژوورەوە</a>
      <a href="{{ route('property.list') }}" class="btn-ghost"   style="text-align:center;"                        id="drw-browse" data-i18n="browseBtn">خانووەکان ببینە</a>
    @endif
  </div>
</aside>

<!-- HERO -->
<section class="hero" id="home">
  <div class="hero-slides">
    <div class="slide"></div>
    <div class="slide"></div>
    <div class="slide"></div>
  </div>
  <div class="hero-overlay"></div>
  <div class="hero-content">
    <div class="hero-badge gsap-fade" id="t-eyebrow" data-i18n="eyebrow">
      <span class="badge-dot"></span>خانوو و زەوی پریمیەم لە کوردستان
    </div>
    <h1 class="hero-title gsap-fade" dir="ltr">DREAM <span class="accent">MULK</span></h1>
    <p class="hero-sub gsap-fade" id="t-sub1" data-i18n="sub1">بۆ کڕین، فرۆشتن و کرێدانی خانوو لە کوردستان — بێ کارمزد</p>
    <p class="hero-meta gsap-fade" id="t-sub2" data-i18n="sub2">کوردستان &bull; هەولێر &bull; ٢٠٢٦</p>
    <div class="search-box gsap-fade" id="hero-search">
      <div class="search-tabs">
        <button class="s-tab active" id="tab-buy"  onclick="setTab(this,'buy')"  data-i18n="tabBuy">🏠 کڕین</button>
        <button class="s-tab"        id="tab-rent" onclick="setTab(this,'rent')" data-i18n="tabRent">🔑 کرێ</button>
        <button class="s-tab"        id="tab-sell" onclick="setTab(this,'sell')" data-i18n="tabSell">💰 فرۆشتن</button>
      </div>
      <div class="search-row">
        <i class="fas fa-search"></i>
        <input class="search-input" type="text" id="hs-input" placeholder="گەڕان لە هەولێر، سلێمانی..." autocomplete="off" inputmode="search"/>
        <button class="search-btn" id="hs-btn" data-i18n="searchBtn">بگەڕێ</button>
      </div>
      <div class="search-chips">
        <span class="chips-label" id="t-popular" data-i18n="popular">شارەکان:</span>
        <a class="chip" data-city="erbil"        id="q-erbil" data-i18n="erbil">هەولێر</a>
        <a class="chip" data-city="sulaymaniyah" id="q-suli"  data-i18n="suli">سلێمانی</a>
        <a class="chip" data-city="duhok"        id="q-duhok" data-i18n="duhok">دهۆک</a>
      </div>
    </div>
    <div class="hero-ctas gsap-fade">
      <a href="{{ route('property.list') }}" class="cta-primary"   id="t-explore" data-i18n="explore">خانووەکان ببینە</a>
      <a href="#app"                         class="cta-secondary"  id="t-app"     data-i18n="appBtn">ئەپەکە دابەزێنە</a>
    </div>
  </div>
  <div class="scroll-hint gsap-fade" id="scrl">
    <div class="scroll-line"></div>
    <span id="t-scroll" data-i18n="scroll">دابەزە</span>
  </div>
</section>

<!-- PAGE BODY -->
<div class="page-body">

  <!-- Stats -->
  <div class="stats-sec">
    <div class="stats-wrap">
      <div class="stat-item gsap-scroll">
        <div class="stat-num" data-t="500" data-s="+">0+</div>
        <div class="stat-label" data-i18n="statLabel1">خانووی تۆمارکراو</div>
      </div>
      <div class="stat-item gsap-scroll" style="transition-delay:.1s">
        <div class="stat-num" data-t="150" data-s="+">0+</div>
        <div class="stat-label" data-i18n="statLabel2">ئەجێنتی پشتڕاستکراو</div>
      </div>
    </div>
  </div>

  <!-- Services -->
  <section class="sec svc-sec" id="services">
    <div class="svc-wrap">
      <div class="sec-header gsap-scroll" style="text-align:center;">
        <span class="sec-tag" data-i18n="svcTag">خزمەتگوزاریەکانمان</span>
        <h2 class="sec-title"><span data-i18n="svcTitle">چی</span> <em data-i18n="svcTitleEm">پێشکەش دەکەین</em></h2>
      </div>
      <div class="svc-grid">
        <a href="{{ route('property.list') }}" class="svc-card gsap-scroll">
          <img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800&q=80" alt="" class="svc-img" loading="lazy">
          <div class="svc-body">
            <span class="svc-badge" data-i18n="svc1Badge">کڕین</span>
            <h3 class="svc-title" data-i18n="svc1Title">کڕینی خانوو</h3>
            <p class="svc-desc" data-i18n="svc1Desc">خانووی خەونەکەت بدۆزەرەوە. لیستی تایبەت لە سەرانسەری کوردستان بە نرخی ئاشکرا و بێ نهێنی.</p>
            <span class="svc-link"><span data-i18n="svc1Cta">بگەڕێ</span> <i class="fas fa-arrow-left"></i></span>
          </div>
        </a>
        <a href="{{ route('login-page') }}" class="svc-card gsap-scroll" style="transition-delay:.1s">
          <img src="https://images.unsplash.com/photo-1582407947304-fd86f028f716?w=800&q=80" alt="" class="svc-img" loading="lazy">
          <div class="svc-body">
            <span class="svc-badge" data-i18n="svc2Badge">فرۆشتن</span>
            <h3 class="svc-title" data-i18n="svc2Title">فرۆشتنی خانوو</h3>
            <p class="svc-desc" data-i18n="svc2Desc">خانووەکەت تۆمار بکە و ڕاستەوخۆ بگەیە کڕیارە راستەقینەکان. بێ ناوەڕاست، بێ کۆمیسیۆن.</p>
            <span class="svc-link"><span data-i18n="svc2Cta">تۆمار بکە</span> <i class="fas fa-arrow-left"></i></span>
          </div>
        </a>
        <a href="{{ route('property.list',['type'=>'rent']) }}" class="svc-card gsap-scroll" style="transition-delay:.2s">
          <img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&q=80" alt="" class="svc-img" loading="lazy">
          <div class="svc-body">
            <span class="svc-badge" data-i18n="svc3Badge">کرێ</span>
            <h3 class="svc-title" data-i18n="svc3Title">کرێدانی خانوو</h3>
            <p class="svc-desc" data-i18n="svc3Desc">کرێی گونجاو بدۆزەرەوە بۆ ژیانت. لیستی پشتڕاستکراو بە نرخی ئاشکرا و مەرجی ڕوون.</p>
            <span class="svc-link"><span data-i18n="svc3Cta">کرێ بدۆزەرەوە</span> <i class="fas fa-arrow-left"></i></span>
          </div>
        </a>
      </div>
    </div>
  </section>

  <!-- Property Preview -->
  <section class="sec props-sec" id="properties">
    <div class="props-wrap">
      <div class="props-header gsap-scroll">
        <div>
          <span class="sec-tag" data-i18n="propsTag">تازەترین خانووەکان</span>
          <h2 class="sec-title" style="margin-top:8px;"><span data-i18n="propsTitle">خانووی</span> <em data-i18n="propsTitleEm">هەڵبژێردراو</em></h2>
        </div>
        <a href="{{ route('property.list') }}" class="view-all" data-i18n="viewAll">هەموویان ببینە <i class="fas fa-arrow-left"></i></a>
      </div>
      <!-- Skeleton loader (shown while API loads) -->
      <div class="prop-grid" id="prop-skeleton">
        <div class="prop-card prop-skeleton">
          <div class="prop-img-wrap skel-img"></div>
          <div class="prop-body">
            <div class="skel-line skel-price"></div>
            <div class="skel-line skel-name"></div>
            <div class="skel-line skel-loc"></div>
            <div class="prop-meta" style="margin-top:14px;">
              <div class="skel-pill"></div>
              <div class="skel-pill"></div>
              <div class="skel-pill"></div>
            </div>
          </div>
        </div>
        <div class="prop-card prop-skeleton">
          <div class="prop-img-wrap skel-img"></div>
          <div class="prop-body">
            <div class="skel-line skel-price"></div>
            <div class="skel-line skel-name"></div>
            <div class="skel-line skel-loc"></div>
            <div class="prop-meta" style="margin-top:14px;">
              <div class="skel-pill"></div>
              <div class="skel-pill"></div>
              <div class="skel-pill"></div>
            </div>
          </div>
        </div>
        <div class="prop-card prop-skeleton">
          <div class="prop-img-wrap skel-img"></div>
          <div class="prop-body">
            <div class="skel-line skel-price"></div>
            <div class="skel-line skel-name"></div>
            <div class="skel-line skel-loc"></div>
            <div class="prop-meta" style="margin-top:14px;">
              <div class="skel-pill"></div>
              <div class="skel-pill"></div>
              <div class="skel-pill"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Real property cards injected here -->
      <div class="prop-grid" id="prop-grid" style="display:none;"></div>

      <!-- Error state -->
      <div id="prop-error" style="display:none;text-align:center;padding:48px 20px;">
        <div style="width:56px;height:56px;border-radius:50%;background:var(--blue-light);display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:22px;color:var(--blue);">
          <i class="fas fa-house-circle-xmark"></i>
        </div>
        <p style="font-size:14px;color:var(--text-2);" data-i18n="propsError">کێشەیەک هەیە لە بارکردنی خانووەکان</p>
      </div>

    </div>
  </section>

  <!-- App -->
  <section class="sec app-sec" id="app">
    <div class="app-grid">

      <!-- Phone mockup side -->
      <div class="gsap-scroll app-phone-wrap" style="transition-delay:.2s">
        <div class="phone-mockup">
          <div class="phone-frame">
            <div class="phone-notch"></div>
            <div class="phone-screen">
              <img src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=400&q=85" alt="Dream Mulk App" class="phone-img" loading="lazy">
              <div class="phone-overlay">
                <div class="phone-label">
                  <span class="phone-app-name">Dream Mulk</span>
                  <span class="phone-app-sub" data-i18n="phoneAppSub">بێ کۆمیسیۆن — هەمیشە بەخۆڕایی</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Text side -->
      <div class="gsap-scroll">
        <span class="sec-tag" data-i18n="appTag">ئەپی مۆبایل</span>
        <h2 class="sec-title" style="margin:12px 0 18px;">
          <span data-i18n="appTitle">خانووەکان</span><br>
          <em data-i18n="appTitleEm">لەگەڵ تۆ</em>
        </h2>
        <p class="app-desc" data-i18n="appDesc">ئەپی Dream Mulk بازاڕی خانووبەرەی کوردستان دەهێنێتە نووکی پەنجەکانت. بگەڕێ، کاتی سەردانی خانوو دابنێ، و ڕاستەوخۆ پەیوەندی بکە بە فرۆشیارەکان.</p>
        <div class="app-feats">
          <div class="app-feat"><i class="fas fa-bell"></i><span data-i18n="appF1">لیستی خانووی نوێ و ئاگادارکردنەوەی خێرا</span></div>
          <div class="app-feat"><i class="fas fa-calendar-check"></i><span data-i18n="appF2">کاتی بینینی خانوو بە ئەجێنت دابنێ</span></div>
          <div class="app-feat"><i class="fas fa-language"></i><span data-i18n="appF3">بە کوردی، عەرەبی و ئینگلیزی</span></div>
          <div class="app-feat"><i class="fas fa-shield-halved"></i><span data-i18n="appF4">پەیامرێنی پارێزراو و بەڵگەنامەکان</span></div>
        </div>

        <!-- Single smart download button — /download auto-detects iOS vs Android -->
        <div class="store-btns">
          <a href="https://dreammulk.com/download" class="store-btn store-btn-ios">
            <i class="fab fa-apple"></i>
            <div>
              <div class="store-btn-sm" data-i18n="appStoreLabel">دابەزێنە لە</div>
              <div class="store-btn-nm">App Store</div>
            </div>
          </a>
          <a href="https://dreammulk.com/download" class="store-btn store-btn-android">
            <i class="fab fa-google-play"></i>
            <div>
              <div class="store-btn-sm" data-i18n="playStoreLabel">وەربگرە لە</div>
              <div class="store-btn-nm">Google Play</div>
            </div>
          </a>
        </div>

      </div>
    </div>
  </section>

  <!-- About -->
  <section class="sec abt-sec" id="about">
    <div class="abt-grid">
      <div class="gsap-scroll">
        <span class="sec-tag" data-i18n="abtTag">چیرۆکی ئێمە</span>
        <h2 class="sec-title" style="margin:12px 0 24px;"><span data-i18n="abtTitle">Dream Mulk</span><br><em data-i18n="abtTitleEm">کێیە؟</em></h2>
        <p class="abt-p" data-i18n="abtP1">Dream Mulk بە ئامانجێکی بەهێز دامەزراوە: ئاسانکردن و ئاشکراکردنی بازاڕی خانووبەرەی کوردستان. ئێمە کڕیار و فرۆشیار ڕاستەوخۆ پەیوەند دەکەین — بێ ناوەڕاست، بێ کۆمیسیۆن.</p>
        <p class="abt-p" data-i18n="abtP2">لە بازاڕێکدا کە زۆرجار ئاڵۆز و تاریکە، ئێمە ڕووناکی و شەفافیەت دەهێنینەوە بۆ هەر مامەڵەیەک.</p>
        <div class="abt-quote"><p data-i18n="abtQuote">«خانوو زەوییە، بەڵام "مولک" مێژوویە. یارمەتیت دەدەین کە مێژووی خۆت بنووسیت.»</p></div>
      </div>
      <div class="vals gsap-scroll" style="transition-delay:.2s">
        <div class="val-item"><div class="val-icon"><i class="fas fa-crown"></i></div><div><div class="val-title" data-i18n="val1Title">تایبەتمەندی</div><div class="val-sub" data-i18n="val1Sub">خانووی هەڵبژێردراو</div></div></div>
        <div class="val-item"><div class="val-icon"><i class="fas fa-handshake"></i></div><div><div class="val-title" data-i18n="val2Title">ئامانجداری</div><div class="val-sub" data-i18n="val2Sub">ئاشکرایی تەواو</div></div></div>
        <div class="val-item"><div class="val-icon"><i class="fas fa-mobile-alt"></i></div><div><div class="val-title" data-i18n="val3Title">تەکنەلۆژیا</div><div class="val-sub" data-i18n="val3Sub">ئەپ و وێبگەی مۆدێرن</div></div></div>
        <div class="val-item"><div class="val-icon"><i class="fas fa-map-marker-alt"></i></div><div><div class="val-title" data-i18n="val4Title">لە هەولێر</div><div class="val-sub" data-i18n="val4Sub">دامەزراوە ٢٠٢٦</div></div></div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="cta-sec" id="contact">
    <div class="cta-inner">
      <div class="gsap-scroll">
        <span class="cta-tag" data-i18n="rdrTag">بۆ کۆمپانیاکانی خانووبەرە</span>
        <h2 class="cta-title"><span data-i18n="rdrTitle">کاروبارەکەت بگەشێنە</span><br><span data-i18n="rdrWith">لەگەڵ</span> <strong>Dream Mulk</strong></h2>
        <p class="cta-desc" data-i18n="rdrDesc">کۆمپانیاکەت تۆمار بکە و بگەیە بە هەزاران کڕیار و کرێیار لە سەرانسەری کوردستان. خانوو لیست بکە، ئەجێنت بەڕێوەبەرە، مامەڵەکان تەواو بکە.</p>
      </div>
      <div class="cta-btns gsap-scroll" style="transition-delay:.2s">
        <a href="{{ route('office.login') }}"  class="cta-btn1"><i class="fas fa-building"></i><span data-i18n="rdrBtn1">چوونەژوورەوەی کۆمپانیا</span></a>
        <a href="{{ route('property.list') }}" class="cta-btn2"><i class="fas fa-search"></i><span data-i18n="rdrBtn2">بگەڕێ بەبێ تۆمارکردن</span></a>
      </div>
    </div>
  </section>

  <!-- Footer -->
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
            <li><a href="https://apps.apple.com/us/app/dream-mulk/id6756894199" target="_blank" rel="noopener"><i class="fab fa-apple"></i> App Store</a></li>
            <li><a href="https://play.google.com/store/apps/details?id=com.dreammulk.dreamhaven" target="_blank" rel="noopener"><i class="fab fa-google-play"></i> Google Play</a></li>
            <li><a href="{{ route('contact-us') }}" data-i18n="ftLink9">پەیوەندیمان پێوە بکە</a></li>
          </ul>
        </div>
      </div>
      <div class="ft-bottom">
        <div class="ft-copy">© {{ date('Y') }} <span>Dream Mulk</span>. Erbil, Kurdistan Region of Iraq.</div>
        <div class="ft-social">
          <a href="https://www.facebook.com/share/1CGLEbK7qh/"                 target="_blank" rel="noopener" class="soc-link"><i class="fab fa-facebook-f"></i></a>
          <a href="https://www.instagram.com/dream_mulk?igsh=MWt4YXd1eTN4NW5j" target="_blank" rel="noopener" class="soc-link"><i class="fab fa-instagram"></i></a>
        </div>
      </div>
    </div>
  </footer>
</div>

<div class="fab"><div class="btt" id="btt"><i class="fas fa-arrow-up"></i></div></div>

<script>
document.addEventListener('DOMContentLoaded',()=>{
  const lenis=new Lenis({duration:1.15,easing:t=>Math.min(1,1.001-Math.pow(2,-10*t)),smoothWheel:true,smoothTouch:false});
  lenis.on('scroll',ScrollTrigger.update);
  gsap.ticker.add(time=>lenis.raf(time*1000));
  gsap.ticker.lagSmoothing(0);
  document.querySelectorAll('a[href^="#"]').forEach(a=>{
    a.addEventListener('click',e=>{
      const id=a.getAttribute('href');
      if(id&&id.length>1){const el=document.querySelector(id);if(el){e.preventDefault();lenis.scrollTo(el,{duration:1.2});}}
    });
  });
  const hdr=document.getElementById('hdr'),btt=document.getElementById('btt');
  lenis.on('scroll',e=>{hdr.classList.toggle('sc',e.animatedScroll>50);btt.classList.toggle('show',e.animatedScroll>400);});
  btt.addEventListener('click',()=>lenis.scrollTo(0,{duration:1.2}));
  const ham=document.getElementById('ham'),drw=document.getElementById('drw'),bdp=document.getElementById('bdp'),dx=document.getElementById('dx');
  const openD=()=>{drw.classList.add('on');bdp.classList.add('on');document.body.style.overflow='hidden';};
  const closeD=()=>{drw.classList.remove('on');bdp.classList.remove('on');document.body.style.overflow='';};
  ham.addEventListener('click',openD);dx.addEventListener('click',closeD);bdp.addEventListener('click',closeD);
  drw.querySelectorAll('a').forEach(a=>a.addEventListener('click',closeD));
  window.addEventListener('resize',()=>{if(window.innerWidth>1024)closeD();});
  gsap.set('.gsap-fade',{y:22,opacity:0});
  gsap.to('.gsap-fade',{y:0,opacity:1,duration:0.85,stagger:0.1,ease:'power3.out',delay:0.1,clearProps:'transform'});
  document.querySelectorAll('.gsap-scroll').forEach(el=>{
    gsap.fromTo(el,{y:32,opacity:0},{y:0,opacity:1,duration:0.7,ease:'power2.out',scrollTrigger:{trigger:el,start:'top 88%',toggleActions:'play none none none'}});
  });
  let done=false;
  ScrollTrigger.create({trigger:'.stats-sec',start:'top 88%',onEnter:()=>{
    if(done)return;done=true;
    document.querySelectorAll('.stat-num[data-t]').forEach(el=>{
      const target=parseInt(el.dataset.t),suffix=el.dataset.s||'',obj={v:0};
      gsap.to(obj,{v:target,duration:2,ease:'power3.out',onUpdate(){el.textContent=Math.round(obj.v)+suffix;}});
    });
  }});
});
var currentTab='buy';
window.setTab=function(el,type){
  document.querySelectorAll('.s-tab').forEach(t=>t.classList.remove('active'));
  el.classList.add('active');currentTab=type;
  const T=I18N[currentLang]||I18N['ku'];
  const inp=document.getElementById('hs-input');
  if(inp)inp.placeholder={buy:T.placeholderBuy,rent:T.placeholderRent,sell:T.placeholderSell}[type]||T.placeholderBuy;
};
function doSearch(){
  const kw=document.getElementById('hs-input').value.trim();
  const base='{{ route("property.list") }}';
  const p=new URLSearchParams();
  if(currentTab==='rent')p.set('type','rent');
  else if(currentTab==='buy')p.set('type','sell');
  if(kw)p.set('search',kw);
  window.location.href=base+(p.toString()?'?'+p.toString():'');
}
document.getElementById('hs-btn').addEventListener('click',doSearch);
document.getElementById('hs-input').addEventListener('keydown',e=>{if(e.key==='Enter'){e.preventDefault();doSearch();}});
document.querySelectorAll('.chip[data-city]').forEach(a=>{
  a.addEventListener('click',e=>{
    e.preventDefault();const p=new URLSearchParams();
    if(currentTab==='rent')p.set('type','rent');else if(currentTab==='buy')p.set('type','sell');
    p.set('city',a.dataset.city);
    window.location.href='{{ route("property.list") }}?'+p.toString();
  });
});
var currentLang='ku';
const I18N={
  ku:{dir:'rtl',eyebrow:'خانوو و زەوی پریمیەم لە کوردستان',sub1:'بۆ کڕین، فرۆشتن و کرێدانی خانوو لە کوردستان — بێ کارمزد',sub2:'کوردستان • هەولێر • ٢٠٢٦',tabBuy:'🏠 کڕین',tabRent:'🔑 کرێ',tabSell:'💰 فرۆشتن',searchBtn:'بگەڕێ',placeholderBuy:'بگەڕێ... هەولێر، سلێمانی، دهۆک',placeholderRent:'خانوی کرێ بدۆزەرەوە...',placeholderSell:'خانووەکەت بفرۆشە...',popular:'شارەکان:',erbil:'هەولێر',suli:'سلێمانی',duhok:'دهۆک',explore:'خانووەکان ببینە',appBtn:'ئەپەکە دابەزێنە',scroll:'دابەزە',navHome:'سەرەتا',navProps:'خانووەکان',navApp:'ئەپ',navAbout:'دەربارەمان',navContact:'پەیوەندی',loginBtn:'چوونەژوورەوە',browseBtn:'خانووەکان ببینە',statLabel1:'خانووی تۆمارکراو',statLabel2:'ئەجێنتی پشتڕاستکراو',svcTag:'خزمەتگوزاریەکانمان',svcTitle:'چی',svcTitleEm:'پێشکەش دەکەین',svc1Badge:'کڕین',svc1Title:'کڕینی خانوو',svc1Desc:'خانووی خەونەکەت بدۆزەرەوە. لیستی تایبەت لە سەرانسەری کوردستان بە نرخی ئاشکرا و بێ نهێنی.',svc1Cta:'بگەڕێ',svc2Badge:'فرۆشتن',svc2Title:'فرۆشتنی خانوو',svc2Desc:'خانووەکەت تۆمار بکە و ڕاستەوخۆ بگەیە کڕیارە راستەقینەکان. بێ ناوەڕاست، بێ کۆمیسیۆن.',svc2Cta:'تۆمار بکە',svc3Badge:'کرێ',svc3Title:'کرێدانی خانوو',svc3Desc:'کرێی گونجاو بدۆزەرەوە بۆ ژیانت. لیستی پشتڕاستکراو بە نرخی ئاشکرا و مەرجی ڕوون.',svc3Cta:'کرێ بدۆزەرەوە',propsTag:'تازەترین خانووەکان',propsTitle:'خانووی',propsTitleEm:'هەڵبژێردراو',viewAll:'هەموویان ببینە',propBadgeSell:'فرۆشتن',propBadgeRent:'کرێ',propCurr:'دۆلار',propPerMonth:'/ مانگ',prop1Name:'ویلای مۆدێرن — هەولێر',prop1Loc:'گەڕەکی ئەنکاوا، هەولێر',prop2Name:'شووقەی شيک — سلێمانی',prop2Loc:'سەرچەم، سلێمانی',prop3Name:'خانووی بلند — دهۆک',prop3Loc:'ناوەندی شار، دهۆک',appTag:'ئەپی مۆبایل',appTitle:'خانووەکان',appTitleEm:'لەگەڵ تۆ',appDesc:'ئەپی Dream Mulk بازاڕی خانووبەرەی کوردستان دەهێنێتە نووکی پەنجەکانت. بگەڕێ، کاتی سەردانی خانوو دابنێ، و ڕاستەوخۆ پەیوەندی بکە بە فرۆشیارەکان.',appF1:'لیستی خانووی نوێ و ئاگادارکردنەوەی خێرا',appF2:'کاتی بینینی خانوو بە ئەجێنت دابنێ',appF3:'بە کوردی، عەرەبی و ئینگلیزی',appF4:'پەیامرێنی پارێزراو و بەڵگەنامەکان',appF5:'بێ کۆمیسیۆن — هەمیشە بەخۆڕایی',appStoreLabel:'دابەزێنە لە',playStoreLabel:'وەربگرە لە',phoneAppSub:'بێ کۆمیسیۆن — هەمیشە بەخۆڕایی',qrSub:'بەخۆڕایی',qrHint:'کامێراکەت بەرەو ئەمە بگرە',qrBtn:'بکرەوە لە App Store',qrBtnAndroid:'بکرەوە لە Google Play',abtTag:'چیرۆکی ئێمە',abtTitle:'Dream Mulk',abtTitleEm:'کێیە؟',abtP1:'Dream Mulk بە ئامانجێکی بەهێز دامەزراوە: ئاسانکردن و ئاشکراکردنی بازاڕی خانووبەرەی کوردستان. ئێمە کڕیار و فرۆشیار ڕاستەوخۆ پەیوەند دەکەین — بێ ناوەڕاست، بێ کۆمیسیۆن.',abtP2:'لە بازاڕێکدا کە زۆرجار ئاڵۆز و تاریکە، ئێمە ڕووناکی و شەفافیەت دەهێنینەوە بۆ هەر مامەڵەیەک.',abtQuote:'«خانوو زەوییە، بەڵام "مولک" مێژوویە. یارمەتیت دەدەین کە مێژووی خۆت بنووسیت.»',val1Title:'تایبەتمەندی',val1Sub:'خانووی هەڵبژێردراو',val2Title:'ئامانجداری',val2Sub:'ئاشکرایی تەواو',val3Title:'تەکنەلۆژیا',val3Sub:'ئەپ و وێبگەی مۆدێرن',val4Title:'لە هەولێر',val4Sub:'دامەزراوە ٢٠٢٦',rdrTag:'بۆ کۆمپانیاکانی خانووبەرە',rdrTitle:'کاروبارەکەت بگەشێنە',rdrWith:'لەگەڵ',rdrDesc:'کۆمپانیاکەت تۆمار بکە و بگەیە بە هەزاران کڕیار و کرێیار لە سەرانسەری کوردستان. خانوو لیست بکە، ئەجێنت بەڕێوەبەرە، مامەڵەکان تەواو بکە.',rdrBtn1:'چوونەژوورەوەی کۆمپانیا',rdrBtn2:'بگەڕێ بەبێ تۆمارکردن',ftTag:'باشترین پلاتفۆرمی خانووبەرەی کوردستان. بێ کارمزد. بێ کۆمیسیۆن. بێ نهێنی.',ftCol1:'پلاتفۆرم',ftCol2:'خزمەتگوزاری',ftCol3:'ئەپەکە دابەزێنە',ftLink1:'خانووەکان ببینە',ftLink2:'چوونەژوورەوەی کڕیار',ftLink3:'پۆرتاڵی ئەجێنت',ftLink4:'دەربارەی ئێمە',ftLink5:'کڕینی خانوو',ftLink6:'فرۆشتنی خانوو',ftLink7:'کرێدانی خانوو',ftLink8:'ئەجێنت بدۆزەرەوە',ftLink9:'پەیوەندیمان پێوە بکە'},
  en:{dir:'ltr',eyebrow:'Premium Real Estate in Kurdistan',sub1:'Buy, sell & rent properties across Kurdistan — zero commission',sub2:'Kurdistan • Erbil • Est. 2026',tabBuy:'🏠 Buy',tabRent:'🔑 Rent',tabSell:'💰 Sell',searchBtn:'Search',placeholderBuy:'Search in Erbil, Sulaymaniyah...',placeholderRent:'Find rentals in Kurdistan...',placeholderSell:'List your property...',popular:'Popular:',erbil:'Erbil',suli:'Sulaymaniyah',duhok:'Duhok',explore:'Explore Properties',appBtn:'Download App',scroll:'Scroll',navHome:'Home',navProps:'Properties',navApp:'App',navAbout:'About Us',navContact:'Contact',loginBtn:'Sign In',browseBtn:'Browse Properties',statLabel1:'Listed Properties',statLabel2:'Verified Agents',svcTag:'Our Services',svcTitle:'What We',svcTitleEm:'Offer',svc1Badge:'Buy',svc1Title:'Buy a Property',svc1Desc:'Find your dream home with advanced filters. Exclusive listings across Kurdistan with transparent pricing.',svc1Cta:'Explore',svc2Badge:'Sell',svc2Title:'Sell a Property',svc2Desc:'List your property and reach serious buyers directly. No middlemen, no commissions, ever.',svc2Cta:'List Now',svc3Badge:'Rent',svc3Title:'Rent a Property',svc3Desc:'Find the right rental at the right price. Verified listings with transparent terms and conditions.',svc3Cta:'Find Rentals',propsTag:'Latest Listings',propsTitle:'Featured',propsTitleEm:'Properties',viewAll:'View All',propBadgeSell:'For Sale',propBadgeRent:'For Rent',propCurr:'USD',propPerMonth:'/ mo',prop1Name:'Modern Villa — Erbil',prop1Loc:'Ankawa District, Erbil',prop2Name:'Chic Apartment — Sulaymaniyah',prop2Loc:'Sarchinar, Sulaymaniyah',prop3Name:'Spacious Home — Duhok',prop3Loc:'City Center, Duhok',appTag:'Mobile App',appTitle:'Properties',appTitleEm:'With You',appDesc:"The Dream Mulk app puts Kurdistan's real estate market in your pocket. Search, book viewings, and contact sellers directly.",appF1:'Live listings & instant notifications',appF2:'Book property viewings instantly',appF3:'Kurdish, Arabic & English',appF4:'Secure messaging & documents',appF5:'Zero commission — always free',appStoreLabel:'Download on the',playStoreLabel:'Get it on',phoneAppSub:'Zero commission — always free',qrSub:'Free Download',qrHint:'Point your camera to scan',qrBtn:'Open in App Store',qrBtnAndroid:'Open in Google Play',abtTag:'Our Story',abtTitle:'Dream Mulk',abtTitleEm:'Story',abtP1:"Dream Mulk was built to simplify and bring transparency to Kurdistan's real estate market. We connect buyers and sellers directly — no middlemen, no commissions.",abtP2:"In a market full of complexity, we bring clarity and modern technology to every transaction.",abtQuote:'"Property is land, but Mulk is legacy. We help you write yours."',val1Title:'Exclusive',val1Sub:'Curated listings',val2Title:'Integrity',val2Sub:'Full transparency',val3Title:'Technology',val3Sub:'Modern app & platform',val4Title:'Erbil Based',val4Sub:'Est. 2026',rdrTag:'For Real Estate Offices',rdrTitle:'Grow Your Business',rdrWith:'With',rdrDesc:'Register your office and reach thousands of buyers and renters across Kurdistan. List properties, manage agents, and close deals.',rdrBtn1:'Office Login',rdrBtn2:'Browse Without Login',ftTag:"Kurdistan's real estate platform. No fees. No commissions. Always transparent.",ftCol1:'Platform',ftCol2:'Services',ftCol3:'Download App',ftLink1:'Browse Properties',ftLink2:'Client Login',ftLink3:'Agent Portal',ftLink4:'About Us',ftLink5:'Buy Property',ftLink6:'Sell Property',ftLink7:'Rent Property',ftLink8:'Find an Agent',ftLink9:'Contact Us'},
  ar:{dir:'rtl',eyebrow:'عقارات كردستان المتميزة',sub1:'شراء وبيع وإيجار العقارات في كردستان — بدون عمولة',sub2:'كردستان • أربيل • ٢٠٢٦',tabBuy:'🏠 شراء',tabRent:'🔑 إيجار',tabSell:'💰 بيع',searchBtn:'ابحث الآن',placeholderBuy:'ابحث في أربيل، السليمانية، دهوك...',placeholderRent:'ابحث عن شقق وبيوت للإيجار...',placeholderSell:'أضف عقارك وابدأ البيع...',popular:'أشهر المدن:',erbil:'أربيل',suli:'السليمانية',duhok:'دهوك',explore:'استعرض العقارات',appBtn:'حمّل التطبيق',scroll:'اكتشف',navHome:'الرئيسية',navProps:'العقارات',navApp:'التطبيق',navAbout:'من نحن',navContact:'تواصل معنا',loginBtn:'تسجيل الدخول',browseBtn:'استعرض العقارات',statLabel1:'عقارات مدرجة',statLabel2:'وكلاء موثقون',svcTag:'خدماتنا العقارية',svcTitle:'ماذا',svcTitleEm:'نقدم لك',svc1Badge:'شراء',svc1Title:'شراء عقار',svc1Desc:'اعثر على منزل أحلامك بفلاتر ذكية. قوائم حصرية في كردستان بأسعار شفافة.',svc1Cta:'ابحث الآن',svc2Badge:'بيع',svc2Title:'بيع عقار',svc2Desc:'أدرج عقارك وتواصل مع مشترين جادين مباشرة دون وسطاء أو عمولات.',svc2Cta:'أضف عقارك',svc3Badge:'إيجار',svc3Title:'إيجار عقار',svc3Desc:'ابحث عن إيجار يناسبك بسعر واضح. قوائم موثقة بشروط شفافة.',svc3Cta:'ابحث عن إيجار',propsTag:'أحدث العقارات',propsTitle:'عقارات',propsTitleEm:'مختارة',viewAll:'عرض الكل',propBadgeSell:'للبيع',propBadgeRent:'للإيجار',propCurr:'دولار',propPerMonth:'/ شهر',prop1Name:'فيلا عصرية — أربيل',prop1Loc:'حي عنكاوا، أربيل',prop2Name:'شقة أنيقة — السليمانية',prop2Loc:'سرچنار، السليمانية',prop3Name:'منزل واسع — دهوك',prop3Loc:'وسط المدينة، دهوك',appTag:'تطبيق الجوال',appTitle:'العقارات',appTitleEm:'في متناول يدك',appDesc:'تطبيق Dream Mulk يضع سوق عقارات كردستان بأكمله بين يديك.',appF1:'قوائم عقارات فورية وإشعارات لحظية',appF2:'احجز موعد معاينة بخطوة واحدة',appF3:'يدعم العربية والكردية والإنجليزية',appF4:'تراسل آمن ووثائق رسمية',appF5:'بدون عمولة — مجاني تماماً',appStoreLabel:'حمّل من',playStoreLabel:'احصل عليه من',phoneAppSub:'بدون عمولة — مجاني دائماً',qrSub:'تحميل مجاني',qrHint:'وجّه الكاميرا للمسح',qrBtn:'افتح في App Store',qrBtnAndroid:'افتح في Google Play',abtTag:'قصتنا',abtTitle:'Dream Mulk',abtTitleEm:'من نحن',abtP1:'أسسنا Dream Mulk لجعل سوق العقارات في كردستان أكثر شفافية وسهولة.',abtP2:'في سوق يكتنفه التعقيد، نجلب الوضوح والتقنية الحديثة لكل صفقة.',abtQuote:'«العقار أرض، لكن المُلك إرث. نساعدك على بناء إرثك.»',val1Title:'الحصرية',val1Sub:'عقارات مختارة بعناية',val2Title:'النزاهة',val2Sub:'شفافية تامة',val3Title:'التقنية',val3Sub:'تطبيق ومنصة حديثة',val4Title:'مقرنا أربيل',val4Sub:'تأسست عام ٢٠٢٦',rdrTag:'لشركات العقارات والمكاتب',rdrTitle:'طوّر أعمالك العقارية',rdrWith:'مع',rdrDesc:'سجّل شركتك وتواصل مع آلاف المشترين والمستأجرين في كردستان.',rdrBtn1:'دخول المكتب العقاري',rdrBtn2:'تصفح بدون تسجيل',ftTag:'منصة العقارات الأولى في كردستان. بدون رسوم. بدون عمولات.',ftCol1:'المنصة',ftCol2:'الخدمات',ftCol3:'حمّل التطبيق',ftLink1:'استعرض العقارات',ftLink2:'دخول العملاء',ftLink3:'بوابة الوكلاء',ftLink4:'من نحن',ftLink5:'شراء عقار',ftLink6:'بيع عقار',ftLink7:'إيجار عقار',ftLink8:'ابحث عن وكيل',ftLink9:'تواصل معنا'}
};
function setLang(lang){
  const T=I18N[lang];if(!T)return;
  currentLang=lang;localStorage.setItem('dm_lang',lang);
  document.body.dir=T.dir;
  document.documentElement.lang=lang==='ar'?'ar':lang==='ku'?'ku':'en';
  document.body.classList.remove('lang-ku','lang-en','lang-ar');
  document.body.classList.add('lang-'+lang);
  const ht=document.querySelector('.hero-title');
  if(ht){ht.dir='ltr';ht.style.direction='ltr';}
  document.querySelectorAll('.lang-btn').forEach(b=>b.classList.toggle('active',b.dataset.lang===lang));
  const isRtl=T.dir==='rtl';
  document.querySelectorAll('.svc-link i').forEach(i=>{i.className=isRtl?'fas fa-arrow-left':'fas fa-arrow-right';});
  document.querySelectorAll('.view-all i').forEach(i=>{i.className=isRtl?'fas fa-arrow-left':'fas fa-arrow-right';});
  document.querySelectorAll('.qr-link i:last-child').forEach(i=>{i.className=isRtl?'fas fa-arrow-left':'fas fa-arrow-right';});
  document.querySelectorAll('[data-i18n]').forEach(el=>{
    const k=el.getAttribute('data-i18n');
    if(T[k]===undefined)return;
    if(!el.children.length){el.textContent=T[k];}
    else{for(let n of el.childNodes){if(n.nodeType===Node.TEXT_NODE&&n.textContent.trim()){n.textContent=T[k];break;}}}
  });
  const idMap={'t-eyebrow':'eyebrow','t-sub1':'sub1','t-sub2':'sub2','tab-buy':'tabBuy','tab-rent':'tabRent','tab-sell':'tabSell','hs-btn':'searchBtn','t-popular':'popular','q-erbil':'erbil','q-suli':'suli','q-duhok':'duhok','t-explore':'explore','t-app':'appBtn','t-scroll':'scroll','nav-home':'navHome','nav-props':'navProps','nav-app':'navApp','nav-about':'navAbout','nav-contact':'navContact','drw-home':'navHome','drw-props':'navProps','drw-app':'navApp','drw-about':'navAbout','drw-contact':'navContact','drw-login':'loginBtn','drw-browse':'browseBtn','nav-browse-btn':'browseBtn'};
  Object.entries(idMap).forEach(([id,key])=>{
    const el=document.getElementById(id);
    if(el&&T[key]!==undefined){
      if(id==='nav-login-btn'){const s=el.querySelector('span');if(s)s.textContent=T[key];}
      else el.textContent=T[key];
    }
  });
  const inp=document.getElementById('hs-input');
  if(inp)inp.placeholder={buy:T.placeholderBuy,rent:T.placeholderRent,sell:T.placeholderSell}[currentTab]||T.placeholderBuy;
}
document.querySelectorAll('.lang-btn').forEach(b=>b.addEventListener('click',()=>setLang(b.dataset.lang)));
setLang(localStorage.getItem('dm_lang')||'ku');

/* ══════════════════════════════════════
   HOMEPAGE FEATURED PROPERTIES — real API
══════════════════════════════════════ */
(function loadFeaturedProperties() {

  const grid     = document.getElementById('prop-grid');
  const skeleton = document.getElementById('prop-skeleton');
  const errEl    = document.getElementById('prop-error');

  // ── Helpers ──────────────────────────────────────────────────────────────

  function getDetailRoute(id) {
    // Uses the same Laravel route pattern as the properties listing page
    return '/properties/' + id;
  }

  function getPropertyName(p) {
    if (p.name) {
      if (typeof p.name === 'object') {
        return p.name[currentLang] || p.name['en'] || p.name['ku'] || Object.values(p.name)[0] || '';
      }
      return p.name;
    }
    return '';
  }

  function getPropertyAddress(p) {
    // Try address_details structure first (matches Laravel model)
    if (p.address_details) {
      const city = p.address_details.city;
      const area = p.address_details.area;
      const cityName = city
        ? (typeof city === 'object' ? (city[currentLang] || city['en'] || city['ku'] || '') : city)
        : '';
      const areaName = area
        ? (typeof area === 'object' ? (area[currentLang] || area['en'] || area['ku'] || '') : area)
        : '';
      return [areaName, cityName].filter(Boolean).join('، ');
    }
    if (p.address) return p.address;
    return '';
  }

  function getPropertyImage(p) {
    if (Array.isArray(p.images) && p.images.length > 0) return p.images[0];
    if (p.image) return p.image;
    if (p.thumbnail) return p.thumbnail;
    return null;
  }

  function getPrice(p) {
    if (p.price) {
      if (typeof p.price === 'object') return p.price.usd || p.price.USD || 0;
      return p.price;
    }
    return p.price_usd || p.priceUsd || 0;
  }

  function getRooms(p, type) {
    // type = 'bedroom' | 'bathroom'
    if (p.rooms && p.rooms[type]) {
      return p.rooms[type].count ?? p.rooms[type] ?? 0;
    }
    if (type === 'bedroom')  return p.bedrooms  || p.bedroom_count  || p.beds  || '—';
    if (type === 'bathroom') return p.bathrooms || p.bathroom_count || p.baths || '—';
    return '—';
  }

  function getArea(p) {
    return p.area || p.area_sqm || p.size || '—';
  }

  function getListingType(p) {
    const lt = (p.listing_type || p.listingType || p.type || '').toLowerCase();
    return lt;
  }

  // ── Card builder ─────────────────────────────────────────────────────────

  function buildCard(p, index) {
    const id      = p.id;
    const name    = getPropertyName(p);
    const address = getPropertyAddress(p);
    const image   = getPropertyImage(p);
    const priceUsd = getPrice(p);
    const lt      = getListingType(p);
    const isRent  = lt === 'rent';
    const beds    = getRooms(p, 'bedroom');
    const baths   = getRooms(p, 'bathroom');
    const area    = getArea(p);

    const href    = getDetailRoute(id);
    const delay   = index * 0.1;

    // Price display
    const priceFormatted = priceUsd ? '$' + Number(priceUsd).toLocaleString() : '—';
    const priceSuffix    = isRent
      ? `<span class="prop-price-suffix">/ ${currentLang === 'en' ? 'mo' : currentLang === 'ar' ? 'شهر' : 'مانگ'}</span>`
      : `<span class="prop-price-suffix">${currentLang === 'en' ? 'USD' : currentLang === 'ar' ? 'دولار' : 'دۆلار'}</span>`;

    // Badge label
    const badgeLabel = isRent
      ? (currentLang === 'en' ? 'Rent' : currentLang === 'ar' ? 'إيجار' : 'کرێ')
      : (currentLang === 'en' ? 'Sale' : currentLang === 'ar' ? 'بيع'  : 'فرۆشتن');

    // Fallback image
    const imgSrc = image || 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=600&q=80';

    // i18n labels
    const lblBed  = currentLang === 'en' ? 'Beds'  : currentLang === 'ar' ? 'غرف'    : 'جێخەو';
    const lblBath = currentLang === 'en' ? 'Baths' : currentLang === 'ar' ? 'حمامات' : 'حەمام';

    const statusLabel = isRent
      ? (currentLang === 'en' ? 'For Rent' : currentLang === 'ar' ? 'إيجار' : 'کرێ')
      : (currentLang === 'en' ? 'Available' : currentLang === 'ar' ? 'بەردەستە' : 'بەردەستە');

    return `
      <a href="${href}" class="prop-card gsap-scroll" style="transition-delay:${delay}s;opacity:0;" data-id="${id}">
        <div class="prop-img-wrap">
          <img src="${imgSrc}" alt="${name}" class="prop-img" loading="lazy"
            onerror="this.src='https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=600&q=80'">
          <div class="prop-heart"><i class="far fa-heart"></i></div>
          <div class="prop-status ${isRent ? 'rent' : 'available'}">
            <span class="prop-status-dot"></span>
            ${statusLabel}
          </div>
        </div>
        <div class="prop-body">
          <div class="prop-price-row">
            <span class="prop-price-num">${priceFormatted}</span>
            ${priceSuffix}
          </div>
          <div class="prop-name">${name || '—'}</div>
          <div class="prop-loc">
            <i class="fas fa-location-dot"></i>
            <span>${address || (currentLang === 'en' ? 'Kurdistan Region' : currentLang === 'ar' ? 'إقليم كردستان' : 'کوردستان')}</span>
          </div>
          <div class="prop-meta">
            <div class="prop-meta-item"><i class="fas fa-bed"></i> ${beds}</div>
            <div class="prop-meta-item"><i class="fas fa-bath"></i> ${baths}</div>
            <div class="prop-meta-item"><i class="fas fa-ruler-combined"></i> ${area} m²</div>
          </div>
        </div>
      </a>`;
  }

  // ── Render ────────────────────────────────────────────────────────────────

  function renderCards(properties) {
    const top3 = properties.slice(0, 3);
    if (top3.length === 0) {
      skeleton.style.display = 'none';
      errEl.style.display    = 'block';
      return;
    }

    grid.innerHTML = top3.map((p, i) => buildCard(p, i)).join('');

    // Hide skeleton, show real grid
    skeleton.style.display = 'none';
    grid.style.display     = 'grid';

    // Animate cards in with GSAP (same as rest of page)
    if (typeof gsap !== 'undefined') {
      gsap.fromTo('#prop-grid .prop-card',
        { y: 32, opacity: 0 },
        {
          y: 0, opacity: 1, duration: 0.7, stagger: 0.1,
          ease: 'power2.out', clearProps: 'transform',
          scrollTrigger: {
            trigger: '#prop-grid',
            start: 'top 88%',
            toggleActions: 'play none none none',
          }
        }
      );
    }
  }

  // ── API call ──────────────────────────────────────────────────────────────

  async function fetchProperties() {
    try {
      // Uses your existing Laravel API endpoint (same base as properties listing)
      const response = await fetch('/v1/api/properties/recent?per_page=6', {
        headers: {
          'Accept':          'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]')?.content || '',
        }
      });

      if (!response.ok) throw new Error('HTTP ' + response.status);

      const json = await response.json();

      // Handle both paginated { data: { data: [...] } } and flat { data: [...] }
      let properties = [];
      if (json.data) {
        if (Array.isArray(json.data))            properties = json.data;
        else if (Array.isArray(json.data.data))  properties = json.data.data;
        else if (json.data.properties)           properties = json.data.properties;
      } else if (Array.isArray(json.properties)) {
        properties = json.properties;
      } else if (Array.isArray(json)) {
        properties = json;
      }

      renderCards(properties);

    } catch (err) {
      console.warn('[DreamMulk] Featured properties fetch failed:', err);
      // Show error state
      skeleton.style.display = 'none';
      errEl.style.display    = 'block';
    }
  }

  // Start loading immediately when DOM is ready
  fetchProperties();

  // Re-render card text when language changes (prices/labels update)
  // We hook into the existing setLang function
  const _origSetLang = window.setLang;
  if (typeof _origSetLang === 'function') {
    window.setLang = function(lang) {
      _origSetLang(lang);
      // Re-render cards with new language if grid is already populated
      if (grid.style.display !== 'none' && grid.children.length > 0) {
        // Re-fetch is overkill — just re-render from cached data
        if (window._dmFeaturedProperties) {
          renderCards(window._dmFeaturedProperties);
        }
      }
    };
  }

  // Cache for lang re-render
  const _origFetch = fetchProperties;
  fetchProperties = async function() {
    try {
      const response = await fetch('/v1/api/properties/recent?per_page=6', {
        headers: {
          'Accept':          'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]')?.content || '',
        }
      });
      if (!response.ok) throw new Error('HTTP ' + response.status);
      const json = await response.json();
      let properties = [];
      if (json.data) {
        if (Array.isArray(json.data))            properties = json.data;
        else if (Array.isArray(json.data.data))  properties = json.data.data;
        else if (json.data.properties)           properties = json.data.properties;
      } else if (Array.isArray(json.properties)) {
        properties = json.properties;
      } else if (Array.isArray(json)) {
        properties = json;
      }
      window._dmFeaturedProperties = properties; // cache
      renderCards(properties);
    } catch (err) {
      console.warn('[DreamMulk] Featured properties fetch failed:', err);
      skeleton.style.display = 'none';
      errEl.style.display    = 'block';
    }
  };

  fetchProperties();

})();
</script>
</body>
</html>
