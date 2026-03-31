<?php
// login.php - User Login with Role-Based Redirect
session_start();
require 'db_config.php';

$error = '';
$redirect = $_GET['redirect'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        // Check user credentials
        $stmt = $conn->prepare("SELECT user_id, email, password_hash, role, first_name, last_name, preferred_language, grade_level, school_id, is_active, offline_access_enabled FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            if (!$user['is_active']) {
                $error = "Account is deactivated. Please contact support.";
            } elseif (password_verify($password, $user['password_hash'])) {
                // Successful login
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['preferred_language'] = $user['preferred_language'];
                $_SESSION['grade_level'] = $user['grade_level'];
                $_SESSION['school_id'] = $user['school_id'];
                $_SESSION['offline_access'] = $user['offline_access_enabled'];
                
                // Update last login
                $update = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
                $update->bind_param("i", $user['user_id']);
                $update->execute();
                
                // Log login attempt
                $log = $conn->prepare("INSERT INTO login_attempts (email, ip_address, success) VALUES (?, ?, TRUE)");
                $ip = $_SERVER['REMOTE_ADDR'];
                $log->bind_param("ss", $email, $ip);
                $log->execute();
                
                // Create session record
                $session_id = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
                $is_offline = $user['offline_access_enabled'] ? 1 : 0;
                
                $sess = $conn->prepare("INSERT INTO user_sessions (session_id, user_id, ip_address, user_agent, expires_at, is_offline_valid) VALUES (?, ?, ?, ?, ?, ?)");
                $user_agent = $_SERVER['HTTP_USER_AGENT'];
                $sess->bind_param("sisssi", $session_id, $user['user_id'], $ip, $user_agent, $expires, $is_offline);
                $sess->execute();
                
                // Set session cookie
                $cookie_options = [
                    'expires' => strtotime('+24 hours'),
                    'path' => '/',
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Strict'
                ];
                setcookie('smart_lms_session', $session_id, $cookie_options);
                
                // Redirect based on role
                switch ($user['role']) {
                    case 'admin':
                        header("Location: admin_dashboard.php");
                        break;
                    case 'teacher':
                        header("Location: teacher_dashboard.php");
                        break;
                    case 'student':
                        header("Location: student_dashboard.php");
                        break;
                    case 'parent':
                        header("Location: parent_dashboard.php");
                        break;
                    default:
                        header("Location: index.php");
                }
                exit();
                
            } else {
                $error = "Invalid email or password.";
                // Log failed attempt
                $log = $conn->prepare("INSERT INTO login_attempts (email, ip_address, success, failure_reason) VALUES (?, ?, FALSE, 'Invalid password')");
                $ip = $_SERVER['REMOTE_ADDR'];
                $log->bind_param("ss", $email, $ip);
                $log->execute();
            }
        } else {
            $error = "Invalid email or password.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Smart LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
    :root {
        --primary: #0f766e;
        --primary-light: #14b8a6;
        --gradient-1: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
    }
    
    body {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f766e 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Inter', sans-serif;
    }
    
    .login-container {
        width: 100%;
        max-width: 450px;
        padding: 2rem;
    }
    
    .login-card {
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(10px);
        border-radius: 24px;
        box-shadow: 0 25px 50px rgba(0,0,0,0.25);
        overflow: hidden;
    }
    
    .login-header {
        background: var(--gradient-1);
        color: #fff;
        padding: 2.5rem 2rem;
        text-align: center;
    }
    
    .login-body {
        padding: 2.5rem;
    }
    
    .form-floating > .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.25rem rgba(15, 118, 110, 0.25);
    }
    
    .btn-login {
        background: var(--gradient-1);
        color: #fff;
        border: none;
        padding: 1rem;
        border-radius: 12px;
        font-weight: 600;
        width: 100%;
        transition: all 0.3s;
    }
    
    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(15, 118, 110, 0.4);
        color: #fff;
    }
    
    .divider {
        display: flex;
        align-items: center;
        text-align: center;
        margin: 1.5rem 0;
        color: #6b7280;
    }
    
    .divider::before, .divider::after {
        content: '';
        flex: 1;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .divider span {
        padding: 0 1rem;
    }
    
    .offline-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: rgba(34, 197, 94, 0.1);
        color: #16a34a;
        border-radius: 50px;
        font-size: 0.875rem;
        margin-top: 1rem;
    }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <h2><i class="bi bi-mortarboard-fill me-2"></i>Smart LMS</h2>
            <p class="mb-0">Welcome back! Please login to continue.</p>
        </div>
        
        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['registered'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>Registration successful! Please login.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" required autofocus>
                    <label for="email"><i class="bi bi-envelope me-1"></i>Email Address</label>
                </div>
                
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password"><i class="bi bi-lock me-1"></i>Password</label>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <a href="forgot_password.php" style="color: var(--primary); text-decoration: none; font-size: 0.875rem;">Forgot password?</a>
                </div>
        <!-- HOME BUTTON -->

<!-- LOGIN BUTTON -->
<button type="submit" class="btn btn-login btn-lg">
    <i class="bi bi-box-arrow-in-right me-2"></i>Login
</button>

</form>

<div class="divider">
    <span>OR</span>
</div>

<!-- REGISTER BUTTON -->
<div class="d-grid gap-2">
    <a href="register.php" class="btn btn-outline-primary btn-lg rounded-3">
        <i class="bi bi-person-plus me-2"></i>Create New Account
    </a>
</div>
            

<div class="d-grid mb-3">
    <a href="index.php" class="btn btn-outline-light btn-lg rounded-3">
        <i class="bi bi-house-door me-2"></i>Home
    </a>
</div>

            <div class="text-center mt-4">
                <div class="offline-badge">
                    <i class="bi bi-wifi-off"></i>
                    <span>Offline mode available after login</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="text-center mt-4 text-white">
        <small>
            <i class="bi bi-shield-check me-1"></i>Secure login • 
            <i class="bi bi-wifi-off me-1"></i>Works offline • 
            <i class="bi bi-translate me-1"></i>11 Languages
        </small>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>