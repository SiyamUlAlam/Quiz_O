<?php
require 'config.php';
require 'auth.php';
redirectIfNotAdmin();
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $created_by = $_SESSION["user_id"];
    $stmt = $conn->prepare("INSERT INTO quizzes (title, created_by) VALUES (?, ?)");
    $stmt->bind_param("si", $title, $created_by);
    if ($stmt->execute()) {
        $message = "✅ Quiz created successfully. <a href='admin_dashboard.php'>Go back</a>";
    } else {
        $message = "❌ Error: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Quiz</title>
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

        .container {
            max-width: 500px;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            margin: 2rem auto;
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
            margin-bottom: 1.5rem;
            text-align: center;
            letter-spacing: -0.025em;
        }

        label {
            font-size: 1rem;
            font-weight: 500;
            color: #1a1a40;
            margin-bottom: 0.5rem;
            display: block;
        }

        input[type="text"] {
            width: 100%;
            padding: 0.9rem;
            margin-bottom: 1rem;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            color: #1a1a40;
            background: #f8fafc;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: #6e8efb;
            box-shadow: 0 0 0 3px rgba(110, 142, 251, 0.2);
        }

        input[type="text"]::placeholder {
            color: #94a3b8;
        }

        button {
            width: 100%;
            padding: 0.9rem;
            background: linear-gradient(90deg, #6e8efb, #a777e3);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        button:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        button:active {
            transform: scale(0.98);
        }

        .message {
            margin-top: 1rem;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 0.9rem;
            text-align: center;
            animation: fadeIn 0.3s ease forwards;
        }

        .message a {
            color: #6e8efb;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .message a:hover {
            color: #a777e3;
        }

        .message:where(:has(a[href*='admin_dashboard'])) {
            background: #e8f8f5;
            border-left: 4px solid #2ecc71;
            color: #1a1a40;
        }

        .message:not(:has(a[href*='admin_dashboard'])) {
            background: #fee2e2;
            border-left: 4px solid #e63946;
            color: #1a1a40;
        }

        .footer {
            margin-top: auto;
            padding: 1.5rem;
            color: #ffffff;
            font-size: 0.9rem;
            text-align: center;
            width: 100%;
            background: #1a1a40;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 600px) {
            .container {
                margin: 1rem;
                padding: 1.5rem;
            }

            h2 {
                font-size: 1.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Create New Quiz</h2>
        <form method="POST">
            <label for="title">Quiz Title:</label>
            <input type="text" id="title" name="title" placeholder="Enter quiz title" required>
            <button type="submit">Create</button>
        </form>
        <?php if ($message): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>
    </div>
    <div class="footer">
        &copy; <?= date("Y") ?> Quiz App by Siyam Ul Alam. All rights reserved.
    </div>
</body>
</html>
