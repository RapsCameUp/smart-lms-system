
html_content = '''<?php
session_start();
require 'db_config.php';

// Check if user is logged in as parent
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'parent') {
    header("Location: login.php");
    exit();
}

$parent_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'] ?? 'Parent';
$last_name = $_SESSION['last_name'] ?? '';

// Initialize children array
$children = [];
$selected_child_id = $_GET['child_id'] ?? null;

// Fetch parent's children from database
if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
    try {
        $children_query = "SELECT u.user_id, u.first_name, u.last_name, u.grade_level, 
                                  sp.student_id, sp.relationship
                           FROM users u
                           JOIN student_parents sp ON u.user_id = sp.student_id
                           WHERE sp.parent_id = ? AND u.role = 'student' AND u.is_active = 1";
        $stmt = $conn->prepare($children_query);
        if ($stmt) {
            $stmt->bind_param("i", $parent_id);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $children[] = $row;
            }
            $stmt->close();
        }
    } catch (Exception $e) {
        error_log("Error fetching children: " . $e->getMessage());
    }
}

// If no children found, use demo data
if (empty($children)) {
    $children = [
        ['user_id' => 1, 'first_name' => 'John', 'last_name' => 'Doe', 'grade_level' => '10', 'relationship' => 'Father'],
        ['user_id' => 2, 'first_name' => 'Jane', 'last_name' => 'Doe', 'grade_level' => '8', 'relationship' => 'Father']
    ];
}

// Select first child if none selected
if (!$selected_child_id && !empty($children)) {
    $selected_child_id = $children[0]['user_id'];
}

// Get selected child details
$selected_child = null;
foreach ($children as $child) {
    if ($child['user_id'] == $selected_child_id) {
        $selected_child = $child;
        break;
    }
}

// Initialize stats
$child_stats = [
    'overall_progress' => 75,
    'assessments_done' => 12,
    'attendance_rate' => 92,
    'total_points' => 2450,
    'current_streak' => 7,
    'subjects_enrolled' => 4,
    'average_score' => 78,
    'time_spent' => 45
];

// Fetch child statistics from database
if ($selected_child_id && isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
    try {
        // Get overall progress
        $table_check = $conn->query("SHOW TABLES LIKE 'concept_mastery'");
        if ($table_check && $table_check->num_rows > 0) {
            $progress_query = "SELECT AVG(mastery_level) as avg_mastery FROM concept_mastery WHERE user_id = ?";
            $stmt = $conn->prepare($progress_query);
            if ($stmt) {
                $stmt->bind_param("i", $selected_child_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result && $row = $result->fetch_assoc()) {
                    $child_stats['overall_progress'] = round($row['avg_mastery'] ?? 75);
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
                $stmt->bind_param("i", $selected_child_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result && $row = $result->fetch_assoc()) {
                    $child_stats['assessments_done'] = $row['count'] ?? 12;
                }
                $stmt->close();
            }
        }

        // Get total points
        $table_check = $conn->query("SHOW TABLES LIKE 'gamification_points'");
        if ($table_check && $table_check->num_rows > 0) {
            $points_query = "SELECT total_points FROM gamification_points WHERE user_id = ?";
            $stmt = $conn->prepare($points_query);
            if ($stmt) {
                $stmt->bind_param("i", $selected_child_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result && $row = $result->fetch_assoc()) {
                    $child_stats['total_points'] = $row['total_points'] ?? 2450;
                }
                $stmt->close();
            }
        }

        // Get attendance rate
        $table_check = $conn->query("SHOW TABLES LIKE 'attendance'");
        if ($table_check && $table_check->num_rows > 0) {
            $attendance_query = "SELECT 
                COUNT(*) as total_days,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days
                FROM attendance WHERE user_id = ? AND date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $stmt = $conn->prepare($attendance_query);
            if ($stmt) {
                $stmt->bind_param("i", $selected_child_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result && $row = $result->fetch_assoc()) {
                    if ($row['total_days'] > 0) {
                        $child_stats['attendance_rate'] = round(($row['present_days'] / $row['total_days']) * 100);
                    }
                }
                $stmt->close();
            }
        }

        // Get learning streak
        $table_check = $conn->query("SHOW TABLES LIKE 'learning_streaks'");
        if ($table_check && $table_check->num_rows > 0) {
            $streak_query = "SELECT current_streak FROM learning_streaks WHERE user_id = ?";
            $stmt = $conn->prepare($streak_query);
            if ($stmt) {
                $stmt->bind_param("i", $selected_child_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result && $row = $result->fetch_assoc()) {
                    $child_stats['current_streak'] = $row['current_streak'] ?? 7;
                }
                $stmt->close();
            }
        }

    } catch (Exception $e) {
        error_log("Database error in parent dashboard: " . $e->getMessage());
    }
}

// Fetch child's courses
$child_courses = [
    ['subject_id' => 1, 'subject_name' => 'Mathematics', 'icon' => 'calculator', 'mastery_level' => 75, 'completed_lessons' => 12, 'total_lessons' => 20, 'color' => '#667eea', 'last_activity' => '2 hours ago'],
    ['subject_id' => 2, 'subject_name' => 'Physical Sciences', 'icon' => 'atom', 'mastery_level' => 68, 'completed_lessons' => 8, 'total_lessons' => 15, 'color' => '#10b981', 'last_activity' => '1 day ago'],
    ['subject_id' => 3, 'subject_name' => 'Life Sciences', 'icon' => 'flower1', 'mastery_level' => 82, 'completed_lessons' => 10, 'total_lessons' => 12, 'color' => '#f59e0b', 'last_activity' => '3 hours ago'],
    ['subject_id' => 4, 'subject_name' => 'English', 'icon' => 'book', 'mastery_level' => 90, 'completed_lessons' => 18, 'total_lessons' => 20, 'color' => '#ec4899', 'last_activity' => '5 hours ago']
];

// Recent activities
$recent_activities = [
    ['icon' => 'bi-check-circle-fill', 'title' => 'Completed Algebra Quiz', 'subject' => 'Mathematics', 'time' => '2 hours ago', 'score' => '85%', 'color' => '#10b981'],
    ['icon' => 'bi-play-circle-fill', 'title' => 'Watched Video: Cell Structure', 'subject' => 'Life Sciences', 'time' => '3 hours ago', 'duration' => '15 min', 'color' => '#f59e0b'],
    ['icon' => 'bi-trophy-fill', 'title' => 'Earned Badge: Quick Learner', 'subject' => 'General', 'time' => '1 day ago', 'points' => '+150 XP', 'color' => '#8b5cf6'],
    ['icon' => 'bi-file-earmark-text-fill', 'title' => 'Submitted Essay', 'subject' => 'English', 'time' => '2 days ago', 'status' => 'Pending', 'color' => '#3b82f6']
];

// Upcoming deadlines
$upcoming_deadlines = [
    ['title' => 'Mathematics Assignment', 'subject' => 'Mathematics', 'due_date' => 'Tomorrow', 'urgency' => 'high', 'color' => '#667eea'],
    ['title' => 'Science Project', 'subject' => 'Physical Sciences', 'due_date' => '3 days', 'urgency' => 'medium', 'color' => '#10b981'],
    ['title' => 'English Essay', 'subject' => 'English', 'due_date' => '1 week', 'urgency' => 'low', 'color' => '#ec4899']
];

// Performance trends (last 7 days)
$performance_data = [65, 72, 68, 75, 80, 78, 82];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Parent Dashboard - Smart LMS</title>

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
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    animation: float 25s infinite linear;
}

@keyframes float {
    0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
    10% { opacity: 1; }
    90% { opacity: 1; }
    100% { transform: translateY(-100vh) rotate(720deg); opacity: 0; }
}

/* NAVBAR */
.navbar {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(20px);
    border-bottom: 1px solid var(--glass-border);
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
    display: flex;
    align-items: center;
    gap: 10px;
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
    background: var(--parent-gradient);
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

/* Child Selector */
.child-selector {
    background: var(--card-bg);
    backdrop-filter: blur(20px);
    border: 1px solid var(--glass-border);
    border-radius: 20px;
    padding: 20px;
    margin-bottom: 30px;
    animation: fadeInUp 0.8s ease-out;
}

.child-tabs {
    display: flex;
    gap: 15px;
    overflow-x: auto;
    padding-bottom: 5px;
}

.child-tab {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px 25px;
    background: rgba(255,255,255,0.05);
    border: 2px solid transparent;
    border-radius: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 200px;
}

.child-tab:hover {
    background: rgba(255,255,255,0.1);
    transform: translateY(-3px);
}

.child-tab.active {
    background: rgba(240, 147, 251, 0.2);
    border-color: rgba(240, 147, 251, 0.5);
    box-shadow: 0 10px 30px rgba(240, 147, 251, 0.2);
}

.child-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.2rem;
    border: 3px solid rgba(255,255,255,0.3);
}

.child-tab.active .child-avatar {
    background: var(--parent-gradient);
}

.child-info h6 {
    margin: 0;
    font-weight: 600;
    color: #fff;
}

.child-info span {
    font-size: 0.85rem;
    color: rgba(255,255,255,0.6);
}

@keyframes fadeInUp {
    from { transform: translateY(30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* Welcome Section */
.welcome-section {
    margin-bottom: 35px;
    animation: fadeInUp 0.8s ease-out 0.1s backwards;
}

.welcome-section h4 {
    color: #fff;
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 8px;
    text-shadow: 2px 2px 8px rgba(0,0,0,0.3);
}

.welcome-section p {
    color: rgba(255,255,255,0.7);
    font-size: 1.1rem;
}

/* STATS CARDS */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    position: relative;
    border-radius: 24px;
    overflow: hidden;
    height: 180px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    cursor: pointer;
    animation: cardEntrance 0.8s ease-out backwards;
    border: 1px solid rgba(255,255,255,0.1);
    background: var(--card-bg);
    backdrop-filter: blur(20px);
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
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 30px 60px rgba(0,0,0,0.3);
}

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
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    border: 2px solid rgba(255,255,255,0.2);
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
}

.card-progress .card-icon-wrapper { color: #4ade80; border-color: rgba(74, 222, 128, 0.3); background: rgba(74, 222, 128, 0.1); }
.card-assessments .card-icon-wrapper { color: #fb7185; border-color: rgba(251, 113, 133, 0.3); background: rgba(251, 113, 133, 0.1); }
.card-attendance .card-icon-wrapper { color: #60a5fa; border-color: rgba(96, 165, 250, 0.3); background: rgba(96, 165, 250, 0.1); }
.card-points .card-icon-wrapper { color: #fbbf24; border-color: rgba(251, 191, 36, 0.3); background: rgba(251, 191, 36, 0.1); }

.card-value {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0;
    line-height: 1;
    text-shadow: 2px 2px 8px rgba(0,0,0,0.4);
}

.card-label {
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    opacity: 0.8;
    margin-top: 8px;
    font-weight: 500;
}

.card-subtext {
    font-size: 0.85rem;
    color: rgba(255,255,255,0.6);
    margin-top: 5px;
}

/* DASHBOARD GRID */
.dashboard-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
    margin-bottom: 40px;
}

@media (max-width: 1200px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}

/* SECTION CARDS */
.section-card {
    background: var(--card-bg);
    backdrop-filter: blur(20px);
    border: 1px solid var(--glass-border);
    border-radius: 24px;
    padding: 25px;
    animation: cardEntrance 0.8s ease-out backwards;
}

.section-title {
    font-size: 1.3rem;
    font-weight: 600;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 12px;
    color: #fff;
}

.section-title i {
    color: #f093fb;
    font-size: 1.5rem;
}

/* COURSES LIST */
.courses-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.course-item {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px;
    background: rgba(255,255,255,0.03);
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,0.05);
    transition: all 0.3s ease;
    cursor: pointer;
}

.course-item:hover {
    background: rgba(255,255,255,0.08);
    transform: translateX(10px);
    border-color: rgba(255,255,255,0.1);
}

.course-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    flex-shrink: 0;
}

.course-details {
    flex: 1;
}

.course-details h6 {
    margin: 0 0 8px 0;
    font-weight: 600;
    color: #fff;
    font-size: 1.1rem;
}

.course-meta {
    display: flex;
    gap: 15px;
    font-size: 0.85rem;
    color: rgba(255,255,255,0.6);
}

.course-progress {
    width: 100px;
    text-align: right;
}

.progress-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: conic-gradient(var(--course-color) calc(var(--progress) * 1%), rgba(255,255,255,0.1) 0);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: auto;
    margin-bottom: 8px;
    position: relative;
}

.progress-circle::before {
    content: '';
    position: absolute;
    width: 40px;
    height: 40px;
    background: rgba(15, 23, 42, 0.9);
    border-radius: 50%;
}

.progress-text {
    position: relative;
    font-weight: 600;
    font-size: 0.9rem;
    color: #fff;
}

.last-activity {
    font-size: 0.8rem;
    color: rgba(255,255,255,0.5);
}

/* ACTIVITY FEED */
.activity-feed {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.activity-item {
    display: flex;
    gap: 15px;
    padding: 15px;
    background: rgba(255,255,255,0.03);
    border-radius: 16px;
    border-left: 4px solid var(--activity-color);
    transition: all 0.3s ease;
}

.activity-item:hover {
    background: rgba(255,255,255,0.06);
    transform: translateX(5px);
}

.activity-icon {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    background: rgba(255,255,255,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    color: var(--activity-color);
    flex-shrink: 0;
}

.activity-content h6 {
    margin: 0 0 5px 0;
    font-weight: 600;
    color: #fff;
    font-size: 0.95rem;
}

.activity-content p {
    margin: 0 0 5px 0;
    font-size: 0.85rem;
    color: rgba(255,255,255,0.6);
}

.activity-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.8rem;
}

.activity-time {
    color: rgba(255,255,255,0.5);
}

.activity-badge {
    padding: 4px 10px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.75rem;
}

/* UPCOMING DEADLINES */
.deadlines-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.deadline-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 18px;
    background: rgba(255,255,255,0.03);
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,0.05);
    transition: all 0.3s ease;
}

.deadline-item:hover {
    background: rgba(255,255,255,0.06);
    transform: translateX(5px);
}

.deadline-urgency {
    width: 4px;
    height: 50px;
    border-radius: 2px;
    flex-shrink: 0;
}

.deadline-urgency.high { background: #ef4444; box-shadow: 0 0 10px rgba(239, 68, 68, 0.5); }
.deadline-urgency.medium { background: #f59e0b; box-shadow: 0 0 10px rgba(245, 158, 11, 0.5); }
.deadline-urgency.low { background: #10b981; box-shadow: 0 0 10px rgba(16, 185, 129, 0.5); }

.deadline-content {
    flex: 1;
}

.deadline-content h6 {
    margin: 0 0 5px 0;
    font-weight: 600;
    color: #fff;
}

.deadline-content span {
    font-size: 0.85rem;
    color: rgba(255,255,255,0.6);
}

.deadline-date {
    text-align: right;
}

.deadline-date .date {
    font-weight: 600;
    color: #fff;
    display: block;
}

.deadline-date .label {
    font-size: 0.75rem;
    color: rgba(255,255,255,0.5);
}

/* PERFORMANCE CHART */
.performance-section {
    margin-bottom: 40px;
}

.chart-container {
    height: 300px;
    position: relative;
    background: rgba(255,255,255,0.03);
    border-radius: 16px;
    padding: 20px;
}

/* QUICK ACTIONS */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.action-card {
    background: var(--card-bg);
    backdrop-filter: blur(20px);
    border: 1px solid var(--glass-border);
    border-radius: 20px;
    padding: 25px;
    text-align: center;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    animation: cardEntrance 0.8s ease-out backwards;
}

.action-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    border-color: rgba(240, 147, 251, 0.3);
}

.action-icon {
    width: 70px;
    height: 70px;
    border-radius: 20px;
    background: var(--parent-gradient);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin: 0 auto 15px;
    color: #fff;
    box-shadow: 0 10px 30px rgba(240, 147, 251, 0.3);
}

.action-card h6 {
    margin: 0 0 8px 0;
    font-weight: 600;
    color: #fff;
    font-size: 1.1rem;
}

.action-card p {
    margin: 0;
    font-size: 0.9rem;
    color: rgba(255,255,255,0.6);
}

/* NOTIFICATIONS WIDGET */
.notifications-widget {
    position: fixed;
    top: 100px;
    right: 30px;
    width: 350px;
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    box-shadow: 0 25px 50px rgba(0,0,0,0.3);
    border: 1px solid rgba(255,255,255,0.3);
    z-index: 1001;
    display: none;
    overflow: hidden;
    animation: slideIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

.notifications-header {
    background: var(--parent-gradient);
    color: #fff;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.notifications-header h6 {
    margin: 0;
    font-weight: 600;
}

.notifications-list {
    max-height: 400px;
    overflow-y: auto;
    padding: 15px;
}

.notification-item {
    display: flex;
    gap: 12px;
    padding: 15px;
    border-radius: 12px;
    margin-bottom: 10px;
    background: #f8fafc;
    transition: all 0.3s ease;
}

.notification-item:hover {
    background: #f1f5f9;
    transform: translateX(5px);
}

.notification-item.unread {
    background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(236, 72, 153, 0.1));
    border-left: 3px solid #8b5cf6;
}

.notification-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.notification-content p {
    margin: 0 0 5px 0;
    font-size: 0.9rem;
    color: #1e293b;
    font-weight: 500;
}

.notification-content span {
    font-size: 0.8rem;
    color: #64748b;
}

/* Scrollbar */
::-webkit-scrollbar {
    width: 6px;
    height: 6px;
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
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    .child-tabs {
        flex-wrap: nowrap;
    }
    .welcome-section h4 { 
        font-size: 1.5rem; 
    }
    .notifications-widget {
        width: calc(100% - 40px);
        right: 20px;
        left: 20px;
    }
    .sidebar-toggle {
        display: flex;
    }
    .quick-actions {
        grid-template-columns: 1fr;
    }
}

@media (min-width: 769px) and (max-width: 1024px) {
    .sidebar { width: 80px; }
    .sidebar .sidebar-header h5 span,
    .sidebar a span { display: none; }
    .sidebar a { justify-content: center; padding: 14px; }
    .main-content { margin-left: 80px !important; }
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}
</style>
</head>

<body>

<!-- Animated Particles -->
<div class="particles" id="particles"></div>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg">
<div class="container-fluid">
    <span class="navbar-brand"><i class="bi bi-shield-check"></i> Smart LMS Parent</span>
    <div class="user-info ms-auto">
        <button class="btn btn-outline-light rounded-pill px-3 me-3" onclick="toggleNotifications()">
            <i class="bi bi-bell-fill"></i>
            <span class="badge bg-danger ms-1">3</span>
        </button>
        <div class="user-avatar"><?= substr($first_name, 0, 1) . substr($last_name, 0, 1) ?></div>
        <span class="d-none d-md-block"><?= $first_name ?> <?= $last_name ?></span>
    </div>
</div>
</nav>

<!-- Notifications Widget -->
<div class="notifications-widget" id="notificationsWidget">
    <div class="notifications-header">
        <h6><i class="bi bi-bell-fill me-2"></i>Notifications</h6>
        <button class="btn btn-sm btn-light rounded-pill" onclick="toggleNotifications()">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <div class="notifications-list">
        <div class="notification-item unread">
            <div class="notification-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div class="notification-content">
                <p>Assignment overdue: Math homework</p>
                <span>John - 2 hours ago</span>
            </div>
        </div>
        <div class="notification-item unread">
            <div class="notification-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                <i class="bi bi-trophy-fill"></i>
            </div>
            <div class="notification-content">
                <p>New badge earned: Quick Learner</p>
                <span>Jane - 5 hours ago</span>
            </div>
        </div>
        <div class="notification-item unread">
            <div class="notification-icon" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
            <div class="notification-content">
                <p>Weekly progress report available</p>
                <span>System - 1 day ago</span>
            </div>
        </div>
        <div class="notification-item">
            <div class="notification-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div class="notification-content">
                <p>Parent-teacher meeting scheduled</p>
                <span>Admin - 2 days ago</span>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Toggle -->
<button class="sidebar-toggle" onclick="toggleSidebar()">
    <i class="bi bi-list"></i>
</button>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h5><i class="bi bi-shield-check"></i> <span>Parent Portal</span></h5>
    </div>
    
    <a href="parent_dashboard.php" class="active"><i class="bi bi-speedometer2"></i> <span>Dashboard</span></a>
    <a href="children_progress.php"><i class="bi bi-graph-up"></i> <span>Progress</span></a>
    <a href="children_activities.php"><i class="bi bi-clock-history"></i> <span>Activities</span></a>
    <a href="assignments.php"><i class="bi bi-journal-check"></i> <span>Assignments</span></a>
    <a href="attendance.php"><i class="bi bi-calendar-check"></i> <span>Attendance</span></a>
    <a href="reports.php"><i class="bi bi-file-earmark-text"></i> <span>Reports</span></a>
    <a href="messages.php"><i class="bi bi-chat-dots"></i> <span>Messages</span></a>
    <a href="settings.php"><i class="bi bi-gear"></i> <span>Settings</span></a>
    
    <div class="sidebar-divider"></div>
    
    <a href="logout.php" class="logout-link"><i class="bi bi-box-arrow-right"></i> <span>Logout</span></a>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">

<!-- Child Selector -->
<div class="child-selector">
    <h6 class="mb-3" style="color: rgba(255,255,255,0.8);"><i class="bi bi-people-fill me-2"></i>Select Child</h6>
    <div class="child-tabs">
        <?php foreach ($children as $index => $child): ?>
        <div class="child-tab <?= $child['user_id'] == $selected_child_id ? 'active' : '' ?>" 
             onclick="selectChild(<?= $child['user_id'] ?>)">
            <div class="child-avatar">
                <?= substr($child['first_name'], 0, 1) . substr($child['last_name'], 0, 1) ?>
            </div>
            <div class="child-info">
                <h6><?= htmlspecialchars($child['first_name'] . ' ' . $child['last_name']) ?></h6>
                <span>Grade <?= $child['grade_level'] ?></span>
            </div>
        </div>
        <?php endforeach; ?>
        <div class="child-tab" onclick="addChild()" style="border: 2px dashed rgba(255,255,255,0.2); background: transparent;">
            <div class="child-avatar" style="background: rgba(255,255,255,0.1);">
                <i class="bi bi-plus-lg"></i>
            </div>
            <div class="child-info">
                <h6>Add Child</h6>
                <span>Link new account</span>
            </div>
        </div>
    </div>
</div>

<!-- Welcome Section -->
<div class="welcome-section">
    <h4><?= $selected_child ? htmlspecialchars($selected_child['first_name'] . "'s") : 'Child' ?> Learning Overview</h4>
    <p>Track progress, view activities, and stay updated with your child's educational journey.</p>
</div>

<!-- STATS CARDS -->
<div class="stats-grid">

    <div class="stat-card card-progress">
        <div class="card-content">
            <div class="card-icon-wrapper"><i class="bi bi-graph-up-arrow"></i></div>
            <div>
                <h3 class="card-value"><?= $child_stats['overall_progress'] ?>%</h3>
                <div class="card-label">Overall Progress</div>
                <div class="card-subtext">+5% this week</div>
            </div>
        </div>
    </div>

    <div class="stat-card card-assessments">
        <div class="card-content">
            <div class="card-icon-wrapper"><i class="bi bi-fire"></i></div>
            <div>
                <h3 class="card-value"><?= $child_stats['current_streak'] ?></h3>
                <div class="card-label">Day Streak</div>
                <div class="card-subtext">Keep it up!</div>
            </div>
        </div>
    </div>

    <div class="stat-card card-attendance">
        <div class="card-content">
            <div class="card-icon-wrapper"><i class="bi bi-calendar-check"></i></div>
            <div>
                <h3 class="card-value"><?= $child_stats['attendance_rate'] ?>%</h3>
                <div class="card-label">Attendance</div>
                <div class="card-subtext">Last 30 days</div>
            </div>
        </div>
    </div>

    <div class="stat-card card-points">
        <div class="card-content">
            <div class="card-icon-wrapper"><i class="bi bi-gem"></i></div>
            <div>
                <h3 class="card-value"><?= number_format($child_stats['total_points']) ?></h3>
                <div class="card-label">Total Points</div>
                <div class="card-subtext">Level 5 Scholar</div>
            </div>
        </div>
    </div>

</div>

<!-- QUICK ACTIONS -->
<div class="quick-actions">
    <div class="action-card" onclick="viewDetailedReport()">
        <div class="action-icon"><i class="bi bi-file-earmark-bar-graph"></i></div>
        <h6>Detailed Report</h6>
        <p>View comprehensive analytics</p>
    </div>
    <div class="action-card" onclick="contactTeacher()">
        <div class="action-icon"><i class="bi bi-chat-dots"></i></div>
        <h6>Contact Teacher</h6>
        <p>Message educators directly</p>
    </div>
    <div class="action-card" onclick="setGoals()">
        <div class="action-icon"><i class="bi bi-bullseye"></i></div>
        <h6>Set Goals</h6>
        <p>Define learning objectives</p>
    </div>
    <div class="action-card" onclick="scheduleMeeting()">
        <div class="action-icon"><i class="bi bi-calendar-event"></i></div>
        <h6>Schedule Meeting</h6>
        <p>Book parent-teacher conference</p>
    </div>
</div>

<!-- DASHBOARD GRID -->
<div class="dashboard-grid">
    <!-- Left Column -->
    <div class="left-column">
        <!-- Courses Section -->
        <div class="section-card" style="animation-delay: 0.2s;">
            <h5 class="section-title"><i class="bi bi-journal-bookmark"></i> Current Courses</h5>
            <div class="courses-list">
                <?php foreach ($child_courses as $course): 
                    $progress = $course['total_lessons'] > 0 ? round(($course['completed_lessons'] / $course['total_lessons']) * 100) : 0;
                ?>
                <div class="course-item" onclick="viewCourseDetail(<?= $course['subject_id'] ?>)">
                    <div class="course-icon" style="background: <?= $course['color'] ?>20; color: <?= $course['color'] ?>;">
                        <i class="bi bi-<?= $course['icon'] ?? 'book' ?>"></i>
                    </div>
                    <div class="course-details">
                        <h6><?= htmlspecialchars($course['subject_name']) ?></h6>
                        <div class="course-meta">
                            <span><i class="bi bi-check-circle me-1"></i><?= $course['completed_lessons'] ?>/<?= $course['total_lessons'] ?> Lessons</span>
                            <span><i class="bi bi-star me-1"></i><?= $course['mastery_level'] ?>% Mastery</span>
                        </div>
                    </div>
                    <div class="course-progress">
                        <div class="progress-circle" style="--progress: <?= $progress ?>; --course-color: <?= $course['color'] ?>">
                            <span class="progress-text"><?= $progress ?>%</span>
                        </div>
                        <div class="last-activity"><?= $course['last_activity'] ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Performance Chart Section -->
        <div class="section-card performance-section" style="animation-delay: 0.3s; margin-top: 30px;">
            <h5 class="section-title"><i class="bi bi-graph-up"></i> 7-Day Performance Trend</h5>
            <div class="chart-container">
                <canvas id="performanceChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="right-column">
        <!-- Recent Activity -->
        <div class="section-card" style="animation-delay: 0.2s; margin-bottom: 30px;">
            <h5 class="section-title"><i class="bi bi-clock-history"></i> Recent Activity</h5>
            <div class="activity-feed">
                <?php foreach ($recent_activities as $activity): ?>
                <div class="activity-item" style="--activity-color: <?= $activity['color'] ?>">
                    <div class="activity-icon" style="color: <?= $activity['color'] ?>;">
                        <i class="bi <?= $activity['icon'] ?>"></i>
                    </div>
                    <div class="activity-content">
                        <h6><?= $activity['title'] ?></h6>
                        <p><?= $activity['subject'] ?></p>
                        <div class="activity-meta">
                            <span class="activity-time"><i class="bi bi-clock me-1"></i><?= $activity['time'] ?></span>
                            <?php if (isset($activity['score'])): ?>
                                <span class="activity-badge" style="background: <?= $activity['color'] ?>20; color: <?= $activity['color'] ?>;"><?= $activity['score'] ?></span>
                            <?php elseif (isset($activity['points'])): ?>
                                <span class="activity-badge" style="background: <?= $activity['color'] ?>20; color: <?= $activity['color'] ?>;"><?= $activity['points'] ?></span>
                            <?php elseif (isset($activity['duration'])): ?>
                                <span class="activity-badge" style="background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.7);"><?= $activity['duration'] ?></span>
                            <?php elseif (isset($activity['status'])): ?>
                                <span class="activity-badge" style="background: rgba(245, 158, 11, 0.2); color: #f59e0b;"><?= $activity['status'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button class="btn btn-outline-light w-100 mt-3 rounded-pill" onclick="viewAllActivities()">
                View All Activities <i class="bi bi-arrow-right ms-2"></i>
            </button>
        </div>

        <!-- Upcoming Deadlines -->
        <div class="section-card" style="animation-delay: 0.3s;">
            <h5 class="section-title"><i class="bi bi-alarm"></i> Upcoming Deadlines</h5>
            <div class="deadlines-list">
                <?php foreach ($upcoming_deadlines as $deadline): ?>
                <div class="deadline-item">
                    <div class="deadline-urgency <?= $deadline['urgency'] ?>"></div>
                    <div class="deadline-content">
                        <h6><?= $deadline['title'] ?></h6>
                        <span><?= $deadline['subject'] ?></span>
                    </div>
                    <div class="deadline-date">
                        <span class="date"><?= $deadline['due_date'] ?></span>
                        <span class="label">Due</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

</div><!-- End Main Content -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Create floating particles
function createParticles() {
    const container = document.getElementById('particles');
    for (let i = 0; i < 20; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        particle.style.left = Math.random() * 100 + '%';
        particle.style.animationDelay = Math.random() * 25 + 's';
        particle.style.animationDuration = (20 + Math.random() * 10) + 's';
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

// Notifications Toggle
function toggleNotifications() {
    const widget = document.getElementById('notificationsWidget');
    if (widget.style.display === 'block') {
        widget.style.display = 'none';
    } else {
        widget.style.display = 'block';
    }
}

// Select Child
function selectChild(childId) {
    window.location.href = 'parent_dashboard.php?child_id=' + childId;
}

// Add Child
function addChild() {
    alert('Link a new child account feature coming soon!');
}

// View Course Detail
function viewCourseDetail(courseId) {
    window.location.href = 'course_detail.php?id=' + courseId + '&parent_view=1';
}

// View All Activities
function viewAllActivities() {
    window.location.href = 'children_activities.php';
}

// Quick Actions
function viewDetailedReport() {
    window.location.href = 'reports.php';
}

function contactTeacher() {
    window.location.href = 'messages.php';
}

function setGoals() {
    window.location.href = 'goals.php';
}

function scheduleMeeting() {
    window.location.href = 'schedule_meeting.php';
}

// Initialize Performance Chart
const ctx = document.getElementById('performanceChart').getContext('2d');
const performanceChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [{
            label: 'Daily Score',
            data: <?= json_encode($performance_data) ?>,
            borderColor: '#f093fb',
            backgroundColor: 'rgba(240, 147, 251, 0.1)',
            borderWidth: 3,
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#f5576c',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 6,
            pointHoverRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: 'rgba(15, 23, 42, 0.9)',
                titleColor: '#fff',
                bodyColor: '#fff',
                borderColor: 'rgba(255,255,255,0.1)',
                borderWidth: 1,
                cornerRadius: 12,
                displayColors: false,
                callbacks: {
                    label: function(context) {
                        return 'Score: ' + context.parsed.y + '%';
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                max: 100,
                grid: {
                    color: 'rgba(255,255,255,0.05)',
                    drawBorder: false
                },
                ticks: {
                    color: 'rgba(255,255,255,0.5)',
                    callback: function(value) {
                        return value + '%';
                    }
                }
            },
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    color: 'rgba(255,255,255,0.5)'
                }
            }
        },
        interaction: {
            intersect: false,
            mode: 'index'
        }
    }
});

// Card hover effects
document.querySelectorAll('.stat-card').forEach(card => {
    card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        card.style.transform = `perspective(1000px) rotateX(${(y - centerY)/20}deg) rotateY(${(centerX - x)/20}deg) translateY(-10px) scale(1.02)`;
    });
    
    card.addEventListener('mouseleave', () => { 
        card.style.transform = ''; 
    });
});

// Close notifications when clicking outside
document.addEventListener('click', function(event) {
    const widget = document.getElementById('notificationsWidget');
    const bell = event.target.closest('.btn-outline-light');
    if (!widget.contains(event.target) && !bell && widget.style.display === 'block') {
        widget.style.display = 'none';
    }
});
</script>

</body>
</html>'''

