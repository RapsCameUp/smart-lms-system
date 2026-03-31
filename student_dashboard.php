<?php
session_start();
require 'db_config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'] ?? 'Student';
$last_name = $_SESSION['last_name'] ?? '';
$grade_level = $_SESSION['grade_level'] ?? '10';

// Initialize stats with default values
$stats = [
    'overall_progress' => 75,
    'assessments_done' => 12,
    'badges_earned' => 8,
    'total_points' => 2450
];

// Fetch student stats only if connection exists
if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
    try {
        // Get learning streak
        $table_check = $conn->query("SHOW TABLES LIKE 'learning_streaks'");
        if ($table_check && $table_check->num_rows > 0) {
            $streak_query = "SELECT current_streak FROM learning_streaks WHERE user_id = ?";
            $stmt = $conn->prepare($streak_query);
            if ($stmt) {
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result && $row = $result->fetch_assoc()) {
                    $current_streak = $row['current_streak'] ?? 12;
                }
                $stmt->close();
            }
        }

        // Get total points and stats
        $table_check = $conn->query("SHOW TABLES LIKE 'gamification_points'");
        if ($table_check && $table_check->num_rows > 0) {
            $points_query = "SELECT total_points, current_level FROM gamification_points WHERE user_id = ?";
            $stmt = $conn->prepare($points_query);
            if ($stmt) {
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result && $row = $result->fetch_assoc()) {
                    $stats['total_points'] = $row['total_points'] ?? 2450;
                    $current_level = $row['current_level'] ?? 5;
                }
                $stmt->close();
            }
        }

        // Get mastery level for progress
        $table_check = $conn->query("SHOW TABLES LIKE 'concept_mastery'");
        if ($table_check && $table_check->num_rows > 0) {
            $mastery_query = "SELECT AVG(mastery_level) as avg_mastery FROM concept_mastery WHERE user_id = ?";
            $stmt = $conn->prepare($mastery_query);
            if ($stmt) {
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result && $row = $result->fetch_assoc()) {
                    $stats['overall_progress'] = round($row['avg_mastery'] ?? 75);
                }
                $stmt->close();
            }
        }

        // Get assessments count
        $table_check = $conn->query("SHOW TABLES LIKE 'assessments'");
        if ($table_check && $table_check->num_rows > 0) {
            $assess_query = "SELECT COUNT(*) as count FROM assessments WHERE user_id = ?";
            $stmt = $conn->prepare($assess_query);
            if ($stmt) {
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result && $row = $result->fetch_assoc()) {
                    $stats['assessments_done'] = $row['count'] ?? 12;
                }
                $stmt->close();
            }
        }

        // Get badges count
        $table_check = $conn->query("SHOW TABLES LIKE 'badges'");
        if ($table_check && $table_check->num_rows > 0) {
            $badge_query = "SELECT COUNT(*) as count FROM badges WHERE user_id = ?";
            $stmt = $conn->prepare($badge_query);
            if ($stmt) {
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result && $row = $result->fetch_assoc()) {
                    $stats['badges_earned'] = $row['count'] ?? 8;
                }
                $stmt->close();
            }
        }

    } catch (Exception $e) {
        error_log("Database error in student dashboard: " . $e->getMessage());
    }
}

// Fetch current courses
$courses = [
    ['subject_id' => 1, 'subject_name' => 'Mathematics', 'icon' => 'calculator', 'mastery_level' => 75, 'completed_lessons' => 12, 'total_lessons' => 20, 'color' => '#667eea'],
    ['subject_id' => 2, 'subject_name' => 'Physical Sciences', 'icon' => 'atom', 'mastery_level' => 68, 'completed_lessons' => 8, 'total_lessons' => 15, 'color' => '#10b981'],
    ['subject_id' => 3, 'subject_name' => 'Life Sciences', 'icon' => 'flower1', 'mastery_level' => 82, 'completed_lessons' => 10, 'total_lessons' => 12, 'color' => '#f59e0b'],
    ['subject_id' => 4, 'subject_name' => 'English', 'icon' => 'book', 'mastery_level' => 90, 'completed_lessons' => 18, 'total_lessons' => 20, 'color' => '#ec4899']
];

if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
    try {
        $tables_exist = true;
        $required_tables = ['subjects', 'student_subjects'];
        foreach ($required_tables as $table) {
            $check = $conn->query("SHOW TABLES LIKE '$table'");
            if (!$check || $check->num_rows == 0) {
                $tables_exist = false;
                break;
            }
        }

        if ($tables_exist) {
            $courses_query = "SELECT s.subject_id, s.subject_name, s.icon, s.description, s.color,
                cm.mastery_level, cm.completed_lessons, cm.total_lessons
                FROM subjects s
                JOIN student_subjects ss ON s.subject_id = ss.subject_id
                LEFT JOIN concept_mastery cm ON s.subject_id = cm.subject_id AND cm.user_id = ?
                WHERE ss.user_id = ? AND s.is_active = 1
                LIMIT 4";
            $stmt = $conn->prepare($courses_query);
            if ($stmt) {
                $stmt->bind_param("ii", $user_id, $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result && $result->num_rows > 0) {
                    $courses = $result->fetch_all(MYSQLI_ASSOC);
                }
                $stmt->close();
            }
        }
    } catch (Exception $e) {
        error_log("Courses query error: " . $e->getMessage());
    }
}

// Recent achievements
$achievements = [
    ['icon' => 'bi-fire', 'title' => '7 Day Streak', 'desc' => 'Learned 7 days in a row', 'points' => 100, 'color' => '#ef4444'],
    ['icon' => 'bi-star-fill', 'title' => 'Quick Learner', 'desc' => 'Completed 5 lessons in one day', 'points' => 150, 'color' => '#f59e0b'],
    ['icon' => 'bi-trophy-fill', 'title' => 'Math Master', 'desc' => 'Scored 100% on algebra quiz', 'points' => 200, 'color' => '#8b5cf6']
];
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Dashboard - Smart LMS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

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

