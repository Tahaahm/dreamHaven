<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Dream Mulk - Premium Real Estate Kurdistan</title>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&family=Cinzel:wght@400;600;700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css"/>

    <style>
        *,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
        :root{
            /* Dream Mulk Color Palette */
            --primary:#303b97;
            --primary-dark:#1a225a;
            --primary-mid:#232d7a;
            --primary-deep:#0d1038;
            --gold:#d4af37;
            --gold-light:#e8cb6a;
            --gold-dark:#b5952f;
            --gold-pale:#f5e8c0;
            --white:#ffffff;
            --text-dim:rgba(255,255,255,0.65);
            --glass-dark:rgba(48,59,151,0.95);

            /* UI colors for pagination and inputs */
            --B: #303b97;
            --BD: #1a225a;
            --BL: #eef0fb;
            --mid: #52596e;
            --dim: #9aa0b8;
            --hr: #e4e6f0;
            --bg: #f4f6fa;

            /* The magic curve for buttery animations */
            --ease-out-expo: cubic-bezier(0.16, 1, 0.3, 1);
        }
        html{scroll-behavior:smooth;}
        body{font-family:'Poppins',sans-serif;background:var(--primary-deep);color:var(--white);overflow-x:hidden;}

        /* SCROLL REVEAL ANIMATIONS */
        .reveal-up {
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 0.8s var(--ease-out-expo), transform 0.8s var(--ease-out-expo);
            will-change: opacity, transform;
        }
        .reveal-up.is-visible { opacity: 1; transform: translateY(0); }
        .delay-100 { transition-delay: 100ms; }
        .delay-200 { transition-delay: 200ms; }
        .delay-300 { transition-delay: 300ms; }

        /* HEADER */
        .unique-header{position:fixed;top:0;left:0;right:0;height:100px;z-index:1100;padding:0 40px;transition:all 0.5s var(--ease-out-expo);background:linear-gradient(to bottom,rgba(0,0,0,0.6) 0%,transparent 100%);display:flex;align-items:center;}
        .unique-header.scrolled{background:var(--glass-dark);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);height:80px;box-shadow:0 10px 30px rgba(0,0,0,0.15);border-bottom:1px solid rgba(255,255,255,0.08);}
        .unique-nav{max-width:1400px;width:100%;margin:0 auto;display:flex;height:100%;align-items:center;justify-content:space-between;}

        /* LOGO STYLING - MADE MUCH BIGGER */
        .unique-nav-logo{font-family:'Playfair Display',serif;font-size:28px;color:var(--white);font-weight:700;display:flex;align-items:center;gap:16px;letter-spacing:0.5px;text-decoration:none;}
        .brand-logo-img { width: 68px; height: 68px; border-radius: 50%; border: 3px solid var(--gold); object-fit: contain; background: #fff; transition:transform 0.5s var(--ease-out-expo), width 0.5s var(--ease-out-expo), height 0.5s var(--ease-out-expo); box-shadow: 0 4px 15px rgba(212,175,55,0.25); }
        .unique-nav-logo:hover .brand-logo-img{transform:scale(1.08) rotate(5deg);}

        /* Shrink logo slightly when user scrolls down so it doesn't block the screen */
        .unique-header.scrolled .brand-logo-img { width: 48px; height: 48px; border-width: 2px; }
        .unique-header.scrolled .unique-nav-logo { font-size: 24px; gap: 12px; }

        .unique-nav-items{display:flex;align-items:center;gap:40px;}
        .nav-list{display:flex;gap:35px;align-items:center;list-style:none;}
        .unique-nav-link{color:rgba(255,255,255,0.85);font-size:15px;font-weight:400;position:relative;padding:5px 0;letter-spacing:0.5px;text-decoration:none;transition:color 0.3s;}
        .unique-nav-link:hover,.unique-nav-link.active{color:var(--gold);}
        .unique-nav-link::after{content:'';position:absolute;width:0;height:2px;bottom:0;left:0;background-color:var(--gold);transition:width 0.4s var(--ease-out-expo);}
        .unique-nav-link:hover::after,.unique-nav-link.active::after{width:100%;}
        .btn-outline{padding:10px 26px;border:1px solid var(--gold);background:transparent;border-radius:50px;cursor:pointer;color:var(--gold);font-weight:500;font-size:14px;letter-spacing:1px;text-transform:uppercase;transition:all 0.4s var(--ease-out-expo);text-decoration:none;display:inline-block;}
        .btn-outline:hover{background:var(--gold);color:var(--primary-dark);box-shadow:0 0 20px rgba(212,175,55,0.4);transform:translateY(-2px);}
        .btn-solid{padding:10px 26px;border:1px solid var(--gold);background:var(--gold);border-radius:50px;cursor:pointer;color:var(--primary-dark);font-weight:700;font-size:14px;letter-spacing:1px;text-transform:uppercase;transition:all 0.4s var(--ease-out-expo);text-decoration:none;display:inline-block;}
        .btn-solid:hover{background:var(--gold-light);transform:translateY(-2px);box-shadow:0 8px 25px rgba(212,175,55,0.45);}
        .menu-toggle{display:none;background:transparent;border:none;font-size:28px;color:var(--gold);cursor:pointer;}

        /* MOBILE DRAWER */
        .nav-backdrop{position:fixed;inset:0;background:rgba(0,0,0,0.6);opacity:0;pointer-events:none;transition:opacity .4s var(--ease-out-expo);z-index:1090;backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);}
        .nav-backdrop.show{opacity:1;pointer-events:auto;}
        .nav-drawer{position:fixed;top:0;right:-100%;height:100vh;width:min(380px,85%);background:var(--primary);z-index:1100;padding:40px 30px;display:flex;flex-direction:column;gap:20px;transition:right .5s var(--ease-out-expo);box-shadow:-10px 0 30px rgba(0,0,0,0.5);}
        .nav-drawer::before{content:'';position:absolute;top:0;bottom:0;left:0;width:4px;background:var(--gold);}
        .nav-drawer.open{right:0;}
        .drawer-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;}
        .drawer-title{font-family:'Playfair Display',serif;font-size:24px;color:var(--white);}
        .drawer-links a{font-size:18px;padding:15px 0;color:rgba(255,255,255,0.8);border-bottom:1px solid rgba(255,255,255,0.05);display:block;transition:all 0.3s;text-decoration:none;}
        .drawer-links a:hover,.drawer-links a.active{color:var(--gold);padding-left:12px;}
        @media(max-width:992px){.unique-nav-items,.hdr-btns{display:none!important;}.menu-toggle{display:block;}.unique-header{padding:0 20px;height:80px;}.unique-header.scrolled{height:70px;} .brand-logo-img{width: 50px; height: 50px;}}

        /* HERO & THE NEW BACKGROUND REVEAL ANIMATION */
        .hero{position:relative;height:100vh;min-height:700px;display:flex;align-items:center;justify-content:center;overflow:hidden;background-color:var(--primary-deep);}

        /* The Vanta background container now scales and fades in on load */
        #hero-3d {
            position: absolute; inset: 0; z-index: 0;
            opacity: 0;
            animation: bgReveal 2s var(--ease-out-expo) forwards;
        }
        @keyframes bgReveal {
            0% { opacity: 0; transform: scale(1.1); filter: blur(4px); }
            100% { opacity: 1; transform: scale(1); filter: blur(0px); }
        }

        /* Dark overlay so text is perfectly readable */
        .hero-overlay { position:absolute; inset:0; background:radial-gradient(circle at center, transparent 0%, rgba(13,16,56,0.85) 100%); z-index:1; pointer-events:none; }

        /* Reduced the delays since there is no loading screen anymore */
        .hero-content{position:relative;z-index:10;text-align:center;max-width:860px;padding:0 24px; pointer-events:none;}
        .hero-eyebrow{display:inline-flex;align-items:center;gap:14px;font-size:11px;font-weight:600;letter-spacing:4px;text-transform:uppercase;color:var(--gold);margin-bottom:22px;opacity:0;animation:slideUp 1s var(--ease-out-expo) 0.4s forwards;}
        .hero-eyebrow::before,.hero-eyebrow::after{content:'';width:40px;height:1px;background:var(--gold);opacity:0.5;}
        .hero-title{font-family:'Playfair Display',serif;font-size:clamp(58px,9.5vw,112px);font-weight:800;line-height:0.95;letter-spacing:-2px;color:var(--white);margin-bottom:14px;opacity:0;animation:slideUp 1s var(--ease-out-expo) 0.6s forwards;}
        .hero-title span{color:var(--gold);}
        .hero-sub-line{font-family:'Cinzel',serif;font-size:clamp(11px,1.8vw,15px);letter-spacing:6px;text-transform:uppercase;color:rgba(255,255,255,0.38);margin-bottom:30px;opacity:0;animation:slideUp 1s var(--ease-out-expo) 0.8s forwards;}
        .hero-desc{font-size:17px;line-height:1.8;color:var(--text-dim);max-width:580px;margin:0 auto 48px;font-weight:300;opacity:0;animation:slideUp 1s var(--ease-out-expo) 1s forwards;}

        /* Enable clicking on buttons */
        .hero-actions{display:flex;align-items:center;justify-content:center;gap:18px;flex-wrap:wrap;opacity:0;animation:slideUp 1s var(--ease-out-expo) 1.2s forwards; pointer-events:auto;}

        .hero-btn-primary{padding:16px 44px;background:var(--gold);color:var(--primary-dark);font-weight:700;font-size:13px;letter-spacing:2px;text-transform:uppercase;border:none;border-radius:50px;cursor:pointer;text-decoration:none;transition:all 0.4s var(--ease-out-expo);box-shadow:0 4px 20px rgba(212,175,55,0.35);}
        .hero-btn-primary:hover{background:var(--gold-light);transform:translateY(-4px);box-shadow:0 14px 36px rgba(212,175,55,0.5);}
        .hero-btn-secondary{padding:15px 44px;border:1px solid rgba(255,255,255,0.3);color:var(--white);font-weight:500;font-size:13px;letter-spacing:2px;text-transform:uppercase;border-radius:50px;text-decoration:none;transition:all 0.4s var(--ease-out-expo);}
        .hero-btn-secondary:hover{border-color:var(--gold);color:var(--gold);transform:translateY(-4px); background:rgba(212,175,55,0.05);}

        @keyframes slideUp{from{opacity:0;transform:translateY(40px);}to{opacity:1;transform:translateY(0);}}
        .scroll-cue{position:absolute;bottom:38px;left:50%;transform:translateX(-50%);display:flex;flex-direction:column;align-items:center;gap:6px;opacity:0;animation:fadeIn 1s ease 2s forwards; z-index:10;}
        .scroll-cue span{font-size:10px;letter-spacing:3px;text-transform:uppercase;color:rgba(255,255,255,0.3);}
        .scroll-line{width:1px;height:50px;background:linear-gradient(to bottom,rgba(212,175,55,0.7),transparent);animation:pulseLine 2s ease-in-out infinite;}
        @keyframes pulseLine{0%,100%{height:50px;opacity:0.6;}50%{height:66px;opacity:1;}}
        @keyframes fadeIn{to{opacity:1;}}

        /* STATS */
        .stats-bar{background:rgba(212,175,55,0.07);border-top:1px solid rgba(212,175,55,0.18);border-bottom:1px solid rgba(212,175,55,0.18);padding:34px 60px;}
        .stats-inner{max-width:1050px;margin:0 auto;display:flex;justify-content:space-between;align-items:center;gap:24px;}
        .stat-item{text-align:center;}
        .stat-num{font-family:'Playfair Display',serif;font-size:46px;font-weight:700;color:var(--gold);line-height:1;}
        .stat-label{font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--text-dim);margin-top:6px;}
        .stat-div{width:1px;height:50px;background:rgba(212,175,55,0.22);}
        @media(max-width:768px){.stats-inner{flex-wrap:wrap;justify-content:center;gap:28px;}.stat-div{display:none;}}

        /* SERVICES (Fully Clickable Cards) */
        .services-section{padding:120px 60px;background:linear-gradient(180deg,var(--primary-deep) 0%,var(--primary-dark) 100%);}
        .sec-inner{max-width:1300px;margin:0 auto;}
        .sec-label{font-size:11px;letter-spacing:4px;text-transform:uppercase;color:var(--gold);margin-bottom:14px;}
        .sec-title{font-family:'Playfair Display',serif;font-size:clamp(36px,5vw,58px);font-weight:700;color:var(--white);line-height:1.15;margin-bottom:58px;}
        .sec-title em{font-style:italic;color:var(--gold);}
        .services-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:3px;}

        .svc-card{position:relative;height:520px;overflow:hidden;cursor:pointer;display:block;text-decoration:none;color:inherit;}
        .svc-card::before{content:'';position:absolute;inset:0;background:linear-gradient(to top,rgba(13,16,56,0.97) 0%,rgba(26,34,90,0.5) 50%,rgba(48,59,151,0.18) 100%);transition:all 0.6s var(--ease-out-expo);z-index:1;}
        .svc-card:hover::before{background:linear-gradient(to top,rgba(13,16,56,0.98) 0%,rgba(26,34,90,0.7) 60%,rgba(48,59,151,0.3) 100%);}
        .svc-img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;filter:saturate(0.7);transition:transform 0.8s var(--ease-out-expo),filter 0.6s ease;}
        .svc-card:hover .svc-img{transform:scale(1.08);filter:saturate(0.4);}
        .svc-content{position:absolute;bottom:0;left:0;right:0;padding:44px 36px;z-index:2;transition:transform 0.6s var(--ease-out-expo);}
        .svc-card:hover .svc-content{transform:translateY(-15px);}
        .svc-number{font-family:'Playfair Display',serif;font-size:64px;font-weight:700;color:rgba(212,175,55,0.18);line-height:1;margin-bottom:6px;transition:color 0.4s ease;}
        .svc-card:hover .svc-number{color:rgba(212,175,55,0.38);}
        .svc-icon{width:50px;height:50px;border:1px solid rgba(212,175,55,0.4);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--gold);font-size:19px;margin-bottom:16px;transition:all 0.5s var(--ease-out-expo);}
        .svc-card:hover .svc-icon{background:var(--gold);color:var(--primary-dark);border-color:var(--gold);transform:scale(1.1);}
        .svc-title{font-family:'Playfair Display',serif;font-size:30px;font-weight:700;color:var(--white);margin-bottom:10px;transition:color 0.3s;}
        .svc-card:hover .svc-title{color:var(--gold);}
        .svc-text{font-size:14px;line-height:1.7;color:var(--text-dim);max-height:0;overflow:hidden;opacity:0;transition:all 0.6s var(--ease-out-expo);margin-bottom:0;}
        .svc-card:hover .svc-text{max-height:120px;opacity:1;margin-bottom:16px;}
        .svc-link-div{display:inline-flex;align-items:center;gap:8px;font-size:12px;letter-spacing:2px;text-transform:uppercase;color:var(--gold);font-weight:600;transition:gap 0.4s var(--ease-out-expo);}
        .svc-card:hover .svc-link-div{gap:14px;}
        @media(max-width:1024px){.services-grid{grid-template-columns:1fr 1fr;}.svc-card:last-child{grid-column:span 2;}}
        @media(max-width:768px){.services-section{padding:80px 24px;}.services-grid{grid-template-columns:1fr;}.svc-card:last-child{grid-column:span 1;}}

        /* APP SECTION */
        .app-section{padding:120px 60px;background:linear-gradient(135deg,var(--primary-mid) 0%,var(--primary) 50%,var(--primary-dark) 100%);position:relative;overflow:hidden;}
        .app-section::before{content:'';position:absolute;top:-200px;right:-200px;width:700px;height:700px;border-radius:50%;border:1px solid rgba(212,175,55,0.07);}
        .app-section::after{content:'';position:absolute;bottom:-150px;left:-150px;width:500px;height:500px;border-radius:50%;border:1px solid rgba(212,175,55,0.05);}
        .app-inner{max-width:1300px;margin:0 auto;position:relative;z-index:1;}
        .app-grid{display:grid;grid-template-columns:1fr 1fr;gap:80px;align-items:center;}
        .app-desc{font-size:16px;line-height:1.8;color:var(--text-dim);margin-bottom:34px;max-width:480px;font-weight:300;}
        .app-features{display:flex;flex-direction:column;gap:13px;margin-bottom:42px;}
        .app-feature{display:flex;align-items:center;gap:13px;font-size:14px;color:rgba(255,255,255,0.78);}
        .app-feature i{color:var(--gold);font-size:15px;width:18px;}
        .store-buttons{display:flex;gap:14px;flex-wrap:wrap;}
        .store-btn{display:flex;align-items:center;gap:12px;padding:13px 22px;border:1px solid rgba(212,175,55,0.35);border-radius:12px;text-decoration:none;background:rgba(212,175,55,0.07);transition:all 0.4s var(--ease-out-expo);}
        .store-btn:hover{background:rgba(212,175,55,0.16);border-color:var(--gold);transform:translateY(-4px);box-shadow:0 10px 28px rgba(212,175,55,0.2);}
        .store-btn i{font-size:28px;color:var(--gold);}
        .store-btn-small{font-size:10px;letter-spacing:1px;text-transform:uppercase;color:var(--text-dim);}
        .store-btn-name{font-size:16px;font-weight:700;color:var(--white);font-family:'Playfair Display',serif;}

        /* QR CARD */
        .app-qr-area{display:flex;flex-direction:column;align-items:center;gap:20px;}
        .qr-card{background:var(--white);border-radius:28px;padding:28px 24px;display:flex;flex-direction:column;align-items:center;gap:18px;box-shadow:0 0 0 1.5px rgba(212,175,55,0.5),0 32px 72px rgba(0,0,0,0.55),0 8px 20px rgba(48,59,151,0.2);max-width:272px;width:100%;position:relative;overflow:hidden;transition:transform 0.6s var(--ease-out-expo);}
        .qr-card:hover { transform: translateY(-8px) scale(1.02); }
        .qr-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--primary) 0%,var(--gold) 50%,var(--primary) 100%);}
        .qr-card::after{content:'';position:absolute;bottom:-40px;right:-40px;width:120px;height:120px;border-radius:50%;background:radial-gradient(circle,rgba(48,59,151,0.06) 0%,transparent 70%);pointer-events:none;}
        .qr-header{width:100%;}
        .qr-brand-row{display:flex;align-items:center;gap:12px;}
        .qr-brand-icon{width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,var(--primary-dark),var(--primary));display:flex;align-items:center;justify-content:center;color:var(--white);font-size:20px;flex-shrink:0;}
        .qr-header-text{font-family:'Playfair Display',serif;font-size:16px;font-weight:700;color:var(--primary-dark);line-height:1.2;}
        .qr-header-sub{font-size:9.5px;color:#999;letter-spacing:1.5px;text-transform:uppercase;margin-top:2px;}
        .qr-divider{width:100%;height:1px;background:linear-gradient(90deg,transparent,rgba(48,59,151,0.12),transparent);}
        .qr-img-area{display:flex;align-items:center;justify-content:center;padding:4px;}
        .qr-real{width:190px;height:190px;display:block;border-radius:12px;border:3px solid rgba(48,59,151,0.08);box-shadow:0 4px 16px rgba(48,59,151,0.1);}
        .qr-scan-instruction{display:flex;align-items:center;gap:8px;font-size:11px;color:#777;letter-spacing:1px;text-transform:uppercase;font-weight:500;}
        .qr-scan-icon{width:26px;height:26px;border-radius:50%;background:rgba(48,59,151,0.08);display:flex;align-items:center;justify-content:center;color:var(--primary);font-size:13px;}
        .qr-store-link{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:12px 16px;background:linear-gradient(135deg,var(--primary-dark),var(--primary));color:var(--white);border-radius:14px;font-size:13px;font-weight:600;letter-spacing:0.5px;text-decoration:none;transition:all 0.4s var(--ease-out-expo);}
        .qr-store-link:hover{transform:translateY(-2px);box-shadow:0 8px 22px rgba(48,59,151,0.45);color:var(--white);}
        .qr-store-link .fab{font-size:16px;}
        .qr-arrow{font-size:11px;margin-left:2px;opacity:0.7;transition:transform 0.4s var(--ease-out-expo);}
        .qr-store-link:hover .qr-arrow{transform:translateX(5px);opacity:1;}
        @media(max-width:1024px){.app-grid{grid-template-columns:1fr;gap:60px;}}
        @media(max-width:768px){.app-section{padding:80px 24px;}}

        /* ABOUT */
        .about-section{padding:120px 60px;background:linear-gradient(180deg,var(--primary-dark) 0%,var(--primary-deep) 100%);}
        .about-grid{display:grid;grid-template-columns:1.4fr 1fr;gap:90px;align-items:start;}
        .about-p{font-size:16px;line-height:1.9;color:var(--text-dim);margin-bottom:20px;font-weight:300;}
        .quote-bar{margin-top:32px;padding:24px 28px;border-left:3px solid var(--gold);background:rgba(212,175,55,0.06);border-radius:0 8px 8px 0;}
        .quote-bar p{font-family:'Playfair Display',serif;font-size:20px;font-style:italic;color:var(--gold-pale);line-height:1.55;}
        .values-stack{display:flex;flex-direction:column;gap:2px;}
        .value-row{display:flex;align-items:center;gap:22px;padding:24px 26px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);transition:all 0.5s var(--ease-out-expo);}
        .value-row:hover{background:rgba(212,175,55,0.06);border-color:rgba(212,175,55,0.22);transform:translateX(10px);}
        .value-icon{width:54px;height:54px;background:rgba(212,175,55,0.1);border:1px solid rgba(212,175,55,0.28);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--gold);font-size:21px;flex-shrink:0;transition:all 0.4s var(--ease-out-expo);}
        .value-row:hover .value-icon{background:var(--gold);color:var(--primary-dark);transform:scale(1.1) rotate(10deg);}
        .value-info h4{font-family:'Playfair Display',serif;font-size:22px;font-weight:700;color:var(--white);margin-bottom:3px;}
        .value-info span{font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:var(--text-dim);}
        @media(max-width:1024px){.about-grid{grid-template-columns:1fr;gap:60px;}}
        @media(max-width:768px){.about-section{padding:80px 24px;}}

        /* REDIRECT / CTA */
        .redirect-section{padding:100px 60px;background:var(--primary);position:relative;overflow:hidden;}
        .redirect-section::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 60% 80% at 85% 50%,rgba(212,175,55,0.1) 0%,transparent 70%),radial-gradient(ellipse 40% 60% at 15% 50%,rgba(13,16,56,0.55) 0%,transparent 70%);}
        .redirect-section::after{content:'';position:absolute;top:-120px;right:-120px;width:520px;height:520px;border-radius:50%;border:1px solid rgba(212,175,55,0.1);}
        .redirect-inner{max-width:1150px;margin:0 auto;position:relative;z-index:1;display:grid;grid-template-columns:1fr auto;gap:70px;align-items:center;}
        .redirect-eyebrow{font-size:11px;letter-spacing:4px;text-transform:uppercase;color:var(--gold);margin-bottom:14px;}
        .redirect-title{font-family:'Playfair Display',serif;font-size:clamp(32px,4vw,52px);font-weight:700;color:var(--white);line-height:1.2;margin-bottom:16px;}
        .redirect-title strong{color:var(--gold);}
        .redirect-desc{font-size:15px;color:var(--text-dim);line-height:1.75;max-width:520px;font-weight:300;}
        .redirect-actions{display:flex;flex-direction:column;gap:13px;align-items:stretch;min-width:250px;}
        .redirect-btn-main{display:flex;align-items:center;justify-content:center;gap:10px;padding:17px 36px;background:var(--gold);color:var(--primary-dark);font-weight:700;font-size:13px;letter-spacing:2px;text-transform:uppercase;border-radius:50px;text-decoration:none;transition:all 0.4s var(--ease-out-expo);box-shadow:0 4px 20px rgba(212,175,55,0.4);}
        .redirect-btn-main:hover{background:var(--gold-light);transform:translateY(-4px);box-shadow:0 14px 36px rgba(212,175,55,0.5);}
        .redirect-btn-secondary{display:flex;align-items:center;justify-content:center;gap:10px;padding:16px 36px;border:1px solid rgba(255,255,255,0.25);color:rgba(255,255,255,0.85);font-size:13px;letter-spacing:1.5px;text-transform:uppercase;border-radius:50px;text-decoration:none;transition:all 0.4s var(--ease-out-expo);}
        .redirect-btn-secondary:hover{border-color:var(--gold);color:var(--gold);transform:translateY(-4px); background:rgba(212,175,55,0.05);}
        @media(max-width:900px){.redirect-inner{grid-template-columns:1fr;}.redirect-actions{flex-direction:row;flex-wrap:wrap;}}
        @media(max-width:768px){.redirect-section{padding:80px 24px;}}

        /* FOOTER */
        .footer{background:var(--primary-dark);border-top:1px solid rgba(212,175,55,0.12);padding:60px 60px 32px;}
        .footer-inner{max-width:1300px;margin:0 auto;}
        .footer-top{display:flex;justify-content:space-between;align-items:flex-start;padding-bottom:40px;border-bottom:1px solid rgba(255,255,255,0.05);margin-bottom:30px;gap:40px;flex-wrap:wrap;}
        .footer-logo-row{display:flex;align-items:center;gap:12px;margin-bottom:12px;}
        .footer-logo-name{font-family:'Playfair Display',serif;font-size:22px;font-weight:700;color:var(--white);}
        .footer-tagline{font-size:13px;color:var(--text-dim);line-height:1.65;max-width:255px;}
        .footer-nav-group h5{font-size:10px;letter-spacing:3px;text-transform:uppercase;color:var(--gold);margin-bottom:16px;}
        .footer-nav-group ul{list-style:none;display:flex;flex-direction:column;gap:10px;}
        .footer-nav-group a{font-size:14px;color:var(--text-dim);text-decoration:none;transition:color 0.3s;}
        .footer-nav-group a:hover{color:var(--gold);}
        .footer-bottom{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;}
        .footer-copy{font-size:12px;color:rgba(255,255,255,0.28);}
        .footer-copy span{color:var(--gold);}
        .footer-socials{display:flex;gap:12px;}
        .social-link{width:36px;height:36px;border-radius:50%;border:1px solid rgba(255,255,255,0.1);display:flex;align-items:center;justify-content:center;color:var(--text-dim);font-size:14px;text-decoration:none;transition:all 0.4s var(--ease-out-expo);}
        .social-link:hover{border-color:var(--gold);color:var(--gold);transform:translateY(-4px) scale(1.1);}
        @media(max-width:768px){.footer{padding:60px 24px 32px;}.footer-top{flex-direction:column;}}

        /* DUAL FAB */
        .fab-container { position: fixed; bottom: 36px; right: 36px; z-index: 900; display: flex; flex-direction: column; align-items: flex-end; gap: 12px; }
        .fab-pill { display: flex; align-items: center; gap: 11px; padding: 13px 24px; border-radius: 50px; font-family: 'Poppins', sans-serif; font-size: 13px; font-weight: 700; letter-spacing: 0.8px; text-decoration: none; cursor: pointer; border: none; transition: transform 0.4s var(--ease-out-expo), box-shadow 0.4s ease, background 0.3s; white-space: nowrap; position: relative; overflow: hidden; }
        .fab-pill::after { content: ''; position: absolute; top: 0; left: -75%; width: 50%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.22), transparent); transform: skewX(-18deg); transition: left 0.6s var(--ease-out-expo); pointer-events: none; }
        .fab-pill:hover::after { left: 130%; }
        .fab-agent { background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%); color: var(--primary-dark); box-shadow: 0 8px 24px rgba(212,175,55,0.45), 0 2px 6px rgba(0,0,0,0.15), inset 0 1px 0 rgba(255,255,255,0.25); }
        .fab-agent:hover { transform: translateY(-5px) scale(1.03); box-shadow: 0 16px 36px rgba(212,175,55,0.6), 0 4px 10px rgba(0,0,0,0.18), inset 0 1px 0 rgba(255,255,255,0.25); color: var(--primary-dark); }
        .fab-agent .fab-icon { width: 30px; height: 30px; background: rgba(26,34,90,0.18); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }
        .fab-client { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: var(--white); border: 1.5px solid rgba(212,175,55,0.55); box-shadow: 0 8px 24px rgba(48,59,151,0.5), 0 2px 6px rgba(0,0,0,0.2), inset 0 1px 0 rgba(255,255,255,0.08); }
        .fab-client:hover { transform: translateY(-5px) scale(1.03); background: linear-gradient(135deg, var(--primary-light, #3d4ab5) 0%, var(--primary) 100%); border-color: var(--gold); box-shadow: 0 16px 36px rgba(48,59,151,0.6), 0 0 0 3px rgba(212,175,55,0.18), 0 4px 10px rgba(0,0,0,0.22), inset 0 1px 0 rgba(255,255,255,0.1); color: var(--white); }
        .fab-client .fab-icon { width: 30px; height: 30px; background: rgba(212,175,55,0.18); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; color: var(--gold); flex-shrink: 0; transition: background 0.4s var(--ease-out-expo), color 0.4s; }
        .fab-client:hover .fab-icon { background: var(--gold); color: var(--primary-dark); }
        .fab-top { width: 46px; height: 46px; background: rgba(48,59,151,0.92); border: 1px solid rgba(212,175,55,0.35); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--gold); font-size: 16px; cursor: pointer; opacity: 0; pointer-events: none; transition: all 0.4s var(--ease-out-expo); backdrop-filter: blur(8px); }
        .fab-top.show { opacity: 1; pointer-events: auto; }
        .fab-top:hover { background: var(--gold); color: var(--primary-dark); transform: translateY(-5px) scale(1.05); }
        .fab-pill .fab-tooltip { position: absolute; right: calc(100% + 12px); top: 50%; transform: translateY(-50%); background: rgba(13,16,56,0.95); color: var(--gold); font-size: 11px; letter-spacing: 1.5px; text-transform: uppercase; font-weight: 600; padding: 6px 12px; border-radius: 6px; white-space: nowrap; border: 1px solid rgba(212,175,55,0.2); opacity: 0; pointer-events: none; transition: opacity 0.3s ease, transform 0.4s var(--ease-out-expo); transform: translateY(-50%) translateX(10px); }
        .fab-pill:hover .fab-tooltip { opacity: 1; transform: translateY(-50%) translateX(0); }
        @media (max-width: 500px) { .fab-container { bottom: 20px; right: 16px; gap: 10px; } .fab-pill { padding: 11px 18px; font-size: 12px; } .fab-pill .fab-tooltip { display: none; } }

        /* ==========================================
           2. GLOBAL PAGINATION FIX (PERFECT SQUARES)
        ========================================== */
        .pgn { clear: both; padding-top: 50px; display: flex; flex-direction: column; align-items: center; gap: 16px; }
        .pgn-info { font-size: 14px; color: var(--mid); font-weight: 500; }
        .pgn-info strong { color: var(--B); font-weight: 700; }
        .pgn nav > div.sm\:hidden { display: none !important; }
        .pgn nav > div:first-of-type { display: none !important; }
        .pgn nav > div:last-of-type, .pgn nav ul.pagination { display: inline-flex !important; align-items: center; justify-content: center; gap: 10px !important; margin: 0 !important; padding: 0 !important; border: none !important; box-shadow: none !important; }

        .pgn nav a.relative, .pgn nav span.relative, .pgn nav li > a, .pgn nav li > span, .pgn nav button.relative {
            width: 44px !important; height: 44px !important; min-width: 44px !important; max-width: 44px !important;
            padding: 0 !important; margin: 0 !important; display: flex !important; align-items: center !important; justify-content: center !important;
            flex-shrink: 0 !important; border-radius: 12px !important; background: #fff !important; border: 1.5px solid var(--hr) !important;
            color: var(--mid) !important; font-family: 'Outfit', sans-serif !important; font-size: 15px !important; font-weight: 600 !important;
            text-decoration: none !important; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        .pgn nav a.relative:hover, .pgn nav li > a:hover {
            border-color: var(--B) !important; color: var(--B) !important; background: var(--BL) !important;
            transform: translateY(-3px) !important; box-shadow: 0 8px 20px rgba(48,59,151,0.15) !important; z-index: 2 !important;
        }
        .pgn nav span[aria-current="page"] > span, .pgn nav span.active, .pgn nav li.active > span {
            background: linear-gradient(135deg, var(--B), var(--BD)) !important; color: #fff !important; border-color: transparent !important;
            box-shadow: 0 8px 24px rgba(48,59,151,0.3) !important; transform: scale(1.05) !important; z-index: 3 !important;
        }
        .pgn nav span[aria-disabled="true"] > span, .pgn nav span.disabled, .pgn nav li.disabled > span {
            background: var(--bg) !important; color: var(--dim) !important; border-color: var(--hr) !important;
            box-shadow: none !important; cursor: not-allowed !important; transform: none !important;
        }
        .pgn nav svg { width: 18px !important; height: 18px !important; display: block !important; margin: 0 !important; }
    </style>
</head>
<body>

<header class="unique-header" id="navbar">
    <nav class="unique-nav">
        <a href="{{ route('newindex') }}" class="unique-nav-logo">
            <img src="{{ asset('favicon.ico') }}" alt="Dream Mulk" class="brand-logo-img" onerror="this.src='https://cdn-icons-png.flaticon.com/512/2111/2111307.png'" />
            <span>Dream Mulk</span>
        </a>

        <div class="unique-nav-items">
            <ul class="nav-list">
                <li><a class="unique-nav-link {{ request()->routeIs('newindex') ? 'active' : '' }}" href="{{ route('newindex') }}">Home</a></li>
                <li><a class="unique-nav-link {{ request()->routeIs('property.list') ? 'active' : '' }}" href="{{ route('property.list') }}">Properties</a></li>
                <li><a class="unique-nav-link" href="#app">Mobile App</a></li>
                <li><a class="unique-nav-link {{ request()->routeIs('about-us') ? 'active' : '' }}" href="{{ route('about-us') }}">About Us</a></li>
                <li><a class="unique-nav-link {{ request()->routeIs('contact-us') ? 'active' : '' }}" href="{{ route('contact-us') }}">Contact</a></li>
            </ul>
        </div>
        <div style="display:flex;align-items:center;gap:14px;" class="hdr-btns">
            @php
                $user = \Illuminate\Support\Facades\Auth::user();
                $agent = \Illuminate\Support\Facades\Auth::guard('agent')->user();
                $unreadCount = 0;
                if ($user) {
                    $unreadCount = \DB::table('notifications')->where('user_id',$user->id)->where('is_read',false)->where(function($q){ $q->whereNull('expires_at')->orWhere('expires_at','>',now()); })->count();
                }
            @endphp
            @if($user || $agent)
                <a href="{{ route('user.notifications') }}" style="color:var(--gold);font-size:20px;position:relative;text-decoration:none;">
                    <i class="far fa-bell"></i>
                    @if($unreadCount > 0)
                        <span style="position:absolute;top:-4px;right:-6px;background:#e74c3c;color:#fff;font-size:9px;font-weight:700;padding:2px 4px;border-radius:5px;">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                    @endif
                </a>
                @php $displayName = $user ? ($user->username ?? $user->name ?? 'User') : $agent->agent_name; $redirectRoute = $user ? route('user.profile') : route('agent.profile.page'); @endphp
                <a href="{{ $redirectRoute }}" style="width:38px;height:38px;border-radius:50%;background:var(--gold);color:var(--primary-dark);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:15px;text-decoration:none;transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">{{ strtoupper(substr($displayName,0,1)) }}</a>
            @else
                <a href="{{ route('property.list') }}" class="btn-outline">Browse</a>
                <a href="{{ route('login-page') }}" class="btn-solid">Client Login</a>
            @endif
        </div>
        <button id="hamburger" class="menu-toggle"><i class="uil uil-bars"></i></button>
    </nav>
</header>

<div id="nav-backdrop" class="nav-backdrop"></div>
<aside id="mobile-drawer" class="nav-drawer">
    <div class="drawer-header">
        <div class="drawer-title">Dream Mulk</div>
        <button id="drawer-close" style="background:transparent;border:none;color:var(--gold);font-size:24px;cursor:pointer;"><i class="uil uil-times"></i></button>
    </div>
    <nav class="drawer-links">
        <a href="{{ route('newindex') }}" data-close>Home</a>
        <a href="{{ route('property.list') }}" data-close>Properties</a>
        <a href="#app" data-close>Mobile App</a>
        <a href="{{ route('about-us') }}" data-close>About Us</a>
        <a href="{{ route('contact-us') }}" data-close>Contact</a>
    </nav>
    <div style="margin-top:auto;display:flex;flex-direction:column;gap:12px;">
        <a href="{{ route('login-page') }}" class="btn-solid" style="text-align:center;">Client Login</a>
        <a href="{{ route('property.list') }}" class="btn-outline" style="text-align:center;">Browse Properties</a>
    </div>
</aside>

<section class="hero">
    <div id="hero-3d"></div>

    <div class="hero-overlay"></div>

    <div class="hero-content">
        <div class="hero-eyebrow">Premium Real Estate</div>
        <h1 class="hero-title">Dream <span>Mulk</span></h1>
        <div class="hero-sub-line">Kurdistan &bull; Erbil &bull; Est. 2026</div>
        <p class="hero-desc">A revolutionary platform to discover, buy, sell, and rent premium properties across Kurdistan — with zero agent fees or hidden commissions.</p>
        <div class="hero-actions">
            <a href="{{ route('property.list') }}" class="hero-btn-primary">Explore Properties</a>
            <a href="#app" class="hero-btn-secondary">Download App</a>
        </div>
    </div>
    <div class="scroll-cue">
        <span>Scroll</span>
        <div class="scroll-line"></div>
    </div>
</section>

<div class="stats-bar">
    <div class="stats-inner">
        <div class="stat-item reveal-up"><div class="stat-num" data-target="500" data-suffix="+">500+</div><div class="stat-label">Listed Properties</div></div>
        <div class="stat-div"></div>
        <div class="stat-item reveal-up delay-100"><div class="stat-num" data-target="150" data-suffix="+">150+</div><div class="stat-label">Verified Agents</div></div>
        <div class="stat-div"></div>
        <div class="stat-item reveal-up delay-200"><div class="stat-num">0%</div><div class="stat-label">Commission Fees</div></div>
        <div class="stat-div"></div>
        <div class="stat-item reveal-up delay-300"><div class="stat-num" data-target="10" data-suffix="K+">10K+</div><div class="stat-label">Happy Clients</div></div>
    </div>
</div>

<section class="services-section">
    <div class="sec-inner">
        <div style="text-align:center;margin-bottom:68px;">
            <div class="sec-label reveal-up">Our Services</div>
            <h2 class="sec-title reveal-up delay-100">What We <em>Offer</em></h2>
        </div>
        <div class="services-grid">

            <a href="{{ route('property.list') }}" class="svc-card reveal-up">
                <img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800&q=80" alt="Buy" class="svc-img">
                <div class="svc-content">
                    <div class="svc-number">01</div>
                    <div class="svc-icon"><i class="fas fa-key"></i></div>
                    <h3 class="svc-title">Buy a Property</h3>
                    <p class="svc-text">Discover your dream home with advanced filters. Browse exclusive listings across Kurdistan with full transparency.</p>
                    <div class="svc-link-div">Explore <i class="fas fa-arrow-right"></i></div>
                </div>
            </a>

            <a href="{{ route('login-page') }}" class="svc-card reveal-up delay-100">
                <img src="https://images.unsplash.com/photo-1582407947304-fd86f028f716?w=800&q=80" alt="Sell" class="svc-img">
                <div class="svc-content">
                    <div class="svc-number">02</div>
                    <div class="svc-icon"><i class="fas fa-tags"></i></div>
                    <h3 class="svc-title">Sell a Property</h3>
                    <p class="svc-text">List your property and connect with serious buyers. Maximum visibility, competitive pricing, zero commissions.</p>
                    <div class="svc-link-div">List Now <i class="fas fa-arrow-right"></i></div>
                </div>
            </a>

            <a href="{{ route('property.list', ['type'=>'rent']) }}" class="svc-card reveal-up delay-200">
                <img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&q=80" alt="Rent" class="svc-img">
                <div class="svc-content">
                    <div class="svc-number">03</div>
                    <div class="svc-icon"><i class="fas fa-home"></i></div>
                    <h3 class="svc-title">Rent a Property</h3>
                    <p class="svc-text">Find the perfect rental that fits your lifestyle and budget. Verified listings with transparent pricing.</p>
                    <div class="svc-link-div">Find Rentals <i class="fas fa-arrow-right"></i></div>
                </div>
            </a>

        </div>
    </div>
</section>

<section class="app-section" id="app">
    <div class="app-inner">
        <div class="app-grid">
            <div class="reveal-up">
                <div class="sec-label">Mobile Experience</div>
                <h2 class="sec-title" style="margin-bottom:20px;">Your Property Journey<br>In Your <em>Pocket</em></h2>
                <p class="app-desc">The Dream Mulk app brings Kurdistan's finest real estate market to your fingertips. Search, schedule appointments, and connect with verified agents — all in one elegant experience.</p>
                <div class="app-features">
                    <div class="app-feature"><i class="fas fa-check-circle"></i> Real-time property listings & instant alerts</div>
                    <div class="app-feature"><i class="fas fa-check-circle"></i> Appointment scheduling with agents</div>
                    <div class="app-feature"><i class="fas fa-check-circle"></i> Multi-language: English, Arabic & Kurdish</div>
                    <div class="app-feature"><i class="fas fa-check-circle"></i> Secure in-app messaging & documents</div>
                    <div class="app-feature"><i class="fas fa-check-circle"></i> Zero commission — always free to browse</div>
                </div>
                <div class="store-buttons">
                    <a href="https://apps.apple.com/us/app/dream-mulk/id6756894199" target="_blank" class="store-btn">
                        <i class="fab fa-apple"></i>
                        <div>
                            <div class="store-btn-small">Download on the</div>
                            <div class="store-btn-name">App Store</div>
                        </div>
                    </a>
                    <a href="https://play.google.com/store/apps/details?id=com.dreammulk" target="_blank" class="store-btn">
                        <i class="fab fa-google-play"></i>
                        <div>
                            <div class="store-btn-small">Get it on</div>
                            <div class="store-btn-name">Google Play</div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="app-qr-area reveal-up delay-200">
                <div class="qr-card">
                    <div class="qr-header">
                        <div class="qr-brand-row">
                            <div class="qr-brand-icon"><i class="fab fa-apple"></i></div>
                            <div>
                                <div class="qr-header-text">Dream Mulk</div>
                                <div class="qr-header-sub">App Store — Free Download</div>
                            </div>
                        </div>
                    </div>
                    <div class="qr-divider"></div>
                    <div class="qr-img-area">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=https://apps.apple.com/us/app/dream-mulk/id6756894199&bgcolor=ffffff&color=1a225a&margin=10&format=png&qzone=1&ecc=M" alt="Scan to download Dream Mulk" class="qr-real" loading="lazy" />
                    </div>
                    <div class="qr-scan-instruction">
                        <div class="qr-scan-icon"><i class="fas fa-mobile-alt"></i></div>
                        <span>Point your camera to scan</span>
                    </div>
                    <div class="qr-divider"></div>
                    <a href="https://apps.apple.com/us/app/dream-mulk/id6756894199" target="_blank" class="qr-store-link">
                        <i class="fab fa-apple"></i>
                        <span>Open in App Store</span>
                        <i class="fas fa-arrow-right qr-arrow"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="about-section" id="about">
    <div class="sec-inner">
        <div class="about-grid">
            <div class="reveal-up">
                <div class="sec-label">Our Story</div>
                <h2 class="sec-title">The Dream Mulk<br><em>Standard</em></h2>
                <p class="about-p">Dream Mulk was established with a singular, powerful ambition: to elevate the standard of real estate in Kurdistan. We are not merely a platform — we are the architects of your next chapter.</p>
                <p class="about-p">In a market often defined by complexity, we serve as your beacon of clarity and sophistication. Our journey is fueled by a commitment to modern technology and timeless integrity.</p>
                <div class="quote-bar"><p>"Property is land, but 'Mulk' is legacy. We help you build yours."</p></div>
            </div>
            <div class="values-stack reveal-up delay-200">
                <div class="value-row"><div class="value-icon"><i class="fas fa-crown"></i></div><div class="value-info"><h4>Exclusivity</h4><span>Curated Portfolio</span></div></div>
                <div class="value-row"><div class="value-icon"><i class="fas fa-handshake"></i></div><div class="value-info"><h4>Integrity</h4><span>Radical Transparency</span></div></div>
                <div class="value-row"><div class="value-icon"><i class="fas fa-mobile-alt"></i></div><div class="value-info"><h4>Technology</h4><span>Smart & Modern Platform</span></div></div>
                <div class="value-row"><div class="value-icon"><i class="fas fa-map-marked-alt"></i></div><div class="value-info"><h4>Erbil Based</h4><span>Est. 2026 — Kurdistan</span></div></div>
            </div>
        </div>
    </div>
</section>

<section class="redirect-section" id="contact">
    <div class="redirect-inner">
        <div class="reveal-up">
            <div class="redirect-eyebrow">For Real Estate Offices</div>
            <h2 class="redirect-title">Grow Your Business<br>With <strong>Dream Mulk</strong></h2>
            <p class="redirect-desc">Register your real estate office and reach thousands of buyers and renters across the Kurdistan Region. List properties, manage agents, and close deals — all in one platform.</p>
        </div>
        <div class="redirect-actions reveal-up delay-200">
            <a href="{{ route('office.login') }}" class="redirect-btn-main">
                <i class="fas fa-building"></i> Real Estate Login
            </a>
            <a href="{{ route('property.list') }}" class="redirect-btn-secondary">
                <i class="fas fa-search"></i> Browse Without Login
            </a>
        </div>
    </div>
</section>

<footer class="footer reveal-up">
    <div class="footer-inner">
        <div class="footer-top">
            <div>
                <div class="footer-logo-row">
                    <img src="{{ asset('favicon.ico') }}" alt="Dream Mulk" class="brand-logo-img" onerror="this.src='https://cdn-icons-png.flaticon.com/512/2111/2111307.png'" style="width: 32px; height:32px; border:none; box-shadow:none;"/>
                    <span class="footer-logo-name">Dream Mulk</span>
                </div>
                <p class="footer-tagline">Kurdistan's premier real estate platform. No fees. No commissions. Pure transparency.</p>
            </div>
            <div class="footer-nav-group">
                <h5>Platform</h5>
                <ul>
                    <li><a href="{{ route('property.list') }}">Browse Properties</a></li>
                    <li><a href="{{ route('login-page') }}">Client Login</a></li>
                    <li><a href="{{ route('agent.login') }}">Agent Portal</a></li>
                    <li><a href="{{ route('about-us') }}">About Us</a></li>
                </ul>
            </div>
            <div class="footer-nav-group">
                <h5>Services</h5>
                <ul>
                    <li><a href="{{ route('property.list') }}">Buy Property</a></li>
                    <li><a href="{{ route('login-page') }}">Sell Property</a></li>
                    <li><a href="{{ route('property.list', ['type'=>'rent']) }}">Rent Property</a></li>
                    <li><a href="{{ route('agents.list') }}">Find an Agent</a></li>
                </ul>
            </div>
            <div class="footer-nav-group">
                <h5>Download App</h5>
                <ul>
                    <li><a href="https://apps.apple.com/us/app/dream-mulk/id6756894199" target="_blank"><i class="fab fa-apple"></i> App Store</a></li>
                    <li><a href="https://play.google.com/store/apps/details?id=com.dreammulk" target="_blank"><i class="fab fa-google-play"></i> Google Play</a></li>
                    <li><a href="{{ route('contact-us') }}">Contact Us</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="footer-copy">© 2026 <span>Dream Mulk</span>. All rights reserved. Erbil, Kurdistan Region of Iraq.</div>
            <div class="footer-socials">
                <a href="https://www.facebook.com/share/1CGLEbK7qh/" target="_blank" rel="noopener" class="social-link"><i class="fab fa-facebook-f"></i></a>
                <a href="https://www.instagram.com/dream_mulk?igsh=MWt4YXd1eTN4NW5j" target="_blank" rel="noopener" class="social-link"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
    </div>
</footer>

<div class="fab-container">
    <a href="{{ route('agent.login') }}" class="fab-pill fab-agent">
        <div class="fab-icon"><i class="fas fa-user-shield"></i></div>
        <span>Agent Portal</span>
        <div class="fab-tooltip">Agent Login</div>
    </a>
    <a href="{{ route('office.login') }}" class="fab-pill fab-client">
        <div class="fab-icon"><i class="fas fa-building"></i></div>
        <span>Real Estate Login</span>
        <div class="fab-tooltip">Real Estate Office</div>
    </a>
    <div class="fab-top" id="backToTop"><i class="fas fa-arrow-up"></i></div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.net.min.js"></script>

<script>
    // 1. Initialize the 3D Background
    document.addEventListener("DOMContentLoaded", function() {
        if(typeof VANTA !== 'undefined') {
            VANTA.NET({
                el: "#hero-3d",           // Targets our hero section
                mouseControls: true,      // Reacts to mouse movement
                touchControls: true,      // Reacts to mobile swiping
                gyroControls: false,
                minHeight: 200.00,
                minWidth: 200.00,
                scale: 1.00,
                scaleMobile: 1.00,
                color: 0xd4af37,          // Dream Mulk Gold
                backgroundColor: 0x0d1038,// Deep Navy Blue
                points: 12.00,            // Density of the 3D nodes
                maxDistance: 22.00,       // Connection distance
                spacing: 18.00,           // Spread of the geometry
                showDots: true            // Shows the intersection points
            });
        }
    });

    // 2. Header scroll & Back to Top logic
    const navbar = document.getElementById('navbar');
    const backToTop = document.getElementById('backToTop');
    window.addEventListener('scroll', () => {
        navbar.classList.toggle('scrolled', window.scrollY > 60);
        backToTop.classList.toggle('show', window.scrollY > 300);
    });
    backToTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

    // 3. Mobile drawer logic
    (function(){
        const hamburger = document.getElementById('hamburger');
        const drawer = document.getElementById('mobile-drawer');
        const backdrop = document.getElementById('nav-backdrop');
        const closeBtn = document.getElementById('drawer-close');
        const open = () => { drawer.classList.add('open'); backdrop.classList.add('show'); document.body.style.overflow = 'hidden'; };
        const close = () => { drawer.classList.remove('open'); backdrop.classList.remove('show'); document.body.style.overflow = ''; };
        hamburger.addEventListener('click', open);
        closeBtn.addEventListener('click', close);
        backdrop.addEventListener('click', close);
        drawer.querySelectorAll('[data-close]').forEach(el => el.addEventListener('click', close));
        window.addEventListener('resize', () => { if (window.innerWidth > 992) close(); });
    })();

    // 4. Stats counter animation
    const statsBar = document.querySelector('.stats-bar');
    let counted = false;
    new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && !counted) {
            counted = true;
            document.querySelectorAll('.stat-num[data-target]').forEach(el => {
                const target = parseInt(el.dataset.target);
                const suffix = el.dataset.suffix || '';
                let c = 0;
                const step = Math.ceil(target / 55);
                const t = setInterval(() => {
                    c = Math.min(c + step, target);
                    el.textContent = c + suffix;
                    if (c >= target) clearInterval(t);
                }, 22);
            });
        }
    }).observe(statsBar);

    // 5. Smooth Scroll Reveal Animation Observer
    const revealElements = document.querySelectorAll('.reveal-up');
    const revealObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target); // Only animate once
            }
        });
    }, {
        root: null,
        threshold: 0.15, // Trigger when 15% of element is visible
        rootMargin: "0px 0px -50px 0px"
    });

    revealElements.forEach(el => revealObserver.observe(el));

    // 6. Anchor smooth scroll for internal links
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', e => {
            const t = document.querySelector(a.getAttribute('href'));
            if (t) { e.preventDefault(); t.scrollIntoView({ behavior: 'smooth' }); }
        });
    });
</script>

</body>
</html>
