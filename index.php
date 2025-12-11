<?php
// Simple entry point for GreenPay mobile web login
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GreenPay - Login</title>
    <link rel="stylesheet" href="assets/css/styles.css" />
</head>
<body>
    <div class="gp-page-bg"></div>
    <div class="gp-login-wrapper">
        <a href="welcome.php" class="gp-back-btn" title="Back to Welcome">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="gp-login-card">
            <div class="gp-logo-section">
                <div class="gp-logo-circle">
                    <span class="gp-logo-icon">GP</span>
                </div>
                <div class="gp-logo-text">GREENPAY</div>
            </div>

            <form class="gp-form" method="post" action="login.php">
                <input type="hidden" name="role" id="roleInput" value="canteen" />

                <div class="gp-input">
                    <input
                        type="text"
                        name="staff_id"
                        id="staffId"
                        placeholder="Canteen staff email (canteenstaff@gmail.com)"
                        autocomplete="off"
                        required
                    />
                </div>

                <div class="gp-input">
                    <input
                        type="password"
                        name="password"
                        id="password"
                        placeholder="Password"
                        required
                    />
                </div>

                <a href="canteen_reset.php" class="gp-forgot" id="forgotLink">Forgot password?</a>
                <a
                    href="register.php"
                    class="gp-btn-link"
                    id="studentRegisterLink"
                    style="display: none; margin-top: 8px; text-align: center;"
                >
                    Register
                </a>

                <button type="submit" class="gp-btn-primary">Log In</button>
            </form>

            <div class="gp-role-toggle">
                <button type="button" class="gp-toggle-btn" data-role="student">
                    Student
                </button>
                <button
                    type="button"
                    class="gp-toggle-btn gp-toggle-btn--active"
                    data-role="canteen"
                >
                    Canteen Staff
                </button>
            </div>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>


