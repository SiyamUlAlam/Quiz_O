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
            align-items: center;
            padding: 2rem;
        }

        header {
            background: #1a1a40;
            color: white;
            padding: 1.5rem 2rem;
            width: 100%;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        header h1 {
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: -0.025em;
        }

        main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }

        .score-box {
            background: rgba(255, 255, 255, 0.95);
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            max-width: 500px;
            width: 100%;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .score-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        h2 {
            font-size: 1.75rem;
            font-weight: 600;
            color: #1a1a40;
            margin-bottom: 1rem;
        }

        p {
            font-size: 1.25rem;
            font-weight: 500;
            color: #1a1a40;
            margin-bottom: 1.5rem;
        }

        p strong {
            font-weight: 700;
        }

        a.button {
            display: inline-block;
            padding: 0.9rem 1.5rem;
            background: linear-gradient(90deg, #6e8efb, #a777e3);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        a.button:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        a.button:active {
            transform: scale(0.98);
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
            .score-box {
                padding: 1.5rem;
                margin: 1rem;
            }

            header h1 {
                font-size: 1.75rem;
            }

            h2 {
                font-size: 1.5rem;
            }

            p {
                font-size: 1.1rem;
            }
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
        &copy; <?= date('Y') ?> Quiz App by Siyam Ul Alam. All rights reserved.
    </footer>
</body>
</html>
