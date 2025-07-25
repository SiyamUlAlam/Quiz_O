<?php
require 'config.php';
require 'auth.php';
redirectIfNotLoggedIn();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("❌ Invalid or missing quiz ID.");
}

$quiz_id = (int) $_GET['id'];
$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $score = 0;
    foreach ($_POST["answers"] as $question_id => $answer) {
        // Get correct answer
        $stmt = $conn->prepare("SELECT correct_answer FROM questions WHERE id = ?");
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $correct = $result->fetch_assoc()["correct_answer"];
        $is_correct = ($answer === $correct) ? 1 : 0;
        $score += $is_correct;

        // Save user answer
        $stmt = $conn->prepare("INSERT INTO user_answers (user_id, question_id, selected_option, is_correct)
                                VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iisi", $user_id, $question_id, $answer, $is_correct);
        $stmt->execute();
    }

    // Save score
    $stmt = $conn->prepare("INSERT INTO scores (user_id, quiz_id, score) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $user_id, $quiz_id, $score);
    $stmt->execute();

    header("Location: result.php?quiz_id=$quiz_id");
    exit();
}

// Load questions
$stmt = $conn->prepare("SELECT * FROM questions WHERE quiz_id = ?");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$questions = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Take Quiz</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f8;
            padding: 20px;
            margin: 0;
        }

        h2 {
            color: #2c3e50;
        }

        form {
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            max-width: 700px;
            margin: 0 auto;
        }

        .question {
            margin-bottom: 20px;
            padding: 15px;
            border-left: 4px solid #2980b9;
            background: #f9fbfc;
        }

        label {
            display: block;
            margin: 8px 0;
            cursor: pointer;
        }

        button {
            background: #2980b9;
            color: #fff;
            padding: 10px 16px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #1c5980;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            color: #777;
            font-size: 14px;
        }
    </style>
</head>
<body>

<h2>Take the Quiz</h2>

<form method="POST">
<?php while ($q = $questions->fetch_assoc()): ?>
    <div class="question">
        <p><strong><?= htmlspecialchars($q['question_text']) ?></strong></p>
        <?php
        $opt_stmt = $conn->prepare("SELECT * FROM options WHERE question_id = ?");
        $opt_stmt->bind_param("i", $q['id']);
        $opt_stmt->execute();
        $options = $opt_stmt->get_result();
        while ($opt = $options->fetch_assoc()):
        ?>
            <label>
                <input type="radio" name="answers[<?= $q['id'] ?>]" value="<?= htmlspecialchars($opt['option_text']) ?>" required>
                <?= htmlspecialchars($opt['option_text']) ?>
            </label>
        <?php endwhile; ?>
    </div>
<?php endwhile; ?>
    <button type="submit">Submit Quiz</button>
</form>

<div class="footer">
    &copy; <?= date("Y") ?> Quiz App. All rights reserved.
</div>

</body>
</html>
