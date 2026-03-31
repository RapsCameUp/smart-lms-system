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
$preferred_language = $_SESSION['preferred_language'] ?? 'en';

// Initialize empty arrays
$chat_history = [];
$suggested_topics = [];

// Check if database connection exists
if (!isset($conn) || $conn->connect_error) {
    die("Database connection failed: " . ($conn->connect_error ?? "Unknown error"));
}

// Fetch chat history - FIXED QUERY
try {
    $history_query = "SELECT * FROM ai_tutor_interactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 50";
    $stmt = $conn->prepare($history_query);
    
    if ($stmt === false) {
        throw new Exception("Failed to prepare chat history query: " . $conn->error);
    }
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $chat_history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    // Silently handle error - table might not exist yet
    $chat_history = [];
}

// Fetch suggested topics - FIXED QUERY (simplified)
try {
    // Check if tables exist first
    $check_table = $conn->query("SHOW TABLES LIKE 'concept_mastery'");
    
    if ($check_table && $check_table->num_rows > 0) {
        $weak_areas_query = "SELECT c.concept_name, s.subject_name, cm.mastery_level 
            FROM concept_mastery cm
            JOIN concepts c ON cm.concept_id = c.concept_id
            JOIN subjects s ON c.subject_id = s.subject_id
            WHERE cm.user_id = ? AND cm.mastery_level < 60
            ORDER BY cm.mastery_level ASC LIMIT 5";
        
        $stmt = $conn->prepare($weak_areas_query);
        
        if ($stmt === false) {
            // Try alternative query without joins if tables have different structure
            $weak_areas_query = "SELECT * FROM concept_mastery WHERE user_id = ? AND mastery_level < 60 LIMIT 5";
            $stmt = $conn->prepare($weak_areas_query);
        }
        
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $suggested_topics = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }
    }
} catch (Exception $e) {
    $suggested_topics = [];
}

// Get current page for sidebar
$current_page = basename($_SERVER['PHP_SELF']);

/* ================= AI CHAT API ================= */
if (isset($_POST['ai_message'])) {
    $msg = $_POST['ai_message'];
    
    // Enhanced AI response logic
    $response = generateAIResponse($msg, $first_name);
    
    // Save to database - only if table exists
    try {
        $check_table = $conn->query("SHOW TABLES LIKE 'ai_tutor_interactions'");
        if ($check_table && $check_table->num_rows > 0) {
            $stmt = $conn->prepare("INSERT INTO ai_tutor_interactions(user_id, query_text, ai_response) VALUES(?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("iss", $user_id, $msg, $response);
                $stmt->execute();
                $stmt->close();
            }
        }
    } catch (Exception $e) {
        // Silently fail if table doesn't exist
    }
    
    echo $response;
    exit;
}

