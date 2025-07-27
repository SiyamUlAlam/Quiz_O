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
    <title>User Dashboard</title>
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
            margin: 0;
        }

        header {
            background: #1a1a40;
            color: white;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        header h1 {
            font-size: 2.25rem;
            font-weight: 700;
            letter-spacing: -0.025em;
        }

        main {
            max-width: 900px;
            margin: 2rem auto;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        main:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        h2 {
            font-size: 1.75rem;
            font-weight: 600;
            color: #1a1a40;
            margin-bottom: 1.5rem;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        li {
            margin: 0.75rem 0;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 8px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        li:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .quiz-link {
            text-decoration: none;
            color: #6e8efb;
            font-weight: 600;
            font-size: 1.1rem;
            transition: color 0.3s ease;
        }

        .quiz-link:hover {
            color: #a777e3;
        }

        .no-quizzes {
            color: #94a3b8;
            font-size: 1rem;
            text-align: center;
            padding: 1rem;
        }

        .logout-btn {
            display: inline-block;
            margin-top: 1.5rem;
            padding: 0.9rem 1.5rem;
            background: linear-gradient(90deg, #e63946, #f9844a);
            color: white;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 600;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .logout-btn:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .logout-btn:active {
            transform: scale(0.98);
        }

        footer {
            background: #1a1a40;
            color: white;
            padding: 1.5rem;
            text-align: center;
            font-size: 0.9rem;
            position: relative;
            bottom: 0;
            width: 100%;
        }

        @media (max-width: 600px) {
            main {
                margin: 1rem;
                padding: 1.5rem;
            }

            header h1 {
                font-size: 1.75rem;
            }

            h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Welcome, <?= htmlspecialchars($username) ?>!</h1>
    </header>
    <main>
        <h2>Available Quizzes</h2>
        <?php if ($result->num_rows > 0): ?>
            <ul>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <li>
                        <a class="quiz-link" href="quiz.php?id=<?= $row['id'] ?>">
                            <?= htmlspecialchars($row['title']) ?>
                        </a>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p class="no-quizzes">No quizzes available right now.</p>
        <?php endif; ?>
        <a href="logout.php" class="logout-btn">Logout</a>
    </main>
    <footer>
        &copy; <?= date("Y") ?> Quiz App by Siyam Ul Alam
    </footer>
</body>
</html>
