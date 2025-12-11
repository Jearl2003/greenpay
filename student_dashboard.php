<?php
// Student dashboard - shown after successful login
session_start();

// Set timezone to Philippine Time
date_default_timezone_set('Asia/Manila');

// Get student info from session or redirect to login
$studentId = $_SESSION['student_id'] ?? '';
$studentName = $_SESSION['student_name'] ?? '';
$studentDob = $_SESSION['student_dob'] ?? '';
$studentImage = $_SESSION['student_image'] ?? null;

if (!$studentId) {
    header('Location: index.php');
    exit;
}

// Get balance from database
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'greenpay';

$mysqli = @new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($mysqli->connect_errno) {
    $balance = 0.00;
    $transactions = [];
} else {
    // Set MySQL timezone to Philippine Time
    $mysqli->query("SET time_zone = '+08:00'");
    $balance = 0.00;
    $stmt = $mysqli->prepare('SELECT balance FROM student_balances WHERE student_id = ? LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('s', $studentId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $balanceRow = $result->fetch_assoc();
            $balance = floatval($balanceRow['balance']);
        }
        $stmt->close();
    }
    
    // Get transaction history (exclude deposits)
    $transactions = [];
    $stmt = $mysqli->prepare('SELECT item_name, item_type, quantity, amount, transaction_date FROM transactions WHERE student_id = ? AND item_type != ? ORDER BY transaction_date DESC');
    if ($stmt) {
        $depositType = 'Deposit';
        $stmt->bind_param('ss', $studentId, $depositType);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }
        $stmt->close();
    }
    
    // Calculate running balance for each transaction
    $runningBalance = $balance;
    foreach ($transactions as &$trans) {
        $trans['balance_after'] = $runningBalance;
        $runningBalance += floatval($trans['amount']);
        $trans['balance_before'] = $runningBalance;
    }
    
    $mysqli->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GreenPay - Student Dashboard</title>
    <link rel="stylesheet" href="assets/css/styles.css" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background-color: #f3f4f6;
            min-height: 100vh;
            display: flex;
        }
        .gp-student-dashboard {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }
        .gp-student-left {
            width: 320px;
            background-color: #14532d;
            border-right: 2px solid #16a34a;
            display: flex;
            flex-direction: column;
            padding: 24px;
            color: #fff;
        }
        .gp-student-id-box {
            background-color: #ffffff;
            color: #000;
            text-align: center;
            padding: 12px;
            font-weight: 700;
            font-size: 18px;
            margin-bottom: 24px;
            border-radius: 4px;
        }
        .gp-student-avatar-box {
            background-color: #000000;
            padding: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            border-radius: 4px;
        }
        .gp-student-avatar-icon {
            width: 120px;
            height: 120px;
            position: relative;
        }
        .gp-student-avatar-head {
            width: 80px;
            height: 80px;
            background-color: #fff;
            border-radius: 50%;
            margin: 0 auto 12px;
        }
        .gp-student-avatar-body {
            width: 100px;
            height: 50px;
            background-color: #fff;
            border-radius: 100px 100px 0 0;
            margin: 0 auto;
        }
        .gp-student-name-box {
            background-color: #ffffff;
            color: #000;
            text-align: center;
            padding: 12px;
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 16px;
            border-radius: 4px;
        }
        .gp-student-balance-btn {
            background-color: #4ade80;
            color: #166534;
            border: 2px solid #16a34a;
            border-radius: 9999px;
            padding: 12px 24px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            display: block;
            text-align: center;
            margin-bottom: 12px;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .gp-student-balance-btn:hover {
            background-color: #22c55e;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(34, 197, 94, 0.3);
        }
        .gp-student-logout-btn {
            background-color: #dc2626;
            color: #fff;
            border: 2px solid #991b1b;
            border-radius: 9999px;
            padding: 12px 24px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            display: block;
            text-align: center;
            margin-top: auto;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .gp-student-logout-btn:hover {
            background-color: #b91c1c;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(220, 38, 38, 0.4);
        }
        .gp-student-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            background-color: #ffffff;
            overflow: hidden;
        }
        .gp-student-logo-background {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 800"><rect fill="%23e0f2fe" width="1200" height="800"/><rect fill="%23bae6fd" x="0" y="400" width="1200" height="400"/></svg>');
            background-size: cover;
            background-position: center;
            filter: blur(8px);
            z-index: 1;
        }
        .gp-student-logo-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            z-index: 10;
        }
        .gp-student-logo-g {
            font-size: 120px;
            font-weight: 900;
            color: #166534;
            line-height: 1;
            margin-bottom: 16px;
        }
        .gp-student-logo-text {
            font-size: 48px;
            font-weight: 800;
            color: #166534;
            letter-spacing: 8px;
        }
        .gp-student-balance-table-container {
            display: none;
            padding: 24px;
            width: 100%;
            height: 100%;
            position: relative;
            background-color: #ffffff;
            z-index: 20;
        }
        .gp-student-balance-table-container.active {
            display: block;
        }
        .gp-student-balance-logo-header {
            position: absolute;
            top: 24px;
            right: 24px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .gp-student-balance-logo-icon {
            width: 32px;
            height: 32px;
            background-color: #16a34a;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 14px;
        }
        .gp-student-balance-logo-text {
            font-size: 20px;
            font-weight: 800;
            color: #166534;
        }
        .gp-student-balance-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 80px;
            margin-bottom: 24px;
        }
        .gp-student-balance-table thead {
            background-color: #4ade80;
            color: #fff;
        }
        .gp-student-balance-table th {
            padding: 12px;
            text-align: left;
            font-weight: 700;
            font-size: 14px;
        }
        .gp-student-balance-table th,
        .gp-student-balance-table td {
            padding: 12px;
            background-color: #f3f4f6;
            color: #000;
            font-size: 14px;
            border: 1px solid #000;
        }
        .gp-student-balance-table tbody tr:empty {
            display: table-row;
        }
        .gp-student-balance-table tbody tr:empty td {
            height: 40px;
        }
        .gp-student-balance-back-btn {
            position: absolute;
            bottom: 24px;
            right: 24px;
            background-color: #4ade80;
            color: #166534;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .gp-student-balance-back-btn:hover {
            background-color: #22c55e;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(74, 222, 128, 0.3);
        }
        @media (max-width: 768px) {
            .gp-student-dashboard {
                flex-direction: column;
            }
            .gp-student-left {
                width: 100%;
                border-right: none;
                border-bottom: 2px solid #16a34a;
            }
            .gp-student-right {
                min-height: 400px;
            }
        }
    </style>
