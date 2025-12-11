<?php
// Student balance view - shows transaction ledger for students
session_start();

// Set timezone to Philippine Time
date_default_timezone_set('Asia/Manila');

// Get student_id from session or URL
$studentId = $_SESSION['student_id'] ?? $_GET['student_id'] ?? '';

if (!$studentId) {
    header('Location: index.php');
    exit;
}

$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'greenpay';

$mysqli = @new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($mysqli->connect_errno) {
    die('Database connection failed: ' . htmlspecialchars($mysqli->connect_error));
}

// Set MySQL timezone to Philippine Time
$mysqli->query("SET time_zone = '+08:00'");

// Get student info
$student = null;
$stmt = $mysqli->prepare('SELECT id, first_name, middle_name, last_name, student_id, dob_password, image_path FROM students WHERE student_id = ? LIMIT 1');
if ($stmt) {
    $stmt->bind_param('s', $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $student = $result->fetch_assoc();
        // Concatenate full name
        $student['full_name'] = $student['first_name'];
        if ($student['middle_name']) {
            $student['full_name'] .= ' ' . $student['middle_name'];
        }
        $student['full_name'] .= ' ' . $student['last_name'];
    } else {
        header('Location: index.php');
        exit;
    }
    $stmt->close();
}

// Get current balance
$currentBalance = 0.00;
$stmt = $mysqli->prepare('SELECT balance FROM student_balances WHERE student_id = ? LIMIT 1');
if ($stmt) {
    $stmt->bind_param('s', $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $balanceRow = $result->fetch_assoc();
        $currentBalance = floatval($balanceRow['balance']);
    }
    $stmt->close();
}

// Get transaction history
$transactions = [];
$stmt = $mysqli->prepare('SELECT item_name, item_type, amount, transaction_date FROM transactions WHERE student_id = ? ORDER BY transaction_date DESC');
if ($stmt) {
    $stmt->bind_param('s', $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
    $stmt->close();
}

$mysqli->close();

// Calculate running balance for each transaction (work backwards from current balance)
$runningBalance = $currentBalance;
foreach ($transactions as &$trans) {
    $trans['balance_after'] = $runningBalance; // Balance after this transaction
    $runningBalance += floatval($trans['amount']); // Add back the deducted amount to get balance before
    $trans['balance_before'] = $runningBalance; // Balance before this transaction (amount available)
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GreenPay - My Balance</title>
    <link rel="stylesheet" href="assets/css/styles.css" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background-color: #ffffff;
            min-height: 100vh;
            display: flex;
        }
        .gp-balance-view {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }
        .gp-balance-left {
            width: 320px;
            background-color: #14532d;
            display: flex;
            flex-direction: column;
            padding: 24px;
            color: #fff;
        }
        .gp-balance-info-box {
            background-color: #ffffff;
            color: #000;
            padding: 16px;
            margin-bottom: 16px;
            border-radius: 4px;
        }
        .gp-balance-id {
            font-weight: 700;
            font-size: 18px;
            margin-bottom: 16px;
            text-align: center;
        }
        .gp-balance-avatar-box {
            background-color: #000000;
            padding: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            border-radius: 4px;
        }
        .gp-balance-avatar-icon {
            width: 120px;
            height: 120px;
            position: relative;
        }
        .gp-balance-avatar-head {
            width: 80px;
            height: 80px;
            background-color: #fff;
            border-radius: 50%;
            margin: 0 auto 12px;
        }
        .gp-balance-avatar-body {
            width: 100px;
            height: 50px;
            background-color: #fff;
            border-radius: 100px 100px 0 0;
            margin: 0 auto;
        }
        .gp-balance-name {
            font-weight: 700;
            font-size: 16px;
            text-align: center;
        }
        .gp-balance-mybalance-btn {
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
            margin-top: auto;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .gp-balance-mybalance-btn:hover {
            background-color: #22c55e;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(74, 222, 128, 0.3);
        }
        .gp-balance-right {
            flex: 1;
            background-color: #ffffff;
            padding: 24px;
            position: relative;
        }
        .gp-balance-logo {
            position: absolute;
            top: 24px;
            right: 24px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .gp-balance-logo-icon {
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
        .gp-balance-logo-text {
            font-size: 20px;
            font-weight: 800;
            color: #166534;
        }
        .gp-balance-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 80px;
            margin-bottom: 24px;
        }
        .gp-balance-table thead {
            background-color: #4ade80;
            color: #fff;
        }
        .gp-balance-table th {
            padding: 12px;
            text-align: left;
            font-weight: 700;
            font-size: 14px;
        }
        .gp-balance-table td {
            padding: 12px;
            background-color: #f3f4f6;
            color: #000;
            font-size: 14px;
        }
        .gp-balance-table tbody tr:empty {
            display: table-row;
        }
        .gp-balance-table tbody tr:empty td {
            height: 40px;
        }
        .gp-balance-back-btn {
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
        .gp-balance-back-btn:hover {
            background-color: #22c55e;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(74, 222, 128, 0.3);
        }
        @media (max-width: 768px) {
            .gp-balance-view {
                flex-direction: column;
            }
            .gp-balance-left {
                width: 100%;
            }
            .gp-balance-table {
                font-size: 12px;
            }
            .gp-balance-table th,
            .gp-balance-table td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="gp-balance-view">
        <div class="gp-balance-left">
            <div class="gp-balance-info-box">
                <div class="gp-balance-id"><?php echo htmlspecialchars($student['student_id']); ?></div>

                <div class="gp-balance-avatar-box">
                    <?php if ($student['image_path'] && file_exists(__DIR__ . '/' . $student['image_path'])): ?>
                        <img src="<?php echo htmlspecialchars($student['image_path']); ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    <?php else: ?>
                        <div class="gp-balance-avatar-icon">
                            <div class="gp-balance-avatar-head"></div>
                            <div class="gp-balance-avatar-body"></div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="gp-balance-name"><?php echo htmlspecialchars($student['full_name']); ?></div>
            </div>
        </div>

        <div class="gp-balance-right">
            <div class="gp-balance-logo">
                <div class="gp-balance-logo-icon">GP</div>
                <div class="gp-balance-logo-text">GREENPAY</div>
            </div>

            <table class="gp-balance-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Deduct</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #9ca3af;">No transactions yet</td>
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
                                <td><?php echo number_format($trans['balance_before'], 2); ?></td>
                                <td><?php echo number_format($trans['amount'], 2); ?></td>
                                <td><?php echo number_format($trans['balance_after'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <!-- Empty rows for visual spacing -->
                    <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td></tr>
                    <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td></tr>
                    <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td></tr>
                </tbody>
            </table>

            <a href="student_dashboard.php" class="gp-balance-back-btn">Back</a>
        </div>
    </div>
</body>
</html>

