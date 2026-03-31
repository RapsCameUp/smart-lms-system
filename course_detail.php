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

// Get course ID from URL
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 1;

// Initialize course data with defaults
$course = [
    'subject_id' => $course_id,
    'subject_name' => 'Advanced Mathematics',
    'description' => 'Master calculus, algebra, and mathematical reasoning with interactive 3D visualizations and adaptive learning paths.',
    'icon' => 'calculator',
    'color' => '#667eea',
    'total_lessons' => 24,
    'completed_lessons' => 12,
    'mastery_level' => 75,
    'instructor' => 'Prof. Sarah Johnson',
    'duration' => '48 hours',
    'students_enrolled' => 1250,
    'rating' => 4.8
];

$lessons = [];
$assessments = [];
$resources = [];

// Fetch from database if connection exists
if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
    try {
        // Get course details
        $check_table = $conn->query("SHOW TABLES LIKE 'subjects'");
        if ($check_table && $check_table->num_rows > 0) {
            $stmt = $conn->prepare("SELECT * FROM subjects WHERE subject_id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $course_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result && $row = $result->fetch_assoc()) {
                    $course = array_merge($course, $row);
                }
                $stmt->close();
            }
        }

        // Get lessons
        $check_lessons = $conn->query("SHOW TABLES LIKE 'lessons'");
        if ($check_lessons && $check_lessons->num_rows > 0) {
            $stmt = $conn->prepare("SELECT * FROM lessons WHERE subject_id = ? ORDER BY lesson_order ASC");
            if ($stmt) {
                $stmt->bind_param("i", $course_id);
                $stmt->execute();
                $lessons = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
            }
        }
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
    }
}

// Fallback lessons if none from DB
if (empty($lessons)) {
    $lessons = [
        [
            'lesson_id' => 1,
            'lesson_title' => 'Introduction to Calculus',
            'lesson_description' => 'Understanding limits and derivatives fundamentals',
            'duration' => '45 min',
            'is_completed' => true,
            'has_3d' => true,
            '3d_model' => 'derivative',
            'progress' => 100
        ],
        [
            'lesson_id' => 2,
            'lesson_title' => '3D Functions Visualization',
            'lesson_description' => 'Interactive exploration of multivariable functions',
            'duration' => '60 min',
            'is_completed' => true,
            'has_3d' => true,
            '3d_model' => 'surface',
            'progress' => 100
        ],
        [
            'lesson_id' => 3,
            'lesson_title' => 'Integration Techniques',
            'lesson_description' => 'Advanced methods for solving integrals',
            'duration' => '55 min',
            'is_completed' => false,
            'has_3d' => true,
            '3d_model' => 'volume',
            'progress' => 65
        ],
        [
            'lesson_id' => 4,
            'lesson_title' => 'Differential Equations',
            'lesson_description' => 'Modeling real-world phenomena with ODEs',
            'duration' => '70 min',
            'is_completed' => false,
            'has_3d' => true,
            '3d_model' => 'field',
            'progress' => 30
        ],
        [
            'lesson_id' => 5,
            'lesson_title' => 'Vector Calculus',
            'lesson_description' => 'Gradient, divergence, and curl in 3D space',
            'duration' => '65 min',
            'is_completed' => false,
            'has_3d' => true,
            '3d_model' => 'vector',
            'progress' => 0
        ],
        [
            'lesson_id' => 6,
            'lesson_title' => 'Final Project: 3D Modeling',
            'lesson_description' => 'Apply concepts to build interactive mathematical models',
            'duration' => '120 min',
            'is_completed' => false,
            'has_3d' => true,
            '3d_model' => 'project',
            'progress' => 0
        ]
    ];
}

// Assessments
$assessments = [
    [
        'assessment_id' => 1,
        'title' => 'Calculus Fundamentals Quiz',
        'type' => 'quiz',
        'questions' => 15,
        'duration' => '30 min',
        'is_completed' => true,
        'score' => 85,
        'max_score' => 100
    ],
    [
        'assessment_id' => 2,
        'title' => '3D Visualization Challenge',
        'type' => 'interactive',
        'questions' => 8,
        'duration' => '45 min',
        'is_completed' => true,
        'score' => 92,
        'max_score' => 100
    ],
    [
        'assessment_id' => 3,
        'title' => 'Integration Mastery Test',
        'type' => 'test',
        'questions' => 20,
        'duration' => '60 min',
        'is_completed' => false,
        'score' => null,
        'max_score' => 100
    ]
];

// Resources
$resources = [
    ['icon' => 'bi-book', 'title' => 'Course Textbook', 'type' => 'PDF', 'size' => '15 MB'],
    ['icon' => 'bi-file-earmark-text', 'title' => 'Formula Sheet', 'type' => 'PDF', 'size' => '2 MB'],
    ['icon' => 'bi-camera-video', 'title' => 'Video Lectures', 'type' => 'Playlist', 'size' => '24 videos'],
    ['icon' => 'bi-code-square', 'title' => '3D Models Source', 'type' => 'ZIP', 'size' => '156 MB']
];

$progress_percent = $course['total_lessons'] > 0 ? 
    round(($course['completed_lessons'] / $course['total_lessons']) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($course['subject_name']) ?> - Smart LMS 3D</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<!-- Three.js for 3D Visualizations -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<!-- GSAP for Animations -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<!-- OrbitControls -->
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>

<style>
:root {
    --primary: #667eea;
    --secondary: #764ba2;
    --course-color: <?= $course['color'] ?>;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
    font-family: 'Poppins', sans-serif;
    min-height: 100vh;
    color: #fff;
    overflow-x: hidden;
}

/* 3D Background Canvas */
#bg-canvas {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 0;
}