</head>
<body>
    <?php
    // Check for recent balance addition notification
    $showNotification = false;
    $notificationAmount = 0;
    if (!empty($transactions)) {
        foreach ($transactions as $trans) {
            if ($trans['item_type'] === 'Deposit') {
                $showNotification = true;
                $notificationAmount = $trans['amount'];
                break; // Show only the latest one
            }
        }
    }
    ?>

    <?php if ($showNotification): ?>
        <div id="balanceNotification" class="gp-notification" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: #dcfce7; color: #166534; padding: 20px 40px; border-radius: 8px; font-weight: 600; font-size: 18px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); z-index: 1000; text-align: center;">
            You received ₱<?php echo number_format($notificationAmount, 2); ?> balance addition!
        </div>
    <?php endif; ?>

    <div class="gp-student-dashboard">
        <div class="gp-student-left">
            <div class="gp-student-id-box">
                <?php echo htmlspecialchars($studentId); ?>
            </div>

            <div class="gp-student-avatar-box">
                <?php if ($studentImage && file_exists(__DIR__ . '/' . $studentImage)): ?>
                    <img src="<?php echo htmlspecialchars($studentImage); ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px;">
                <?php else: ?>
                    <div class="gp-student-avatar-icon">
                        <div class="gp-student-avatar-head"></div>
                        <div class="gp-student-avatar-body"></div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="gp-student-name-box">
                <?php echo htmlspecialchars($studentName); ?>
            </div>

            <div class="gp-student-name-box" style="font-size: 14px; font-weight: 400; margin-bottom: 16px;">
                Date of Birth: <?php echo htmlspecialchars($studentDob); ?>
            </div>

            <div class="gp-student-name-box" style="font-size: 14px; font-weight: 400; margin-bottom: 32px;">
                Current Balance: <span style="color: <?php echo ($balance <= 100) ? '#dc2626' : '#16a34a'; ?>;">₱ <?php echo number_format($balance, 2); ?></span>
            </div>

            <button type="button" class="gp-student-balance-btn" id="myBalanceBtn" style="margin-bottom: 16px;">
                My History 
            </button>

            <a href="logout.php" class="gp-student-logout-btn">
                Log Out
            </a>
        </div>

        <div class="gp-student-right">
            <!-- Blurred background for logo view -->
            <div class="gp-student-logo-background" id="logoBackground"></div>
            
            <!-- Default view: logo -->
            <div class="gp-student-logo-overlay" id="logoView">
                <div class="gp-student-logo-g">G</div>
                <div class="gp-student-logo-text">GREENPAY</div>
            </div>

            <!-- Balance view: transaction table -->
            <div class="gp-student-balance-table-container" id="balanceView">
                <div class="gp-student-balance-logo-header">
                    <div class="gp-student-balance-logo-icon">GP</div>
                    <div class="gp-student-balance-logo-text">GREENPAY</div>
                </div>

                <table class="gp-student-balance-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Description</th>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Amount</th>
                            <th>Deduct</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transactions)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: #9ca3af;">No transactions yet</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($transactions as $trans): ?>
                                <tr>
                                    <td><?php 
                                        // Display Philippine time (already stored in PH time)
                                        $dateTime = new DateTime($trans['transaction_date'], new DateTimeZone('Asia/Manila'));
                                        echo $dateTime->format('m/d/y') . ' ' . $dateTime->format('g:i A');
                                    ?></td>
                                    <td><?php echo htmlspecialchars($trans['item_type']); ?></td>
                                    <td><?php echo htmlspecialchars($trans['item_name']); ?></td>
                                    <td><?php echo htmlspecialchars($trans['quantity']); ?></td>
                                    <td><?php echo number_format($trans['balance_before'], 2); ?></td>
                                    <td><?php echo number_format($trans['amount'], 2); ?></td>
                                    <td><?php echo number_format($trans['balance_after'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <!-- Empty rows for visual spacing -->
                        <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                        <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                        <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                    </tbody>
                </table>

                <button type="button" class="gp-student-balance-back-btn" id="backToLogoBtn">Back</button>
            </div>
        </div>
    </div>

    <script>
        // Auto-hide balance notification after 2 seconds
        const notification = document.getElementById('balanceNotification');
        if (notification) {
            setTimeout(function() {
                notification.style.display = 'none';
            }, 2000);
        }

        // Toggle between logo view and balance/transaction view
        (function() {
            const myBalanceBtn = document.getElementById('myBalanceBtn');
            const logoView = document.getElementById('logoView');
            const balanceView = document.getElementById('balanceView');
            const backToLogoBtn = document.getElementById('backToLogoBtn');

            if (!myBalanceBtn || !logoView || !balanceView || !backToLogoBtn) return;

            myBalanceBtn.addEventListener('click', function() {
                logoView.style.display = 'none';
                document.getElementById('logoBackground').style.display = 'none';
                balanceView.classList.add('active');
            });

            backToLogoBtn.addEventListener('click', function() {
                balanceView.classList.remove('active');
                logoView.style.display = 'block';
                document.getElementById('logoBackground').style.display = 'block';
            });
        })();
    </script>
</body>
</html>

