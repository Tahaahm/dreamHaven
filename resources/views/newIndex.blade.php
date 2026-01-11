

<!DOCTYPE html>
<html lang="en">
  <head>
  <link href='https://unpkg.com/css.gg@2.0.0/icons/css/search.css' rel='stylesheet'>
  <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />

    <title>Real Estate Listings</title>
    <meta content="" name="description" />
    <meta content="" name="keywords" />

    <!-- Favicons -->

    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon" />

    <!-- Google Fonts -->
    <link
      href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,700,700i|Poppins:300,400,500,700"
      rel="stylesheet"
    />

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/aos/aos.css" rel="stylesheet" />

    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}">


    <link
      href="../vendor2/bootstrap-icons/bootstrap-icons.css"
      rel="stylesheet"
    />
    <link href="../vendo2r/boxicons/css/boxicons.min.css" rel="stylesheet" />
    <link
      href="../vendor2/glightbox/css/glightbox.min.css"  rel="stylesheet"
    />

    <link rel="stylesheet" type="text/css" href="{{ asset('../vendor2/swiper/swiper-bundle.min.css') }}">


    <!-- t Main CSS File -->
    <link rel="stylesheet" type="text/css" href="{{ asset('../css/newstyle.css') }}">

    <!-- =======================================================
  * t Name: Regna
  * Updated: Sep 18 2023 with Bootstrap v5.3.2
  * t URL: https://bootstrapmade.com/regna-bootstrap-onepage-t/
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
  </head>

  <body>





    <!-- ======= Header ======= -->
    @include('navbar')
    <!-- End Header -->

    <!-- ======= Hero Section ======= -->
    @php
    $backgroundImageUrl = asset('images/design-house-modern-villa-with-open-plan-living-private-bedroom-wing-large-terrace-with-privacy.jpg');
@endphp

<section style="background-image: url('{{ $backgroundImageUrl }}');" id="hero">
    <div class="hero-container" data-aos="zoom-in" data-aos-delay="100">
        <h1>DREAM Mulk</h1>
        <h2>
            A great platform to buy, sell and rent your properties without any agent or commissions.
        </h2>


<!--searchBox-->




