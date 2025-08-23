<?php
require 'config.php';
session_start();
// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$quiz_id = $_GET['quiz_id'] ?? 0;
if (!$quiz_id || !is_numeric($quiz_id)) {
    echo "Invalid quiz ID.";
    exit();
}

// Fetch the score and quiz details
$result = $conn->query("SELECT s.score, q.title, 
    (SELECT COUNT(*) FROM questions WHERE quiz_id = $quiz_id) as total_questions
    FROM scores s 
    JOIN quizzes q ON s.quiz_id = q.id 
    WHERE s.user_id = $user_id AND s.quiz_id = $quiz_id");

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $score = $row['score'];
    $quiz_title = $row['title'];
    $total_questions = $row['total_questions'];
    $percentage = round(($score / $total_questions) * 100, 1);
} else {
    $score = "Not Found";
    $quiz_title = "Unknown Quiz";
    $total_questions = 0;
    $percentage = 0;
}

// Determine performance level and message
$performance_level = '';
$performance_message = '';
$performance_icon = '';
$performance_color = '';

if ($percentage >= 90) {
    $performance_level = 'Excellent!';
    $performance_message = 'Outstanding performance! You\'re a quiz champion! 🏆';
    $performance_icon = '🌟';
    $performance_color = '#10b981';
} elseif ($percentage >= 75) {
    $performance_level = 'Great Job!';
    $performance_message = 'Well done! You have a solid understanding of the topic. 👏';
    $performance_icon = '🎯';
    $performance_color = '#3b82f6';
} elseif ($percentage >= 60) {
    $performance_level = 'Good Work!';
    $performance_message = 'Nice effort! There\'s room for improvement, but you\'re on the right track. 📚';
    $performance_icon = '👍';
    $performance_color = '#f59e0b';
} elseif ($percentage >= 40) {
    $performance_level = 'Keep Learning!';
    $performance_message = 'Don\'t give up! Review the material and try again. You can do it! 💪';
    $performance_icon = '📖';
    $performance_color = '#ef4444';
} else {
    $performance_level = 'Study More!';
    $performance_message = 'This is a learning opportunity! Review and practice more. 🎓';
    $performance_icon = '📝';
    $performance_color = '#6b7280';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results - <?= htmlspecialchars($username) ?></title>
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
            text-align: center;
        }

        .header-content h1 {
            font-size: 2.5rem;
            font-weight: 800;
            color: white;
            margin-bottom: 0.5rem;
            background: linear-gradient(45deg, #fff, #f0f9ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header-subtitle {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
        }

        main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            position: relative;
            z-index: 10;
        }

        .result-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
            padding: 3rem;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideUp 0.8s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .celebration-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            animation: bounce 2s ease-in-out infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-20px); }
            60% { transform: translateY(-10px); }
        }

        .performance-level {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .score-display {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 2rem 0;
            gap: 2rem;
        }

        .score-circle {
            position: relative;
            width: 180px;
            height: 180px;
            margin: 0 auto;
        }

        .score-progress {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: conic-gradient(
                var(--performance-color, #3b82f6) 0deg,
                var(--performance-color, #3b82f6) calc(var(--percentage, 0) * 3.6deg),
                #e5e7eb calc(var(--percentage, 0) * 3.6deg),
                #e5e7eb 360deg
            );
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            animation: fillProgress 2s ease-out;
        }

        @keyframes fillProgress {
            from { background: conic-gradient(#e5e7eb 0deg, #e5e7eb 360deg); }
        }

        .score-progress::before {
            content: '';
            position: absolute;
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: white;
        }

        .score-text {
            position: absolute;
            z-index: 10;
            text-align: center;
        }

        .score-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: #1f2937;
            line-height: 1;
        }

        .score-fraction {
            font-size: 1.2rem;
            color: #6b7280;
            font-weight: 600;
        }

        .score-percentage {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--performance-color, #3b82f6);
            margin-top: 0.5rem;
        }

        .quiz-details {
            background: linear-gradient(135deg, rgba(103, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            border-radius: 16px;
            padding: 1.5rem;
            margin: 2rem 0;
            border: 1px solid rgba(103, 126, 234, 0.2);
        }

        .quiz-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .quiz-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .stat-item {
            text-align: center;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: #1f2937;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #6b7280;
            font-weight: 500;
        }

        .performance-message {
            font-size: 1.2rem;
            color: #4b5563;
            font-weight: 500;
            margin: 2rem 0;
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 16px;
            border-left: 4px solid var(--performance-color, #3b82f6);
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
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
            background: rgba(255, 255, 255, 0.9);
            color: #374151;
            border: 2px solid rgba(103, 126, 234, 0.3);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .btn:active {
            transform: translateY(-1px);
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
            .result-container {
                padding: 2rem;
                margin: 1rem;
            }

            .score-display {
                flex-direction: column;
                gap: 1rem;
            }

            .score-circle {
                width: 150px;
                height: 150px;
            }

            .score-progress::before {
                width: 120px;
                height: 120px;
            }

            .score-number {
                font-size: 2rem;
            }

            .action-buttons {
                flex-direction: column;
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
<body style="--performance-color: <?= $performance_color ?>; --percentage: <?= $percentage ?>">
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
            <h1>🎊 Quiz Results</h1>
            <p class="header-subtitle">See how you performed, <?= htmlspecialchars($username) ?>!</p>
        </div>
    </header>

    <main>
        <div class="result-container">
            <div class="celebration-icon"><?= $performance_icon ?></div>
            <h2 class="performance-level"><?= $performance_level ?></h2>
            
            <div class="score-display">
                <div class="score-circle">
                    <div class="score-progress">
                        <div class="score-text">
                            <div class="score-number"><?= $score ?></div>
                            <div class="score-fraction">/ <?= $total_questions ?></div>
                            <div class="score-percentage"><?= $percentage ?>%</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="quiz-details">
                <h3 class="quiz-title"><?= htmlspecialchars($quiz_title) ?></h3>
                <div class="quiz-stats">
                    <div class="stat-item">
                        <div class="stat-value"><?= $score ?></div>
                        <div class="stat-label">Correct</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= $total_questions - $score ?></div>
                        <div class="stat-label">Incorrect</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= $total_questions ?></div>
                        <div class="stat-label">Total</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= $percentage ?>%</div>
                        <div class="stat-label">Accuracy</div>
                    </div>
                </div>
            </div>

            <div class="performance-message">
                <?= $performance_message ?>
            </div>

            <div class="action-buttons">
                <a href="user_dashboard.php" class="btn btn-primary">
                    🏠 Back to Dashboard
                </a>
                <a href="view_scores.php" class="btn btn-secondary">
                    📊 View All Scores
                </a>
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
        // Add entrance animation
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.querySelector('.result-container');
            container.style.animation = 'slideUp 0.8s ease-out';
            
            // Animate score progress
            setTimeout(() => {
                const progress = document.querySelector('.score-progress');
                progress.style.animation = 'fillProgress 2s ease-out';
            }, 500);

            // Add confetti effect for high scores
            const percentage = <?= $percentage ?>;
            if (percentage >= 75) {
                createConfetti();
            }
        });

        function createConfetti() {
            const colors = ['#667eea', '#764ba2', '#ff6b6b', '#4ecdc4', '#45b7d1', '#96ceb4', '#feca57'];
            const confettiCount = 50;
            
            for (let i = 0; i < confettiCount; i++) {
                createConfettiPiece(colors[Math.floor(Math.random() * colors.length)]);
            }
        }

        function createConfettiPiece(color) {
            const confetti = document.createElement('div');
            confetti.style.position = 'fixed';
            confetti.style.left = Math.random() * 100 + 'vw';
            confetti.style.top = '-10px';
            confetti.style.width = '10px';
            confetti.style.height = '10px';
            confetti.style.backgroundColor = color;
            confetti.style.borderRadius = '50%';
            confetti.style.pointerEvents = 'none';
            confetti.style.zIndex = '1000';
            confetti.style.animation = `fall ${Math.random() * 3 + 2}s linear forwards`;
            
            document.body.appendChild(confetti);
            
            setTimeout(() => {
                confetti.remove();
            }, 5000);
        }

        // Add fall animation for confetti
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fall {
                to {
                    transform: translateY(100vh) rotate(360deg);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
