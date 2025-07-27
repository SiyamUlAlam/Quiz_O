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
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f5f7fa;
            margin: 0;
        }
        header, footer {
            background: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        main {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 { color: #2c3e50; }
        ul { padding: 0; list-style: none; }
        li {
            margin: 10px 0;
            padding: 15px;
            background: #ecf0f1;
            border-radius: 8px;
        }
        .quiz-link {
            text-decoration: none;
            color: #2980b9;
            font-weight: bold;
        }
        .logout-btn {
            display: inline-block;
            margin-top: 20px;
            background: #e74c3c;
            color: white;
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
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
        <p>No quizzes available right now.</p>
    <?php endif; ?>

    <a href="logout.php" class="logout-btn">Logout</a>
</main>

<footer>
    &copy; <?= date("Y") ?> Quiz App
</footer>

</body>
</html>