/* NAVBAR */
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

.navbar-brand {
    color: #fff !important;
    font-weight: 700;
    font-size: 1.6rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.user-info {
    color: #fff;
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1rem;
    border: 3px solid rgba(255,255,255,0.4);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

/* SIDEBAR */
.sidebar {
    width: 260px;
    position: fixed;
    height: 100vh;
    background: rgba(15, 23, 42, 0.98);
    backdrop-filter: blur(20px);
    color: #fff;
    z-index: 999;
    border-right: 1px solid rgba(255,255,255,0.1);
    padding-top: 20px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.sidebar-header {
    padding: 0 20px 25px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    margin-bottom: 20px;
}

.sidebar-header h5 {
    margin: 0;
    font-weight: 700;
    font-size: 1.3rem;
    background: linear-gradient(45deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    display: flex;
    align-items: center;
    gap: 12px;
}

.sidebar a {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 20px;
    margin: 5px 15px;
    color: #94a3b8;
    text-decoration: none;
    border-radius: 12px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    font-weight: 500;
}

.sidebar a::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 0;
    background: linear-gradient(90deg, #667eea, #764ba2);
    transition: width 0.3s ease;
    z-index: -1;
    border-radius: 12px;
}

.sidebar a:hover::before,
.sidebar a.active::before {
    width: 100%;
}

.sidebar a:hover,
.sidebar a.active {
    color: #fff;
    transform: translateX(8px);
}

.sidebar a.active {
    background: rgba(102, 126, 234, 0.15);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.sidebar-divider {
    height: 1px;
    background: rgba(255,255,255,0.1);
    margin: 15px 20px;
}

.logout-link {
    color: #ef4444 !important;
}

.logout-link::before {
    background: linear-gradient(90deg, #ef4444, #f87171) !important;
}

/* Mobile Toggle */
.sidebar-toggle {
    display: none;
    position: fixed;
    top: 20px;
    left: 20px;
    width: 45px;
    height: 45px;
    border-radius: 12px;
    background: rgba(15, 23, 42, 0.95);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255,255,255,0.2);
    color: #fff;
    font-size: 1.5rem;
    cursor: pointer;
    z-index: 1001;
    align-items: center;
    justify-content: center;
}

.sidebar-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(5px);
    z-index: 998;
}

.sidebar-overlay.active {
    display: block;
}

/* MAIN CONTENT */
.main-content {
    margin-left: 260px;
    padding: 30px;
    position: relative;
    z-index: 1;
}

.welcome-section {
    margin-bottom: 35px;
    animation: fadeInUp 0.8s ease-out;
}

.welcome-section h4 {
    color: #fff;
    font-size: 2.2rem;
    font-weight: 700;
    margin-bottom: 8px;
    text-shadow: 2px 2px 8px rgba(0,0,0,0.3);
}

@keyframes fadeInUp {
    from { transform: translateY(30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* STATS CARDS */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.stat-card {
    position: relative;
    border-radius: 24px;
    overflow: hidden;
    height: 200px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    cursor: pointer;
    animation: cardEntrance 0.8s ease-out backwards;
    border: 1px solid rgba(255,255,255,0.1);
}

.stat-card:nth-child(1) { animation-delay: 0.1s; }
.stat-card:nth-child(2) { animation-delay: 0.2s; }
.stat-card:nth-child(3) { animation-delay: 0.3s; }
.stat-card:nth-child(4) { animation-delay: 0.4s; }

@keyframes cardEntrance {
    from { transform: translateY(60px) scale(0.9) rotateX(10deg); opacity: 0; }
    to { transform: translateY(0) scale(1) rotateX(0); opacity: 1; }
}

.stat-card:hover {
    transform: translateY(-15px) scale(1.03) rotateX(5deg);
    box-shadow: 0 30px 60px rgba(0,0,0,0.3);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-size: cover;
    background-position: center;
    transition: transform 0.6s ease;
}

.stat-card:hover::before {
    transform: scale(1.15);
    filter: brightness(1.1);
}

.stat-card::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.4) 50%, rgba(0,0,0,0.7) 100%);
    z-index: 1;
    transition: all 0.3s ease;
}

.card-progress::before { background-image: url('https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=500&h=400&fit=crop'); }
.card-assessments::before { background-image: url('https://images.unsplash.com/photo-1606326608606-aa0b62935f2b?w=500&h=400&fit=crop'); }
.card-badges::before { background-image: url('https://images.unsplash.com/photo-1567427017947-545c5f8d16ad?w=500&h=400&fit=crop'); }
.card-points::before { background-image: url('https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=500&h=400&fit=crop'); }

.card-content {
    position: relative;
    z-index: 2;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 25px;
    color: #fff;
}

.card-icon-wrapper {
    width: 55px;
    height: 55px;
    border-radius: 16px;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    border: 2px solid rgba(255,255,255,0.3);
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    animation: iconGlow 2s ease-in-out infinite;
}

@keyframes iconGlow {
    0%, 100% { box-shadow: 0 0 0 0 rgba(255,255,255,0.4); transform: scale(1); }
    50% { box-shadow: 0 0 20px 5px rgba(255,255,255,0.3); transform: scale(1.05); }
}

.card-progress .card-icon-wrapper { color: #4ade80; border-color: rgba(74, 222, 128, 0.5); }
.card-assessments .card-icon-wrapper { color: #fb7185; border-color: rgba(251, 113, 133, 0.5); }
.card-badges .card-icon-wrapper { color: #fbbf24; border-color: rgba(251, 191, 36, 0.5); }
.card-points .card-icon-wrapper { color: #60a5fa; border-color: rgba(96, 165, 250, 0.5); }

.card-value {
    font-size: 2.8rem;
    font-weight: 700;
    margin: 0;
    line-height: 1;
    text-shadow: 2px 2px 8px rgba(0,0,0,0.4);
    background: linear-gradient(to right, #fff, rgba(255,255,255,0.9));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.card-label {
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 2px;
    opacity: 0.9;
    margin-top: 8px;
    font-weight: 500;
}

/* COURSES SECTION 3D */
.courses-section-3d {
    margin-bottom: 40px;
}

.section-title {
    font-size: 1.4rem;
    font-weight: 600;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.section-title i {
    color: #667eea;
    font-size: 1.6rem;
}

.courses-slider-3d {
    display: flex;
    gap: 25px;
    overflow-x: auto;
    padding: 20px 10px;
    scroll-snap-type: x mandatory;
    perspective: 1000px;
}

.courses-slider-3d::-webkit-scrollbar {
    height: 8px;
}

.courses-slider-3d::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.1);
    border-radius: 4px;
}

.courses-slider-3d::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.3);
    border-radius: 4px;
}

.course-card-3d {
    flex-shrink: 0;
    width: 320px;
    height: 420px;
    position: relative;
    transform-style: preserve-3d;
    transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
    scroll-snap-align: start;
    cursor: pointer;
    animation: cardEntrance 0.8s ease-out backwards;
}

.course-card-3d:nth-child(1) { animation-delay: 0.1s; }
.course-card-3d:nth-child(2) { animation-delay: 0.2s; }
.course-card-3d:nth-child(3) { animation-delay: 0.3s; }
.course-card-3d:nth-child(4) { animation-delay: 0.4s; }

.course-card-3d:hover {
    transform: translateZ(50px) rotateX(5deg);
}

.course-card-inner-3d {
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(20px);
    border-radius: 30px;
    border: 1px solid rgba(255,255,255,0.1);
    overflow: hidden;
    position: relative;
    transform-style: preserve-3d;
    transition: all 0.5s;
}

.course-card-3d:hover .course-card-inner-3d {
    box-shadow: 0 30px 60px rgba(0,0,0,0.4);
    border-color: rgba(255,255,255,0.3);
}

.course-image-3d {
    height: 180px;
    position: relative;
    overflow: hidden;
}

.course-image-3d::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, transparent 0%, rgba(15,23,42,0.9) 100%);
    z-index: 1;
}

.course-icon-3d {
    position: absolute;
    bottom: -30px;
    left: 30px;
    width: 80px;
    height: 80px;
    border-radius: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    color: #fff;
    z-index: 2;
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    transform: translateZ(30px);
    transition: transform 0.3s;
}

.course-card-3d:hover .course-icon-3d {
    transform: translateZ(50px) scale(1.1);
}

.course-content-3d {
    padding: 50px 30px 30px;
    position: relative;
    z-index: 1;
}

.course-title-3d {
    font-size: 1.4rem;
    font-weight: 700;
    margin-bottom: 10px;
    color: #fff;
    transform: translateZ(20px);
}

.course-progress-3d {
    margin-top: 20px;
}

.progress-header-3d {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    font-size: 0.9rem;
    color: rgba(255,255,255,0.7);
}

.progress-bar-bg-3d {
    height: 10px;
    background: rgba(255,255,255,0.1);
    border-radius: 10px;
    overflow: hidden;
    position: relative;
}

.progress-bar-fill-3d {
    height: 100%;
    border-radius: 10px;
    transition: width 1s ease-out;
    position: relative;
    overflow: hidden;
}

.progress-bar-fill-3d::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

.course-stats-3d {
    display: flex;
    gap: 20px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid rgba(255,255,255,0.1);
}

.stat-3d {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
    color: rgba(255,255,255,0.7);
}

.stat-3d i {
    font-size: 1.2rem;
}

/* ACHIEVEMENTS 3D */
.achievements-section-3d {
    margin-bottom: 40px;
}

.achievements-grid-3d {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
}

.achievement-card-3d {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    border: 1px solid rgba(255,255,255,0.1);
    padding: 30px;
    display: flex;
    align-items: center;
    gap: 20px;
    transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
    transform-style: preserve-3d;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    animation: cardEntrance 0.8s ease-out backwards;
}

.achievement-card-3d:nth-child(1) { animation-delay: 0.1s; }
.achievement-card-3d:nth-child(2) { animation-delay: 0.2s; }
.achievement-card-3d:nth-child(3) { animation-delay: 0.3s; }

.achievement-card-3d::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, transparent, rgba(255,255,255,0.05), transparent);
    transform: translateX(-100%);
    transition: transform 0.6s;
}

.achievement-card-3d:hover::before {
    transform: translateX(100%);
}

.achievement-card-3d:hover {
    transform: translateY(-10px) translateZ(20px);
    box-shadow: 0 30px 60px rgba(0,0,0,0.3);
    border-color: rgba(255,255,255,0.2);
}

.achievement-icon-3d {
    width: 70px;
    height: 70px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    flex-shrink: 0;
    position: relative;
    transform: translateZ(30px);
    transition: transform 0.3s;
}

.achievement-card-3d:hover .achievement-icon-3d {
    transform: translateZ(40px) rotateY(360deg);
    transition: transform 0.6s;
}

.achievement-info-3d h6 {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 600;
    color: #fff;
    margin-bottom: 5px;
}

.achievement-info-3d p {
    margin: 0;
    font-size: 0.9rem;
    color: rgba(255,255,255,0.6);
    margin-bottom: 8px;
}

.achievement-points-3d {
    font-size: 1rem;
    font-weight: 700;
    color: #fbbf24;
    display: flex;
    align-items: center;
    gap: 5px;
}

/* STEM SECTION */
.stem-section {
    margin-top: 40px;
    margin-bottom: 40px;
}

.stem-card {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    transform-style: preserve-3d;
    animation: cardEntrance 0.8s ease-out backwards;
}

.stem-card:nth-child(1) { animation-delay: 0.1s; }
.stem-card:nth-child(2) { animation-delay: 0.2s; }
.stem-card:nth-child(3) { animation-delay: 0.3s; }

.stem-card:hover {
    transform: scale(1.05) translateZ(30px);
    box-shadow: 0 30px 60px rgba(0,0,0,0.4);
}

.stem-card img {
    width: 100%;
    height: 250px;
    object-fit: cover;
    transition: transform 0.6s;
}

.stem-card:hover img {
    transform: scale(1.1);
}

.stem-overlay {
    position: absolute;
    bottom: 0;
    width: 100%;
    padding: 20px;
    background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.4) 60%, transparent 100%);
    color: #fff;
    transform: translateZ(20px);
}

