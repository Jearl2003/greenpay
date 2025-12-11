<?php
// Login handler: checks student logins against MySQL "students" table.
// Canteen staff login uses fixed username and password from "canteen_staff" table.

$role = $_POST['role'] ?? 'canteen';
$id = trim($_POST['staff_id'] ?? '');
$password = trim($_POST['password'] ?? '');

$error = '';
$displayRole = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($id === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } else {
        if ($role === 'student') {
            // DB settings (adjust if your MySQL uses different credentials)
            $dbHost = 'localhost';
            $dbUser = 'root';
            $dbPass = '';          // default XAMPP is empty password
            $dbName = 'greenpay';  // database that contains "students" table

            $mysqli = @new mysqli($dbHost, $dbUser, $dbPass, $dbName);
            if ($mysqli->connect_errno) {
                $error = 'Database connection failed.';
            } else {
                $stmt = $mysqli->prepare('SELECT id, first_name, middle_name, last_name, student_id, dob_password, image_path FROM students WHERE student_id = ? AND dob_password = ? LIMIT 1');
                if ($stmt) {
                    $stmt->bind_param('ss', $id, $password);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows === 1) {
                        $studentData = $result->fetch_assoc();

                        // Concatenate full name
                        $fullName = $studentData['first_name'];
                        if ($studentData['middle_name']) {
                            $fullName .= ' ' . $studentData['middle_name'];
                        }
                        $fullName .= ' ' . $studentData['last_name'];

                        // Start session and store student info
                        session_start();
                        $_SESSION['student_id'] = $studentData['student_id'];
                        $_SESSION['student_name'] = $fullName;
                        $_SESSION['student_dob'] = $studentData['dob_password'];
                        $_SESSION['student_image'] = $studentData['image_path'] ?? null;
                        
                        // Redirect to student dashboard
                        $stmt->close();
                        $mysqli->close();
                        header('Location: student_dashboard.php');
                        exit;
                    } else {
                        $error = 'Invalid student ID or date of birth.';
                    }

                    $stmt->close();
                } else {
                    $error = 'Database error.';
                }

                $mysqli->close();
            }
        } else {
            // Canteen staff login: single fixed username, password stored in canteen_staff table
            $dbHost = 'localhost';
            $dbUser = 'root';
            $dbPass = '';          // default XAMPP is empty password
            $dbName = 'greenpay';  // database that contains "canteen_staff" table

            $mysqli = @new mysqli($dbHost, $dbUser, $dbPass, $dbName);
            if ($mysqli->connect_errno) {
                $error = 'Database connection failed.';
            } else {
                $username = 'canteenstaff@gmail.com';

                if ($id !== $username) {
                    $error = 'Invalid username. Use canteenstaff@gmail.com.';
                } else {
                    $stmt = $mysqli->prepare('SELECT id FROM canteen_staff WHERE username = ? AND password = ? LIMIT 1');
                    if ($stmt) {
                        $stmt->bind_param('ss', $username, $password);
                        $stmt->execute();
                        $stmt->store_result();

                        if ($stmt->num_rows === 1) {
                            // Successful canteen login: go to dashboard page
                            $stmt->close();
                            $mysqli->close();
                            header('Location: canteen_dashboard.php');
                            exit;
                        } else {
                            $error = 'Invalid password. Use Forgot password to set a new one.';
                        }

                        $stmt->close();
                    } else {
                        $error = 'Database error.';
                    }
                }

                $mysqli->close();
            }
        }
    }
} else {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenPay - Login Result</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="gp-login-wrapper">
        <div class="gp-login-card">
            <?php if ($error): ?>
                <p style="color:#dc2626; margin-bottom: 12px; font-size: 14px;">
                    <?php echo htmlspecialchars($error); ?>
                </p>
                <a href="index.php" class="gp-btn-primary" style="display:inline-block; text-decoration:none; text-align:center;">Back to Login</a>
            <?php else: ?>
                <h2 style="margin-bottom: 12px; font-size: 18px; color:#16a34a;">Welcome to GreenPay</h2>
                <p style="margin-bottom: 18px; font-size: 14px;">
                    Logged in as <strong><?php echo htmlspecialchars($displayRole); ?></strong>
                    <?php if ($displayRole === 'Student'): ?>
                        <br>
                        ID: <strong><?php echo htmlspecialchars($id); ?></strong>
                    <?php endif; ?>
                </p>
                <a href="index.php" class="gp-btn-primary" style="display:inline-block; text-decoration:none; text-align:center;">Log out</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>


