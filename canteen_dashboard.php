<?php
// Simple Canteen Staff dashboard shown after successful login
// (In a real app, you would also check session here to make sure user is logged in.)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GreenPay - Canteen Dashboard</title>
    <link rel="stylesheet" href="assets/css/styles.css" />
    <style>
        /* Quick layout to match the mock dashboard */
        .gp-dashboard {
            display: flex;
            background-color: #f3f4f6;
            min-height: 100vh;
        }
        .gp-dashboard-left {
            width: 260px;
            background-color: #14532d;
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 16px 16px 24px;
        }
        .gp-dashboard-avatar-box {
            width: 100%;
            border: 8px solid #14532d; /* green frame around avatar block */
            background-color: #000;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 0;
            margin-bottom: 12px;
        }
        .gp-dashboard-avatar-head {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #fff;
            margin-bottom: 12px;
        }
        .gp-dashboard-avatar-body {
            width: 140px;
            height: 70px;
            background-color: #fff;
            border-top-left-radius: 140px;
            border-top-right-radius: 140px;
        }
        .gp-dashboard-role-bar {
            width: 100%;
            background-color: #f9fafb;
            color: #000;
            text-align: center;
            font-weight: 700;
            font-size: 18px;
            padding: 8px 0;
            margin-bottom: 40px;
        }
        .gp-dashboard-transaction-btn {
            width: 80%;
            border-radius: 9999px;
            background-color: #e5e7eb;
            color: #6b7280;
            text-align: center;
            font-style: italic;
            padding: 10px 0;
            font-weight: 600;
            border: none;
            cursor: pointer;
            margin-bottom: 12px;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .gp-dashboard-transaction-btn:hover {
            background-color: #d1d5db;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        .gp-dashboard-logout-btn {
            width: 80%;
            border-radius: 9999px;
            background-color: #dc2626;
            color: #fff;
            text-align: center;
            padding: 10px 0;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .gp-dashboard-logout-btn:hover {
            background-color: #b91c1c;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(220, 38, 38, 0.4);
        }
        .gp-dashboard-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .gp-dashboard-logo-wrapper {
            text-align: center;
        }
        .gp-dashboard-logo-text {
            font-size: 40px;
            font-weight: 800;
            color: #166534;
            letter-spacing: 3px;
            margin-top: 16px;
        }
        .gp-dashboard-transaction-panel {
            background-color: #ffffff;
            padding: 40px 24px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            border-radius: 4px;
            text-align: center;
            max-width: 340px;
            width: 100%;
        }
        .gp-dashboard-action-btn {
            display: block;
            width: 100%;
            border-radius: 9999px;
            margin: 0 auto 18px;
            padding: 14px 0;
            font-weight: 700;
            font-style: italic;
            border: none;
            cursor: pointer;
            text-decoration: underline;
        }
        .gp-dashboard-action-btn {
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .gp-dashboard-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        .gp-dashboard-action-btn--primary {
            background-color: #4ade80; /* light green */
            color: #166534;
        }
        .gp-dashboard-action-btn--primary:hover {
            background-color: #22c55e;
        }
        .gp-dashboard-action-btn--secondary {
            background-color: #e5e7eb;
            color: #9ca3af;
        }
        .gp-dashboard-action-btn--secondary:hover {
            background-color: #d1d5db;
        }

        /* Mobile layout tweaks */
        @media (max-width: 768px) {
            .gp-dashboard {
                flex-direction: column;
                min-height: 100vh;
            }
            .gp-dashboard-left {
                width: 100%;
                max-width: 420px;
                margin: 0 auto;
            }
            .gp-dashboard-right {
                width: 100%;
                padding: 16px;
            }
            .gp-dashboard-logo-text {
                font-size: 28px;
            }
            .gp-dashboard-transaction-panel {
                margin-top: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="gp-dashboard">
        <div class="gp-dashboard-left">
            <div class="gp-dashboard-avatar-box">
                <div class="gp-dashboard-avatar-head"></div>
                <div class="gp-dashboard-avatar-body"></div>
            </div>

            <div class="gp-dashboard-role-bar">Canteen Staff</div>

            <button class="gp-dashboard-transaction-btn" type="button" id="gpTransactionToggle">
                Transaction
            </button>

            <a href="index.php" style="width: 80%; text-decoration: none; margin-top: auto;">
                <button class="gp-dashboard-logout-btn" type="button">
                    Log Out
                </button>
            </a>
        </div>

        <div class="gp-dashboard-right">
            <!-- Default view: logo -->
            <div class="gp-dashboard-logo-wrapper" id="gpLogoView">
                <div style="font-size: 80px; font-weight: 900; color: #166534;">
                    G
                </div>
                <div class="gp-dashboard-logo-text">GREENPAY</div>
            </div>

            <!-- Transaction view: Student's Balance button -->
            <div class="gp-dashboard-transaction-panel" id="gpTransactionView" style="display: none;">
                <a href="canteen_search.php" style="text-decoration: none; display: block; width: 100%;">
                    <button class="gp-dashboard-action-btn gp-dashboard-action-btn--primary" type="button">
                        Student's Balance
                    </button>
                </a>
            </div>
        </div>
    </div>

    <script>
        // Toggle between logo view and transaction buttons when Transaction is clicked
        (function () {
            const toggleBtn = document.getElementById("gpTransactionToggle");
            const logoView = document.getElementById("gpLogoView");
            const transactionView = document.getElementById("gpTransactionView");

            if (!toggleBtn || !logoView || !transactionView) return;

            toggleBtn.addEventListener("click", function () {
                const showingTransactions = transactionView.style.display === "block";

                if (showingTransactions) {
                    transactionView.style.display = "none";
                    logoView.style.display = "block";
                } else {
                    logoView.style.display = "none";
                    transactionView.style.display = "block";
                }
            });
        })();
    </script>
</body>
</html>