<style>
  #search {
    display: grid;
    grid-area: search;
    grid-template:
      "search" 60px
      / 420px;
    justify-content: center;
    align-content: center;
    justify-items: stretch;
    align-items: stretch;
    background: none;
  }

  #search input {
    color: #fff;
    display: block;
    grid-area: search;
    -webkit-appearance: none;
    appearance: none;
    width: 100%;
    height: 100%;
    background: none;
    padding: 0 30px 0 60px;
    border: none;
    border-radius: 100px;
    font: 24px/1 system-ui, sans-serif;
    outline-offset: -8px;
  }

  #search svg {
    grid-area: search;
    overflow: visible;
    color: #c2c7ee;
    fill: none;
    stroke: currentColor;
  }

  .spark {
    fill: currentColor;
    stroke: none;
    r: 15;
  }

  .spark:nth-child(1) {
    animation: spark-radius 2.03s 1s both, spark-one-motion 2s 1s both;
  }

  @keyframes spark-radius {
    0% {
      r: 0;
      animation-timing-function: cubic-bezier(0, 0.3, 0, 1.57);
    }
    30% {
      r: 15;
      animation-timing-function: cubic-bezier(1, -0.39, 0.68, 1.04);
    }
    95% {
      r: 8;
    }
    99% {
      r: 10;
    }
    99.99% {
      r: 7;
    }
    100% {
      r: 0;
    }
  }

  @keyframes spark-one-motion {
    0% {
      transform: translate(-20%, 50%);
      animation-timing-function: cubic-bezier(0.63, 0.88, 0, 1.25);
    }
    20% {
      transform: rotate(-0deg) translate(0%, -50%);
      animation-timing-function: ease-in;
    }
    80% {
      transform: rotate(-230deg) translateX(-20%) rotate(-100deg)
        translateX(15%);
      animation-timing-function: linear;
    }
    100% {
      transform: rotate(-360deg) translate(30px, 100%);
      animation-timing-function: cubic-bezier(0.64, 0.66, 0, 0.51);
    }
  }

  .spark:nth-child(2) {
    animation: spark-radius 2.03s 1s both, spark-two-motion 2.03s 1s both;
  }

  @keyframes spark-two-motion {
    0% {
      transform: translate(120%, 50%) rotate(-70deg) translateY(0%);
      animation-timing-function: cubic-bezier(0.36, 0.18, 0.94, 0.55);
    }
    20% {
      transform: translate(90%, -80%) rotate(60deg) translateY(-80%);
      animation-timing-function: cubic-bezier(0.16, 0.77, 1, 0.4);
    }
    40% {
      transform: translate(110%, -50%) rotate(-30deg) translateY(-120%);
      animation-timing-function: linear;
    }
    70% {
      transform: translate(100%, -50%) rotate(120deg) translateY(-100%);
      animation-timing-function: linear;
    }
    80% {
      transform: translate(95%, 50%) rotate(80deg) translateY(-150%);
      animation-timing-function: cubic-bezier(0.64, 0.66, 0, 0.51);
    }
    100% {
      transform: translate(100%, 50%) rotate(120deg) translateY(0%);
    }
  }

  .spark:nth-child(3) {
    animation: spark-radius 2.05s 1s both, spark-three-motion 2.03s 1s both;
  }

  @keyframes spark-three-motion {
    0% {
      transform: translate(50%, 100%) rotate(-40deg) translateX(0%);
      animation-timing-function: cubic-bezier(0.62, 0.56, 1, 0.54);
    }
    30% {
      transform: translate(40%, 70%) rotate(20deg) translateX(20%);
      animation-timing-function: cubic-bezier(0, 0.21, 0.88, 0.46);
    }
    40% {
      transform: translate(65%, 20%) rotate(-50deg) translateX(15%);
      animation-timing-function: cubic-bezier(0, 0.24, 1, 0.62);
    }
    60% {
      transform: translate(60%, -40%) rotate(-50deg) translateX(20%);
      animation-timing-function: cubic-bezier(0, 0.24, 1, 0.62);
    }
    70% {
      transform: translate(70%, -0%) rotate(-180deg) translateX(20%);
      animation-timing-function: cubic-bezier(0.15, 0.48, 0.76, 0.26);
    }
    100% {
      transform: translate(70%, -0%) rotate(-360deg) translateX(0%)
        rotate(180deg) translateX(20%);
    }
  }

  .burst {
    stroke-width: 3;
  }

  .burst :nth-child(2n) {
    color: #ff783e;
  }
  .burst :nth-child(3n) {
    color: #ffab00;
  }
  .burst :nth-child(4n) {
    color: #55e214;
  }
  .burst :nth-child(5n) {
    color: #82d9f5;
  }

  .circle {
    r: 6;
  }

  .rect {
    width: 10px;
    height: 10px;
  }

  .triangle {
    d: path("M0,-6 L7,6 L-7,6 Z");
    stroke-linejoin: round;
  }

  .plus {
    d: path("M0,-5 L0,5 M-5,0L 5,0");
    stroke-linecap: round;
  }

  .burst:nth-child(4) {
    transform: translate(30px, 100%) rotate(150deg);
  }

  .burst:nth-child(5) {
    transform: translate(50%, 0%) rotate(-20deg);
  }

  .burst:nth-child(6) {
    transform: translate(100%, 50%) rotate(75deg);
  }



  @keyframes particle-fade {
    0%,
    100% {
      opacity: 0;
    }
    5%,
    80% {
      opacity: 1;
    }
  }

  .burst :nth-child(1) {
    animation: particle-fade 600ms 2.95s both,
      particle-one-move 600ms 2.95s both;
  }
  .burst :nth-child(2) {
    animation: particle-fade 600ms 2.95s both,
      particle-two-move 600ms 2.95s both;
  }
  .burst :nth-child(3) {
    animation: particle-fade 600ms 2.95s both,
      particle-three-move 600ms 2.95s both;
  }
  .burst :nth-child(4) {
    animation: particle-fade 600ms 2.95s both,
      particle-four-move 600ms 2.95s both;
  }
  .burst :nth-child(5) {
    animation: particle-fade 600ms 2.95s both,
      particle-five-move 600ms 2.95s both;
  }
  .burst :nth-child(6) {
    animation: particle-fade 600ms 2.95s both,
      particle-six-move 600ms 2.95s both;
  }

  @keyframes particle-one-move {
    0% {
      transform: rotate(0deg) translate(-5%) scale(0.0001, 0.0001);
    }
    100% {
      transform: rotate(-20deg) translateX(8%) scale(0.5, 0.5);
    }
  }
  @keyframes particle-two-move {
    0% {
      transform: rotate(0deg) translate(-5%) scale(0.0001, 0.0001);
    }
    100% {
      transform: rotate(0deg) translateX(8%) scale(0.5, 0.5);
    }
  }
  @keyframes particle-three-move {
    0% {
      transform: rotate(0deg) translate(-5%) scale(0.0001, 0.0001);
    }
    100% {
      transform: rotate(20deg) translateX(8%) scale(0.5, 0.5);
    }
  }
  @keyframes particle-four-move {
    0% {
      transform: rotate(0deg) translate(-5%) scale(0.0001, 0.0001);
    }
    100% {
      transform: rotate(-35deg) translateX(12%);
    }
  }
  @keyframes particle-five-move {
    0% {
      transform: rotate(0deg) translate(-5%) scale(0.0001, 0.0001);
    }
    100% {
      transform: rotate(0deg) translateX(12%);
    }
  }
  @keyframes particle-six-move {
    0% {
      transform: rotate(0deg) translate(-5%) scale(0.0001, 0.0001);
    }
    100% {
      transform: rotate(35deg) translateX(12%);
    }
  }

  .bar {
    width: 100%;
    height: 100%;
    ry: 50%;
    stroke-width: 3;
    animation: bar-in 900ms 3s both;
  }

  @keyframes bar-in {
    0% {
      stroke-dasharray: 0 180 0 226 0 405 0 0;
    }
    100% {
      stroke-dasharray: 0 0 181 0 227 0 405 0;
    }
  }

  .magnifier {
    animation: magnifier-in 600ms 3.6s both;
    transform-box: fill-box;
  }

  @keyframes magnifier-in {
    0% {
      transform: translate(20px, 8px) rotate(-45deg) scale(0.01, 0.01);
    }
    50% {
      transform: translate(-4px, 8px) rotate(-45deg);
    }
    100% {
      transform: translate(0px, 0px) rotate(0deg);
    }
  }

  .magnifier .handle {
    x1: 32;
    y1: 32;
    x2: 44;
    y2: 44;
    stroke-width: 3;
  }

  #searchIcon {
    cursor: pointer;
    display: none; /* Initially hide the search icon */
  }
  #searchInput::placeholder {
   color      : #c2c7ee;
  opacity: 1; /* Ensures full opacity for the color */
}

  #results {
    grid-area: results;
    background: hsl(0, 0%, 95%);
  }

  #search i {
    margin-top: -40px;
    margin-left: 15px;
    color: #c2c7ee;
    cursor: pointer;
  }
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
      document.getElementById("searchIcon").style.display = "block"; // Show the search icon after 3 seconds
    }, 3000); // Set the delay in milliseconds

    // Log a message when the script attaches event listeners
    console.log("Event listeners attached");


    document.getElementById('searchInput').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const query = this.value;
            window.location.href = `{{ route('properties.search') }}?q=${query}`;
        }
    });
    // Add click event listener directly to the search icon element
    document.querySelector('#searchIcon').addEventListener("click", function() {
      console.log("Search icon clicked");
      var query = document.getElementById("searchInput").value;
      window.location.href = "{{ route('properties.search') }}?q=" + encodeURIComponent(query);
    });
  });
