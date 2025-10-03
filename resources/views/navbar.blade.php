<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Website Navbar</title>
    <style>
        /* Import Google font - Poppins */
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap");
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }
        a {
            text-decoration: none;
        }
      
        .unique-header {
        position: fixed;
        height: 80px;
        width: 100%;
        z-index: 100;
        padding: 0 20px;
        transition: transform 0.3s ease;
      }
        .unique-nav {
            max-width: 1100px;
            width: 100%;
            margin: 0 auto;
        }
        .unique-nav,
        .unique-nav-item {
            display: flex;
            height: 100%;
            align-items: center;
            justify-content: space-between;
        }
        .unique-nav-logo,
        .unique-nav-link,
        .unique-button {
            color: #fff;
        }
        .unique-nav-logo {
            font-size: 25px;
        }
        .unique-nav-item {
            column-gap: 25px;
        }
        .unique-nav-link:hover {
            color: #fff;
        }
        .unique-button {
            padding: 6px 24px;
            border: 2px solid #fff;
            background: transparent;
            border-radius: 6px;
            cursor: pointer;
        }
        .unique-button:active {
            transform: scale(0.98);
        }
        .user-initial-circle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #000;
            font-weight: 500;
            text-transform: uppercase; /* Ensure text is always uppercase */
            border: none; /* Remove border */
            transition: 0.3s;
        }
        .user-initial-circle:hover {
            font-size: 18px;
            box-shadow: 0px 0px 10px white;
        }
        .dropdown-menu {
            background-color: #fff;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
    <!-- Unicons -->
    <link
      rel="stylesheet"
      href="https://unicons.iconscout.com/release/v4.0.0/css/line.css"
    />
</head>
<body>
    <!-- Header -->
    <header class="unique-header" id="navbar">
        <nav class="unique-nav">
            <a href="#" class="unique-nav-logo">Dream Haven</a>

            <ul class="unique-nav-items">
                <li class="unique-nav-item">
                    <a class="unique-nav-link {{ request()->routeIs('newindex') ? ' active' : '' }}" href="{{ route('newindex') }}">Home</a>
                    <a class="unique-nav-link" href="{{ route('list') }}">Properties</a>
                    <a class="unique-nav-link" href="{{ route('about-us') }}">About Us</a>
                    <a class="unique-nav-link" href="{{ route('contact-us') }}">Contact</a>
                </li>
            </ul>

            @auth
              <a href="{{ route('agent.admin-dashboard') }}" class="btn-get-started">
                <div class="user-initial-circle">
                  {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
              </a>
            @else
              <a href="{{ route('login-page') }}">
                <button class="unique-button">Login</button>
              </a>
            @endauth
        </nav>
    </header>

    <script>
        let lastScrollTop = 0;
        const navbar = document.getElementById('navbar');
        
        window.addEventListener('scroll', function() {
            let currentScroll = window.pageYOffset || document.documentElement.scrollTop;
            
            if (currentScroll > lastScrollTop) {
                // Scrolling down
                navbar.style.transform = 'translateY(-100%)'; // Hide navbar
            } else {
                // Scrolling up
                navbar.style.transform = 'translateY(0)'; // Show navbar
            }
            
            lastScrollTop = currentScroll <= 0 ? 0 : currentScroll; // For Mobile or negative scrolling
        }, false);
    </script>
</body>
</html>
