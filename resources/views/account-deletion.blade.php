<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Account Deletion – Dream Mulk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #4a6fa5;
            --primary-dark: #385d8a;
            --secondary: #ff7e5f;
            --light: #f8f9fa;
            --dark: #343a40;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --success: #28a745;
            --warning: #ffc107;
            --transition: all 0.3s ease;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --radius: 12px;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        header {
            text-align: center;
            padding: 30px 0 20px;
            margin-bottom: 30px;
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .logo-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            box-shadow: var(--shadow);
        }

        .logo-text {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 32px;
            color: var(--primary-dark);
            letter-spacing: -0.5px;
        }

        h1 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 42px;
            color: var(--dark);
            margin-bottom: 15px;
            position: relative;
            display: inline-block;
        }

        h1:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--secondary);
            border-radius: 2px;
        }

        .tagline {
            font-size: 18px;
            color: var(--gray);
            max-width: 700px;
            margin: 0 auto 30px;
        }

        .main-content {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            margin-bottom: 40px;
        }

        .card {
            background: white;
            border-radius: var(--radius);
            padding: 30px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            flex: 1;
            min-width: 300px;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .card-primary {
            border-top: 5px solid var(--primary);
        }

        .card-warning {
            border-top: 5px solid var(--warning);
        }

        .card-success {
            border-top: 5px solid var(--success);
        }

        h2 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 24px;
            color: var(--primary-dark);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        h2 i {
            color: var(--primary);
        }

        h3 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 20px;
            color: var(--dark);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-box {
            background-color: var(--light-gray);
            border-left: 4px solid var(--primary);
            padding: 20px;
            border-radius: 0 var(--radius) var(--radius) 0;
            margin-bottom: 25px;
        }

        ul {
            list-style-type: none;
            padding-left: 0;
        }

        li {
            margin-bottom: 12px;
            padding-left: 30px;
            position: relative;
        }

        li:before {
            content: '\f058';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: var(--primary);
            position: absolute;
            left: 0;
            top: 2px;
        }

        .warning li:before {
            content: '\f06a';
            color: var(--warning);
        }

        .success li:before {
            content: '\f00c';
            color: var(--success);
        }

        .highlight {
            background-color: rgba(255, 126, 95, 0.1);
            color: var(--dark);
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 500;
        }

        .email-option {
            background-color: var(--light);
            border-radius: var(--radius);
            padding: 25px;
            margin-top: 20px;
            border: 1px dashed var(--primary);
            transition: var(--transition);
        }

        .email-option:hover {
            background-color: rgba(74, 111, 165, 0.05);
        }

        .email-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .email-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
        }

        .email-details {
            flex: 1;
        }

        .email-subject {
            font-weight: 600;
            color: var(--primary-dark);
            font-size: 18px;
        }

        .email-address {
            color: var(--gray);
            font-size: 16px;
        }

        .copy-btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 50px;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 15px;
        }

        .copy-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .data-items {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .data-item {
            background-color: var(--light);
            padding: 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: var(--transition);
        }

        .data-item:hover {
            background-color: rgba(74, 111, 165, 0.08);
            transform: translateX(5px);
        }

        .data-icon {
            width: 40px;
            height: 40px;
            background-color: rgba(74, 111, 165, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 18px;
        }

        .timeline {
            position: relative;
            padding-left: 30px;
            margin-top: 25px;
        }

        .timeline:before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background-color: var(--primary);
            border-radius: 3px;
        }

        .timeline-step {
            margin-bottom: 25px;
            position: relative;
        }

        .timeline-step:before {
            content: '';
            position: absolute;
            left: -36px;
            top: 5px;
            width: 15px;
            height: 15px;
            background-color: var(--primary);
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 0 0 3px rgba(74, 111, 165, 0.2);
        }

        .step-title {
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: 5px;
            font-size: 18px;
        }

        .step-desc {
            color: var(--gray);
        }

        footer {
            text-align: center;
            padding: 30px 0;
            color: var(--gray);
            border-top: 1px solid var(--light-gray);
            margin-top: 40px;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .footer-links a {
            color: var(--primary);
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: var(--secondary);
            text-decoration: underline;
        }

        .success-message {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: var(--success);
            color: white;
            padding: 15px 25px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 10px;
            transform: translateY(100px);
            opacity: 0;
            transition: var(--transition);
            z-index: 1000;
        }

        .success-message.show {
            transform: translateY(0);
            opacity: 1;
        }

        .immediate-processing {
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.1) 0%, rgba(40, 167, 69, 0.05) 100%);
            border: 1px solid rgba(40, 167, 69, 0.2);
            border-radius: var(--radius);
            padding: 20px;
            margin: 20px 0;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .immediate-icon {
            width: 50px;
            height: 50px;
            background-color: var(--success);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 22px;
            flex-shrink: 0;
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 32px;
            }

            .logo-text {
                font-size: 28px;
            }

            .main-content {
                flex-direction: column;
            }

            .card {
                min-width: 100%;
            }

            .data-items {
                grid-template-columns: 1fr;
            }

            .immediate-processing {
                flex-direction: column;
                text-align: center;
            }
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(255, 126, 95, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(255, 126, 95, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 126, 95, 0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-home"></i>
                </div>
                <div class="logo-text">Dream Mulk</div>
            </div>
            <h1>Account Deletion Request</h1>
            <p class="tagline">Your privacy matters to us. You can request deletion of your account and associated data at any time.</p>
        </header>

        <div class="main-content">
            <div class="card card-primary">
                <h2><i class="fas fa-user-minus"></i> How to Request Deletion</h2>
                <p>To initiate the account deletion process, please send us an email with the required information.</p>

                <div class="email-option">
                    <div class="email-header">
                        <div class="email-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="email-details">
                            <div class="email-subject">Account Deletion Request</div>
                            <div class="email-address">info@dreammulk.com</div>
                        </div>
                    </div>

                    <p>Please include the following in your email:</p>
                    <ul>
                        <li>Your registered email address</li>
                        <li>Your registered phone number</li>
                        <li>Reason for deletion (optional)</li>
                    </ul>

                    <button class="copy-btn pulse" id="copyEmailBtn">
                        <i class="fas fa-copy"></i> Copy Email Address
                    </button>
                </div>

                <div class="timeline">
                    <div class="timeline-step">
                        <div class="step-title">Send Request</div>
                        <div class="step-desc">Email us at info@dreammulk.com with your account details</div>
                    </div>
                    <div class="timeline-step">
                        <div class="step-title">Verification</div>
                        <div class="step-desc">We'll verify your identity to ensure account security</div>
                    </div>
                    <div class="timeline-step">
                        <div class="step-title">Immediate Processing</div>
                        <div class="step-desc">Your account deletion begins immediately after verification</div>
                    </div>
                    <div class="timeline-step">
                        <div class="step-title">Completion</div>
                        <div class="step-desc">Your account and all associated data are permanently deleted</div>
                    </div>
                </div>
            </div>

            <div class="card card-warning">
                <h2><i class="fas fa-database"></i> Data That Will Be Deleted</h2>
                <p>Upon successful deletion request, the following data associated with your account will be permanently removed from our systems:</p>

                <div class="data-items">
                    <div class="data-item">
                        <div class="data-icon">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div>
                            <div class="highlight">User Account & Profile</div>
                            <small>All your profile information</small>
                        </div>
                    </div>

                    <div class="data-item">
                        <div class="data-icon">
                            <i class="fas fa-address-card"></i>
                        </div>
                        <div>
                            <div class="highlight">Personal Information</div>
                            <small>Email, phone number, address</small>
                        </div>
                    </div>

                    <div class="data-item">
                        <div class="data-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div>
                            <div class="highlight">Saved Properties & Favorites</div>
                            <small>All your bookmarked properties</small>
                        </div>
                    </div>

                    <div class="data-item">
                        <div class="data-icon">
                            <i class="fas fa-history"></i>
                        </div>
                        <div>
                            <div class="highlight">Application Activity</div>
                            <small>Search history, interactions, etc.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-success">
                <h2><i class="fas fa-shield-alt"></i> Data Retention & Processing</h2>

                <div class="info-box">
                    <h3><i class="fas fa-info-circle"></i> Important Information</h3>
                    <p><b>No personal data is retained.</b> Once the deletion request is processed, your account and all associated personal data are permanently deleted from our servers.</p>
                </div>

                <div class="immediate-processing">
                    <div class="immediate-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div>
                        <h3>Immediate Processing</h3>
                        <p>Account deletion requests are processed <span class="highlight">immediately after verification</span> of ownership. There is no waiting period.</p>
                    </div>
                </div>

                <h3><i class="fas fa-undo-alt"></i> Cannot Be Undone</h3>
                <p>Please note that account deletion is a permanent action. Once your account is deleted, it cannot be recovered. If you wish to use Dream Mulk again, you will need to create a new account.</p>
            </div>
        </div>

        <footer>
            <p>Dream Mulk &copy; <span id="currentYear"></span>. All rights reserved.</p>
            <div class="footer-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Contact Us</a>
                <a href="#">FAQ</a>
            </div>
        </footer>

        <div class="success-message" id="successMessage">
            <i class="fas fa-check-circle"></i>
            <span>Email address copied to clipboard!</span>
        </div>
    </div>

    <script>
        // Set current year in footer
        document.getElementById('currentYear').textContent = new Date().getFullYear();

        // Copy email to clipboard functionality
        const copyEmailBtn = document.getElementById('copyEmailBtn');
        const successMessage = document.getElementById('successMessage');

        copyEmailBtn.addEventListener('click', function() {
            const email = 'info@dreammulk.com';

            // Copy to clipboard
            navigator.clipboard.writeText(email)
                .then(() => {
                    // Show success message
                    successMessage.classList.add('show');

                    // Change button text temporarily
                    const originalText = copyEmailBtn.innerHTML;
                    copyEmailBtn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                    copyEmailBtn.style.backgroundColor = 'var(--success)';

                    // Reset button after 2 seconds
                    setTimeout(() => {
                        copyEmailBtn.innerHTML = originalText;
                        copyEmailBtn.style.backgroundColor = '';
                        successMessage.classList.remove('show');
                    }, 2000);
                })
                .catch(err => {
                    console.error('Failed to copy: ', err);
                    alert('Failed to copy email to clipboard. Please copy manually: ' + email);
                });
        });

        // Animate cards on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Apply initial styles and observe cards
        document.querySelectorAll('.card').forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            card.style.transitionDelay = (index * 0.1) + 's';

            observer.observe(card);
        });

        // Add hover effect to data items
        document.querySelectorAll('.data-item').forEach(item => {
            item.addEventListener('mouseenter', function() {
                const icon = this.querySelector('.data-icon');
                icon.style.transform = 'rotate(15deg) scale(1.1)';
                icon.style.transition = 'transform 0.3s ease';
            });

            item.addEventListener('mouseleave', function() {
                const icon = this.querySelector('.data-icon');
                icon.style.transform = 'rotate(0) scale(1)';
            });
        });

        // Add pulse animation to immediate processing icon
        const immediateIcon = document.querySelector('.immediate-icon');
        setInterval(() => {
            immediateIcon.style.transform = 'scale(1.1)';
            setTimeout(() => {
                immediateIcon.style.transform = 'scale(1)';
            }, 300);
        }, 2000);
    </script>
</body>
</html>
