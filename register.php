<?php
// Student registration page (saves into MySQL "students" table)

// TODO: adjust these DB settings if your XAMPP MySQL uses different credentials
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';          // default XAMPP is empty password
$dbName = 'greenpay';  // make sure this database exists

$mysqli = @new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($mysqli->connect_errno) {
    die('Database connection failed: ' . htmlspecialchars($mysqli->connect_error));
}

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$success = false;
$errorMsg = '';

// Create uploads directory if it doesn't exist
$uploadDir = __DIR__ . '/assets/uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($isPost) {
    $firstName = trim($_POST['first_name'] ?? '');
    $middleName = trim($_POST['middle_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $studentId = trim($_POST['student_id'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $imagePath = null;

    // Combine names
    $fullName = $firstName;
    if ($middleName !== '') {
        $fullName .= ' ' . $middleName;
    }
    $fullName .= ' ' . $lastName;

    // Handle optional image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_image'];
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxSize) {
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = $studentId . '_' . time() . '.' . $extension;
            $targetPath = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $imagePath = 'assets/uploads/' . $filename;
            }
        }
    }

    // Basic validation
    if ($firstName === '' || $lastName === '' || $studentId === '' || $password === '') {
        $errorMsg = 'Please fill in all required fields.';
    } else {
        // Optional: validate mm/dd/yyyy format on server side
        if (!preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/', $password)) {
            $errorMsg = 'Date of birth must be in mm/dd/yyyy format.';
        } else {
            // Insert into students table
            if ($imagePath) {
                $stmt = $mysqli->prepare('INSERT INTO students (first_name, middle_name, last_name, student_id, dob_password, image_path) VALUES (?, ?, ?, ?, ?, ?)');
                if ($stmt) {
                    $stmt->bind_param('ssssss', $firstName, $middleName, $lastName, $studentId, $password, $imagePath);
                }
            } else {
                $stmt = $mysqli->prepare('INSERT INTO students (first_name, middle_name, last_name, student_id, dob_password) VALUES (?, ?, ?, ?, ?)');
                if ($stmt) {
                    $stmt->bind_param('sssss', $firstName, $middleName, $lastName, $studentId, $password);
                }
            }
            
            if ($stmt) {
                if ($stmt->execute()) {
                    $success = true;
                } else {
                    if ($mysqli->errno === 1062) {
                        $errorMsg = 'That student ID is already registered.';
                    } else {
                        $errorMsg = 'Failed to save registration. Please try again.';
                    }
                }
                $stmt->close();
            } else {
                $errorMsg = 'Database error. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GreenPay - Student Registration</title>
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
                <h2 style="font-size: 18px; margin-bottom: 12px;">Registration Successful</h2>
                <p style="margin-bottom: 18px; font-size: 14px;">
                    Student <strong><?php echo htmlspecialchars($fullName); ?></strong> (ID:
                    <strong><?php echo htmlspecialchars($studentId); ?></strong>) has been registered.
                </p>
                <a href="index.php" class="gp-btn-primary" style="display:inline-block; text-decoration:none; text-align:center;">Back to Login</a>
            <?php else: ?>
                <h2 style="font-size: 18px; margin-bottom: 12px; text-align:center;">
                    Student Registration
                </h2>

                <?php if ($isPost && !$success && $errorMsg !== ''): ?>
                    <p style="color:#dc2626; margin-bottom: 12px; font-size: 14px; text-align:center;">
                        <?php echo htmlspecialchars($errorMsg); ?>
                    </p>
                <?php endif; ?>

                <form class="gp-form" method="post" action="register.php" enctype="multipart/form-data">
                    <div class="gp-input">
                        <input
                            type="text"
                            name="first_name"
                            id="firstName"
                            placeholder="First Name"
                            required
                        />
                    </div>

                    <div class="gp-input">
                        <input
                            type="text"
                            name="middle_name"
                            id="middleName"
                            placeholder="Middle Name (Optional)"
                        />
                    </div>

                    <div class="gp-input">
                        <input
                            type="text"
                            name="last_name"
                            id="lastName"
                            placeholder="Last Name"
                            required
                        />
                    </div>

                    <div class="gp-input">
                        <input
                            type="text"
                            name="student_id"
                            id="studentIdRegister"
                            placeholder="Student ID Number"
                            required
                        />
                    </div>

                    <div class="gp-input">
                        <input
                            type="text"
                            name="password"
                            id="studentDobPassword"
                            placeholder="Password required your date of birth (mm/dd/yyyy)"
                            inputmode="numeric"
                            pattern="[0-9]{2}/[0-9]{2}/[0-9]{4}"
                            title="Use format mm/dd/yyyy, for example 05/21/2008"
                            maxlength="10"
                            required
                        />
                    </div>

                    <div class="gp-input">
                        <label for="profile_image" style="font-size: 14px; color: #666; margin-bottom: 8px; display: block;">
                            Profile Picture (Optional)
                        </label>
                        <input
                            type="file"
                            name="profile_image"
                            id="profile_image"
                            accept="image/jpeg,image/jpg,image/png,image/gif"
                            style="font-size: 14px; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; width: 100%;"
                        />
                    </div>

                    <button type="submit" class="gp-btn-primary">Register</button>
                </form>

                <a href="index.php" class="gp-btn-link" style="margin-top: 12px; text-align:center; display:block;">
                    Back to Login
                </a>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Force mm/dd/yyyy numeric format in DOB field
        (function () {
            const dobInput = document.getElementById("studentDobPassword");
            if (!dobInput) return;

            dobInput.addEventListener("input", function () {
                // Remove all non-digits
                let digits = this.value.replace(/\D/g, "").slice(0, 8); // max 8 digits: mmddyyyy

                let mm = digits.slice(0, 2);
                let dd = digits.slice(2, 4);
                let yyyy = digits.slice(4, 8);

                let formatted = mm;
                if (dd) formatted += "/" + dd;
                if (yyyy) formatted += "/" + yyyy;

                this.value = formatted;
            });
        })();
    </script>
</body>
</html>


