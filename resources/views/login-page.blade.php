<!DOCTYPE html>
<html lang="en" dir="ltr" id="html-root">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Dream Mulk — Sign In</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,800;1,400&family=DM+Sans:wght@300;400;500;600&family=Noto+Sans+Arabic:wght@300;400;500;600;700&family=Noto+Naskh+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<style>
/* ── RESET & TOKENS ─────────────────────────────────── */
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;-webkit-tap-highlight-color:transparent;}
:root{
  --P:#303b97;--PD:#1a225a;--PM:#232d7a;
  --G:#c9a227;--GL:#dbb83d;
  --surface:#ffffff;
  --surface2:#f7f5f0;
  --border:#e4ddd2;
  --txt:#16162a;
  --txt-muted:#5e5b72;
  --txt-dim:#9b97ae;
  --input-bg:#faf8f5;
  --input-h:56px;
  --E:cubic-bezier(.16,1,.3,1);
  --r:14px;
  --font-ar:'Noto Sans Arabic',sans-serif;
  --font-ar-h:'Noto Naskh Arabic',serif;
}
html,body{height:100%;background:#f0ece3;}
body{font-family:'DM Sans',sans-serif;color:var(--txt);-webkit-font-smoothing:antialiased;overflow-x:hidden;}
body.rtl{font-family:var(--font-ar);}

/* ── PAGE SHELL ─────────────────────────────────────── */
.page{min-height:100vh;display:flex;}

/* ── LEFT — PHOTO PANEL ─────────────────────────────── */
.panel-left{
  width:44%;flex-shrink:0;position:relative;overflow:hidden;
}
.burj-img{
  position:absolute;inset:0;width:100%;height:100%;
  object-fit:cover;object-position:center top;
  filter:brightness(.72) saturate(1.1);
}
/* dark gradient overlay so text pops */
.left-overlay{
  position:absolute;inset:0;
  background:
    linear-gradient(to bottom, rgba(10,16,50,.55) 0%, rgba(10,16,50,.18) 35%, rgba(10,16,50,.08) 60%, rgba(10,16,50,.85) 100%);
  z-index:1;
}
.left-top{
  position:absolute;top:0;left:0;right:0;
  padding:44px 52px 0;
  z-index:2;
}
.left-bottom{
  position:absolute;bottom:0;left:0;right:0;
  padding:0 52px 48px;
  z-index:2;
}
.brand-row{display:flex;align-items:center;gap:14px;}
.brand-icon{
  width:50px;height:50px;border-radius:14px;
  background:rgba(255,255,255,.12);border:1.5px solid rgba(255,255,255,.25);
  display:flex;align-items:center;justify-content:center;
  font-size:20px;color:var(--G);backdrop-filter:blur(8px);
}
.brand-name{
  font-family:'Playfair Display',serif !important;
  font-size:23px;font-weight:700;color:#fff;letter-spacing:-.3px;
  text-shadow:0 2px 12px rgba(0,0,0,.4);
}
.left-eyebrow{
  font-size:11px;font-weight:600;letter-spacing:4px;text-transform:uppercase;
  color:var(--G);margin-bottom:12px;
  display:flex;align-items:center;gap:10px;
}
.left-eyebrow::after{content:'';width:36px;height:1px;background:var(--G);opacity:.6;}
[dir="rtl"] .left-eyebrow{flex-direction:row-reverse;letter-spacing:.5px;font-family:var(--font-ar);}
[dir="rtl"] .left-eyebrow::after{display:none;}
[dir="rtl"] .left-eyebrow::before{content:'';width:36px;height:1px;background:var(--G);opacity:.6;}
.left-headline{
  font-family:'Playfair Display',serif !important;
  font-size:clamp(30px,3.2vw,44px);font-weight:800;
  line-height:1.08;letter-spacing:-1.2px;color:#fff;margin-bottom:10px;
  text-shadow:0 3px 18px rgba(0,0,0,.5);
}
.left-headline em{font-style:italic;color:var(--G);}
[dir="rtl"] .left-headline{font-family:var(--font-ar-h) !important;letter-spacing:0;line-height:1.4;}
.left-desc{
  font-size:14.5px;line-height:1.85;color:rgba(255,255,255,.72);
  font-weight:300;max-width:320px;margin-bottom:36px;
  text-shadow:0 1px 8px rgba(0,0,0,.4);
}
[dir="rtl"] .left-desc{font-family:var(--font-ar);}
/* stats row */
.stats-row{display:flex;gap:28px;}
[dir="rtl"] .stats-row{flex-direction:row-reverse;}
.stat-item{text-align:center;}
.stat-num{
  font-family:'Playfair Display',serif !important;
  font-size:26px;font-weight:700;color:var(--G);line-height:1;
}
.stat-lbl{font-size:10.5px;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.45);margin-top:2px;}
[dir="rtl"] .stat-lbl{letter-spacing:.2px;font-family:var(--font-ar);font-size:11px;}

/* ── RIGHT — FORM PANEL ─────────────────────────────── */
.panel-right{
  flex:1;background:var(--surface);
  display:flex;flex-direction:column;
  overflow-y:auto;position:relative;
}
.panel-right::before{
  content:'';position:absolute;top:0;left:0;right:0;height:4px;
  background:linear-gradient(90deg,var(--PD),var(--P),var(--G));z-index:2;
}
.form-shell{
  max-width:500px;width:100%;margin:0 auto;
  padding:48px 44px 44px;
  flex:1;display:flex;flex-direction:column;justify-content:center;
}

