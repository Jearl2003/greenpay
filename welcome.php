<?php
// Welcome/Splash page for GreenPay
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GreenPay - Welcome</title>
    <link rel="stylesheet" href="assets/css/styles.css" />
    <style>
        .gp-welcome-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 16px;
            position: relative;
        }

        .gp-welcome-content {
            text-align: center;
            z-index: 1;
            animation: fadeInUp 0.8s ease-out;
        }

        .gp-welcome-title {
            font-size: 100px;
            font-weight: 700;
            color:rgba(22, 163, 74, 0.9);
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.5),
                         0 0 20px rgba(0, 0, 0, 0.3);
            margin-bottom: 24px;
            letter-spacing: 2px;
        }

        .gp-welcome-subtitle {
            font-size: 15px;
            font-weight: 400;
            color: rgba(255, 255, 255, 0.95);
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.5);
            margin-bottom: 48px;
            letter-spacing: 1px;
        }

        .gp-welcome-btn {
            background: rgba(255, 255, 255, 0.9);
            color: #1f2933;
            border: none;
            border-radius: 24px;
            padding: 16px 48px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
            text-decoration: none;
            display: inline-block;
        }

        .gp-welcome-btn:hover {
            background: rgba(22, 163, 74, 0.9);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        .gp-welcome-btn:active {
            transform: translateY(0);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .gp-welcome-title {
                font-size: 36px;
                letter-spacing: 1px;
            }

            .gp-welcome-subtitle {
                font-size: 16px;
            }

            .gp-welcome-btn {
                padding: 14px 40px;
                font-size: 16px;
            }
        }

        @media (max-width: 480px) {
            .gp-welcome-title {
                font-size: 28px;
            }

            .gp-welcome-subtitle {
                font-size: 14px;
                margin-bottom: 36px;
            }

            .gp-welcome-btn {
                padding: 12px 32px;
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="gp-page-bg"></div>
    <div class="gp-welcome-container">
        <div class="gp-welcome-content">
            <h1 class="gp-welcome-title">WELCOME TO GREENPAY</h1>
            <p class="gp-welcome-subtitle">Note: Only DLSJBC students can use this system</p>
            <a href="index.php" class="gp-welcome-btn">Get Started</a>
        </div>
    </div>
</body>
</html>

