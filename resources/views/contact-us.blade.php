<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us</title>
    <style>
        /* Global styles */


        
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #dedee0;
opacity: 1;
background-image:  repeating-radial-gradient( circle at 0 0, transparent 0, #dedee0 9px ), repeating-linear-gradient( #45e5f755, #45e5f7 );
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
        .centered-content {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100vh;
        }
        
        .container {
    max-width: 2000px; /* Increased maximum width */
    padding: 40px; /* Increased padding */
    background-color: rgba(255, 255, 255, 0.7); /* Set opacity to 70% */
    border-radius: 12px; /* Increased border radius */
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.2); /* Adjusted box shadow */
}


        h1 {
            text-align: center;
            color: #333;
        }

        .info {
            max-width: 2000px; /* Increased maximum width */
    padding: 40px; /* Increased padding */
    background-color: rgba(255, 255, 255, 0.7); /* Set opacity to 70% */
    border-radius: 12px; /* Increased border radius */
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.2); /* Adjusted box shadow */
        }

        .info p {
            margin-bottom: 10px;
        }

        .info p:last-child {
            margin-bottom: 0;
        }

        .info p strong {
            color: #555;
        }

        .info p a {
            color: #007bff;
            text-decoration: none;
        }

        .info p a:hover {
            text-decoration: underline;
        }
        .black-navbar #header #navbar a,
      .black-navbar #header #navbar a:focus {
        color: #000; /* Set the desired text color */
      }

      .black-navbar #header {
        background: #fff; /* Set the desired background color */
      }
    </style>
  </head>
  <body class="black-navbar">

      @include('navbar')

<div class="centered-content">
    <div class="container">
        <h1>contact us</h1>
        <div class="info">
            <p><strong>Email:</strong> <a href="kurdmulk.gmail.com">kurdmulk.gmail.com</a></p>
            <p><strong>Phone:</strong> <a href="tel:07701231216">07701231216</a></p>
         
            
        </div>
    </div>
</div>

</body>
</html>
