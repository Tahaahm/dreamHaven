<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Dream Mulk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            /* Your specific requested color */
            --primary: #303b97;
            --primary-dark: #202660;
            --accent: #d4af37; /* Gold for luxury contrast */
            --text-dark: #1a1a1a;
            --text-light: #555555;
            --bg-light: #f4f6fa;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            overflow-x: hidden;
            line-height: 1.7;
        }

        /* --- Animations --- */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes floatShape {
            0% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(20px, 20px) rotate(5deg); }
            100% { transform: translate(0, 0) rotate(0deg); }
        }

        /* --- Hero Section --- */
        .hero-section {
            position: relative;
            height: 65vh; /* Slightly shorter to emphasize the content overlap */
            background: linear-gradient(135deg, var(--primary) 0%, #1a2166 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            overflow: hidden;
            padding: 0 20px;
        }

        /* Abstract Background Elements */
        .hero-circle {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(45deg, rgba(255,255,255,0.1), rgba(255,255,255,0));
            animation: floatShape 15s infinite ease-in-out;
            z-index: 1;
        }
        .c1 { width: 500px; height: 500px; top: -200px; left: -100px; }
        .c2 { width: 300px; height: 300px; bottom: -50px; right: -50px; animation-delay: 2s; }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 900px;
            animation: fadeInUp 1s ease-out;
        }

        .hero-subtitle {
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: 3px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 15px;
            display: block;
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: 4rem;
            color: var(--white);
            margin-bottom: 20px;
            line-height: 1.1;
        }

        .hero-desc {
            color: rgba(255, 255, 255, 0.85);
            font-size: 1.2rem;
            max-width: 650px;
            margin: 0 auto;
            font-weight: 300;
        }

        /* --- Main Content Overlay --- */
        .main-container {
            max-width: 1100px;
            margin: -120px auto 0; /* Creates the overlapping effect */
            position: relative;
            z-index: 10;
            padding: 0 20px 80px;
        }

        /* --- The Narrative Card (Replaces Student Story) --- */
        .glass-card {
            background: rgba(255, 255, 255, 0.98);
            padding: 70px;
            border-radius: 20px;
            box-shadow: 0 30px 60px rgba(48, 59, 151, 0.15); /* Shadow matches your blue */
            margin-bottom: 60px;
            animation: fadeInUp 1s ease-out 0.3s forwards;
            opacity: 0;
            position: relative;
            overflow: hidden;
        }

        /* Decorative top accent line */
        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
        }

        .narrative-grid {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 60px;
            align-items: center;
        }

        .narrative-text h2 {
            font-family: 'Playfair Display', serif;
            color: var(--primary);
            font-size: 2.5rem;
            margin-bottom: 25px;
            line-height: 1.2;
        }

        .narrative-text p {
            color: var(--text-light);
            font-size: 1.05rem;
            margin-bottom: 20px;
            text-align: justify;
        }

        .quote-box {
            background-color: #f8f9fc;
            border-left: 4px solid var(--accent);
            padding: 20px 25px;
            margin-top: 30px;
            font-style: italic;
            color: var(--primary);
            font-weight: 500;
        }

        /* Right Side Details */
        .details-list {
            list-style: none;
        }

        .detail-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 30px;
        }

        .detail-icon {
            flex-shrink: 0;
            width: 50px;
            height: 50px;
            background: rgba(48, 59, 151, 0.1); /* Light version of your color */
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 20px;
            margin-right: 20px;
        }

        .detail-content h4 {
            font-size: 1.1rem;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .detail-content p {
            font-size: 0.9rem;
            color: var(--text-light);
            line-height: 1.5;
        }

        /* --- Values Section (Replaces Team) --- */
        .values-section {
            margin-top: 80px;
            text-align: center;
        }

        .values-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            color: var(--primary);
            margin-bottom: 50px;
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }

        .value-card {
            background: var(--white);
            padding: 40px 30px;
            border-radius: 15px;
            transition: all 0.4s ease;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            border: 1px solid rgba(0,0,0,0.02);
        }

        .value-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(48, 59, 151, 0.15);
            border-bottom: 3px solid var(--accent);
        }

        .value-icon-lg {
            font-size: 3rem;
            margin-bottom: 25px;
            background: -webkit-linear-gradient(var(--primary), #667eea);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .value-card h3 {
            font-size: 1.3rem;
            color: var(--text-dark);
            margin-bottom: 15px;
        }

        .value-card p {
            font-size: 0.95rem;
            color: var(--text-light);
        }

        /* --- Footer Info --- */
        .footer-badge {
            margin-top: 60px;
            text-align: center;
            opacity: 0.6;
            font-size: 0.9rem;
            letter-spacing: 1px;
            color: var(--primary);
        }

        /* --- Responsive --- */
        @media screen and (max-width: 900px) {
            .narrative-grid { grid-template-columns: 1fr; }
            .hero-title { font-size: 3rem; }
            .glass-card { padding: 40px; }
        }

        @media screen and (max-width: 600px) {
            .values-grid { grid-template-columns: 1fr; }
            .hero-section { height: 60vh; }
            .main-container { margin-top: -80px; }
        }
    </style>
</head>
<body>

    <header class="hero-section">
        <div class="hero-circle c1"></div>
        <div class="hero-circle c2"></div>

        <div class="hero-content">
            <span class="hero-subtitle">Premium Real Estate Solutions</span>
            <h1 class="hero-title">Dream Mulk</h1>
            <p class="hero-desc">Redefining ownership. Where architectural brilliance meets your future legacy in Erbil.</p>
        </div>
    </header>

    <div class="main-container">
        <div class="glass-card">
            <div class="narrative-grid">
                <div class="narrative-text">
                    <h2>The Evolution of Living</h2>
                    <p>
                        Dream Mulk was established with a singular, powerful ambition: to elevate the standard of real estate in Kurdistan. We are not merely agents; we are the architects of your next chapter. In a market often defined by complexity, we serve as your beacon of clarity and sophistication.
                    </p>
                    <p>
                        Our journey is fueled by a commitment to modern technology and timeless integrity. We understand that acquiring property is not just a transaction—it is the foundation of your heritage. Whether you are seeking a sanctuary for your family or a cornerstone for your investment portfolio, Dream Mulk provides the vision to see what others miss.
                    </p>
                    <div class="quote-box">
                        "Property is land, but 'Mulk' is legacy. We help you build yours."
                    </div>
                </div>

                <div class="side-details">
                    <ul class="details-list">
                        <li class="detail-item">
                            <div class="detail-icon"><i class="fas fa-calendar-check"></i></div>
                            <div class="detail-content">
                                <h4>Established</h4>
                                <p>April 2026</p>
                            </div>
                        </li>
                        <li class="detail-item">
                            <div class="detail-icon"><i class="fas fa-map-marked"></i></div>
                            <div class="detail-content">
                                <h4>Headquarters</h4>
                                <p>Erbil, Kurdistan Region</p>
                            </div>
                        </li>
                        <li class="detail-item">
                            <div class="detail-icon"><i class="fas fa-globe"></i></div>
                            <div class="detail-content">
                                <h4>Vision</h4>
                                <p>To become the most trusted real estate ecosystem in Iraq.</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <section class="values-section">
            <h2 class="values-title">The Dream Mulk Standard</h2>

            <div class="values-grid">
                <div class="value-card">
                    <i class="fas fa-crown value-icon-lg"></i>
                    <h3>Exclusivity</h3>
                    <p>Access to a curated portfolio of properties that meet our rigorous standards for quality and location.</p>
                </div>

                <div class="value-card">
                    <i class="fas fa-laptop-code value-icon-lg"></i>
                    <h3>Innovation</h3>
                    <p>Utilizing state-of-the-art digital tools to make your buying or selling journey seamless and transparent.</p>
                </div>

                <div class="value-card">
                    <i class="fas fa-hand-holding-heart value-icon-lg"></i>
                    <h3>Integrity</h3>
                    <p>We build trust through radical transparency. No hidden details, just honest, expert advice.</p>
                </div>
            </div>
        </section>

        <div class="footer-badge">
            &copy; 2026 DREAM MULK REAL ESTATE GROUP
        </div>
    </div>

</body>
</html>
