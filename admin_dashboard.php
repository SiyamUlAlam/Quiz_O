<?php
require 'config.php';
require 'auth.php';
redirectIfNotAdmin();
$result = $conn->query("SELECT * FROM quizzes WHERE created_by = {$_SESSION['user_id']}");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #6e8efb, #a777e3);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            background: #1a1a40;
            color: white;
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            letter-spacing: -0.025em;
        }

        nav a {
            color: #ffffff;
            text-decoration: none;
            margin-left: 1.5rem;
            font-weight: 500;
            font-size: 1rem;
            transition: color 0.3s ease;
        }

        nav a:hover {
            color: #a777e3;
        }

        .container {
            max-width: 960px;
            margin: 2rem auto;
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .container:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #1a1a40;
            margin-bottom: 1rem;
        }

        h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1a1a40;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
        }

        .top-links {
            margin-bottom: 1.5rem;
            display: flex;
            gap: 1rem;
        }

        .top-links a {
            display: inline-block;
            padding: 0.75rem 1.25rem;
            background: linear-gradient(90deg, #6e8efb, #a777e3);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .top-links a:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .top-links a:active {
            transform: scale(0.98);
        }

        .quiz-list {
            list-style: none;
            padding: 0;
        }

        .quiz-list li {
            background: #f8fafc;
            padding: 1rem;
            margin-bottom: 0.75rem;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .quiz-list li:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .quiz-list span {
            font-size: 1.1rem;
            font-weight: 500;
            color: #1a1a40;
        }

        .actions a {
            color: #6e8efb;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            margin-left: 1rem;
            transition: color 0.3s ease;
        }

        .actions a:hover {
            color: #a777e3;
        }

        footer {
            margin-top: auto;
            padding: 1.5rem;
            background: #1a1a40;
            color: white;
            text-align: center;
            font-size: 0.9rem;
            width: 100%;
        }

        @media (max-width: 600px) {
            .container {
                margin: 1rem;
                padding: 1.5rem;
            }

            header h1 {
                font-size: 1.5rem;
            }

            nav a {
                margin-left: 1rem;
                font-size: 0.9rem;
            }

            h2 {
                font-size: 1.75rem;
            }

            h3 {
                font-size: 1.25rem;
            }

            .top-links {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Quiz App</h1>
        <nav>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="create_quiz.php">Create Quiz</a>
            <a href="view_scores.php">View Scores</a>
            <a href="logout.php">Logout (<?= htmlspecialchars($_SESSION['username']) ?>)</a>
        </nav>
    </header>
    <div class="container">
        <h2>Welcome Admin <?= htmlspecialchars($_SESSION['username']) ?></h2>
        <div class="top-links">
            <a href="create_quiz.php">+ Create New Quiz</a>
            <a href="view_scores.php">View All Scores</a>
            <a href="logout.php">Logout</a>
        </div>
        <h3>Your Quizzes</h3>
        <ul class="quiz-list">
            <?php while ($row = $result->fetch_assoc()): ?>
                <li>
                    <span><?= htmlspecialchars($row['title']) ?></span>
                    <span class="actions">
                        <a href="add_question.php?quiz_id=<?= $row['id'] ?>">Add Questions</a>
                    </span>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
    <footer>
        &copy; <?= date('Y') ?> Quiz App by Siyam Ul Alam. All rights reserved.
    </footer>
</body>
</html>