.stem-tag {
    font-size: 0.75rem;
    padding: 5px 10px;
    border-radius: 20px;
    margin-bottom: 8px;
    display: inline-block;
    font-weight: 600;
}

/* AI LEARNING ASSISTANT - ENHANCED */
.ai-assistant-widget {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 15px;
}

.ai-fab {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: var(--ai-gradient);
    background-size: 200% 200%;
    animation: gradientShift 3s ease infinite, robotFloat 3s ease-in-out infinite;
    color: #fff;
    font-size: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 10px 40px rgba(139, 92, 246, 0.4);
    border: none;
    position: relative;
    transition: all 0.3s ease;
}

@keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

@keyframes robotFloat {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.ai-fab:hover {
    transform: scale(1.1);
    box-shadow: 0 15px 50px rgba(139, 92, 246, 0.6);
}

.ai-fab::before {
    content: '';
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: inherit;
    opacity: 0.5;
    animation: pulseRing 2s infinite;
}

@keyframes pulseRing {
    0% { transform: scale(1); opacity: 0.5; }
    100% { transform: scale(1.6); opacity: 0; }
}

.ai-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    width: 24px;
    height: 24px;
    background: #ef4444;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 700;
    animation: badgePulse 2s infinite;
}

@keyframes badgePulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.2); }
}

