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
  --input-h:48px;
  --E:cubic-bezier(.16,1,.3,1);
  --r:10px;
  --font-ar:'Noto Sans Arabic',sans-serif;
  --font-ar-h:'Noto Naskh Arabic',serif;
}
html,body{height:100%;background:#f0ece3;}
body{font-family:'DM Sans',sans-serif;color:var(--txt);-webkit-font-smoothing:antialiased;overflow-x:hidden;}
body.rtl{font-family:var(--font-ar);}

/* ── PAGE SHELL ─────────────────────────────────────── */
.page{min-height:100vh;display:flex;}

/* ── LEFT — PHOTO PANEL ─────────────────────────────── */
.panel-left{width:44%;flex-shrink:0;position:relative;overflow:hidden;}
.burj-img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center top;filter:brightness(.72) saturate(1.1);}
.left-overlay{position:absolute;inset:0;background:linear-gradient(to bottom, rgba(10,16,50,.55) 0%, rgba(10,16,50,.18) 35%, rgba(10,16,50,.08) 60%, rgba(10,16,50,.85) 100%);z-index:1;}
.left-top{position:absolute;top:0;left:0;right:0;padding:44px 52px 0;z-index:2;}
.left-bottom{position:absolute;bottom:0;left:0;right:0;padding:0 52px 48px;z-index:2;}
.brand-row{display:flex;align-items:center;gap:14px;}
.brand-icon{width:44px;height:44px;border-radius:12px;background:rgba(255,255,255,.12);border:1.5px solid rgba(255,255,255,.25);display:flex;align-items:center;justify-content:center;font-size:18px;color:var(--G);backdrop-filter:blur(8px);}
.brand-name{font-family:'Playfair Display',serif !important;font-size:22px;font-weight:700;color:#fff;letter-spacing:-.3px;text-shadow:0 2px 12px rgba(0,0,0,.4);}
.left-eyebrow{font-size:10px;font-weight:600;letter-spacing:4px;text-transform:uppercase;color:var(--G);margin-bottom:10px;display:flex;align-items:center;gap:10px;}
.left-eyebrow::after{content:'';width:36px;height:1px;background:var(--G);opacity:.6;}
[dir="rtl"] .left-eyebrow{flex-direction:row-reverse;letter-spacing:.5px;font-family:var(--font-ar);}
[dir="rtl"] .left-eyebrow::after{display:none;}
[dir="rtl"] .left-eyebrow::before{content:'';width:36px;height:1px;background:var(--G);opacity:.6;}
.left-headline{font-family:'Playfair Display',serif !important;font-size:clamp(28px,3vw,40px);font-weight:800;line-height:1.1;letter-spacing:-1px;color:#fff;margin-bottom:10px;text-shadow:0 3px 18px rgba(0,0,0,.5);}
.left-headline em{font-style:italic;color:var(--G);}
[dir="rtl"] .left-headline{font-family:var(--font-ar-h) !important;letter-spacing:0;line-height:1.4;}
.left-desc{font-size:14px;line-height:1.8;color:rgba(255,255,255,.72);font-weight:300;max-width:320px;margin-bottom:30px;text-shadow:0 1px 8px rgba(0,0,0,.4);}
[dir="rtl"] .left-desc{font-family:var(--font-ar);}
.stats-row{display:flex;gap:24px;}
[dir="rtl"] .stats-row{flex-direction:row-reverse;}
.stat-item{text-align:center;}
.stat-num{font-family:'Playfair Display',serif !important;font-size:24px;font-weight:700;color:var(--G);line-height:1;}
.stat-lbl{font-size:10px;letter-spacing:1px;text-transform:uppercase;color:rgba(255,255,255,.45);margin-top:4px;}
[dir="rtl"] .stat-lbl{letter-spacing:0;font-family:var(--font-ar);font-size:11px;}

/* ── RIGHT — FORM PANEL ─────────────────────────────── */
.panel-right{flex:1;background:var(--surface);display:flex;flex-direction:column;overflow-y:auto;position:relative;}
.panel-right::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--PD),var(--P),var(--G));z-index:2;}
.form-shell{max-width:440px;width:100%;margin:0 auto;padding:30px 24px;flex:1;display:flex;flex-direction:column;justify-content:center;}
.top-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;}
.back-link{display:flex;align-items:center;gap:6px;font-size:12px;color:var(--txt-muted);text-decoration:none;transition:color .25s;}
.back-link:hover{color:var(--P);}
[dir="rtl"] .back-link i{transform:scaleX(-1);}
.lang-sw{display:flex;background:var(--surface2);border:1px solid var(--border);border-radius:50px;padding:2px;gap:2px;}
.lb{padding:4px 10px;border-radius:50px;border:none;background:transparent;color:var(--txt-muted);font-size:11px;font-weight:600;letter-spacing:.4px;cursor:pointer;transition:all .25s;font-family:'DM Sans',sans-serif;}
.lb.active{background:var(--P);color:#fff;}
.lb:hover:not(.active){background:var(--border);color:var(--txt);}

/* ══ WHO ARE YOU — ULTRA COMPACT ═════════════════════ */
.who-label{font-size:16px;font-weight:800;color:var(--txt);margin-bottom:2px;letter-spacing:-.3px;}
[dir="rtl"] .who-label{font-family:var(--font-ar-h);font-size:17px;}
.who-hint{font-size:12px;color:var(--txt-muted);margin-bottom:14px;line-height:1.5;}
[dir="rtl"] .who-hint{font-family:var(--font-ar);}

.portal-cards{display:flex;flex-direction:column;gap:8px;margin-bottom:22px;}
.portal-card{display:flex;align-items:center;gap:12px;padding:10px 14px;border:1.5px solid var(--border);border-radius:10px;background:var(--surface2);cursor:pointer;transition:all .2s var(--E);position:relative;overflow:hidden;}
.portal-card:hover{border-color:rgba(48,59,151,.35);background:#fff;}
.portal-card.active{border-color:var(--P);border-width:1.5px;background:#fff;box-shadow:0 4px 12px rgba(48,59,151,.08);padding:10px 14px;}
.portal-card.active::before{content:'';position:absolute;top:0;left:0;bottom:0;width:4px;}
.pc-user.active::before{background:linear-gradient(180deg,#16a34a,#22c55e);}
.pc-agent.active::before{background:linear-gradient(180deg,var(--PD),var(--P));}
.pc-office.active::before{background:linear-gradient(180deg,#a07a10,var(--G));}

.portal-card.active::after{content:'\f058';font-family:'Font Awesome 6 Free';font-weight:900;position:absolute;top:50%;right:12px;transform:translateY(-50%);color:var(--P);font-size:16px;animation:popIn .2s var(--E);}
@keyframes popIn{from{transform:translateY(-50%) scale(0);opacity:0;}to{transform:translateY(-50%) scale(1);opacity:1;}}
[dir="rtl"] .portal-card.active::after{right:auto;left:12px;}
[dir="rtl"] .portal-card{flex-direction:row-reverse;}
[dir="rtl"] .portal-card.active::before{left:auto;right:0;}

.pc-icon-wrap{flex-shrink:0;border-radius:8px;display:flex;align-items:center;justify-content:center;transition:all .2s var(--E);width:38px;height:38px;font-size:16px;}
.portal-card.active .pc-icon-wrap{width:38px;height:38px;font-size:18px;}
.pc-user .pc-icon-wrap{background:rgba(34,197,94,.12);color:#16a34a;}
.pc-agent .pc-icon-wrap{background:rgba(48,59,151,.12);color:var(--P);}
.pc-office .pc-icon-wrap{background:rgba(201,162,39,.15);color:#a07a10;}
.portal-card.active.pc-user .pc-icon-wrap{background:rgba(34,197,94,.18);color:#15803d;}
.portal-card.active.pc-agent .pc-icon-wrap{background:rgba(48,59,151,.16);color:var(--PD);}
.portal-card.active.pc-office .pc-icon-wrap{background:rgba(201,162,39,.22);color:#92700c;}

.pc-text{flex:1;min-width:0;padding-right:24px;}
[dir="rtl"] .pc-text{padding-right:0;padding-left:24px;}
.pc-title{font-size:13.5px;font-weight:700;color:var(--txt);margin-bottom:1px;transition:all .2s;}
.portal-card.active .pc-title{font-size:14px;font-weight:800;color:var(--P);}
.pc-user.active .pc-title{color:#15803d;}
.pc-agent.active .pc-title{color:var(--PD);}
.pc-office.active .pc-title{color:#92700c;}
[dir="rtl"] .pc-title{font-family:var(--font-ar-h);}
.pc-desc{font-size:11px;color:var(--txt-muted);line-height:1.4;transition:all .2s;}
.portal-card.active .pc-desc{font-size:11.5px;color:var(--txt-muted);}
[dir="rtl"] .pc-desc{font-family:var(--font-ar);}

.auth-tabs{display:flex;border-bottom:1.5px solid var(--border);margin-bottom:18px;}
.auth-tab{flex:1;text-align:center;padding:6px 0;font-size:12.5px;font-weight:600;color:var(--txt-dim);cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-1.5px;transition:all .2s;}
[dir="rtl"] .auth-tab{font-family:var(--font-ar);}
.auth-tab.active{color:var(--P);border-bottom-color:var(--P);}
.auth-tab:hover:not(.active){color:var(--txt);}
.form-section,.panel{display:none;}
.form-section.active,.panel.active{display:block;animation:fadeUp .3s var(--E);}
@keyframes fadeUp{from{opacity:0;transform:translateY(6px);}to{opacity:1;transform:translateY(0);}}

.form-title{font-family:'Playfair Display',serif !important;font-size:18px;font-weight:700;color:var(--txt);margin-bottom:2px;}
[dir="rtl"] .form-title{font-family:var(--font-ar-h) !important;}
.form-sub{font-size:12px;color:var(--txt-muted);margin-bottom:14px;}
[dir="rtl"] .form-sub{font-family:var(--font-ar);}

.ibox{margin-bottom:12px;}
.ibox label{display:block;font-size:11px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;color:var(--txt-muted);margin-bottom:4px;}
[dir="rtl"] .ibox label{letter-spacing:0;font-family:var(--font-ar);text-transform:none;}
.ifield{position:relative;}
.ifield i.ico{position:absolute;top:50%;transform:translateY(-50%);left:12px;font-size:13px;color:var(--txt-dim);pointer-events:none;transition:color .2s;}
[dir="rtl"] .ifield i.ico{left:auto;right:12px;}
.ifield input{width:100%;height:var(--input-h);background:var(--input-bg);border:1px solid var(--border);border-radius:var(--r);padding:0 38px;font-size:13.5px;color:var(--txt);font-family:'DM Sans',sans-serif;outline:none;transition:all .2s;}
[dir="rtl"] .ifield input{font-family:var(--font-ar);}
.ifield input::placeholder{color:var(--txt-dim);font-size:12.5px;}
.ifield input:focus{border-color:var(--P);background:#fff;box-shadow:0 0 0 3px rgba(48,59,151,.08);}
.ifield:focus-within i.ico{color:var(--P);}
.eye-btn{position:absolute;top:50%;transform:translateY(-50%);right:10px;border:none;background:none;color:var(--txt-dim);font-size:14px;cursor:pointer;padding:6px;transition:color .2s;}
[dir="rtl"] .eye-btn{right:auto;left:10px;}
.eye-btn:hover{color:var(--P);}

.err{font-size:11px;color:#c0392b;margin-top:3px;display:block;}
.alert-box{padding:8px 10px;border-radius:6px;font-size:12px;margin-bottom:12px;display:flex;align-items:center;gap:6px;line-height:1.4;}
.alert-box.danger{background:#fef2f2;color:#991b1b;border:1px solid #fecaca;}
.alert-box.success{background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;}

.opt-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;}
[dir="rtl"] .opt-row{flex-direction:row-reverse;}
.chk-wrap{display:flex;align-items:center;gap:6px;cursor:pointer;}
.chk-wrap input[type=checkbox]{width:14px;height:14px;border-radius:4px;accent-color:var(--P);cursor:pointer;}
.chk-wrap span{font-size:12px;color:var(--txt-muted);}
[dir="rtl"] .chk-wrap span{font-family:var(--font-ar);}
.forgot-link{font-size:12px;color:var(--P);font-weight:500;text-decoration:none;}
.forgot-link:hover{text-decoration:underline;}

.submit-btn{width:100%;height:46px;background:linear-gradient(135deg,var(--P) 0%,var(--PM) 100%);color:#fff;font-family:'DM Sans',sans-serif;font-size:13.5px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;border:none;border-radius:var(--r);cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:all .3s;box-shadow:0 4px 12px rgba(48,59,151,.25);position:relative;overflow:hidden;}
[dir="rtl"] .submit-btn{font-family:var(--font-ar);letter-spacing:.2px;}
.submit-btn::after{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(255,255,255,.15),transparent);opacity:0;transition:opacity .3s;}
.submit-btn:hover{transform:translateY(-1px);box-shadow:0 6px 16px rgba(48,59,151,.35);}
.submit-btn:hover::after{opacity:1;}

.divider{display:flex;align-items:center;gap:10px;margin:14px 0;}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--border);}
.divider span{font-size:10px;color:var(--txt-dim);letter-spacing:1px;text-transform:uppercase;}
.g-btn{width:100%;height:42px;border-radius:var(--r);border:1px solid var(--border);background:var(--surface2);color:var(--txt);font-family:'DM Sans',sans-serif;font-size:12.5px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:all .2s;}
.g-btn:hover{border-color:var(--P);background:#fff;}
.g-btn svg{width:16px;height:16px;}

.switch-text{text-align:center;font-size:12px;color:var(--txt-muted);margin-top:14px;}
.switch-text a{color:var(--P);font-weight:600;text-decoration:none;}
.switch-text a:hover{text-decoration:underline;}

@media(max-width:960px){
  .panel-left{display:none;}
  .panel-right{width:100%;}
  .form-shell{padding:20px 20px;}
  html,body{overflow:auto;}
}
</style>
</head>
<body>

<div class="page">

  <div class="panel-left">
    <img src="https://images.unsplash.com/photo-1512453979798-5ea266f8880c?w=900&q=85&fit=crop&auto=format" alt="Dubai skyline" class="burj-img"/>
    <div class="left-overlay"></div>

    <div class="left-top">
      <div class="brand-row">
        <div class="brand-icon"><i class="fas fa-building"></i></div>
        <span class="brand-name">Dream Mulk</span>
      </div>
    </div>

    <div class="left-bottom">
      <div class="left-eyebrow" data-i18n="lpEyebrow">Premium Real Estate</div>
      <h2 class="left-headline" data-i18n="lpHeadline">Your Dream<br>Property <em>Awaits</em></h2>
      <p class="left-desc" data-i18n="lpDesc">Kurdistan's most trusted platform to buy, sell and rent — zero commission, full transparency.</p>
      <div class="stats-row">
        <div class="stat-item"><div class="stat-num">500+</div><div class="stat-lbl" data-i18n="lsProps">Properties</div></div>
        <div class="stat-item"><div class="stat-num">150+</div><div class="stat-lbl" data-i18n="lsAgents">Agents</div></div>
        <div class="stat-item"><div class="stat-num">0%</div><div class="stat-lbl" data-i18n="lsComm">Commission</div></div>
      </div>
    </div>
  </div>

  <div class="panel-right">
    <div class="form-shell">

      <div class="top-row">
        <a href="{{ route('newindex') }}" class="back-link">
          <i class="fas fa-arrow-left"></i>&nbsp;<span data-i18n="back">Back to Home</span>
        </a>
        <div class="lang-sw">
          <button class="lb" onclick="setLang('en',this)">EN</button>
          <button class="lb" onclick="setLang('ar',this)">ع</button>
          <button class="lb" onclick="setLang('ku',this)">کو</button>
        </div>
      </div>

      <div class="who-label" data-i18n="whoLabel">Who are you?</div>
      <div class="who-hint" data-i18n="whoHint">Choose the option that matches you — this is important!</div>

      <div class="portal-cards">
        <div class="portal-card pc-user active" id="pcard-user" onclick="switchPortal('user')">
          <div class="pc-icon-wrap"><i class="fas fa-user"></i></div>
          <div class="pc-text">
            <div class="pc-title" data-i18n="pcUserTitle">I am a regular person</div>
            <div class="pc-desc" data-i18n="pcUserDesc">I want to buy, sell or rent a property</div>
          </div>
        </div>

        <div class="portal-card pc-agent" id="pcard-agent" onclick="switchPortal('agent')">
          <div class="pc-icon-wrap"><i class="fas fa-id-badge"></i></div>
          <div class="pc-text">
            <div class="pc-title" data-i18n="pcAgentTitle">I am a property agent</div>
            <div class="pc-desc" data-i18n="pcAgentDesc">I list properties on behalf of clients</div>
          </div>
        </div>

        <div class="portal-card pc-office" id="pcard-office" onclick="switchPortal('office')">
          <div class="pc-icon-wrap"><i class="fas fa-building-user"></i></div>
          <div class="pc-text">
            <div class="pc-title" data-i18n="pcOfficeTitle">I own a real estate office</div>
            <div class="pc-desc" data-i18n="pcOfficeDesc">I manage a registered agency with agents</div>
          </div>
        </div>
      </div>

      <div class="form-section active" id="section-user">
        <div class="auth-tabs">
          <div class="auth-tab active" id="utab-login" onclick="switchAuthTab('user','login')" data-i18n="signIn">Sign In</div>
          <div class="auth-tab" id="utab-signup" onclick="switchAuthTab('user','signup')" data-i18n="createAccount">Create Account</div>
        </div>
        <div class="panel active" id="upanel-login">
          <div class="form-title" data-i18n="uTl">Welcome back</div>
          <div class="form-sub" data-i18n="uSl">Enter your email and password to sign in.</div>

          @if(session('error') && old('portal_type') == 'user')
            <div class="alert-box danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
          @endif

          <form action="{{ route('login.submit') }}" method="POST">
            @csrf
            <input type="hidden" name="portal_type" value="user">
            <div class="ibox">
              <label data-i18n="email">Email Address</label>
              <div class="ifield"><i class="fas fa-envelope ico"></i>
                <input type="email" name="email" data-i18n-placeholder="emailPlaceholder" placeholder="you@example.com" value="{{ old('portal_type') == 'user' ? old('email') : '' }}" required/>
              </div>
              @if(old('portal_type') == 'user') @error('email')<span class="err">{{ $message }}</span>@enderror @endif
            </div>
            <div class="ibox">
              <label data-i18n="password">Password</label>
              <div class="ifield"><i class="fas fa-lock ico"></i>
                <input type="password" id="ulp" name="password" data-i18n-placeholder="pwdPlaceholder" placeholder="••••••••" required/>
                <button type="button" class="eye-btn" onclick="togglePw('ulp',this)"><i class="fas fa-eye-slash"></i></button>
              </div>
            </div>
            <div class="opt-row">
              <label class="chk-wrap"><input type="checkbox" name="remember"/><span data-i18n="remember">Remember me</span></label>
              <a href="#" class="forgot-link" data-i18n="forgot">Forgot password?</a>
            </div>
            <button type="submit" class="submit-btn"><i class="fas fa-arrow-right-to-bracket"></i><span data-i18n="subLogin">Sign In</span></button>
          </form>

          <div class="divider"><span data-i18n="or">or continue with</span></div>
          <button class="g-btn" onclick="triggerGoogle()">
            <svg viewBox="0 0 48 48"><path fill="#FFC107" d="M43.6 20.1H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34.5 6.5 29.5 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.6-.4-3.9z"/><path fill="#FF3D00" d="m6.3 14.7 6.6 4.8C14.7 16 19 13 24 13c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34.5 6.5 29.5 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/><path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.3 35.1 26.8 36 24 36c-5.2 0-9.6-3.3-11.3-8H6.4C9.7 35.6 16.3 44 24 44z"/><path fill="#1976D2" d="M43.6 20.1H42V20H24v8h11.3c-.8 2.3-2.3 4.2-4.2 5.6l6.2 5.2C37 39 44 34 44 24c0-1.3-.1-2.6-.4-3.9z"/></svg>
            <span data-i18n="google">Continue with Google</span>
          </button>
          <div class="switch-text"><span data-i18n="noAcc">Don't have an account?</span> <a href="#" onclick="switchAuthTab('user','signup');return false;" data-i18n="createOne">Create one</a></div>
          <div id="google_button" style="display:none;"></div>
        </div>
        <div class="panel" id="upanel-signup">
          <div class="form-title" data-i18n="uTs">Create your account</div>
          <div class="form-sub" data-i18n="uSs">Fill in your details to get started on Dream Mulk.</div>
          @if(session('success'))<div class="alert-box success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>@endif

          <form action="{{ route('user.store') }}" method="POST">
            @csrf
            <input type="hidden" name="role" value="user"/>
            <input type="hidden" name="portal_type" value="user">
            <div class="ibox"><label data-i18n="username">Username</label><div class="ifield"><i class="fas fa-user ico"></i><input type="text" name="username" data-i18n-placeholder="unamePlaceholder" placeholder="johndoe" value="{{ old('portal_type') == 'user' ? old('username') : '' }}" required/></div>@if(old('portal_type') == 'user') @error('username')<span class="err">{{ $message }}</span>@enderror @endif</div>
            <div class="ibox"><label data-i18n="email">Email Address</label><div class="ifield"><i class="fas fa-envelope ico"></i><input type="email" name="email" data-i18n-placeholder="emailPlaceholder" placeholder="you@example.com" value="{{ old('portal_type') == 'user' ? old('email') : '' }}" required/></div>@if(old('portal_type') == 'user') @error('email')<span class="err">{{ $message }}</span>@enderror @endif</div>
            <div class="ibox"><label data-i18n="password">Password</label><div class="ifield"><i class="fas fa-lock ico"></i><input type="password" id="usp" name="password" data-i18n-placeholder="pwdPlaceholder" placeholder="Create a strong password" required/><button type="button" class="eye-btn" onclick="togglePw('usp',this)"><i class="fas fa-eye-slash"></i></button></div>@if(old('portal_type') == 'user') @error('password')<span class="err">{{ $message }}</span>@enderror @endif</div>
            <div class="ibox"><label data-i18n="confirmPwd">Confirm Password</label><div class="ifield"><i class="fas fa-lock ico"></i><input type="password" id="uscp" name="password_confirmation" data-i18n-placeholder="cpwdPlaceholder" placeholder="Repeat your password" required/><button type="button" class="eye-btn" onclick="togglePw('uscp',this)"><i class="fas fa-eye-slash"></i></button></div></div>
            <div class="ibox"><label data-i18n="phone">Phone Number</label><div class="ifield"><i class="fas fa-phone ico"></i><input type="tel" name="phone" data-i18n-placeholder="phonePlaceholder" placeholder="07XX XXX XXXX" value="{{ old('portal_type') == 'user' ? old('phone') : '' }}" required/></div>@if(old('portal_type') == 'user') @error('phone')<span class="err">{{ $message }}</span>@enderror @endif</div>
            <button type="submit" class="submit-btn" style="margin-top:4px;"><i class="fas fa-user-plus"></i><span data-i18n="createAccount2">Create Account</span></button>
          </form>
          <div class="switch-text"><span data-i18n="haveAcc">Already have an account?</span> <a href="#" onclick="switchAuthTab('user','login');return false;" data-i18n="signIn">Sign In</a></div>
        </div>
      </div>

      <div class="form-section" id="section-agent">
        <div class="auth-tabs">
          <div class="auth-tab active" id="atab-login" onclick="switchAuthTab('agent','login')" data-i18n="signIn">Sign In</div>
          <div class="auth-tab" id="atab-signup" onclick="switchAuthTab('agent','signup')" data-i18n="register">Register</div>
        </div>
        <div class="panel active" id="apanel-login">
          <div class="form-title" data-i18n="aTl">Agent Portal</div>
          <div class="form-sub" data-i18n="aSl">Sign in to manage your listings and appointments.</div>

          @if(session('error') && old('portal_type') == 'agent')
            <div class="alert-box danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
          @endif

          <form action="{{ route('agent.login.submit') }}" method="POST">
            @csrf
            <input type="hidden" name="portal_type" value="agent">
            <div class="ibox"><label data-i18n="email">Email Address</label><div class="ifield"><i class="fas fa-envelope ico"></i><input type="email" name="email" data-i18n-placeholder="emailPlaceholder" placeholder="agent@example.com" value="{{ old('portal_type') == 'agent' ? old('email') : '' }}" required/></div>
            @if(old('portal_type') == 'agent') @error('email')<span class="err">{{ $message }}</span>@enderror @endif
            </div>
            <div class="ibox"><label data-i18n="password">Password</label><div class="ifield"><i class="fas fa-lock ico"></i><input type="password" id="alp" name="password" data-i18n-placeholder="pwdPlaceholder" placeholder="••••••••" required/><button type="button" class="eye-btn" onclick="togglePw('alp',this)"><i class="fas fa-eye-slash"></i></button></div></div>
            <div class="opt-row">
              <label class="chk-wrap"><input type="checkbox" name="remember"/><span data-i18n="remember">Remember me</span></label>
              <a href="#" class="forgot-link" data-i18n="forgot">Forgot password?</a>
            </div>
            <button type="submit" class="submit-btn"><i class="fas fa-arrow-right-to-bracket"></i><span data-i18n="subLogin">Sign In</span></button>
          </form>
          <div class="switch-text"><span data-i18n="noAgentAcc">Don't have an agent account?</span> <a href="#" onclick="switchAuthTab('agent','signup');return false;" data-i18n="regNow">Register now</a></div>
        </div>
        <div class="panel" id="apanel-signup">
          <div class="form-title" data-i18n="aTs">Agent Registration</div>
          <div class="form-sub" data-i18n="aSs">Create your agent account to list properties.</div>
          <form action="{{ route('agent.register.submit') }}" method="POST">
            @csrf
            <input type="hidden" name="portal_type" value="agent">
            <div class="ibox"><label data-i18n="fullName">Full Name</label><div class="ifield"><i class="fas fa-user ico"></i><input type="text" name="agent_name" data-i18n-placeholder="fnPlaceholder" placeholder="John Doe" value="{{ old('portal_type') == 'agent' ? old('agent_name') : '' }}" required/></div>@if(old('portal_type') == 'agent') @error('agent_name')<span class="err">{{ $message }}</span>@enderror @endif</div>
            <div class="ibox"><label data-i18n="email">Email Address</label><div class="ifield"><i class="fas fa-envelope ico"></i><input type="email" name="primary_email" data-i18n-placeholder="emailPlaceholder" placeholder="agent@example.com" value="{{ old('portal_type') == 'agent' ? old('primary_email') : '' }}" required/></div>@if(old('portal_type') == 'agent') @error('primary_email')<span class="err">{{ $message }}</span>@enderror @endif</div>
            <div class="ibox"><label data-i18n="phone">Phone Number</label><div class="ifield"><i class="fas fa-phone ico"></i><input type="tel" name="primary_phone" data-i18n-placeholder="phonePlaceholder" placeholder="07XX XXX XXXX" value="{{ old('portal_type') == 'agent' ? old('primary_phone') : '' }}" required/></div>@if(old('portal_type') == 'agent') @error('primary_phone')<span class="err">{{ $message }}</span>@enderror @endif</div>
            <div class="ibox"><label data-i18n="city">City</label><div class="ifield"><i class="fas fa-map-marker-alt ico"></i><input type="text" name="city" data-i18n-placeholder="cityPlaceholder" placeholder="Erbil" value="{{ old('portal_type') == 'agent' ? old('city') : '' }}" required/></div></div>
            <div class="ibox"><label data-i18n="password">Password</label><div class="ifield"><i class="fas fa-lock ico"></i><input type="password" id="asp" name="password" data-i18n-placeholder="pwdPlaceholder" placeholder="Create a strong password" required/><button type="button" class="eye-btn" onclick="togglePw('asp',this)"><i class="fas fa-eye-slash"></i></button></div>@if(old('portal_type') == 'agent') @error('password')<span class="err">{{ $message }}</span>@enderror @endif</div>
            <div class="ibox"><label data-i18n="confirmPwd">Confirm Password</label><div class="ifield"><i class="fas fa-lock ico"></i><input type="password" id="ascp" name="password_confirmation" data-i18n-placeholder="cpwdPlaceholder" placeholder="Repeat your password" required/><button type="button" class="eye-btn" onclick="togglePw('ascp',this)"><i class="fas fa-eye-slash"></i></button></div></div>
            <button type="submit" class="submit-btn" style="margin-top:4px;"><i class="fas fa-id-badge"></i><span data-i18n="subAgent">Register as Agent</span></button>
          </form>
          <div class="switch-text"><span data-i18n="haveAcc">Already have an account?</span> <a href="#" onclick="switchAuthTab('agent','login');return false;" data-i18n="signIn">Sign In</a></div>
        </div>
      </div>

      <div class="form-section" id="section-office">
        <div class="auth-tabs">
          <div class="auth-tab active" id="otab-login" onclick="switchAuthTab('office','login')" data-i18n="signIn">Sign In</div>
          <div class="auth-tab" id="otab-signup" onclick="switchAuthTab('office','signup')" data-i18n="register">Register</div>
        </div>
        <div class="panel active" id="opanel-login">
          <div class="form-title" data-i18n="oTl">Office Portal</div>
          <div class="form-sub" data-i18n="oSl">Sign in to manage your office, agents, and properties.</div>

          @if(session('error') && old('portal_type') == 'office')
            <div class="alert-box danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
          @endif

          <form action="{{ route('office.login.submit') }}" method="POST">
            @csrf
            <input type="hidden" name="portal_type" value="office">
            <div class="ibox"><label data-i18n="officeEmail">Office Email</label><div class="ifield"><i class="fas fa-envelope ico"></i><input type="email" name="email" data-i18n-placeholder="emailPlaceholder" placeholder="office@dreammulk.com" value="{{ old('portal_type') == 'office' ? old('email') : '' }}" required/></div>
            @if(old('portal_type') == 'office') @error('email')<span class="err">{{ $message }}</span>@enderror @endif
            </div>
            <div class="ibox"><label data-i18n="password">Password</label><div class="ifield"><i class="fas fa-lock ico"></i><input type="password" id="olp" name="password" data-i18n-placeholder="pwdPlaceholder" placeholder="••••••••" required/><button type="button" class="eye-btn" onclick="togglePw('olp',this)"><i class="fas fa-eye-slash"></i></button></div></div>
            <div class="opt-row">
              <label class="chk-wrap"><input type="checkbox" name="remember"/><span data-i18n="remember">Remember me</span></label>
              <a href="#" class="forgot-link" data-i18n="forgot">Forgot password?</a>
            </div>
            <button type="submit" class="submit-btn"><i class="fas fa-arrow-right-to-bracket"></i><span data-i18n="subLogin">Sign In</span></button>
          </form>
          <div class="switch-text"><span data-i18n="noOfficeAcc">Don't have an office account?</span> <a href="#" onclick="switchAuthTab('office','signup');return false;" data-i18n="regNow">Register now</a></div>
        </div>
        <div class="panel" id="opanel-signup">
          <div class="form-title" data-i18n="oTs">Office Registration</div>
          <div class="form-sub" data-i18n="oSs">Register your real estate office on Dream Mulk.</div>
          <form action="{{ route('office.register.submit') }}" method="POST">
            @csrf
            <input type="hidden" name="portal_type" value="office">
            <div class="ibox"><label data-i18n="officeName">Office Name</label><div class="ifield"><i class="fas fa-building ico"></i><input type="text" name="company_name" data-i18n-placeholder="onPlaceholder" placeholder="Al-Salam Real Estate" value="{{ old('portal_type') == 'office' ? old('company_name') : '' }}" required/></div>@if(old('portal_type') == 'office') @error('company_name')<span class="err">{{ $message }}</span>@enderror @endif</div>
            <div class="ibox"><label data-i18n="email">Email Address</label><div class="ifield"><i class="fas fa-envelope ico"></i><input type="email" name="email" data-i18n-placeholder="emailPlaceholder" placeholder="office@example.com" value="{{ old('portal_type') == 'office' ? old('email') : '' }}" required/></div>@if(old('portal_type') == 'office') @error('email')<span class="err">{{ $message }}</span>@enderror @endif</div>
            <div class="ibox"><label data-i18n="phone">Phone Number</label><div class="ifield"><i class="fas fa-phone ico"></i><input type="tel" name="phone_number" data-i18n-placeholder="phonePlaceholder" placeholder="07XX XXX XXXX" value="{{ old('portal_type') == 'office' ? old('phone_number') : '' }}" required/></div>@if(old('portal_type') == 'office') @error('phone_number')<span class="err">{{ $message }}</span>@enderror @endif</div>
            <div class="ibox"><label data-i18n="city">City</label><div class="ifield"><i class="fas fa-map-marker-alt ico"></i><input type="text" name="city" data-i18n-placeholder="cityPlaceholder" placeholder="Erbil" value="{{ old('portal_type') == 'office' ? old('city') : '' }}" required/></div></div>
            <div class="ibox"><label data-i18n="password">Password</label><div class="ifield"><i class="fas fa-lock ico"></i><input type="password" id="osp" name="password" data-i18n-placeholder="pwdPlaceholder" placeholder="Create a strong password" required/><button type="button" class="eye-btn" onclick="togglePw('osp',this)"><i class="fas fa-eye-slash"></i></button></div>@if(old('portal_type') == 'office') @error('password')<span class="err">{{ $message }}</span>@enderror @endif</div>
            <div class="ibox"><label data-i18n="confirmPwd">Confirm Password</label><div class="ifield"><i class="fas fa-lock ico"></i><input type="password" id="oscp" name="password_confirmation" data-i18n-placeholder="cpwdPlaceholder" placeholder="Repeat your password" required/><button type="button" class="eye-btn" onclick="togglePw('oscp',this)"><i class="fas fa-eye-slash"></i></button></div></div>
            <button type="submit" class="submit-btn" style="margin-top:4px;"><i class="fas fa-building-user"></i><span data-i18n="subOffice">Register Office</span></button>
          </form>
          <div class="switch-text"><span data-i18n="haveAcc">Already have an account?</span> <a href="#" onclick="switchAuthTab('office','login');return false;" data-i18n="signIn">Sign In</a></div>
        </div>
      </div>

    </div></div></div><script src="https://accounts.google.com/gsi/client" async defer></script>
<script>
/* ══ TRANSLATIONS ══════════════════════════════════════ */
const TR = {
  en:{dir:'ltr',
    back:'Back to Home',whoLabel:'Who are you?',whoHint:'Choose the option that matches you — this is important!',
    wrongPortal:'⚠️ Please select the right option above before signing in.',
    pcUserTitle:'I am a regular person',pcUserDesc:'I want to buy, sell or rent a property',
    pcAgentTitle:'I am a property agent',pcAgentDesc:'I list properties on behalf of clients',
    pcOfficeTitle:'I own a real estate office',pcOfficeDesc:'I manage a registered agency with agents',
    signIn:'Sign In',register:'Register',createAccount:'Create Account',
    uTl:'Welcome back',uSl:'Enter your email and password to sign in.',
    uTs:'Create your account',uSs:'Fill in your details to get started on Dream Mulk.',
    aTl:'Agent Portal',aSl:'Sign in to manage your listings and appointments.',
    aTs:'Agent Registration',aSs:'Create your agent account to list properties.',
    oTl:'Office Portal',oSl:'Sign in to manage your office, agents, and properties.',
    oTs:'Office Registration',oSs:'Register your real estate office on Dream Mulk.',
    email:'Email Address',emailPlaceholder:'you@example.com',
    password:'Password',pwdPlaceholder:'••••••••',
    confirmPwd:'Confirm Password',cpwdPlaceholder:'Repeat your password',
    phone:'Phone Number',phonePlaceholder:'07XX XXX XXXX',
    username:'Username',unamePlaceholder:'johndoe',
    fullName:'Full Name',fnPlaceholder:'John Doe',
    officeName:'Office Name',onPlaceholder:'Al-Salam Real Estate',
    city:'City',cityPlaceholder:'Erbil',
    officeEmail:'Office Email',
    remember:'Remember me',forgot:'Forgot password?',
    or:'or continue with',google:'Continue with Google',
    subLogin:'Sign In',createAccount2:'Create Account',subAgent:'Register as Agent',subOffice:'Register Office',
    noAcc:"Don't have an account?",createOne:'Create one',haveAcc:'Already have an account?',
    noAgentAcc:"Don't have an agent account?",regNow:'Register now',
    noOfficeAcc:"Don't have an office account?",
    lpEyebrow:'Premium Real Estate',lpHeadline:'Your Dream<br>Property <em>Awaits</em>',
    lpDesc:"Kurdistan's most trusted platform to buy, sell and rent — zero commission, full transparency.",
    lsProps:'Properties',lsAgents:'Agents',lsComm:'Commission',
  },
  ar:{dir:'rtl',
    back:'العودة للرئيسية',whoLabel:'من أنت؟',whoHint:'اختر الخيار المناسب لك — هذا مهم جداً!',
    wrongPortal:'⚠️ تأكد من اختيار الخيار الصحيح أعلاه قبل تسجيل الدخول.',
    pcUserTitle:'أنا شخص عادي',pcUserDesc:'أريد شراء أو بيع أو استئجار عقار',
    pcAgentTitle:'أنا وكيل عقاري',pcAgentDesc:'أدرج العقارات نيابة عن العملاء',
    pcOfficeTitle:'أمتلك مكتباً عقارياً',pcOfficeDesc:'لدي شركة عقارية مسجلة',
    signIn:'تسجيل الدخول',register:'إنشاء حساب',createAccount:'إنشاء حساب',
    uTl:'أهلاً بعودتك',uSl:'أدخل بريدك الإلكتروني وكلمة المرور.',
    uTs:'إنشاء حساب جديد',uSs:'أدخل بياناتك للانضمام إلى Dream Mulk.',
    aTl:'بوابة الوكلاء',aSl:'سجّل دخولك لإدارة قوائمك ومواعيدك.',
    aTs:'تسجيل وكيل عقاري',aSs:'أنشئ حساب الوكيل الخاص بك.',
    oTl:'بوابة المكاتب',oSl:'سجّل دخولك لإدارة مكتبك ووكلائك.',
    oTs:'تسجيل مكتب عقاري',oSs:'سجّل مكتبك العقاري في Dream Mulk.',
    email:'البريد الإلكتروني',emailPlaceholder:'أدخل بريدك الإلكتروني',
    password:'كلمة المرور',pwdPlaceholder:'••••••••',
    confirmPwd:'تأكيد كلمة المرور',cpwdPlaceholder:'أعد إدخال كلمة المرور',
    phone:'رقم الهاتف',phonePlaceholder:'07XX XXX XXXX',
    username:'اسم المستخدم',unamePlaceholder:'اسم المستخدم',
    fullName:'الاسم الكامل',fnPlaceholder:'الاسم الكامل',
    officeName:'اسم المكتب',onPlaceholder:'اسم مكتب العقارات',
    city:'المدينة',cityPlaceholder:'المدينة',
    officeEmail:'بريد المكتب',
    remember:'تذكرني',forgot:'نسيت كلمة المرور؟',
    or:'أو تابع بـ',google:'متابعة مع Google',
    subLogin:'دخول',createAccount2:'إنشاء الحساب',subAgent:'التسجيل كوكيل',subOffice:'تسجيل المكتب',
    noAcc:'ليس لديك حساب؟',createOne:'أنشئ واحداً',haveAcc:'لديك حساب بالفعل؟',
    noAgentAcc:'ليس لديك حساب وكيل؟',regNow:'سجّل الآن',
    noOfficeAcc:'ليس لديك حساب مكتب؟',
    lpEyebrow:'عقارات كردستان',lpHeadline:'عقارك المثالي<br><em>بانتظارك</em>',
    lpDesc:'منصة العقارات الأكثر موثوقية في كردستان. شراء وبيع وإيجار بدون عمولة.',
    lsProps:'عقار',lsAgents:'وكيل',lsComm:'عمولة',
  },
  ku:{dir:'rtl',
    back:'گەڕانەوە بۆ ماڵپەڕ',whoLabel:'تۆ کێیت؟',whoHint:'ئەو بژاردەیە هەڵبژێرە کە بۆ تۆ گونجاوە — ئەمە زۆر گرنگە!',
    wrongPortal:'⚠️ تکایە پێش چوونەژوورەوە دڵنیابە لە هەڵبژاردنی بژاردەی گونجاو.',
    pcUserTitle:'کەسێکی ئاساییم',pcUserDesc:'دەمەوێ خانوویەک بکڕم، بفرۆشم یان کرێی بدەم',
    pcAgentTitle:'نوێنەری خانووبەرەم',pcAgentDesc:'خانووبەرە لە ناوی کڕیارەکان تۆمار دەکەم',
    pcOfficeTitle:'خاوەنی ئۆفیسی خانووبەرەم',pcOfficeDesc:'کۆمپانیایەکی خانووبەرەی تۆمارکراوم هەیە',
    signIn:'چوونەژوورەوە',register:'تۆمارکردن',createAccount:'دروستکردنی ئەکاونت',
    uTl:'بەخێربێیتەوە',uSl:'ئیمەیڵ و وشەی نهێنیەکەت داخڵ بکە.',
    uTs:'دروستکردنی ئەکاونت',uSs:'زانیاریەکانت پڕبکەرەوە بۆ بەکارهێنانی Dream Mulk.',
    aTl:'دەرگای نوێنەران',aSl:'بچۆرە ژوورەوە بۆ بەڕێوەبردنی خانووبەرەکانت.',
    aTs:'تۆمارکردنی نوێنەر',aSs:'ئەکاونتی نوێنەرەکەت دروست بکە.',
    oTl:'دەرگای ئۆفیسەکان',oSl:'بچۆرە ژوورەوە بۆ بەڕێوەبردنی ئۆفیسەکەت.',
    oTs:'تۆمارکردنی ئۆفیس',oSs:'ئۆفیسی خانووبەرەکەت تۆمار بکە.',
    email:'ئیمەیڵ',emailPlaceholder:'ئیمەیڵەکەت بنووسە',
    password:'وشەی نهێنی',pwdPlaceholder:'••••••••',
    confirmPwd:'دڵنیاکردنەوەی وشەی نهێنی',cpwdPlaceholder:'وشەی نهێنی دووبارە بکەوە',
    phone:'ژمارەی تەلەفۆن',phonePlaceholder:'07XX XXX XXXX',
    username:'ناوی بەکارهێنەر',unamePlaceholder:'ناوی بەکارهێنەر',
    fullName:'ناوی تەواو',fnPlaceholder:'ناوی تەواو',
    officeName:'ناوی ئۆفیس',onPlaceholder:'ناوی ئۆفیسەکەت بنووسە',
    city:'شار',cityPlaceholder:'شارەکەت',
    officeEmail:'ئیمەیڵی ئۆفیس',
    remember:'لەبیرم بهێلەرەوە',forgot:'وشەی نهێنیت لەبیرچووە؟',
    or:'یان بەردەوام بە',google:'بەردەوامبوون لەگەڵ Google',
    subLogin:'چوونەژوورەوە',createAccount2:'دروستکردنی ئەکاونت',subAgent:'تۆمارکردن وەک نوێنەر',subOffice:'تۆمارکردنی ئۆفیس',
    noAcc:'ئەکاونتت نییە؟',createOne:'دروستی بکە',haveAcc:'ئەکاونتت هەیە؟',
    noAgentAcc:'ئەکاونتی نوێنەرت نییە؟',regNow:'ئێستا تۆمار بکە',
    noOfficeAcc:'ئەکاونتی ئۆفیست نییە؟',
    lpEyebrow:'خانووبەرەی تایبەت',lpHeadline:'خانووبەرەی خەونەکەت<br><em>چاوەڕێی تۆیە</em>',
    lpDesc:'پلاتفۆرمی خانووبەرەی ئەمینترین کوردستان. کڕین، فرۆشتن و کرێ بەبێ کۆمیشن.',
    lsProps:'خانووبەرە',lsAgents:'نوێنەر',lsComm:'کۆمیشن',
  }
};

function applyLang(lang) {
  if (!TR[lang]) lang = 'ku';
  const t = TR[lang];
  localStorage.setItem('dm_lang', lang);
  const html = document.getElementById('html-root');
  html.setAttribute('lang', lang);
  html.setAttribute('dir', t.dir);
  document.body.classList.toggle('rtl', t.dir === 'rtl');

  document.querySelectorAll('.lb').forEach(b => {
    const fn = b.getAttribute('onclick') || '';
    b.classList.toggle('active', fn.includes("'" + lang + "'"));
  });

  // Apply all text translations
  document.querySelectorAll('[data-i18n]').forEach(el => {
    const key = el.getAttribute('data-i18n');
    if(t[key]) {
      if(el.tagName === 'H2' && el.innerHTML.includes('<em>')) {
        el.innerHTML = t[key]; // For HTML content
      } else {
        el.textContent = t[key]; // For plain text
      }
    }
  });

  // Apply all placeholder translations
  document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
    const key = el.getAttribute('data-i18n-placeholder');
    if(t[key]) {
      el.placeholder = t[key];
    }
  });
}

function setLang(lang) { applyLang(lang); }

function switchPortal(portal) {
  localStorage.setItem('dm_active_portal', portal);
  document.querySelectorAll('.portal-card').forEach(c => c.classList.remove('active'));
  document.getElementById('pcard-' + portal).classList.add('active');

  document.querySelectorAll('.form-section').forEach(s => s.classList.remove('active'));
  document.getElementById('section-' + portal).classList.add('active');
}

function switchAuthTab(portal, tab) {
  localStorage.setItem('dm_active_tab_' + portal, tab);
  const p = {user:'u', agent:'a', office:'o'}[portal];
  ['login','signup'].forEach(t => {
    document.getElementById(p+'tab-'+t).classList.remove('active');
    document.getElementById(p+'panel-'+t).classList.remove('active');
  });
  document.getElementById(p+'tab-'+tab).classList.add('active');
  document.getElementById(p+'panel-'+tab).classList.add('active');
}

function togglePw(id, btn) {
  const inp = document.getElementById(id);
  inp.type = inp.type === 'password' ? 'text' : 'password';
  btn.querySelector('i').className = inp.type === 'text' ? 'fas fa-eye' : 'fas fa-eye-slash';
}

function triggerGoogle() { if (typeof google !== 'undefined') google.accounts.id.prompt(); }

window.addEventListener('DOMContentLoaded', () => {
  applyLang(localStorage.getItem('dm_lang') || 'ku');

  // Memory & Validation Tracking
  let activePortal = "{{ old('portal_type', session('portal_type', false)) }}";
  let activeTab = "login";

  if(!activePortal) {
      activePortal = localStorage.getItem('dm_active_portal') || 'user';
      activeTab = localStorage.getItem('dm_active_tab_' + activePortal) || 'login';
  }

  // Force Tab if Validation Fails
  @if($errors->has('username') || $errors->has('phone'))
      activePortal = 'user'; activeTab = 'signup';
  @elseif($errors->has('agent_name') || $errors->has('license_number'))
      activePortal = 'agent'; activeTab = 'signup';
  @elseif($errors->has('company_name') || $errors->has('office_address'))
      activePortal = 'office'; activeTab = 'signup';
  @endif

  switchPortal(activePortal);
  switchAuthTab(activePortal, activeTab);

  if (typeof google !== 'undefined') {
    google.accounts.id.initialize({
      client_id: "YOUR_GOOGLE_CLIENT_ID",
      callback: function(r) {
        fetch("{{ route('auth.google') }}", {
          method:"POST",
          headers:{"Content-Type":"application/json","X-CSRF-TOKEN":"{{ csrf_token() }}"},
          body: JSON.stringify({id_token:r.credential,device_name:navigator.userAgent})
        }).then(res=>res.json()).then(d=>{
          if(d.success) window.location.href="/";
          else alert(d.message||"Google login failed");
        });
      }
    });
    google.accounts.id.renderButton(document.getElementById("google_button"),{theme:"outline",size:"large",width:"100%"});
  }
});
</script>
</body>
</html>
