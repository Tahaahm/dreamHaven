<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Profile</title>
    <style>
        /* Reset some default browser styles */
        body {
            background-color: #fff;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
          
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
        .allin {
            
            margin-top: 10px;
            margin-left: 225px;
        }
        /* Container for the entire profile page */
        .profile-page {
            width: 90%;
            max-width: 1700px;
            margin: auto;
            background-color: none;
            border-radius: 8px;
            overflow: hidden;
            
        }

        /* Header section styling */
        .header {
            position: relative;
        }

        .background-img {
            width: 100%;
            height: 250px;
            background-size: cover;
            background-position: center;
        }

        .profile-info {
            position: absolute;
            top: 170px;
            left: 20px;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .profile-photo-container {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            overflow: hidden;
            border: 5px solid white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            margin-bottom: 90px;
        }

        .profile-photo {
    width: 100px; /* Adjust based on the desired size */
    height: 100px; /* Adjust based on the desired size */
    object-fit: cover;
    image-rendering: -webkit-optimize-contrast; /* Improve rendering on Webkit-based browsers */
    image-rendering: crisp-edges; /* Try different rendering modes */
}


      /* Container for profile information */
.profile-info {
    margin-top: 40px;
}

/* Fixed width for the username container */
.username {
    width: 200px; /* Set a fixed width */
    white-space: nowrap; /* Prevent text wrapping */
    overflow: hidden; /* Hide overflow if the text is too long */
    text-overflow: ellipsis; /* Add ellipsis (...) if text overflows */
    font-size: 24px;
    margin-bottom: 85px;
    margin-left: -10px;
}

/* Role styling */
.role {
    margin-left: -220px;
    margin-top: 10px;
    font-size: 16px;
    color: gray;
}

/* Edit button styling */
.edit-btn {
    display: inline-flex; /* Align icon and text horizontally */
    align-items: center; /* Center items vertically */
    text-decoration: underline; /* Underline the text */
    text-underline-offset: 3px; /* Adjust distance between underline and text */
    margin-left: 1285px;
    margin-bottom: 86px; 
    background: none;
    padding-left: 8px;
    color: #0056b3;
    height: 25px;
    border: none;
    border-radius: 15px;
    cursor: pointer;
    transition: 0.4s;
    width: 55px;
}

/* Icon inside the edit button */
.edit-btn i {
    margin-top: 1px;
    margin-right: 0px;
    /* Space between icon and text */
}

/* Hover effect for the edit button */
.edit-btn:hover {
    border-radius: 15px;
    width: 65px;
}


        /* Social icons styling */
        .social-icons {
            display: flex;
            justify-content: center;
            gap: 10px;
            padding: 20px 0;
        }

        .social-icons a img {
            width: 40px;
            height: 40px;
        }

        .info-section {
            padding: 20px;
        }

        .info-card {
            background-color: none;
            border-radius: 8px;
           
            padding: 20px;
        }

        .info-card h2 {
            font-size: 18px;
            margin-bottom: 20px;
        }

        .info-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 20px; /* Reduced gap */
}


        .info-item {
            flex: 1 1 calc(33.333% - 20px); /* 3 columns with gap */
            box-sizing: border-box;
        }

        .info-item label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

       
    .info-item span {
        padding-left: 5px;
        width: 290px;
        display: flex;          /* Use flexbox layout */
        align-items: center;    /* Center content vertically */
        text-align: left;       /* Align text to the left */
        color: #333;
        height: 45px;
        box-shadow: 0px 0px 3px rgba(133, 133, 133, 0.4);
        border-radius: 7px;
    }



        @media (max-width: 768px) {
            .info-item {
                flex: 1 1 calc(50% - 20px); /* 2 columns on smaller screens */
            }
        }

        @media (max-width: 480px) {
            .info-item {
                flex: 1 1 100%; /* 1 column on very small screens */
            }
        }
    </style>
</head>
<body>

@include('layouts.sidebar')

<div class="allin">
    <div class="profile-page">
        <div class="header">
        <div class="background-img" style="background-image: url('{{ asset($backgroundImage ?? 'images/AdobeStock_565645717.jpeg') }}');"></div>

            <div class="profile-info">
                <div class="profile-photo-container">
                <img src="{{ asset($user->image ? $user->image : 'property_images/IMG_0697.JPG') }}" 
     srcset="{{ asset($user->image) }} 1x, {{ asset($user->image) }} 2x" 
     alt="Profile Photo" 
     class="profile-photo">

                </div>


                <h1 class="username">{{ $user->name }}</h1>
<p class="role">{{ $user->role }}</p>




<button class="edit-btn" onclick="window.location.href='{{ route('profile.edit', ['id' => $user->user_id]) }}'">
    <i class="fas fa-edit"></i> Edit
</button>












            </div>
        </div>
        <div class="social-icons">
            @if($user->facebook)
                <a href="{{ $user->facebook }}" target="_blank"><img src="{{ asset('icons/facebook.png') }}" alt="Facebook"></a>
            @endif
            @if($user->twitter)
                <a href="{{ $user->twitter }}" target="_blank"><img src="{{ asset('icons/twitter.png') }}" alt="Twitter"></a>
            @endif
            @if($user->instagram)
                <a href="{{ $user->instagram }}" target="_blank"><img src="{{ asset('icons/instagram.png') }}" alt="Instagram"></a>
            @endif
            @if($user->website)
                <a href="{{ $user->website }}" target="_blank"><img src="{{ asset('icons/website.png') }}" alt="Website"></a>
            @endif
        </div>
        <div class="info-section">
            <div class="info-card">
                <h2>Staff Member Information</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Email:</label>
                        <span>{{ $user->email }}</span>
                    </div>
                    <div class="info-item">
                        <label>Phone:</label>
                        <span>{{ $user->phone }}</span>
                    </div>
                    <div class="info-item">
                        <label>City:</label>
                        <span>{{ $user->city }}</span>
                    </div>
                    <div class="info-item">
                        <label>State:</label>
                        <span>{{ $user->state }}</span>
                    </div>
                    <div class="info-item">
                        <label>Zip Code:</label>
                        <span>{{ $user->zip_code }}</span>
                    </div>
                    <div class="info-item">
                        <label>Address:</label>
                        <span>{{ $user->address }}</span>
                    </div>
                    <div class="info-item">
                        <label>Bio:</label>
                        <span>{{ $user->bio }}</span>
                    </div>
                    <div class="info-item">
                        <label>User Type:</label>
                        <span>{{ $user->role }}</span>
                    </div>
                    <div class="info-item">
                        <label>Account Status:</label>
                        <span>Account Granted</span> <!-- Update this dynamically if needed -->
                    </div>
                    <div class="info-item">
                        <label>Two Factor Authentication:</label>
                        <span>Not Set</span> <!-- Update this dynamically if needed -->
                    </div>
                    <div class="info-item">
                        <label>website:</label>
                        <span>{{ $user->website }}</span>
                    </div>
                    <div class="info-item">
                        <label>facebook:</label>
                        <span>{{ $user->facebook }}</span>
                    </div>
                    <div class="info-item">
                        <label>twitter:</label>
                        <span>{{ $user->twitter }}</span>
                    </div>
                    <div class="info-item">
                        <label>instagram:</label>
                        <span>{{ $user->instagram }}</span>
                    </div>
                      <div class="info-item">
                        <label>instagram:</label>
                        <span>{{ $user->instagram }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>