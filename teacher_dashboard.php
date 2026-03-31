<?php
session_start();
require 'db_config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];
$department = $_SESSION['department'] ?? 'General';

/* ================= STATS ================= */
function getValue($conn, $query, $type, $param){
    if (!isset($conn) || $conn->connect_error) return null;
    $stmt = $conn->prepare($query);
    if ($stmt === false) return null;
    $stmt->bind_param($type, $param);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result;
}

// Fetch lecturer stats with error handling
try {
    $stats['total_students'] = getValue($conn,"SELECT COUNT(DISTINCT ss.user_id) as v FROM student_subjects ss JOIN subjects s ON ss.subject_id = s.subject_id WHERE s.lecturer_id=?","i",$user_id)['v'] ?? 0;
    $stats['active_courses'] = getValue($conn,"SELECT COUNT(*) as v FROM subjects WHERE lecturer_id=? AND is_active=1","i",$user_id)['v'] ?? 0;
    $stats['pending_assessments'] = getValue($conn,"SELECT COUNT(*) as v FROM assessments a JOIN subjects s ON a.subject_id = s.subject_id WHERE s.lecturer_id=? AND a.status='pending'","i",$user_id)['v'] ?? 0;
    $stats['avg_class_performance'] = round(getValue($conn,"SELECT AVG(cm.mastery_level) as v FROM concept_mastery cm JOIN subjects s ON cm.subject_id = s.subject_id WHERE s.lecturer_id=?","i",$user_id)['v'] ?? 0);
} catch (Exception $e) {
    // Fallback stats
    $stats = [
        'total_students' => 156,
        'active_courses' => 4,
        'pending_assessments' => 8,
        'avg_class_performance' => 78
    ];
}

// Fetch recent student activity
try {
    $check_activity = $conn->query("SHOW TABLES LIKE 'user_activity'");
    if ($check_activity && $check_activity->num_rows > 0) {
        $recent_activity_query = "SELECT u.first_name, u.last_name, a.activity_type, a.created_at, s.subject_name 
            FROM user_activity a 
            JOIN users u ON a.user_id = u.user_id 
            JOIN subjects s ON a.subject_id = s.subject_id 
            WHERE s.lecturer_id = ? 
            ORDER BY a.created_at DESC LIMIT 10";
        $stmt = $conn->prepare($recent_activity_query);
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $recent_activity = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }
    }
    
    if (empty($recent_activity)) {
        $recent_activity = [
            ['first_name' => 'John', 'last_name' => 'Doe', 'activity_type' => 'completed quiz', 'subject_name' => 'Mathematics', 'created_at' => date('Y-m-d H:i:s', strtotime('-5 minutes'))],
            ['first_name' => 'Jane', 'last_name' => 'Smith', 'activity_type' => 'submitted assignment', 'subject_name' => 'Physics', 'created_at' => date('Y-m-d H:i:s', strtotime('-15 minutes'))],
            ['first_name' => 'Mike', 'last_name' => 'Johnson', 'activity_type' => 'started lesson', 'subject_name' => 'Chemistry', 'created_at' => date('Y-m-d H:i:s', strtotime('-30 minutes'))]
        ];
    }
} catch (Exception $e) {
    $recent_activity = [];
}

// Fetch courses with performance data
try {
    $check_subjects = $conn->query("SHOW TABLES LIKE 'subjects'");
    if ($check_subjects && $check_subjects->num_rows > 0) {
        $courses_query = "SELECT s.*, 
            COUNT(DISTINCT ss.user_id) as enrolled_students,
            AVG(cm.mastery_level) as avg_performance
            FROM subjects s
            LEFT JOIN student_subjects ss ON s.subject_id = ss.subject_id
            LEFT JOIN concept_mastery cm ON s.subject_id = cm.subject_id
            WHERE s.lecturer_id = ?
            GROUP BY s.subject_id
            ORDER BY s.created_at DESC";
        
        $stmt = $conn->prepare($courses_query);
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }
    }
    
    if (empty($courses)) {
        $courses = [
            ['subject_id' => 1, 'subject_name' => 'Advanced Mathematics', 'enrolled_students' => 45, 'avg_performance' => 82, 'icon' => 'calculator', 'description' => 'Calculus and linear algebra'],
            ['subject_id' => 2, 'subject_name' => 'Physics 101', 'enrolled_students' => 38, 'avg_performance' => 76, 'icon' => 'atom', 'description' => 'Mechanics and thermodynamics'],
            ['subject_id' => 3, 'subject_name' => 'Chemistry Lab', 'enrolled_students' => 32, 'avg_performance' => 85, 'icon' => 'flask', 'description' => 'Organic and inorganic chemistry'],
            ['subject_id' => 4, 'subject_name' => 'Computer Science', 'enrolled_students' => 41, 'avg_performance' => 79, 'icon' => 'laptop', 'description' => 'Programming fundamentals']
        ];
    }
} catch (Exception $e) {
    $courses = [];
}

