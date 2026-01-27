<!DOCTYPE html>
<html lang="en">
<head>
    <link href='https://unpkg.com/css.gg@2.0.0/icons/css/search.css' rel='stylesheet'>
    <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />

    <title>Dream Mulk - Real Estate</title>
    <meta content="" name="description" />
    <meta content="" name="keywords" />

    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon" />

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,700,700i|Poppins:300,400,500,600,700|Playfair+Display:400,700" rel="stylesheet"/>

    <link href="assets/vendor/aos/aos.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link href="../vendor2/bootstrap-icons/bootstrap-icons.css" rel="stylesheet"/>
    <link href="../vendo2r/boxicons/css/boxicons.min.css" rel="stylesheet" />
    <link href="../vendor2/glightbox/css/glightbox.min.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="{{ asset('../vendor2/swiper/swiper-bundle.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" type="text/css" href="{{ asset('../css/newstyle.css') }}">

    <style>
        :root {
            --primary: #303b97; /* Your Brand Color */
            --accent: #d4af37; /* Gold */
        }

        /* --- Agent Floating Button --- */
        .agent-fab {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--primary);
            color: white;
            padding: 15px 25px;
            border-radius: 50px;
            box-shadow: 0 4px 20px rgba(48, 59, 151, 0.4);
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            border: 2px solid white;
        }

        .agent-fab:hover {
            transform: translateY(-5px);
            background: #202660;
            color: var(--accent);
            box-shadow: 0 8px 25px rgba(48, 59, 151, 0.6);
        }

        .agent-fab i {
            font-size: 1.2rem;
        }

        /* --- Updated Service Buttons to match Brand --- */
        .button {
            background-color: var(--primary) !important;
            border: none !important;
        }
        .button:hover {
            background-color: #202660 !important;
        }

        /* --- About Section Premium Styles --- */
        #about {
            background-color: #f4f6fa;
            padding: 80px 0;
            position: relative;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 60px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(48, 59, 151, 0.1);
            position: relative;
            overflow: hidden;
            border-top: 5px solid var(--accent);
        }

        .about-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 50px;
            align-items: center;
        }

        .about-content h2 {
            font-family: 'Playfair Display', serif;
            color: var(--primary);
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        .about-content p {
            color: #555;
            font-size: 1.05rem;
            line-height: 1.8;
            margin-bottom: 20px;
        }

        .quote-box {
            background: #f8f9fa;
            border-left: 4px solid var(--accent);
            padding: 15px 20px;
            font-style: italic;
            color: var(--primary);
            font-weight: 500;
            margin-top: 20px;
        }

        .values-list {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .value-item {
            background: white;
            padding: 20px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }

        .value-item:hover {
            transform: translateX(10px);
        }

        .value-icon {
            width: 50px;
            height: 50px;
            background: rgba(48, 59, 151, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 1.2rem;
        }

        .value-text h4 {
            margin: 0;
            font-size: 1.1rem;
            color: var(--primary);
            font-family: 'Playfair Display', serif;
        }

        .value-text span {
            font-size: 0.9rem;
            color: #666;
        }

        @media (max-width: 991px) {
            .about-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <a href="{{ route('agent.login') }}" class="agent-fab" data-aos="fade-left" data-aos-delay="500">
        <i class="fa-solid fa-user-shield"></i>
        <span>Agent Portal</span>
    </a>

    @include('navbar')
    @php
        $backgroundImageUrl = asset('images/design-house-modern-villa-with-open-plan-living-private-bedroom-wing-large-terrace-with-privacy.jpg');
    @endphp

    <section style="background-image: url('{{ $backgroundImageUrl }}');" id="hero">
        <div class="hero-container" data-aos="zoom-in" data-aos-delay="100">
            <h1 style="font-family: 'Playfair Display', serif;">DREAM Mulk</h1>
            <h2>
                A great platform to buy, sell and rent your properties without any agent or commissions.
            </h2>

            <style>
                #search { display: grid; grid-area: search; grid-template: "search" 60px / 420px; justify-content: center; align-content: center; justify-items: stretch; align-items: stretch; background: none; }
                #search input { color: #fff; display: block; grid-area: search; -webkit-appearance: none; appearance: none; width: 100%; height: 100%; background: none; padding: 0 30px 0 60px; border: none; border-radius: 100px; font: 24px/1 system-ui, sans-serif; outline-offset: -8px; }
                #search svg { grid-area: search; overflow: visible; color: #c2c7ee; fill: none; stroke: currentColor; }
                .spark { fill: currentColor; stroke: none; r: 15; }
                .spark:nth-child(1) { animation: spark-radius 2.03s 1s both, spark-one-motion 2s 1s both; }
                @keyframes spark-radius { 0% { r: 0; animation-timing-function: cubic-bezier(0, 0.3, 0, 1.57); } 30% { r: 15; animation-timing-function: cubic-bezier(1, -0.39, 0.68, 1.04); } 95% { r: 8; } 99% { r: 10; } 99.99% { r: 7; } 100% { r: 0; } }
                @keyframes spark-one-motion { 0% { transform: translate(-20%, 50%); animation-timing-function: cubic-bezier(0.63, 0.88, 0, 1.25); } 20% { transform: rotate(-0deg) translate(0%, -50%); animation-timing-function: ease-in; } 80% { transform: rotate(-230deg) translateX(-20%) rotate(-100deg) translateX(15%); animation-timing-function: linear; } 100% { transform: rotate(-360deg) translate(30px, 100%); animation-timing-function: cubic-bezier(0.64, 0.66, 0, 0.51); } }
                .spark:nth-child(2) { animation: spark-radius 2.03s 1s both, spark-two-motion 2.03s 1s both; }
                @keyframes spark-two-motion { 0% { transform: translate(120%, 50%) rotate(-70deg) translateY(0%); animation-timing-function: cubic-bezier(0.36, 0.18, 0.94, 0.55); } 20% { transform: translate(90%, -80%) rotate(60deg) translateY(-80%); animation-timing-function: cubic-bezier(0.16, 0.77, 1, 0.4); } 40% { transform: translate(110%, -50%) rotate(-30deg) translateY(-120%); animation-timing-function: linear; } 70% { transform: translate(100%, -50%) rotate(120deg) translateY(-100%); animation-timing-function: linear; } 80% { transform: translate(95%, 50%) rotate(80deg) translateY(-150%); animation-timing-function: cubic-bezier(0.64, 0.66, 0, 0.51); } 100% { transform: translate(100%, 50%) rotate(120deg) translateY(0%); } }
                .spark:nth-child(3) { animation: spark-radius 2.05s 1s both, spark-three-motion 2.03s 1s both; }
                @keyframes spark-three-motion { 0% { transform: translate(50%, 100%) rotate(-40deg) translateX(0%); animation-timing-function: cubic-bezier(0.62, 0.56, 1, 0.54); } 30% { transform: translate(40%, 70%) rotate(20deg) translateX(20%); animation-timing-function: cubic-bezier(0, 0.21, 0.88, 0.46); } 40% { transform: translate(65%, 20%) rotate(-50deg) translateX(15%); animation-timing-function: cubic-bezier(0, 0.24, 1, 0.62); } 60% { transform: translate(60%, -40%) rotate(-50deg) translateX(20%); animation-timing-function: cubic-bezier(0, 0.24, 1, 0.62); } 70% { transform: translate(70%, -0%) rotate(-180deg) translateX(20%); animation-timing-function: cubic-bezier(0.15, 0.48, 0.76, 0.26); } 100% { transform: translate(70%, -0%) rotate(-360deg) translateX(0%) rotate(180deg) translateX(20%); } }
                .burst { stroke-width: 3; }
                .burst :nth-child(2n) { color: #ff783e; }
                .burst :nth-child(3n) { color: #ffab00; }
                .burst :nth-child(4n) { color: #55e214; }
                .burst :nth-child(5n) { color: #82d9f5; }
                .circle { r: 6; }
                .rect { width: 10px; height: 10px; }
                .triangle { d: path("M0,-6 L7,6 L-7,6 Z"); stroke-linejoin: round; }
                .plus { d: path("M0,-5 L0,5 M-5,0L 5,0"); stroke-linecap: round; }
                .burst:nth-child(4) { transform: translate(30px, 100%) rotate(150deg); }
                .burst:nth-child(5) { transform: translate(50%, 0%) rotate(-20deg); }
                .burst:nth-child(6) { transform: translate(100%, 50%) rotate(75deg); }
                @keyframes particle-fade { 0%, 100% { opacity: 0; } 5%, 80% { opacity: 1; } }
                .burst :nth-child(1) { animation: particle-fade 600ms 2.95s both, particle-one-move 600ms 2.95s both; }
                .burst :nth-child(2) { animation: particle-fade 600ms 2.95s both, particle-two-move 600ms 2.95s both; }
                .burst :nth-child(3) { animation: particle-fade 600ms 2.95s both, particle-three-move 600ms 2.95s both; }
                .burst :nth-child(4) { animation: particle-fade 600ms 2.95s both, particle-four-move 600ms 2.95s both; }
                .burst :nth-child(5) { animation: particle-fade 600ms 2.95s both, particle-five-move 600ms 2.95s both; }
                .burst :nth-child(6) { animation: particle-fade 600ms 2.95s both, particle-six-move 600ms 2.95s both; }
                @keyframes particle-one-move { 0% { transform: rotate(0deg) translate(-5%) scale(0.0001, 0.0001); } 100% { transform: rotate(-20deg) translateX(8%) scale(0.5, 0.5); } }
                @keyframes particle-two-move { 0% { transform: rotate(0deg) translate(-5%) scale(0.0001, 0.0001); } 100% { transform: rotate(0deg) translateX(8%) scale(0.5, 0.5); } }
                @keyframes particle-three-move { 0% { transform: rotate(0deg) translate(-5%) scale(0.0001, 0.0001); } 100% { transform: rotate(20deg) translateX(8%) scale(0.5, 0.5); } }
                @keyframes particle-four-move { 0% { transform: rotate(0deg) translate(-5%) scale(0.0001, 0.0001); } 100% { transform: rotate(-35deg) translateX(12%); } }
                @keyframes particle-five-move { 0% { transform: rotate(0deg) translate(-5%) scale(0.0001, 0.0001); } 100% { transform: rotate(0deg) translateX(12%); } }
                @keyframes particle-six-move { 0% { transform: rotate(0deg) translate(-5%) scale(0.0001, 0.0001); } 100% { transform: rotate(35deg) translateX(12%); } }
                .bar { width: 100%; height: 100%; ry: 50%; stroke-width: 3; animation: bar-in 900ms 3s both; }
                @keyframes bar-in { 0% { stroke-dasharray: 0 180 0 226 0 405 0 0; } 100% { stroke-dasharray: 0 0 181 0 227 0 405 0; } }
                .magnifier { animation: magnifier-in 600ms 3.6s both; transform-box: fill-box; }
                @keyframes magnifier-in { 0% { transform: translate(20px, 8px) rotate(-45deg) scale(0.01, 0.01); } 50% { transform: translate(-4px, 8px) rotate(-45deg); } 100% { transform: translate(0px, 0px) rotate(0deg); } }
                .magnifier .handle { x1: 32; y1: 32; x2: 44; y2: 44; stroke-width: 3; }
                #searchIcon { cursor: pointer; display: none; }
                #searchInput::placeholder { color: #c2c7ee; opacity: 1; }
                #results { grid-area: results; background: hsl(0, 0%, 95%); }
                #search i { margin-top: -40px; margin-left: 15px; color: #c2c7ee; cursor: pointer; }
            </style>

            <div id="search">
                <i class="gg-search search-icon" id="searchIcon"></i>
                <svg viewBox="0 0 420 60" xmlns="http://www.w3.org/2000/svg">
                    <rect class="bar" />
                    <g class="sparks">
                        <circle class="spark" />
                        <circle class="spark" />
                        <circle class="spark" />
                    </g>
                    <g class="burst pattern-one">
                        <circle class="particle circle" />
                        <path class="particle triangle" />
                        <circle class="particle circle" />
                        <path class="particle plus" />
                        <rect class="particle rect" />
                        <path class="particle triangle" />
                    </g>
                    <g class="burst pattern-two">
                        <path class="particle plus" />
                        <circle class="particle circle" />
                        <path class="particle triangle" />
                        <rect class="particle rect" />
                        <circle class="particle circle" />
                        <path class="particle plus" />
                    </g>
                    <g class="burst pattern-three">
                        <circle class="particle circle" />
                        <rect class="particle rect" />
                        <path class="particle plus" />
                        <path class="particle triangle" />
                        <rect class="particle rect" />
                        <path class="particle plus" />
                    </g>
                </svg>
                <input type="search" name="q" aria-label="Search for inspiration" id="searchInput" placeholder="" />
            </div>

            <div id="results"></div>

            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    setTimeout(function() {
                        document.getElementById("searchInput").setAttribute("placeholder", "Search for an address or city");
                        document.getElementById("searchIcon").style.display = "block";
                    }, 3000);

                    document.getElementById('searchInput').addEventListener('keypress', function (e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            const query = this.value;
                            window.location.href = `{{ route('properties.search') }}?q=${query}`;
                        }
                    });

                    document.querySelector('#searchIcon').addEventListener("click", function() {
                        var query = document.getElementById("searchInput").value;
                        window.location.href = "{{ route('properties.search') }}?q=" + encodeURIComponent(query);
                    });
                });
            </script>
        </div>
    </section>
    <main id="main">

        <div id="services">
            <div class="service-box" style="background-image: url('../images/AdobeStock_565645717.jpeg');">
                <div class="service-content">
                    <h3 class="service-title">Buy a Property</h3>
                    <p class="service-text">Finding the perfect home can be daunting. Our search tool streamlines the process, allowing users to filter properties by location, price, and more.</p>
                    <div class="buttons-container">
                        <a href="{{ route('property.list') }}" class="button">Search</a>
                    </div>
                </div>
            </div>

            <div class="service-box" style="background-image: url('../images/house.jpg');">
                <div class="service-content">
                    <h3 class="service-title">Sell a Property</h3>
                    <p class="service-text">Unlock the charm of your property. Upload it here and embark on a journey to find the perfect buyer who will appreciate it as much as you do.</p>
                    <div class="buttons-container">
                        @php
                            $user = Auth::user();
                            $agentId = session('agent_id');
                        @endphp

                        @if($user || $agentId)
                            <a href="{{ route('property.upload') }}" class="button">Register Your Home</a>
                        @else
                            <a href="{{ route('login-page') }}" class="button">Register Your Home</a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="service-box" style="background-image: url('../images/giving house keys.webp');">
                <div class="service-content">
                    <h3 class="service-title">Rent a Property</h3>
                    <p class="service-text">We're simplifying the rental process - from browsing to payment. Discover your perfect space effortlessly, tailored to your needs and budget.</p>
                    <div class="buttons-container">
                        <a href="{{ route('property.list', ['type' => 'rent']) }}" class="button find-rental-button">Find Rental</a>
                    </div>
                </div>
            </div>
        </div>

        <section id="about">
            <div class="container" data-aos="fade-up">
                <div class="glass-card">
                    <div class="about-grid">
                        <div class="about-content">
                            <h2>The Dream Mulk Standard</h2>
                            <p>
                                Dream Mulk was established with a singular, powerful ambition: to elevate the standard of real estate in Kurdistan. We are not merely agents; we are the architects of your next chapter. In a market often defined by complexity, we serve as your beacon of clarity and sophistication.
                            </p>
                            <p>
                                Our journey is fueled by a commitment to modern technology and timeless integrity. We understand that acquiring property is not just a transaction—it is the foundation of your heritage.
                            </p>
                            <div class="quote-box">
                                "Property is land, but 'Mulk' is legacy. We help you build yours."
                            </div>
                        </div>

                        <div class="values-list">
                            <div class="value-item">
                                <div class="value-icon"><i class="fas fa-crown"></i></div>
                                <div class="value-text">
                                    <h4>Exclusivity</h4>
                                    <span>Curated Portfolio</span>
                                </div>
                            </div>
                            <div class="value-item">
                                <div class="value-icon"><i class="fas fa-handshake"></i></div>
                                <div class="value-text">
                                    <h4>Integrity</h4>
                                    <span>Radical Transparency</span>
                                </div>
                            </div>
                            <div class="value-item">
                                <div class="value-icon"><i class="fas fa-map-marked-alt"></i></div>
                                <div class="value-text">
                                    <h4>Erbil Based</h4>
                                    <span>Est. 2026</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
        <i class="bi bi-arrow-up-short"></i>
    </a>

    <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
    <script src="assets/vendor/aos/aos.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
    <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
    <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
    <script src="assets/vendor/php-email-form/validate.js"></script>

    <script>
        ;(function () {
            'use strict'
            const select = (el, all = false) => {
                el = el.trim()
                if (all) { return [...document.querySelectorAll(el)] } else { return document.querySelector(el) }
            }
            const on = (type, el, listener, all = false) => {
                let selectEl = select(el, all)
                if (selectEl) {
                    if (all) { selectEl.forEach(e => e.addEventListener(type, listener)) } else { selectEl.addEventListener(type, listener) }
                }
            }
            const onscroll = (el, listener) => { el.addEventListener('scroll', listener) }
            let navbarlinks = select('#navbar .scrollto', true)
            const navbarlinksActive = () => {
                let position = window.scrollY + 200
                navbarlinks.forEach(navbarlink => {
                    if (!navbarlink.hash) return
                    let section = select(navbarlink.hash)
                    if (!section) return
                    if (position >= section.offsetTop && position <= (section.offsetTop + section.offsetHeight)) {
                        navbarlink.classList.add('active')
                    } else {
                        navbarlink.classList.remove('active')
                    }
                })
            }
            window.addEventListener('load', navbarlinksActive)
            onscroll(document, navbarlinksActive)
            const scrollto = (el) => {
                let header = select('#header')
                let offset = header.offsetHeight
                if (!header.classList.contains('header-scrolled')) { offset -= 20 }
                let elementPos = select(el).offsetTop
                window.scrollTo({ top: elementPos - offset, behavior: 'smooth' })
            }
            let selectHeader = select('#header')
            if (selectHeader) {
                const headerScrolled = () => {
                    if (window.scrollY > 100) { selectHeader.classList.add('header-scrolled') } else { selectHeader.classList.remove('header-scrolled') }
                }
                window.addEventListener('load', headerScrolled)
                onscroll(document, headerScrolled)
            }
            let backtotop = select('.back-to-top')
            if (backtotop) {
                const toggleBacktotop = () => {
                    if (window.scrollY > 100) { backtotop.classList.add('active') } else { backtotop.classList.remove('active') }
                }
                window.addEventListener('load', toggleBacktotop)
                onscroll(document, toggleBacktotop)
            }
            on('click', '.mobile-nav-toggle', function (e) {
                select('#navbar').classList.toggle('navbar-mobile')
                this.classList.toggle('bi-list')
                this.classList.toggle('bi-x')
            })
            on('click', '.navbar .dropdown > a', function (e) {
                if (select('#navbar').classList.contains('navbar-mobile')) {
                    e.preventDefault()
                    this.nextElementSibling.classList.toggle('dropdown-active')
                }
            }, true)
            on('click', '.scrollto', function (e) {
                if (select(this.hash)) {
                    e.preventDefault()
                    let navbar = select('#navbar')
                    if (navbar.classList.contains('navbar-mobile')) {
                        navbar.classList.remove('navbar-mobile')
                        let navbarToggle = select('.mobile-nav-toggle')
                        navbarToggle.classList.toggle('bi-list')
                        navbarToggle.classList.toggle('bi-x')
                    }
                    scrollto(this.hash)
                }
            }, true)
            window.addEventListener('load', () => {
                if (window.location.hash) {
                    if (select(window.location.hash)) { scrollto(window.location.hash) }
                }
            })
            window.addEventListener('load', () => {
                let portfolioContainer = document.querySelector('.portfolio-container')
                if (portfolioContainer) {
                    let portfolioIsotope = new Isotope(portfolioContainer, { itemSelector: '.portfolio-item', layoutMode: 'fitRows', getSortData: { date: '.item-date', } })
                    let portfolioFilters = document.querySelectorAll('#portfolio-flters li')
                    portfolioFilters.forEach(function (filter) {
                        filter.addEventListener('click', function (e) {
                            e.preventDefault()
                            portfolioFilters.forEach(function (el) { el.classList.remove('filter-active') })
                            this.classList.add('filter-active')
                            portfolioIsotope.arrange({ filter: this.getAttribute('data-filter') })
                            portfolioIsotope.on('arrangeComplete', function () { AOS.refresh() })
                        })
                    })
                    let sortButton = document.querySelector('#portfolio-flters li[data-sort-by]')
                    if (sortButton) {
                        sortButton.addEventListener('click', function (e) {
                            e.preventDefault()
                            portfolioFilters.forEach(function (el) { el.classList.remove('item-date') })
                            this.classList.add('item-date')
                            portfolioIsotope.arrange({ sortBy: this.getAttribute('data-sort-by') })
                            portfolioIsotope.on('arrangeComplete', function () { AOS.refresh() })
                        })
                    }
                }
            })
            const portfolioLightbox = GLightbox({ selector: '.portfolio-lightbox' })
            new Swiper('.portfolio-details-slider', { speed: 300, loop: true, autoplay: { delay: 4000, disableOnInteraction: false }, pagination: { el: '.swiper-pagination', type: 'bullets', clickable: true } })
            window.addEventListener('load', () => { AOS.init({ duration: 1000, easing: 'ease-in-out', once: true, mirror: false }) })
            new PureCounter()
        })()
    </script>

</body>
</html>
