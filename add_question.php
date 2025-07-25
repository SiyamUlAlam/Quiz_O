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
            max-width: 800px;
            margin: 30px auto;
            background-color: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }

        textarea, input[type="text"], input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 14px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        button {
            background-color: #3498db;
            color: #fff;
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #2c81ba;
        }

        .success-msg {
            background: #e8f8f5;
            padding: 12px 18px;
            margin: 15px auto;
            border-left: 4px solid #2ecc71;
            color: #2c3e50;
            width: fit-content;
            border-radius: 6px;
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
    <h2>Add Question</h2>

    <form method="POST">
        <label>Question:</label><br>
        <textarea name="question" rows="3" required></textarea><br>

        <label>Options:</label><br>
        <input name="options[]" type="text" placeholder="Option 1" required><br>
        <input name="options[]" type="text" placeholder="Option 2" required><br>
        <input name="options[]" type="text" placeholder="Option 3" required><br>
        <input name="options[]" type="text" placeholder="Option 4" required><br>

        <label>Correct Answer:</label>
        <input name="correct" type="text" placeholder="Must match one of the options" required><br>

        <button type="submit">Add Question</button>
    </form>
</div>

<footer>
    &copy; <?= date('Y') ?> Quiz App. All rights reserved.
</footer>

</body>
</html>
