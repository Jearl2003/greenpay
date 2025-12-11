<?php
// Simple page showing Search and Student's Balance buttons for canteen staff
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GreenPay - Canteen Transactions</title>
    <link rel="stylesheet" href="assets/css/styles.css" />
    <style>
        body {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background-color: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .gp-transaction-card {
            text-align: center;
        }
        .gp-transaction-logo-letter {
            font-size: 72px;
            font-weight: 900;
            color: #166534;
            margin-bottom: 4px;
        }
        .gp-transaction-logo-text {
            font-size: 32px;
            font-weight: 800;
            color: #166534;
            letter-spacing: 6px;
            margin-bottom: 40px;
        }
        .gp-transaction-btn {
            display: block;
            width: 260px;
            border-radius: 9999px;
            margin: 0 auto 18px;
            padding: 14px 0;
            font-weight: 700;
            font-style: italic;
            border: none;
            cursor: pointer;
            text-decoration: underline;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .gp-transaction-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        .gp-transaction-btn--primary {
            background-color: #4ade80; /* light green */
            color: #166534;
        }
        .gp-transaction-btn--primary:hover {
            background-color: #22c55e;
        }
        .gp-transaction-btn--secondary {
            background-color: #e5e7eb;
            color: #9ca3af;
        }
        .gp-transaction-btn--secondary:hover {
            background-color: #d1d5db;
        }
        .gp-transaction-back {
            margin-top: 8px;
            font-size: 14px;
        }
        .gp-transaction-back a {
            color: #166534;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease, transform 0.2s ease;
        }
        .gp-transaction-back a:hover {
            color: #15803d;
            text-decoration: underline;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="gp-transaction-card">
        <div class="gp-transaction-logo-letter">G</div>
        <div class="gp-transaction-logo-text">GREENPAY</div>

        <button class="gp-transaction-btn gp-transaction-btn--primary" type="button">
            Search
        </button>
        <button class="gp-transaction-btn gp-transaction-btn--secondary" type="button">
            Student's Balance
        </button>
        <div class="gp-transaction-back">
            <a href="canteen_dashboard.php">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>