function generateAIResponse($msg, $name) {
    $msg_lower = strtolower($msg);
    
    if (strpos($msg_lower, 'hello') !== false || strpos($msg_lower, 'hi') !== false) {
        return "👋 Hello $name! I'm your AI tutor. What would you like to learn today?";
    }
    elseif (strpos($msg_lower, 'help') !== false) {
        return "🤖 I'm here to help! You can ask me about:\n• Specific concepts you're struggling with\n• Practice questions\n• Study tips and techniques\n• Explanations of difficult topics\n\nWhat do you need help with?";
    }
    elseif (strpos($msg_lower, 'math') !== false) {
        return "📐 Math is all about practice! Try breaking problems into smaller steps. Would you like me to walk through a specific problem or concept?";
    }
    elseif (strpos($msg_lower, 'science') !== false) {
        return "🔬 Science is fascinating! Focus on understanding the 'why' behind concepts. What specific topic are you studying?";
    }
    elseif (strpos($msg_lower, 'exam') !== false || strpos($msg_lower, 'test') !== false) {
        return "📝 For exams, I recommend:\n1. Review your weak areas (check your Analytics)\n2. Practice with past questions\n3. Take regular breaks while studying\n\nNeed help with a specific subject?";
    }
    else {
        return "🤖 Smart Tutor: I understand your question about \"$msg\". Let me help you break this down step by step. \n\nKey points to remember:\n• Focus on understanding core concepts\n• Practice regularly with varied problems\n• Review mistakes to learn from them\n\nWould you like me to explain this in more detail or provide practice examples?";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI Tutor - Smart LMS</title>

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
    --ai-gradient: linear-gradient(135deg, #8b5cf6 0%, #ec4899 50%, #8b5cf6 100%);
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
    margin-bottom: 30px;
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

/* AI TUTOR LAYOUT */
.ai-tutor-container {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 25px;
    height: calc(100vh - 200px);
    min-height: 600px;
}

/* Main Chat Area */
.chat-main {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    border: 1px solid rgba(255,255,255,0.1);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0,0,0,0.3);
    animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
    from { transform: translateY(40px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.chat-header {
    padding: 20px 25px;
    background: var(--ai-gradient);
    background-size: 200% 200%;
    animation: gradientShift 5s ease infinite;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 15px;
}

@keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.ai-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255,255,255,0.4); }
    50% { transform: scale(1.05); box-shadow: 0 0 20px 5px rgba(255,255,255,0.2); }
}

.ai-status {
    flex: 1;
}

.ai-status h6 {
    margin: 0;
    font-weight: 600;
    font-size: 1.1rem;
}

.ai-status span {
    font-size: 0.85rem;
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

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 25px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.message {
    max-width: 80%;
    animation: messageSlide 0.4s ease-out;
}

@keyframes messageSlide {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.message-user {
    align-self: flex-end;
}

.message-ai {
    align-self: flex-start;
}

.message-bubble {
    padding: 15px 20px;
    border-radius: 20px;
    font-size: 0.95rem;
    line-height: 1.6;
    white-space: pre-line;
}

.message-user .message-bubble {
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    color: #fff;
    border-bottom-right-radius: 5px;
}

.message-ai .message-bubble {
    background: rgba(255,255,255,0.1);
    color: #fff;
    border: 1px solid rgba(255,255,255,0.1);
    border-bottom-left-radius: 5px;
}

.message-time {
    font-size: 0.75rem;
    color: rgba(255,255,255,0.5);
    margin-top: 5px;
    text-align: right;
}

.message-ai .message-time {
    text-align: left;
}

.typing-indicator {
    display: flex;
    gap: 5px;
    padding: 20px;
    align-self: flex-start;
}

.typing-indicator span {
    width: 10px;
    height: 10px;
    background: rgba(255,255,255,0.5);
    border-radius: 50%;
    animation: typingBounce 1.4s infinite ease-in-out both;
}

.typing-indicator span:nth-child(1) { animation-delay: -0.32s; }
.typing-indicator span:nth-child(2) { animation-delay: -0.16s; }

@keyframes typingBounce {
    0%, 80%, 100% { transform: scale(0); }
    40% { transform: scale(1); }
}

.chat-input-area {
    padding: 20px 25px;
    background: rgba(0,0,0,0.2);
    border-top: 1px solid rgba(255,255,255,0.1);
}

.input-wrapper {
    display: flex;
    gap: 12px;
    align-items: center;
}

.chat-input {
    flex: 1;
    background: rgba(255,255,255,0.1);
    border: 2px solid rgba(255,255,255,0.1);
    border-radius: 16px;
    padding: 15px 20px;
    color: #fff;
    font-size: 0.95rem;
    transition: all 0.3s;
}

.chat-input::placeholder {
    color: rgba(255,255,255,0.5);
}

.chat-input:focus {
    outline: none;
    border-color: #8b5cf6;
    background: rgba(255,255,255,0.15);
}

.send-btn {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    border: none;
    color: #fff;
    font-size: 1.3rem;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.send-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 10px 30px rgba(139, 92, 246, 0.4);
}

.send-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

.quick-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
    flex-wrap: wrap;
}

.quick-btn {
    padding: 8px 16px;
    border-radius: 20px;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    color: rgba(255,255,255,0.8);
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.3s;
}

.quick-btn:hover {
    background: rgba(139, 92, 246, 0.3);
    border-color: #8b5cf6;
    color: #fff;
    transform: translateY(-2px);
}

/* Sidebar Panel */
.ai-sidebar {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.sidebar-card {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    border: 1px solid rgba(255,255,255,0.1);
    padding: 25px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    animation: cardEntrance 0.6s ease-out backwards;
}

.sidebar-card:nth-child(1) { animation-delay: 0.1s; }
.sidebar-card:nth-child(2) { animation-delay: 0.2s; }

@keyframes cardEntrance {
    from { transform: translateX(30px); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

.sidebar-card h6 {
    color: #fff;
    font-weight: 600;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.sidebar-card h6 i {
    color: #8b5cf6;
    font-size: 1.2rem;
}

/* Suggested Topics */
.topic-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.topic-item {
    padding: 15px;
    background: rgba(255,255,255,0.05);
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.1);
    cursor: pointer;
    transition: all 0.3s;
}

.topic-item:hover {
    background: rgba(139, 92, 246, 0.15);
    border-color: #8b5cf6;
    transform: translateX(5px);
}

.topic-name {
    color: #fff;
    font-weight: 500;
    font-size: 0.9rem;
    margin-bottom: 5px;
}

.topic-subject {
    color: rgba(255,255,255,0.6);
    font-size: 0.8rem;
}

.topic-level {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 10px;
    font-size: 0.75rem;
    margin-top: 8px;
}

.level-low {
    background: rgba(239, 68, 68, 0.3);
    color: #fca5a5;
}

.level-medium {
    background: rgba(245, 158, 11, 0.3);
    color: #fcd34d;
}

/* Chat History */
.history-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-height: 300px;
    overflow-y: auto;
}

.history-item {
    padding: 12px 15px;
    background: rgba(255,255,255,0.05);
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s;
    border-left: 3px solid transparent;
}

.history-item:hover {
    background: rgba(255,255,255,0.1);
    border-left-color: #8b5cf6;
}

.history-query {
    color: #fff;
    font-size: 0.9rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.history-time {
    color: rgba(255,255,255,0.5);
    font-size: 0.75rem;
    margin-top: 5px;
}

/* Stats Card */
.ai-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.stat-box {
    text-align: center;
    padding: 20px;
    background: rgba(255,255,255,0.05);
    border-radius: 16px;
}

.stat-number {
    font-size: 1.8rem;
    font-weight: 700;
    color: #fff;
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.stat-label {
    color: rgba(255,255,255,0.6);
    font-size: 0.8rem;
    margin-top: 5px;
}

/* Scrollbar */
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.05);
    border-radius: 3px;
}

::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.2);
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: rgba(255,255,255,0.3);
}

/* Responsive */
@media (max-width: 1024px) {
    .ai-tutor-container {
        grid-template-columns: 1fr;
        height: auto;
    }
    
    .ai-sidebar {
        display: none;
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
    <h4>AI Tutor 🤖</h4>
    <p>Your personal learning assistant - ask anything, anytime!</p>
</div>

<!-- AI Tutor Container -->
<div class="ai-tutor-container">

    <!-- Main Chat Area -->
    <div class="chat-main">
        <div class="chat-header">
            <div class="ai-avatar">🤖</div>
            <div class="ai-status">
                <h6>Smart Tutor</h6>
                <span><span class="status-dot"></span> Online & Ready</span>
            </div>
        </div>
        
        <div class="chat-messages" id="chatMessages">
            <!-- Welcome Message -->
            <div class="message message-ai">
                <div class="message-bubble">
👋 Hello <?= $first_name ?>! I'm your AI Tutor.

I can help you with:
• Explaining difficult concepts
• Practice questions & quizzes
• Study tips & techniques
• Homework assistance
• Exam preparation

What would you like to learn today?
                </div>
                <div class="message-time">Just now</div>
            </div>
        </div>
        
        <div class="typing-indicator" id="typingIndicator" style="display: none;">
            <span></span><span></span><span></span>
        </div>
        
        <div class="chat-input-area">
            <div class="input-wrapper">
                <input type="text" class="chat-input" id="messageInput" 
                    placeholder="Type your question here..." 
                    onkeypress="if(event.key==='Enter')sendMessage()">
                <button class="send-btn" onclick="sendMessage()" id="sendBtn">
                    <i class="bi bi-send-fill"></i>
                </button>
            </div>
            
            <div class="quick-actions">
                <button class="quick-btn" onclick="sendQuick('Help me with Math')">
                    <i class="bi bi-calculator"></i> Math Help
                </button>
                <button class="quick-btn" onclick="sendQuick('Explain Science concepts')">
                    <i class="bi bi-flask"></i> Science
                </button>
                <button class="quick-btn" onclick="sendQuick('Study tips for exams')">
                    <i class="bi bi-journal-check"></i> Study Tips
                </button>
                <button class="quick-btn" onclick="sendQuick('Practice questions')">
                    <i class="bi bi-question-circle"></i> Practice
                </button>
            </div>
        </div>
    </div>

    <!-- Sidebar Panel -->
    <div class="ai-sidebar">
        
        <!-- Suggested Topics -->
        <div class="sidebar-card">
            <h6><i class="bi bi-lightbulb"></i> Focus Areas</h6>
            <div class="topic-list">
                <?php if (count($suggested_topics) > 0): ?>
                    <?php foreach ($suggested_topics as $topic): ?>
                    <div class="topic-item" onclick="askAboutTopic('<?= htmlspecialchars($topic['concept_name'] ?? 'General') ?>')">
                        <div class="topic-name"><?= htmlspecialchars($topic['concept_name'] ?? 'General Learning') ?></div>
                        <div class="topic-subject"><?= htmlspecialchars($topic['subject_name'] ?? 'All Subjects') ?></div>
                        <?php if (isset($topic['mastery_level'])): ?>
                        <span class="topic-level level-<?= ($topic['mastery_level'] ?? 50) < 40 ? 'low' : 'medium' ?>">
                            <?= ($topic['mastery_level'] ?? 50) < 40 ? 'Needs Work' : 'Improving' ?> (<?= round($topic['mastery_level'] ?? 50) ?>%)
                        </span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="topic-item" onclick="askAboutTopic('general')">
                        <div class="topic-name">General Learning Tips</div>
                        <div class="topic-subject">Getting Started</div>
                        <span class="topic-level level-medium">Recommended</span>
                    </div>
                    <div class="topic-item" onclick="askAboutTopic('math')">
                        <div class="topic-name">Mathematics</div>
                        <div class="topic-subject">Practice Problems</div>
                        <span class="topic-level level-low">Popular</span>
                    </div>
                    <div class="topic-item" onclick="askAboutTopic('science')">
                        <div class="topic-name">Science</div>
                        <div class="topic-subject">Concepts & Theory</div>
                        <span class="topic-level level-medium">Trending</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Chat History -->
        <div class="sidebar-card">
            <h6><i class="bi bi-clock-history"></i> Recent Chats</h6>
            <div class="history-list">
                <?php if (count($chat_history) > 0): ?>
                    <?php foreach (array_slice($chat_history, 0, 5) as $chat): ?>
                    <div class="history-item" onclick="loadChatHistory('<?= htmlspecialchars(substr($chat['query_text'], 0, 50)) ?>')">
                        <div class="history-query"><?= htmlspecialchars(substr($chat['query_text'], 0, 40)) ?>...</div>
                        <div class="history-time"><?= isset($chat['created_at']) ? date('M j, g:i a', strtotime($chat['created_at'])) : 'Recently' ?></div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="color: rgba(255,255,255,0.5); text-align: center; padding: 20px;">
                        <i class="bi bi-chat-dots" style="font-size: 2rem; display: block; margin-bottom: 10px;"></i>
                        No chat history yet.<br>Start a conversation!
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- AI Stats -->
        <div class="sidebar-card">
            <h6><i class="bi bi-bar-chart"></i> Your Activity</h6>
            <div class="ai-stats">
                <div class="stat-box">
                    <div class="stat-number"><?= count($chat_history) ?></div>
                    <div class="stat-label">Questions</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?= count($suggested_topics) ?></div>
                    <div class="stat-label">Focus Areas</div>
                </div>
            </div>
        </div>

    </div>

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

// Chat Functions
let isTyping = false;

function sendMessage() {
    const input = document.getElementById('messageInput');
    const message = input.value.trim();
    
    if (!message || isTyping) return;
    
    // Add user message
    addMessage(message, 'user');
    input.value = '';
    
    // Show typing indicator
    showTyping();
    
    // Send to server
    fetch('', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'ai_message=' + encodeURIComponent(message)
    })
    .then(res => res.text())
    .then(response => {
        hideTyping();
        addMessage(response, 'ai');
    })
    .catch(err => {
        hideTyping();
        addMessage('Sorry, I encountered an error. Please try again.', 'ai');
    });
}

function sendQuick(text) {
    document.getElementById('messageInput').value = text;
    sendMessage();
}

function askAboutTopic(topic) {
    const question = topic === 'general' 
        ? 'Give me some general learning tips to improve my studies'
        : `Help me understand ${topic} better`;
    document.getElementById('messageInput').value = question;
    sendMessage();
}

function loadChatHistory(query) {
    document.getElementById('messageInput').value = query;
    sendMessage();
}

function addMessage(text, sender) {
    const container = document.getElementById('chatMessages');
    const time = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `message message-${sender}`;
    messageDiv.innerHTML = `
        <div class="message-bubble">${escapeHtml(text)}</div>
        <div class="message-time">${time}</div>
    `;
    
    container.appendChild(messageDiv);
    container.scrollTop = container.scrollHeight;
}

function showTyping() {
    isTyping = true;
    document.getElementById('typingIndicator').style.display = 'flex';
    document.getElementById('sendBtn').disabled = true;
}

function hideTyping() {
    isTyping = false;
    document.getElementById('typingIndicator').style.display = 'none';
    document.getElementById('sendBtn').disabled = false;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Auto-focus input on load
window.addEventListener('load', () => {
    document.getElementById('messageInput').focus();
});
</script>

</body>
</html>