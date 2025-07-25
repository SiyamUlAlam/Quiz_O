<?php
require 'config.php';
require 'auth.php';
redirectIfNotAdmin();

// Fetch all scores with related usernames and quiz titles
$result = $conn->query("
    SELECT u.username, q.title, s.score 
    FROM scores s
    JOIN users u ON s.user_id = u.id
    JOIN quizzes q ON s.quiz_id = q.id
    ORDER BY q.title, u.username
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Quiz Scores</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 30px;
        }

        h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        th, td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #3498db;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        a.back-link {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            background-color: #2980b9;
            color: white;
            padding: 10px 16px;
            border-radius: 6px;
            transition: background 0.2s ease;
        }

        a.back-link:hover {
            background-color: #1c5980;
        }

        .footer {
            text-align: center;
            margin-top: 50px;
            color: #999;
            font-size: 14px;
        }
    </style>
</head>
<body>

<h2>All Quiz Scores</h2>

<table>
    <tr>
        <th>User</th>
        <th>Quiz</th>
        <th>Score</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= $row['score'] ?></td>
        </tr>
    <?php endwhile; ?>
</table>

<a href="admin_dashboard.php" class="back-link">← Back to Dashboard</a>

<div class="footer">
    &copy; <?= date("Y") ?> Quiz App
</div>

</body>
</html>