</script>











                                     <!--searchBox-->

    </div>

</section>
    <!-- End Hero Section -->

    <main id="main">
      <!-- ======= About Section ======= -->
      <section id="about">
        <div class="container" data-aos="fade-up">
          <!-- End Facts Section -->

          <!-- ======= Services Section ======= -->

          <!-- End Services Section -->

          <!-- ======= Call To Action Section ======= -->
          @php
    $SECbackgroundImageUrl2 = asset('../images/AdobeStock_565645717.jpeg');
    $SECbackgroundImageUrl = asset('../images/house.jpg');
    $SECbackgroundImageUrl = asset('../images/giving house keys.webp');
@endphp

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
    $agentId = session('agent_id'); // agent session
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




          <!-- End Call To Action Section -->

          <!-- search section -->




<div class="about-centered-content">
    <div class="about-container">
        <h1>About Us</h1>
        <div class="about-info">
        <p>Welcome to our website!</p>
            <p>We are a group of passionate students who came together to create something beautiful.</p>
            <p>Our project started as a simple idea and grew into something much more. It's a testament to our dedication, creativity, and teamwork.</p>
            <p>Through hard work and collaboration, we've built a platform that we're proud of. This project has been a journey of learning and growth for all of us.</p>
            <p>Thank you for visiting and being a part of our story.</p>
            <p>Created by Ahmad Nyaz, Zana Goran, and Taha Ahmed.</p>
            <p><strong>Address:</strong> Erbil, Kurdistan, Iraq</p>
            <p><strong>Website Creation Date:</strong> April 3, 2024</p>

        </div>
    </div>
