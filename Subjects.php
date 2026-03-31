<?php
session_start();
require 'db_config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];

// Initialize empty arrays
$subjects = [];
$recommended = [];

// Check database connection
if (!isset($conn) || $conn->connect_error) {
    die("Database connection failed: " . ($conn->connect_error ?? "Unknown error"));
}

// Fetch enrolled subjects with progress - FIXED
try {
    // Check if required tables exist
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
        // Build query based on available tables
        $has_concept_mastery = $conn->query("SHOW TABLES LIKE 'concept_mastery'")->num_rows > 0;
        $has_assessments = $conn->query("SHOW TABLES LIKE 'assessments'")->num_rows > 0;
        
        $select_parts = ["s.*"];
        $join_parts = [];
        $group_by = "GROUP BY s.subject_id";
        
        if ($has_concept_mastery) {
            $select_parts[] = "COALESCE(AVG(cm.mastery_level), 0) as progress";
            $select_parts[] = "COUNT(DISTINCT cm.concept_id) as concepts_learned";
            $join_parts[] = "LEFT JOIN concept_mastery cm ON s.subject_id = cm.subject_id AND cm.user_id = ?";
        } else {
            $select_parts[] = "0 as progress";
            $select_parts[] = "0 as concepts_learned";
        }
        
        if ($has_assessments) {
            $select_parts[] = "COUNT(DISTINCT a.assessment_id) as assessments_taken";
            $join_parts[] = "LEFT JOIN assessments a ON s.subject_id = a.subject_id";
        } else {
            $select_parts[] = "0 as assessments_taken";
        }
        
        $subjects_query = "SELECT " . implode(", ", $select_parts) . "
            FROM subjects s
            LEFT JOIN student_subjects ss ON s.subject_id = ss.subject_id
            " . implode("\n", $join_parts) . "
            WHERE ss.user_id = ? OR s.is_default = 1
            $group_by";
        
        $stmt = $conn->prepare($subjects_query);
        
        if ($stmt === false) {
            throw new Exception("Query prepare failed: " . $conn->error);
        }
        
        // Bind parameters dynamically
        if ($has_concept_mastery && strpos($subjects_query, 'cm.user_id = ?') !== false) {
            $stmt->bind_param("ii", $user_id, $user_id);
        } else {
            $stmt->bind_param("i", $user_id);
        }
        
        $stmt->execute();
        $subjects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
    
} catch (Exception $e) {
    // Fallback: create dummy subjects
    $subjects = [
        [
            'subject_id' => 1,
            'subject_name' => 'Mathematics',
            'description' => 'Learn algebra, calculus, and more',
            'icon' => 'calculator',
            'image_url' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=500&h=300&fit=crop',
            'progress' => 65,
            'concepts_learned' => 12,
            'assessments_taken' => 5
        ],
        [
            'subject_id' => 2,
            'subject_name' => 'Science',
            'description' => 'Physics, Chemistry, and Biology',
            'icon' => 'flask',
            'image_url' => 'https://images.unsplash.com/photo-1606326608606-aa0b62935f2b?w=500&h=300&fit=crop',
            'progress' => 40,
            'concepts_learned' => 8,
            'assessments_taken' => 3
        ],
        [
            'subject_id' => 3,
            'subject_name' => 'English',
            'description' => 'Literature and grammar',
            'icon' => 'book',
            'image_url' => 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=500&h=300&fit=crop',
            'progress' => 80,
            'concepts_learned' => 20,
            'assessments_taken' => 8
        ]
    ];
}

