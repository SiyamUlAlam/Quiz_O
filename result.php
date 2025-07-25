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

// Fetch the score
$result = $conn->query("SELECT score FROM scores WHERE user_id = $user_id AND quiz_id = $quiz_id");

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $score = $row['score'];
} else {
    $score = "Not Found";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Score</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background-color: #f0f2f5;
            text-align: center;
            padding-top: 50px;
        }
        header, footer {
            background-color: #2c3e50;
            color: white;
            padding: 15px 30px;
        }
        .score-box {
            background: white;
            margin: auto;
            padding: 30px 50px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            display: inline-block;
        }
        h2 {
            color: #2c3e50;
        }
        a.button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 18px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }
        a.button:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>

<header>
    <h1>Quiz Result</h1>
</header>

<main>
    <div class="score-box">
        <h2>Hello, <?= htmlspecialchars($username) ?></h2>
        <p><strong>Your Score:</strong> <?= htmlspecialchars($score) ?></p>
        <a href="user_dashboard.php" class="button">Back to Dashboard</a>
    </div>
</main>

<footer>
    &copy; <?= date('Y') ?> Quiz App. All rights reserved.
</footer>

</body>
</html>
