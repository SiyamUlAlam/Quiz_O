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
        $stmt = $conn->prepare("INSERT INTO user_answers (user_id, question_id, selected_option, is_correct) VALUES (?, ?, ?, ?)");
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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #6e8efb, #a777e3);
            min-height: 100vh;
            padding: 2rem;
            display: flex;
            flex-direction: column;
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

        form {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            max-width: 800px;
            width: 100%;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        form:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        .question {
            margin-bottom: 1.5rem;
            padding: 1.5rem;
            background: #f8fafc;
            border-radius: 8px;
            border-left: 4px solid #6e8efb;
            transition: transform 0.2s ease;
        }

        .question:hover {
            transform: translateX(5px);
        }

        .question p {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1a1a40;
            margin-bottom: 1rem;
        }

        label {
            display: flex;
            align-items: center;
            margin: 0.5rem 0;
            font-size: 1rem;
            color: #1a1a40;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        label:hover {
            color: #6e8efb;
        }

        input[type="radio"] {
            margin-right: 0.75rem;
            accent-color: #6e8efb;
            width: 1.2rem;
            height: 1.2rem;
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

        .footer {
            margin-top: 2rem;
            color: #ffffff;
            font-size: 0.9rem;
            text-align: center;
            opacity: 0.8;
        }

        @media (max-width: 600px) {
            form {
                padding: 1.5rem;
            }

            h2 {
                font-size: 1.75rem;
            }

            .question {
                padding: 1rem;
            }
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
        &copy; <?= date("Y") ?> Quiz App by Siyam Ul Alam. All rights reserved.
    </div>
</body>
</html>
