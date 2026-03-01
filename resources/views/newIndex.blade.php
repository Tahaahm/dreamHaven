<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width,initial-scale=1.0" name="viewport"/>
<title>Dream Mulk - Premium Real Estate Kurdistan</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,800;1,700&family=DM+Sans:wght@300;400;500;600&family=Cinzel:wght@400;600&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
:root{
  --P:#303b97;--PD:#1a225a;--PM:#232d7a;--deep:#06091e;
  --G:#d4af37;--GL:#e8cb6a;--GP:#f5e8c0;
  --dim:rgba(255,255,255,.55);--E:cubic-bezier(.16,1,.3,1);
}
html{scroll-behavior:smooth;}
body{font-family:'DM Sans',sans-serif;background:var(--deep);color:#fff;overflow-x:hidden;}

/* HEADER */
header{position:fixed;top:0;left:0;right:0;height:90px;z-index:1100;padding:0 50px;display:flex;align-items:center;background:linear-gradient(to bottom,rgba(6,9,30,.7),transparent);transition:all .5s var(--E);}
header.sc{background:rgba(6,9,30,.96);backdrop-filter:blur(20px);height:72px;box-shadow:0 1px 0 rgba(212,175,55,.1);}
nav{max-width:1400px;width:100%;margin:0 auto;display:flex;align-items:center;justify-content:space-between;}
.logo{display:flex;align-items:center;gap:14px;text-decoration:none;}
.logo img{width:56px;height:56px;border-radius:50%;border:2.5px solid var(--G);object-fit:contain;background:#fff;transition:transform .5s var(--E),width .5s var(--E);box-shadow:0 4px 15px rgba(212,175,55,.25);}
header.sc .logo img{width:44px;height:44px;}
.logo:hover img{transform:scale(1.08) rotate(5deg);}
.logo-name{font-family:'Playfair Display',serif;font-size:24px;font-weight:700;color:#fff;letter-spacing:.3px;}
header.sc .logo-name{font-size:21px;}
.nav-ul{display:flex;gap:36px;list-style:none;}
.nav-ul a{color:rgba(255,255,255,.7);font-size:14.5px;text-decoration:none;position:relative;padding:4px 0;transition:color .3s;}
.nav-ul a::after{content:'';position:absolute;bottom:0;left:0;width:0;height:1.5px;background:var(--G);transition:width .4s var(--E);}
.nav-ul a:hover,.nav-ul a.ac{color:#fff;}
.nav-ul a:hover::after,.nav-ul a.ac::after{width:100%;}
.nav-right{display:flex;align-items:center;gap:13px;}
.bell-wrap{position:relative;color:var(--G);font-size:20px;text-decoration:none;}
.bell-badge{position:absolute;top:-4px;right:-6px;background:#e74c3c;color:#fff;font-size:9px;font-weight:700;padding:2px 4px;border-radius:5px;}
.av-btn{width:38px;height:38px;border-radius:50%;background:var(--G);color:var(--PD);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:15px;text-decoration:none;transition:transform .3s;}
.av-btn:hover{transform:scale(1.1);}
.btn-o{padding:9px 24px;border:1px solid rgba(212,175,55,.45);border-radius:50px;color:var(--G);font-size:13px;text-decoration:none;transition:all .3s;}
.btn-o:hover{background:rgba(212,175,55,.1);}
.btn-s{padding:9px 24px;border:1px solid var(--G);border-radius:50px;background:var(--G);color:var(--PD);font-size:13px;font-weight:700;text-decoration:none;transition:all .4s var(--E);}
.btn-s:hover{background:var(--GL);transform:translateY(-2px);box-shadow:0 8px 22px rgba(212,175,55,.4);}
.hbtn{display:none;background:none;border:none;color:var(--G);font-size:26px;cursor:pointer;}
@media(max-width:1000px){.nav-ul,.nav-right{display:none;}.hbtn{display:block;}header{padding:0 22px;height:76px;}}

/* DRAWER */
.bkdp{position:fixed;inset:0;background:rgba(0,0,0,.6);opacity:0;pointer-events:none;transition:opacity .4s;z-index:1090;backdrop-filter:blur(6px);}
.bkdp.on{opacity:1;pointer-events:auto;}
.drw{position:fixed;top:0;right:-100%;height:100vh;width:min(360px,85%);background:var(--P);z-index:1100;padding:38px 28px;display:flex;flex-direction:column;gap:18px;transition:right .5s var(--E);box-shadow:-10px 0 40px rgba(0,0,0,.5);}
.drw::before{content:'';position:absolute;top:0;bottom:0;left:0;width:3px;background:var(--G);}
.drw.on{right:0;}
.drw-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;}
.drw-hd span{font-family:'Playfair Display',serif;font-size:24px;color:#fff;}
.drw-x{background:none;border:none;color:var(--G);font-size:22px;cursor:pointer;}
.drw-nav a{display:block;font-size:17px;padding:15px 0;color:rgba(255,255,255,.75);border-bottom:1px solid rgba(255,255,255,.06);text-decoration:none;transition:all .3s;}
.drw-nav a:hover{color:var(--G);padding-left:12px;}
.drw-ft{margin-top:auto;display:flex;flex-direction:column;gap:12px;}

/* HERO */
.hero{position:relative;height:100vh;min-height:680px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:var(--deep);}
#bc{position:absolute;inset:0;width:100%;height:100%;z-index:0;}
.vig{position:absolute;inset:0;z-index:1;pointer-events:none;
  background:
    radial-gradient(ellipse 95% 52% at 50% 100%,rgba(6,9,30,1) 0%,transparent 62%),
    radial-gradient(ellipse 55% 38% at 50% 0%,rgba(6,9,30,.65) 0%,transparent 55%),
    radial-gradient(ellipse 28% 100% at 0%,rgba(6,9,30,.58) 0%,transparent 50%),
    radial-gradient(ellipse 28% 100% at 100%,rgba(6,9,30,.58) 0%,transparent 50%);}
.hc{position:relative;z-index:10;text-align:center;max-width:860px;padding:0 24px;pointer-events:none;}
.eyebrow{display:inline-flex;align-items:center;gap:14px;font-size:10px;font-weight:600;letter-spacing:5px;text-transform:uppercase;color:var(--G);margin-bottom:22px;opacity:0;animation:fup .9s var(--E) .3s forwards;}
.eyebrow::before,.eyebrow::after{content:'';width:46px;height:1px;}
.eyebrow::before{background:linear-gradient(90deg,transparent,var(--G));}
.eyebrow::after{background:linear-gradient(90deg,var(--G),transparent);}
h1{font-family:'Playfair Display',serif;font-size:clamp(60px,10vw,116px);font-weight:800;line-height:.93;letter-spacing:-3px;color:#fff;margin-bottom:12px;opacity:0;animation:fup .9s var(--E) .5s forwards;}
h1 .g{color:var(--G);}
.sub{font-family:'Cinzel',serif;font-size:clamp(10px,1.4vw,13px);letter-spacing:7px;text-transform:uppercase;color:rgba(255,255,255,.27);margin-bottom:28px;opacity:0;animation:fup .9s var(--E) .7s forwards;}
.hdesc{font-size:16px;line-height:1.85;color:var(--dim);max-width:500px;margin:0 auto 48px;font-weight:300;opacity:0;animation:fup .9s var(--E) .9s forwards;}
.hbtns{display:flex;align-items:center;justify-content:center;gap:16px;flex-wrap:wrap;opacity:0;animation:fup .9s var(--E) 1.1s forwards;pointer-events:auto;}
.hb1{padding:16px 46px;background:var(--G);color:var(--PD);font-weight:700;font-size:12px;letter-spacing:2.5px;text-transform:uppercase;border-radius:50px;text-decoration:none;transition:all .4s var(--E);box-shadow:0 4px 26px rgba(212,175,55,.4);}
.hb1:hover{background:var(--GL);transform:translateY(-4px);box-shadow:0 16px 40px rgba(212,175,55,.55);}
.hb2{padding:15px 46px;border:1px solid rgba(255,255,255,.2);color:rgba(255,255,255,.8);font-size:12px;letter-spacing:2.5px;text-transform:uppercase;border-radius:50px;text-decoration:none;transition:all .4s var(--E);backdrop-filter:blur(8px);}
.hb2:hover{border-color:var(--G);color:var(--G);transform:translateY(-4px);background:rgba(212,175,55,.06);}
.scrl{position:absolute;bottom:34px;left:50%;transform:translateX(-50%);z-index:10;display:flex;flex-direction:column;align-items:center;gap:8px;opacity:0;animation:fi 1.5s ease 2.8s forwards;}
.scrl span{font-size:9px;letter-spacing:4px;text-transform:uppercase;color:rgba(255,255,255,.22);}
.mouse{width:22px;height:34px;border:1.5px solid rgba(212,175,55,.38);border-radius:12px;display:flex;justify-content:center;padding-top:6px;}
.mouse::after{content:'';width:3px;height:8px;border-radius:2px;background:var(--G);animation:sp 2s ease-in-out infinite;}
@keyframes fup{from{opacity:0;transform:translateY(38px);}to{opacity:1;transform:translateY(0);}}
@keyframes fi{to{opacity:1;}}
@keyframes sp{0%,100%{transform:translateY(0);opacity:1;}50%{transform:translateY(9px);opacity:0;}}

/* STATS */
.stats-bar{background:rgba(212,175,55,.06);border-top:1px solid rgba(212,175,55,.15);border-bottom:1px solid rgba(212,175,55,.15);padding:36px 60px;}
.stats-inner{max-width:1050px;margin:0 auto;display:flex;justify-content:space-between;align-items:center;gap:24px;}
.stat-item{text-align:center;}
.stat-num{font-family:'Playfair Display',serif;font-size:48px;font-weight:700;color:var(--G);line-height:1;}
.stat-label{font-size:11px;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.4);margin-top:6px;}
.stat-div{width:1px;height:50px;background:rgba(212,175,55,.2);}
@media(max-width:768px){.stats-bar{padding:34px 22px;}.stats-inner{flex-wrap:wrap;justify-content:center;gap:28px;}.stat-div{display:none;}}

/* SERVICES */
.svc-sec{padding:120px 60px;background:linear-gradient(180deg,var(--deep) 0%,var(--PD) 100%);}
.sec-wrap{max-width:1320px;margin:0 auto;}
.sec-hd{text-align:center;margin-bottom:70px;}
.stag{font-size:10px;letter-spacing:5px;text-transform:uppercase;color:var(--G);display:block;margin-bottom:14px;}
.stitle{font-family:'Playfair Display',serif;font-size:clamp(36px,5vw,60px);font-weight:700;color:#fff;line-height:1.1;}
.stitle em{font-style:italic;color:var(--G);}
.svc-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:3px;}
.svc-card{position:relative;height:530px;overflow:hidden;display:block;text-decoration:none;color:inherit;}
.svc-card::before{content:'';position:absolute;inset:0;background:linear-gradient(to top,rgba(6,9,30,.97) 0%,rgba(18,25,72,.5) 50%,rgba(48,59,151,.1) 100%);transition:all .6s var(--E);z-index:1;}
.svc-card:hover::before{background:linear-gradient(to top,rgba(6,9,30,.98) 0%,rgba(18,25,72,.75) 65%,rgba(48,59,151,.25) 100%);}
.svc-img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;filter:saturate(.45) brightness(.75);transition:transform .9s var(--E),filter .6s;}
.svc-card:hover .svc-img{transform:scale(1.1);filter:saturate(.25) brightness(.65);}
.svc-body{position:absolute;bottom:0;left:0;right:0;padding:46px 38px;z-index:2;transition:transform .6s var(--E);}
.svc-card:hover .svc-body{transform:translateY(-16px);}
.svc-n{font-family:'Playfair Display',serif;font-size:70px;font-weight:700;color:rgba(212,175,55,.12);line-height:1;margin-bottom:4px;transition:color .4s;}
.svc-card:hover .svc-n{color:rgba(212,175,55,.3);}
.svc-ico{width:52px;height:52px;border:1.5px solid rgba(212,175,55,.38);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--G);font-size:18px;margin-bottom:18px;transition:all .5s var(--E);}
.svc-card:hover .svc-ico{background:var(--G);color:var(--PD);border-color:var(--G);transform:scale(1.1);}
.svc-t{font-family:'Playfair Display',serif;font-size:30px;font-weight:700;color:#fff;margin-bottom:10px;transition:color .3s;}
.svc-card:hover .svc-t{color:var(--G);}
.svc-p{font-size:13.5px;line-height:1.75;color:rgba(255,255,255,.58);max-height:0;overflow:hidden;opacity:0;transition:all .6s var(--E);margin-bottom:0;}
.svc-card:hover .svc-p{max-height:120px;opacity:1;margin-bottom:18px;}
.svc-cta{display:inline-flex;align-items:center;gap:8px;font-size:11px;letter-spacing:2.5px;text-transform:uppercase;color:var(--G);font-weight:600;transition:gap .4s var(--E);}
.svc-card:hover .svc-cta{gap:16px;}
@media(max-width:1024px){.svc-grid{grid-template-columns:1fr 1fr;}.svc-card:last-child{grid-column:span 2;}}
@media(max-width:768px){.svc-sec{padding:80px 22px;}.svc-grid{grid-template-columns:1fr;}.svc-card:last-child{grid-column:span 1;}}

/* APP */
.app-sec{padding:120px 60px;background:linear-gradient(135deg,var(--PM) 0%,var(--P) 50%,var(--PD) 100%);position:relative;overflow:hidden;}
.app-sec::before{content:'';position:absolute;top:-200px;right:-200px;width:600px;height:600px;border-radius:50%;border:1px solid rgba(212,175,55,.06);}
.app-g{display:grid;grid-template-columns:1fr 1fr;gap:90px;align-items:center;max-width:1300px;margin:0 auto;position:relative;z-index:1;}
.app-desc{font-size:16px;line-height:1.85;color:rgba(255,255,255,.62);margin-bottom:36px;font-weight:300;}
.app-feats{display:flex;flex-direction:column;gap:14px;margin-bottom:44px;}
.af{display:flex;align-items:center;gap:14px;font-size:14px;color:rgba(255,255,255,.75);}
.af i{color:var(--G);width:18px;}
.sbtns{display:flex;gap:14px;flex-wrap:wrap;}
.sbtn{display:flex;align-items:center;gap:14px;padding:14px 24px;border:1px solid rgba(212,175,55,.3);border-radius:14px;text-decoration:none;background:rgba(212,175,55,.06);transition:all .4s var(--E);}
.sbtn:hover{background:rgba(212,175,55,.14);border-color:var(--G);transform:translateY(-4px);box-shadow:0 12px 30px rgba(212,175,55,.2);}
.sbtn i{font-size:26px;color:var(--G);}
.sbtn-sm{font-size:10px;letter-spacing:1.2px;text-transform:uppercase;color:rgba(255,255,255,.45);}
.sbtn-nm{font-family:'Playfair Display',serif;font-size:17px;font-weight:700;color:#fff;}
.qr-card{background:#fff;border-radius:26px;padding:28px;display:flex;flex-direction:column;align-items:center;gap:18px;max-width:272px;margin:0 auto;box-shadow:0 0 0 1.5px rgba(212,175,55,.5),0 40px 80px rgba(0,0,0,.6);position:relative;overflow:hidden;transition:transform .6s var(--E);}
.qr-card:hover{transform:translateY(-10px) scale(1.02);}
.qr-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--P),var(--G),var(--P));}
.qr-brand{display:flex;align-items:center;gap:12px;width:100%;}
.qr-ico{width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,var(--PD),var(--P));display:flex;align-items:center;justify-content:center;color:#fff;font-size:19px;}
.qr-t{font-family:'Playfair Display',serif;font-size:16px;font-weight:700;color:var(--PD);}
.qr-s{font-size:9.5px;letter-spacing:1.5px;text-transform:uppercase;color:#aaa;}
.qr-div{width:100%;height:1px;background:rgba(48,59,151,.1);}
.qr-img{width:188px;height:188px;border-radius:12px;display:block;}
.qr-hint{display:flex;align-items:center;gap:8px;font-size:10.5px;letter-spacing:1px;text-transform:uppercase;color:#999;font-weight:500;}
.qr-lnk{display:flex;align-items:center;justify-content:center;gap:9px;width:100%;padding:12px;background:linear-gradient(135deg,var(--PD),var(--P));color:#fff;border-radius:14px;font-size:13px;font-weight:600;text-decoration:none;transition:all .4s var(--E);}
.qr-lnk:hover{box-shadow:0 8px 24px rgba(48,59,151,.5);transform:translateY(-2px);color:#fff;}
@media(max-width:1024px){.app-g{grid-template-columns:1fr;gap:60px;}}
@media(max-width:768px){.app-sec{padding:80px 22px;}}

/* ABOUT */
.abt-sec{padding:120px 60px;background:linear-gradient(180deg,var(--PD) 0%,var(--deep) 100%);}
.abt-g{display:grid;grid-template-columns:1.4fr 1fr;gap:100px;align-items:start;max-width:1300px;margin:0 auto;}
.abt-p{font-size:15.5px;line-height:1.95;color:rgba(255,255,255,.58);margin-bottom:20px;font-weight:300;}
.qbar{margin-top:34px;padding:26px 30px;border-left:3px solid var(--G);background:rgba(212,175,55,.04);border-radius:0 12px 12px 0;}
.qbar p{font-family:'Playfair Display',serif;font-size:20px;font-style:italic;color:var(--GP);line-height:1.55;}
.vals{display:flex;flex-direction:column;gap:2px;}
.vi{display:flex;align-items:center;gap:22px;padding:26px 28px;background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.05);transition:all .5s var(--E);cursor:default;}
.vi:hover{background:rgba(212,175,55,.05);border-color:rgba(212,175,55,.2);transform:translateX(12px);}
.vico{width:56px;height:56px;background:rgba(212,175,55,.1);border:1.5px solid rgba(212,175,55,.25);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--G);font-size:20px;flex-shrink:0;transition:all .4s var(--E);}
.vi:hover .vico{background:var(--G);color:var(--PD);transform:scale(1.1) rotate(10deg);}
.vinfo h4{font-family:'Playfair Display',serif;font-size:22px;font-weight:700;color:#fff;margin-bottom:3px;}
.vinfo span{font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.38);}
@media(max-width:1024px){.abt-g{grid-template-columns:1fr;gap:60px;}}
@media(max-width:768px){.abt-sec{padding:80px 22px;}}

/* REDIRECT / CTA */
.rdr-sec{padding:100px 60px;background:var(--P);position:relative;overflow:hidden;}
.rdr-sec::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 60% 80% at 85% 50%,rgba(212,175,55,.1) 0%,transparent 70%),radial-gradient(ellipse 40% 60% at 15% 50%,rgba(6,9,30,.5) 0%,transparent 70%);}
.rdr-sec::after{content:'';position:absolute;top:-120px;right:-120px;width:520px;height:520px;border-radius:50%;border:1px solid rgba(212,175,55,.1);}
.rdr-in{max-width:1150px;margin:0 auto;position:relative;z-index:1;display:grid;grid-template-columns:1fr auto;gap:70px;align-items:center;}
.rdr-ey{font-size:10px;letter-spacing:5px;text-transform:uppercase;color:var(--G);display:block;margin-bottom:14px;}
.rdr-t{font-family:'Playfair Display',serif;font-size:clamp(32px,4vw,52px);font-weight:700;color:#fff;line-height:1.2;margin-bottom:16px;}
.rdr-t strong{color:var(--G);}
.rdr-d{font-size:15px;color:rgba(255,255,255,.58);line-height:1.75;font-weight:300;}
.rdr-bs{display:flex;flex-direction:column;gap:13px;min-width:255px;}
.rdr-b1{display:flex;align-items:center;justify-content:center;gap:10px;padding:17px 36px;background:var(--G);color:var(--PD);font-weight:700;font-size:12px;letter-spacing:2.5px;text-transform:uppercase;border-radius:50px;text-decoration:none;transition:all .4s var(--E);box-shadow:0 4px 20px rgba(212,175,55,.4);}
.rdr-b1:hover{background:var(--GL);transform:translateY(-4px);box-shadow:0 14px 36px rgba(212,175,55,.5);}
.rdr-b2{display:flex;align-items:center;justify-content:center;gap:10px;padding:16px 36px;border:1px solid rgba(255,255,255,.22);color:rgba(255,255,255,.8);font-size:12px;letter-spacing:2px;text-transform:uppercase;border-radius:50px;text-decoration:none;transition:all .4s var(--E);}
.rdr-b2:hover{border-color:var(--G);color:var(--G);transform:translateY(-4px);background:rgba(212,175,55,.05);}
@media(max-width:900px){.rdr-in{grid-template-columns:1fr;}.rdr-bs{flex-direction:row;flex-wrap:wrap;}}
@media(max-width:768px){.rdr-sec{padding:80px 22px;}}

/* FOOTER */
footer{background:var(--PD);border-top:1px solid rgba(212,175,55,.12);padding:62px 60px 34px;}
.ft-in{max-width:1300px;margin:0 auto;}
.ft-top{display:flex;justify-content:space-between;align-items:flex-start;padding-bottom:44px;border-bottom:1px solid rgba(255,255,255,.05);margin-bottom:30px;gap:40px;flex-wrap:wrap;}
.ft-logo{display:flex;align-items:center;gap:10px;margin-bottom:12px;}
.ft-logo img{width:32px;height:32px;border-radius:50%;border:none;object-fit:contain;background:#fff;}
.ft-logo-name{font-family:'Playfair Display',serif;font-size:22px;font-weight:700;color:#fff;}
.ft-tag{font-size:13px;color:rgba(255,255,255,.38);line-height:1.7;max-width:248px;}
.ft-col h5{font-size:10px;letter-spacing:3px;text-transform:uppercase;color:var(--G);margin-bottom:16px;}
.ft-col ul{list-style:none;display:flex;flex-direction:column;gap:10px;}
.ft-col a{font-size:14px;color:rgba(255,255,255,.45);text-decoration:none;transition:color .3s;}
.ft-col a:hover{color:var(--G);}
.ft-bot{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;}
.ft-copy{font-size:12px;color:rgba(255,255,255,.22);}
.ft-copy span{color:var(--G);}
.ft-soc{display:flex;gap:10px;}
.soa{width:36px;height:36px;border-radius:50%;border:1px solid rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.38);font-size:14px;text-decoration:none;transition:all .4s var(--E);}
.soa:hover{border-color:var(--G);color:var(--G);transform:translateY(-4px);}
@media(max-width:768px){footer{padding:60px 22px 30px;}.ft-top{flex-direction:column;}}

/* FABS */
.fab-w{position:fixed;bottom:36px;right:36px;z-index:900;display:flex;flex-direction:column;align-items:flex-end;gap:12px;}
.fpill{display:flex;align-items:center;gap:11px;padding:13px 24px;border-radius:50px;font-size:13px;font-weight:700;letter-spacing:.8px;text-decoration:none;border:none;cursor:pointer;transition:all .4s var(--E);white-space:nowrap;position:relative;overflow:hidden;}
.fpill::after{content:'';position:absolute;top:0;left:-75%;width:50%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.22),transparent);transform:skewX(-18deg);transition:left .6s var(--E);pointer-events:none;}
.fpill:hover::after{left:130%;}
.fa-a{background:linear-gradient(135deg,var(--G),var(--GL));color:var(--PD);box-shadow:0 8px 24px rgba(212,175,55,.45);}
.fa-a:hover{transform:translateY(-5px) scale(1.03);box-shadow:0 16px 36px rgba(212,175,55,.6);color:var(--PD);}
.fa-a .fic{width:30px;height:30px;border-radius:50%;background:rgba(26,34,90,.18);display:flex;align-items:center;justify-content:center;font-size:14px;}
.fa-o{background:linear-gradient(135deg,var(--P),var(--PD));color:#fff;border:1.5px solid rgba(212,175,55,.55);box-shadow:0 8px 24px rgba(48,59,151,.5);}
.fa-o:hover{transform:translateY(-5px) scale(1.03);border-color:var(--G);box-shadow:0 16px 36px rgba(48,59,151,.6);color:#fff;}
.fa-o .fic{width:30px;height:30px;border-radius:50%;background:rgba(212,175,55,.18);display:flex;align-items:center;justify-content:center;font-size:14px;color:var(--G);transition:all .4s var(--E);}
.fa-o:hover .fic{background:var(--G);color:var(--PD);}
.ftop{width:46px;height:46px;border-radius:50%;background:rgba(48,59,151,.9);border:1px solid rgba(212,175,55,.3);display:flex;align-items:center;justify-content:center;color:var(--G);font-size:16px;cursor:pointer;opacity:0;pointer-events:none;transition:all .4s var(--E);backdrop-filter:blur(8px);}
.ftop.show{opacity:1;pointer-events:auto;}
.ftop:hover{background:var(--G);color:var(--PD);transform:translateY(-5px);}
@media(max-width:500px){.fab-w{bottom:20px;right:16px;}.fpill{padding:11px 18px;font-size:12px;}}

/* REVEAL */
.rv{opacity:0;transform:translateY(34px);transition:opacity .9s var(--E),transform .9s var(--E);}
.rv.on{opacity:1;transform:translateY(0);}
.rv.d1{transition-delay:100ms;}.rv.d2{transition-delay:200ms;}.rv.d3{transition-delay:300ms;}
</style>
</head>
<body>

<!-- HEADER -->
<header id="hdr">
<nav>
  <a href="{{ route('newindex') }}" class="logo">
    <img src="{{ asset('favicon.ico') }}" alt="Dream Mulk" onerror="this.src='https://cdn-icons-png.flaticon.com/512/2111/2111307.png'"/>
    <span class="logo-name">Dream Mulk</span>
  </a>
  <ul class="nav-ul">
    <li><a href="{{ route('newindex') }}" class="{{ request()->routeIs('newindex') ? 'ac' : '' }}">Home</a></li>
    <li><a href="{{ route('property.list') }}" class="{{ request()->routeIs('property.list') ? 'ac' : '' }}">Properties</a></li>
    <li><a href="#app">Mobile App</a></li>
    <li><a href="{{ route('about-us') }}" class="{{ request()->routeIs('about-us') ? 'ac' : '' }}">About Us</a></li>
    <li><a href="{{ route('contact-us') }}" class="{{ request()->routeIs('contact-us') ? 'ac' : '' }}">Contact</a></li>
  </ul>
  <div class="nav-right">
    @php
      $user  = \Illuminate\Support\Facades\Auth::user();
      $agent = \Illuminate\Support\Facades\Auth::guard('agent')->user();
      $unreadCount = 0;
      if($user){
        $unreadCount = \DB::table('notifications')->where('user_id',$user->id)->where('is_read',false)->where(function($q){$q->whereNull('expires_at')->orWhere('expires_at','>',now());})->count();
      }
    @endphp
    @if($user || $agent)
      <a href="{{ route('user.notifications') }}" class="bell-wrap">
        <i class="far fa-bell"></i>
        @if($unreadCount > 0)<span class="bell-badge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>@endif
      </a>
      @php
        $displayName   = $user ? ($user->username ?? $user->name ?? 'User') : $agent->agent_name;
        $redirectRoute = $user ? route('user.profile') : route('agent.profile.page');
      @endphp
      <a href="{{ $redirectRoute }}" class="av-btn">{{ strtoupper(substr($displayName,0,1)) }}</a>
    @else
      <a href="{{ route('property.list') }}" class="btn-o">Browse</a>
      <a href="{{ route('login-page') }}" class="btn-s">Client Login</a>
    @endif
  </div>
  <button class="hbtn" id="ham"><i class="fas fa-bars"></i></button>
</nav>
</header>

<!-- DRAWER -->
<div class="bkdp" id="bdp"></div>
<aside class="drw" id="drw">
  <div class="drw-hd"><span>Dream Mulk</span><button class="drw-x" id="dx"><i class="fas fa-times"></i></button></div>
  <nav class="drw-nav">
    <a href="{{ route('newindex') }}">Home</a>
    <a href="{{ route('property.list') }}">Properties</a>
    <a href="#app">Mobile App</a>
    <a href="{{ route('about-us') }}">About Us</a>
    <a href="{{ route('contact-us') }}">Contact</a>
  </nav>
  <div class="drw-ft">
    <a href="{{ route('login-page') }}" class="btn-s" style="text-align:center;">Client Login</a>
    <a href="{{ route('property.list') }}" class="btn-o" style="text-align:center;">Browse Properties</a>
  </div>
</aside>

<!-- HERO -->
<section class="hero">
  <canvas id="bc"></canvas>
  <div class="vig"></div>
  <div class="hc">
    <div class="eyebrow">Premium Real Estate</div>
    <h1>Dream <span class="g">Mulk</span></h1>
    <div class="sub">Kurdistan &bull; Erbil &bull; Est. 2026</div>
    <p class="hdesc">A revolutionary platform to discover, buy, sell, and rent premium properties across Kurdistan — with zero agent fees or hidden commissions.</p>
    <div class="hbtns">
      <a href="{{ route('property.list') }}" class="hb1">Explore Properties</a>
      <a href="#app" class="hb2">Download App</a>
    </div>
  </div>
  <div class="scrl"><div class="mouse"></div><span>Scroll</span></div>
</section>

<!-- STATS -->
<div class="stats-bar">
  <div class="stats-inner">
    <div class="stat-item rv"><div class="stat-num" data-t="500" data-s="+">0+</div><div class="stat-label">Listed Properties</div></div>
    <div class="stat-div"></div>
    <div class="stat-item rv d1"><div class="stat-num" data-t="150" data-s="+">0+</div><div class="stat-label">Verified Agents</div></div>
    <div class="stat-div"></div>
    <div class="stat-item rv d2"><div class="stat-num">0%</div><div class="stat-label">Commission Fees</div></div>
    <div class="stat-div"></div>
    <div class="stat-item rv d3"><div class="stat-num" data-t="10" data-s="K+">0K+</div><div class="stat-label">Happy Clients</div></div>
  </div>
</div>

<!-- SERVICES -->
<section class="svc-sec">
  <div class="sec-wrap">
    <div class="sec-hd">
      <span class="stag rv">Our Services</span>
      <h2 class="stitle rv d1">What We <em>Offer</em></h2>
    </div>
    <div class="svc-grid">
      <a href="{{ route('property.list') }}" class="svc-card rv">
        <img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800&q=80" alt="Buy" class="svc-img">
        <div class="svc-body"><div class="svc-n">01</div><div class="svc-ico"><i class="fas fa-key"></i></div><h3 class="svc-t">Buy a Property</h3><p class="svc-p">Discover your dream home with advanced filters. Browse exclusive listings across Kurdistan with full transparency.</p><div class="svc-cta">Explore <i class="fas fa-arrow-right"></i></div></div>
      </a>
      <a href="{{ route('login-page') }}" class="svc-card rv d1">
        <img src="https://images.unsplash.com/photo-1582407947304-fd86f028f716?w=800&q=80" alt="Sell" class="svc-img">
        <div class="svc-body"><div class="svc-n">02</div><div class="svc-ico"><i class="fas fa-tags"></i></div><h3 class="svc-t">Sell a Property</h3><p class="svc-p">List your property and connect with serious buyers. Maximum visibility, competitive pricing, zero commissions.</p><div class="svc-cta">List Now <i class="fas fa-arrow-right"></i></div></div>
      </a>
      <a href="{{ route('property.list', ['type'=>'rent']) }}" class="svc-card rv d2">
        <img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&q=80" alt="Rent" class="svc-img">
        <div class="svc-body"><div class="svc-n">03</div><div class="svc-ico"><i class="fas fa-home"></i></div><h3 class="svc-t">Rent a Property</h3><p class="svc-p">Find the perfect rental that fits your lifestyle and budget. Verified listings with transparent pricing.</p><div class="svc-cta">Find Rentals <i class="fas fa-arrow-right"></i></div></div>
      </a>
    </div>
  </div>
</section>

<!-- APP -->
<section class="app-sec" id="app">
  <div class="app-g">
    <div class="rv">
      <span class="stag">Mobile Experience</span>
      <h2 class="stitle" style="margin-bottom:20px;">Your Property Journey<br>In Your <em>Pocket</em></h2>
      <p class="app-desc">The Dream Mulk app brings Kurdistan's finest real estate market to your fingertips. Search, schedule appointments, and connect with verified agents — all in one elegant experience.</p>
      <div class="app-feats">
        <div class="af"><i class="fas fa-check-circle"></i> Real-time property listings &amp; instant alerts</div>
        <div class="af"><i class="fas fa-check-circle"></i> Appointment scheduling with agents</div>
        <div class="af"><i class="fas fa-check-circle"></i> Multi-language: English, Arabic &amp; Kurdish</div>
        <div class="af"><i class="fas fa-check-circle"></i> Secure in-app messaging &amp; documents</div>
        <div class="af"><i class="fas fa-check-circle"></i> Zero commission — always free to browse</div>
      </div>
      <div class="sbtns">
        <a href="https://apps.apple.com/us/app/dream-mulk/id6756894199" target="_blank" class="sbtn"><i class="fab fa-apple"></i><div><div class="sbtn-sm">Download on the</div><div class="sbtn-nm">App Store</div></div></a>
        <a href="https://play.google.com/store/apps/details?id=com.dreammulk" target="_blank" class="sbtn"><i class="fab fa-google-play"></i><div><div class="sbtn-sm">Get it on</div><div class="sbtn-nm">Google Play</div></div></a>
      </div>
    </div>
    <div class="rv d2">
      <div class="qr-card">
        <div class="qr-brand"><div class="qr-ico"><i class="fab fa-apple"></i></div><div><div class="qr-t">Dream Mulk</div><div class="qr-s">App Store — Free Download</div></div></div>
        <div class="qr-div"></div>
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=https://apps.apple.com/us/app/dream-mulk/id6756894199&bgcolor=ffffff&color=1a225a&margin=10&format=png&qzone=1&ecc=M" alt="QR" class="qr-img" loading="lazy"/>
        <div class="qr-hint"><i class="fas fa-mobile-alt"></i><span>Point camera to scan</span></div>
        <div class="qr-div"></div>
        <a href="https://apps.apple.com/us/app/dream-mulk/id6756894199" target="_blank" class="qr-lnk"><i class="fab fa-apple"></i> Open in App Store <i class="fas fa-arrow-right"></i></a>
      </div>
    </div>
  </div>
</section>

<!-- ABOUT -->
<section class="abt-sec" id="about">
  <div class="abt-g">
    <div class="rv">
      <span class="stag">Our Story</span>
      <h2 class="stitle">The Dream Mulk<br><em>Standard</em></h2>
      <p class="abt-p">Dream Mulk was established with a singular, powerful ambition: to elevate the standard of real estate in Kurdistan. We are not merely a platform — we are the architects of your next chapter.</p>
      <p class="abt-p">In a market often defined by complexity, we serve as your beacon of clarity and sophistication. Our journey is fueled by a commitment to modern technology and timeless integrity.</p>
      <div class="qbar"><p>"Property is land, but 'Mulk' is legacy. We help you build yours."</p></div>
    </div>
    <div class="vals rv d2">
      <div class="vi"><div class="vico"><i class="fas fa-crown"></i></div><div class="vinfo"><h4>Exclusivity</h4><span>Curated Portfolio</span></div></div>
      <div class="vi"><div class="vico"><i class="fas fa-handshake"></i></div><div class="vinfo"><h4>Integrity</h4><span>Radical Transparency</span></div></div>
      <div class="vi"><div class="vico"><i class="fas fa-mobile-alt"></i></div><div class="vinfo"><h4>Technology</h4><span>Smart &amp; Modern Platform</span></div></div>
      <div class="vi"><div class="vico"><i class="fas fa-map-marked-alt"></i></div><div class="vinfo"><h4>Erbil Based</h4><span>Est. 2026 — Kurdistan</span></div></div>
    </div>
  </div>
</section>

<!-- REDIRECT -->
<section class="rdr-sec" id="contact">
  <div class="rdr-in">
    <div class="rv">
      <span class="rdr-ey">For Real Estate Offices</span>
      <h2 class="rdr-t">Grow Your Business<br>With <strong>Dream Mulk</strong></h2>
      <p class="rdr-d">Register your real estate office and reach thousands of buyers and renters across the Kurdistan Region. List properties, manage agents, and close deals — all in one platform.</p>
    </div>
    <div class="rdr-bs rv d2">
      <a href="{{ route('office.login') }}" class="rdr-b1"><i class="fas fa-building"></i> Real Estate Login</a>
      <a href="{{ route('property.list') }}" class="rdr-b2"><i class="fas fa-search"></i> Browse Without Login</a>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer class="rv">
  <div class="ft-in">
    <div class="ft-top">
      <div>
        <div class="ft-logo">
          <img src="{{ asset('favicon.ico') }}" alt="Dream Mulk" onerror="this.src='https://cdn-icons-png.flaticon.com/512/2111/2111307.png'"/>
          <span class="ft-logo-name">Dream Mulk</span>
        </div>
        <p class="ft-tag">Kurdistan's premier real estate platform. No fees. No commissions. Pure transparency.</p>
      </div>
      <div class="ft-col"><h5>Platform</h5><ul>
        <li><a href="{{ route('property.list') }}">Browse Properties</a></li>
        <li><a href="{{ route('login-page') }}">Client Login</a></li>
        <li><a href="{{ route('agent.login') }}">Agent Portal</a></li>
        <li><a href="{{ route('about-us') }}">About Us</a></li>
      </ul></div>
      <div class="ft-col"><h5>Services</h5><ul>
        <li><a href="{{ route('property.list') }}">Buy Property</a></li>
        <li><a href="{{ route('login-page') }}">Sell Property</a></li>
        <li><a href="{{ route('property.list', ['type'=>'rent']) }}">Rent Property</a></li>
        <li><a href="{{ route('agents.list') }}">Find an Agent</a></li>
      </ul></div>
      <div class="ft-col"><h5>Download App</h5><ul>
        <li><a href="https://apps.apple.com/us/app/dream-mulk/id6756894199" target="_blank"><i class="fab fa-apple"></i> App Store</a></li>
        <li><a href="https://play.google.com/store/apps/details?id=com.dreammulk" target="_blank"><i class="fab fa-google-play"></i> Google Play</a></li>
        <li><a href="{{ route('contact-us') }}">Contact Us</a></li>
      </ul></div>
    </div>
    <div class="ft-bot">
      <div class="ft-copy">© 2026 <span>Dream Mulk</span>. All rights reserved. Erbil, Kurdistan Region of Iraq.</div>
      <div class="ft-soc">
        <a href="https://www.facebook.com/share/1CGLEbK7qh/" target="_blank" rel="noopener" class="soa"><i class="fab fa-facebook-f"></i></a>
        <a href="https://www.instagram.com/dream_mulk?igsh=MWt4YXd1eTN4NW5j" target="_blank" rel="noopener" class="soa"><i class="fab fa-instagram"></i></a>
      </div>
    </div>
  </div>
</footer>

<!-- FABS -->
<div class="fab-w">
  <a href="{{ route('agent.login') }}" class="fpill fa-a"><div class="fic"><i class="fas fa-user-shield"></i></div> Agent Portal</a>
  <a href="{{ route('office.login') }}" class="fpill fa-o"><div class="fic"><i class="fas fa-building"></i></div> Real Estate Login</a>
  <div class="ftop" id="btt"><i class="fas fa-arrow-up"></i></div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js"></script>
<script>
(function(){
  /* ═══════════════════════════════════════════════
     BURJ KHALIFA WIREFRAME — DRAWS ITSELF ON LOAD
     Lines are born from bottom → top over ~4 seconds
     using LineDashedMaterial dashOffset animation
  ═══════════════════════════════════════════════ */
  const CV = document.getElementById('bc');
  const W  = () => CV.parentElement.offsetWidth;
  const H  = () => CV.parentElement.offsetHeight;

  const renderer = new THREE.WebGLRenderer({ canvas: CV, antialias: true });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  renderer.setSize(W(), H());
  renderer.setClearColor(0x06091e, 1);

  const scene  = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(52, W()/H(), 0.01, 200);
  camera.position.set(0, 1.6, 7.0);
  camera.lookAt(0, 1.3, 0);

  // colors
  const CG  = 0xd4af37;  // gold
  const CG2 = 0xe8cb6a;  // light gold
  const CB  = 0x2d3fb0;  // blue
  const CBM = 0x1a2460;  // deep blue

  // tower dimensions
  const TOT = 4.4;          // total height in scene units
  const BY  = -1.55;        // base y (ground level)
  const SPR = 0.42;         // extra spire above tower

  // 10 tiers: normalised height + half-width
  const TIERS = [
    {h:0.00,w:0.60},{h:0.12,w:0.51},{h:0.23,w:0.43},
    {h:0.36,w:0.36},{h:0.50,w:0.29},{h:0.63,w:0.22},
    {h:0.75,w:0.15},{h:0.85,w:0.09},{h:0.92,w:0.04},{h:1.00,w:0.00}
  ];
  const RINGS = [7,6,6,5,5,4,3,2,1]; // rings between tiers

  // Burj Khalifa: 3-lobe Y floor plan
  const LOBES = [Math.PI*.5, Math.PI*.5+2.094, Math.PI*.5+4.189];
  const SPIRAL = Math.PI * 0.68; // floor plan rotation base→top

  function lerp(a,b,t){return a+(b-a)*t;}
  function easeOut(t){return 1-Math.pow(1-t,3);}

  // Build one Y-lobe ring path
  function yRing(cx,cy,cz,radius,spin,segs){
    const pts=[], ARC=Math.PI*.74, S=segs||12;
    for(let l=0;l<3;l++){
      const la = LOBES[l]+spin;
      for(let s=0;s<=S;s++){
        const t=s/S, ang=la-ARC/2+t*ARC;
        const bulge=radius*.40*Math.sin(t*Math.PI);
        const r=radius+bulge;
        pts.push(new THREE.Vector3(cx+Math.cos(ang)*r, cy, cz+Math.sin(ang)*r));
      }
    }
    pts.push(pts[0].clone());
    return pts;
  }

  // All line definitions {pts,col,op,born,dur}
  const LDEFS = [];

  const ASTART = 0.5;  // delay before drawing starts
  const ATOT   = 3.8;  // total draw time base→top

  // Ground grid — appears first
  const GS=5.5, GN=16;
  for(let i=0;i<=GN;i++){
    const t=(i/GN)*GS-GS/2;
    LDEFS.push({pts:[new THREE.Vector3(t,BY,-GS/2),new THREE.Vector3(t,BY,GS/2)],  col:CBM,op:.18,born:.05,dur:.2});
    LDEFS.push({pts:[new THREE.Vector3(-GS/2,BY,t),new THREE.Vector3(GS/2,BY,t)], col:CBM,op:.18,born:.05,dur:.2});
  }

  // Collect rings
  const ALLR = [];
  for(let ti=0;ti<TIERS.length-1;ti++){
    const T0=TIERS[ti], T1=TIERS[ti+1], nR=RINGS[ti]||2;
    for(let r=0;r<=nR;r++){
      const f=r/nR, ny=lerp(T0.h,T1.h,f), w=lerp(T0.w,T1.w,f);
      const y=BY+ny*TOT, spin=ny*SPIRAL, major=(r===0);
      ALLR.push({ny,y,w,spin,major});
    }
  }
  ALLR.sort((a,b)=>a.y-b.y);

  // Floor rings
  ALLR.forEach(({ny,y,w,spin,major})=>{
    if(w<.005) return;
    const col = ny>.82?CG2:ny>.50?CG:CB;
    LDEFS.push({
      pts: yRing(0,y,0,w,spin,major?16:9),
      col, op:major?.88:.38,
      born: ASTART+ny*ATOT, dur:major?.55:.32
    });
  });

  // Vertical lobe-edge columns (3 outer + 3 inner)
  for(let l=0;l<3;l++){
    const out=[], mid=[];
    ALLR.forEach(({ny,y,w,spin})=>{
      if(w<.005) return;
      const la=LOBES[l]+spin, bulge=w*.40;
      out.push(new THREE.Vector3(Math.cos(la)*(w+bulge), y, Math.sin(la)*(w+bulge)));
      mid.push(new THREE.Vector3(Math.cos(la)*w*.55, y, Math.sin(la)*w*.55));
    });
    LDEFS.push({pts:out, col:CG,  op:.60, born:ASTART,      dur:ATOT+.6});
    LDEFS.push({pts:mid, col:CB,  op:.28, born:ASTART+.15,  dur:ATOT+.6});
  }

  // Center column
  LDEFS.push({pts:ALLR.filter(r=>r.w>.005).map(r=>new THREE.Vector3(0,r.y,0)), col:CBM, op:.22, born:ASTART+.1, dur:ATOT+.5});

  // Cross-braces at setback tiers
  TIERS.forEach(T=>{
    if(T.w<.04) return;
    const y=BY+T.h*TOT, spin=T.h*SPIRAL, born=ASTART+T.h*ATOT;
    for(let l=0;l<3;l++){
      const la=LOBES[l]+spin, ro=T.w+T.w*.40;
      LDEFS.push({pts:[new THREE.Vector3(Math.cos(la)*ro,y,Math.sin(la)*ro),new THREE.Vector3(0,y,0)], col:CB, op:.2, born, dur:.28});
    }
  });

  // Spire — last to draw
  const SB=BY+.92*TOT, ST=SB+SPR+TOT*.08;
  const spirePts=[];
  for(let i=0;i<=24;i++) spirePts.push(new THREE.Vector3(0,lerp(SB,ST,i/24),0));
  LDEFS.push({pts:spirePts, col:0xfff8b0, op:1.0, born:ASTART+ATOT*.88, dur:.45});

  // Reflection — bottom rings mirrored below ground, fading
  ALLR.filter(r=>r.ny<.36).forEach(({ny,y,w,spin})=>{
    if(w<.02) return;
    const refY=BY-(y-BY)*.5;
    if(refY<BY-1.0) return;
    const op=Math.max(0,.28-(BY-refY)*.3);
    LDEFS.push({pts:yRing(0,refY,0,w,spin,8), col:CB, op, born:ASTART+ny*ATOT+.18, dur:.32});
  });

  // Create Three.js line objects — draw-on by growing drawRange.count each frame
  // This is the ONLY reliable way to animate line drawing in Three.js
  const OBJS = LDEFS.map(ld=>{
    const nPts = ld.pts.length;
    // Flatten all points into a Float32Array
    const positions = new Float32Array(nPts * 3);
    ld.pts.forEach((p,i)=>{ positions[i*3]=p.x; positions[i*3+1]=p.y; positions[i*3+2]=p.z; });

    const geo = new THREE.BufferGeometry();
    geo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    // Start with drawRange.count = 0 → nothing visible
    geo.setDrawRange(0, 0);

    const mat = new THREE.LineBasicMaterial({
      color: ld.col, transparent: true, opacity: ld.op
    });
    const line = new THREE.Line(geo, mat);
    scene.add(line);
    return { geo, born: ld.born, dur: ld.dur, nPts };
  });

  // Atmosphere particles
  {
    const g=new THREE.BufferGeometry(), p=[];
    for(let i=0;i<750;i++) p.push((Math.random()-.5)*18,(Math.random()-.15)*9+.5,(Math.random()-.85)*12-1);
    g.setAttribute('position',new THREE.Float32BufferAttribute(p,3));
    scene.add(new THREE.Points(g,new THREE.PointsMaterial({color:0x6688cc,size:.02,transparent:true,opacity:.32})));
  }
  {
    const g=new THREE.BufferGeometry(), p=[];
    for(let i=0;i<220;i++) p.push((Math.random()-.5)*12, BY+Math.random()*.32, (Math.random()-.5)*8);
    g.setAttribute('position',new THREE.Float32BufferAttribute(p,3));
    scene.add(new THREE.Points(g,new THREE.PointsMaterial({color:CG,size:.026,transparent:true,opacity:.26})));
  }

  // Mouse parallax
  let mx=0,my=0,smx=0,smy=0;
  document.addEventListener('mousemove',e=>{mx=(e.clientX/innerWidth-.5);my=(e.clientY/innerHeight-.5);});

  // Resize
  window.addEventListener('resize',()=>{
    renderer.setSize(W(),H());
    camera.aspect=W()/H();
    camera.updateProjectionMatrix();
  });

  // Render loop
  const clk=new THREE.Clock();
  (function loop(){
    requestAnimationFrame(loop);
    const el=clk.getElapsedTime();
    smx+=(mx-smx)*.035; smy+=(my-smy)*.035;

    // Grow each line's visible point count from 0 → full
    OBJS.forEach(({geo,born,dur,nPts})=>{
      const age=el-born;
      if(age<0){ geo.setDrawRange(0,0); return; }
      const progress = easeOut(Math.min(age/dur, 1));
      // drawRange.count must be at least 2 to render, and we step by whole points
      const visible = Math.max(2, Math.floor(progress * nPts));
      geo.setDrawRange(0, visible);
    });

    // Gentle rotation + mouse
    scene.rotation.y=el*.04+smx*.20;
    scene.rotation.x=smy*.07;
    camera.position.y=1.6+Math.sin(el*.28)*.055;
    renderer.render(scene,camera);
  })();
})();
</script>

<script>
// Header + back-to-top
const hdr=document.getElementById('hdr'), btt=document.getElementById('btt');
window.addEventListener('scroll',()=>{
  hdr.classList.toggle('sc',scrollY>60);
  btt.classList.toggle('show',scrollY>300);
});
btt.addEventListener('click',()=>window.scrollTo({top:0,behavior:'smooth'}));

// Drawer
const ham=document.getElementById('ham'),drw=document.getElementById('drw'),
      bdp=document.getElementById('bdp'),dx=document.getElementById('dx');
const oD=()=>{drw.classList.add('on');bdp.classList.add('on');document.body.style.overflow='hidden';};
const cD=()=>{drw.classList.remove('on');bdp.classList.remove('on');document.body.style.overflow='';};
ham.addEventListener('click',oD); dx.addEventListener('click',cD); bdp.addEventListener('click',cD);
window.addEventListener('resize',()=>{if(innerWidth>1000)cD();});

// Stats counter
let cnt=false;
new IntersectionObserver(es=>{
  if(es[0].isIntersecting&&!cnt){
    cnt=true;
    document.querySelectorAll('.stat-num[data-t]').forEach(el=>{
      const tg=parseInt(el.dataset.t),sf=el.dataset.s||'';
      let c=0,step=Math.ceil(tg/60);
      const iv=setInterval(()=>{c=Math.min(c+step,tg);el.textContent=c+sf;if(c>=tg)clearInterval(iv);},20);
    });
  }
}).observe(document.querySelector('.stats-bar'));

// Scroll reveal
const obs=new IntersectionObserver((es,o)=>{
  es.forEach(e=>{if(e.isIntersecting){e.target.classList.add('on');o.unobserve(e.target);}});
},{threshold:.1,rootMargin:'0px 0px -30px 0px'});
document.querySelectorAll('.rv').forEach(el=>obs.observe(el));

// Smooth anchor scroll
document.querySelectorAll('a[href^="#"]').forEach(a=>{
  a.addEventListener('click',e=>{
    const h=a.getAttribute('href');
    if(h&&h.length>1){const t=document.querySelector(h);if(t){e.preventDefault();t.scrollIntoView({behavior:'smooth'});}}
  });
});
</script>
</body>
</html>
