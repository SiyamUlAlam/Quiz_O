<?php
require 'config.php';
require 'auth.php';
redirectIfNotAdmin();

// Get admin statistics
$admin_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Count total quizzes created by admin
$quiz_count_result = $conn->query("SELECT COUNT(*) as total_quizzes FROM quizzes WHERE created_by = $admin_id");
$total_quizzes = $quiz_count_result->fetch_assoc()['total_quizzes'];

// Count total questions across all admin's quizzes
$question_count_result = $conn->query("
    SELECT COUNT(*) as total_questions 
    FROM questions q 
    JOIN quizzes qz ON q.quiz_id = qz.id 
    WHERE qz.created_by = $admin_id
");
$total_questions = $question_count_result->fetch_assoc()['total_questions'];

// Count total attempts on admin's quizzes
$attempt_count_result = $conn->query("
    SELECT COUNT(*) as total_attempts 
    FROM scores s 
    JOIN quizzes qz ON s.quiz_id = qz.id 
    WHERE qz.created_by = $admin_id
");
$total_attempts = $attempt_count_result->fetch_assoc()['total_attempts'];

// Get recent quiz attempts
$recent_attempts = $conn->query("
    SELECT u.username, qz.title, s.score, s.submitted_at,
           (SELECT COUNT(*) FROM questions WHERE quiz_id = qz.id) as total_questions
    FROM scores s 
    JOIN users u ON s.user_id = u.id 
    JOIN quizzes qz ON s.quiz_id = qz.id 
    WHERE qz.created_by = $admin_id 
    ORDER BY s.submitted_at DESC 
    LIMIT 5
");

// Get admin's quizzes
$result = $conn->query("SELECT * FROM quizzes WHERE created_by = $admin_id ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Quiz Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            position: relative;
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
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 20s infinite linear;
        }

        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 60%;
            right: 15%;
            animation-delay: -5s;
        }

        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            bottom: 30%;
            left: 20%;
            animation-delay: -10s;
        }

        .shape:nth-child(4) {
            width: 100px;
            height: 100px;
            top: 10%;
            right: 30%;
            animation-delay: -15s;
        }

        .shape:nth-child(5) {
            width: 90px;
            height: 90px;
            bottom: 20%;
            right: 10%;
            animation-delay: -7s;
        }

        @keyframes float {
            0% { transform: translateY(0) rotate(0deg); opacity: 1; }
            33% { transform: translateY(-30px) rotate(120deg); opacity: 0.8; }
            66% { transform: translateY(20px) rotate(240deg); opacity: 0.6; }
            100% { transform: translateY(0) rotate(360deg); opacity: 1; }
        }

        header {
            position: relative;
            z-index: 10;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding: 1.5rem 2rem;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo h1 {
            font-size: 1.8rem;
            font-weight: 800;
            color: white;
            background: linear-gradient(45deg, #fff, #f0f9ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .logo-icon {
            font-size: 2rem;
        }

        nav {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        nav a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: 1px solid transparent;
        }

        nav a:hover, nav a.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
        }

        main {
            flex: 1;
            position: relative;
            z-index: 10;
            padding: 2rem;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .welcome-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
        }

        .welcome-title {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .welcome-subtitle {
            font-size: 1.2rem;
            color: #6b7280;
            font-weight: 500;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
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
            background: var(--card-color, linear-gradient(135deg, #667eea, #764ba2));
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1rem;
            color: #6b7280;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        .section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 4px 15px rgba(103, 126, 234, 0.3);
        }

        .btn-secondary {
            background: rgba(103, 126, 234, 0.1);
            color: #667eea;
            border: 1px solid rgba(103, 126, 234, 0.2);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .quiz-grid {
            display: grid;
            gap: 1rem;
        }

        .quiz-card {
            background: linear-gradient(135deg, rgba(103, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
            border: 1px solid rgba(103, 126, 234, 0.1);
            border-radius: 12px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            position: relative;
        }

        .quiz-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border-color: rgba(103, 126, 234, 0.2);
        }

        .quiz-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .quiz-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .quiz-date {
            font-size: 0.85rem;
            color: #6b7280;
        }

        .quiz-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-small {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
            border-radius: 8px;
        }

        .recent-attempts {
            display: grid;
            gap: 1rem;
        }

        .attempt-item {
            background: linear-gradient(135deg, rgba(168, 237, 234, 0.1), rgba(254, 214, 227, 0.1));
            border: 1px solid rgba(168, 237, 234, 0.2);
            border-radius: 10px;
            padding: 1rem;
        }

        .attempt-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .attempt-user {
            font-weight: 600;
            color: #1f2937;
        }

        .attempt-score {
            font-weight: 700;
            color: #059669;
        }

        .attempt-quiz {
            font-size: 0.9rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }

        .attempt-date {
            font-size: 0.8rem;
            color: #9ca3af;
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
        }

        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        footer {
            position: relative;
            z-index: 10;
            background: rgba(31, 41, 55, 0.9);
            backdrop-filter: blur(20px);
            color: white;
            padding: 2rem;
            text-align: center;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 2rem;
            align-items: center;
        }

        .footer-info h4 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #f3f4f6;
        }

        .footer-info p {
            font-size: 0.9rem;
            color: #d1d5db;
            margin: 0.2rem 0;
        }

        .footer-copyright {
            text-align: right;
        }

        .footer-copyright p {
            font-size: 0.85rem;
            color: #9ca3af;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }

            nav {
                gap: 1rem;
            }

            .content-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }

            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 1rem;
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
        <div class="shape"></div>
    </div>

    <header>
        <div class="header-content">
            <div class="logo">
                <span class="logo-icon">🎯</span>
                <h1>Quiz Admin</h1>
            </div>
            <nav>
                <a href="admin_dashboard.php" class="active">📊 Dashboard</a>
                <a href="create_quiz.php">➕ Create Quiz</a>
                <a href="view_scores.php">📈 View Scores</a>
                <a href="logout.php">🚪 Logout</a>
            </nav>
            <div class="user-info">
                <span>👨‍💼</span>
                <span><?= htmlspecialchars($username) ?></span>
            </div>
        </div>
    </header>

    <main>
        <div class="dashboard-container">
            <div class="welcome-section">
                <h1 class="welcome-title">Welcome back, <?= htmlspecialchars($username) ?>!</h1>
                <p class="welcome-subtitle">Manage your quizzes and track student performance from your admin dashboard</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card" style="--card-color: linear-gradient(135deg, #667eea, #764ba2);">
                    <div class="stat-icon">📚</div>
                    <div class="stat-value"><?= $total_quizzes ?></div>
                    <div class="stat-label">Total Quizzes</div>
                </div>
                <div class="stat-card" style="--card-color: linear-gradient(135deg, #f093fb, #f5576c);">
                    <div class="stat-icon">❓</div>
                    <div class="stat-value"><?= $total_questions ?></div>
                    <div class="stat-label">Total Questions</div>
                </div>
                <div class="stat-card" style="--card-color: linear-gradient(135deg, #4facfe, #00f2fe);">
                    <div class="stat-icon">📝</div>
                    <div class="stat-value"><?= $total_attempts ?></div>
                    <div class="stat-label">Quiz Attempts</div>
                </div>
            </div>

            <div class="content-grid">
                <div class="section">
                    <div class="section-header">
                        <h2 class="section-title">📚 Your Quizzes</h2>
                        <a href="create_quiz.php" class="btn btn-primary">
                            ➕ Create New Quiz
                        </a>
                    </div>
                    
                    <div class="quiz-grid">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <div class="quiz-card">
                                    <h3 class="quiz-title"><?= htmlspecialchars($row['title']) ?></h3>
                                    <div class="quiz-meta">
                                        <span class="quiz-date">
                                            📅 Created: <?= date('M j, Y', strtotime($row['created_at'])) ?>
                                        </span>
                                    </div>
                                    <div class="quiz-actions">
                                        <a href="add_question.php?quiz_id=<?= $row['id'] ?>" class="btn btn-secondary btn-small">
                                            ❓ Add Questions
                                        </a>
                                        <a href="view_scores.php?quiz_id=<?= $row['id'] ?>" class="btn btn-secondary btn-small">
                                            📊 View Results
                                        </a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">📚</div>
                                <h3>No quizzes yet</h3>
                                <p>Create your first quiz to get started!</p>
                                <a href="create_quiz.php" class="btn btn-primary" style="margin-top: 1rem;">
                                    ➕ Create First Quiz
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="section">
                    <div class="section-header">
                        <h2 class="section-title">🎯 Recent Activity</h2>
                        <a href="view_scores.php" class="btn btn-secondary btn-small">
                            View All
                        </a>
                    </div>
                    
                    <div class="recent-attempts">
                        <?php if ($recent_attempts->num_rows > 0): ?>
                            <?php while ($attempt = $recent_attempts->fetch_assoc()): ?>
                                <div class="attempt-item">
                                    <div class="attempt-header">
                                        <span class="attempt-user">👤 <?= htmlspecialchars($attempt['username']) ?></span>
                                        <span class="attempt-score">
                                            <?= $attempt['score'] ?>/<?= $attempt['total_questions'] ?>
                                        </span>
                                    </div>
                                    <div class="attempt-quiz">📝 <?= htmlspecialchars($attempt['title']) ?></div>
                                    <div class="attempt-date">
                                        🕒 <?= date('M j, Y g:i A', strtotime($attempt['submitted_at'])) ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">🎯</div>
                                <p>No recent quiz attempts</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
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
        // Add entrance animations
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stat-card, .quiz-card, .attempt-item');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease-out';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Add scroll animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animation = 'fadeInUp 0.6s ease-out';
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.section').forEach(section => {
                observer.observe(section);
            });
        });

        // Add keyframe animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeInUp {
                from { opacity: 0; transform: translateY(30px); }
                to { opacity: 1; transform: translateY(0); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
