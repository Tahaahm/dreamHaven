
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Dream Mulk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Poppins:wght@300;400;500&family=Noto+Sans+Arabic:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root{--primary:#303b97;--primary-dark:#1a225a;--gold:#d4af37;--gold-light:#f3e5ab;--text-dark:#1f1f1f;--text-gray:#666;--white:#fff;}
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Poppins',sans-serif;background-color:#f0f2f5;min-height:100vh;display:flex;flex-direction:column;overflow-x:hidden;}
        body.rtl{font-family:'Noto Sans Arabic',sans-serif;direction:rtl;}
        body.rtl .icon-box{margin-right:0;margin-left:20px;}
        body.rtl .contact-item::before{left:auto;right:0;}
        body.rtl .card-visual{background:var(--primary);}
        body.rtl .card-visual::after{right:auto;left:-50px;}
        body.rtl .social-row{justify-content:flex-start;}

        /* Lang switcher */
        .lang-bar{position:fixed;top:20px;right:20px;z-index:999;display:flex;gap:4px;background:rgba(255,255,255,.15);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.2);border-radius:50px;padding:4px;}
        body.rtl .lang-bar{right:auto;left:20px;}
        .lbtn{padding:6px 14px;border-radius:50px;border:none;background:transparent;color:#fff;font-size:12px;font-weight:700;cursor:pointer;transition:all .25s;font-family:'Poppins',sans-serif;}
        .lbtn.active{background:var(--gold);color:var(--primary-dark);}
        .lbtn:hover:not(.active){background:rgba(255,255,255,.2);}

        .bg-canvas{position:fixed;top:0;left:0;width:100%;height:100%;background:linear-gradient(135deg,var(--primary) 0%,#0f143c 100%);z-index:-1;}
        .bg-canvas::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 15% 50%,rgba(212,175,55,.08) 0%,transparent 25%),radial-gradient(circle at 85% 30%,rgba(255,255,255,.05) 0%,transparent 25%);pointer-events:none;}
        .main-wrapper{flex:1;display:flex;align-items:center;justify-content:center;padding:40px 20px;}
        .contact-card{display:flex;width:100%;max-width:1100px;background:rgba(255,255,255,.96);backdrop-filter:blur(20px);border-radius:24px;box-shadow:0 25px 50px -12px rgba(0,0,0,.5);overflow:hidden;min-height:600px;animation:slideUp 1s cubic-bezier(.16,1,.3,1);}
        .card-visual{width:40%;background:var(--primary);position:relative;padding:60px;display:flex;flex-direction:column;justify-content:space-between;overflow:hidden;color:#fff;}
        .card-visual::after{content:'';position:absolute;bottom:-50px;right:-50px;width:200px;height:200px;border:2px solid rgba(212,175,55,.3);border-radius:50%;}
        .visual-header h2{font-family:'Playfair Display',serif;font-size:3rem;line-height:1.1;margin-bottom:20px;}
        body.rtl .visual-header h2{font-family:'Noto Sans Arabic',sans-serif;font-size:2.4rem;}
        .visual-header p{font-size:1.05rem;opacity:.8;font-weight:300;line-height:1.6;}
        body.rtl .visual-header p{font-family:'Noto Sans Arabic',sans-serif;}
        .social-row{display:flex;gap:15px;margin-top:20px;}
        .social-label{font-size:.9rem;text-transform:uppercase;letter-spacing:1px;color:var(--gold);}
        body.rtl .social-label{letter-spacing:0;}
        .social-btn{width:45px;height:45px;border:1px solid rgba(255,255,255,.2);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;transition:all .3s ease;text-decoration:none;font-size:1.2rem;}
        .social-btn:hover{background:var(--gold);border-color:var(--gold);color:var(--primary);transform:translateY(-3px);}
        .card-content{width:60%;padding:60px;display:flex;flex-direction:column;justify-content:center;}
        .info-grid{display:grid;grid-template-columns:1fr;gap:30px;}
        .contact-item{display:flex;align-items:flex-start;padding:25px;border-radius:16px;background:#fff;border:1px solid rgba(0,0,0,.04);transition:all .4s cubic-bezier(.16,1,.3,1);cursor:pointer;position:relative;overflow:hidden;}
        .contact-item::before{content:'';position:absolute;left:0;top:0;bottom:0;width:4px;background:var(--gold);transform:scaleY(0);transition:transform .3s ease;transform-origin:bottom;}
        .contact-item:hover{transform:translateY(-5px);box-shadow:0 15px 30px rgba(48,59,151,.1);border-color:transparent;}
        .contact-item:hover::before{transform:scaleY(1);}
        .icon-box{width:50px;height:50px;background:rgba(48,59,151,.08);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:var(--primary);margin-right:20px;flex-shrink:0;transition:all .3s ease;}
        .contact-item:hover .icon-box{background:var(--primary);color:var(--gold);}
        .text-box h3{font-family:'Playfair Display',serif;font-size:1.2rem;color:var(--primary);margin-bottom:5px;}
        body.rtl .text-box h3{font-family:'Noto Sans Arabic',sans-serif;}
        .text-box p,.text-box a{font-size:.95rem;color:var(--text-gray);text-decoration:none;transition:color .2s;display:block;margin-bottom:4px;}
        body.rtl .text-box p,body.rtl .text-box a{font-family:'Noto Sans Arabic',sans-serif;}
        .text-box a:hover{color:var(--primary);font-weight:500;}
        .response-note{font-size:.8rem!important;margin-top:4px;color:#999!important;}
        .footer-tiny{position:fixed;bottom:20px;width:100%;text-align:center;color:rgba(255,255,255,.4);font-size:.8rem;letter-spacing:1px;z-index:0;}
        @keyframes slideUp{from{opacity:0;transform:translateY(40px)}to{opacity:1;transform:translateY(0)}}
        @media(max-width:900px){.contact-card{flex-direction:column;min-height:auto;}.card-visual,.card-content{width:100%;padding:40px;}.visual-header h2{font-size:2.5rem;}}
    </style>
</head>
<body>
@php $navbarStyle = 'navbar-dark'; @endphp
@include('navbar')

<div class="lang-bar">
    <button class="lbtn" data-lang="ku">کو</button>
    <button class="lbtn" data-lang="en">EN</button>
    <button class="lbtn" data-lang="ar">ع</button>
</div>

<div class="bg-canvas"></div>
<div class="main-wrapper">
    <div class="contact-card">
        <div class="card-visual">
            <div class="visual-header">
                <h2 data-i18n="heroTitle"></h2>
                <br>
                <p data-i18n="heroDesc"></p>
            </div>
            <div class="visual-footer">
                <p class="social-label" data-i18n="socialLabel"></p>
                <div class="social-row">
                    <a href="https://www.facebook.com/share/1EErL7Mihd/" target="_blank" class="social-btn"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://www.instagram.com/dream_mulk?igsh=d2h2ZGM3bHdmaHRo&utm_source=qr" target="_blank" class="social-btn"><i class="fab fa-instagram"></i></a>
                    <a href="https://vt.tiktok.com/ZSaMYV1qt/" target="_blank" class="social-btn"><i class="fab fa-tiktok"></i></a>
                    <a href="https://wa.me/9647501911315" target="_blank" class="social-btn"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
        </div>
        <div class="card-content">
            <div class="info-grid">
                <div class="contact-item" onclick="window.location.href='mailto:info@dreammulk.com'">
                    <div class="icon-box"><i class="fas fa-envelope-open-text"></i></div>
                    <div class="text-box">
                        <h3 data-i18n="emailTitle"></h3>
                        <a href="mailto:info@dreammulk.com">info@dreammulk.com</a>
                        <p class="response-note" data-i18n="emailNote"></p>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="icon-box"><i class="fas fa-phone-alt"></i></div>
                    <div class="text-box">
                        <h3 data-i18n="phoneTitle"></h3>
                        <a href="tel:9647501911315">+964 750 191 1315</a>
                        <a href="tel:9647517812988">+964 751 781 2988</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="footer-tiny">&copy; 2026 DREAM MULK REAL ESTATE GROUP</div>

<script>
const T={
    ku:{dir:'rtl',
        heroTitle:'میراتەکەت بنیادبنێ.',
        heroDesc:'Dream Mulk شوێنێکە کە داهاتووکەت ناونیشانی دەدات. پەیوەندیمان پێوە بکە بۆ وەرگرتنی خزمەتگوزاری تایبەت.',
        socialLabel:'بمدۆزەرەوە',
        emailTitle:'ئیمەیل',
        emailNote:'وەڵام لە ناو ٢ کاتژمێردا',
        phoneTitle:'مۆبایل',
    },
    en:{dir:'ltr',
        heroTitle:"Let's Build Your Legacy.",
        heroDesc:'Dream Mulk is where your future finds its address. Reach out to our team for the exclusive service you deserve.',
        socialLabel:'Connect with us',
        emailTitle:'Electronic Mail',
        emailNote:'Response within 2 hours',
        phoneTitle:'Private Line',
    },
    ar:{dir:'rtl',
        heroTitle:'ابنِ إرثك معنا.',
        heroDesc:'Dream Mulk هو المكان الذي يجد فيه مستقبلك عنوانه. تواصل مع فريقنا للحصول على الخدمة المتميزة التي تستحقها.',
        socialLabel:'تواصل معنا',
        emailTitle:'البريد الإلكتروني',
        emailNote:'الرد خلال ساعتين',
        phoneTitle:'الخط الخاص',
    }
};
function setLang(lang){
    const L=T[lang];if(!L)return;
    document.documentElement.dir=L.dir;
    document.body.classList.remove('rtl','lang-ku','lang-en','lang-ar');
    document.body.classList.add('lang-'+lang);
    if(L.dir==='rtl')document.body.classList.add('rtl');
    document.querySelectorAll('[data-i18n]').forEach(el=>{const k=el.getAttribute('data-i18n');if(L[k]!==undefined)el.textContent=L[k];});
    document.querySelectorAll('.lbtn').forEach(b=>b.classList.toggle('active',b.getAttribute('data-lang')===lang));
    localStorage.setItem('dm_lang',lang);
}
document.querySelectorAll('.lbtn').forEach(b=>b.addEventListener('click',()=>setLang(b.getAttribute('data-lang'))));
setLang(localStorage.getItem('dm_lang')||'ku');
</script>
</body>
</html>