</div>





      </section>






    </main>
    <!-- End #main -->




    <a
      href="#"
      class="back-to-top d-flex align-items-center justify-content-center"
      ><i class="bi bi-arrow-up-short"></i
    ></a>

    <!-- Vendor JS Files -->
    <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
    <script src="assets/vendor/aos/aos.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>

    <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>

    <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
    <script src="assets/vendor/php-email-form/validate.js"></script>

    <!-- t Main JS File -->
    <script>/**
* t Name: Regna
* Updated: Sep 18 2023 with Bootstrap v5.3.2
* t URL: https://bootstrapmade.com/regna-bootstrap-onepage-t/
* Author: BootstrapMade.com
* License: https://bootstrapmade.com/license/
*/
;(function () {
  'use strict'

  /**
   * Easy selector helper function
   */
  const select = (el, all = false) => {
    el = el.trim()
    if (all) {
      return [...document.querySelectorAll(el)]
    } else {
      return document.querySelector(el)
    }
  }

  /**
   * Easy event listener function
   */
  const on = (type, el, listener, all = false) => {
    let selectEl = select(el, all)
    if (selectEl) {
      if (all) {
        selectEl.forEach(e => e.addEventListener(type, listener))
      } else {
        selectEl.addEventListener(type, listener)
      }
    }
  }

  /**
   * Easy on scroll event listener
   */
  const onscroll = (el, listener) => {
    el.addEventListener('scroll', listener)
  }

  /**
   * Navbar links active state on scroll
   */
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

  /**
   * Scrolls to an element with header offset
   */
  const scrollto = (el) => {
    let header = select('#header')
    let offset = header.offsetHeight

    if (!header.classList.contains('header-scrolled')) {
      offset -= 20
    }

    let elementPos = select(el).offsetTop
    window.scrollTo({
      top: elementPos - offset,
      behavior: 'smooth'
    })
  }

  /**
   * Toggle .header-scrolled class to #header when page is scrolled
   */
  let selectHeader = select('#header')
  if (selectHeader) {
    const headerScrolled = () => {
      if (window.scrollY > 100) {
        selectHeader.classList.add('header-scrolled')
      } else {
        selectHeader.classList.remove('header-scrolled')
      }
    }
    window.addEventListener('load', headerScrolled)
    onscroll(document, headerScrolled)
  }

  /**
   * Back to top button
   */
  let backtotop = select('.back-to-top')
  if (backtotop) {
    const toggleBacktotop = () => {
      if (window.scrollY > 100) {
        backtotop.classList.add('active')
      } else {
        backtotop.classList.remove('active')
      }
    }
    window.addEventListener('load', toggleBacktotop)
    onscroll(document, toggleBacktotop)
  }

  /**
   * Mobile nav toggle
   */
  on('click', '.mobile-nav-toggle', function (e) {
    select('#navbar').classList.toggle('navbar-mobile')
    this.classList.toggle('bi-list')
    this.classList.toggle('bi-x')
  })

  /**
   * Mobile nav dropdowns activate
   */
  on('click', '.navbar .dropdown > a', function (e) {
    if (select('#navbar').classList.contains('navbar-mobile')) {
      e.preventDefault()
      this.nextElementSibling.classList.toggle('dropdown-active')
    }
  }, true)

  /**
   * Scrool with ofset on links with a class name .scrollto
   */
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

  /**
   * Scroll with ofset on page load with hash links in the url
   */
  window.addEventListener('load', () => {
    if (window.location.hash) {
      if (select(window.location.hash)) {
        scrollto(window.location.hash)
      }
    }
  })

  /**
   * Porfolio isotope and filter
   */

  window.addEventListener('load', () => {
    let portfolioContainer = document.querySelector('.portfolio-container')
    if (portfolioContainer) {
      let portfolioIsotope = new Isotope(portfolioContainer, {
        itemSelector: '.portfolio-item',
        layoutMode: 'fitRows',
        getSortData: {
          date: '.item-date', // Update this to match your HTML structure
        }
      })

      let portfolioFilters = document.querySelectorAll('#portfolio-flters li')

      portfolioFilters.forEach(function (filter) {
        filter.addEventListener('click', function (e) {
          e.preventDefault()
          portfolioFilters.forEach(function (el) {
            el.classList.remove('filter-active')
          })
          this.classList.add('filter-active')

          portfolioIsotope.arrange({
            filter: this.getAttribute('data-filter')
          })
          portfolioIsotope.on('arrangeComplete', function () {
            AOS.refresh()
          })
        })
      })

      let sortButton = document.querySelector('#portfolio-flters li[data-sort-by]')
      if (sortButton) {
        sortButton.addEventListener('click', function (e) {
          e.preventDefault()
          portfolioFilters.forEach(function (el) {
            el.classList.remove('item-date')
          })
          this.classList.add('item-date')

          portfolioIsotope.arrange({
            sortBy: this.getAttribute('data-sort-by')
          })
          portfolioIsotope.on('arrangeComplete', function () {
            AOS.refresh()
          })
        })
      }
    }
  })

  /**
   * Initiate portfolio lightbox
   */
  const portfolioLightbox = GLightbox({
    selector: '.portfolio-lightbox'
  })

  /**
   * Portfolio details slider
   */
  new Swiper('.portfolio-details-slider', {
    speed: 300,
    loop: true,
    autoplay: {
      delay: 4000,
      disableOnInteraction: false
    },
    pagination: {
      el: '.swiper-pagination',
      type: 'bullets',
      clickable: true
    }
  })

  /**
   * Animation on scroll
   */
  window.addEventListener('load', () => {
    AOS.init({
      duration: 1000,
      easing: 'ease-in-out',
      once: true,
      mirror: false
    })
  })

  /**
   * Initiate Pure Counter
   */
  new PureCounter()
})()
</script>

  </body>
</html>