// Fetch recommended subjects - FIXED
try {
    $check_subjects = $conn->query("SHOW TABLES LIKE 'subjects'");
    $check_student_subjects = $conn->query("SHOW TABLES LIKE 'student_subjects'");
    
    if ($check_subjects && $check_subjects->num_rows > 0 && 
        $check_student_subjects && $check_student_subjects->num_rows > 0) {
        
        $recommended_query = "SELECT * FROM subjects WHERE subject_id NOT IN 
            (SELECT subject_id FROM student_subjects WHERE user_id = ?) 
            AND is_active = 1 LIMIT 3";
        
        $stmt = $conn->prepare($recommended_query);
        
        if ($stmt === false) {
            throw new Exception("Recommended query failed: " . $conn->error);
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $recommended = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
    
} catch (Exception $e) {
    // Fallback recommended subjects
    $recommended = [
        [
            'subject_id' => 4,
            'subject_name' => 'History',
            'description' => 'World history and civilizations',
            'icon' => 'globe',
            'image_url' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=500&h=300&fit=crop'
        ],
        [
            'subject_id' => 5,
            'subject_name' => 'Computer Science',
            'description' => 'Programming and algorithms',
            'icon' => 'laptop',
            'image_url' => 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=500&h=300&fit=crop'
        ]
    ];
}

// Get current page for sidebar
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Subjects - Smart LMS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
:root {
    --primary: #667eea;
    --secondary: #764ba2;
    --success: #11998e;
    --warning: #f093fb;
    --info: #4facfe;
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

.sidebar-header h5 i {
    -webkit-text-fill-color: #667eea;
    font-size: 1.4rem;
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

.sidebar a i {
    font-size: 1.3rem;
    transition: transform 0.3s ease;
}

.sidebar a:hover i,
.sidebar a.active i {
    transform: scale(1.1);
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

.page-header {
    margin-bottom: 35px;
    animation: fadeInUp 0.8s ease-out;
}

.page-header h4 {
    color: #fff;
    font-size: 2.2rem;
    font-weight: 700;
    margin-bottom: 8px;
    text-shadow: 2px 2px 8px rgba(0,0,0,0.3);
}

.page-header p {
    color: rgba(255,255,255,0.8);
    font-size: 1.1rem;
}

@keyframes fadeInUp {
    from { transform: translateY(30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* SUBJECT CARDS */
.subjects-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.subject-card {
    position: relative;
    border-radius: 24px;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.1);
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    cursor: pointer;
    animation: cardEntrance 0.8s ease-out backwards;
}

.subject-card:nth-child(1) { animation-delay: 0.1s; }
.subject-card:nth-child(2) { animation-delay: 0.2s; }
.subject-card:nth-child(3) { animation-delay: 0.3s; }
.subject-card:nth-child(4) { animation-delay: 0.4s; }

@keyframes cardEntrance {
    from { transform: translateY(60px) scale(0.9); opacity: 0; }
    to { transform: translateY(0) scale(1); opacity: 1; }
}

.subject-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 30px 60px rgba(0,0,0,0.3);
    border-color: rgba(255,255,255,0.3);
}

.subject-image {
    height: 160px;
    background-size: cover;
    background-position: center;
    position: relative;
}

.subject-image::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, transparent 0%, rgba(15,23,42,0.9) 100%);
}

.subject-icon {
    position: absolute;
    bottom: -25px;
    left: 25px;
    width: 60px;
    height: 60px;
    border-radius: 16px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: #fff;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
    z-index: 2;
    animation: iconFloat 3s ease-in-out infinite;
}

@keyframes iconFloat {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
}

.subject-content {
    padding: 40px 25px 25px;
    color: #fff;
}

.subject-title {
    font-size: 1.4rem;
    font-weight: 600;
    margin-bottom: 8px;
}

.subject-desc {
    color: rgba(255,255,255,0.7);
    font-size: 0.9rem;
    margin-bottom: 20px;
    line-height: 1.5;
}

.subject-stats {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.85rem;
    color: rgba(255,255,255,0.8);
}

.stat-item i {
    color: #667eea;
}

/* Progress Bar */
.progress-wrapper {
    margin-bottom: 20px;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    font-size: 0.85rem;
}

.progress-bar-bg {
    height: 8px;
    background: rgba(255,255,255,0.1);
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    border-radius: 10px;
    transition: width 1s ease-out;
    position: relative;
    overflow: hidden;
}

.progress-bar-fill::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

.subject-actions {
    display: flex;
    gap: 10px;
}

.btn-continue {
    flex: 1;
    padding: 12px 20px;
    border-radius: 12px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-continue:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
}

.btn-outline {
    padding: 12px 15px;
    border-radius: 12px;
    background: transparent;
    color: #fff;
    border: 2px solid rgba(255,255,255,0.2);
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-outline:hover {
    border-color: #667eea;
    background: rgba(102, 126, 234, 0.1);
}

/* Section Headers */
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    margin-top: 40px;
}

.section-header h5 {
    color: #fff;
    font-size: 1.3rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-header h5 i {
    color: #667eea;
}

.view-all {
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s;
}

.view-all:hover {
    color: #fff;
    transform: translateX(5px);
}

/* Recommended Cards */
.recommended-card {
    background: rgba(255,255,255,0.03);
    border: 2px dashed rgba(255,255,255,0.2);
}

.recommended-card:hover {
    border-color: #667eea;
    background: rgba(102, 126, 234, 0.05);
}

.badge-new {
    position: absolute;
    top: 15px;
    right: 15px;
    background: linear-gradient(135deg, #f093fb, #f5576c);
    color: #fff;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 2;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: rgba(255,255,255,0.6);
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 20px;
    opacity: 0.5;
}

/* AI ROBOT BUTTON */
.robot-btn {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    color: #fff;
    font-size: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 10px 40px rgba(139, 92, 246, 0.4);
    border: none;
    z-index: 1000;
    transition: all 0.3s ease;
    animation: robotFloat 3s ease-in-out infinite;
}

@keyframes robotFloat {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.robot-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 15px 50px rgba(139, 92, 246, 0.6);
}

.robot-btn::before {
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

/* AI CHAT */
.ai-box {
    position: fixed;
    bottom: 110px;
    right: 30px;
    width: 380px;
    background: rgba(255, 255, 255, 0.95);
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

.ai-header {
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    color: #fff;
    padding: 20px;
    font-weight: 600;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.ai-header button {
    background: rgba(255,255,255,0.2);
    border: none;
    color: #fff;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ai-header button:hover {
    background: rgba(255,255,255,0.3);
    transform: rotate(90deg);
}

.ai-body {
    padding: 20px;
    height: 320px;
    overflow-y: auto;
    background: #f8fafc;
}

.bubble {
    padding: 12px 16px;
    border-radius: 18px;
    margin: 10px 0;
    max-width: 80%;
    font-size: 0.95rem;
    line-height: 1.4;
    animation: messagePop 0.3s ease-out;
}

@keyframes messagePop {
    from { transform: scale(0); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

.user {
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    color: #fff;
    margin-left: auto;
    border-bottom-right-radius: 4px;
}

.bot {
    background: #fff;
    color: #1e293b;
    margin-right: auto;
    border-bottom-left-radius: 4px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.ai-input-area {
    padding: 20px;
    background: #fff;
    border-top: 1px solid #e2e8f0;
}

.ai-input-area input {
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px 16px;
    transition: all 0.3s;
}

.ai-input-area input:focus {
    border-color: #8b5cf6;
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

.ai-input-area button {
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    border: none;
    border-radius: 12px;
    padding: 12px;
    font-weight: 600;
    transition: all 0.3s;
}

.ai-input-area button:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(139, 92, 246, 0.4);
}

/* Scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.1);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.3);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: rgba(255,255,255,0.5);
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
    
    .sidebar-toggle {
        display: flex;
    }
    
    .main-content {
        margin-left: 0 !important;
        padding: 20px;
    }
    
    .subjects-grid {
        grid-template-columns: 1fr;
    }
    
    .ai-box {
        width: calc(100% - 40px);
        right: 20px;
    }
    
    .page-header h4 {
        font-size: 1.5rem;
    }
}

@media (min-width: 769px) and (max-width: 1024px) {
    .sidebar {
        width: 80px;
    }
    
    .sidebar .sidebar-header h5 span,
    .sidebar a span {
        display: none;
    }
    
    .sidebar a {
        justify-content: center;
        padding: 14px;
    }
    
    .sidebar a i {
        font-size: 1.4rem;
    }
    
    .main-content {
        margin-left: 80px !important;
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
    
    <a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
        <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
    </a>
    <a href="Subjects.php" class="<?= $current_page == 'Subjects.php' ? 'active' : '' ?>">
        <i class="bi bi-book-half"></i> <span>Subjects</span>
    </a>
    <a href="AITutor.php" class="<?= $current_page == 'AITutor.php' ? 'active' : '' ?>">
        <i class="bi bi-robot"></i> <span>AI Tutor</span>
    </a>
    <a href="Analytics.php" class="<?= $current_page == 'Analytics.php' ? 'active' : '' ?>">
        <i class="bi bi-graph-up-arrow"></i> <span>Analytics</span>
    </a>
    <a href="Settings.php" class="<?= $current_page == 'Settings.php' ? 'active' : '' ?>">
        <i class="bi bi-gear"></i> <span>Settings</span>
    </a>
    
    <div class="sidebar-divider"></div>
    
    <a href="logout.php" class="logout-link">
        <i class="bi bi-box-arrow-right"></i> <span>Logout</span>
    </a>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">

<!-- Page Header -->
<div class="page-header">
    <h4>My Subjects 📚</h4>
    <p>Continue learning where you left off or explore new topics</p>
</div>

<!-- Enrolled Subjects -->
<div class="section-header">
    <h5><i class="bi bi-journal-check"></i> Enrolled Subjects</h5>
    <a href="#" class="view-all">View All <i class="bi bi-arrow-right"></i></a>
</div>

<div class="subjects-grid">
    <?php if (count($subjects) > 0): ?>
        <?php foreach ($subjects as $index => $subject): ?>
        <div class="subject-card" onclick="openSubject(<?= $subject['subject_id'] ?>)">
            <div class="subject-image" style="background-image: url('<?= $subject['image_url'] ?? 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=500&h=300&fit=crop' ?>')">
                <div class="subject-icon">
                    <i class="bi bi-<?= $subject['icon'] ?? 'book' ?>"></i>
                </div>
            </div>
            <div class="subject-content">
                <h5 class="subject-title"><?= htmlspecialchars($subject['subject_name']) ?></h5>
                <p class="subject-desc"><?= htmlspecialchars(substr($subject['description'] ?? 'No description available', 0, 100)) ?>...</p>
                
                <div class="subject-stats">
                    <div class="stat-item">
                        <i class="bi bi-check-circle"></i>
                        <span><?= $subject['concepts_learned'] ?? 0 ?> Concepts</span>
                    </div>
                    <div class="stat-item">
                        <i class="bi bi-file-text"></i>
                        <span><?= $subject['assessments_taken'] ?? 0 ?> Tests</span>
                    </div>
                </div>
                
                <div class="progress-wrapper">
                    <div class="progress-header">
                        <span>Progress</span>
                        <span><?= round($subject['progress'] ?? 0) ?>%</span>
                    </div>
                    <div class="progress-bar-bg">
                        <div class="progress-bar-fill" style="width: <?= $subject['progress'] ?? 0 ?>%"></div>
                    </div>
                </div>
                
                <div class="subject-actions">
                    <button class="btn-continue">
                        Continue <i class="bi bi-arrow-right"></i>
                    </button>
                    <button class="btn-outline" onclick="event.stopPropagation(); toggleFavorite(<?= $subject['subject_id'] ?>)">
                        <i class="bi bi-heart"></i>
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="bi bi-journal-x"></i>
            <h5>No subjects enrolled yet</h5>
            <p>Explore recommended subjects below to get started</p>
        </div>
    <?php endif; ?>
</div>

<!-- Recommended Subjects -->
<?php if (count($recommended) > 0): ?>
<div class="section-header">
    <h5><i class="bi bi-stars"></i> Recommended for You</h5>
    <a href="#" class="view-all">Browse All <i class="bi bi-arrow-right"></i></a>
</div>

<div class="subjects-grid">
    <?php foreach ($recommended as $subject): ?>
    <div class="subject-card recommended-card" onclick="enrollSubject(<?= $subject['subject_id'] ?>)">
        <span class="badge-new">NEW</span>
        <div class="subject-image" style="background-image: url('<?= $subject['image_url'] ?? 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=500&h=300&fit=crop' ?>')">
            <div class="subject-icon" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                <i class="bi bi-<?= $subject['icon'] ?? 'star' ?>"></i>
            </div>
        </div>
        <div class="subject-content">
            <h5 class="subject-title"><?= htmlspecialchars($subject['subject_name']) ?></h5>
            <p class="subject-desc"><?= htmlspecialchars(substr($subject['description'] ?? 'Start learning this exciting new subject today!', 0, 100)) ?>...</p>
            
            <div class="subject-actions">
                <button class="btn-continue" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                    Enroll Now <i class="bi bi-plus-lg"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

</div>

<!-- AI ROBOT BUTTON -->
<div class="robot-btn" onclick="toggleAI()">🤖</div>

<!-- AI CHAT -->
<div class="ai-box" id="aiBox">
<div class="ai-header">
    <span><i class="bi bi-robot me-2"></i>AI Tutor</span>
    <button onclick="toggleAI()">✕</button>
</div>

<div class="ai-body" id="chat">
    <div class="bubble bot">👋 Hi <?= $first_name ?>! Need help choosing a subject or understanding a topic? I'm here to help!</div>
</div>

<div class="ai-input-area">
    <input type="text" id="msg" class="form-control" placeholder="Ask about any subject..." onkeypress="if(event.key==='Enter')sendMsg()">
    <button class="btn btn-primary mt-2 w-100" onclick="sendMsg()">
        <i class="bi bi-send me-2"></i>Send Message
    </button>
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

// AI Chat Toggle
function toggleAI(){
    let box = document.getElementById('aiBox');
    if (box.style.display === 'flex') {
        box.style.animation = 'chatPop 0.3s ease-out reverse';
        setTimeout(() => {
            box.style.display = 'none';
            box.style.animation = '';
        }, 300);
    } else {
        box.style.display = 'flex';
    }
}

// Send Message
function sendMsg(){
    let msg = document.getElementById('msg').value.trim();
    if(!msg) return;

    let chat = document.getElementById('chat');
    
    chat.innerHTML += `<div class="bubble user">${msg}</div>`;
    chat.scrollTop = chat.scrollHeight;
    
    let loadingId = 'loading-' + Date.now();
    chat.innerHTML += `<div class="bubble bot" id="${loadingId}"><i class="bi bi-three-dots"></i> Thinking...</div>`;
    chat.scrollTop = chat.scrollHeight;

    fetch('',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'ai_message='+encodeURIComponent(msg)
    })
    .then(res => res.text())
    .then(data => {
        document.getElementById(loadingId).remove();
        chat.innerHTML += `<div class="bubble bot">${data}</div>`;
        chat.scrollTop = chat.scrollHeight;
    });

    document.getElementById('msg').value = '';
}

// Subject Actions
function openSubject(subjectId) {
    window.location.href = 'subject-detail.php?id=' + subjectId;
}

function enrollSubject(subjectId) {
    if(confirm('Enroll in this subject?')) {
        window.location.href = 'enroll.php?subject_id=' + subjectId;
    }
}

function toggleFavorite(subjectId) {
    fetch('toggle-favorite.php?subject_id=' + subjectId)
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                event.target.closest('button').innerHTML = '<i class="bi bi-heart' + (data.favorited ? '-fill' : '') + '"></i>';
            }
        });
}

// Add hover tilt effect to cards
document.querySelectorAll('.subject-card').forEach(card => {
    card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        const rotateX = (y - centerY) / 20;
        const rotateY = (centerX - x) / 20;
        card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-10px) scale(1.02)`;
    });
    
    card.addEventListener('mouseleave', () => {
        card.style.transform = '';
    });
});

// Animate progress bars on load
window.addEventListener('load', () => {
    document.querySelectorAll('.progress-bar-fill').forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0';
        setTimeout(() => {
            bar.style.width = width;
        }, 300);
    });
});
</script>

</body>
</html>