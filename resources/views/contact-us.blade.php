<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Dream Mulk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #303b97;       /* Dream Mulk Blue */
            --primary-dark: #1a225a;
            --gold: #d4af37;          /* Luxury Gold */
            --gold-light: #f3e5ab;
            --text-dark: #1f1f1f;
            --text-gray: #666666;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f2f5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* --- Background styling --- */
        .bg-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary) 0%, #0f143c 100%);
            z-index: -1;
        }

        /* Subtle geometric overlay for texture */
        .bg-canvas::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image:
                radial-gradient(circle at 15% 50%, rgba(212, 175, 55, 0.08) 0%, transparent 25%),
                radial-gradient(circle at 85% 30%, rgba(255, 255, 255, 0.05) 0%, transparent 25%);
            pointer-events: none;
        }

        .main-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        /* --- The Glass Panel (Main Container) --- */
        .contact-card {
            display: flex;
            width: 100%;
            max-width: 1100px;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            min-height: 600px;
            animation: slideUp 1s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* --- Left Side: The "Invitation" --- */
        .card-visual {
            width: 40%;
            background: var(--primary);
            position: relative;
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            color: var(--white);
        }

        /* Decorative Gold Circle */
        .card-visual::after {
            content: '';
            position: absolute;
            bottom: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            border: 2px solid rgba(212, 175, 55, 0.3);
            border-radius: 50%;
        }

        .visual-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            line-height: 1.1;
            margin-bottom: 20px;
        }

        .visual-header p {
            font-size: 1.05rem;
            opacity: 0.8;
            font-weight: 300;
            line-height: 1.6;
        }

        .visual-footer {
            z-index: 2;
        }

        .social-row {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .social-btn {
            width: 45px;
            height: 45px;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            transition: all 0.3s ease;
            text-decoration: none;
            font-size: 1.2rem;
        }

        .social-btn:hover {
            background: var(--gold);
            border-color: var(--gold);
            color: var(--primary);
            transform: translateY(-3px);
        }

        /* --- Right Side: Contact Details --- */
        .card-content {
            width: 60%;
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
        }

        /* --- The Contact Item --- */
        .contact-item {
            display: flex;
            align-items: flex-start;
            padding: 25px;
            border-radius: 16px;
            background: #fff;
            border: 1px solid rgba(0,0,0,0.04);
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        /* Hover effect: Gold line appears on left */
        .contact-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--gold);
            transform: scaleY(0);
            transition: transform 0.3s ease;
            transform-origin: bottom;
        }

        .contact-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(48, 59, 151, 0.1);
            border-color: transparent;
        }

        .contact-item:hover::before {
            transform: scaleY(1);
        }

        .icon-box {
            width: 50px;
            height: 50px;
            background: rgba(48, 59, 151, 0.08); /* Very light blue */
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: var(--primary);
            margin-right: 20px;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }

        .contact-item:hover .icon-box {
            background: var(--primary);
            color: var(--gold);
        }

        .text-box h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .text-box p, .text-box a {
            font-size: 0.95rem;
            color: var(--text-gray);
            text-decoration: none;
            transition: color 0.2s;
            display: block;
            margin-bottom: 4px;
        }

        .text-box a:hover {
            color: var(--primary);
            font-weight: 500;
        }

        /* --- Footer/Copyright --- */
        .footer-tiny {
            position: fixed;
            bottom: 20px;
            width: 100%;
            text-align: center;
            color: rgba(255,255,255,0.4);
            font-size: 0.8rem;
            letter-spacing: 1px;
            z-index: 0;
        }

        /* --- Animation Keyframes --- */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* --- Responsive --- */
        @media (max-width: 900px) {
            .contact-card {
                flex-direction: column;
                min-height: auto;
            }
            .card-visual, .card-content {
                width: 100%;
                padding: 40px;
            }
            .card-visual {
                padding-bottom: 60px;
            }
            .visual-header h2 { font-size: 2.5rem; }
        }
    </style>
</head>
<body>

    <div class="bg-canvas"></div>

    <div class="main-wrapper">
        <div class="contact-card">

            <div class="card-visual">
                <div class="visual-header">
                    <h2>Let’s Build <br>Your Legacy.</h2>
                    <br>
                    <p>Dream Mulk is where your future finds its address. Reach out to our team for the exclusive service you deserve.</p>
                </div>

                <div class="visual-footer">
                    <p style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; color: var(--gold);">Connect with us</p>
                    <div class="social-row">
                        <a href="https://www.facebook.com/share/1EErL7Mihd/" target="_blank" class="social-btn" title="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://www.instagram.com/dream_mulk?igsh=d2h2ZGM3bHdmaHRo&utm_source=qr" target="_blank" class="social-btn" title="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://vt.tiktok.com/ZSaMYV1qt/" target="_blank" class="social-btn" title="TikTok">
                            <i class="fab fa-tiktok"></i>
                        </a>
                        <a href="https://wa.me/9647501911315" target="_blank" class="social-btn" title="WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-content">
                <div class="info-grid">

                    <div class="contact-item" onclick="window.location.href='mailto:info@dreammulk.com'">
                        <div class="icon-box">
                            <i class="fas fa-envelope-open-text"></i>
                        </div>
                        <div class="text-box">
                            <h3>Electronic Mail</h3>
                            <a href="mailto:info@dreammulk.com">info@dreammulk.com</a>
                            <p style="font-size: 0.8rem; margin-top: 4px; color: #999;">Response within 2 hours</p>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="icon-box">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div class="text-box">
                            <h3>Private Line</h3>
                            <a href="tel:9647501911315">+964 750 191 1315</a>
                            <a href="tel:9647517812988">+964 751 781 2988</a>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="icon-box">
                            <i class="fas fa-map-marked-alt"></i>
                        </div>
                        <div class="text-box">
                            <h3>Headquarters</h3>
                            <p>Dream Tower, Floor 12</p>
                            <p>Erbil, Kurdistan Region, Iraq</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="footer-tiny">
        &copy; 2026 DREAM MULK REAL ESTATE GROUP
    </div>

</body>
</html>