/* AI CHAT INTERFACE */
.ai-chat-container {
    position: fixed;
    bottom: 110px;
    right: 30px;
    width: 450px;
    max-height: 600px;
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    display: none;
    flex-direction: column;
    box-shadow: 0 25px 50px rgba(0,0,0,0.3);
    border: 1px solid rgba(255,255,255,0.3);
    z-index: 1001;
    overflow: hidden;
    animation: chatPop 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

@keyframes chatPop {
    from { transform: scale(0.8) translateY(20px); opacity: 0; }
    to { transform: scale(1) translateY(0); opacity: 1; }
}

.ai-chat-header {
    background: var(--ai-gradient);
    background-size: 200% 200%;
    animation: gradientShift 5s ease infinite;
    color: #fff;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.ai-chat-title {
    display: flex;
    align-items: center;
    gap: 12px;
}

.ai-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    animation: pulse 2s infinite;
}

.ai-status {
    display: flex;
    flex-direction: column;
}

.ai-status h6 {
    margin: 0;
    font-weight: 600;
    font-size: 1rem;
}

.ai-status span {
    font-size: 0.8rem;
    opacity: 0.9;
    display: flex;
    align-items: center;
    gap: 6px;
}

.status-dot {
    width: 8px;
    height: 8px;
    background: #4ade80;
    border-radius: 50%;
    animation: blink 2s infinite;
}

@keyframes blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.ai-controls {
    display: flex;
    gap: 10px;
}

.ai-controls button {
    background: rgba(255,255,255,0.2);
    border: none;
    color: #fff;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ai-controls button:hover {
    background: rgba(255,255,255,0.3);
    transform: rotate(90deg);
}

/* Mode Selector */
.ai-modes {
    display: flex;
    gap: 8px;
    padding: 15px 20px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    overflow-x: auto;
}

.mode-btn {
    padding: 8px 16px;
    border-radius: 20px;
    border: 2px solid #e2e8f0;
    background: #fff;
    color: #64748b;
    font-size: 0.85rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 6px;
}

.mode-btn:hover {
    border-color: #8b5cf6;
    color: #8b5cf6;
}

.mode-btn.active {
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    color: #fff;
    border-color: transparent;
}

/* Subject Selector */
.ai-subjects {
    display: flex;
    gap: 10px;
    padding: 10px 20px;
    background: #f8fafc;
}

.subject-btn {
    padding: 6px 14px;
    border-radius: 15px;
    border: 1px solid #cbd5e1;
    background: #fff;
    color: #475569;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.3s;
}

.subject-btn:hover, .subject-btn.active {
    background: #667eea;
    color: #fff;
    border-color: #667eea;
}

/* Chat Messages */
.ai-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    max-height: 350px;
    background: #f8fafc;
}

.message {
    margin-bottom: 15px;
    animation: messageSlide 0.3s ease-out;
}

