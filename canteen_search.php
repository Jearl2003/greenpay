<?php
// Search page for canteen staff to find student by ID
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'greenpay';

$mysqli = @new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($mysqli->connect_errno) {
    die('Database connection failed: ' . htmlspecialchars($mysqli->connect_error));
}

$student = null;
$error = '';
$studentId = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = trim($_POST['student_id'] ?? '');
    
    if ($studentId === '') {
        $error = 'Please enter a Student ID number.';
    } else {
        $stmt = $mysqli->prepare('SELECT first_name, middle_name, last_name, student_id, dob_password, image_path FROM students WHERE student_id = ? LIMIT 1');
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
                $error = 'Student not found.';
            }
            
            $stmt->close();
        } else {
            $error = 'Database error.';
        }
    }
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GreenPay - Search Student</title>
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
            padding: 20px;
            position: relative;
        }
        .gp-canteen-logo-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            z-index: 10;
        }
        .gp-canteen-logo-g {
            font-size: 120px;
            font-weight: 900;
            color: #166534;
            line-height: 1;
            margin-bottom: 16px;
        }
        .gp-canteen-logo-text {
            font-size: 48px;
            font-weight: 800;
            color: #166534;
            letter-spacing: 8px;
        }
        .gp-search-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .gp-search-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
            font-size: 24px;
            font-weight: 700;
            color: #000;
        }
        .gp-search-icon {
            width: 24px;
            height: 24px;
            border: 2px solid #a855f7;
            border-radius: 50%;
            position: relative;
        }
        .gp-search-icon::after {
            content: '';
            position: absolute;
            bottom: -4px;
            right: -4px;
            width: 8px;
            height: 2px;
            background-color: #a855f7;
            transform: rotate(45deg);
        }
        .gp-search-form {
            margin-bottom: 24px;
        }
        .gp-search-label {
            font-weight: 700;
            color: #000;
            margin-bottom: 8px;
            display: block;
        }
        .gp-search-input-group {
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }
        .gp-search-input {
            flex: 1;
            padding: 8px 0;
            border: none;
            border-bottom: 2px solid #000;
            font-size: 16px;
            outline: none;
            background: transparent;
        }
        .gp-search-btn {
            background-color: #16a34a;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 24px;
            font-weight: 600;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .gp-search-btn:hover {
            background-color: #15803d;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(22, 163, 74, 0.4);
        }
        .gp-search-result {
            background-color: #4ade80;
            border-radius: 12px;
            padding: 24px;
            display: flex;
            gap: 20px;
            align-items: flex-start;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .gp-search-result:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .gp-search-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #166534;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .gp-search-avatar-icon {
            width: 50px;
            height: 50px;
            background-color: #fff;
            border-radius: 50%;
            position: relative;
        }
        .gp-search-avatar-icon::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 35px;
            height: 18px;
            background-color: #fff;
            border-radius: 35px 35px 0 0;
        }
        .gp-search-details {
            flex: 1;
        }
        .gp-search-detail-row {
            margin-bottom: 8px;
            color: #000;
            font-size: 16px;
        }
        .gp-search-detail-label {
            font-weight: 600;
        }
        .gp-search-detail-value {
            text-decoration: underline;
        }
        .gp-search-error {
            background-color: #fee2e2;
            color: #dc2626;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-weight: 600;
        }
        .gp-search-back-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #16a34a;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .gp-search-back-btn:hover {
            background-color: #15803d;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(22, 163, 74, 0.4);
        }
        @media (max-width: 768px) {
            body {
                padding: 16px;
            }
            .gp-search-header {
                font-size: 20px;
            }
            .gp-search-input-group {
                flex-direction: column;
            }
            .gp-search-btn {
                width: 100%;
            }
            .gp-search-result {
                flex-direction: column;
                text-align: center;
            }
            .gp-search-avatar {
                margin: 0 auto;
            }
            .gp-search-back-btn {
                position: relative;
                bottom: auto;
                right: auto;
                margin-top: 20px;
                display: block;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="gp-canteen-logo-overlay">
        <div class="gp-canteen-logo-g">G</div>
        <div class="gp-canteen-logo-text">GREENPAY</div>
    </div>
    <div class="gp-search-container">
        <div class="gp-search-header">
            <div class="gp-search-icon"></div>
            <span>SEARCH</span>
        </div>

        <form method="post" action="canteen_search.php" class="gp-search-form">
            <?php if ($error && !$student): ?>
                <div class="gp-search-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <label class="gp-search-label" for="student_id">Student's ID number:</label>
            <div class="gp-search-input-group">
                <input
                    type="text"
                    name="student_id"
                    id="student_id"
                    class="gp-search-input"
                    value="<?php echo htmlspecialchars($studentId); ?>"
                    placeholder="Enter Student ID"
                    required
                    autofocus
                />
                <button type="submit" class="gp-search-btn">Search</button>
            </div>
        </form>

        <?php if ($student): ?>
            <a href="student_balance.php?student_id=<?php echo urlencode($student['student_id']); ?>" style="text-decoration: none; display: block;">
                <div class="gp-search-result" style="cursor: pointer;">
                    <div class="gp-search-avatar">
                        <?php if ($student['image_path'] && file_exists(__DIR__ . '/' . $student['image_path'])): ?>
                            <img src="<?php echo htmlspecialchars($student['image_path']); ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        <?php else: ?>
                            <div class="gp-search-avatar-icon"></div>
                        <?php endif; ?>
                    </div>
                    <div class="gp-search-details">
                        <div class="gp-search-detail-row">
                            <span class="gp-search-detail-label">Full Name:</span>
                            <span><?php echo htmlspecialchars($student['full_name']); ?></span>
                        </div>
                        <div class="gp-search-detail-row">
                            <span class="gp-search-detail-label">ID Number:</span>
                            <span class="gp-search-detail-value"><?php echo htmlspecialchars($student['student_id']); ?></span>
                        </div>
                        <div class="gp-search-detail-row">
                            <span class="gp-search-detail-label">Date of Birth:</span>
                            <span><?php echo htmlspecialchars($student['dob_password']); ?></span>
                        </div>
                    </div>
                </div>
            </a>
        <?php endif; ?>

        <a href="canteen_dashboard.php" class="gp-search-back-btn">Back</a>
    </div>
</body>
</html>

