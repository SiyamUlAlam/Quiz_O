<?php
require 'config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Fetch scores based on user role
if ($is_admin) {
    // Admin can see all scores
    $query = "
        SELECT u.username, q.title, s.score, s.created_at
        FROM scores s
        JOIN users u ON s.user_id = u.id
        JOIN quizzes q ON s.quiz_id = q.id
        ORDER BY s.created_at DESC, q.title, u.username
    ";
    $result = $conn->query($query);
    $page_title = "All Quiz Scores";
} else {
    // Regular users can only see their own scores
    $stmt = $conn->prepare("
        SELECT q.title, s.score, s.created_at
        FROM scores s
        JOIN quizzes q ON s.quiz_id = q.id
        WHERE s.user_id = ?
        ORDER BY s.created_at DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $page_title = "My Quiz Scores";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Quiz App</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            min-height: 100vh;
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
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 15s ease-in-out infinite;
        }

        .shape:nth-child(1) {
            width: 120px;
            height: 120px;
            top: 20%;
            left: 15%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 180px;
            height: 180px;
            top: 60%;
            right: 20%;
            animation-delay: 5s;
        }

        .shape:nth-child(3) {
            width: 90px;
            height: 90px;
            bottom: 30%;
            left: 70%;
            animation-delay: 10s;
        }

        .shape:nth-child(4) {
            width: 150px;
            height: 150px;
            top: 10%;
            right: 10%;
            animation-delay: 3s;
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
            content: '📊';
            margin-right: 0.5rem;
            font-size: 1.2rem;
        }

        main {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 2rem;
            position: relative;
            z-index: 10;
        }

        .stats-section {
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
            background: linear-gradient(90deg, #4facfe, #00f2fe);
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

        .scores-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
        }

        .scores-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #4facfe, #00f2fe);
        }

        .scores-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
        }

        .table-header {
            padding: 2rem;
            background: rgba(79, 172, 254, 0.1);
            border-bottom: 1px solid rgba(79, 172, 254, 0.2);
        }

        .table-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1a1a40;
            margin: 0;
        }

        .scores-table {
            width: 100%;
            border-collapse: collapse;
        }

        .scores-table th,
        .scores-table td {
            padding: 1.5rem;
            text-align: left;
            border-bottom: 1px solid rgba(79, 172, 254, 0.1);
        }

        .scores-table th {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            color: white;
            font-weight: 600;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .scores-table td {
            color: #1a1a40;
            font-size: 1rem;
            transition: background 0.3s ease;
        }

        .scores-table tr:hover td {
            background: rgba(79, 172, 254, 0.05);
        }

        .score-value {
            font-weight: 700;
            font-size: 1.2rem;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            display: inline-block;
            min-width: 60px;
            text-align: center;
        }

        .score-excellent {
            background: linear-gradient(135deg, #00b894, #00cec9);
            color: white;
        }

        .score-good {
            background: linear-gradient(135deg, #fdcb6e, #e17055);
            color: white;
        }

        .score-average {
            background: linear-gradient(135deg, #fd79a8, #fdcb6e);
            color: white;
        }

        .score-poor {
            background: linear-gradient(135deg, #d63031, #e17055);
            color: white;
        }

        .no-scores {
            padding: 4rem 2rem;
            text-align: center;
        }

        .no-scores-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .no-scores-text {
            font-size: 1.5rem;
            color: #64748b;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .no-scores-subtitle {
            color: #94a3b8;
            font-size: 1rem;
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
            position: relative;
            overflow: hidden;
        }

        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.6s ease;
        }

        .action-btn:hover::before {
            left: 100%;
        }

        .dashboard-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .quiz-btn {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            color: white;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .date-badge {
            background: rgba(79, 172, 254, 0.1);
            color: #4facfe;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 500;
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
            50% { transform: translateY(-60px) rotate(180deg); }
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

            .stats-section {
                grid-template-columns: 1fr;
            }

            .scores-table th,
            .scores-table td {
                padding: 1rem;
                font-size: 0.9rem;
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
        <h1><?= $page_title ?></h1>
        <div class="user-badge">
            <?= $is_admin ? 'Admin View' : 'Welcome, ' . htmlspecialchars($username) ?>
        </div>
    </header>

    <main>
        <!-- Statistics Section -->
        <?php
        $total_scores = $result->num_rows;
        if (!$is_admin) {
            // Calculate user-specific stats
            $stmt_avg = $conn->prepare("SELECT AVG(score) as avg_score, MAX(score) as best_score FROM scores WHERE user_id = ?");
            $stmt_avg->bind_param("i", $user_id);
            $stmt_avg->execute();
            $stats = $stmt_avg->get_result()->fetch_assoc();
            $avg_score = round($stats['avg_score'] ?? 0, 1);
            $best_score = $stats['best_score'] ?? 0;
        } else {
            // Calculate overall stats for admin
            $avg_query = $conn->query("SELECT AVG(score) as avg_score, MAX(score) as best_score FROM scores");
            $stats = $avg_query->fetch_assoc();
            $avg_score = round($stats['avg_score'] ?? 0, 1);
            $best_score = $stats['best_score'] ?? 0;
        }
        ?>

        <div class="stats-section">
            <div class="stat-card">
                <span class="stat-icon">📊</span>
                <div class="stat-number"><?= $total_scores ?></div>
                <div class="stat-label"><?= $is_admin ? 'Total Scores' : 'Quiz Attempts' ?></div>
            </div>
            <div class="stat-card">
                <span class="stat-icon">⭐</span>
                <div class="stat-number"><?= $best_score ?>%</div>
                <div class="stat-label"><?= $is_admin ? 'Highest Score' : 'Best Score' ?></div>
            </div>
            <div class="stat-card">
                <span class="stat-icon">📈</span>
                <div class="stat-number"><?= $avg_score ?>%</div>
                <div class="stat-label">Average Score</div>
            </div>
        </div>

        <!-- Scores Table -->
        <div class="scores-container">
            <div class="table-header">
                <h2 class="table-title">Score History</h2>
            </div>
            
            <?php if ($result->num_rows > 0): ?>
                <table class="scores-table">
                    <thead>
                        <tr>
                            <?php if ($is_admin): ?>
                                <th>👤 User</th>
                            <?php endif; ?>
                            <th>📚 Quiz</th>
                            <th>🎯 Score</th>
                            <th>📅 Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <?php if ($is_admin): ?>
                                    <td><?= htmlspecialchars($row['username']) ?></td>
                                <?php endif; ?>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td>
                                    <?php
                                    $score = $row['score'];
                                    $scoreClass = '';
                                    if ($score >= 90) $scoreClass = 'score-excellent';
                                    elseif ($score >= 75) $scoreClass = 'score-good';
                                    elseif ($score >= 60) $scoreClass = 'score-average';
                                    else $scoreClass = 'score-poor';
                                    ?>
                                    <span class="score-value <?= $scoreClass ?>"><?= $score ?>%</span>
                                </td>
                                <td>
                                    <span class="date-badge">
                                        <?= date('M j, Y', strtotime($row['created_at'])) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-scores">
                    <div class="no-scores-icon">📈</div>
                    <p class="no-scores-text"><?= $is_admin ? 'No scores recorded yet' : 'You haven\'t taken any quizzes yet' ?></p>
                    <p class="no-scores-subtitle"><?= $is_admin ? 'Scores will appear here once users start taking quizzes' : 'Start taking quizzes to see your scores here!' ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <?php if ($is_admin): ?>
                <a href="admin_dashboard.php" class="action-btn dashboard-btn">
                    ⚙️ Admin Dashboard
                </a>
            <?php else: ?>
                <a href="user_dashboard.php" class="action-btn dashboard-btn">
                    🏠 Dashboard
                </a>
                <a href="user_dashboard.php#quizzes" class="action-btn quiz-btn">
                    🧠 Take More Quizzes
                </a>
            <?php endif; ?>
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
        // Animate cards on scroll
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
        document.querySelectorAll('.stat-card, .scores-container').forEach(card => {
            observer.observe(card);
        });

        // Add loading state to action buttons
        document.querySelectorAll('.action-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const originalText = this.innerHTML;
                this.innerHTML = '⏳ Loading...';
                this.style.opacity = '0.7';
                
                // Restore original text if navigation fails
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.style.opacity = '1';
                }, 3000);
            });
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