@keyframes messageSlide {
    from { transform: translateY(10px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.message-user {
    text-align: right;
}

.message-bubble {
    display: inline-block;
    padding: 12px 16px;
    border-radius: 18px;
    max-width: 85%;
    font-size: 0.9rem;
    line-height: 1.5;
    text-align: left;
}

.message-user .message-bubble {
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    color: #fff;
    border-bottom-right-radius: 4px;
}

.message-ai .message-bubble {
    background: #fff;
    color: #1e293b;
    border: 1px solid #e2e8f0;
    border-bottom-left-radius: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

/* Typing Indicator */
.typing-indicator {
    display: flex;
    gap: 5px;
    padding: 15px 20px;
    align-items: center;
    color: #94a3b8;
    font-size: 0.85rem;
}

.typing-indicator span {
    width: 8px;
    height: 8px;
    background: #cbd5e1;
    border-radius: 50%;
    animation: typingBounce 1.4s infinite ease-in-out both;
}

.typing-indicator span:nth-child(1) { animation-delay: -0.32s; }
.typing-indicator span:nth-child(2) { animation-delay: -0.16s; }

@keyframes typingBounce {
    0%, 80%, 100% { transform: scale(0); }
    40% { transform: scale(1); }
}

/* Chat Input */
.ai-input-area {
    padding: 15px 20px;
    background: #fff;
    border-top: 1px solid #e2e8f0;
}

.input-wrapper {
    display: flex;
    gap: 10px;
    align-items: center;
}

.ai-input {
    flex: 1;
    border: 2px solid #e2e8f0;
    border-radius: 25px;
    padding: 12px 20px;
    font-size: 0.9rem;
    transition: all 0.3s;
    background: #f8fafc;
}

.ai-input:focus {
    outline: none;
    border-color: #8b5cf6;
    background: #fff;
}

.ai-send-btn {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    border: none;
    color: #fff;
    font-size: 1.2rem;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ai-send-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 5px 20px rgba(139, 92, 246, 0.4);
}

.ai-send-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

/* Quick Actions */
.ai-quick-actions {
    display: flex;
    gap: 8px;
    margin-top: 10px;
    flex-wrap: wrap;
}

.quick-chip {
    padding: 6px 12px;
    border-radius: 15px;
    background: #f1f5f9;
    color: #64748b;
    font-size: 0.75rem;
    cursor: pointer;
    transition: all 0.3s;
    border: none;
}

.quick-chip:hover {
    background: #8b5cf6;
    color: #fff;
}

/* Voice Button */
.voice-btn {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    border: 2px solid #e2e8f0;
    background: #fff;
    color: #64748b;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.voice-btn:hover {
    border-color: #ef4444;
    color: #ef4444;
}

.voice-btn.recording {
    background: #ef4444;
    color: #fff;
    border-color: #ef4444;
    animation: pulse 1s infinite;
}

/* Scrollbar */
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.1);
    border-radius: 3px;
}

::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.3);
    border-radius: 3px;
}