/* top row */
.top-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:32px;}
.back-link{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--txt-muted);text-decoration:none;transition:color .25s;}
.back-link:hover{color:var(--P);}
[dir="rtl"] .back-link i{transform:scaleX(-1);}
.lang-sw{display:flex;background:var(--surface2);border:1px solid var(--border);border-radius:50px;padding:3px;gap:2px;}
.lb{padding:5px 13px;border-radius:50px;border:none;background:transparent;color:var(--txt-muted);font-size:12px;font-weight:600;letter-spacing:.4px;cursor:pointer;transition:all .25s;font-family:'DM Sans',sans-serif;}
.lb.active{background:var(--P);color:#fff;}
.lb:hover:not(.active){background:var(--border);color:var(--txt);}

/* ══ WHO ARE YOU — BIG CLEAR PORTAL CARDS ══════════════
   This is the most important UI element — must be
   unmistakable even for non-tech / elderly users.
   Active card expands significantly.
═══════════════════════════════════════════════════════ */
.who-label{
  font-size:20px;font-weight:800;color:var(--txt);
  margin-bottom:6px;letter-spacing:-.3px;
}
[dir="rtl"] .who-label{font-family:var(--font-ar-h);font-size:21px;}
.who-hint{
  font-size:14px;color:var(--txt-muted);margin-bottom:22px;line-height:1.6;
}
[dir="rtl"] .who-hint{font-family:var(--font-ar);}

.portal-cards{display:flex;flex-direction:column;gap:12px;margin-bottom:30px;}

/* ── BASE CARD (unselected) ── */
.portal-card{
  display:flex;align-items:center;gap:20px;
  padding:20px 22px;
  border:2px solid var(--border);
  border-radius:18px;
  background:var(--surface2);
  cursor:pointer;
  transition:all .4s var(--E);
  position:relative;
  user-select:none;
  -webkit-user-select:none;
  overflow:hidden;
}
.portal-card:hover{
  border-color:rgba(48,59,151,.35);
  background:#fff;
  box-shadow:0 6px 24px rgba(48,59,151,.1);
  transform:translateY(-2px);
}

/* ── ACTIVE CARD — much bigger, very obvious ── */
.portal-card.active{
  border-color:var(--P);
  border-width:2.5px;
  background:#fff;
  box-shadow:0 0 0 5px rgba(48,59,151,.1), 0 12px 40px rgba(48,59,151,.18);
  padding:28px 26px;   /* extra vertical padding */
  transform:translateY(-2px);
}
.portal-card.active::before{
  content:'';
  position:absolute;top:0;left:0;right:0;height:4px;
  border-radius:0;
}
.pc-user.active::before{background:linear-gradient(90deg,#16a34a,#22c55e);}
.pc-agent.active::before{background:linear-gradient(90deg,var(--PD),var(--P));}
.pc-office.active::before{background:linear-gradient(90deg,#a07a10,var(--G));}

/* selected checkmark badge */
.portal-card.active::after{
  content:'\f058';
  font-family:'Font Awesome 6 Free';
  font-weight:900;
  position:absolute;
  top:18px;right:20px;
  color:var(--P);font-size:24px;
  animation:popIn .3s var(--E);
}
@keyframes popIn{from{transform:scale(0);opacity:0;}to{transform:scale(1);opacity:1;}}
[dir="rtl"] .portal-card.active::after{right:auto;left:20px;}
[dir="rtl"] .portal-card{flex-direction:row-reverse;}

/* ── ICON WRAP ── */
.pc-icon-wrap{
  flex-shrink:0;
  border-radius:16px;
  display:flex;align-items:center;justify-content:center;
  transition:all .4s var(--E);
  /* default size */
  width:62px;height:62px;font-size:28px;
}
/* active: icon gets bigger */
.portal-card.active .pc-icon-wrap{
  width:76px;height:76px;font-size:34px;
  border-radius:20px;
}
.pc-user .pc-icon-wrap{background:rgba(34,197,94,.12);color:#16a34a;}
.pc-agent .pc-icon-wrap{background:rgba(48,59,151,.12);color:var(--P);}
.pc-office .pc-icon-wrap{background:rgba(201,162,39,.15);color:#a07a10;}
.portal-card.active.pc-user .pc-icon-wrap{background:rgba(34,197,94,.18);color:#15803d;}
.portal-card.active.pc-agent .pc-icon-wrap{background:rgba(48,59,151,.16);color:var(--PD);}
.portal-card.active.pc-office .pc-icon-wrap{background:rgba(201,162,39,.22);color:#92700c;}

/* ── TEXT ── */
.pc-text{flex:1;min-width:0;}
.pc-title{
  font-size:17px;font-weight:700;color:var(--txt);
  margin-bottom:4px;line-height:1.25;
  transition:all .3s;
}
/* active: title bigger and colored */
.portal-card.active .pc-title{
  font-size:19px;font-weight:800;color:var(--P);
}
.pc-user.active .pc-title{color:#15803d;}
.pc-agent.active .pc-title{color:var(--PD);}
.pc-office.active .pc-title{color:#92700c;}
[dir="rtl"] .pc-title{font-family:var(--font-ar-h);}
[dir="rtl"] .portal-card.active .pc-title{font-size:20px;}
.pc-desc{
  font-size:13px;color:var(--txt-muted);line-height:1.55;
  transition:all .3s;
}
/* active: desc slightly bigger */
.portal-card.active .pc-desc{
  font-size:14px;color:var(--txt-muted);
  margin-top:2px;
}
[dir="rtl"] .pc-desc{font-family:var(--font-ar);}

/* "selected" label badge on active card */
.pc-badge{
  display:none;
  font-size:11px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;
  padding:3px 10px;border-radius:20px;margin-top:8px;width:fit-content;
}
.portal-card.active .pc-badge{display:block;}
.pc-user.active .pc-badge{background:rgba(34,197,94,.12);color:#15803d;}
.pc-agent.active .pc-badge{background:rgba(48,59,151,.1);color:var(--PD);}
.pc-office.active .pc-badge{background:rgba(201,162,39,.15);color:#92700c;}
[dir="rtl"] .pc-badge{font-family:var(--font-ar);letter-spacing:.2px;}

/* WRONG PORTAL WARNING — shown when user tries wrong portal */
.wrong-portal-msg{
  display:none;
  background:#fff8e1;border:1.5px solid #f59e0b;
  border-radius:12px;padding:14px 16px;
  font-size:13.5px;color:#92400e;
  margin-bottom:16px;line-height:1.55;
  align-items:flex-start;gap:10px;
}
[dir="rtl"] .wrong-portal-msg{font-family:var(--font-ar);}
.wrong-portal-msg.show{display:flex;}
.wrong-portal-msg i{font-size:18px;color:#f59e0b;flex-shrink:0;margin-top:1px;}

/* auth sub-tabs */
.auth-tabs{display:flex;border-bottom:2px solid var(--border);margin-bottom:24px;}
.auth-tab{
  flex:1;text-align:center;padding:10px 0;
  font-size:14px;font-weight:600;color:var(--txt-dim);
  cursor:pointer;border-bottom:2.5px solid transparent;margin-bottom:-2px;
  transition:all .28s var(--E);
}
[dir="rtl"] .auth-tab{font-family:var(--font-ar);}
.auth-tab.active{color:var(--P);border-bottom-color:var(--P);}
.auth-tab:hover:not(.active){color:var(--txt);}

/* form sections / panels */
.form-section{display:none;}
.form-section.active{display:block;animation:fadeUp .35s var(--E);}
@keyframes fadeUp{from{opacity:0;transform:translateY(8px);}to{opacity:1;transform:translateY(0);}}
.panel{display:none;}
.panel.active{display:block;animation:fadeUp .3s var(--E);}

.form-title{font-family:'Playfair Display',serif !important;font-size:21px;font-weight:700;color:var(--txt);margin-bottom:4px;}
[dir="rtl"] .form-title{font-family:var(--font-ar-h) !important;}
.form-sub{font-size:13.5px;color:var(--txt-muted);margin-bottom:22px;line-height:1.6;}
[dir="rtl"] .form-sub{font-family:var(--font-ar);}

/* ── INPUTS ─────────────────────────────────────────── */
.ibox{margin-bottom:15px;}
.ibox label{display:block;font-size:12px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;color:var(--txt-muted);margin-bottom:6px;}
[dir="rtl"] .ibox label{letter-spacing:.1px;font-family:var(--font-ar);text-transform:none;}
.ifield{position:relative;}
.ifield i.ico{position:absolute;top:50%;transform:translateY(-50%);left:15px;font-size:15px;color:var(--txt-dim);pointer-events:none;transition:color .25s;z-index:1;}
[dir="rtl"] .ifield i.ico{left:auto;right:15px;}
.ifield input{
  width:100%;height:var(--input-h);
  background:var(--input-bg);border:1.5px solid var(--border);border-radius:var(--r);
  padding:0 46px;font-size:15px;color:var(--txt);
  font-family:'DM Sans',sans-serif;outline:none;
  transition:border-color .25s,box-shadow .25s,background .25s;
}
[dir="rtl"] .ifield input{font-family:var(--font-ar);}
.ifield input::placeholder{color:var(--txt-dim);font-size:13.5px;}
.ifield input:focus{border-color:var(--P);background:#fff;box-shadow:0 0 0 4px rgba(48,59,151,.09);}
.ifield:focus-within i.ico{color:var(--P);}
.ifield input[type="file"]{height:auto;padding:14px 14px 14px 46px;font-size:13px;}
[dir="rtl"] .ifield input[type="file"]{padding:14px 46px 14px 14px;}
.eye-btn{position:absolute;top:50%;transform:translateY(-50%);right:13px;border:none;background:none;color:var(--txt-dim);font-size:16px;cursor:pointer;padding:6px;transition:color .25s;z-index:1;}
[dir="rtl"] .eye-btn{right:auto;left:13px;}
.eye-btn:hover{color:var(--P);}

/* Validation */
.err{font-size:12.5px;color:#c0392b;margin-top:5px;display:block;}
.alert-box{padding:12px 14px;border-radius:10px;font-size:13.5px;margin-bottom:16px;display:flex;align-items:center;gap:10px;line-height:1.5;}
.alert-box.danger{background:#fef2f2;color:#991b1b;border:1px solid #fecaca;}
.alert-box.success{background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;}

/* Options row */
.opt-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;}
[dir="rtl"] .opt-row{flex-direction:row-reverse;}
.chk-wrap{display:flex;align-items:center;gap:9px;cursor:pointer;}
.chk-wrap input[type=checkbox]{width:19px;height:19px;border-radius:5px;accent-color:var(--P);cursor:pointer;flex-shrink:0;}
.chk-wrap span{font-size:14px;color:var(--txt-muted);}
[dir="rtl"] .chk-wrap span{font-family:var(--font-ar);}
.forgot-link{font-size:13.5px;color:var(--P);font-weight:500;text-decoration:none;transition:color .25s;}
.forgot-link:hover{color:var(--PD);}

/* Submit */
.submit-btn{
  width:100%;height:54px;
  background:linear-gradient(135deg,var(--P) 0%,var(--PM) 100%);
  color:#fff;font-family:'DM Sans',sans-serif;
  font-size:15px;font-weight:700;letter-spacing:1px;text-transform:uppercase;
  border:none;border-radius:var(--r);cursor:pointer;
  display:flex;align-items:center;justify-content:center;gap:10px;
  transition:all .35s var(--E);
  box-shadow:0 4px 20px rgba(48,59,151,.3);
  position:relative;overflow:hidden;
}
[dir="rtl"] .submit-btn{font-family:var(--font-ar);letter-spacing:.2px;}
.submit-btn::after{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(255,255,255,.12),transparent);opacity:0;transition:opacity .3s;}
.submit-btn:hover{transform:translateY(-2px);box-shadow:0 10px 30px rgba(48,59,151,.4);}
.submit-btn:hover::after{opacity:1;}
.submit-btn:active{transform:translateY(0);}

/* Divider + Google */
.divider{display:flex;align-items:center;gap:12px;margin:18px 0;}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--border);}
.divider span{font-size:11.5px;color:var(--txt-dim);letter-spacing:1px;text-transform:uppercase;white-space:nowrap;}
.g-btn{
  width:100%;height:50px;border-radius:var(--r);border:1.5px solid var(--border);
  background:var(--surface2);color:var(--txt);font-family:'DM Sans',sans-serif;
  font-size:14px;font-weight:500;cursor:pointer;
  display:flex;align-items:center;justify-content:center;gap:12px;
  transition:all .3s var(--E);
}
.g-btn:hover{border-color:var(--P);background:#fff;box-shadow:0 2px 12px rgba(48,59,151,.1);}
.g-btn svg{width:20px;height:20px;flex-shrink:0;}

/* Switch / note */
.switch-text{text-align:center;font-size:13.5px;color:var(--txt-muted);margin-top:18px;}
.switch-text a,.portal-note a{color:var(--P);font-weight:600;text-decoration:none;transition:color .25s;}
.switch-text a:hover,.portal-note a:hover{color:var(--PD);}
.portal-note{text-align:center;font-size:13px;color:var(--txt-dim);margin-top:14px;line-height:1.7;}

/* scroll */
.panel-right::-webkit-scrollbar{width:4px;}
.panel-right::-webkit-scrollbar-track{background:transparent;}
.panel-right::-webkit-scrollbar-thumb{background:var(--border);border-radius:4px;}
*:focus-visible{outline:3px solid var(--P);outline-offset:2px;border-radius:4px;}

/* ── RESPONSIVE ─────────────────────────────────────── */
@media(max-width:960px){
  .panel-left{display:none;}
  .panel-right{width:100%;}
  .form-shell{padding:40px 26px;}
  html,body{overflow:auto;}
}
@media(max-width:480px){
  .form-shell{padding:30px 16px;}
  .portal-card{padding:15px 16px;gap:14px;}
  .pc-icon-wrap{width:50px;height:50px;font-size:22px;}
  .pc-title{font-size:16px;}
}
</style>
</head>
<body>

<div class="page">

  <!-- ══ LEFT — BURJ KHALIFA PHOTO ════════════════════════ -->
  <div class="panel-left">
    <!-- Burj Khalifa image from Unsplash (free, no attribution required) -->
    <img
      src="https://images.unsplash.com/photo-1512453979798-5ea266f8880c?w=900&q=85&fit=crop&auto=format"
      alt="Dubai skyline"
      class="burj-img"
    />
    <div class="left-overlay"></div>

    <!-- Brand top-left -->
    <div class="left-top">
      <div class="brand-row">
        <div class="brand-icon"><i class="fas fa-building"></i></div>
        <span class="brand-name">Dream Mulk</span>
      </div>
    </div>

    <!-- Headline + stats bottom -->
    <div class="left-bottom">
      <div class="left-eyebrow" id="lp-eyebrow">Premium Real Estate</div>
      <h2 class="left-headline" id="lp-headline">Your Dream<br>Property <em>Awaits</em></h2>
      <p class="left-desc" id="lp-desc">Kurdistan's most trusted platform to buy, sell and rent — zero commission, full transparency.</p>
      <div class="stats-row">
        <div class="stat-item"><div class="stat-num">500+</div><div class="stat-lbl" id="ls-props">Properties</div></div>
        <div class="stat-item"><div class="stat-num">150+</div><div class="stat-lbl" id="ls-agents">Agents</div></div>
        <div class="stat-item"><div class="stat-num">0%</div><div class="stat-lbl" id="ls-comm">Commission</div></div>
      </div>
    </div>
  </div>

  <!-- ══ RIGHT — FORM ════════════════════════════════════ -->
  <div class="panel-right">
    <div class="form-shell">

      <!-- Back + Lang -->
      <div class="top-row">
        <a href="{{ route('newindex') }}" class="back-link">
          <i class="fas fa-arrow-left"></i>&nbsp;<span id="back-txt">Back to Home</span>
        </a>
        <div class="lang-sw">
          <button class="lb" onclick="setLang('en',this)">EN</button>
          <button class="lb" onclick="setLang('ar',this)">ع</button>
          <button class="lb" onclick="setLang('ku',this)">کو</button>
        </div>
      </div>

      <!-- ══ WHO ARE YOU — LARGE CLEAR CARDS ═════════════
           Each card shows: big icon + bold title + plain
           human description so anyone knows which to pick.
      ═══════════════════════════════════════════════════ -->
      <div class="who-label" id="who-label">Who are you?</div>
      <div class="who-hint" id="who-hint">Choose the option that matches you — this is important!</div>

      <!-- Wrong portal warning -->
      <div class="wrong-portal-msg" id="wrong-portal-msg">
        <i class="fas fa-exclamation-triangle"></i>
        <span id="wrong-portal-txt">Please make sure you pick the right option above before signing in.</span>
      </div>

      <div class="portal-cards">

        <div class="portal-card pc-user active" id="pcard-user" onclick="switchPortal('user')">
          <div class="pc-icon-wrap"><i class="fas fa-user"></i></div>
          <div class="pc-text">
            <div class="pc-title" id="pc-user-title">I am a regular person</div>
            <div class="pc-desc" id="pc-user-desc">I want to buy, sell or rent a property for myself or my family</div>
            <div class="pc-badge" id="pc-user-badge">✓ Selected</div>
          </div>
        </div>

        <div class="portal-card pc-agent" id="pcard-agent" onclick="switchPortal('agent')">
          <div class="pc-icon-wrap"><i class="fas fa-id-badge"></i></div>
          <div class="pc-text">
            <div class="pc-title" id="pc-agent-title">I am a property agent</div>
            <div class="pc-desc" id="pc-agent-desc">I work as an agent and I list properties on behalf of clients</div>
            <div class="pc-badge" id="pc-agent-badge">✓ Selected</div>
          </div>
        </div>

        <div class="portal-card pc-office" id="pcard-office" onclick="switchPortal('office')">
          <div class="pc-icon-wrap"><i class="fas fa-building-user"></i></div>
          <div class="pc-text">
            <div class="pc-title" id="pc-office-title">I own a real estate office</div>
            <div class="pc-desc" id="pc-office-desc">I have a registered real estate company or office with multiple agents</div>
            <div class="pc-badge" id="pc-office-badge">✓ Selected</div>
          </div>
        </div>

      </div><!-- /portal-cards -->

      <!-- ─── USER SECTION ───────────────────────────── -->
      <div class="form-section active" id="section-user">
        <div class="auth-tabs">
          <div class="auth-tab active" id="utab-login" onclick="switchAuthTab('user','login')"><span id="u-si">Sign In</span></div>
          <div class="auth-tab" id="utab-signup" onclick="switchAuthTab('user','signup')"><span id="u-ca">Create Account</span></div>
        </div>
        <!-- USER LOGIN -->
        <div class="panel active" id="upanel-login">
          <div class="form-title" id="u-tl">Welcome back</div>
          <div class="form-sub" id="u-sl">Enter your email and password to sign in.</div>
          @if(session('error') && old('active_form') === 'login-section')
            <div class="alert-box danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
          @endif
          <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="ibox">
              <label id="fl-email">Email Address</label>
              <div class="ifield"><i class="fas fa-envelope ico"></i>
                <input type="email" name="email" placeholder="you@example.com" value="{{ old('email') }}" required/>
              </div>
              @error('email')<span class="err">{{ $message }}</span>@enderror
            </div>
            <div class="ibox">
              <label id="fl-pwd">Password</label>
              <div class="ifield"><i class="fas fa-lock ico"></i>
                <input type="password" id="ulp" name="password" placeholder="••••••••" required/>
                <button type="button" class="eye-btn" onclick="togglePw('ulp',this)"><i class="fas fa-eye-slash"></i></button>
              </div>
              @error('password')<span class="err">{{ $message }}</span>@enderror
            </div>
            <div class="opt-row">
              <label class="chk-wrap"><input type="checkbox" name="remember"/><span id="fl-rem">Remember me</span></label>
              <a href="#" class="forgot-link" id="fl-fgt">Forgot password?</a>
            </div>
            <button type="submit" class="submit-btn"><i class="fas fa-arrow-right-to-bracket"></i><span id="fl-sub">Sign In</span></button>
          </form>
          <div class="divider"><span id="fl-or">or continue with</span></div>
          <button class="g-btn" onclick="triggerGoogle()">
            <svg viewBox="0 0 48 48"><path fill="#FFC107" d="M43.6 20.1H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34.5 6.5 29.5 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.6-.4-3.9z"/><path fill="#FF3D00" d="m6.3 14.7 6.6 4.8C14.7 16 19 13 24 13c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34.5 6.5 29.5 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/><path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.3 35.1 26.8 36 24 36c-5.2 0-9.6-3.3-11.3-8H6.4C9.7 35.6 16.3 44 24 44z"/><path fill="#1976D2" d="M43.6 20.1H42V20H24v8h11.3c-.8 2.3-2.3 4.2-4.2 5.6l6.2 5.2C37 39 44 34 44 24c0-1.3-.1-2.6-.4-3.9z"/></svg>
            <span id="fl-ggl">Continue with Google</span>
          </button>
          <div class="switch-text" id="u-sw-l"></div>
          <div id="google_button" style="display:none;"></div>
        </div>
        <!-- USER REGISTER -->
        <div class="panel" id="upanel-signup">
          <div class="form-title" id="u-ts">Create your account</div>
          <div class="form-sub" id="u-ss">Fill in your details to get started on Dream Mulk.</div>
          @if(session('success'))<div class="alert-box success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>@endif
          @if(session('error') && !old('active_form'))<div class="alert-box danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>@endif
          <form action="{{ route('user.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="role" value="user"/>
            <div class="ibox"><label id="fs-uname">Username</label><div class="ifield"><i class="fas fa-user ico"></i><input type="text" name="username" placeholder="johndoe" value="{{ old('username') }}" required/></div>@error('username')<span class="err">{{ $message }}</span>@enderror</div>
            <div class="ibox"><label id="fs-email">Email Address</label><div class="ifield"><i class="fas fa-envelope ico"></i><input type="email" name="email" placeholder="you@example.com" value="{{ old('email', session('email')) }}" required/></div>@error('email')<span class="err">{{ $message }}</span>@enderror</div>
            <div class="ibox"><label id="fs-pwd">Password</label><div class="ifield"><i class="fas fa-lock ico"></i><input type="password" id="usp" name="password" placeholder="Create a strong password" required/><button type="button" class="eye-btn" onclick="togglePw('usp',this)"><i class="fas fa-eye-slash"></i></button></div>@error('password')<span class="err">{{ $message }}</span>@enderror</div>
            <div class="ibox"><label id="fs-cpwd">Confirm Password</label><div class="ifield"><i class="fas fa-lock ico"></i><input type="password" id="uscp" name="password_confirmation" placeholder="Repeat your password" required/><button type="button" class="eye-btn" onclick="togglePw('uscp',this)"><i class="fas fa-eye-slash"></i></button></div></div>
            <div class="ibox"><label id="fs-phone">Phone Number</label><div class="ifield"><i class="fas fa-phone ico"></i><input type="tel" name="phone" placeholder="07XX XXX XXXX" value="{{ old('phone') }}" required/></div>@error('phone')<span class="err">{{ $message }}</span>@enderror</div>
            <div class="ibox"><label id="fs-img">Profile Image <span style="font-weight:400;text-transform:none;font-size:11px;color:var(--txt-dim);" id="fs-opt">(optional)</span></label><div class="ifield"><i class="fas fa-image ico"></i><input type="file" name="image" accept="image/*"/></div></div>
            <button type="submit" class="submit-btn" style="margin-top:4px;"><i class="fas fa-user-plus"></i><span id="fs-sub">Create Account</span></button>
          </form>
          <div class="switch-text" id="u-sw-s"></div>
        </div>
      </div>

      <!-- ─── AGENT SECTION ──────────────────────────── -->
      <div class="form-section" id="section-agent">
        <div class="auth-tabs">
          <div class="auth-tab active" id="atab-login" onclick="switchAuthTab('agent','login')"><span id="a-si">Sign In</span></div>
          <div class="auth-tab" id="atab-signup" onclick="switchAuthTab('agent','signup')"><span id="a-rg">Register</span></div>
        </div>
        <!-- AGENT LOGIN -->
        <div class="panel active" id="apanel-login">
          <div class="form-title" id="a-tl">Agent Portal</div>
          <div class="form-sub" id="a-sl">Sign in to manage your listings and appointments.</div>
          @if(session('error'))<div class="alert-box danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>@endif
          <form action="{{ route('agent.login.submit') }}" method="POST">
            @csrf
            <div class="ibox"><label id="al-email">Email Address</label><div class="ifield"><i class="fas fa-envelope ico"></i><input type="email" name="email" placeholder="agent@example.com" value="{{ old('email') }}" required/></div></div>
            <div class="ibox"><label id="al-pwd">Password</label><div class="ifield"><i class="fas fa-lock ico"></i><input type="password" id="alp" name="password" placeholder="••••••••" required/><button type="button" class="eye-btn" onclick="togglePw('alp',this)"><i class="fas fa-eye-slash"></i></button></div></div>
            <div class="opt-row">
              <label class="chk-wrap"><input type="checkbox" name="remember"/><span id="al-rem">Remember me</span></label>
              <a href="#" class="forgot-link" id="al-fgt">Forgot password?</a>
            </div>
            <button type="submit" class="submit-btn"><i class="fas fa-arrow-right-to-bracket"></i><span id="al-sub">Sign In</span></button>
          </form>
          <div class="portal-note" id="a-sw-l"></div>
        </div>
        <!-- AGENT REGISTER -->
        <div class="panel" id="apanel-signup">
          <div class="form-title" id="a-ts">Agent Registration</div>
          <div class="form-sub" id="a-ss">Create your agent account to list properties.</div>
          <form action="{{ route('agent.register.submit') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="ibox"><label id="ar-fn">Full Name</label><div class="ifield"><i class="fas fa-user ico"></i><input type="text" name="name" placeholder="John Doe" required/></div></div>
            <div class="ibox"><label id="ar-email">Email Address</label><div class="ifield"><i class="fas fa-envelope ico"></i><input type="email" name="email" placeholder="agent@example.com" required/></div></div>
            <div class="ibox"><label id="ar-phone">Phone Number</label><div class="ifield"><i class="fas fa-phone ico"></i><input type="tel" name="phone" placeholder="07XX XXX XXXX" required/></div></div>
            <div class="ibox"><label id="ar-pwd">Password</label><div class="ifield"><i class="fas fa-lock ico"></i><input type="password" id="asp" name="password" placeholder="Create a strong password" required/><button type="button" class="eye-btn" onclick="togglePw('asp',this)"><i class="fas fa-eye-slash"></i></button></div></div>
            <div class="ibox"><label id="ar-cpwd">Confirm Password</label><div class="ifield"><i class="fas fa-lock ico"></i><input type="password" id="ascp" name="password_confirmation" placeholder="Repeat your password" required/><button type="button" class="eye-btn" onclick="togglePw('ascp',this)"><i class="fas fa-eye-slash"></i></button></div></div>
            <button type="submit" class="submit-btn" style="margin-top:4px;"><i class="fas fa-id-badge"></i><span id="ar-sub">Register as Agent</span></button>
          </form>
          <div class="switch-text" id="a-sw-s"></div>
        </div>
      </div>

      <!-- ─── OFFICE SECTION ─────────────────────────── -->
      <div class="form-section" id="section-office">
        <div class="auth-tabs">
          <div class="auth-tab active" id="otab-login" onclick="switchAuthTab('office','login')"><span id="o-si">Sign In</span></div>
          <div class="auth-tab" id="otab-signup" onclick="switchAuthTab('office','signup')"><span id="o-rg">Register</span></div>
        </div>
        <!-- OFFICE LOGIN -->
        <div class="panel active" id="opanel-login">
          <div class="form-title" id="o-tl">Office Portal</div>
          <div class="form-sub" id="o-sl">Sign in to manage your office, agents, and properties.</div>
          @if(session('error'))<div class="alert-box danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>@endif
          <form action="{{ route('office.login.submit') }}" method="POST">
            @csrf
            <div class="ibox"><label id="ol-email">Office Email</label><div class="ifield"><i class="fas fa-envelope ico"></i><input type="email" name="email" placeholder="office@dreammulk.com" required/></div></div>
            <div class="ibox"><label id="ol-pwd">Password</label><div class="ifield"><i class="fas fa-lock ico"></i><input type="password" id="olp" name="password" placeholder="••••••••" required/><button type="button" class="eye-btn" onclick="togglePw('olp',this)"><i class="fas fa-eye-slash"></i></button></div></div>
            <div class="opt-row">
              <label class="chk-wrap"><input type="checkbox" name="remember"/><span id="ol-rem">Remember me</span></label>
              <a href="#" class="forgot-link" id="ol-fgt">Forgot password?</a>
            </div>
            <button type="submit" class="submit-btn"><i class="fas fa-arrow-right-to-bracket"></i><span id="ol-sub">Sign In</span></button>
          </form>
          <div class="portal-note" id="o-sw-l"></div>
        </div>
        <!-- OFFICE REGISTER -->
        <div class="panel" id="opanel-signup">
          <div class="form-title" id="o-ts">Office Registration</div>
          <div class="form-sub" id="o-ss">Register your real estate office on Dream Mulk.</div>
          <form action="{{ route('office.register.submit') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="ibox"><label id="or-name">Office Name</label><div class="ifield"><i class="fas fa-building ico"></i><input type="text" name="name" placeholder="Al-Salam Real Estate" required/></div></div>
            <div class="ibox"><label id="or-email">Email Address</label><div class="ifield"><i class="fas fa-envelope ico"></i><input type="email" name="email" placeholder="office@example.com" required/></div></div>
            <div class="ibox"><label id="or-phone">Phone Number</label><div class="ifield"><i class="fas fa-phone ico"></i><input type="tel" name="phone" placeholder="07XX XXX XXXX" required/></div></div>
            <div class="ibox"><label id="or-city">City</label><div class="ifield"><i class="fas fa-map-marker-alt ico"></i><input type="text" name="city" placeholder="Erbil / Sulaymaniyah / Duhok" required/></div></div>
            <div class="ibox"><label id="or-pwd">Password</label><div class="ifield"><i class="fas fa-lock ico"></i><input type="password" id="osp" name="password" placeholder="Create a strong password" required/><button type="button" class="eye-btn" onclick="togglePw('osp',this)"><i class="fas fa-eye-slash"></i></button></div></div>
            <div class="ibox"><label id="or-cpwd">Confirm Password</label><div class="ifield"><i class="fas fa-lock ico"></i><input type="password" id="oscp" name="password_confirmation" placeholder="Repeat your password" required/><button type="button" class="eye-btn" onclick="togglePw('oscp',this)"><i class="fas fa-eye-slash"></i></button></div></div>
            <button type="submit" class="submit-btn" style="margin-top:4px;"><i class="fas fa-building-user"></i><span id="or-sub">Register Office</span></button>
          </form>
          <div class="switch-text" id="o-sw-s"></div>
        </div>
      </div>

    </div><!-- /form-shell -->
  </div><!-- /panel-right -->
</div><!-- /page -->

<script src="https://accounts.google.com/gsi/client" async defer></script>
<script>
/* ══ TRANSLATIONS ══════════════════════════════════════ */
const TR = {
  en:{dir:'ltr',
    back:'Back to Home',
    whoLabel:'Who are you?',
    whoHint:'Choose the option that matches you — this is important!',
    wrongPortal:'⚠️ Make sure you selected the right option above before signing in. Office owners must select "I own a real estate office".',
    pcUserTitle:'I am a regular person',
    pcUserDesc:'I want to buy, sell or rent a property for myself or my family',
    pcAgentTitle:'I am a property agent',
    pcAgentDesc:'I work as an agent and I list properties on behalf of clients',
    pcOfficeTitle:'I own a real estate office',
    pcOfficeDesc:'I have a registered real estate company or office with multiple agents',
    signIn:'Sign In',register:'Register',createAccount:'Create Account',
    uTl:'Welcome back',uSl:'Enter your email and password to sign in.',
    uTs:'Create your account',uSs:'Fill in your details to get started on Dream Mulk.',
    aTl:'Agent Portal',aSl:'Sign in to manage your listings and appointments.',
    aTs:'Agent Registration',aSs:'Create your agent account to list properties.',
    oTl:'Office Portal',oSl:'Sign in to manage your office, agents, and properties.',
    oTs:'Office Registration',oSs:'Register your real estate office on Dream Mulk.',
    email:'Email Address',password:'Password',confirmPwd:'Confirm Password',
    phone:'Phone Number',username:'Username',fullName:'Full Name',
    officeName:'Office Name',city:'City',officeEmail:'Office Email',
    profileImg:'Profile Image',optional:'(optional)',
    remember:'Remember me',forgot:'Forgot password?',
    or:'or continue with',google:'Continue with Google',
    subLogin:'Sign In',createAccount2:'Create Account',subAgent:'Register as Agent',subOffice:'Register Office',
    noAcc:"Don't have an account?",createOne:'Create one',haveAcc:'Already have an account?',
    noAgentAcc:"Don't have an agent account?",regNow:'Register now',
    noOfficeAcc:"Don't have an office account?",
    lpEyebrow:'Premium Real Estate',
    lpHeadline:'Your Dream<br>Property <em>Awaits</em>',
    lpDesc:"Kurdistan's most trusted platform to buy, sell and rent — zero commission, full transparency.",
    lsProps:'Properties',lsAgents:'Agents',lsComm:'Commission',
  },
  ar:{dir:'rtl',
    back:'العودة للرئيسية',
    whoLabel:'من أنت؟',
    whoHint:'اختر الخيار المناسب لك — هذا مهم جداً!',
    wrongPortal:'⚠️ تأكد من اختيار الخيار الصحيح أعلاه قبل تسجيل الدخول. أصحاب المكاتب يجب أن يختاروا "أمتلك مكتباً عقارياً".',
    pcUserTitle:'أنا شخص عادي',
    pcUserDesc:'أريد شراء أو بيع أو استئجار عقار لنفسي أو لعائلتي',
    pcAgentTitle:'أنا وكيل عقاري',
    pcAgentDesc:'أعمل كوكيل وأدرج العقارات نيابة عن العملاء',
    pcOfficeTitle:'أمتلك مكتباً عقارياً',
    pcOfficeDesc:'لدي شركة أو مكتب عقاري مسجل بعدة وكلاء',
    signIn:'تسجيل الدخول',register:'إنشاء حساب',createAccount:'إنشاء حساب',
    uTl:'أهلاً بعودتك',uSl:'أدخل بريدك الإلكتروني وكلمة المرور.',
    uTs:'إنشاء حساب جديد',uSs:'أدخل بياناتك للانضمام إلى Dream Mulk.',
    aTl:'بوابة الوكلاء',aSl:'سجّل دخولك لإدارة قوائمك ومواعيدك.',
    aTs:'تسجيل وكيل عقاري',aSs:'أنشئ حساب الوكيل الخاص بك.',
    oTl:'بوابة المكاتب',oSl:'سجّل دخولك لإدارة مكتبك ووكلائك.',
    oTs:'تسجيل مكتب عقاري',oSs:'سجّل مكتبك العقاري في Dream Mulk.',
    email:'البريد الإلكتروني',password:'كلمة المرور',confirmPwd:'تأكيد كلمة المرور',
    phone:'رقم الهاتف',username:'اسم المستخدم',fullName:'الاسم الكامل',
    officeName:'اسم المكتب',city:'المدينة',officeEmail:'بريد المكتب',
    profileImg:'صورة الملف الشخصي',optional:'(اختياري)',
    remember:'تذكرني',forgot:'نسيت كلمة المرور؟',
    or:'أو تابع بـ',google:'متابعة مع Google',
    subLogin:'دخول',createAccount2:'إنشاء الحساب',subAgent:'التسجيل كوكيل',subOffice:'تسجيل المكتب',
    noAcc:'ليس لديك حساب؟',createOne:'أنشئ واحداً',haveAcc:'لديك حساب بالفعل؟',
    noAgentAcc:'ليس لديك حساب وكيل؟',regNow:'سجّل الآن',
    noOfficeAcc:'ليس لديك حساب مكتب؟',
    lpEyebrow:'عقارات كردستان',
    lpHeadline:'عقارك المثالي<br><em>بانتظارك</em>',
    lpDesc:'منصة العقارات الأكثر موثوقية في كردستان. شراء وبيع وإيجار بدون عمولة.',
    lsProps:'عقار',lsAgents:'وكيل',lsComm:'عمولة',
  },
  ku:{dir:'rtl',
    back:'گەڕانەوە بۆ ماڵپەڕ',
    whoLabel:'تۆ کێیت؟',
    whoHint:'ئەو بژاردەیە هەڵبژێرە کە تۆ پێی دەگونجێ — ئەمە زۆر گرنگە!',
    wrongPortal:'⚠️ پێش چوونەژوورەوە دڵنیابە لە هەڵبژاردنی بژاردەی گونجاو لە سەرەوە. xudanî ئۆفیسەکان دەبێت "خاوەنی ئۆفیسی خانووبەرەم" هەڵبژێرن.',
    pcUserTitle:'ئادامێکی ئاساییم',
    pcUserDesc:'دەمەوێ خانوویەک بکڕم، بفرۆشم یان کرێی بدەم بۆ خۆم یان خێزانەکەم',
    pcAgentTitle:'نوێنەری خانووبەرەم',
    pcAgentDesc:'وەک نوێنەر کار دەکەم و خانووبەرە لە ناوی کڕیارەکان تۆمار دەکەم',
    pcOfficeTitle:'خاوەنی ئۆفیسی خانووبەرەم',
    pcOfficeDesc:'کۆمپانیا یان ئۆفیسێکی خانووبەرەی تۆمارکراوم هەیە کە چەندین نوێنەری هەیە',
    signIn:'چوونەژوورەوە',register:'تۆمارکردن',createAccount:'دروستکردنی ئەکاونت',
    uTl:'بەخێربێیتەوە',uSl:'ئیمەیڵ و وشەی نهێنیەکەت داخڵ بکە.',
    uTs:'دروستکردنی ئەکاونت',uSs:'زانیاریەکانت پڕبکەرەوە بۆ Dream Mulk.',
    aTl:'دەرگای نوێنەران',aSl:'بچۆرە ژوورەوە بۆ بەڕێوەبردنی خانووبەرەکانت.',
    aTs:'تۆمارکردنی نوێنەر',aSs:'ئەکاونتی نوێنەرەکەت دروست بکە.',
    oTl:'دەرگای ئۆفیسەکان',oSl:'بچۆرە ژوورەوە بۆ بەڕێوەبردنی ئۆفیسەکەت.',
    oTs:'تۆمارکردنی ئۆفیس',oSs:'ئۆفیسی خانووبەرەکەت لە Dream Mulk تۆمار بکە.',
    email:'ئیمەیڵ',password:'وشەی نهێنی',confirmPwd:'دڵنیاکردنەوەی وشەی نهێنی',
    phone:'ژمارەی تەلەفۆن',username:'ناوی بەکارهێنەر',fullName:'ناوی تەواو',
    officeName:'ناوی ئۆفیس',city:'شار',officeEmail:'ئیمەیڵی ئۆفیس',
    profileImg:'وێنەی پرۆفایل',optional:'(ئارەزووی)',
    remember:'لەبیرم بهێلەرەوە',forgot:'وشەی نهێنیت لەبیرچووە؟',
    or:'یان بەردەوام بە',google:'بەردەوامبوون لەگەڵ Google',
    subLogin:'چوونەژوورەوە',createAccount2:'دروستکردنی ئەکاونت',subAgent:'تۆمارکردن وەک نوێنەر',subOffice:'تۆمارکردنی ئۆفیس',
    noAcc:'ئەکاونتت نییە؟',createOne:'دروستی بکە',haveAcc:'ئەکاونتت هەیە؟',
    noAgentAcc:'ئەکاونتی نوێنەرت نییە؟',regNow:'ئێستا تۆمار بکە',
    noOfficeAcc:'ئەکاونتی ئۆفیست نییە؟',
    lpEyebrow:'خانووبەرەی تایبەت',
    lpHeadline:'خانووبەرەی خەونەکەت<br><em>چاوەڕێی تۆیە</em>',
    lpDesc:'پلاتفۆرمی خانووبەرەی ئەمینترین کوردستان. کڕین، فرۆشتن و کرێ بەبێ کۆمیشن.',
    lsProps:'خانووبەرە',lsAgents:'نوێنەر',lsComm:'کۆمیشن',
  }
};

/* ══ APPLY LANGUAGE ════════════════════════════════════ */
function applyLang(lang) {
  if (!TR[lang]) lang = 'ku';
  const t = TR[lang];
  localStorage.setItem('dm_lang', lang);
  const html = document.getElementById('html-root');
  html.setAttribute('lang', lang);
  html.setAttribute('dir', t.dir);
  document.body.classList.toggle('rtl', t.dir === 'rtl');

  // Highlight correct lang button
  document.querySelectorAll('.lb').forEach(b => {
    const fn = b.getAttribute('onclick') || '';
    b.classList.toggle('active', fn.includes("'" + lang + "'"));
  });

  // Left panel
  document.getElementById('lp-eyebrow').textContent = t.lpEyebrow;
  document.getElementById('lp-headline').innerHTML  = t.lpHeadline;
  document.getElementById('lp-desc').textContent    = t.lpDesc;
  document.getElementById('ls-props').textContent   = t.lsProps;
  document.getElementById('ls-agents').textContent  = t.lsAgents;
  document.getElementById('ls-comm').textContent    = t.lsComm;

  // Who are you
  document.getElementById('back-txt').textContent       = t.back;
  document.getElementById('who-label').textContent      = t.whoLabel;
  document.getElementById('who-hint').textContent       = t.whoHint;
  document.getElementById('wrong-portal-txt').textContent = t.wrongPortal;
  document.getElementById('pc-user-title').textContent  = t.pcUserTitle;
  document.getElementById('pc-user-desc').textContent   = t.pcUserDesc;
  document.getElementById('pc-agent-title').textContent = t.pcAgentTitle;
  document.getElementById('pc-agent-desc').textContent  = t.pcAgentDesc;
  document.getElementById('pc-office-title').textContent= t.pcOfficeTitle;
  document.getElementById('pc-office-desc').textContent = t.pcOfficeDesc;
  // badges
  const selLabel = {en:'✓ Selected', ar:'✓ تم الاختيار', ku:'✓ هەڵبژێردراو'}[lang] || '✓ Selected';
  document.getElementById('pc-user-badge').textContent   = selLabel;
  document.getElementById('pc-agent-badge').textContent  = selLabel;
  document.getElementById('pc-office-badge').textContent = selLabel;

  // USER
  document.getElementById('u-si').textContent  = t.signIn;
  document.getElementById('u-ca').textContent  = t.createAccount;
  document.getElementById('u-tl').textContent  = t.uTl;
  document.getElementById('u-sl').textContent  = t.uSl;
  document.getElementById('u-ts').textContent  = t.uTs;
  document.getElementById('u-ss').textContent  = t.uSs;
  document.getElementById('fl-email').textContent = t.email;
  document.getElementById('fl-pwd').textContent   = t.password;
  document.getElementById('fl-rem').textContent   = t.remember;
  document.getElementById('fl-fgt').textContent   = t.forgot;
  document.getElementById('fl-sub').textContent   = t.subLogin;
  document.getElementById('fl-or').textContent    = t.or;
  document.getElementById('fl-ggl').textContent   = t.google;
  document.getElementById('u-sw-l').innerHTML = `${t.noAcc} <a href="#" onclick="switchAuthTab('user','signup');return false;">${t.createOne}</a>`;
  document.getElementById('fs-uname').textContent = t.username;
  document.getElementById('fs-email').textContent = t.email;
  document.getElementById('fs-pwd').textContent   = t.password;
  document.getElementById('fs-cpwd').textContent  = t.confirmPwd;
  document.getElementById('fs-phone').textContent = t.phone;
  document.getElementById('fs-img').innerHTML     = `${t.profileImg} <span style="font-weight:400;text-transform:none;font-size:11px;color:var(--txt-dim);" id="fs-opt">${t.optional}</span>`;
  document.getElementById('fs-sub').textContent   = t.createAccount2;
  document.getElementById('u-sw-s').innerHTML = `${t.haveAcc} <a href="#" onclick="switchAuthTab('user','login');return false;">${t.signIn}</a>`;

  // AGENT
  document.getElementById('a-si').textContent  = t.signIn;
  document.getElementById('a-rg').textContent  = t.register;
  document.getElementById('a-tl').textContent  = t.aTl;
  document.getElementById('a-sl').textContent  = t.aSl;
  document.getElementById('a-ts').textContent  = t.aTs;
  document.getElementById('a-ss').textContent  = t.aSs;
  document.getElementById('al-email').textContent = t.email;
  document.getElementById('al-pwd').textContent   = t.password;
  document.getElementById('al-rem').textContent   = t.remember;
  document.getElementById('al-fgt').textContent   = t.forgot;
  document.getElementById('al-sub').textContent   = t.subLogin;
  document.getElementById('a-sw-l').innerHTML = `${t.noAgentAcc} <a href="#" onclick="switchAuthTab('agent','signup');return false;">${t.regNow}</a>`;
  document.getElementById('ar-fn').textContent    = t.fullName;
  document.getElementById('ar-email').textContent = t.email;
  document.getElementById('ar-phone').textContent = t.phone;
  document.getElementById('ar-pwd').textContent   = t.password;
  document.getElementById('ar-cpwd').textContent  = t.confirmPwd;
  document.getElementById('ar-sub').textContent   = t.subAgent;
  document.getElementById('a-sw-s').innerHTML = `${t.haveAcc} <a href="#" onclick="switchAuthTab('agent','login');return false;">${t.signIn}</a>`;

  // OFFICE
  document.getElementById('o-si').textContent  = t.signIn;
  document.getElementById('o-rg').textContent  = t.register;
  document.getElementById('o-tl').textContent  = t.oTl;
  document.getElementById('o-sl').textContent  = t.oSl;
  document.getElementById('o-ts').textContent  = t.oTs;
  document.getElementById('o-ss').textContent  = t.oSs;
  document.getElementById('ol-email').textContent = t.officeEmail;
  document.getElementById('ol-pwd').textContent   = t.password;
  document.getElementById('ol-rem').textContent   = t.remember;
  document.getElementById('ol-fgt').textContent   = t.forgot;
  document.getElementById('ol-sub').textContent   = t.subLogin;
  document.getElementById('o-sw-l').innerHTML = `${t.noOfficeAcc} <a href="#" onclick="switchAuthTab('office','signup');return false;">${t.regNow}</a>`;
  document.getElementById('or-name').textContent  = t.officeName;
  document.getElementById('or-email').textContent = t.email;
  document.getElementById('or-phone').textContent = t.phone;
  document.getElementById('or-city').textContent  = t.city;
  document.getElementById('or-pwd').textContent   = t.password;
  document.getElementById('or-cpwd').textContent  = t.confirmPwd;
  document.getElementById('or-sub').textContent   = t.subOffice;
  document.getElementById('o-sw-s').innerHTML = `${t.haveAcc} <a href="#" onclick="switchAuthTab('office','login');return false;">${t.signIn}</a>`;
}

function setLang(lang) { applyLang(lang); }

/* ══ PORTAL SWITCHING ══════════════════════════════════ */
function switchPortal(portal) {
  // Update card visuals
  document.querySelectorAll('.portal-card').forEach(c => c.classList.remove('active'));
  document.getElementById('pcard-' + portal).classList.add('active');

  // Show correct form section
  document.querySelectorAll('.form-section').forEach(s => s.classList.remove('active'));
  document.getElementById('section-' + portal).classList.add('active');

  // Show warning if NOT user (since many users pick wrong one)
  const msg = document.getElementById('wrong-portal-msg');
  if (portal !== 'user') {
    msg.classList.add('show');
    // Auto-hide after 6 seconds
    clearTimeout(window._warnTimer);
    window._warnTimer = setTimeout(() => msg.classList.remove('show'), 6000);
  } else {
    msg.classList.remove('show');
  }

  document.querySelector('.panel-right').scrollTop = 0;
}

function switchAuthTab(portal, tab) {
  const p = {user:'u', agent:'a', office:'o'}[portal];
  ['login','signup'].forEach(t => {
    document.getElementById(p+'tab-'+t).classList.remove('active');
    document.getElementById(p+'panel-'+t).classList.remove('active');
  });
  document.getElementById(p+'tab-'+tab).classList.add('active');
  document.getElementById(p+'panel-'+tab).classList.add('active');
  document.querySelector('.panel-right').scrollTop = 0;
}

/* ══ PASSWORD TOGGLE ════════════════════════════════════ */
function togglePw(id, btn) {
  const inp = document.getElementById(id);
  inp.type = inp.type === 'password' ? 'text' : 'password';
  btn.querySelector('i').className = inp.type === 'text' ? 'fas fa-eye' : 'fas fa-eye-slash';
}

/* ══ GOOGLE ═════════════════════════════════════════════ */
function triggerGoogle() { if (typeof google !== 'undefined') google.accounts.id.prompt(); }

/* ══ INIT ════════════════════════════════════════════════
   Read language from localStorage (same key as index)
════════════════════════════════════════════════════════ */
window.addEventListener('DOMContentLoaded', () => {
  const saved = localStorage.getItem('dm_lang') || 'ku';
  applyLang(saved);

  if (typeof google !== 'undefined') {
    google.accounts.id.initialize({
      client_id: "YOUR_GOOGLE_CLIENT_ID",
      callback: function(r) {
        fetch("{{ route('auth.google') }}", {
          method:"POST",
          headers:{"Content-Type":"application/json","X-CSRF-TOKEN":"{{ csrf_token() }}"},
          body: JSON.stringify({id_token:r.credential,device_name:navigator.userAgent})
        }).then(res=>res.json()).then(d=>{
          if(d.success) window.location.href="/dashboard";
          else alert(d.message||"Google login failed");
        });
      }
    });
    google.accounts.id.renderButton(document.getElementById("google_button"),{theme:"outline",size:"large",width:"100%"});
  }

  @if($errors->has('username') || $errors->has('phone'))
    switchPortal('user');
    switchAuthTab('user','signup');
  @endif
});
</script>
</body>
</html>