/* Glass Overlay */
.glass-overlay {
    position: relative;
    z-index: 1;
    min-height: 100vh;
    background: rgba(15, 23, 42, 0.3);
    backdrop-filter: blur(10px);
}

/* Navbar */
.navbar {
    background: rgba(15, 23, 42, 0.8);
    backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(255,255,255,0.1);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.navbar-brand {
    font-weight: 700;
    font-size: 1.6rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
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
    border: 3px solid rgba(255,255,255,0.3);
}

/* Course Hero Section */
.course-hero {
    padding: 60px 0;
    position: relative;
    overflow: hidden;
}

.hero-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 50px;
    align-items: center;
}

.hero-text h1 {
    font-size: 3.5rem;
    font-weight: 700;
    margin-bottom: 20px;
    line-height: 1.2;
}

.hero-text p {
    font-size: 1.2rem;
    color: rgba(255,255,255,0.7);
    margin-bottom: 30px;
    line-height: 1.6;
}

.hero-stats {
    display: flex;
    gap: 30px;
    margin-bottom: 30px;
}

.hero-stat {
    display: flex;
    align-items: center;
    gap: 10px;
}

.hero-stat i {
    font-size: 1.5rem;
    color: var(--course-color);
}

.hero-stat span {
    font-size: 0.9rem;
    color: rgba(255,255,255,0.8);
}

.hero-actions {
    display: flex;
    gap: 15px;
}

.btn-primary-3d {
    padding: 15px 40px;
    border-radius: 30px;
    background: linear-gradient(135deg, var(--course-color), <?= adjustBrightness($course['color'], 20) ?>);
    color: #fff;
    border: none;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.btn-primary-3d:hover {
    transform: translateY(-3px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.4);
}

.btn-secondary-3d {
    padding: 15px 40px;
    border-radius: 30px;
    background: rgba(255,255,255,0.1);
    color: #fff;
    border: 2px solid rgba(255,255,255,0.2);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 10px;
}

.btn-secondary-3d:hover {
    background: rgba(255,255,255,0.2);
    border-color: rgba(255,255,255,0.4);
}

/* 3D Visualization Container */
.hero-3d-container {
    width: 100%;
    height: 400px;
    position: relative;
    border-radius: 30px;
    overflow: hidden;
    background: radial-gradient(circle at center, rgba(102, 126, 234, 0.1) 0%, transparent 70%);
    border: 1px solid rgba(255,255,255,0.1);
}

#course3DCanvas {
    width: 100%;
    height: 100%;
}

/* Progress Section */
.progress-section {
    background: rgba(255,255,255,0.05);
    backdrop-filter: blur(20px);
    border-radius: 30px;
    padding: 30px;
    margin-bottom: 40px;
    border: 1px solid rgba(255,255,255,0.1);
}

.progress-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.progress-title {
    font-size: 1.3rem;
    font-weight: 600;
}

.progress-percent {
    font-size: 2rem;
    font-weight: 700;
    color: var(--course-color);
}

.progress-bar-container {
    height: 12px;
    background: rgba(255,255,255,0.1);
    border-radius: 10px;
    overflow: hidden;
    position: relative;
}

.progress-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--course-color), <?= adjustBrightness($course['color'], 30) ?>);
    border-radius: 10px;
    transition: width 1s ease;
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
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

.progress-stats {
    display: flex;
    justify-content: space-between;
    margin-top: 15px;
    font-size: 0.9rem;
    color: rgba(255,255,255,0.6);
}

/* Content Tabs */
.content-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
    overflow-x: auto;
    padding-bottom: 10px;
}

.tab-btn {
    padding: 12px 25px;
    border-radius: 25px;
    background: rgba(255,255,255,0.05);
    color: rgba(255,255,255,0.7);
    border: 1px solid rgba(255,255,255,0.1);
    cursor: pointer;
    transition: all 0.3s;
    font-weight: 500;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 8px;
}

.tab-btn:hover {
    background: rgba(255,255,255,0.1);
    color: #fff;
}

.tab-btn.active {
    background: linear-gradient(135deg, var(--course-color), <?= adjustBrightness($course['color'], 20) ?>);
    color: #fff;
    border-color: transparent;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

/* Lessons Grid */
.lessons-grid {
    display: grid;
    gap: 20px;
}

.lesson-card {
    background: rgba(255,255,255,0.05);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    border: 1px solid rgba(255,255,255,0.1);
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
    display: grid;
    grid-template-columns: 120px 1fr auto;
    align-items: center;
    cursor: pointer;
}

.lesson-card:hover {
    transform: translateY(-5px) translateZ(20px);
    box-shadow: 0 25px 50px rgba(0,0,0,0.3);
    border-color: rgba(255,255,255,0.2);
}

.lesson-thumbnail {
    width: 120px;
    height: 120px;
    position: relative;
    background: radial-gradient(circle at center, rgba(102, 126, 234, 0.2) 0%, transparent 70%);
    display: flex;
    align-items: center;
    justify-content: center;
}

.lesson-3d-preview {
    width: 80px;
    height: 80px;
}

.lesson-status {
    position: absolute;
    top: 10px;
    left: 10px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
}

.lesson-status.completed {
    background: #10b981;
    color: #fff;
}

.lesson-status.pending {
    background: rgba(255,255,255,0.2);
    border: 2px solid rgba(255,255,255,0.3);
}

.lesson-info {
    padding: 25px;
}

.lesson-title {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 8px;
}

.lesson-desc {
    font-size: 0.9rem;
    color: rgba(255,255,255,0.6);
    margin-bottom: 15px;
}

.lesson-meta {
    display: flex;
    gap: 20px;
    font-size: 0.85rem;
    color: rgba(255,255,255,0.5);
}

.lesson-meta span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.lesson-progress {
    padding: 25px;
    text-align: center;
}

.progress-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: conic-gradient(var(--course-color) <?= $progress_percent * 3.6 ?>deg, rgba(255,255,255,0.1) 0deg);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    margin: 0 auto 10px;
}