/* Responsive */
@media (max-width: 768px) {
    .sidebar { 
        width: 280px; 
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    .sidebar.active {
        transform: translateX(0);
    }
    .main-content { 
        margin-left: 0; 
        padding: 20px; 
    }
    .stats-grid { 
        grid-template-columns: 1fr; 
    }
    .ai-chat-container { 
        width: calc(100% - 40px); 
        right: 20px; 
        left: 20px; 
    }
    .welcome-section h4 { 
        font-size: 1.5rem; 
    }
    .courses-slider-3d {
        flex-direction: column;
        align-items: center;
    }
    .course-card-3d {
        width: 100%;
        max-width: 350px;
    }
    .achievements-grid-3d {
        grid-template-columns: 1fr;
    }
    .sidebar-toggle {
        display: flex;
    }
}

@media (min-width: 769px) and (max-width: 1024px) {
    .sidebar { width: 80px; }
    .sidebar .sidebar-header h5 span,
    .sidebar a span { display: none; }
    .sidebar a { justify-content: center; padding: 14px; }
    .main-content { margin-left: 80px !important; }
}
</style>
</head>

<body>

<!-- Animated Particles -->
<div class="particles" id="particles"></div>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg">
<div class="container-fluid">
    <span class="navbar-brand">🎓 Smart LMS</span>
    <div class="user-info ms-auto">
        <div class="user-avatar"><?= substr($first_name, 0, 1) . substr($last_name, 0, 1) ?></div>
        <span><?= $first_name ?> <?= $last_name ?></span>
    </div>
</div>
</nav>

<!-- Mobile Toggle -->
<button class="sidebar-toggle" onclick="toggleSidebar()">
    <i class="bi bi-list"></i>
</button>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h5><i class="bi bi-grid-3x3-gap-fill"></i> <span>Smart LMS</span></h5>
    </div>
    
    <a href="dashboard.php" class="active"><i class="bi bi-speedometer2"></i> <span>Dashboard</span></a>
    <a href="Subjects.php"><i class="bi bi-book-half"></i> <span>Subjects</span></a>
    <a href="https://smart-3d-simulator-learning.onrender.com/"><i class="bi bi-book"></i> <span>My Learning</span></a>
    <a href="adaptive_path.php"><i class="bi bi-sliders"></i> <span>Adaptive Path</span></a>
    <a href="achievements.php"><i class="bi bi-trophy"></i> <span>Achievements</span></a>
    <a href="offline_content.php"><i class="bi bi-wifi-off"></i> <span>Offline Content</span></a>
    <a href="leaderboard.php"><i class="bi bi-bar-chart-line"></i> <span>Leaderboard</span></a>
    <a href="AITutor.php"><i class="bi bi-robot"></i> <span>AI Tutor</span></a>
    <a href="Analytics.php"><i class="bi bi-graph-up-arrow"></i> <span>Analytics</span></a>
    <a href="Settings.php"><i class="bi bi-gear"></i> <span>Settings</span></a>
    
    <div class="sidebar-divider"></div>
    
    <a href="logout.php" class="logout-link"><i class="bi bi-box-arrow-right"></i> <span>Logout</span></a>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">

<!-- Welcome Section -->
<div class="welcome-section">
    <h4>Welcome back, <?= $first_name ?>! 👋</h4>
    <p>Continue your learning journey. You're making great progress!</p>
</div>

<!-- STATS CARDS -->
<div class="stats-grid">

    <div class="stat-card card-progress">
        <div class="card-content">
            <div class="card-icon-wrapper"><i class="bi bi-graph-up-arrow"></i></div>
            <div>
                <h3 class="card-value"><?= $stats['overall_progress'] ?>%</h3>
                <div class="card-label">Overall Progress</div>
            </div>
        </div>
    </div>

    <div class="stat-card card-assessments">
        <div class="card-content">
            <div class="card-icon-wrapper"><i class="bi bi-fire"></i></div>
            <div>
                <h3 class="card-value"><?= $stats['assessments_done'] ?></h3>
                <div class="card-label">Assessments</div>
            </div>
        </div>
    </div>

    <div class="stat-card card-badges">
        <div class="card-content">
            <div class="card-icon-wrapper"><i class="bi bi-trophy-fill"></i></div>
            <div>
                <h3 class="card-value"><?= $stats['badges_earned'] ?></h3>
                <div class="card-label">Badges Earned</div>
            </div>
        </div>
    </div>

    <div class="stat-card card-points">
        <div class="card-content">
            <div class="card-icon-wrapper"><i class="bi bi-gem"></i></div>
            <div>
                <h3 class="card-value"><?= number_format($stats['total_points']) ?></h3>
                <div class="card-label">Total Points</div>
            </div>
        </div>
    </div>

</div>

<!-- COURSES SECTION -->
<div class="courses-section-3d">
    <h5 class="section-title"><i class="bi bi-journal-bookmark"></i> Continue Learning</h5>
    
    <div class="courses-slider-3d">
        <?php foreach ($courses as $course): 
            $progress = $course['total_lessons'] > 0 ? round(($course['completed_lessons'] / $course['total_lessons']) * 100) : 0;
        ?>
        <div class="course-card-3d" onclick="openCourse(<?= $course['subject_id'] ?>)">
            <div class="course-card-inner-3d">
                <div class="course-image-3d" style="background: linear-gradient(135deg, <?= $course['color'] ?>40, <?= $course['color'] ?>20);">
                    <div class="course-icon-3d" style="background: <?= $course['color'] ?>;">
                        <i class="bi bi-<?= $course['icon'] ?? 'book' ?>"></i>
                    </div>
                </div>
                <div class="course-content-3d">
                    <h5 class="course-title-3d"><?= htmlspecialchars($course['subject_name']) ?></h5>
                    <div class="course-progress-3d">
                        <div class="progress-header-3d">
                            <span><?= $course['completed_lessons'] ?>/<?= $course['total_lessons'] ?> Lessons</span>
                            <span><?= $progress ?>%</span>
                        </div>
                        <div class="progress-bar-bg-3d">
                            <div class="progress-bar-fill-3d" style="width: <?= $progress ?>%; background: <?= $course['color'] ?>;"></div>
                        </div>
                    </div>
                    <div class="course-stats-3d">
                        <div class="stat-3d">
                            <i class="bi bi-check-circle" style="color: <?= $course['color'] ?>;"></i>
                            <span><?= $course['completed_lessons'] ?> Done</span>
                        </div>
                        <div class="stat-3d">
                            <i class="bi bi-star" style="color: <?= $course['color'] ?>;"></i>
                            <span><?= $course['mastery_level'] ?>% Mastered</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ACHIEVEMENTS -->
<div class="achievements-section-3d">
    <h5 class="section-title"><i class="bi bi-trophy"></i> Recent Achievements</h5>
    
    <div class="achievements-grid-3d">
        <?php foreach ($achievements as $achievement): ?>
        <div class="achievement-card-3d">
            <div class="achievement-icon-3d" style="background: <?= $achievement['color'] ?>20; color: <?= $achievement['color'] ?>; box-shadow: 0 0 30px <?= $achievement['color'] ?>40;">
                <i class="bi <?= $achievement['icon'] ?>"></i>
            </div>
            <div class="achievement-info-3d">
                <h6><?= $achievement['title'] ?></h6>
                <p><?= $achievement['desc'] ?></p>
                <span class="achievement-points-3d">
                    <i class="bi bi-lightning-charge-fill"></i> +<?= $achievement['points'] ?> XP
                </span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- STEM SECTION -->
<section class="stem-section" id="stem">
    <div class="container-fluid px-0">

        <div class="text-center mb-5">
            <span class="badge bg-info text-dark mb-2">🔬 STEM Education</span>
            <h2 class="fw-bold text-white">Interactive 3D Simulations</h2>
            <p class="text-white-50 mx-auto" style="max-width: 600px;">
                Making science and mathematics easier through visual and gamified learning.
            </p>
        </div>

        <div class="row g-4">
            
            <!-- Chemistry -->
            <div class="col-md-4">
                <div class="stem-card">
                    <img src="https://images.unsplash.com/photo-1635070041078-e363dbe005cb?w=600" class="img-fluid" alt="Chemistry">
                    <div class="stem-overlay">
                        <span class="stem-tag bg-success">Chemistry</span>
                        <h5>Virtual Laboratory</h5>
                        <p>Run safe experiments in a 3D lab.</p>
                        <button class="btn btn-light btn-sm rounded-pill">Try Simulation</button>
                    </div>
                </div>
            </div>

            <!-- Physics -->
            <div class="col-md-4">
                <div class="stem-card">
                    <img src="https://images.unsplash.com/photo-1636466497217-26a8cbeaf0aa?w=600" class="img-fluid" alt="Physics">
                    <div class="stem-overlay">
                        <span class="stem-tag bg-primary">Physics</span>
                        <h5>Mechanics Sandbox</h5>
                        <p>Explore motion, forces, and energy.</p>
                        <button class="btn btn-light btn-sm rounded-pill">Try Simulation</button>
                    </div>
                </div>
            </div>

            <!-- Math -->
            <div class="col-md-4">
                <div class="stem-card">
                    <img src="https://images.unsplash.com/photo-1509228468518-180dd4864904?w=600" class="img-fluid" alt="Mathematics">
                    <div class="stem-overlay">
                        <span class="stem-tag bg-warning text-dark">Mathematics</span>
                        <h5>3D Graph Tool</h5>
                        <p>Visualize equations in 3D.</p>
                        <button class="btn btn-light btn-sm rounded-pill">Try Simulation</button>
                    </div>
                </div>
            </div>

        </div>

        <!-- Gamification -->
        <div class="mt-5 p-4 rounded-4" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.2);">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h5 class="fw-bold text-white">
                        🎮 Gamified Learning
                    </h5>
                    <p class="text-white-75 mb-0">
                        Earn points, unlock badges, and compete with other students.
                    </p>
                </div>
                <div class="col-lg-4 text-end mt-3 mt-lg-0">
                    <button class="btn btn-primary rounded-pill px-4">
                        View Leaderboard
                    </button>
                </div>
            </div>
        </div>

    </div>
