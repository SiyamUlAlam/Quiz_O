<?php
require 'config.php';
session_start();
// DEBUGGING - REMOVE IN PRODUCTION
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Check session
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// Logged-in user data
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
// Query quizzes
$result = $conn->query("SELECT * FROM quizzes ORDER BY id DESC");
if (!$result) {
    die("Query error: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Quiz App</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #667eea, #764ba2);
            min-height: 100vh;
            margin: 0;
            position: relative;
            overflow-x: hidden;
        }

        /* Floating background shapes */
        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            animation: float 12s ease-in-out infinite;
        }

        .shape:nth-child(1) {
            width: 150px;
            height: 150px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 200px;
            height: 200px;
            top: 70%;
            right: 5%;
            animation-delay: 4s;
        }

        .shape:nth-child(3) {
            width: 100px;
            height: 100px;
            bottom: 20%;
            left: 70%;
            animation-delay: 8s;
        }

        .shape:nth-child(4) {
            width: 120px;
            height: 120px;
            top: 40%;
            left: 5%;
            animation-delay: 2s;
        }

        header {
            background: linear-gradient(135deg, #1a1a40, #2d2d5f);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
            z-index: 10;
        }

        header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            animation: shimmer 4s ease-in-out infinite;
        }

        header h1 {
            font-size: 3rem;
            font-weight: 700;
            letter-spacing: -0.025em;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .user-badge {
            background: rgba(255, 255, 255, 0.15);
            padding: 1rem 2rem;
            border-radius: 50px;
            display: inline-block;
            margin-top: 1rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 1;
        }

        .user-badge::before {
            content: '👋';
            margin-right: 0.5rem;
            font-size: 1.2rem;
        }

        .nav-bar {
            background: rgba(255, 255, 255, 0.1);
            padding: 1rem 0;
            text-align: center;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .nav-links {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            font-weight: 500;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        main {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 2rem;
            position: relative;
            z-index: 10;
        }

        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 20px 20px 0 0;
        }

        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        .stat-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1a1a40;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #64748b;
            font-size: 1rem;
            font-weight: 500;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 2rem;
            text-align: center;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .quizzes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }

        .quiz-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
        }

        .quiz-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .quiz-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        .quiz-header {
            padding: 2rem;
            text-align: center;
            position: relative;
        }

        .quiz-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }

        .quiz-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1a1a40;
            margin-bottom: 1rem;
            line-height: 1.4;
        }

        .quiz-actions {
            padding: 1.5rem 2rem;
            background: rgba(102, 126, 234, 0.05);
            text-align: center;
        }

        .quiz-btn {
            display: inline-block;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .quiz-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.6s ease;
        }

        .quiz-btn:hover::before {
            left: 100%;
        }

        .quiz-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .no-quizzes {
            background: rgba(255, 255, 255, 0.95);
            padding: 4rem 2rem;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            margin-top: 2rem;
        }

        .no-quizzes-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .no-quizzes-text {
            font-size: 1.5rem;
            color: #64748b;
            font-weight: 500;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-top: 3rem;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 1rem 2rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logout-btn {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
        }

        .scores-btn {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            color: white;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        footer {
            background: linear-gradient(135deg, #1a1a40, #2d2d5f);
            color: white;
            padding: 2rem;
            text-align: center;
            margin-top: 4rem;
            position: relative;
            z-index: 10;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .footer-info {
            text-align: left;
        }

        .footer-info h4 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.3rem;
            color: #a777e3;
        }

        .footer-info p {
            font-size: 0.85rem;
            opacity: 0.9;
            margin: 0.2rem 0;
        }

        .footer-copyright {
            text-align: right;
            font-size: 0.85rem;
            opacity: 0.8;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-50px) rotate(180deg); }
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%) rotate(45deg); }
            100% { transform: translateX(100%) rotate(45deg); }
        }

        @media (max-width: 768px) {
            header h1 {
                font-size: 2rem;
            }

            main {
                margin: 2rem 1rem;
                padding: 1.5rem;
            }

            .quizzes-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-stats {
                grid-template-columns: 1fr;
            }

            .nav-links {
                gap: 1rem;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }

            .footer-content {
                flex-direction: column;
                text-align: center;
                gap: 1.5rem;
            }

            .footer-info {
                text-align: center;
            }

            .footer-copyright {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Floating background shapes -->
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <header>
        <h1>Welcome to Your Dashboard</h1>
        <div class="user-badge">
            Hello, <?= htmlspecialchars($username) ?>!
        </div>
    </header>

    <nav class="nav-bar">
        <div class="nav-links">
            <a href="index.php" class="nav-link">🏠 Home</a>
            <a href="view_scores.php" class="nav-link">📊 My Scores</a>
            <a href="#quizzes" class="nav-link">🧠 Quizzes</a>
        </div>
    </nav>

    <main>
        <!-- Dashboard Statistics -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <span class="stat-icon">🧠</span>
                <div class="stat-number"><?= $result->num_rows ?></div>
                <div class="stat-label">Available Quizzes</div>
            </div>
            <div class="stat-card">
                <span class="stat-icon">⚡</span>
                <div class="stat-number">Ready</div>
                <div class="stat-label">Your Status</div>
            </div>
            <div class="stat-card">
                <span class="stat-icon">🎯</span>
                <div class="stat-number">∞</div>
                <div class="stat-label">Learning Potential</div>
            </div>
        </div>

        <h2 class="section-title" id="quizzes">🚀 Available Quizzes</h2>
        
        <?php if ($result->num_rows > 0): ?>
            <div class="quizzes-grid">
                <?php 
                $icons = ['🧮', '🔬', '📚', '🌍', '💡', '🎨', '⚗️', '📖', '🔍', '🎭'];
                $iconIndex = 0;
                $result->data_seek(0); // Reset result pointer
                while ($row = $result->fetch_assoc()): 
                ?>
                    <div class="quiz-card">
                        <div class="quiz-header">
                            <span class="quiz-icon"><?= $icons[$iconIndex % count($icons)] ?></span>
                            <h3 class="quiz-title"><?= htmlspecialchars($row['title']) ?></h3>
                        </div>
                        <div class="quiz-actions">
                            <a href="quiz.php?id=<?= $row['id'] ?>" class="quiz-btn">
                                🎯 Start Quiz
                            </a>
                        </div>
                    </div>
                <?php 
                $iconIndex++;
                endwhile; 
                ?>
            </div>
        <?php else: ?>
            <div class="no-quizzes">
                <div class="no-quizzes-icon">📝</div>
                <p class="no-quizzes-text">No quizzes available at the moment</p>
                <p style="color: #94a3b8; margin-top: 1rem;">Check back later for new challenges!</p>
            </div>
        <?php endif; ?>

        <div class="action-buttons">
            <a href="view_scores.php" class="action-btn scores-btn">
                📊 View My Scores
            </a>
            <a href="logout.php" class="action-btn logout-btn">
                🚪 Logout
            </a>
        </div>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-info">
                <h4>Developed by</h4>
                <p><strong>Siyam Ul Alam</strong></p>
                <p>Gazipur Digital University</p>
                <p>📧 2002032@icte.bdu.ac.bd</p>
            </div>
            <div class="footer-copyright">
                <p>&copy; <?= date("Y") ?> Quiz App. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add loading state to quiz buttons
        document.querySelectorAll('.quiz-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                this.innerHTML = '⏳ Loading...';
                this.style.opacity = '0.7';
            });
        });

        // Animate stats on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'slideInUp 0.6s ease-out';
                }
            });
        }, observerOptions);

        // Observe all cards for animation
        document.querySelectorAll('.stat-card, .quiz-card').forEach(card => {
            observer.observe(card);
        });

        // Welcome animation
        window.addEventListener('load', function() {
            const header = document.querySelector('header');
            header.style.animation = 'slideInDown 0.8s ease-out';
        });
    </script>

    <style>
        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideInDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</body>
</html>
