<?php
session_start();
require 'db_config.php';

// Check if student is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch leaderboard (top 10 students by points)
$leaderboard_sql = "
    SELECT first_name, last_name, points, badges 
    FROM users 
    WHERE role='student' 
    ORDER BY points DESC 
    LIMIT 10
";

$leaderboard = $conn->query($leaderboard_sql);
if (!$leaderboard) {
    die("Leaderboard query failed: " . $conn->error);
}

// Fetch current user info
$user_id = (int)$_SESSION['user_id']; // cast to int for safety
$user_sql = "SELECT points, badges FROM users WHERE user_id = $user_id";
$user_result = $conn->query($user_sql);

if (!$user_result) {
    die("User query failed: " . $conn->error);
}

$user = $user_result->fetch_assoc();
$badges = json_decode($user['badges'], true) ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gamified Learning | Smart LMS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<style>
:root {
    --primary: #667eea;
    --secondary: #764ba2;
    --success: #11998e;
    --warning: #f093fb;
    --info: #4facfe;
    --ai-gradient: linear-gradient(135deg, #8b5cf6 0%, #ec4899 50%, #667eea 100%);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #1e3c72 100%);
    background-size: 400% 400%;
    animation: gradientBG 15s ease infinite;
    font-family: 'Poppins', sans-serif;
    min-height: 100vh;
    overflow-x: hidden;
    color: #fff;
    position: relative;
}

@keyframes gradientBG {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* Floating Particles */
.particles {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 0;
    overflow: hidden;
}

.particle {
    position: absolute;
    width: 8px;
    height: 8px;
    background: rgba(255,255,255,0.15);
    border-radius: 50%;
    animation: float 20s infinite linear;
}

@keyframes float {
    0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
    10% { opacity: 1; }
    90% { opacity: 1; }
    100% { transform: translateY(-100vh) rotate(720deg); opacity: 0; }
}

/* Cards & Badges */
.card {
    border-radius: 20px;
    background: rgba(0,0,0,0.5) !important;
    backdrop-filter: blur(10px);
}

.badge-icon {
    width: 32px;
    height: 32px;
}

/* Leaderboard Table */
.table-dark {
    background: rgba(0,0,0,0.4);
    backdrop-filter: blur(10px);
}

/* Headers */
h2, h4, h5 {
    text-shadow: 1px 1px 6px rgba(0,0,0,0.5);
}

/* Navbar (optional if you want) */
.navbar {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(255,255,255,0.2);
    position: sticky;
    top: 0;
    z-index: 1000;
    animation: slideDown 0.8s ease-out;
}
@keyframes slideDown {
    from { transform: translateY(-100%); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
</style>
</head>
<body>

<!-- Floating particles -->
<div class="particles">
    <?php for($i=0; $i<50; $i++): ?>
        <div class="particle" style="left:<?=rand(0,100)?>%; animation-delay:<?=rand(0,20)?>s;"></div>
    <?php endfor; ?>
</div>

<div class="container py-5 position-relative" style="z-index:1;">
    <h2 class="mb-4 text-center"><i class="bi bi-trophy-fill me-2"></i>Gamified Learning</h2>

    <div class="row mb-5">
        <div class="col-md-6 mx-auto">
            <div class="card p-4 text-center">
                <h4>Your Points: <span class="text-warning"><?= $user['points'] ?? 0 ?></span></h4>
                <h5 class="mt-3">Your Badges:</h5>
                <div class="d-flex justify-content-center flex-wrap gap-2 mt-2">
                    <?php if ($badges): ?>
                        <?php foreach ($badges as $badge): ?>
                            <img src="badges/<?= htmlspecialchars($badge) ?>" class="badge-icon" title="<?= htmlspecialchars($badge) ?>" alt="<?= htmlspecialchars($badge) ?>">
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No badges yet. Keep learning!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <h4 class="mb-3 text-center">Leaderboard</h4>
    <div class="table-responsive">
        <table class="table table-dark table-striped table-hover text-center">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Points</th>
                    <th>Badges</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $rank = 1;
                while ($row = $leaderboard->fetch_assoc()):
                    $student_badges = json_decode($row['badges'], true) ?? [];
                ?>
                <tr>
                    <td><?= $rank++ ?></td>
                    <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                    <td><?= $row['points'] ?></td>
                    <td>
                        <?php foreach ($student_badges as $badge): ?>
                            <img src="badges/<?= htmlspecialchars($badge) ?>" class="badge-icon" title="<?= htmlspecialchars($badge) ?>" alt="<?= htmlspecialchars($badge) ?>">
                        <?php endforeach; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>