</section>

</div><!-- End Main Content -->

<!-- AI LEARNING ASSISTANT WIDGET -->
<div class="ai-assistant-widget">
    
    <!-- AI Chat Interface -->
    <div class="ai-chat-container" id="aiChatContainer">
        
        <!-- Header -->
        <div class="ai-chat-header">
            <div class="ai-chat-title">
                <div class="ai-avatar">🤖</div>
                <div class="ai-status">
                    <h6>AI Learning Assistant</h6>
                    <span><span class="status-dot"></span> Online & Ready</span>
                </div>
            </div>
            <div class="ai-controls">
                <button onclick="minimizeChat()" title="Minimize"><i class="bi bi-dash-lg"></i></button>
                <button onclick="closeChat()" title="Close"><i class="bi bi-x-lg"></i></button>
            </div>
        </div>

        <!-- Mode Selector -->
        <div class="ai-modes">
            <button class="mode-btn active" data-mode="tutor" onclick="setMode('tutor')">
                <i class="bi bi-mortarboard"></i> Tutor
            </button>
            <button class="mode-btn" data-mode="homework_help" onclick="setMode('homework_help')">
                <i class="bi bi-journal-check"></i> Homework
            </button>
            <button class="mode-btn" data-mode="quick_answer" onclick="setMode('quick_answer')">
                <i class="bi bi-lightning"></i> Quick
            </button>
            <button class="mode-btn" data-mode="exam_prep" onclick="setMode('exam_prep')">
                <i class="bi bi-clipboard-check"></i> Exam Prep
            </button>
        </div>

        <!-- Subject Selector -->
        <div class="ai-subjects">
            <button class="subject-btn active" data-subject="mathematics" onclick="setSubject('mathematics')">Mathematics</button>
            <button class="subject-btn" data-subject="physical_science" onclick="setSubject('physical_science')">Physical Science</button>
        </div>

        <!-- Messages Area -->
        <div class="ai-messages" id="aiMessages">
            <div class="message message-ai">
                <div class="message-bubble">
                    👋 Hello <?= $first_name ?>! I'm your AI Learning Assistant for <strong>Mathematics</strong> and <strong>Physical Science</strong>.
                    <br><br>
                    Select a mode above and ask me anything! I can help with:
                    <ul style="margin: 10px 0; padding-left: 20px;">
                        <li>Step-by-step problem solving</li>
                        <li>Concept explanations</li>
                        <li>Homework assistance</li>
                        <li>Exam preparation</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Typing Indicator -->
        <div class="typing-indicator" id="typingIndicator" style="display: none;">
            <span></span><span></span><span></span>
            <span style="margin-left: 10px;">AI is thinking...</span>
        </div>

        <!-- Quick Actions -->
        <div class="ai-quick-actions" id="quickActions">
            <button class="quick-chip" onclick="sendQuick('Help me solve 2x + 5 = 15')">Solve equation</button>
            <button class="quick-chip" onclick="sendQuick('Explain Pythagorean theorem')">Pythagorean theorem</button>
            <button class="quick-chip" onclick="sendQuick('What is Newton\'s second law?')">Newton's laws</button>
            <button class="quick-chip" onclick="sendQuick('Practice problems for algebra')">Practice algebra</button>
        </div>

        <!-- Input Area -->
        <div class="ai-input-area">
            <div class="input-wrapper">
                <button class="voice-btn" id="voiceBtn" onclick="toggleVoice()" title="Voice input">
                    <i class="bi bi-mic"></i>
                </button>
                <input type="text" class="ai-input" id="aiInput" placeholder="Ask your question..." 
                    onkeypress="if(event.key==='Enter')sendMessage()">
                <button class="ai-send-btn" id="sendBtn" onclick="sendMessage()">
                    <i class="bi bi-send-fill"></i>
                </button>
            </div>
        </div>

    </div>

    <!-- Floating Action Button -->
    <div class="ai-fab" onclick="toggleChat()" id="aiFab">
        🤖
        <span class="ai-badge" id="aiBadge" style="display: none;">1</span>
    </div>

</div>

<script>
// Create floating particles
function createParticles() {
    const container = document.getElementById('particles');
    for (let i = 0; i < 15; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        particle.style.left = Math.random() * 100 + '%';
        particle.style.animationDelay = Math.random() * 20 + 's';
        particle.style.animationDuration = (15 + Math.random() * 10) + 's';
        particle.style.width = (5 + Math.random() * 10) + 'px';
        particle.style.height = particle.style.width;
        container.appendChild(particle);
    }
}
createParticles();

// Sidebar Toggle
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
    document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
}

// Close sidebar when clicking links on mobile
document.querySelectorAll('.sidebar a').forEach(link => {
    link.addEventListener('click', () => {
        if (window.innerWidth <= 768) {
            toggleSidebar();
        }
    });
});

// AI Learning Assistant State
let currentMode = 'tutor';
let currentSubject = 'mathematics';
let isTyping = false;
let conversationHistory = [];

// Toggle Chat
function toggleChat() {
    const chat = document.getElementById('aiChatContainer');
    const fab = document.getElementById('aiFab');
    
    if (chat.style.display === 'flex') {
        chat.style.animation = 'chatPop 0.3s ease-out reverse';
        setTimeout(() => {
            chat.style.display = 'none';
            chat.style.animation = '';
        }, 300);
        fab.innerHTML = '🤖<span class="ai-badge" id="aiBadge" style="display: none;">1</span>';
    } else {
        chat.style.display = 'flex';
        fab.innerHTML = '<i class="bi bi-chevron-down"></i>';
        document.getElementById('aiInput').focus();
    }
}