.progress-circle::before {
    content: '';
    position: absolute;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: rgba(15, 23, 42, 0.9);
}

.progress-circle span {
    position: relative;
    z-index: 1;
    font-size: 0.8rem;
    font-weight: 600;
}

.btn-start {
    padding: 10px 25px;
    border-radius: 20px;
    background: rgba(255,255,255,0.1);
    color: #fff;
    border: 1px solid rgba(255,255,255,0.2);
    cursor: pointer;
    transition: all 0.3s;
    font-size: 0.9rem;
}

.btn-start:hover {
    background: var(--course-color);
    border-color: var(--course-color);
}

/* Assessments Section */
.assessments-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
}

.assessment-card {
    background: rgba(255,255,255,0.05);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    padding: 30px;
    border: 1px solid rgba(255,255,255,0.1);
    transition: all 0.4s;
    position: relative;
    overflow: hidden;
}

.assessment-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 5px;
    height: 100%;
    background: var(--course-color);
    transform: scaleY(0);
    transition: transform 0.3s;
}

.assessment-card:hover::before {
    transform: scaleY(1);
}

.assessment-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
}

.assessment-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
}

.assessment-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    background: rgba(102, 126, 234, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: var(--course-color);
}

.assessment-status {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.assessment-status.completed {
    background: rgba(16, 185, 129, 0.2);
    color: #34d399;
}

.assessment-status.pending {
    background: rgba(245, 158, 11, 0.2);
    color: #fbbf24;
}

.assessment-title {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 10px;
}

.assessment-meta {
    display: flex;
    gap: 20px;
    font-size: 0.85rem;
    color: rgba(255,255,255,0.6);
    margin-bottom: 20px;
}

.assessment-score {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 20px;
    border-top: 1px solid rgba(255,255,255,0.1);
}

.score-display {
    display: flex;
    align-items: baseline;
    gap: 5px;
}

.score-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--course-color);
}

.score-total {
    font-size: 1rem;
    color: rgba(255,255,255,0.5);
}

/* Resources Section */
.resources-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.resource-card {
    background: rgba(255,255,255,0.05);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 25px;
    border: 1px solid rgba(255,255,255,0.1);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: all 0.3s;
    cursor: pointer;
}

.resource-card:hover {
    transform: translateY(-5px);
    background: rgba(255,255,255,0.08);
    border-color: rgba(255,255,255,0.2);
}

.resource-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    background: rgba(102, 126, 234, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: var(--course-color);
}

.resource-info h5 {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 5px;
}

.resource-info p {
    font-size: 0.85rem;
    color: rgba(255,255,255,0.5);
}

/* 3D Lesson Modal */
.modal-3d {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.9);
    z-index: 2000;
    backdrop-filter: blur(20px);
}

