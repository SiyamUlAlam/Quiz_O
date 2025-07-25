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
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 500px;
            background: #fff;
            margin: 40px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            background-color: #2980b9;
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            font-size: 15px;
            cursor: pointer;
        }

        button:hover {
            background-color: #1c5980;
        }

        .message {
            margin-top: 15px;
            color: green;
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

<div class="container">
    <h2>Create New Quiz</h2>

    <form method="POST">
        <label for="title">Quiz Title:</label>
        <input type="text" id="title" name="title" required>
        <button type="submit">Create</button>
    </form>

    <?php if ($message): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>
</div>

<div class="footer">
    &copy; <?= date("Y") ?> Quiz App
</div>

</body>
</html>
