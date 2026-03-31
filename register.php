<?php
session_start();
require 'db_config.php';

// Enable MySQL error reporting (DEBUG MODE)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Sanitize inputs
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    $password   = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role       = $_POST['role'] ?? 'student';
    $preferred_language = $_POST['preferred_language'] ?? 'en';

    $grade_level = ($role === 'student' && !empty($_POST['grade_level'])) 
        ? (int)$_POST['grade_level'] 
        : NULL;

    $school_id = !empty($_POST['school_id']) 
        ? (int)$_POST['school_id'] 
        : NULL;

    $province = $_POST['province'] ?? 'Unknown';

    // ================= VALIDATION =================
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } 
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } 
    elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    } 
    elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } 
    else {

        // ================= CHECK EMAIL =================
        $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Email already registered. Please login.";
        } else {

            // ================= INSERT USER =================
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("
                INSERT INTO users 
                (email, password_hash, role, first_name, last_name, preferred_language, grade_level, school_id, province) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            // Use NULL handling for integers
            $stmt->bind_param(
                "sssssssss",
                $email,
                $password_hash,
                $role,
                $first_name,
                $last_name,
                $preferred_language,
                $grade_level,  // will convert NULL to null automatically
                $school_id,    // same
                $province
            );

            // Execute safely
            if ($stmt->execute()) {
                $user_id = $stmt->insert_id;
                $success = "Registration successful! Redirecting to login...";

                // Create adaptive learning paths only for students
                if ($role === 'student' && $grade_level !== NULL) {
                    createInitialLearningPaths($conn, $user_id, $grade_level);
                }

                header("refresh:2;url=login.php");
            } else {
                $error = "Database Error: " . $stmt->error;
            }

            $stmt->close();
        }

        $check->close();
    }
}

// ================= HELPER FUNCTION =================
function createInitialLearningPaths($conn, $user_id, $grade_level) {

    $subjects = $conn->prepare("SELECT subject_id FROM subjects WHERE grade_level = ?");
    $subjects->bind_param("i", $grade_level);
    $subjects->execute();
    $result = $subjects->get_result();

    $path_stmt = $conn->prepare("
        INSERT INTO adaptive_learning_paths (user_id, subject_id, path_data) 
        VALUES (?, ?, ?)
    ");

    while ($row = $result->fetch_assoc()) {

        $path_data = json_encode([
            'started_topics' => [],
            'completed_topics' => [],
            'struggling_areas' => [],
            'recommended_next' => null,
            'pace_adjustments' => []
        ]);

        $path_stmt->bind_param("iis", $user_id, $row['subject_id'], $path_data);
        $path_stmt->execute();
    }

    $path_stmt->close();
    $subjects->close();
}

// Fetch schools
$schools_result = $conn->query("
    SELECT school_id, school_name, province 
    FROM schools 
    WHERE is_active = 1 OR is_active IS NULL 
    ORDER BY school_name
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register | Smart LMS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<style>
:root {
    --primary: #0f766e;
    --gradient: linear-gradient(135deg, #0f766e, #14b8a6);
}
body {
    background: linear-gradient(135deg,#0f172a,#1e293b,#0f766e);
    min-height:100vh;
}
.card {
    border-radius:20px;
}
.btn-main {
    background: var(--gradient);
    color:#fff;
    border:none;
}
.btn-main:hover {
    opacity:0.9;
}
.role-option {
    cursor:pointer;
    border:2px solid #ddd;
    padding:1rem;
    border-radius:12px;
    text-align:center;
}
.role-option.active {
    background:var(--gradient);
    color:#fff;
}
</style>
</head>

<body>

<div class="container mt-5">
<div class="card shadow p-4">

<h3 class="text-center mb-3">Create Account</h3>

<?php if ($error): ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<?php if ($success): ?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<form method="POST">

<!-- ROLE -->
<div class="d-flex gap-2 mb-3">
    <div class="role-option active" onclick="selectRole('student',this)">Student</div>
    <div class="role-option" onclick="selectRole('teacher',this)">Teacher</div>
    <div class="role-option" onclick="selectRole('parent',this)">Parent</div>
</div>
<input type="hidden" name="role" id="roleInput" value="student">

<div class="row">
<div class="col-md-6 mb-3">
<input class="form-control" name="first_name" placeholder="First Name" required>
</div>
<div class="col-md-6 mb-3">
<input class="form-control" name="last_name" placeholder="Last Name" required>
</div>
</div>

<input class="form-control mb-3" type="email" name="email" placeholder="Email" required>

<div class="row">
<div class="col-md-6 mb-3">
<input class="form-control" type="password" name="password" placeholder="Password" required>
</div>
<div class="col-md-6 mb-3">
<input class="form-control" type="password" name="confirm_password" placeholder="Confirm Password" required>
</div>
</div>

<select class="form-control mb-3" name="preferred_language">
<option value="en">English</option>
<option value="zu">isiZulu</option>
<option value="xh">isiXhosa</option>
</select>

<div id="gradeField">
<select class="form-control mb-3" name="grade_level">
<option value="8">Grade 8</option>
<option value="9">Grade 9</option>
<option value="10">Grade 10</option>
<option value="11">Grade 11</option>
<option value="12">Grade 12</option>
</select>
</div>

<select class="form-control mb-3" name="province">
<option value="GP">Gauteng</option>
<option value="KZN">KwaZulu-Natal</option>
<option value="WC">Western Cape</option>
</select>

<select class="form-control mb-3" name="school_id">
<option value="">Select School</option>
<?php while($s=$schools_result->fetch_assoc()): ?>
<option value="<?= $s['school_id'] ?>">
<?= $s['school_name'] ?>
</option>
<?php endwhile; ?>
</select>

<button class="btn btn-main w-100">Register</button>

</form>

</div>
</div>

<script>
function selectRole(role, el){
    document.querySelectorAll('.role-option').forEach(e=>e.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('roleInput').value = role;

    const gradeField = document.getElementById('gradeField');
    gradeField.style.display = (role === 'student') ? 'block' : 'none';
}
</script>

</body>
</html>