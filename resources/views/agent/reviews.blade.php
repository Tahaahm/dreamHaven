<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Reviews</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f5f5f5; /* Light gray background */
        }
        .unique-header {
    position  : fixed;
    height    : 80px;
    width     : 100%;
    z-index   : 100;
    padding   : 0 20px;
    background: #303b97;
    /* Ensure a background color if needed */
}
.custom-navbar-color .navbar a,
.custom-navbar-color .navbar a:focus {
    color: #000; /* Example text color change */
}


        .allin {
            margin-left: 275px;
        }

        .reviews {
            text-align: center;
            padding: 50px 20px;
            background: #f5f5f5; /* Light gray background to match body */
        }

        .reviews h2 {
            font-size: 2em;
            margin-bottom: 20px;
            display: inline-block;
            padding: 10px 20px;
            background: #000000;
            color: #ffffff;
            position: relative;
        }

        .reviews h2::before {
            content: 'COMMENTS';
            position: absolute;
            top: -40px;
            left: 50%;
            transform: translateX(-50%);
            background: #ffffff;
            padding: 0 10px;
            font-size: 25px;
            color: #666;
        }

        .review-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            justify-content: center;
        }

        .review-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            position: relative;
        }

        .profile {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .profile img {
            border-radius: 50%;
            width: 50px;
            height: 50px;
            margin-right: 10px;
        }

        .profile .info {
            text-align: left;
        }

        .profile .info h3 {
            margin: 0;
            font-size: 1.2em;
        }

        .profile .info p {
            margin: 0;
            color: #777;
            font-size: 0.9em;
        }

        .review-text {
            margin: 15px 0;
            color: #333;
            font-size: 1em;
            line-height: 1.5em;
        }

        .rating {
            color: rgb(255, 225, 56); /* Golden color for stars */
            font-size: 1.9em;
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .rating span {
            margin-right: 2px;
        }

        @media (max-width: 768px) {
            .review-container {
                grid-template-columns: 1fr; /* Single column layout for tablets and mobile */
                gap: 10px;
            }

            .reviews h2 {
                font-size: 1.5em; /* Adjust title size for smaller screens */
            }

            .profile img {
                width: 40px; /* Slightly smaller profile images on mobile */
                height: 40px;
            }

            .profile .info h3 {
                font-size: 1em; /* Smaller text size for names on mobile */
            }

            .profile .info p {
                font-size: 0.8em; /* Smaller text size for handles on mobile */
            }

            .review-text {
                font-size: 0.9em; /* Adjust review text size for mobile */
            }

            .rating {
                font-size: 1em; /* Smaller rating stars on mobile */
            }
        }

        @media (max-width: 480px) {
            .reviews h2 {
                font-size: 1.2em; /* Further adjust title size for very small screens */
            }

            .profile img {
                width: 35px; /* Even smaller profile images on very small screens */
                height: 35px;
            }

            .profile .info h3 {
                font-size: 0.9em; /* Further reduce name text size */
            }

            .profile .info p {
                font-size: 0.7em; /* Further reduce handle text size */
            }

            .review-text {
                font-size: 0.8em; /* Further reduce review text size */
            }

            .rating {
                font-size: 0.8em; /* Even smaller rating stars */
            }
        }
    </style>
</head>
<body>
@include('layouts.sidebar')
<div class="allin">
    <section class="reviews">
        <h2>Clients Says</h2>
        <div class="review-container">
            <div class="review-card">
                <div class="rating">
                    <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                </div>
                <div class="profile">
                    <img src="../img/bg (1).jpg" alt="Touseeq Ijaz">
                    <div class="info">
                        <h3>Touseeq Ijaz</h3>
                        <p>@touseeqijazweb</p>
                    </div>
                </div>
                <p class="review-text">Lorem ipsum dolor sit amet consectetur adipiscing elit...</p>
            </div>
            <div class="review-card">
                <div class="rating">
                    <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                </div>
                <div class="profile">
                    <img src="../img/images (1).jpeg" alt="J.K Rowling">
                    <div class="info">
                        <h3>J.K Rowling</h3>
                        <p>@jkrowling</p>
                    </div>
                </div>
                <p class="review-text">Lorem ipsum dolor sit amet consectetur adipiscing elit...</p>
            </div>
            <div class="review-card">
                <div class="rating">
                    <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                </div>
                <div class="profile">
                    <img src="../img/mainImg.png" alt="Harry Potter">
                    <div class="info">
                        <h3>Harry Potter</h3>
                        <p>@DanielRedclief</p>
                    </div>
                </div>
                <p class="review-text">Lorem ipsum dolor sit amet consectetur adipiscing elit...</p>
            </div>
            <div class="review-card">
                <div class="rating">
                    <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                </div>
                <div class="profile">
                    <img src="../img/house.jpeg" alt="Oliva">
                    <div class="info">
                        <h3>Oliva</h3>
                        <p>@Olivaadward</p>
                    </div>
                </div>
                <p class="review-text">Lorem ipsum dolor sit amet consectetur adipiscing elit...</p>
            </div>
        </div>
    </section>
</div>
</body>
</html>