.modal-3d.active {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content-3d {
    width: 90%;
    max-width: 1200px;
    height: 85%;
    background: rgba(15, 23, 42, 0.95);
    border-radius: 30px;
    overflow: hidden;
    display: grid;
    grid-template-rows: auto 1fr auto;
    border: 1px solid rgba(255,255,255,0.1);
}

.modal-header-3d {
    padding: 25px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.modal-title-3d {
    font-size: 1.5rem;
    font-weight: 600;
}

.modal-close-3d {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: rgba(255,255,255,0.1);
    border: none;
    color: #fff;
    font-size: 1.5rem;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-close-3d:hover {
    background: rgba(239, 68, 68, 0.3);
    transform: rotate(90deg);
}

.modal-body-3d {
    display: grid;
    grid-template-columns: 2fr 1fr;
    overflow: hidden;
}

.viewer-3d {
    position: relative;
    background: #000;
}

#lesson3DCanvas {
    width: 100%;
    height: 100%;
}

.viewer-controls {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 15px;
    background: rgba(0,0,0,0.7);
    padding: 15px 25px;
    border-radius: 50px;
    backdrop-filter: blur(10px);
}

.control-btn {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    color: #fff;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.control-btn:hover {
    background: var(--course-color);
    border-color: var(--course-color);
}

.lesson-sidebar {
    padding: 30px;
    overflow-y: auto;
    background: rgba(255,255,255,0.02);
}

.lesson-steps {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.step-item {
    padding: 20px;
    background: rgba(255,255,255,0.05);
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,0.1);
    cursor: pointer;
    transition: all 0.3s;
}

.step-item:hover, .step-item.active {
    background: rgba(102, 126, 234, 0.1);
    border-color: var(--course-color);
}

.step-number {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: var(--course-color);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 10px;
}

.step-title {
    font-size: 0.95rem;
    font-weight: 500;
    margin-bottom: 5px;
}

.step-desc {
    font-size: 0.8rem;
    color: rgba(255,255,255,0.5);
}

.modal-footer-3d {
    padding: 20px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid rgba(255,255,255,0.1);
}

/* AI Tutor Floating Button */
.ai-tutor-float {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    cursor: pointer;
    box-shadow: 0 15px 40px rgba(139, 92, 246, 0.4);
    z-index: 999;
    animation: tutorFloat 3s ease-in-out infinite;
    border: none;
    color: #fff;
}

@keyframes tutorFloat {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

/* Responsive */
@media (max-width: 1024px) {
    .hero-content {
        grid-template-columns: 1fr;
    }
    
    .hero-3d-container {
        height: 300px;
    }
    
    .modal-body-3d {
        grid-template-columns: 1fr;
    }
    
    .lesson-card {
        grid-template-columns: 100px 1fr;
    }
    
    .lesson-progress {
        display: none;
    }
}

@media (max-width: 768px) {
    .hero-text h1 {
        font-size: 2rem;
    }
    
    .hero-stats {
        flex-wrap: wrap;
    }
    
    .content-tabs {
        overflow-x: scroll;
    }
    
    .assessments-grid {
        grid-template-columns: 1fr;
    }
}
</style>
</head>
<body>

<!-- 3D Background -->
<canvas id="bg-canvas"></canvas>

<div class="glass-overlay">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg">
<div class="container-fluid">
    <a class="navbar-brand" href="student_dashboard.php">🎓 Smart LMS</a>
    <div class="user-info ms-auto d-flex align-items-center gap-3">
        <a href="student_dashboard.php" class="text-white text-decoration-none">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
        <div class="user-avatar"><?= substr($first_name, 0, 1) ?></div>
    </div>
</div>
</nav>

<!-- Course Hero -->
<section class="course-hero">
<div class="container">
    <div class="hero-content">
        <div class="hero-text">
            <div class="d-flex align-items-center gap-3 mb-3">
                <span class="badge rounded-pill" style="background: <?= $course['color'] ?>20; color: <?= $course['color'] ?>; border: 1px solid <?= $course['color'] ?>40;">
                    <i class="bi bi-<?= $course['icon'] ?> me-2"></i><?= htmlspecialchars($course['subject_name']) ?>
                </span>
                <span class="text-white-50"><i class="bi bi-star-fill text-warning me-1"></i><?= $course['rating'] ?></span>
            </div>
            <h1><?= htmlspecialchars($course['subject_name']) ?></h1>
            <p><?= htmlspecialchars($course['description']) ?></p>
            
            <div class="hero-stats">
                <div class="hero-stat">
                    <i class="bi bi-person-video3"></i>
                    <span><?= $course['instructor'] ?></span>
                </div>
                <div class="hero-stat">
                    <i class="bi bi-clock"></i>
                    <span><?= $course['duration'] ?></span>
                </div>
                <div class="hero-stat">
                    <i class="bi bi-people"></i>
                    <span><?= number_format($course['students_enrolled']) ?> students</span>
                </div>
            </div>
            
            <div class="hero-actions">
                <button class="btn-primary-3d" onclick="continueLearning()">
                    <i class="bi bi-play-fill"></i>
                    <?= $progress_percent > 0 ? 'Continue Learning' : 'Start Course' ?>
                </button>
                <button class="btn-secondary-3d" onclick="downloadOffline()">
                    <i class="bi bi-download"></i>
                    Download Offline
                </button>
            </div>
        </div>
        
        <div class="hero-3d-container">
            <div id="course3DCanvas"></div>
        </div>
    </div>
</div>
</section>

<!-- Progress Section -->
<div class="container">
    <div class="progress-section">
        <div class="progress-header">
            <span class="progress-title">Your Progress</span>
            <span class="progress-percent"><?= $progress_percent ?>%</span>
        </div>
        <div class="progress-bar-container">
            <div class="progress-bar-fill" style="width: <?= $progress_percent ?>%"></div>
        </div>
        <div class="progress-stats">
            <span><?= $course['completed_lessons'] ?> of <?= $course['total_lessons'] ?> lessons completed</span>
            <span><?= $course['total_lessons'] - $course['completed_lessons'] ?> remaining</span>
        </div>
    </div>
</div>

<!-- Course Content -->
<div class="container pb-5">
    
    <!-- Tabs -->
    <div class="content-tabs">
        <button class="tab-btn active" onclick="switchTab('lessons')">
            <i class="bi bi-collection-play"></i> Lessons
        </button>
        <button class="tab-btn" onclick="switchTab('assessments')">
            <i class="bi bi-clipboard-check"></i> Assessments
        </button>
        <button class="tab-btn" onclick="switchTab('resources')">
            <i class="bi bi-folder"></i> Resources
        </button>
        <button class="tab-btn" onclick="switchTab('discussions')">
            <i class="bi bi-chat-dots"></i> Discussions
        </button>
    </div>
    
    <!-- Lessons Tab -->
    <div id="tab-lessons" class="tab-content">
        <div class="lessons-grid">
            <?php foreach ($lessons as $index => $lesson): ?>
            <div class="lesson-card" onclick="openLesson3D(<?= $lesson['lesson_id'] ?>, '<?= $lesson['3d_model'] ?>')">
                <div class="lesson-thumbnail">
                    <div class="lesson-3d-preview" id="preview-<?= $lesson['lesson_id'] ?>"></div>
                    <div class="lesson-status <?= $lesson['is_completed'] ? 'completed' : 'pending' ?>">
                        <i class="bi bi-<?= $lesson['is_completed'] ? 'check-lg' : 'lock' ?>"></i>
                    </div>
                </div>
                <div class="lesson-info">
                    <div class="lesson-title"><?= htmlspecialchars($lesson['lesson_title']) ?></div>
                    <div class="lesson-desc"><?= htmlspecialchars($lesson['lesson_description']) ?></div>
                    <div class="lesson-meta">
                        <span><i class="bi bi-clock"></i> <?= $lesson['duration'] ?></span>
                        <?php if ($lesson['has_3d']): ?>
                        <span><i class="bi bi-box"></i> 3D Interactive</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="lesson-progress">
                    <div class="progress-circle">
                        <span><?= $lesson['progress'] ?>%</span>
                    </div>
                    <button class="btn-start">
                        <?= $lesson['is_completed'] ? 'Review' : ($lesson['progress'] > 0 ? 'Continue' : 'Start') ?>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Assessments Tab -->
    <div id="tab-assessments" class="tab-content" style="display: none;">
        <div class="assessments-grid">
            <?php foreach ($assessments as $assessment): ?>
            <div class="assessment-card">
                <div class="assessment-header">
                    <div class="assessment-icon">
                        <i class="bi bi-<?= $assessment['type'] === 'quiz' ? 'question-circle' : ($assessment['type'] === 'interactive' ? 'controller' : 'file-earmark-text') ?>"></i>
                    </div>
                    <span class="assessment-status <?= $assessment['is_completed'] ? 'completed' : 'pending' ?>">
                        <?= $assessment['is_completed'] ? 'Completed' : 'Pending' ?>
                    </span>
                </div>
                <h4 class="assessment-title"><?= htmlspecialchars($assessment['title']) ?></h4>
                <div class="assessment-meta">
                    <span><i class="bi bi-question-circle"></i> <?= $assessment['questions'] ?> questions</span>
                    <span><i class="bi bi-clock"></i> <?= $assessment['duration'] ?></span>
                </div>
                <div class="assessment-score">
                    <div class="score-display">
                        <?php if ($assessment['is_completed']): ?>
                        <span class="score-value"><?= $assessment['score'] ?></span>
                        <span class="score-total">/<?= $assessment['max_score'] ?></span>
                        <?php else: ?>
                        <span style="color: rgba(255,255,255,0.5); font-size: 0.9rem;">Not started</span>
                        <?php endif; ?>
                    </div>
                    <button class="btn-start" onclick="startAssessment(<?= $assessment['assessment_id'] ?>)">
                        <?= $assessment['is_completed'] ? 'Retake' : 'Start' ?>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Resources Tab -->
    <div id="tab-resources" class="tab-content" style="display: none;">
        <div class="resources-grid">
            <?php foreach ($resources as $resource): ?>
            <div class="resource-card" onclick="downloadResource('<?= $resource['title'] ?>')">
                <div class="resource-icon">
                    <i class="bi <?= $resource['icon'] ?>"></i>
                </div>
                <div class="resource-info">
                    <h5><?= htmlspecialchars($resource['title']) ?></h5>
                    <p><?= $resource['type'] ?> • <?= $resource['size'] ?></p>
                </div>
                <i class="bi bi-download text-white-50"></i>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Discussions Tab -->
    <div id="tab-discussions" class="tab-content" style="display: none;">
        <div class="text-center py-5">
            <i class="bi bi-chat-square-text display-1 text-white-25 mb-3"></i>
            <h4 class="text-white-50">Join the Discussion</h4>
            <p class="text-white-50">Connect with <?= number_format($course['students_enrolled']) ?> students learning this course</p>
            <button class="btn-primary-3d mt-3">
                <i class="bi bi-plus-lg"></i> New Discussion
            </button>
        </div>
    </div>
    
</div>

</div><!-- End Glass Overlay -->

<!-- 3D Lesson Modal -->
<div class="modal-3d" id="lessonModal">
    <div class="modal-content-3d">
        <div class="modal-header-3d">
            <div>
                <h3 class="modal-title-3d" id="modalLessonTitle">3D Interactive Lesson</h3>
                <p class="text-white-50 mb-0">Use mouse to rotate, scroll to zoom</p>
            </div>
            <button class="modal-close-3d" onclick="closeLessonModal()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="modal-body-3d">
            <div class="viewer-3d">
                <canvas id="lesson3DCanvas"></canvas>
                <div class="viewer-controls">
                    <button class="control-btn" onclick="resetCamera()" title="Reset view">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                    <button class="control-btn" onclick="toggleAutoRotate()" title="Auto rotate">
                        <i class="bi bi-arrow-repeat"></i>
                    </button>
                    <button class="control-btn" onclick="toggleWireframe()" title="Wireframe">
                        <i class="bi bi-grid-3x3"></i>
                    </button>
                    <button class="control-btn" onclick="takeScreenshot()" title="Screenshot">
                        <i class="bi bi-camera"></i>
                    </button>
                </div>
            </div>
            <div class="lesson-sidebar">
                <div class="lesson-steps">
                    <div class="step-item active">
                        <div class="step-number">1</div>
                        <div class="step-title">Explore the Model</div>
                        <div class="step-desc">Rotate and examine from all angles</div>
                    </div>
                    <div class="step-item">
                        <div class="step-number">2</div>
                        <div class="step-title">Interactive Elements</div>
                        <div class="step-desc">Click on parts to see details</div>
                    </div>
                    <div class="step-item">
                        <div class="step-number">3</div>
                        <div class="step-title">Practice Exercise</div>
                        <div class="step-desc">Apply what you've learned</div>
                    </div>
                    <div class="step-item">
                        <div class="step-number">4</div>
                        <div class="step-title">Quiz Check</div>
                        <div class="step-desc">Test your understanding</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer-3d">
            <button class="btn-secondary-3d" onclick="prevStep()">
                <i class="bi bi-arrow-left"></i> Previous
            </button>
            <div class="d-flex gap-2">
                <button class="btn-secondary-3d" onclick="markComplete()">
                    <i class="bi bi-check-lg"></i> Mark Complete
                </button>
                <button class="btn-primary-3d" onclick="nextStep()">
                    Next Step <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- AI Tutor Button -->
<button class="ai-tutor-float" onclick="openAITutor()" title="Ask AI Tutor">
    🤖
</button>

<script>
// ==========================================
// THREE.JS BACKGROUND ANIMATION
// ==========================================
let bgScene, bgCamera, bgRenderer, bgParticles;

function initBackground() {
    const canvas = document.getElementById('bg-canvas');
    
    bgScene = new THREE.Scene();
    bgScene.fog = new THREE.FogExp2(0x0f0c29, 0.001);
    
    bgCamera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
    bgCamera.position.z = 50;
    
    bgRenderer = new THREE.WebGLRenderer({ canvas: canvas, alpha: true, antialias: true });
    bgRenderer.setSize(window.innerWidth, window.innerHeight);
    bgRenderer.setPixelRatio(window.devicePixelRatio);
    
    // Create floating geometric shapes
    const geometries = [
        new THREE.IcosahedronGeometry(1, 0),
        new THREE.OctahedronGeometry(1, 0),
        new THREE.TetrahedronGeometry(1, 0)
    ];
    
    const material = new THREE.MeshPhongMaterial({
        color: 0x667eea,
        wireframe: true,
        transparent: true,
        opacity: 0.15
    });
    
    for (let i = 0; i < 30; i++) {
        const mesh = new THREE.Mesh(
            geometries[Math.floor(Math.random() * geometries.length)],
            material
        );
        mesh.position.set(
            (Math.random() - 0.5) * 100,
            (Math.random() - 0.5) * 100,
            (Math.random() - 0.5) * 50
        );
        mesh.rotation.set(Math.random() * Math.PI, Math.random() * Math.PI, 0);
        mesh.userData = {
            rotationSpeed: {
                x: (Math.random() - 0.5) * 0.01,
                y: (Math.random() - 0.5) * 0.01
            },
            floatSpeed: Math.random() * 0.002 + 0.001,
            floatOffset: Math.random() * Math.PI * 2
        };
        bgScene.add(mesh);
    }
    
    // Lights
    const light = new THREE.DirectionalLight(0xffffff, 0.5);
    light.position.set(10, 10, 10);
    bgScene.add(light);
    bgScene.add(new THREE.AmbientLight(0x404040, 0.5));
    
    // Particles
    const particlesGeometry = new THREE.BufferGeometry();
    const particlesCount = 500;
    const posArray = new Float32Array(particlesCount * 3);
    
    for (let i = 0; i < particlesCount * 3; i++) {
        posArray[i] = (Math.random() - 0.5) * 200;
    }
    
    particlesGeometry.setAttribute('position', new THREE.BufferAttribute(posArray, 3));
    const particlesMaterial = new THREE.PointsMaterial({
        size: 0.5,
        color: 0x667eea,
        transparent: true,
        opacity: 0.6
    });
    
    bgParticles = new THREE.Points(particlesGeometry, particlesMaterial);
    bgScene.add(bgParticles);
    
    animateBackground();
}

function animateBackground() {
    requestAnimationFrame(animateBackground);
    
    bgScene.children.forEach(child => {
        if (child.userData.rotationSpeed) {
            child.rotation.x += child.userData.rotationSpeed.x;
            child.rotation.y += child.userData.rotationSpeed.y;
            child.position.y += Math.sin(Date.now() * child.userData.floatSpeed + child.userData.floatOffset) * 0.02;
        }
    });
    
    bgParticles.rotation.y += 0.0005;
    bgParticles.rotation.x += 0.0002;
    
    bgRenderer.render(bgScene, bgCamera);
}

// ==========================================
// COURSE 3D HERO VISUALIZATION
// ==========================================
let heroScene, heroCamera, heroRenderer, heroMesh;

function initHero3D() {
    const container = document.getElementById('course3DCanvas');
    
    heroScene = new THREE.Scene();
    
    heroCamera = new THREE.PerspectiveCamera(50, container.clientWidth / container.clientHeight, 0.1, 100);
    heroCamera.position.z = 5;
    
    heroRenderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
    heroRenderer.setSize(container.clientWidth, container.clientHeight);
    container.appendChild(heroRenderer.domElement);
    
    // Create course-specific 3D object
    const geometry = new THREE.IcosahedronGeometry(1.5, 1);
    const material = new THREE.MeshPhongMaterial({
        color: 0x667eea,
        emissive: 0x4c1d95,
        emissiveIntensity: 0.3,
        shininess: 100,
        wireframe: false
    });
    
    heroMesh = new THREE.Mesh(geometry, material);
    heroScene.add(heroMesh);
    
    // Add wireframe overlay
    const wireframe = new THREE.LineSegments(
        new THREE.WireframeGeometry(geometry),
        new THREE.LineBasicMaterial({ color: 0xffffff, transparent: true, opacity: 0.3 })
    );
    heroMesh.add(wireframe);
    
    // Floating particles around
    const particlesGeo = new THREE.BufferGeometry();
    const particlesCount = 100;
    const posArray = new Float32Array(particlesCount * 3);
    
    for (let i = 0; i < particlesCount * 3; i++) {
        posArray[i] = (Math.random() - 0.5) * 6;
    }
    
    particlesGeo.setAttribute('position', new THREE.BufferAttribute(posArray, 3));
    const particlesMat = new THREE.PointsMaterial({
        size: 0.05,
        color: 0xffffff,
        transparent: true,
        opacity: 0.8
    });
    const particles = new THREE.Points(particlesGeo, particlesMat);
    heroMesh.add(particles);
    
    // Lights
    const light = new THREE.PointLight(0xffffff, 1, 100);
    light.position.set(5, 5, 5);
    heroScene.add(light);
    heroScene.add(new THREE.AmbientLight(0x404040));
    
    // Mouse interaction
    let mouseX = 0, mouseY = 0;
    container.addEventListener('mousemove', (e) => {
        const rect = container.getBoundingClientRect();
        mouseX = ((e.clientX - rect.left) / rect.width) * 2 - 1;
        mouseY = -((e.clientY - rect.top) / rect.height) * 2 + 1;
    });
    
    function animate() {
        requestAnimationFrame(animate);
        
        heroMesh.rotation.y += 0.005;
        heroMesh.rotation.x += 0.002;
        
        // Mouse follow
        heroMesh.rotation.y += mouseX * 0.01;
        heroMesh.rotation.x += mouseY * 0.01;
        
        heroRenderer.render(heroScene, heroCamera);
    }
    animate();
}

// ==========================================
// LESSON 3D MODAL
// ==========================================
let lessonScene, lessonCamera, lessonRenderer, lessonControls, currentModel;
let autoRotate = false;

function openLesson3D(lessonId, modelType) {
    const modal = document.getElementById('lessonModal');
    modal.classList.add('active');
    
    // Initialize 3D viewer
    setTimeout(() => initLesson3D(modelType), 100);
}

function initLesson3D(modelType) {
    const canvas = document.getElementById('lesson3DCanvas');
    
    lessonScene = new THREE.Scene();
    lessonScene.background = new THREE.Color(0x000000);
    
    lessonCamera = new THREE.PerspectiveCamera(75, canvas.clientWidth / canvas.clientHeight, 0.1, 1000);
    lessonCamera.position.z = 5;
    
    lessonRenderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true });
    lessonRenderer.setSize(canvas.clientWidth, canvas.clientHeight);
    lessonRenderer.setPixelRatio(window.devicePixelRatio);
    
    // Create model based on type
    currentModel = createModelByType(modelType);
    lessonScene.add(currentModel);
    
    // Controls
    lessonControls = new THREE.OrbitControls(lessonCamera, lessonRenderer.domElement);
    lessonControls.enableDamping = true;
    lessonControls.dampingFactor = 0.05;
    
    // Lights
    const ambientLight = new THREE.AmbientLight(0x404040, 0.6);
    lessonScene.add(ambientLight);
    
    const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
    directionalLight.position.set(5, 5, 5);
    lessonScene.add(directionalLight);
    
    animateLesson();
}

function createModelByType(type) {
    const group = new THREE.Group();
    
    switch(type) {
        case 'derivative':
            // Function curve with tangent line
            const curve = new THREE.Group();
            for (let i = -5; i <= 5; i += 0.1) {
                const y = Math.sin(i);
                const point = new THREE.Mesh(
                    new THREE.SphereGeometry(0.05),
                    new THREE.MeshPhongMaterial({ color: 0x667eea })
                );
                point.position.set(i, y, 0);
                curve.add(point);
            }
            group.add(curve);
            
            // Tangent line
            const lineGeometry = new THREE.BufferGeometry().setFromPoints([
                new THREE.Vector3(-2, -0.9, 0),
                new THREE.Vector3(2, 0.9, 0)
            ]);
            const line = new THREE.Line(lineGeometry, new THREE.LineBasicMaterial({ color: 0xf59e0b, linewidth: 3 }));
            group.add(line);
            break;
            
        case 'surface':
            // 3D Surface plot
            const surfaceGeo = new THREE.PlaneGeometry(4, 4, 32, 32);
            const posAttribute = surfaceGeo.attributes.position;
            for (let i = 0; i < posAttribute.count; i++) {
                const x = posAttribute.getX(i);
                const y = posAttribute.getY(i);
                const z = Math.sin(Math.sqrt(x*x + y*y)) * 0.5;
                posAttribute.setZ(i, z);
            }
            surfaceGeo.computeVertexNormals();
            
            const surfaceMat = new THREE.MeshPhongMaterial({
                color: 0x667eea,
                wireframe: false,
                side: THREE.DoubleSide,
                shininess: 100
            });
            const surface = new THREE.Mesh(surfaceGeo, surfaceMat);
            surface.rotation.x = -Math.PI / 3;
            group.add(surface);
            
            // Wireframe overlay
            const wireframe = new THREE.LineSegments(
                new THREE.WireframeGeometry(surfaceGeo),
                new THREE.LineBasicMaterial({ color: 0xffffff, transparent: true, opacity: 0.3 })
            );
            wireframe.rotation.x = -Math.PI / 3;
            group.add(wireframe);
            break;
            
        case 'volume':
            // Volume of revolution
            const cylinder = new THREE.Mesh(
                new THREE.CylinderGeometry(1, 1, 2, 32, 1, true),
                new THREE.MeshPhongMaterial({
                    color: 0x667eea,
                    transparent: true,
                    opacity: 0.7,
                    side: THREE.DoubleSide
                })
            );
            group.add(cylinder);
            
            // Axis
            const axisGeometry = new THREE.BufferGeometry().setFromPoints([
                new THREE.Vector3(0, -2, 0),
                new THREE.Vector3(0, 2, 0)
            ]);
            const axis = new THREE.Line(axisGeometry, new THREE.LineBasicMaterial({ color: 0xf59e0b, linewidth: 2 }));
            group.add(axis);
            break;
            
        case 'field':
            // Vector field
            for (let x = -2; x <= 2; x += 1) {
                for (let y = -2; y <= 2; y += 1) {
                    const arrow = new THREE.ArrowHelper(
                        new THREE.Vector3(y, -x, 0).normalize(),
                        new THREE.Vector3(x, y, 0),
                        0.5,
                        0x667eea
                    );
                    group.add(arrow);
                }
            }
            break;
            
        case 'vector':
            // 3D Vectors
            const axes = new THREE.Group();
            const colors = [0xff0000, 0x00ff00, 0x0000ff];
            const directions = [
                new THREE.Vector3(1, 0, 0),
                new THREE.Vector3(0, 1, 0),
                new THREE.Vector3(0, 0, 1)
            ];
            
            directions.forEach((dir, i) => {
                const arrow = new THREE.ArrowHelper(dir, new THREE.Vector3(0, 0, 0), 2, colors[i], 0.3, 0.2);
                axes.add(arrow);
            });
            group.add(axes);
            
            // Curl visualization
            const curlCurve = new THREE.Group();
            for (let t = 0; t < Math.PI * 4; t += 0.1) {
                const point = new THREE.Mesh(
                    new THREE.SphereGeometry(0.05),
                    new THREE.MeshPhongMaterial({ color: 0xf59e0b })
                );
                point.position.set(Math.cos(t), Math.sin(t), t * 0.2);
                curlCurve.add(point);
            }
            group.add(curlCurve);
            break;
            
        default:
            // Default geometric shape
            const defaultGeo = new THREE.IcosahedronGeometry(1.5, 2);
            const defaultMat = new THREE.MeshPhongMaterial({
                color: 0x667eea,
                wireframe: false
            });
            const defaultMesh = new THREE.Mesh(defaultGeo, defaultMat);
            group.add(defaultMesh);
    }
    
    return group;
}

function animateLesson() {
    requestAnimationFrame(animateLesson);
    
    if (autoRotate && currentModel) {
        currentModel.rotation.y += 0.005;
    }
    
    lessonControls.update();
    lessonRenderer.render(lessonScene, lessonCamera);
}

function closeLessonModal() {
    document.getElementById('lessonModal').classList.remove('active');
    if (lessonRenderer) {
        lessonRenderer.dispose();
    }
}

function resetCamera() {
    if (lessonCamera && lessonControls) {
        lessonCamera.position.set(0, 0, 5);
        lessonControls.reset();
    }
}

function toggleAutoRotate() {
    autoRotate = !autoRotate;
}

function toggleWireframe() {
    if (currentModel) {
        currentModel.traverse((child) => {
            if (child.isMesh && child.material) {
                child.material.wireframe = !child.material.wireframe;
            }
        });
    }
}

function takeScreenshot() {
    if (lessonRenderer) {
        const dataURL = lessonRenderer.domElement.toDataURL('image/png');
        const link = document.createElement('a');
        link.download = '3d-lesson-screenshot.png';
        link.href = dataURL;
        link.click();
    }
}

function nextStep() {
    const steps = document.querySelectorAll('.step-item');
    const active = document.querySelector('.step-item.active');
    const currentIndex = Array.from(steps).indexOf(active);
    
    if (currentIndex < steps.length - 1) {
        active.classList.remove('active');
        steps[currentIndex + 1].classList.add('active');
    }
}

function prevStep() {
    const steps = document.querySelectorAll('.step-item');
    const active = document.querySelector('.step-item.active');
    const currentIndex = Array.from(steps).indexOf(active);
    
    if (currentIndex > 0) {
        active.classList.remove('active');
        steps[currentIndex - 1].classList.add('active');
    }
}

function markComplete() {
    alert('Lesson marked as complete! +50 XP earned');
    closeLessonModal();
}

// ==========================================
// UI INTERACTIONS
// ==========================================
function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.style.display = 'none';
    });
    
    // Show selected tab
    document.getElementById('tab-' + tabName).style.display = 'block';
    
    // Update button states
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.closest('.tab-btn').classList.add('active');
}

