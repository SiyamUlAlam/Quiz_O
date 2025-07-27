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
            padding: 2rem;
            align-items: center;
        }

        h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #1a1a40;
            margin-bottom: 1.5rem;
            text-align: center;
            letter-spacing: -0.025em;
        }

        .container {
            max-width: 960px;
            width: 100%;
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

        table {
            width: 100%;
            border-collapse: collapse;
            background: #f8fafc;
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background: linear-gradient(90deg, #6e8efb, #a777e3);
            color: white;
            font-weight: 600;
            font-size: 1rem;
        }

        td {
            color: #1a1a40;
            font-size: 0.95rem;
        }

        tr {
            transition: background 0.2s ease;
        }

        tr:hover {
            background: #e8f0fe;
        }

        a.back-link {
            display: inline-block;
            margin-top: 1.5rem;
            padding: 0.9rem 1.5rem;
            background: linear-gradient(90deg, #6e8efb, #a777e3);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        a.back-link:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        a.back-link:active {
            transform: scale(0.98);
        }

        .footer {
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

            h2 {
                font-size: 1.75rem;
            }

            th, td {
                padding: 0.75rem;
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
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
    </div>
    <div class="footer">
        &copy; <?= date("Y") ?> Quiz App by Siyam Ul Alam. All rights reserved.
    </div>
</body>
</html>
