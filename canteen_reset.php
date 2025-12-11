<?php
// Simple page to (re)set the canteen staff password.

$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'greenpay';

$mysqli = @new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($mysqli->connect_errno) {
    die('Database connection failed: ' . htmlspecialchars($mysqli->connect_error));
}

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$success = false;
$errorMsg = '';
$username = 'canteenstaff@gmail.com';

if ($isPost) {
    $newPassword = trim($_POST['new_password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    if ($newPassword === '' || $confirmPassword === '') {
        $errorMsg = 'Please fill in both password fields.';
    } elseif ($newPassword !== $confirmPassword) {
        $errorMsg = 'Passwords do not match.';
    } else {
        // Upsert: if the row exists, update; otherwise insert
        $stmt = $mysqli->prepare('SELECT id FROM canteen_staff WHERE username = ? LIMIT 1');
        if ($stmt) {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 1) {
                $stmt->close();
                $update = $mysqli->prepare('UPDATE canteen_staff SET password = ? WHERE username = ?');
                if ($update) {
                    $update->bind_param('ss', $newPassword, $username);
                    if ($update->execute()) {
                        $success = true;
                    } else {
                        $errorMsg = 'Failed to update password.';
                    }
                    $update->close();
                } else {
                    $errorMsg = 'Database error (update).';
                }
            } else {
                $stmt->close();
                $insert = $mysqli->prepare('INSERT INTO canteen_staff (username, password) VALUES (?, ?)');
                if ($insert) {
                    $insert->bind_param('ss', $username, $newPassword);
                    if ($insert->execute()) {
                        $success = true;
                    } else {
                        $errorMsg = 'Failed to save password.';
                    }
                    $insert->close();
                } else {
                    $errorMsg = 'Database error (insert).';
                }
            }
        } else {
            $errorMsg = 'Database error.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GreenPay - Canteen Password Reset</title>
    <link rel="stylesheet" href="assets/css/styles.css" />
</head>
<body>
    <div class="gp-page-bg"></div>
    <div class="gp-login-wrapper">
        <div class="gp-login-card">
            <div class="gp-logo-section">
                <div class="gp-logo-circle">
                    <span class="gp-logo-icon">GP</span>
                </div>
                <div class="gp-logo-text">GREENPAY</div>
            </div>

            <?php if ($isPost && $success): ?>
                <h2 style="font-size: 18px; margin-bottom: 12px;">Password Updated</h2>
                <p style="margin-bottom: 18px; font-size: 14px;">
                    The password for <strong><?php echo htmlspecialchars($username); ?></strong> has been updated.
                </p>
                <a href="index.php" class="gp-btn-primary" style="display:inline-block; text-decoration:none; text-align:center;">Back to Login</a>
            <?php else: ?>
                <h2 style="font-size: 18px; margin-bottom: 12px; text-align:center;">
                    Canteen Staff Password
                </h2>

                <?php if ($isPost && !$success && $errorMsg !== ''): ?>
                    <p style="color:#dc2626; margin-bottom: 12px; font-size: 14px; text-align:center;">
                        <?php echo htmlspecialchars($errorMsg); ?>
                    </p>
                <?php endif; ?>

                <form class="gp-form" method="post" action="canteen_reset.php">
                    <div class="gp-input">
                        <input
                            type="password"
                            name="new_password"
                            placeholder="New Password"
                            required
                        />
                    </div>

                    <div class="gp-input">
                        <input
                            type="password"
                            name="confirm_password"
                            placeholder="Confirm Password"
                            required
                        />
                    </div>

                    <button type="submit" class="gp-btn-primary">Save Password</button>
                </form>

                <a href="index.php" class="gp-btn-link" style="margin-top: 12px; text-align:center; display:block;">
                    Back to Login
                </a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>