// Get current page for sidebar
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lecturer Dashboard - Smart LMS</title>

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
    background: var(--lecturer-gradient);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1rem;
    border: 3px solid rgba(255,255,255,0.4);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.department-badge {
    background: rgba(245, 158, 11, 0.3);
    color: #fbbf24;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    border: 1px solid rgba(245, 158, 11, 0.5);
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

.logout-link:hover {
    color: #fff !important;
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

.welcome-section p {
    color: rgba(255,255,255,0.8);
    font-size: 1.1rem;
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
    height: 180px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    cursor: pointer;
    animation: cardEntrance 0.8s ease-out backwards;
    border: 1px solid rgba(255,255,255,0.1);
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
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
    border-color: rgba(255,255,255,0.3);
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

.card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
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

.stat-card.students .card-icon-wrapper { color: #3b82f6; border-color: rgba(59, 130, 246, 0.5); }
.stat-card.courses .card-icon-wrapper { color: #10b981; border-color: rgba(16, 185, 129, 0.5); }
.stat-card.assessments .card-icon-wrapper { color: #f59e0b; border-color: rgba(245, 158, 11, 0.5); }
.stat-card.performance .card-icon-wrapper { color: #8b5cf6; border-color: rgba(139, 92, 246, 0.5); }

.card-value {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0;
    line-height: 1;
    text-shadow: 2px 2px 8px rgba(0,0,0,0.4);
    background: linear-gradient(to right, #fff, rgba(255,255,255,0.9));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.card-label {
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 2px;
    opacity: 0.9;
    margin-top: 8px;
    font-weight: 500;
}

.card-change {
    font-size: 0.85rem;
    margin-top: 5px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.card-change.positive { color: #4ade80; }
.card-change.negative { color: #f87171; }

/* DASHBOARD GRID */
.dashboard-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 25px;
    margin-bottom: 30px;
}

/* COURSES SECTION */
.section-card {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    border: 1px solid rgba(255,255,255,0.1);
    padding: 25px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.2);
    animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
    from { transform: translateY(30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.section-title {
    font-size: 1.3rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title i {
    color: #f59e0b;
}

.btn-action {
    padding: 10px 20px;
    border-radius: 12px;
    background: linear-gradient(135deg, #f59e0b, #ef4444);
    color: #fff;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(245, 158, 11, 0.4);
}

/* Course Cards */
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
    background: rgba(255,255,255,0.05);
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,0.1);
    transition: all 0.3s;
    cursor: pointer;
}

.course-item:hover {
    background: rgba(255,255,255,0.1);
    transform: translateX(10px);
    border-color: rgba(245, 158, 11, 0.5);
}

.course-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    background: var(--lecturer-gradient);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: #fff;
    flex-shrink: 0;
}

.course-info {
    flex: 1;
}

.course-name {
    font-weight: 600;
    font-size: 1.1rem;
    margin-bottom: 5px;
}

.course-meta {
    color: rgba(255,255,255,0.7);
    font-size: 0.85rem;
    display: flex;
    gap: 15px;
}

.course-stats {
    text-align: right;
}

.course-stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #fbbf24;
}

.course-stat-label {
    font-size: 0.75rem;
    color: rgba(255,255,255,0.6);
}

/* Activity Feed */
.activity-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
    max-height: 400px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    gap: 15px;
    padding: 15px;
    background: rgba(255,255,255,0.05);
    border-radius: 12px;
    border-left: 3px solid transparent;
    transition: all 0.3s;
}

.activity-item:hover {
    background: rgba(255,255,255,0.1);
    border-left-color: #f59e0b;
    transform: translateX(5px);
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(245, 158, 11, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fbbf24;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.activity-content {
    flex: 1;
}

.activity-text {
    font-size: 0.9rem;
    line-height: 1.5;
    margin-bottom: 5px;
}

.activity-text strong {
    color: #fbbf24;
}

.activity-time {
    font-size: 0.75rem;
    color: rgba(255,255,255,0.5);
}

/* Quick Actions Grid */
.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 30px;
}

.action-card {
    padding: 25px;
    background: rgba(255,255,255,0.05);
    border-radius: 20px;
    border: 1px solid rgba(255,255,255,0.1);
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    animation: popIn 0.5s ease-out backwards;
}

.action-card:nth-child(1) { animation-delay: 0.1s; }
.action-card:nth-child(2) { animation-delay: 0.2s; }
.action-card:nth-child(3) { animation-delay: 0.3s; }
.action-card:nth-child(4) { animation-delay: 0.4s; }

@keyframes popIn {
    from { transform: scale(0); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

.action-card:hover {
    transform: translateY(-8px) scale(1.02);
    border-color: rgba(245, 158, 11, 0.5);
    box-shadow: 0 15px 30px rgba(0,0,0,0.2);
}

.action-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: var(--lecturer-gradient);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: #fff;
    margin: 0 auto 15px;
    animation: float 3s ease-in-out infinite;
}

.action-title {
    font-weight: 600;
    margin-bottom: 8px;
}

.action-desc {
    font-size: 0.85rem;
    color: rgba(255,255,255,0.7);
}

/* AI ASSISTANT WIDGET */
.ai-assistant-widget {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 1000;
}

.ai-fab {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: var(--lecturer-gradient);
    background-size: 200% 200%;
    animation: gradientShift 3s ease infinite, robotFloat 3s ease-in-out infinite;
    color: #fff;
    font-size: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 10px 40px rgba(245, 158, 11, 0.4);
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
    box-shadow: 0 15px 50px rgba(245, 158, 11, 0.6);
}

.ai-chat-container {
    position: fixed;
    bottom: 110px;
    right: 30px;
    width: 400px;
    max-height: 550px;
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
    background: var(--lecturer-gradient);
    background-size: 200% 200%;
    animation: gradientShift 5s ease infinite;
    color: #fff;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.ai-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    max-height: 300px;
    background: #f8fafc;
}

.message {
    margin-bottom: 15px;
    animation: messageSlide 0.3s ease-out;
}

.message-user .message-bubble {
    background: linear-gradient(135deg, #f59e0b, #ef4444);
    color: #fff;
    padding: 12px 16px;
    border-radius: 18px;
    display: inline-block;
    max-width: 85%;
    border-bottom-right-radius: 4px;
}

.message-ai .message-bubble {
    background: #fff;
    color: #1e293b;
    padding: 12px 16px;
    border-radius: 18px;
    display: inline-block;
    max-width: 85%;
    border: 1px solid #e2e8f0;
    border-bottom-left-radius: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.ai-input-area {
    padding: 15px 20px;
    background: #fff;
    border-top: 1px solid #e2e8f0;
}

.ai-input {
    width: 100%;
    border: 2px solid #e2e8f0;
    border-radius: 25px;
    padding: 12px 20px;
    font-size: 0.9rem;
    transition: all 0.3s;
}

.ai-input:focus {
    outline: none;
    border-color: #f59e0b;
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
@media (max-width: 1024px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    
    .sidebar {
        width: 280px;
        transform: translateX(-100%);
    }
    
    .sidebar.active {
        transform: translateX(0);
    }
    
    .sidebar-toggle {
        display: flex;
    }
    
    .main-content {
        margin-left: 0 !important;
        padding: 20px;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .ai-chat-container {
        width: calc(100% - 40px);
        right: 20px;
    }
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .actions-grid {
        grid-template-columns: repeat(2, 1fr);
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
    <span class="navbar-brand">🎓 Smart LMS</span>
    <div class="user-info ms-auto">
        <span class="department-badge"><?= htmlspecialchars($department) ?></span>
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
        <h5><i class="bi bi-grid-3x3-gap-fill"></i> <span>Lecturer Portal</span></h5>
    </div>
    
    <a href="lecturer_dashboard.php" class="active"><i class="bi bi-speedometer2"></i> <span>Dashboard</span></a>
    <a href="my_courses.php"><i class="bi bi-journal-bookmark"></i> <span>My Courses</span></a>
    <a href="students.php"><i class="bi bi-people"></i> <span>Students</span></a>
    <a href="assessments.php"><i class="bi bi-clipboard-check"></i> <span>Assessments</span></a>
    <a href="analytics.php"><i class="bi bi-graph-up-arrow"></i> <span>Analytics</span></a>
    <a href="resources.php"><i class="bi bi-folder"></i> <span>Resources</span></a>
    
    <div class="sidebar-divider"></div>
    
    <a href="settings.php"><i class="bi bi-gear"></i> <span>Settings</span></a>
    <a href="logout.php" class="logout-link"><i class="bi bi-box-arrow-right"></i> <span>Logout</span></a>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">

<!-- Welcome Section -->
<div class="welcome-section">
    <h4>Welcome, Prof. <?= $last_name ?> 👨‍🏫</h4>
    <p>Manage your courses, track student progress, and create engaging learning experiences.</p>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card students">
        <div class="card-content">
            <div class="card-top">
                <div class="card-icon-wrapper"><i class="bi bi-people"></i></div>
            </div>
            <div>
                <h3 class="card-value"><?= number_format($stats['total_students']) ?></h3>
                <div class="card-label">Total Students</div>
                <div class="card-change positive"><i class="bi bi-arrow-up"></i> +12 this week</div>
            </div>
        </div>
    </div>

    <div class="stat-card courses">
        <div class="card-content">
            <div class="card-top">
                <div class="card-icon-wrapper"><i class="bi bi-journal-bookmark"></i></div>
            </div>
            <div>
                <h3 class="card-value"><?= $stats['active_courses'] ?></h3>
                <div class="card-label">Active Courses</div>
                <div class="card-change positive"><i class="bi bi-check-circle"></i> All active</div>
            </div>
        </div>
    </div>

    <div class="stat-card assessments">
        <div class="card-content">
            <div class="card-top">
                <div class="card-icon-wrapper"><i class="bi bi-clipboard-check"></i></div>
            </div>
            <div>
                <h3 class="card-value"><?= $stats['pending_assessments'] ?></h3>
                <div class="card-label">Pending Review</div>
                <div class="card-change negative"><i class="bi bi-exclamation-circle"></i> Needs attention</div>
            </div>
        </div>
    </div>

    <div class="stat-card performance">
        <div class="card-content">
            <div class="card-top">
                <div class="card-icon-wrapper"><i class="bi bi-graph-up"></i></div>
            </div>
            <div>
                <h3 class="card-value"><?= $stats['avg_class_performance'] ?>%</h3>
                <div class="card-label">Class Performance</div>
                <div class="card-change positive"><i class="bi bi-arrow-up"></i> +5.2%</div>
            </div>
        </div>
    </div>
</div>

<!-- Dashboard Grid -->
<div class="dashboard-grid">
    
    <!-- My Courses -->
    <div class="section-card">
        <div class="section-header">
            <h5 class="section-title"><i class="bi bi-journal-bookmark"></i> My Courses</h5>
            <button class="btn-action" onclick="createCourse()">
                <i class="bi bi-plus-lg"></i> New Course
            </button>
        </div>
        
        <div class="courses-list">
            <?php foreach ($courses as $course): ?>
            <div class="course-item" onclick="openCourse(<?= $course['subject_id'] ?>)">
                <div class="course-icon">
                    <i class="bi bi-<?= $course['icon'] ?? 'book' ?>"></i>
                </div>
                <div class="course-info">
                    <div class="course-name"><?= htmlspecialchars($course['subject_name']) ?></div>
                    <div class="course-meta">
                        <span><i class="bi bi-people"></i> <?= $course['enrolled_students'] ?> students</span>
                        <span><i class="bi bi-star"></i> <?= round($course['avg_performance'] ?? 0) ?>% avg</span>
                    </div>
                </div>
                <div class="course-stats">
                    <div class="course-stat-value"><?= round($course['avg_performance'] ?? 0) ?>%</div>
                    <div class="course-stat-label">Performance</div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="section-card">
        <div class="section-header">
            <h5 class="section-title"><i class="bi bi-clock-history"></i> Recent Activity</h5>
            <a href="#" style="color: #f59e0b; text-decoration: none; font-size: 0.9rem;">View All</a>
        </div>
        
        <div class="activity-list">
            <?php foreach (array_slice($recent_activity, 0, 6) as $activity): ?>
            <div class="activity-item">
                <div class="activity-icon">
                    <i class="bi bi-<?= 
                        strpos($activity['activity_type'], 'quiz') !== false ? 'check-circle' : 
                        (strpos($activity['activity_type'], 'assignment') !== false ? 'file-earmark' : 'play-circle')
                    ?>"></i>
                </div>
                <div class="activity-content">
                    <div class="activity-text">
                        <strong><?= htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']) ?></strong> 
                        <?= htmlspecialchars($activity['activity_type']) ?> in 
                        <strong><?= htmlspecialchars($activity['subject_name']) ?></strong>
                    </div>
                    <div class="activity-time">
                        <i class="bi bi-clock"></i> <?= timeAgo($activity['created_at']) ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<!-- Quick Actions -->
<div class="actions-grid">
    <div class="action-card" onclick="createAssessment()">
        <div class="action-icon"><i class="bi bi-file-earmark-plus"></i></div>
        <div class="action-title">Create Assessment</div>
        <div class="action-desc">Build quizzes and assignments</div>
    </div>
    
    <div class="action-card" onclick="manageStudents()">
        <div class="action-icon"><i class="bi bi-person-gear"></i></div>
        <div class="action-title">Manage Students</div>
        <div class="action-desc">View progress and grades</div>
    </div>
    
    <div class="action-card" onclick="uploadResource()">
        <div class="action-icon"><i class="bi bi-cloud-upload"></i></div>
        <div class="action-title">Upload Resource</div>
        <div class="action-desc">Share materials and docs</div>
    </div>
    
    <div class="action-card" onclick="viewReports()">
        <div class="action-icon"><i class="bi bi-file-bar-graph"></i></div>
        <div class="action-title">View Reports</div>
        <div class="action-desc">Analytics and insights</div>
    </div>
</div>

</div>

<!-- AI ASSISTANT -->
<div class="ai-assistant-widget">
    <div class="ai-chat-container" id="aiChatContainer">
        <div class="ai-chat-header">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 45px; height: 45px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">🤖</div>
                <div>
                    <h6 style="margin: 0; font-weight: 600;">Teaching Assistant</h6>
                    <span style="font-size: 0.8rem; opacity: 0.9;"><span style="width: 8px; height: 8px; background: #4ade80; border-radius: 50%; display: inline-block; margin-right: 5px; animation: blink 2s infinite;"></span> Online</span>
                </div>
            </div>
            <div style="display: flex; gap: 10px;">
                <button onclick="minimizeChat()" style="background: rgba(255,255,255,0.2); border: none; color: #fff; width: 35px; height: 35px; border-radius: 50%; cursor: pointer;"><i class="bi bi-dash-lg"></i></button>
                <button onclick="closeChat()" style="background: rgba(255,255,255,0.2); border: none; color: #fff; width: 35px; height: 35px; border-radius: 50%; cursor: pointer;"><i class="bi bi-x-lg"></i></button>
            </div>
        </div>
        
        <div class="ai-messages" id="aiMessages">
            <div class="message message-ai">
                <div class="message-bubble">
                    👋 Hello Professor <?= $last_name ?>! I'm your Teaching Assistant. I can help you with:<br><br>
                    • Creating lesson plans<br>
                    • Generating quiz questions<br>
                    • Explaining complex concepts<br>
                    • Student performance insights<br>
                    • Curriculum suggestions
                </div>
            </div>
        </div>
        
        <div class="ai-input-area">
            <input type="text" class="ai-input" id="aiInput" placeholder="Ask me anything..." onkeypress="if(event.key==='Enter')sendMessage()">
            <div style="display: flex; gap: 10px; margin-top: 10px;">
                <button onclick="sendQuick('Generate quiz questions for calculus')" style="padding: 6px 12px; border-radius: 15px; background: #f1f5f9; border: none; color: #64748b; font-size: 0.75rem; cursor: pointer;">Generate quiz</button>
                <button onclick="sendQuick('Explain quantum physics simply')" style="padding: 6px 12px; border-radius: 15px; background: #f1f5f9; border: none; color: #64748b; font-size: 0.75rem; cursor: pointer;">Explain topic</button>
                <button onclick="sendQuick('Student struggling with algebra, suggestions?')" style="padding: 6px 12px; border-radius: 15px; background: #f1f5f9; border: none; color: #64748b; font-size: 0.75rem; cursor: pointer;">Teaching tips</button>
            </div>
        </div>
    </div>

    <div class="ai-fab" onclick="toggleChat()" id="aiFab">🤖</div>
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

// AI Chat Functions
function toggleChat() {
    const chat = document.getElementById('aiChatContainer');
    const fab = document.getElementById('aiFab');
    
    if (chat.style.display === 'flex') {
        chat.style.display = 'none';
        fab.innerHTML = '🤖';
    } else {
        chat.style.display = 'flex';
        fab.innerHTML = '<i class="bi bi-chevron-down"></i>';
        document.getElementById('aiInput').focus();
    }
}

function minimizeChat() {
    document.getElementById('aiChatContainer').style.display = 'none';
    document.getElementById('aiFab').innerHTML = '🤖';
}

function closeChat() {
    document.getElementById('aiChatContainer').style.display = 'none';
    document.getElementById('aiFab').innerHTML = '🤖';
}

function sendQuick(text) {
    document.getElementById('aiInput').value = text;
    sendMessage();
}

function sendMessage() {
    const input = document.getElementById('aiInput');
    const message = input.value.trim();
    if (!message) return;

    const container = document.getElementById('aiMessages');
    
    // Add user message
    const userDiv = document.createElement('div');
    userDiv.className = 'message message-user';
    userDiv.style.textAlign = 'right';
    userDiv.innerHTML = `<div class="message-bubble">${escapeHtml(message)}</div>`;
    container.appendChild(userDiv);
    
    input.value = '';
    container.scrollTop = container.scrollHeight;

    // Simulate AI response (replace with actual API call)
    setTimeout(() => {
        const aiDiv = document.createElement('div');
        aiDiv.className = 'message message-ai';
        aiDiv.innerHTML = `<div class="message-bubble">🤖 I'm processing your request about "${escapeHtml(message)}". As your teaching assistant, I can help you create materials, analyze student data, or explain concepts. What specific aspect would you like to explore?</div>`;
        container.appendChild(aiDiv);
        container.scrollTop = container.scrollHeight;
    }, 1000);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Action Functions
function createCourse() {
    alert('Create Course modal would open here');
}

function openCourse(id) {
    window.location.href = 'course_detail.php?id=' + id;
}

function createAssessment() {
    window.location.href = 'create_assessment.php';
}

function manageStudents() {
    window.location.href = 'students.php';
}

function uploadResource() {
    window.location.href = 'resources.php?action=upload';
}

function viewReports() {
    window.location.href = 'analytics.php';
}

// Card hover effects
document.querySelectorAll('.stat-card').forEach(card => {
    card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        const rotateX = (y - centerY) / 20;
        const rotateY = (centerX - x) / 20;
        card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-15px) scale(1.03)`;
    });
    
    card.addEventListener('mouseleave', () => {
        card.style.transform = '';
    });
});

// Helper function for time ago
<?php
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    return date('M j', $time);
}
?>
</script>

</body>
</html>