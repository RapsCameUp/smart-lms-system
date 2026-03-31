<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart LMS | AI-Powered Adaptive Learning for South Africa</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- GSAP for Animations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    
    <style>
    :root {
        --primary: #0f766e;
        --primary-light: #14b8a6;
        --primary-dark: #0d5c56;
        --secondary: #f59e0b;
        --accent: #8b5cf6;
        --success: #22c55e;
        --dark: #0f172a;
        --light: #ffffff;
        --gray: #64748b;
        --gradient-1: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
        --gradient-2: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);
        --gradient-3: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
        --shadow-lg: 0 10px 40px rgba(0,0,0,0.1);
        --shadow-glow: 0 0 40px rgba(15, 118, 110, 0.3);
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }
    
    body {
        font-family: 'Inter', sans-serif;
        background: #f8fafc;
        color: var(--dark);
        overflow-x: hidden;
    }

    h1, h2, h3, h4, h5 { font-family: 'Poppins', sans-serif; font-weight: 600; }

    /* ===== HERO SECTION ===== */
    .hero {
        min-height: 100vh;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f766e 100%);
        position: relative;
        display: flex;
        align-items: center;
        overflow: hidden;
    }

    .hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    .hero-particles {
        position: absolute;
        inset: 0;
        overflow: hidden;
    }

    .particle {
        position: absolute;
        width: 6px;
        height: 6px;
        background: rgba(255,255,255,0.3);
        border-radius: 50%;
        animation: float 20s infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
        10% { opacity: 1; }
        90% { opacity: 1; }
        100% { transform: translateY(-100vh) rotate(720deg); opacity: 0; }
    }

    .hero-content {
        position: relative;
        z-index: 2;
        color: #fff;
        padding: 2rem;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255,255,255,0.1);
        backdrop-filter: blur(10px);
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-size: 0.875rem;
        margin-bottom: 1.5rem;
        border: 1px solid rgba(255,255,255,0.2);
        animation: fadeInDown 0.8s ease;
    }

    .pulse-dot {
        width: 8px;
        height: 8px;
        background: var(--success);
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
        50% { box-shadow: 0 0 0 10px rgba(34, 197, 94, 0); }
    }

    .hero h1 {
        font-size: clamp(2.5rem, 5vw, 4rem);
        font-weight: 700;
        margin-bottom: 1.5rem;
        line-height: 1.2;
    }

    .hero h1 .highlight {
        background: linear-gradient(135deg, #22c55e 0%, #14b8a6 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .typing-text {
        font-size: 1.25rem;
        opacity: 0.9;
        margin-bottom: 2rem;
        min-height: 3rem;
    }

    .hero-buttons {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .btn-hero {
        padding: 1rem 2rem;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
    }

    .btn-hero-primary {
        background: var(--gradient-1);
        color: #fff;
        border: none;
        box-shadow: 0 10px 30px rgba(15, 118, 110, 0.4);
    }

    .btn-hero-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 20px 40px rgba(15, 118, 110, 0.5);
        color: #fff;
    }

    .btn-hero-secondary {
        background: transparent;
        color: #fff;
        border: 2px solid rgba(255,255,255,0.3);
    }

    .btn-hero-secondary:hover {
        background: rgba(255,255,255,0.1);
        border-color: #fff;
        color: #fff;
    }

    .hero-stats {
        position: absolute;
        bottom: 3rem;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 3rem;
        color: #fff;
        z-index: 2;
    }

    .stat-item {
        text-align: center;
        opacity: 0;
        animation: fadeInUp 0.8s ease forwards;
    }

    .stat-item:nth-child(1) { animation-delay: 0.2s; }
    .stat-item:nth-child(2) { animation-delay: 0.4s; }
    .stat-item:nth-child(3) { animation-delay: 0.6s; }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        display: block;
        background: linear-gradient(135deg, #fff 0%, #14b8a6 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    /* ===== AI TUTOR SECTION ===== */
    .ai-tutor-section {
        padding: 6rem 0;
        background: var(--light);
        position: relative;
    }

    .ai-tutor-section::before {
        content: '';
        position: absolute;
        top: -10%;
        right: -5%;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(139, 92, 246, 0.1) 0%, transparent 70%);
        border-radius: 50%;
    }

    .section-header {
        text-align: center;
        margin-bottom: 4rem;
    }

    .section-tag {
        display: inline-block;
        padding: 0.5rem 1rem;
        background: rgba(15, 118, 110, 0.1);
        color: var(--primary);
        border-radius: 50px;
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 1rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .ai-tutor-card {
        background: var(--light);
        border-radius: 24px;
        padding: 2.5rem;
        box-shadow: var(--shadow-lg);
        border: 1px solid #e5e7eb;
        transition: all 0.4s;
        height: 100%;
        position: relative;
        overflow: hidden;
    }

    .ai-tutor-card:hover {
        transform: translateY(-10px);
        box-shadow: var(--shadow-glow);
    }

    .ai-tutor-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: var(--gradient-2);
        transform: scaleX(0);
        transition: transform 0.4s;
    }

    .ai-tutor-card:hover::before {
        transform: scaleX(1);
    }

    .ai-icon {
        width: 80px;
        height: 80px;
        background: var(--gradient-2);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: #fff;
        margin-bottom: 1.5rem;
        position: relative;
    }

    .ai-icon::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.3) 50%, transparent 70%);
        transform: translateX(-100%);
        transition: transform 0.6s;
    }

    .ai-tutor-card:hover .ai-icon::after {
        transform: translateX(100%);
    }

    .language-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.75rem;
        background: rgba(139, 92, 246, 0.1);
        color: var(--accent);
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
    }

    /* ===== FEATURES GRID ===== */
    .features-section {
        padding: 6rem 0;
        background: linear-gradient(180deg, #f8fafc 0%, #fff 100%);
    }

    .feature-card {
        background: var(--light);
        border-radius: 20px;
        padding: 2rem;
        box-shadow: var(--shadow-lg);
        border: 1px solid #e5e7eb;
        transition: all 0.4s;
        height: 100%;
        text-align: center;
    }

    .feature-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }

    .feature-icon {
        width: 70px;
        height: 70px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin: 0 auto 1.5rem;
        color: #fff;
    }

    .feature-icon.purple { background: var(--gradient-2); }
    .feature-icon.teal { background: var(--gradient-1); }
    .feature-icon.orange { background: var(--gradient-3); }
    .feature-icon.green { background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); }
    .feature-icon.red { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
    .feature-icon.blue { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }

    /* ===== OFFLINE SECTION ===== */
    .offline-section {
        padding: 6rem 0;
        background: var(--dark);
        color: #fff;
        position: relative;
        overflow: hidden;
    }

    .offline-section::before {
        content: '';
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    .offline-card {
        background: rgba(255,255,255,0.05);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 20px;
        padding: 2rem;
        transition: all 0.4s;
    }

    .offline-card:hover {
        background: rgba(255,255,255,0.1);
        transform: translateY(-5px);
    }

    .sync-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: rgba(34, 197, 94, 0.2);
        color: var(--success);
        border-radius: 50px;
        font-size: 0.875rem;
    }

    .sync-pulse {
        width: 8px;
        height: 8px;
        background: var(--success);
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    /* ===== STEM SECTION ===== */
    .stem-section {
        padding: 6rem 0;
        background: var(--light);
    }

    .stem-card {
        position: relative;
        border-radius: 24px;
        overflow: hidden;
        height: 300px;
        box-shadow: var(--shadow-lg);
        transition: all 0.4s;
    }

    .stem-card:hover {
        transform: scale(1.02);
        box-shadow: 0 25px 50px rgba(0,0,0,0.2);
    }

    .stem-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s;
    }

    .stem-card:hover img {
        transform: scale(1.1);
    }

    .stem-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(15, 23, 42, 0.9) 0%, transparent 60%);
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        padding: 2rem;
        color: #fff;
    }

    .stem-tag {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        background: var(--secondary);
        color: var(--dark);
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        width: fit-content;
    }

    /* ===== DASHBOARD PREVIEW ===== */
    .dashboard-section {
        padding: 6rem 0;
        background: #f8fafc;
    }

    .dashboard-preview {
        background: var(--light);
        border-radius: 24px;
        box-shadow: var(--shadow-lg);
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }

    .dashboard-header {
        background: var(--gradient-1);
        color: #fff;
        padding: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .dashboard-body {
        padding: 2rem;
    }

    .metric-card {
        background: #f8fafc;
        border-radius: 16px;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.3s;
    }

    .metric-card:hover {
        background: var(--primary);
        color: #fff;
        transform: translateY(-5px);
    }

    .metric-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary);
        transition: color 0.3s;
    }

    .metric-card:hover .metric-value {
        color: #fff;
    }

    .risk-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .risk-high { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
    .risk-medium { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
    .risk-low { background: rgba(34, 197, 94, 0.1); color: #22c55e; }

    /* ===== CTA SECTION ===== */
    .cta-section {
        padding: 6rem 0;
        background: var(--gradient-1);
        color: #fff;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .cta-section::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: rotate 30s linear infinite;
    }

    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .cta-content {
        position: relative;
        z-index: 2;
    }

    /* ===== FOOTER ===== */
    footer {
        background: var(--dark);
        color: #fff;
        padding: 4rem 0 2rem;
    }

    .footer-link {
        color: rgba(255,255,255,0.6);
        text-decoration: none;
        transition: all 0.3s;
        display: block;
        margin-bottom: 0.75rem;
    }

    .footer-link:hover {
        color: var(--primary-light);
        transform: translateX(5px);
    }

    /* ===== FLOATING AI CHAT ===== */
    .ai-float-btn {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        width: 70px;
        height: 70px;
        background: var(--gradient-2);
        border: none;
        border-radius: 50%;
        color: #fff;
        font-size: 1.75rem;
        cursor: pointer;
        box-shadow: 0 10px 30px rgba(139, 92, 246, 0.4);
        z-index: 1000;
        transition: all 0.3s;
        animation: pulse 2s infinite;
    }

    .ai-float-btn:hover {
        transform: scale(1.1) rotate(10deg);
    }

    .ai-chat-window {
        position: fixed;
        bottom: 100px;
        right: 2rem;
        width: 400px;
        max-height: 600px;
        background: var(--light);
        border-radius: 24px;
        box-shadow: 0 25px 50px rgba(0,0,0,0.25);
        z-index: 999;
        display: none;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }

    .ai-chat-window.active {
        display: flex;
        animation: slideUp 0.4s ease;
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .chat-header {
        background: var(--gradient-2);
        color: #fff;
        padding: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .chat-body {
        flex: 1;
        overflow-y: auto;
        padding: 1.5rem;
        background: #f8fafc;
        max-height: 350px;
    }

    .chat-bubble {
        padding: 1rem;
        border-radius: 16px;
        margin-bottom: 1rem;
        max-width: 85%;
        font-size: 0.9rem;
        line-height: 1.5;
    }

    .chat-bubble.bot {
        background: var(--light);
        border: 1px solid #e5e7eb;
        color: var(--dark);
        border-bottom-left-radius: 4px;
    }

    .chat-bubble.user {
        background: var(--gradient-2);
        color: #fff;
        margin-left: auto;
        border-bottom-right-radius: 4px;
    }

    .chat-input-area {
        padding: 1rem;
        background: var(--light);
        border-top: 1px solid #e5e7eb;
        display: flex;
        gap: 0.5rem;
    }

    .language-selector {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }

    .lang-btn {
        padding: 0.5rem 1rem;
        border: 1px solid #e5e7eb;
        background: var(--light);
        border-radius: 50px;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.3s;
    }

    .lang-btn.active, .lang-btn:hover {
        background: var(--accent);
        color: #fff;
        border-color: var(--accent);
    }

    /* ===== ANIMATIONS ===== */
    .reveal {
        opacity: 0;
        transform: translateY(30px);
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .hero { min-height: auto; padding: 6rem 0; }
        .hero-stats {
            position: relative;
            bottom: auto;
            left: auto;
            transform: none;
            flex-direction: column;
            gap: 1.5rem;
            margin-top: 3rem;
        }
        .ai-chat-window {
            width: calc(100% - 2rem);
            right: 1rem;
        }
    }

    /* ===== SCROLLBAR ===== */
    ::-webkit-scrollbar { width: 8px; }
    ::-webkit-scrollbar-track { background: #f1f5f9; }
    ::-webkit-scrollbar-thumb { background: var(--primary); border-radius: 4px; }
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); padding: 1rem 0;">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="#">
                <div style="width: 40px; height: 40px; background: var(--gradient-1); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-mortarboard-fill text-white"></i>
                </div>
                <span class="fw-bold">Smart LMS</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="#ai-tutor">AI Tutor</a></li>
                    <li class="nav-item"><a class="nav-link" href="#stem">STEM</a></li>
                    <li class="nav-item"><a class="nav-link" href="#offline">Offline</a></li>
                    <li class="nav-item ms-lg-3">
                        <a href="login.php" class="btn btn-light rounded-pill px-4 fw-bold">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-particles" id="particles"></div>
        
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    <div class="hero-badge">
                        <span class="pulse-dot"></span>
                        <span>🇿🇦 Built for South African Education</span>
                    </div>
                    
                    <h1>
                        Transforming Education with <span class="highlight">AI-Powered</span> Learning
                    </h1>
                    
                    <p class="typing-text" id="heroTyping"></p>
                    
                    <div class="hero-buttons">
                        <a href="register.php" class="btn-hero btn-hero-primary">
                            <i class="bi bi-rocket-takeoff"></i> Get Started Free
                        </a>
                        <a href="#demo" class="btn-hero btn-hero-secondary">
                            <i class="bi bi-play-circle"></i> Watch Demo
                        </a>
                    </div>

                    <div class="mt-4 d-flex gap-3 flex-wrap">
                        <span class="badge bg-success bg-opacity-25 text-success border border-success">
                            <i class="bi bi-wifi-off me-1"></i> Works Offline
                        </span>
                        <span class="badge bg-primary bg-opacity-25 text-primary border border-primary">
                            <i class="bi bi-translate me-1"></i> 11 Languages
                        </span>
                        <span class="badge bg-warning bg-opacity-25 text-warning border border-warning">
                            <i class="bi bi-phone me-1"></i> Mobile First
                        </span>
                    </div>
                </div>
                
                <div class="col-lg-6 d-none d-lg-block">
                    <div class="position-relative">
                        <!-- Animated Dashboard Preview -->
                        <div style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border-radius: 24px; padding: 2rem; border: 1px solid rgba(255,255,255,0.2);">
                            <div class="d-flex align-items-center gap-3 mb-4">
                                <div style="width: 50px; height: 50px; background: var(--gradient-1); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-robot text-white fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 text-white">AI Tutor Active</h5>
                                    <small class="text-white-50">Personalizing your learning...</small>
                                </div>
                            </div>
                            
                            <div class="mb-3" style="background: rgba(0,0,0,0.2); border-radius: 12px; padding: 1rem;">
                                <div class="d-flex justify-content-between text-white mb-2">
                                    <span>Mathematics Progress</span>
                                    <span>78%</span>
                                </div>
                                <div class="progress" style="height: 8px; background: rgba(255,255,255,0.1);">
                                    <div class="progress-bar bg-success" style="width: 78%"></div>
                                </div>
                            </div>
                            
                            <div style="background: rgba(0,0,0,0.2); border-radius: 12px; padding: 1rem;">
                                <div class="d-flex justify-content-between text-white mb-2">
                                    <span>Science Engagement</span>
                                    <span>92%</span>
                                </div>
                                <div class="progress" style="height: 8px; background: rgba(255,255,255,0.1);">
                                    <div class="progress-bar bg-warning" style="width: 92%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="hero-stats">
            <div class="stat-item">
                <span class="stat-number">50K+</span>
                <span class="stat-label">Active Learners</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">85%</span>
                <span class="stat-label">Pass Rate Improvement</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">11</span>
                <span class="stat-label">Official Languages</span>
            </div>
        </div>
    </section>

    <!-- AI Tutor Section -->
    <section class="ai-tutor-section" id="ai-tutor">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-tag">🤖 Artificial Intelligence</span>
                <h2 class="display-5 fw-bold mb-3">Your Personal AI Tutor</h2>
                <p class="lead text-muted mx-auto" style="max-width: 600px;">
                    Available 24/7 in your preferred South African language. Explains concepts, generates practice problems, and provides instant feedback.
                </p>
            </div>

            <div class="row g-4">
                <div class="col-lg-4 reveal">
                    <div class="ai-tutor-card">
                        <div class="ai-icon">
                            <i class="bi bi-translate"></i>
                        </div>
                        <h4>Multilingual Support</h4>
                        <p class="text-muted mb-3">
                            Learn in your home language. AI Tutor communicates in isiZulu, isiXhosa, Afrikaans, Sesotho, English, and 6 more official languages.
                        </p>
                        <div class="language-selector">
                            <span class="language-badge">🇬🇧 English</span>
                            <span class="language-badge">🇿🇦 isiZulu</span>
                            <span class="language-badge">🇿🇦 isiXhosa</span>
                            <span class="language-badge">🇿🇦 Afrikaans</span>
                            <span class="language-badge">🇿🇦 Sesotho</span>
                            <span class="language-badge">+6 more</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 reveal">
                    <div class="ai-tutor-card">
                        <div class="ai-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);">
                            <i class="bi bi-lightbulb"></i>
                        </div>
                        <h4>Adaptive Explanations</h4>
                        <p class="text-muted mb-3">
                            Struggling with a concept? The AI adjusts its explanation style, provides alternative examples, and creates personalized practice problems.
                        </p>
                        <ul class="list-unstyled text-start">
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-warning me-2"></i>Visual learners get diagrams</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-warning me-2"></i>Step-by-step problem solving</li>
                            <li><i class="bi bi-check-circle-fill text-warning me-2"></i>Real-world SA context examples</li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-4 reveal">
                    <div class="ai-tutor-card">
                        <div class="ai-icon" style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);">
                            <i class="bi bi-chat-dots"></i>
                        </div>
                        <h4>Instant Feedback</h4>
                        <p class="text-muted mb-3">
                            Ask questions anytime. Get immediate, constructive feedback on your work with suggestions for improvement before formal assessments.
                        </p>
                        <div class="d-flex align-items-center gap-2 p-3 bg-light rounded-3">
                            <i class="bi bi-clock-history text-success fs-4"></i>
                            <div>
                                <small class="text-muted d-block">Average Response Time</small>
                                <strong class="text-success">Under 3 seconds</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Demo Interface -->
            <div class="mt-5 reveal">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="row g-0">
                        <div class="col-lg-4 bg-dark text-white p-4">
                            <h5 class="mb-4"><i class="bi bi-robot me-2"></i>Try the AI Tutor</h5>
                            <div class="mb-3">
                                <small class="text-white-50">SELECT SUBJECT</small>
                                <select class="form-select bg-dark text-white border-secondary mt-1">
                                    <option>Mathematics</option>
                                    <option>Physical Sciences</option>
                                    <option>Life Sciences</option>
                                    <option>Chemistry</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <small class="text-white-50">GRADE LEVEL</small>
                                <select class="form-select bg-dark text-white border-secondary mt-1">
                                    <option>Grade 10</option>
                                    <option>Grade 11</option>
                                    <option>Grade 12</option>
                                    <option>University First Year</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <small class="text-white-50">LANGUAGE</small>
                                <div class="language-selector mt-1">
                                    <button class="lang-btn active">English</button>
                                    <button class="lang-btn">isiZulu</button>
                                    <button class="lang-btn">Afrikaans</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8 p-4" style="background: #f8fafc;">
                            <div class="d-flex align-items-center gap-3 mb-4">
                                <div style="width: 40px; height: 40px; background: var(--gradient-2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-robot text-white"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">AI Tutor</h6>
                                    <small class="text-muted">Online • Ready to help</small>
                                </div>
                            </div>
                            
                            <div class="chat-bubble bot mb-3">
                                👋 Sawubona! I'm ready to help with Mathematics. Ask me anything about algebra, calculus, geometry, or trigonometry. I'll explain in isiZulu or switch to your preferred language.
                            </div>

                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Type your question here..." style="border-radius: 12px 0 0 12px;">
                                <button class="btn btn-primary" style="border-radius: 0 12px 12px 0;">
                                    <i class="bi bi-send"></i>
                                </button>
                            </div>
                            
                            <div class="mt-3 d-flex gap-2 flex-wrap">
                                <small class="text-muted">Try asking:</small>
                                <span class="badge bg-light text-dark border" style="cursor: pointer;">"Explain quadratic equations"</span>
                                <span class="badge bg-light text-dark border" style="cursor: pointer;">"Help with trigonometry"</span>
                                <span class="badge bg-light text-dark border" style="cursor: pointer;">"Practice problems"</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Grid -->
    <section class="features-section" id="features">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-tag">🚀 Platform Features</span>
                <h2 class="display-5 fw-bold mb-3">Built for South African Challenges</h2>
                <p class="lead text-muted mx-auto" style="max-width: 600px;">
                    Addressing overcrowded classrooms, teacher shortages, and connectivity gaps with innovative technology solutions.
                </p>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-4 reveal">
                    <div class="feature-card">
                        <div class="feature-icon purple">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <h4>Adaptive Learning</h4>
                        <p class="text-muted">
                            Real-time difficulty adjustment based on performance. Content pacing adapts to each learner's mastery level, ensuring no one is left behind.
                        </p>
                        <div class="mt-3">
                            <span class="badge bg-primary bg-opacity-10 text-primary">Personalized Path</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 reveal">
                    <div class="feature-card">
                        <div class="feature-icon teal">
                            <i class="bi bi-wifi-off"></i>
                        </div>
                        <h4>Offline-First Design</h4>
                        <p class="text-muted">
                            Download lessons, videos, and quizzes for offline use. Automatic sync when connectivity returns. Perfect for rural and low-bandwidth areas.
                        </p>
                        <div class="mt-3">
                            <span class="badge bg-success bg-opacity-10 text-success">Zero Data Mode</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 reveal">
                    <div class="feature-card">
                        <div class="feature-icon orange">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <h4>Early Warning System</h4>
                        <p class="text-muted">
                            AI identifies at-risk students before they drop out. Teachers receive alerts with recommended interventions and support resources.
                        </p>
                        <div class="mt-3">
                            <span class="badge bg-warning bg-opacity-10 text-warning">Predictive Analytics</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 reveal">
                    <div class="feature-card">
                        <div class="feature-icon green">
                            <i class="bi bi-trophy"></i>
                        </div>
                        <h4>Gamified Learning</h4>
                        <p class="text-muted">
                            Earn points, badges, and unlock achievements. Compete with classmates or collaborate in teams. Leaderboards celebrate progress, not just perfection.
                        </p>
                        <div class="mt-3">
                            <span class="badge bg-success bg-opacity-10 text-success">Engagement Boost</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 reveal">
                    <div class="feature-card">
                        <div class="feature-icon red">
                            <i class="bi bi-people"></i>
                        </div>
                        <h4>Teacher Dashboard</h4>
                        <p class="text-muted">
                            Comprehensive analytics for educators. Track class progress, identify struggling topics, and access teaching resources aligned with CAPS curriculum.
                        </p>
                        <div class="mt-3">
                            <span class="badge bg-danger bg-opacity-10 text-danger">CAPS Aligned</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 reveal">
                    <div class="feature-card">
                        <div class="feature-icon blue">
                            <i class="bi bi-phone"></i>
                        </div>
                        <h4>Mobile Optimized</h4>
                        <p class="text-muted">
                            Works on any device - from high-end tablets to basic smartphones. Lightweight app with data-saving features and compressed video streaming.
                        </p>
                        <div class="mt-3">
                            <span class="badge bg-primary bg-opacity-10 text-primary">Any Device</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Offline Section -->
    <section class="offline-section" id="offline">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 reveal">
                    <span class="badge bg-success bg-opacity-25 text-success border border-success mb-3">
                        <i class="bi bi-wifi-off me-1"></i> Connectivity Solution
                    </span>
                    <h2 class="display-5 fw-bold mb-4">Learn Without Internet</h2>
                    <p class="lead mb-4 opacity-75">
                        Designed for South Africa's connectivity challenges. Our offline-first architecture ensures uninterrupted learning in rural areas, townships, and during load shedding.
                    </p>
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="offline-card">
                                <i class="bi bi-download fs-2 text-success mb-3"></i>
                                <h5>Smart Downloads</h5>
                                <p class="small opacity-75 mb-0">Schedule downloads during off-peak hours. Wi-Fi only mode prevents data charges.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="offline-card">
                                <i class="bi bi-arrow-repeat fs-2 text-success mb-3"></i>
                                <h5>Auto-Sync</h5>
                                <p class="small opacity-75 mb-0">Progress automatically uploads when connection returns. Never lose your work.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="offline-card">
                                <i class="bi bi-sd-card fs-2 text-success mb-3"></i>
                                <h5>SD Card Support</h5>
                                <p class="small opacity-75 mb-0">Store content on external memory. Share learning materials via Bluetooth.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="offline-card">
                                <i class="bi bi-battery-charging fs-2 text-success mb-3"></i>
                                <h5>Low Power Mode</h5>
                                <p class="small opacity-75 mb-0">Optimized for extended battery life during power outages.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="sync-indicator">
                            <span class="sync-pulse"></span>
                            <span>Last synced: 2 minutes ago • 156MB saved today</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6 reveal">
                    <div style="background: rgba(255,255,255,0.05); backdrop-filter: blur(10px); border-radius: 24px; padding: 2rem; border: 1px solid rgba(255,255,255,0.1);">
                        <h5 class="mb-4"><i class="bi bi-phone me-2"></i>Mobile App Preview</h5>
                        
                        <div class="mb-4 p-3 rounded-3" style="background: rgba(0,0,0,0.3);">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="small opacity-75">Storage Usage</span>
                                <span class="small text-success">2.4GB / 16GB</span>
                            </div>
                            <div class="progress mb-2" style="height: 8px; background: rgba(255,255,255,0.1);">
                                <div class="progress-bar bg-success" style="width: 15%"></div>
                            </div>
                            <small class="opacity-50">15% used • 45 lessons available offline</small>
                        </div>

                        <div class="mb-4 p-3 rounded-3" style="background: rgba(0,0,0,0.3);">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="small opacity-75">Sync Status</span>
                                <span class="badge bg-success">Up to date</span>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <div class="flex-grow-1">
                                    <small class="d-block opacity-75">Mathematics Grade 12</small>
                                    <div class="progress mt-1" style="height: 4px; background: rgba(255,255,255,0.1);">
                                        <div class="progress-bar bg-primary" style="width: 100%"></div>
                                    </div>
                                </div>
                                <i class="bi bi-check-circle-fill text-success"></i>
                            </div>
                            <div class="d-flex align-items-center gap-3 mt-2">
                                <div class="flex-grow-1">
                                    <small class="d-block opacity-75">Physics Grade 11</small>
                                    <div class="progress mt-1" style="height: 4px; background: rgba(255,255,255,0.1);">
                                        <div class="progress-bar bg-primary" style="width: 75%"></div>
                                    </div>
                                </div>
                                <i class="bi bi-arrow-down-circle-fill text-warning"></i>
                            </div>
                        </div>

                        <div class="p-3 rounded-3" style="background: rgba(0,0,0,0.3);">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small opacity-75">Data Saver Mode</span>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" checked style="background-color: var(--success);">
                                </div>
                            </div>
                            <small class="opacity-50">Compress images • Stream audio only</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- STEM Section -->
    <section class="stem-section" id="stem">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-tag">🔬 STEM Education</span>
                <h2 class="display-5 fw-bold mb-3">Interactive 3D Simulations</h2>
                <p class="lead text-muted mx-auto" style="max-width: 600px;">
                    Making abstract science and mathematics concepts tangible through gamified, visual learning experiences.
                </p>
            </div>

            <div class="row g-4">
                <div class="col-md-4 reveal">
                    <div class="stem-card">
                        <img src="https://images.unsplash.com/photo-1635070041078-e363dbe005cb?w=600" alt="Chemistry">
                        <div class="stem-overlay">
                            <span class="stem-tag">Chemistry</span>
                            <h4>Virtual Laboratory</h4>
                            <p class="small opacity-75">Conduct experiments safely in a 3D virtual lab. Mix chemicals, observe reactions, and learn molecular structures.</p>
                            <button class="btn btn-light btn-sm rounded-pill mt-2">
                                <i class="bi bi-play-fill me-1"></i>Try Simulation
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 reveal">
                    <div class="stem-card">
                        <img src="https://images.unsplash.com/photo-1636466497217-26a8cbeaf0aa?w=600" alt="Physics">
                        <div class="stem-overlay">
                            <span class="stem-tag" style="background: var(--primary); color: white;">Physics</span>
                            <h4>Mechanics Sandbox</h4>
                            <p class="small opacity-75">Explore forces, motion, and energy with interactive physics simulations. Build and test virtual machines.</p>
                            <button class="btn btn-light btn-sm rounded-pill mt-2">
                                <i class="bi bi-play-fill me-1"></i>Try Simulation
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 reveal">
                    <div class="stem-card">
                        <img src="https://images.unsplash.com/photo-1509228468518-180dd4864904?w=600" alt="Mathematics">
                        <div class="stem-overlay">
                            <span class="stem-tag" style="background: var(--secondary);">Mathematics</span>
                            <h4>3D Graphing Tool</h4>
                            <p class="small opacity-75">Visualize complex functions in three dimensions. Rotate, zoom, and explore mathematical concepts interactively.</p>
                            <button class="btn btn-light btn-sm rounded-pill mt-2">
                                <i class="bi bi-play-fill me-1"></i>Try Simulation
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5 p-4 rounded-4 reveal" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border: 1px solid #bae6fd;">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="fw-bold mb-2"><i class="bi bi-controller me-2 text-primary"></i>Gamified Learning Modules</h4>
                        <p class="text-muted mb-0">
                            Earn "Scientist Points" for completing experiments, unlock "Einstein Badge" for mastering physics, and compete on school leaderboards. 
                            Learning becomes an adventure with quests, challenges, and collaborative projects.
                        </p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <button class="btn btn-primary rounded-pill px-4">
                            <i class="bi bi-trophy me-2"></i>View Leaderboard
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Teacher Dashboard Preview -->
    <section class="dashboard-section">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-tag">📊 Analytics</span>
                <h2 class="display-5 fw-bold mb-3">Teacher & Admin Dashboard</h2>
                <p class="lead text-muted mx-auto" style="max-width: 600px;">
                    Data-driven insights to identify at-risk students early and optimize teaching strategies.
                </p>
            </div>

            <div class="dashboard-preview reveal">
                <div class="dashboard-header">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-speedometer2 fs-4"></i>
                        <div>
                            <h5 class="mb-0">Class Performance Overview</h5>
                            <small>Grade 12 Mathematics • 45 Students</small>
                        </div>
                    </div>
                    <button class="btn btn-light btn-sm rounded-pill">
                        <i class="bi bi-download me-1"></i>Export Report
                    </button>
                </div>
                
                <div class="dashboard-body">
                    <div class="row g-4 mb-4">
                        <div class="col-md-3">
                            <div class="metric-card">
                                <div class="metric-value">87%</div>
                                <small class="text-muted">Class Average</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="metric-card">
                                <div class="metric-value">12</div>
                                <small class="text-muted">At-Risk Students</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="metric-card">
                                <div class="metric-value">94%</div>
                                <small class="text-muted">Attendance Rate</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="metric-card">
                                <div class="metric-value">3.2h</div>
                                <small class="text-muted">Avg. Study Time</small>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3">⚠️ Early Warning Indicators</h6>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Student</th>
                                    <th>Risk Level</th>
                                    <th>Indicators</th>
                                    <th>Recommended Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width: 32px; height: 32px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; center; justify-content: center; color: #ef4444; font-weight: bold;">TM</div>
                                            <span>Thabo Mokoena</span>
                                        </div>
                                    </td>
                                    <td><span class="risk-indicator risk-high"><i class="bi bi-exclamation-circle"></i> High Risk</span></td>
                                    <td>3 absences, 45% quiz avg, no login 5 days</td>
                                    <td><button class="btn btn-sm btn-outline-danger">Schedule Intervention</button></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width: 32px; height: 32px; background: #fef3c7; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #f59e0b; font-weight: bold;">LN</div>
                                            <span>Lebo Ndlovu</span>
                                        </div>
                                    </td>
                                    <td><span class="risk-indicator risk-medium"><i class="bi bi-exclamation-triangle"></i> Medium Risk</span></td>
                                    <td>Declining scores, low engagement</td>
                                    <td><button class="btn btn-sm btn-outline-warning">Peer Tutoring</button></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width: 32px; height: 32px; background: #dcfce7; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #22c55e; font-weight: bold;">SZ</div>
                                            <span>Sipho Zulu</span>
                                        </div>
                                    </td>
                                    <td><span class="risk-indicator risk-low"><i class="bi bi-check-circle"></i> On Track</span></td>
                                    <td>Improved 15% this week</td>
                                    <td><button class="btn btn-sm btn-outline-success">Give Kudos</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container cta-content">
            <h2 class="display-4 fw-bold mb-4">Ready to Transform Education?</h2>
            <p class="lead mb-4 opacity-75">Join 50,000+ South African learners already using Smart LMS</p>
            
            <div class="row justify-content-center g-4 mb-5">
                <div class="col-md-4">
                    <div class="p-4 rounded-4" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px);">
                        <i class="bi bi-person-check fs-1 mb-3 d-block"></i>
                        <h5>For Students</h5>
                        <p class="small opacity-75">Personalized learning paths, AI tutoring, and offline access</p>
                        <a href="register.php?type=student" class="btn btn-light rounded-pill w-100">Student Sign Up</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-4 rounded-4" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px);">
                        <i class="bi bi-person-workspace fs-1 mb-3 d-block"></i>
                        <h5>For Teachers</h5>
                        <p class="small opacity-75">Analytics dashboards, automated grading, and curriculum tools</p>
                        <a href="register.php?type=teacher" class="btn btn-light rounded-pill w-100">Teacher Sign Up</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-4 rounded-4" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px);">
                        <i class="bi bi-building fs-1 mb-3 d-block"></i>
                        <h5>For Schools</h5>
                        <p class="small opacity-75">Institutional licenses, admin controls, and implementation support</p>
                        <a href="contact.php" class="btn btn-outline-light rounded-pill w-100">Contact Sales</a>
                    </div>
                </div>
            </div>

            <p class="small opacity-50">
                <i class="bi bi-shield-check me-1"></i> 
                WCAG 2.1 AA Accessible • POPIA Compliant • DBE Curriculum Aligned
            </p>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row g-4 mb-4">
                <div class="col-lg-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div style="width: 40px; height: 40px; background: var(--gradient-1); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-mortarboard-fill text-white"></i>
                        </div>
                        <span class="fw-bold fs-5">Smart LMS</span>
                    </div>
                    <p class="small opacity-75">
                        Empowering South African education through AI-powered adaptive learning, accessible to every learner regardless of location or connectivity.
                    </p>
                    <div class="d-flex gap-3 mt-3">
                        <a href="#" class="text-white opacity-75 hover-opacity-100"><i class="bi bi-facebook fs-5"></i></a>
                        <a href="#" class="text-white opacity-75 hover-opacity-100"><i class="bi bi-twitter fs-5"></i></a>
                        <a href="#" class="text-white opacity-75 hover-opacity-100"><i class="bi bi-linkedin fs-5"></i></a>
                        <a href="#" class="text-white opacity-75 hover-opacity-100"><i class="bi bi-youtube fs-5"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-4">
                    <h6 class="fw-bold mb-3">Platform</h6>
                    <a href="#features" class="footer-link">Features</a>
                    <a href="#ai-tutor" class="footer-link">AI Tutor</a>
                    <a href="#stem" class="footer-link">STEM Modules</a>
                    <a href="#offline" class="footer-link">Offline Mode</a>
                </div>
                
                <div class="col-lg-2 col-md-4">
                    <h6 class="fw-bold mb-3">Resources</h6>
                    <a href="#" class="footer-link">Help Center</a>
                    <a href="#" class="footer-link">CAPS Guide</a>
                    <a href="#" class="footer-link">Training Videos</a>
                    <a href="#" class="footer-link">Community</a>
                </div>
                
                <div class="col-lg-2 col-md-4">
                    <h6 class="fw-bold mb-3">Company</h6>
                    <a href="#" class="footer-link">About Us</a>
                    <a href="#" class="footer-link">Careers</a>
                    <a href="#" class="footer-link">Partners</a>
                    <a href="#" class="footer-link">Contact</a>
                </div>
                
                <div class="col-lg-2">
                    <h6 class="fw-bold mb-3">Legal</h6>
                    <a href="#" class="footer-link">Privacy Policy</a>
                    <a href="#" class="footer-link">Terms of Use</a>
                    <a href="#" class="footer-link">POPIA Compliance</a>
                    <a href="#" class="footer-link">Accessibility</a>
                </div>
            </div>
            
            <hr class="border-secondary">
            
            <div class="text-center small opacity-50">
                <p class="mb-0">&copy; 2024 Smart LMS. Made with ❤️ for South African education.</p>
            </div>
        </div>
    </footer>

    <!-- Floating AI Chat Button -->
    <button class="ai-float-btn" onclick="toggleChat()" title="Chat with AI Tutor">
        <i class="bi bi-robot"></i>
    </button>

    <!-- AI Chat Window -->
    <div class="ai-chat-window" id="aiChatWindow">
        <div class="chat-header">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-robot"></i>
                <div>
                    <h6 class="mb-0">AI Tutor</h6>
                    <small style="font-size: 0.75rem;">Online • Multilingual</small>
                </div>
            </div>
            <button class="btn-close btn-close-white" onclick="toggleChat()"></button>
        </div>
        
        <div class="chat-body" id="chatBody">
            <div class="language-selector">
                <button class="lang-btn active" onclick="setLanguage('en')">English</button>
                <button class="lang-btn" onclick="setLanguage('zu')">isiZulu</button>
                <button class="lang-btn" onclick="setLanguage('af')">Afrikaans</button>
            </div>
            
            <div class="chat-bubble bot">
                👋 Hello! I'm your AI Tutor. I can help with Mathematics, Physical Sciences, Life Sciences, and more. I explain in your preferred South African language. What would you like to learn today?
            </div>
        </div>
        
        <div class="chat-input-area">
            <input type="text" id="chatInput" class="form-control" placeholder="Ask me anything..." onkeypress="handleChatKey(event)">
            <button class="btn btn-primary rounded-circle" style="width: 40px; height: 40px;" onclick="sendChatMessage()">
                <i class="bi bi-send"></i>
            </button>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // ===== PARTICLE ANIMATION =====
    function createParticles() {
        const container = document.getElementById('particles');
        for (let i = 0; i < 30; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.animationDelay = Math.random() * 20 + 's';
            particle.style.animationDuration = (15 + Math.random() * 10) + 's';
            container.appendChild(particle);
        }
    }
    createParticles();

    // ===== TYPING EFFECT =====
    const heroTexts = [
        "Personalized education for every South African learner...",
        "AI tutoring in isiZulu, isiXhosa, Afrikaans & more...",
        "Works offline - no internet required...",
        "Identifying at-risk students before they drop out..."
    ];
    let textIndex = 0, charIndex = 0, isDeleting = false;
    const typingElement = document.getElementById('heroTyping');

    function typeEffect() {
        const currentText = heroTexts[textIndex];
        typingElement.textContent = isDeleting 
            ? currentText.substring(0, charIndex - 1)
            : currentText.substring(0, charIndex + 1);
        
        charIndex += isDeleting ? -1 : 1;
        let speed = isDeleting ? 50 : 100;
        
        if (!isDeleting && charIndex === currentText.length) {
            speed = 2000;
            isDeleting = true;
        } else if (isDeleting && charIndex === 0) {
            isDeleting = false;
            textIndex = (textIndex + 1) % heroTexts.length;
            speed = 500;
        }
        
        setTimeout(typeEffect, speed);
    }
    setTimeout(typeEffect, 1000);

    // ===== GSAP ANIMATIONS =====
    gsap.registerPlugin(ScrollTrigger);
    
    gsap.utils.toArray('.reveal').forEach(elem => {
        gsap.fromTo(elem, 
            { opacity: 0, y: 30 },
            {
                opacity: 1,
                y: 0,
                duration: 0.8,
                scrollTrigger: {
                    trigger: elem,
                    start: "top 85%",
                    toggleActions: "play none none reverse"
                }
            }
        );
    });

    // ===== CHAT TOGGLE =====
    let chatOpen = false;
    function toggleChat() {
        const chat = document.getElementById('aiChatWindow');
        const btn = document.querySelector('.ai-float-btn');
        chatOpen = !chatOpen;
        
        if (chatOpen) {
            chat.classList.add('active');
            btn.style.transform = 'scale(0)';
        } else {
            chat.classList.remove('active');
            btn.style.transform = 'scale(1)';
        }
    }

    function handleChatKey(e) {
        if (e.key === 'Enter') sendChatMessage();
    }

    function sendChatMessage() {
        const input = document.getElementById('chatInput');
        const body = document.getElementById('chatBody');
        const msg = input.value.trim();
        
        if (!msg) return;
        
        // User message
        const userBubble = document.createElement('div');
        userBubble.className = 'chat-bubble user';
        userBubble.textContent = msg;
        body.appendChild(userBubble);
        input.value = '';
        body.scrollTop = body.scrollHeight;
        
        // Typing indicator
        const typing = document.createElement('div');
        typing.className = 'chat-bubble bot';
        typing.innerHTML = '<em><i class="bi bi-three-dots"></i> Thinking...</em>';
        typing.id = 'typing';
        body.appendChild(typing);
        body.scrollTop = body.scrollHeight;
        
        // AI response simulation
        setTimeout(() => {
            document.getElementById('typing').remove();
            
            const responses = [
                "I'd be happy to explain that! Let me break it down step by step with examples relevant to South African contexts.",
                "Great question! Based on the CAPS curriculum, this concept is typically covered in Term 2. Would you like me to create some practice problems?",
                "I can help with that! Let me search for video explanations in your selected language and generate a study guide.",
                "That's a challenging topic. Let me provide multiple explanations - visual, practical, and mathematical approaches."
            ];
            
            const aiBubble = document.createElement('div');
            aiBubble.className = 'chat-bubble bot';
            aiBubble.textContent = responses[Math.floor(Math.random() * responses.length)];
            body.appendChild(aiBubble);
            body.scrollTop = body.scrollHeight;
        }, 1500);
    }

    function setLanguage(lang) {
        document.querySelectorAll('.lang-btn').forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');
        
        const greetings = {
            'en': 'Hello! I\'m your AI Tutor. How can I help you today?',
            'zu': 'Sawubona! Ngiyisifundisi sakho se-AI. Ngingakusiza kanjani namuhla?',
            'af': 'Hallo! Ek is jou KI-tutor. Hoe kan ek jou vandag help?'
        };
        
        const body = document.getElementById('chatBody');
        const bubble = document.createElement('div');
        bubble.className = 'chat-bubble bot';
        bubble.textContent = greetings[lang];
        body.appendChild(bubble);
        body.scrollTop = body.scrollHeight;
    }

    // ===== NAVBAR SCROLL =====
    window.addEventListener('scroll', () => {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 50) {
            navbar.style.background = 'rgba(15, 23, 42, 0.98)';
            navbar.style.boxShadow = '0 4px 20px rgba(0,0,0,0.1)';
        } else {
            navbar.style.background = 'rgba(15, 23, 42, 0.95)';
            navbar.style.boxShadow = 'none';
        }
    });

    // ===== SMOOTH SCROLL =====
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });
    </script>
</body>
</html>