<?php
require 'config.php';
require 'auth.php';
redirectIfNotAdmin();
$quiz_id = $_GET["quiz_id"] ?? 0;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $question = $_POST["question"];
    $options = $_POST["options"];
    $correct = $_POST["correct"];
    $stmt = $conn->prepare("INSERT INTO questions (quiz_id, question_text, correct_answer) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $quiz_id, $question, $correct);
    $stmt->execute();
    $question_id = $stmt->insert_id;
    $optStmt = $conn->prepare("INSERT INTO options (question_id, option_text) VALUES (?, ?)");
    foreach ($options as $opt) {
        $optStmt->bind_param("is", $question_id, $opt);
        $optStmt->execute();
    }
    echo "<div class='success-msg'>Question added. <a href='add_question.php?quiz_id=$quiz_id'>Add another</a></div>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Question</title>
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
            max-width: 800px;
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
            font-size: 1.75rem;
            font-weight: 600;
            color: #1a1a40;
            margin-bottom: 1.5rem;
        }

        label {
            font-size: 1rem;
            font-weight: 500;
            color: #1a1a40;
            margin-bottom: 0.5rem;
            display: block;
        }

        textarea, input[type="text"] {
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

        textarea:focus, input[type="text"]:focus {
            outline: none;
            border-color: #6e8efb;
            box-shadow: 0 0 0 3px rgba(110, 142, 251, 0.2);
        }

        textarea::placeholder, input[type="text"]::placeholder {
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

        .success-msg {
            background: #e8f8f5;
            padding: 0.75rem 1.5rem;
            margin: 1rem auto;
            border-left: 4px solid #2ecc71;
            color: #1a1a40;
            border-radius: 8px;
            width: fit-content;
            font-size: 0.9rem;
            animation: fadeIn 0.3s ease forwards;
        }

        .success-msg a {
            color: #6e8efb;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .success-msg a:hover {
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

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
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
                font-size: 1.5rem;
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
        <h2>Add Question</h2>
        <form method="POST">
            <label>Question:</label>
            <textarea name="question" rows="3" placeholder="Enter the question" required></textarea>
            <label>Options:</label>
            <input name="options[]" type="text" placeholder="Option 1" required>
            <input name="options[]" type="text" placeholder="Option 2" required>
            <input name="options[]" type="text" placeholder="Option 3" required>
            <input name="options[]" type="text" placeholder="Option 4" required>
            <label>Correct Answer:</label>
            <input name="correct" type="text" placeholder="Must match one of the options" required>
            <button type="submit">Add Question</button>
        </form>
    </div>
    <footer>
        &copy; <?= date('Y') ?> Quiz App by Siyam Ul Alam. All rights reserved.
    </footer>
</body>
</html>
