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
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f8;
        }

        header {
            background-color: #2c3e50;
            color: #fff;
            padding: 16px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        header h1 {
            margin: 0;
            font-size: 22px;
        }

        nav a {
            color: #ecf0f1;
            text-decoration: none;
            margin-left: 20px;
            font-weight: 500;
        }

        nav a:hover {
            text-decoration: underline;
        }

        .container {
            max-width: 960px;
            margin: 30px auto;
            background-color: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        h2 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 10px;
        }

        h3 {
            color: #34495e;
            font-size: 22px;
            margin-top: 30px;
        }

        a {
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
        }

        a:hover {
            text-decoration: underline;
        }

        .quiz-list {
            list-style: none;
            padding: 0;
        }

        .quiz-list li {
            background: #fff;
            border: 1px solid #ddd;
            margin-bottom: 10px;
            padding: 12px 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .actions a {
            margin-left: 10px;
            font-size: 14px;
            color: #2980b9;
        }

        .top-links {
            margin-top: 20px;
        }

        .top-links a {
            display: inline-block;
            background: #2980b9;
            color: #fff;
            padding: 8px 14px;
            margin-right: 10px;
            border-radius: 6px;
            transition: background 0.2s ease;
        }

        .top-links a:hover {
            background: #1c5980;
        }

        footer {
            text-align: center;
            padding: 16px;
            background-color: #ecf0f1;
            color: #555;
            margin-top: 40px;
            font-size: 14px;
            border-top: 1px solid #ddd;
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
    &copy; <?= date('Y') ?> Quiz App. All rights reserved.
</footer>

</body>
</html>
