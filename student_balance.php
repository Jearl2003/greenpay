<?php
// Student balance and transaction management page
// Set timezone to Philippine Time
date_default_timezone_set('Asia/Manila');

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

// Get student_id from URL
$studentId = $_GET['student_id'] ?? '';

if (!$studentId) {
    header('Location: canteen_search.php');
    exit;
}

// Get student info
$student = null;
$stmt = $mysqli->prepare('SELECT id, first_name, middle_name, last_name, student_id, dob_password FROM students WHERE student_id = ? LIMIT 1');
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
        header('Location: canteen_search.php');
        exit;
    }
    $stmt->close();
}

// Initialize balance if not exists
$currentBalance = 0.00;
$stmt = $mysqli->prepare('SELECT balance FROM student_balances WHERE student_id = ? LIMIT 1');
if ($stmt) {
    $stmt->bind_param('s', $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $balanceRow = $result->fetch_assoc();
        $currentBalance = floatval($balanceRow['balance']);
    } else {
        // Create initial balance record
        $stmt->close();
        $insert = $mysqli->prepare('INSERT INTO student_balances (student_id, balance) VALUES (?, 0.00)');
        if ($insert) {
            $insert->bind_param('s', $studentId);
            $insert->execute();
            $insert->close();
        }
    }
    if ($stmt) $stmt->close();
}

$message = '';
$messageType = '';

// Handle Add Balance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_balance') {
    $amount = floatval($_POST['amount'] ?? 0);
    if ($amount > 0) {
        $newBalance = $currentBalance + $amount;
        $stmt = $mysqli->prepare('UPDATE student_balances SET balance = ? WHERE student_id = ?');
        if ($stmt) {
            $stmt->bind_param('ds', $newBalance, $studentId);
            if ($stmt->execute()) {
                $currentBalance = $newBalance;
                
                // Record transaction with Philippine time
                $phTime = date('Y-m-d H:i:s'); // Current Philippine time
                $insertTrans = $mysqli->prepare('INSERT INTO transactions (student_id, item_name, item_type, quantity, amount, transaction_date) VALUES (?, ?, ?, ?, ?, ?)');
                if ($insertTrans) {
                    $itemName = 'Balance Added';
                    $itemType = 'Deposit';
                    $quantity = 1;
                    $insertTrans->bind_param('sssids', $studentId, $itemName, $itemType, $quantity, $amount, $phTime);
                    $insertTrans->execute();
                    $insertTrans->close();
                }
                
                $message = 'Balance added successfully!';
                $messageType = 'success';
            } else {
                $message = 'Failed to add balance.';
                $messageType = 'error';
            }
            $stmt->close();
        }
    }
}

