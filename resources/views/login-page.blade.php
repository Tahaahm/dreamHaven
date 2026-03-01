<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Dream Mulk — Sign In</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,800;1,400&family=DM+Sans:wght@300;400;500;600&family=Cinzel:wght@400;600&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css"/>
<script src="https://accounts.google.com/gsi/client" async defer></script>
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
:root{
  --deep:#06091e;--P:#303b97;--PD:#1a225a;--PM:#232d7a;
  --G:#d4af37;--GL:#e8cb6a;--GP:#f5e8c0;
  --E:cubic-bezier(.16,1,.3,1);
}
html,body{height:100%;font-family:'DM Sans',sans-serif;background:var(--deep);color:#fff;overflow:hidden;}

/* ── SPLIT LAYOUT ── */
.wrap{display:flex;height:100vh;width:100%;}

/* LEFT PANEL — visual / brand */
.left{
  flex:1;position:relative;overflow:hidden;
  background:var(--deep);
  display:flex;flex-direction:column;justify-content:flex-end;
  padding:60px;
}
canvas#lc{position:absolute;inset:0;width:100%;height:100%;z-index:0;}
.left-vig{
  position:absolute;inset:0;z-index:1;
  background:
    radial-gradient(ellipse 80% 60% at 50% 100%,rgba(6,9,30,.98) 0%,transparent 65%),
    radial-gradient(ellipse 60% 40% at 0% 50%,rgba(6,9,30,.7) 0%,transparent 55%),
    radial-gradient(ellipse 50% 50% at 100% 0%,rgba(6,9,30,.4) 0%,transparent 55%);
}
.left-content{position:relative;z-index:2;}
.brand-row{display:flex;align-items:center;gap:14px;margin-bottom:40px;}
.brand-icon{
  width:54px;height:54px;border-radius:16px;
  background:linear-gradient(135deg,var(--P),var(--PD));
  border:2px solid rgba(212,175,55,.5);
  display:flex;align-items:center;justify-content:center;
  font-size:22px;color:var(--G);
  box-shadow:0 4px 20px rgba(48,59,151,.4);
}
.brand-name{font-family:'Playfair Display',serif;font-size:26px;font-weight:700;color:#fff;}
.left-tag{
  font-family:'Cinzel',serif;font-size:10px;letter-spacing:5px;
  text-transform:uppercase;color:var(--G);margin-bottom:18px;
  display:flex;align-items:center;gap:12px;
}
.left-tag::before{content:'';width:36px;height:1px;background:var(--G);}
.left-headline{
  font-family:'Playfair Display',serif;
  font-size:clamp(32px,4vw,52px);font-weight:800;
  line-height:1.05;letter-spacing:-1.5px;color:#fff;margin-bottom:16px;
}
.left-headline em{font-style:italic;color:var(--G);}
.left-desc{font-size:15px;line-height:1.8;color:rgba(255,255,255,.55);font-weight:300;max-width:380px;margin-bottom:40px;}
.left-stats{display:flex;gap:36px;}
.lst{text-align:left;}
.lst-n{font-family:'Playfair Display',serif;font-size:30px;font-weight:700;color:var(--G);line-height:1;}
.lst-l{font-size:11px;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.4);margin-top:4px;}

/* RIGHT PANEL — form */
.right{
  width:480px;flex-shrink:0;
  background:rgba(8,12,36,.95);
  backdrop-filter:blur(20px);
  border-left:1px solid rgba(212,175,55,.12);
  display:flex;flex-direction:column;
  justify-content:center;
  padding:56px 52px;
  overflow-y:auto;
  position:relative;
}
.right::before{
  content:'';position:absolute;top:0;left:0;right:0;height:3px;
  background:linear-gradient(90deg,transparent,var(--G),transparent);
}

/* TABS */
.tabs{display:flex;gap:0;margin-bottom:40px;border-bottom:1px solid rgba(255,255,255,.08);}
.tab{
  flex:1;text-align:center;padding:14px 0;font-size:14px;font-weight:600;
  letter-spacing:.5px;color:rgba(255,255,255,.38);cursor:pointer;
  border-bottom:2px solid transparent;margin-bottom:-1px;
  transition:all .35s var(--E);position:relative;
}
.tab.active{color:var(--G);border-bottom-color:var(--G);}
.tab:hover:not(.active){color:rgba(255,255,255,.65);}

/* FORM PANELS */
.panel{display:none;animation:fadeIn .4s var(--E);}
.panel.active{display:block;}
@keyframes fadeIn{from{opacity:0;transform:translateY(12px);}to{opacity:1;transform:translateY(0);}}

.form-title{font-family:'Playfair Display',serif;font-size:26px;font-weight:700;color:#fff;margin-bottom:6px;}
.form-sub{font-size:13.5px;color:rgba(255,255,255,.45);font-weight:300;margin-bottom:30px;line-height:1.6;}

/* INPUT */
.ibox{margin-bottom:18px;}
.ibox label{display:block;font-size:12.5px;font-weight:600;letter-spacing:.5px;color:rgba(255,255,255,.6);margin-bottom:8px;text-transform:uppercase;}
.ifield{
  position:relative;display:flex;align-items:center;
}
.ifield i.ico{
  position:absolute;left:14px;font-size:17px;color:rgba(255,255,255,.3);
  transition:color .3s;pointer-events:none;z-index:1;
}
.ifield input{
  width:100%;height:50px;
  background:rgba(255,255,255,.05);
  border:1px solid rgba(255,255,255,.1);
  border-radius:12px;
  padding:0 44px 0 44px;
  color:#fff;font-size:14.5px;font-family:'DM Sans',sans-serif;
  outline:none;transition:all .3s var(--E);
}
.ifield input::placeholder{color:rgba(255,255,255,.22);}
.ifield input:focus{
  background:rgba(212,175,55,.06);
  border-color:rgba(212,175,55,.5);
  box-shadow:0 0 0 3px rgba(212,175,55,.08);
}
.ifield input:focus ~ i.ico,
.ifield i.ico:has(~ input:focus){color:var(--G);}
.ifield input:focus + .ico-l{color:var(--G);}
/* icon left focus trick — use sibling selector on wrapper */
.ifield:focus-within i.ico{color:var(--G);}

.ifield input[type="file"]{height:auto;padding:13px 14px 13px 44px;}
.eye-btn{
  position:absolute;right:14px;color:rgba(255,255,255,.3);
  font-size:17px;cursor:pointer;transition:color .3s;z-index:1;
  background:none;border:none;
}
.eye-btn:hover{color:var(--G);}

/* ERROR */
.err{font-size:12px;color:#f87171;margin-top:6px;display:block;}
.alert-box{
  padding:12px 14px;border-radius:10px;font-size:13px;margin-bottom:18px;
  display:flex;align-items:center;gap:10px;
}
.alert-box.danger{background:rgba(239,68,68,.1);color:#fca5a5;border:1px solid rgba(239,68,68,.2);}
.alert-box.success{background:rgba(34,197,94,.1);color:#86efac;border:1px solid rgba(34,197,94,.2);}

/* REMEMBER ROW */
.opt-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;}
.chk-wrap{display:flex;align-items:center;gap:8px;cursor:pointer;}
.chk-wrap input[type=checkbox]{
  width:17px;height:17px;border-radius:4px;
  accent-color:var(--G);cursor:pointer;
}
.chk-wrap span{font-size:13px;color:rgba(255,255,255,.55);}
.forgot-link{font-size:13px;color:var(--G);font-weight:500;text-decoration:none;transition:color .3s;}
.forgot-link:hover{color:var(--GL);}

/* SUBMIT BTN */
.submit-btn{
  width:100%;height:52px;
  background:linear-gradient(135deg,var(--G) 0%,var(--GL) 100%);
  color:var(--PD);font-family:'DM Sans',sans-serif;font-size:14px;font-weight:700;
  letter-spacing:1.5px;text-transform:uppercase;border:none;border-radius:12px;
  cursor:pointer;transition:all .4s var(--E);
  box-shadow:0 4px 24px rgba(212,175,55,.35);
  position:relative;overflow:hidden;
  display:flex;align-items:center;justify-content:center;gap:10px;
}
.submit-btn::after{
  content:'';position:absolute;inset:0;
  background:linear-gradient(135deg,rgba(255,255,255,.18),transparent);
  opacity:0;transition:opacity .3s;
}
.submit-btn:hover{transform:translateY(-2px);box-shadow:0 12px 36px rgba(212,175,55,.5);}
.submit-btn:hover::after{opacity:1;}
.submit-btn:active{transform:translateY(0);}

/* DIVIDER */
.divider{display:flex;align-items:center;gap:14px;margin:22px 0;}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:rgba(255,255,255,.08);}
.divider span{font-size:12px;color:rgba(255,255,255,.3);letter-spacing:1px;text-transform:uppercase;}

/* GOOGLE BTN */
#google_button{margin-bottom:4px;}
.g-btn{
  width:100%;height:50px;border-radius:12px;
  border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.05);
  color:rgba(255,255,255,.8);font-family:'DM Sans',sans-serif;font-size:14px;font-weight:500;
  cursor:pointer;display:flex;align-items:center;justify-content:center;gap:12px;
  transition:all .35s var(--E);
}
.g-btn:hover{background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.22);transform:translateY(-1px);}
.g-btn svg{width:20px;height:20px;}

/* SWITCH */
.switch-text{text-align:center;font-size:13px;color:rgba(255,255,255,.4);margin-top:24px;}
.switch-text a{color:var(--G);font-weight:600;text-decoration:none;transition:color .3s;}
.switch-text a:hover{color:var(--GL);}

/* BACK LINK */
.back-home{
  position:absolute;top:24px;right:24px;
  display:flex;align-items:center;gap:7px;
  font-size:12.5px;color:rgba(255,255,255,.35);
  text-decoration:none;transition:color .3s;
  letter-spacing:.5px;
}
.back-home:hover{color:var(--G);}
.back-home i{font-size:14px;}

/* MOBILE */
@media(max-width:900px){
  .left{display:none;}
  .right{width:100%;padding:40px 32px;border-left:none;}
  html,body{overflow:auto;}
}
@media(max-width:480px){.right{padding:32px 22px;}}

/* SCROLL for right panel on small screens */
.right::-webkit-scrollbar{width:0;}
</style>
</head>
<body>

<div class="wrap">

  <!-- LEFT — animated brand panel -->
  <div class="left">
    <canvas id="lc"></canvas>
    <div class="left-vig"></div>
    <div class="left-content">
      <div class="brand-row">
        <div class="brand-icon"><i class="fas fa-building"></i></div>
        <span class="brand-name">Dream Mulk</span>
      </div>
      <div class="left-tag">Premium Real Estate</div>
      <h2 class="left-headline">Your Dream<br>Property <em>Awaits</em></h2>
      <p class="left-desc">Kurdistan's most trusted real estate platform. Buy, sell, and rent properties with zero commissions and radical transparency.</p>
      <div class="left-stats">
        <div class="lst"><div class="lst-n">500+</div><div class="lst-l">Properties</div></div>
        <div class="lst"><div class="lst-n">150+</div><div class="lst-l">Agents</div></div>
        <div class="lst"><div class="lst-n">0%</div><div class="lst-l">Commission</div></div>
      </div>
    </div>
  </div>

  <!-- RIGHT — form -->
  <div class="right">
    <a href="{{ route('newindex') }}" class="back-home"><i class="fas fa-arrow-left"></i> Back to Home</a>

    <!-- TABS -->
    <div class="tabs">
      <div class="tab active" id="tab-login">Sign In</div>
      <div class="tab" id="tab-signup">Create Account</div>
    </div>

    <!-- LOGIN PANEL -->
    <div class="panel active" id="panel-login">
      <div class="form-title">Welcome back</div>
      <div class="form-sub">Enter your credentials to access your account.</div>

      @if(session('error') && old('active_form') === 'login-section')
        <div class="alert-box danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
      @endif

      <form action="{{ route('login') }}" method="POST">
        @csrf
        <div class="ibox">
          <label>Email Address</label>
          <div class="ifield">
            <i class="uil uil-envelope-alt ico"></i>
            <input type="email" name="email" placeholder="you@example.com" value="{{ old('email') }}" required/>
          </div>
          @error('email')<span class="err">{{ $message }}</span>@enderror
        </div>

        <div class="ibox">
          <label>Password</label>
          <div class="ifield">
            <i class="uil uil-lock ico"></i>
            <input type="password" id="lp" name="password" placeholder="Enter your password" required/>
            <button type="button" class="eye-btn" onclick="togglePw('lp',this)"><i class="uil uil-eye-slash"></i></button>
          </div>
          @error('password')<span class="err">{{ $message }}</span>@enderror
        </div>

        <div class="opt-row">
          <label class="chk-wrap">
            <input type="checkbox" name="remember"/>
            <span>Remember me</span>
          </label>
          <a href="#" class="forgot-link">Forgot password?</a>
        </div>

        <button type="submit" class="submit-btn"><i class="fas fa-arrow-right-to-bracket"></i> Sign In</button>
      </form>

      <div class="divider"><span>or continue with</span></div>

      <button class="g-btn" onclick="triggerGoogle()">
        <svg viewBox="0 0 48 48"><path fill="#FFC107" d="M43.6 20.1H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34.5 6.5 29.5 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.6-.4-3.9z"/><path fill="#FF3D00" d="m6.3 14.7 6.6 4.8C14.7 16 19 13 24 13c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34.5 6.5 29.5 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/><path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.3 35.1 26.8 36 24 36c-5.2 0-9.6-3.3-11.3-8H6.4C9.7 35.6 16.3 44 24 44z"/><path fill="#1976D2" d="M43.6 20.1H42V20H24v8h11.3c-.8 2.3-2.3 4.2-4.2 5.6l6.2 5.2C37 39 44 34 44 24c0-1.3-.1-2.6-.4-3.9z"/></svg>
        Continue with Google
      </button>

      <div class="switch-text">Don't have an account? <a href="#" id="go-signup">Create one</a></div>

      <div id="google_button" style="display:none;"></div>
    </div>

    <!-- SIGNUP PANEL -->
    <div class="panel" id="panel-signup">
      <div class="form-title">Create account</div>
      <div class="form-sub">Fill in your details to get started on Dream Mulk.</div>

      @if(session('error'))<div class="alert-box danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>@endif
      @if(session('success'))<div class="alert-box success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>@endif

      <form action="{{ route('user.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="role" value="user"/>

        <div class="ibox">
          <label>Username</label>
          <div class="ifield">
            <i class="uil uil-user ico"></i>
            <input type="text" name="username" placeholder="johndoe" value="{{ old('username') }}" required/>
          </div>
          @error('username')<span class="err">{{ $message }}</span>@enderror
        </div>

        <div class="ibox">
          <label>Email Address</label>
          <div class="ifield">
            <i class="uil uil-envelope-alt ico"></i>
            <input type="email" name="email" placeholder="you@example.com" value="{{ old('email', session('email')) }}" required/>
          </div>
          @error('email')<span class="err">{{ $message }}</span>@enderror
        </div>

        <div class="ibox">
          <label>Password</label>
          <div class="ifield">
            <i class="uil uil-lock ico"></i>
            <input type="password" id="sp" name="password" placeholder="Create a strong password" required/>
            <button type="button" class="eye-btn" onclick="togglePw('sp',this)"><i class="uil uil-eye-slash"></i></button>
          </div>
          @error('password')<span class="err">{{ $message }}</span>@enderror
        </div>

        <div class="ibox">
          <label>Confirm Password</label>
          <div class="ifield">
            <i class="uil uil-lock ico"></i>
            <input type="password" id="scp" name="password_confirmation" placeholder="Repeat your password" required/>
            <button type="button" class="eye-btn" onclick="togglePw('scp',this)"><i class="uil uil-eye-slash"></i></button>
          </div>
          @error('password_confirmation')<span class="err">{{ $message }}</span>@enderror
        </div>

        <div class="ibox">
          <label>Phone Number</label>
          <div class="ifield">
            <i class="uil uil-phone ico"></i>
            <input type="tel" name="phone" placeholder="07XX XXX XXXX" value="{{ old('phone') }}" pattern="[0-9]{10,15}" required/>
          </div>
          @error('phone')<span class="err">{{ $message }}</span>@enderror
        </div>

        <div class="ibox">
          <label>Profile Image <span style="color:rgba(255,255,255,.3);font-weight:400;">(optional)</span></label>
          <div class="ifield">
            <i class="uil uil-image ico"></i>
            <input type="file" name="image" accept="image/*"/>
          </div>
          @error('image')<span class="err">{{ $message }}</span>@enderror
        </div>

        @if(session('error') && old('active_form') === 'signup-section')
          <div class="alert-box danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
        @endif

        <button type="submit" class="submit-btn" style="margin-top:4px;"><i class="fas fa-user-plus"></i> Create Account</button>
      </form>

      <div class="switch-text">Already have an account? <a href="#" id="go-login">Sign in</a></div>
    </div>

  </div><!-- /right -->
</div><!-- /wrap -->

<!-- THREE.JS background for left panel -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js"></script>
<script>
(function(){
  const cv=document.getElementById('lc');
  if(!cv)return;
  const W=()=>cv.parentElement.offsetWidth, H=()=>cv.parentElement.offsetHeight;
  const renderer=new THREE.WebGLRenderer({canvas:cv,antialias:true});
  renderer.setPixelRatio(Math.min(devicePixelRatio,2));
  renderer.setSize(W(),H());
  renderer.setClearColor(0x06091e,1);
  const scene=new THREE.Scene(), camera=new THREE.PerspectiveCamera(55,W()/H(),.01,200);
  camera.position.set(0,1.4,6.8); camera.lookAt(0,1.2,0);

  const CG=0xd4af37,CG2=0xe8cb6a,CB=0x2d3fb0,CBM=0x1a2460;
  const TOT=4.2,BY=-1.5,SPR=.38;
  const TIERS=[{h:0,w:.58},{h:.12,w:.49},{h:.24,w:.41},{h:.37,w:.34},{h:.51,w:.27},{h:.64,w:.20},{h:.76,w:.13},{h:.86,w:.07},{h:.93,w:.03},{h:1,w:0}];
  const RINGS=[7,6,6,5,5,4,3,2,1];
  const LOBES=[Math.PI*.5,Math.PI*.5+2.094,Math.PI*.5+4.189];
  const SPIRAL=Math.PI*.65;

  function lerp(a,b,t){return a+(b-a)*t;}
  function easeOut(t){return 1-Math.pow(1-t,3);}

  function yRing(cx,cy,cz,radius,spin,segs){
    const pts=[],ARC=Math.PI*.73,S=segs||11;
    for(let l=0;l<3;l++){
      const la=LOBES[l]+spin;
      for(let s=0;s<=S;s++){
        const t=s/S,ang=la-ARC/2+t*ARC,bulge=radius*.38*Math.sin(t*Math.PI);
        pts.push(new THREE.Vector3(cx+Math.cos(ang)*(radius+bulge),cy,cz+Math.sin(ang)*(radius+bulge)));
      }
    }
    pts.push(pts[0].clone());
    return pts;
  }

  const LDEFS=[];
  const AS=.5,AT=3.6;

  // ground grid
  const GS=5,GN=14;
  for(let i=0;i<=GN;i++){
    const t=(i/GN)*GS-GS/2;
    LDEFS.push({pts:[new THREE.Vector3(t,BY,-GS/2),new THREE.Vector3(t,BY,GS/2)],col:CBM,op:.16,born:.05,dur:.2});
    LDEFS.push({pts:[new THREE.Vector3(-GS/2,BY,t),new THREE.Vector3(GS/2,BY,t)],col:CBM,op:.16,born:.05,dur:.2});
  }

  const ALLR=[];
  for(let ti=0;ti<TIERS.length-1;ti++){
    const T0=TIERS[ti],T1=TIERS[ti+1],nR=RINGS[ti]||2;
    for(let r=0;r<=nR;r++){
      const f=r/nR,ny=lerp(T0.h,T1.h,f),w=lerp(T0.w,T1.w,f);
      ALLR.push({ny,y:BY+ny*TOT,w,spin:ny*SPIRAL,major:r===0});
    }
  }
  ALLR.sort((a,b)=>a.y-b.y);

  ALLR.forEach(({ny,y,w,spin,major})=>{
    if(w<.005)return;
    LDEFS.push({pts:yRing(0,y,0,w,spin,major?15:8),col:ny>.82?CG2:ny>.50?CG:CB,op:major?.86:.36,born:AS+ny*AT,dur:major?.5:.3});
  });

  for(let l=0;l<3;l++){
    const out=[],mid=[];
    ALLR.forEach(({ny,y,w,spin})=>{
      if(w<.005)return;
      const la=LOBES[l]+spin,b=w*.38;
      out.push(new THREE.Vector3(Math.cos(la)*(w+b),y,Math.sin(la)*(w+b)));
      mid.push(new THREE.Vector3(Math.cos(la)*w*.52,y,Math.sin(la)*w*.52));
    });
    LDEFS.push({pts:out,col:CG,op:.58,born:AS,dur:AT+.5});
    LDEFS.push({pts:mid,col:CB,op:.26,born:AS+.12,dur:AT+.5});
  }

  LDEFS.push({pts:ALLR.filter(r=>r.w>.005).map(r=>new THREE.Vector3(0,r.y,0)),col:CBM,op:.2,born:AS+.08,dur:AT+.4});

  TIERS.forEach(T=>{
    if(T.w<.04)return;
    const y=BY+T.h*TOT,spin=T.h*SPIRAL,born=AS+T.h*AT;
    for(let l=0;l<3;l++){
      const la=LOBES[l]+spin,ro=T.w+T.w*.38;
      LDEFS.push({pts:[new THREE.Vector3(Math.cos(la)*ro,y,Math.sin(la)*ro),new THREE.Vector3(0,y,0)],col:CB,op:.18,born,dur:.26});
    }
  });

  const SB=BY+.92*TOT,ST=SB+SPR+TOT*.07,spirePts=[];
  for(let i=0;i<=22;i++) spirePts.push(new THREE.Vector3(0,lerp(SB,ST,i/22),0));
  LDEFS.push({pts:spirePts,col:0xfff8b0,op:1,born:AS+AT*.88,dur:.4});

  // reflection
  ALLR.filter(r=>r.ny<.34).forEach(({ny,y,w,spin})=>{
    if(w<.02)return;
    const refY=BY-(y-BY)*.48;
    if(refY<BY-1)return;
    const op=Math.max(0,.26-(BY-refY)*.3);
    LDEFS.push({pts:yRing(0,refY,0,w,spin,7),col:CB,op,born:AS+ny*AT+.16,dur:.3});
  });

  const OBJS=LDEFS.map(ld=>{
    const nPts=ld.pts.length;
    const pos=new Float32Array(nPts*3);
    ld.pts.forEach((p,i)=>{pos[i*3]=p.x;pos[i*3+1]=p.y;pos[i*3+2]=p.z;});
    const geo=new THREE.BufferGeometry();
    geo.setAttribute('position',new THREE.BufferAttribute(pos,3));
    geo.setDrawRange(0,0);
    const mat=new THREE.LineBasicMaterial({color:ld.col,transparent:true,opacity:ld.op});
    scene.add(new THREE.Line(geo,mat));
    return{geo,born:ld.born,dur:ld.dur,nPts};
  });

  // particles
  {const g=new THREE.BufferGeometry(),p=[];for(let i=0;i<600;i++)p.push((Math.random()-.5)*16,(Math.random()-.15)*8+.5,(Math.random()-.85)*10-1);g.setAttribute('position',new THREE.Float32BufferAttribute(p,3));scene.add(new THREE.Points(g,new THREE.PointsMaterial({color:0x6688cc,size:.018,transparent:true,opacity:.28})));}
  {const g=new THREE.BufferGeometry(),p=[];for(let i=0;i<180;i++)p.push((Math.random()-.5)*10,BY+Math.random()*.28,(Math.random()-.5)*7);g.setAttribute('position',new THREE.Float32BufferAttribute(p,3));scene.add(new THREE.Points(g,new THREE.PointsMaterial({color:CG,size:.022,transparent:true,opacity:.24})));}

  let mx=0,my=0,smx=0,smy=0;
  document.addEventListener('mousemove',e=>{mx=(e.clientX/innerWidth-.5);my=(e.clientY/innerHeight-.5);});
  window.addEventListener('resize',()=>{renderer.setSize(W(),H());camera.aspect=W()/H();camera.updateProjectionMatrix();});

  const clk=new THREE.Clock();
  (function loop(){
    requestAnimationFrame(loop);
    const el=clk.getElapsedTime();
    smx+=(mx-smx)*.03; smy+=(my-smy)*.03;
    OBJS.forEach(({geo,born,dur,nPts})=>{
      const age=el-born;
      if(age<0){geo.setDrawRange(0,0);return;}
      geo.setDrawRange(0,Math.max(2,Math.floor(easeOut(Math.min(age/dur,1))*nPts)));
    });
    scene.rotation.y=el*.04+smx*.16;
    scene.rotation.x=smy*.06;
    camera.position.y=1.4+Math.sin(el*.25)*.05;
    renderer.render(scene,camera);
  })();
})();
</script>

<script>
/* ── GOOGLE LOGIN ── */
window.onload=function(){
  if(typeof google!=='undefined'){
    google.accounts.id.initialize({
      client_id:"YOUR_GOOGLE_CLIENT_ID",
      callback:function(response){
        fetch("{{ route('auth.google') }}",{
          method:"POST",
          headers:{"Content-Type":"application/json","X-CSRF-TOKEN":"{{ csrf_token() }}"},
          body:JSON.stringify({id_token:response.credential,device_name:navigator.userAgent})
        }).then(r=>r.json()).then(d=>{
          if(d.success) window.location.href="/dashboard";
          else alert(d.message||"Google login failed");
        });
      }
    });
    google.accounts.id.renderButton(document.getElementById("google_button"),{theme:"outline",size:"large",width:"100%"});
  }
};

function triggerGoogle(){
  if(typeof google!=='undefined') google.accounts.id.prompt();
}

/* ── PASSWORD TOGGLE ── */
function togglePw(id,btn){
  const inp=document.getElementById(id);
  const ico=btn.querySelector('i');
  if(inp.type==='password'){inp.type='text';ico.className='uil uil-eye';}
  else{inp.type='password';ico.className='uil uil-eye-slash';}
}

/* ── TAB SWITCHING ── */
const tabLogin=document.getElementById('tab-login');
const tabSignup=document.getElementById('tab-signup');
const panelLogin=document.getElementById('panel-login');
const panelSignup=document.getElementById('panel-signup');
const goSignup=document.getElementById('go-signup');
const goLogin=document.getElementById('go-login');

function showLogin(){
  tabLogin.classList.add('active'); tabSignup.classList.remove('active');
  panelLogin.classList.add('active'); panelSignup.classList.remove('active');
  document.querySelector('.right').scrollTop=0;
}
function showSignup(){
  tabSignup.classList.add('active'); tabLogin.classList.remove('active');
  panelSignup.classList.add('active'); panelLogin.classList.remove('active');
  document.querySelector('.right').scrollTop=0;
}

tabLogin.addEventListener('click',showLogin);
tabSignup.addEventListener('click',showSignup);
goSignup.addEventListener('click',e=>{e.preventDefault();showSignup();});
goLogin.addEventListener('click',e=>{e.preventDefault();showLogin();});

/* Auto-switch to signup if validation errors exist on signup fields */
@if(session('error') || $errors->has('username') || $errors->has('phone'))
  showSignup();
@endif
</script>
</body>
</html>
