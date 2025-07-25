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

$result = $conn->query("SELECT * FROM quizzes ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background: #f0f2f5;
        }
        header, footer {
            background: #2c3e50;
            color: white;
            padding: 15px 30px;
            text-align: center;
        }
        main {
            padding: 30px;
            max-width: 800px;
            margin: auto;
        }
        h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        li {
            background: #fff;
            margin-bottom: 10px;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        a.quiz-link {
            text-decoration: none;
            color: #2980b9;
            font-weight: 500;
            font-size: 16px;
        }
        a.quiz-link:hover {
            text-decoration: underline;
        }
        .logout-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 18px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
        }
        .logout-btn:hover {
            background: #c0392b;
        }
    </style>
</head>
<body>

<header>
    <h1>Welcome, <?= htmlspecialchars($username) ?>!</h1>
</header>

<main>
    <h2>Available Quizzes</h2>
    <ul>
        <?php while ($row = $result->fetch_assoc()): ?>
            <li>
                <a class="quiz-link" href="quiz.php?id=<?= $row['id'] ?>">
                    <?= htmlspecialchars($row['title']) ?>
                </a>
            </li>
        <?php endwhile; ?>
    </ul>

    <a href="logout.php" class="logout-btn">Logout</a>
</main>

<footer>
    &copy; <?= date('Y') ?> Quiz App. All rights reserved.
</footer>

</body>
</html>