// Minimize/Close Chat
function minimizeChat() { toggleChat(); }
function closeChat() {
    const chat = document.getElementById('aiChatContainer');
    const fab = document.getElementById('aiFab');
    chat.style.display = 'none';
    fab.innerHTML = '🤖<span class="ai-badge" id="aiBadge" style="display: none;">1</span>';
}

// Set Mode & Subject
function setMode(mode) { 
    currentMode = mode; 
    // Update active state
    document.querySelectorAll('.mode-btn').forEach(btn => {
        btn.classList.remove('active');
        if(btn.dataset.mode === mode) btn.classList.add('active');
    });
    updateQuickActions(); 
}

function setSubject(subject) { 
    currentSubject = subject; 
    // Update active state
    document.querySelectorAll('.subject-btn').forEach(btn => {
        btn.classList.remove('active');
        if(btn.dataset.subject === subject) btn.classList.add('active');
    });
    addMessage(`Switched to ${subject === 'mathematics' ? 'Mathematics' : 'Physical Science'}`, 'system'); 
    updateQuickActions(); 
}

function updateQuickActions() {
    const actions = {
        tutor: ['Solve equation', 'Explain concept', 'Show steps', 'Practice problem'],
        homework_help: ['Check my answer', 'Help with problem', 'Explain solution', 'Similar examples'],
        quick_answer: ['Quick formula', 'Definition', 'Key concept', 'Summary'],
        exam_prep: ['Practice test', 'Review topic', 'Key formulas', 'Test strategies']
    };
    const container = document.getElementById('quickActions');
    container.innerHTML = actions[currentMode].map(a => `<button class="quick-chip" onclick="sendQuick('${a}')">${a}</button>`).join('');
}

// Send Quick Message
function sendQuick(text) { document.getElementById('aiInput').value = text; sendMessage(); }

// Show/Hide Typing
function showTyping() {
    document.getElementById('typingIndicator').style.display = 'flex';
    isTyping = true;
}

function hideTyping() {
    document.getElementById('typingIndicator').style.display = 'none';
    isTyping = false;
}

// Add Message to Chat
function addMessage(text, sender) {
    const container = document.getElementById('aiMessages');
    const div = document.createElement('div');
    div.className = `message message-${sender}`;
    div.innerHTML = `<div class="message-bubble">${escapeHtml(text)}</div>`;
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
}

// Escape HTML
function escapeHtml(text) { 
    const div = document.createElement('div'); 
    div.textContent = text; 
    return div.innerHTML; 
}

// Send Message to AI
async function sendMessage() {
    const input = document.getElementById('aiInput');
    const message = input.value.trim();
    if (!message || isTyping) return;

    addMessage(message, 'user');
    input.value = '';
    showTyping();

    try {
        const response = await fetch('learning_assistant.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                message: message,
                assistant_mode: currentMode,
                subject: currentSubject,
                conversation_tail: conversationHistory.slice(-5)
            })
        });

        const data = await response.json();
        hideTyping();

        if (data.success) {
            addMessage(data.response, 'ai');
            conversationHistory.push(
                { role: 'user', content: message },
                { role: 'assistant', content: data.response }
            );
            if (conversationHistory.length > 10) conversationHistory = conversationHistory.slice(-10);

            // Voice reply
            speak(data.response);
        } else {
            addMessage('Sorry, something went wrong. Please try again.', 'ai');
        }
    } catch (err) {
        hideTyping();
        console.error(err);
        addMessage('Connection error. Please check your internet connection.', 'ai');
    }
}

// Text-to-Speech (AI voice)
function speak(text) {
    if ('speechSynthesis' in window) {
        // Stop any current speech
        speechSynthesis.cancel();
        
        const utter = new SpeechSynthesisUtterance(text);
        utter.lang = 'en-US';
        utter.rate = 1;
        utter.pitch = 1;
        speechSynthesis.speak(utter);
    }
}

// Voice Input (Speech-to-Text)
let recognition = null;
if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    recognition = new SpeechRecognition();
    recognition.continuous = false;
    recognition.interimResults = false;
    recognition.lang = 'en-US';

    recognition.onresult = (event) => {
        const transcript = event.results[0][0].transcript;
        document.getElementById('aiInput').value = transcript;
        sendMessage();
    };
    
    recognition.onerror = (event) => { 
        console.error('Speech recognition error:', event.error); 
        document.getElementById('voiceBtn').classList.remove('recording');
    }
    
    recognition.onend = () => { 
        document.getElementById('voiceBtn').classList.remove('recording'); 
    }
}

// Toggle voice input
function toggleVoice() {
    if (!recognition) { 
        alert('Voice input is not supported in your browser.'); 
        return; 
    }
    
    const btn = document.getElementById('voiceBtn');
    if (btn.classList.contains('recording')) { 
        recognition.stop(); 
        btn.classList.remove('recording'); 
    } else { 
        recognition.start(); 
        btn.classList.add('recording'); 
    }
}

// Open Course
function openCourse(id) {
    window.location.href = 'course_detail.php?id=' + id;
}

// Card hover effects for stat cards
document.querySelectorAll('.stat-card').forEach(card => {
    card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        card.style.transform = `perspective(1000px) rotateX(${(y - centerY)/20}deg) rotateY(${(centerX - x)/20}deg) translateY(-15px) scale(1.03)`;
    });
    
    card.addEventListener('mouseleave', () => { 
        card.style.transform = ''; 
    });
});

// Animate progress bars on load
window.addEventListener('load', () => {
    document.querySelectorAll('.progress-bar-fill-3d').forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0';
        setTimeout(() => {
            bar.style.width = width;
        }, 500);
    });
});
</script>

</body>
</html>