function continueLearning() {
    // Scroll to lessons
    document.getElementById('tab-lessons').scrollIntoView({ behavior: 'smooth' });
    // Open first incomplete lesson
    const firstIncomplete = document.querySelector('.lesson-status.pending');
    if (firstIncomplete) {
        firstIncomplete.closest('.lesson-card').click();
    }
}

function downloadOffline() {
    alert('Course content is being prepared for offline download...');
}

function startAssessment(id) {
    alert('Starting assessment ' + id + '...');
}

function downloadResource(title) {
    alert('Downloading ' + title + '...');
}

function openAITutor() {
    // Open AI tutor in new window or redirect
    window.location.href = 'AITutor.php';
}

// ==========================================
// INITIALIZATION
// ==========================================
window.addEventListener('load', () => {
    initBackground();
    initHero3D();
    
    // Animate progress bar
    setTimeout(() => {
        document.querySelector('.progress-bar-fill').style.width = '<?= $progress_percent ?>%';
    }, 500);
});

window.addEventListener('resize', () => {
    if (bgCamera && bgRenderer) {
        bgCamera.aspect = window.innerWidth / window.innerHeight;
        bgCamera.updateProjectionMatrix();
        bgRenderer.setSize(window.innerWidth, window.innerHeight);
    }
    
    if (heroCamera && heroRenderer) {
        const container = document.getElementById('course3DCanvas');
        heroCamera.aspect = container.clientWidth / container.clientHeight;
        heroCamera.updateProjectionMatrix();
        heroRenderer.setSize(container.clientWidth, container.clientHeight);
    }
});
</script>

</body>
</html>

<?php
// Helper function to adjust color brightness
function adjustBrightness($hex, $steps) {
    $hex = ltrim($hex, '#');
    $r = max(0, min(255, hexdec(substr($hex, 0, 2)) + $steps));
    $g = max(0, min(255, hexdec(substr($hex, 2, 2)) + $steps));
    $b = max(0, min(255, hexdec(substr($hex, 4, 2)) + $steps));
    return '#' . dechex($r) . dechex($g) . dechex($b);
}
?>