// Handle Purchase
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'purchase') {
    $purchaseAmount = floatval($_POST['amount'] ?? 0);
    $itemName = trim($_POST['item_name'] ?? '');
    $quantity = intval($_POST['quantity'] ?? 1);
    $itemType = trim($_POST['item_type'] ?? '');
    
    if ($purchaseAmount > 0 && $itemName !== '' && $quantity > 0) {
        if ($currentBalance >= $purchaseAmount) {
            $newBalance = $currentBalance - $purchaseAmount;
            
            // Update balance
            $stmt = $mysqli->prepare('UPDATE student_balances SET balance = ? WHERE student_id = ?');
            if ($stmt) {
                $stmt->bind_param('ds', $newBalance, $studentId);
                if ($stmt->execute()) {
                    $currentBalance = $newBalance;
                    
                    // Record transaction with Philippine time
                    $phTime = date('Y-m-d H:i:s'); // Current Philippine time
                    $insertTrans = $mysqli->prepare('INSERT INTO transactions (student_id, item_name, item_type, quantity, amount, transaction_date) VALUES (?, ?, ?, ?, ?, ?)');
                    if ($insertTrans) {
                        $insertTrans->bind_param('sssids', $studentId, $itemName, $itemType, $quantity, $purchaseAmount, $phTime);
                        $insertTrans->execute();
                        $insertTrans->close();
                    }
                    
                    $message = 'Purchase completed successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Failed to process purchase.';
                    $messageType = 'error';
                }
                $stmt->close();
            }
        } else {
            $message = 'Insufficient balance.';
            $messageType = 'error';
        }
    } else {
        $message = 'Please fill in all purchase fields.';
        $messageType = 'error';
    }
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GreenPay - Student Balance</title>
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
            padding: 16px;
        }
        .gp-balance-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding: 0 8px;
        }
        .gp-balance-logo {
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
        }
        .gp-balance-logo-text {
            font-size: 20px;
            font-weight: 800;
            color: #166534;
        }
        .gp-balance-id-display {
            background-color: #4ade80;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            color: #166534;
        }
        .gp-balance-section {
            margin-bottom: 24px;
        }
        .gp-balance-section-header {
            background-color: #14532d;
            color: #fff;
            padding: 12px 16px;
            font-weight: 700;
            font-size: 16px;
            border-radius: 8px 8px 0 0;
        }
        .gp-balance-section-content {
            background-color: #ffffff;
            padding: 16px;
            border: 2px solid #14532d;
            border-top: none;
            border-radius: 0 0 8px 8px;
        }
        .gp-balance-info-row {
            margin-bottom: 12px;
            font-size: 16px;
        }
        .gp-balance-label {
            font-weight: 600;
            color: #000;
        }
        .gp-balance-value {
            color: #166534;
            font-weight: 700;
        }
        .gp-balance-add-btn {
            background-color: #16a34a;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 12px;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .gp-balance-add-btn:hover {
            background-color: #15803d;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(22, 163, 74, 0.3);
        }
        .gp-balance-add-form {
            display: none;
            margin-top: 12px;
            padding: 12px;
            background-color: #f3f4f6;
            border-radius: 8px;
        }
        .gp-balance-add-form.active {
            display: block;
        }
        .gp-balance-input-group {
            margin-bottom: 12px;
        }
        .gp-balance-input {
            width: 100%;
            padding: 8px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
        }
        .gp-balance-purchase-btn {
            background-color: #16a34a;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 12px;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .gp-balance-purchase-btn:hover {
            background-color: #15803d;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(22, 163, 74, 0.3);
        }
        .gp-balance-purchase-form {
            display: none;
            margin-top: 12px;
        }
        .gp-balance-purchase-form.active {
            display: block;
        }
        .gp-balance-type-buttons {
            display: flex;
            gap: 12px;
            margin-bottom: 12px;
        }
        .gp-balance-type-btn {
            flex: 1;
            background-color: #4ade80;
            color: #166534;
            border: none;
            border-radius: 8px;
            padding: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .gp-balance-type-btn:hover {
            background-color: #22c55e;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(74, 222, 128, 0.3);
        }
        .gp-balance-type-btn.active {
            background-color: #16a34a;
            color: #fff;
        }
        .gp-balance-type-btn.active:hover {
            background-color: #15803d;
        }
        .gp-balance-pay-btn {
            background-color: #9ca3af;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 24px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 12px;
            width: 100%;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .gp-balance-pay-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .gp-balance-pay-btn.active {
            background-color: #16a34a;
        }
        .gp-balance-pay-btn.active:hover {
            background-color: #15803d;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(22, 163, 74, 0.4);
        }
        .gp-balance-deduction {
            color: #dc2626;
            font-weight: 700;
        }
        .gp-balance-back-btn {
            background-color: #4ade80;
            color: #166534;
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .gp-balance-back-btn:hover {
            background-color: #22c55e;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(74, 222, 128, 0.3);
        }
        .gp-balance-message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-weight: 600;
        }
        .gp-balance-message.success {
            background-color: #dcfce7;
            color: #166534;
        }
        .gp-balance-message.error {
            background-color: #fee2e2;
            color: #dc2626;
        }
        @media (max-width: 768px) {
            .gp-balance-header {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="gp-balance-header">
        <div class="gp-balance-logo">
            <div class="gp-balance-logo-icon">🔍</div>
            <div class="gp-balance-logo-text">GREENPAY</div>
        </div>
        <div class="gp-balance-id-display"><?php echo htmlspecialchars($studentId); ?></div>
    </div>

    <?php if ($message): ?>
        <div id="popupMessage" class="gp-popup-message <?php echo $messageType; ?>" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: <?php echo $messageType === 'success' ? '#dcfce7' : '#fee2e2'; ?>; color: <?php echo $messageType === 'success' ? '#166534' : '#dc2626'; ?>; padding: 20px 40px; border-radius: 8px; font-weight: 600; font-size: 18px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); z-index: 1000; text-align: center;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Student's Personal Information Section -->
    <div class="gp-balance-section">
        <div class="gp-balance-section-header">
            Student's Personal Information:
        </div>
        <div class="gp-balance-section-content">
            <div class="gp-balance-info-row">
                <span class="gp-balance-label">Full Name: </span>
                <span><?php echo htmlspecialchars($student['full_name']); ?></span>
            </div>
            <div class="gp-balance-info-row">
                <span class="gp-balance-label">Current Balance: </span>
                <span class="gp-balance-value" style="color: <?php echo ($currentBalance <= 100) ? '#dc2626' : '#166534'; ?>;">₱<?php echo number_format($currentBalance, 2); ?></span>
            </div>
            <div class="gp-balance-info-row">
                <span class="gp-balance-label">Remaining Balance: </span>
                <span class="gp-balance-value" id="remainingBalance">₱<?php echo number_format($currentBalance, 2); ?></span>
            </div>
            <button type="button" class="gp-balance-add-btn" id="addBalanceBtn">Add Balance</button>
            <form method="post" action="student_balance.php?student_id=<?php echo urlencode($studentId); ?>" class="gp-balance-add-form" id="addBalanceForm">
                <input type="hidden" name="action" value="add_balance">
                <div class="gp-balance-input-group">
                    <input type="number" name="amount" class="gp-balance-input" placeholder="Amount (₱)" step="0.01" min="0.01" required>
                </div>
                <button type="submit" class="gp-balance-add-btn" style="width: 100%;">Add</button>
            </form>
        </div>
    </div>

    <!-- Purchase Section -->
    <div class="gp-balance-section">
        <div class="gp-balance-section-header">
            Purchase:
        </div>
        <div class="gp-balance-section-content">
            <div class="gp-balance-info-row">
                <span class="gp-balance-label">Purchase Amount(₱):</span>
            </div>
            <div class="gp-balance-info-row">
                <span id="purchaseAmountDisplay">₱0.00</span>
            </div>
            <div class="gp-balance-info-row">
                <span class="gp-balance-label">Amount Deducted: </span>
                <span class="gp-balance-deduction" id="amountDeducted">-₱0.00</span>
            </div>
            <button type="button" class="gp-balance-purchase-btn" id="purchaseBtn">Purchase</button>
            <form method="post" action="student_balance.php?student_id=<?php echo urlencode($studentId); ?>" class="gp-balance-purchase-form" id="purchaseForm">
                <input type="hidden" name="action" value="purchase">
                <div class="gp-balance-type-buttons">
                    <button type="button" class="gp-balance-type-btn" data-type="food">Food</button>
                    <button type="button" class="gp-balance-type-btn" data-type="supply">School Supply</button>
                </div>
                <input type="hidden" name="item_type" id="itemType" required>
                <div class="gp-balance-input-group">
                    <input type="text" name="item_name" class="gp-balance-input" placeholder="Item Name" required>
                </div>
                <div class="gp-balance-input-group">
                    <input type="number" name="quantity" class="gp-balance-input" placeholder="Quantity" min="1" value="1" required>
                </div>
                <div class="gp-balance-input-group">
                    <input type="number" name="amount" id="purchaseAmountInput" class="gp-balance-input" placeholder="Amount (₱)" step="0.01" min="0.01" required>
                </div>
                <button type="submit" class="gp-balance-pay-btn" id="payBtn">PAY</button>
            </form>
        </div>
    </div>

    <div style="text-align: center; margin-top: 24px; margin-bottom: 24px;">
        <a href="canteen_search.php" class="gp-balance-back-btn">Back</a>
    </div>

    <script>
        // Auto-hide success/error messages after 2 seconds
        const messageElement = document.getElementById('popupMessage');
        if (messageElement) {
            setTimeout(function() {
                messageElement.style.display = 'none';
            }, 2000);
        }

        // Add Balance toggle
        document.getElementById('addBalanceBtn').addEventListener('click', function() {
            const form = document.getElementById('addBalanceForm');
            form.classList.toggle('active');
        });

        // Purchase toggle
        document.getElementById('purchaseBtn').addEventListener('click', function() {
            const form = document.getElementById('purchaseForm');
            form.classList.toggle('active');
        });

        // Item type buttons
        document.querySelectorAll('.gp-balance-type-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.gp-balance-type-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                document.getElementById('itemType').value = this.dataset.type;
            });
        });

        // Update purchase amount display
        const purchaseAmountInput = document.getElementById('purchaseAmountInput');
        const purchaseAmountDisplay = document.getElementById('purchaseAmountDisplay');
        const amountDeducted = document.getElementById('amountDeducted');
        const remainingBalance = document.getElementById('remainingBalance');
        const payBtn = document.getElementById('payBtn');
        const currentBalance = <?php echo $currentBalance; ?>;

        purchaseAmountInput.addEventListener('input', function() {
            const amount = parseFloat(this.value) || 0;
            purchaseAmountDisplay.textContent = '₱' + amount.toFixed(2);
            amountDeducted.textContent = '-₱' + amount.toFixed(2);
            
            const newBalance = currentBalance - amount;
            remainingBalance.textContent = '₱' + newBalance.toFixed(2);
            
            if (amount > 0 && document.getElementById('itemType').value) {
                payBtn.classList.add('active');
            } else {
                payBtn.classList.remove('active');
            }
        });

        // Enable PAY button when item type is selected
        document.querySelectorAll('.gp-balance-type-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const amount = parseFloat(purchaseAmountInput.value) || 0;
                if (amount > 0) {
                    payBtn.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>

