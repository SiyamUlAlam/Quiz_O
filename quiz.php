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
    // Debug: Check if answers are being submitted
    error_log("POST data received: " . print_r($_POST, true));
    error_log("Quiz ID: $quiz_id, User ID: $user_id");
    
    if (isset($_POST["answers"]) && is_array($_POST["answers"]) && !empty($_POST["answers"])) {
        $score = 0;
        $total_questions = count($_POST["answers"]);
        
        // Check if user has already taken this quiz
        $check_stmt = $conn->prepare("SELECT id FROM scores WHERE user_id = ? AND quiz_id = ?");
        $check_stmt->bind_param("ii", $user_id, $quiz_id);
        $check_stmt->execute();
        $existing_score = $check_stmt->get_result();
        
        if ($existing_score->num_rows > 0) {
            error_log("User $user_id already took quiz $quiz_id");
            $error_message = "You have already taken this quiz.";
        } else {
            // Clear any existing user answers for this quiz (in case of retry)
            $clear_stmt = $conn->prepare("DELETE FROM user_answers WHERE user_id = ? AND question_id IN (SELECT id FROM questions WHERE quiz_id = ?)");
            $clear_stmt->bind_param("ii", $user_id, $quiz_id);
            $clear_stmt->execute();
            
            foreach ($_POST["answers"] as $question_id => $answer) {
                // Validate question_id and answer
                if (!is_numeric($question_id) || empty($answer)) {
                    error_log("Invalid question_id ($question_id) or answer ($answer)");
                    continue;
                }
                
                // Get correct answer
                $stmt = $conn->prepare("SELECT correct_answer FROM questions WHERE id = ? AND quiz_id = ?");
                $stmt->bind_param("ii", $question_id, $quiz_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $correct = $row["correct_answer"];
                    $is_correct = ($answer === $correct) ? 1 : 0;
                    $score += $is_correct;
                    
                    error_log("Question $question_id: Answer '$answer', Correct '$correct', Is Correct: $is_correct");
                    
                    // Save user answer
                    $stmt = $conn->prepare("INSERT INTO user_answers (user_id, question_id, selected_option, is_correct) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("iisi", $user_id, $question_id, $answer, $is_correct);
                    if (!$stmt->execute()) {
                        error_log("Failed to save answer for question $question_id: " . $conn->error);
                    }
                } else {
                    error_log("Question $question_id not found for quiz $quiz_id");
                }
            }
            
            // Save score
            $stmt = $conn->prepare("INSERT INTO scores (user_id, quiz_id, score) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $user_id, $quiz_id, $score);
            
            if ($stmt->execute()) {
                // Debug: Log successful redirect
                error_log("Quiz submitted successfully. Score: $score/$total_questions. Redirecting to result.php?quiz_id=$quiz_id");
                
                // Use absolute redirect to ensure it works
                $redirect_url = "result.php?quiz_id=$quiz_id";
                header("Location: $redirect_url");
                exit();
            } else {
                $error_message = "Failed to save quiz results. Please try again.";
                error_log("Database error saving score: " . $conn->error);
            }
        }
    } else {
        $error_message = "Please answer all questions before submitting the quiz.";
        error_log("No answers received in POST data");
    }
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Quiz - Quiz App</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
            color: #1f2937;
        }

        /* Floating background shapes */
        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 20s infinite linear;
        }

        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 60%;
            right: 15%;
            animation-delay: -5s;
        }

        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            bottom: 30%;
            left: 20%;
            animation-delay: -10s;
        }

        .shape:nth-child(4) {
            width: 100px;
            height: 100px;
            top: 10%;
            right: 30%;
            animation-delay: -15s;
        }

        .shape:nth-child(5) {
            width: 90px;
            height: 90px;
            bottom: 20%;
            right: 10%;
            animation-delay: -7s;
        }

        @keyframes float {
            0% { transform: translateY(0) rotate(0deg); opacity: 1; }
            33% { transform: translateY(-30px) rotate(120deg); opacity: 0.8; }
            66% { transform: translateY(20px) rotate(240deg); opacity: 0.6; }
            100% { transform: translateY(0) rotate(360deg); opacity: 1; }
        }

        header {
            position: relative;
            z-index: 10;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding: 2rem;
            text-align: center;
        }

        header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            color: white;
            margin-bottom: 0.5rem;
            background: linear-gradient(45deg, #fff, #f0f9ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .quiz-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            display: inline-block;
            margin-top: 0.5rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            font-weight: 500;
        }

        .progress-bar {
            position: sticky;
            top: 0;
            z-index: 20;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            padding: 1.5rem 0;
        }

        .progress-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .progress-text {
            color: #374151;
            margin-bottom: 1rem;
            font-weight: 600;
            text-align: center;
            font-size: 1rem;
        }

        .progress-track {
            background: #e5e7eb;
            height: 8px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .progress-fill {
            background: linear-gradient(90deg, #667eea, #764ba2);
            height: 100%;
            width: 0%;
            border-radius: 10px;
            transition: width 0.5s ease;
            box-shadow: 0 2px 8px rgba(103, 126, 234, 0.3);
        }

        main {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 2rem;
            position: relative;
            z-index: 10;
        }

        .quiz-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(20px);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideUp 0.8s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .quiz-header {
            padding: 3rem 2rem 2rem;
            background: linear-gradient(135deg, rgba(103, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
            border-bottom: 1px solid rgba(103, 126, 234, 0.1);
            text-align: center;
        }

        .quiz-title {
            font-size: 2.2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }

        .quiz-instructions {
            color: #6b7280;
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .timer-display {
            background: linear-gradient(135deg, #f59e0b, #f97316);
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            font-size: 1.1rem;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
        }

        .questions-form {
            padding: 2rem;
        }

        .question-card {
            margin-bottom: 2.5rem;
            padding: 2.5rem;
            background: white;
            border-radius: 16px;
            border: 2px solid #f3f4f6;
            transition: all 0.3s ease;
            position: relative;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .question-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border-color: #e5e7eb;
        }

        .question-number {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            font-weight: 700;
            font-size: 0.9rem;
            display: inline-block;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .question-text {
            font-size: 1.4rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .options-container {
            display: grid;
            gap: 1rem;
        }

        .option-label {
            display: flex;
            align-items: center;
            padding: 1.25rem 1.5rem;
            background: #f9fafb;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .option-label::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(103, 126, 234, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .option-label:hover::before {
            left: 100%;
        }

        .option-label:hover {
            border-color: #667eea;
            background: rgba(103, 126, 234, 0.05);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(103, 126, 234, 0.15);
        }

        .option-radio {
            margin-right: 1rem;
            width: 1.5rem;
            height: 1.5rem;
            accent-color: #667eea;
            transform: scale(1.2);
        }

        .option-text {
            font-size: 1.1rem;
            color: #374151;
            font-weight: 500;
            flex: 1;
        }

        .option-label input[type="radio"]:checked + .option-text {
            font-weight: 700;
            color: #1f2937;
        }

        .option-label:has(input[type="radio"]:checked) {
            border-color: #667eea;
            background: linear-gradient(135deg, rgba(103, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            box-shadow: 0 8px 20px rgba(103, 126, 234, 0.2);
        }

        .submit-container {
            padding: 3rem 2rem;
            background: linear-gradient(135deg, rgba(103, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
            text-align: center;
            border-top: 1px solid rgba(103, 126, 234, 0.1);
        }

        .submit-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 1.5rem 3rem;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 8px 25px rgba(103, 126, 234, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.6s ease;
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(103, 126, 234, 0.4);
        }

        .submit-btn:active {
            transform: translateY(-1px);
        }

        .submit-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none !important;
        }

        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        footer {
            position: relative;
            z-index: 10;
            background: rgba(31, 41, 55, 0.9);
            backdrop-filter: blur(20px);
            color: white;
            padding: 2rem;
            text-align: center;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 2rem;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 2rem;
            align-items: center;
        }

        .footer-info {
            text-align: left;
        }

        .footer-info h4 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #f3f4f6;
        }

        .footer-info p {
            font-size: 0.9rem;
            color: #d1d5db;
            margin: 0.2rem 0;
        }

        .footer-copyright {
            text-align: right;
        }

        .footer-copyright p {
            font-size: 0.85rem;
            color: #9ca3af;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-70px) rotate(180deg); }
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%) rotate(45deg); }
            100% { transform: translateX(100%) rotate(45deg); }
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .question-card.answered {
            border-color: #667eea !important;
            background: linear-gradient(135deg, rgba(103, 126, 234, 0.05), rgba(118, 75, 162, 0.05)) !important;
            box-shadow: 0 8px 20px rgba(103, 126, 234, 0.15) !important;
        }

        .question-card.answered .question-number {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            header h1 {
                font-size: 2rem;
            }

            main {
                margin: 1rem;
                padding: 0;
            }

            .quiz-header {
                padding: 2rem 1.5rem 1.5rem;
            }

            .quiz-title {
                font-size: 1.8rem;
            }

            .questions-form {
                padding: 1.5rem;
            }

            .question-card {
                padding: 2rem 1.5rem;
                margin-bottom: 2rem;
            }

            .question-text {
                font-size: 1.2rem;
            }

            .option-label {
                padding: 1rem;
            }

            .submit-container {
                padding: 2rem 1.5rem;
            }

            .submit-btn {
                padding: 1.25rem 2rem;
                font-size: 1rem;
            }

            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 1rem;
            }

            .footer-info {
                text-align: center;
            }

            .footer-copyright {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Floating background shapes -->
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <header>
        <h1>🎯 Quiz Challenge</h1>
        <div class="quiz-badge">
            💡 Test Your Knowledge
        </div>
    </header>

    <div class="progress-bar">
        <div class="progress-container">
            <div class="progress-text">Question <span id="current-question">1</span> of <span id="total-questions"><?= $questions->num_rows ?></span></div>
            <div class="progress-track">
                <div class="progress-fill" id="progress-fill"></div>
            </div>
        </div>
    </div>

    <main>
        <div class="quiz-container">
            <div class="quiz-header">
                <h2 class="quiz-title">Answer All Questions</h2>
                <p class="quiz-instructions">Choose the best answer for each question. Good luck! 🍀</p>
                <div class="timer-display" id="timer">⏱️ 00:00</div>
            </div>

            <form method="POST" class="questions-form" id="quizForm" action="">
                <!-- Hidden field to help debug form submission -->
                <input type="hidden" name="form_submitted" value="1">
                <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">
                
                <?php if (isset($error_message)): ?>
                    <div class="error-message" style="background: linear-gradient(135deg, #ef4444, #dc2626); color: white; padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 2rem; text-align: center; font-weight: 600; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);">
                        ⚠️ <?= htmlspecialchars($error_message) ?>
                    </div>
                <?php endif; ?>
                
                <?php 
                $question_number = 1;
                $questions->data_seek(0); // Reset pointer
                while ($q = $questions->fetch_assoc()): 
                ?>
                    <div class="question-card" data-question="<?= $question_number ?>">
                        <div class="question-number">Question <?= $question_number ?></div>
                        <div class="question-text"><?= htmlspecialchars($q['question_text']) ?></div>
                        
                        <div class="options-container">
                            <?php
                            $opt_stmt = $conn->prepare("SELECT * FROM options WHERE question_id = ?");
                            $opt_stmt->bind_param("i", $q['id']);
                            $opt_stmt->execute();
                            $options = $opt_stmt->get_result();
                            while ($opt = $options->fetch_assoc()):
                            ?>
                                <label class="option-label">
                                    <input type="radio" name="answers[<?= $q['id'] ?>]" value="<?= htmlspecialchars($opt['option_text']) ?>" required class="option-radio">
                                    <span class="option-text"><?= htmlspecialchars($opt['option_text']) ?></span>
                                </label>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php 
                $question_number++;
                endwhile; 
                ?>

                <div class="submit-container">
                    <button type="submit" class="submit-btn" id="submitBtn">
                        <span class="loading-spinner" id="loadingSpinner"></span>
                        <span id="btnText">🚀 Submit Quiz</span>
                    </button>
                    
                    <noscript>
                        <div style="margin-top: 1rem; padding: 1rem; background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; color: #92400e;">
                            <strong>JavaScript is disabled.</strong> The form will still work, but without interactive features.
                        </div>
                    </noscript>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-info">
                <h4>Developed by</h4>
                <p><strong>Siyam Ul Alam</strong></p>
                <p>Gazipur Digital University</p>
                <p>📧 2002032@icte.bdu.ac.bd</p>
            </div>
            <div class="footer-copyright">
                <p>&copy; <?= date("Y") ?> Quiz App. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Timer functionality
        let startTime = Date.now();
        let timerInterval;

        function updateTimer() {
            const elapsed = Date.now() - startTime;
            const minutes = Math.floor(elapsed / 60000);
            const seconds = Math.floor((elapsed % 60000) / 1000);
            document.getElementById('timer').innerHTML = `⏱️ ${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }

        // Start timer
        timerInterval = setInterval(updateTimer, 1000);

        // Progress tracking
        const totalQuestions = <?= $questions->num_rows ?>;
        let answeredQuestions = 0;

        function updateProgress() {
            const currentQuestion = Math.min(answeredQuestions + 1, totalQuestions);
            const progressPercentage = (answeredQuestions / totalQuestions) * 100;
            
            document.getElementById('current-question').textContent = currentQuestion;
            document.getElementById('progress-fill').style.width = progressPercentage + '%';
        }

        // Track answered questions
        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const questionName = this.name;
                const questionCard = this.closest('.question-card');
                
                // Mark question as answered
                if (!questionCard.classList.contains('answered')) {
                    questionCard.classList.add('answered');
                    answeredQuestions++;
                    updateProgress();
                }

                // Add visual feedback
                questionCard.style.borderColor = '#667eea';
                questionCard.style.background = 'linear-gradient(135deg, rgba(103, 126, 234, 0.05), rgba(118, 75, 162, 0.05))';
                questionCard.style.boxShadow = '0 8px 20px rgba(103, 126, 234, 0.15)';
                
                // Smooth scroll to next question
                const nextQuestion = questionCard.nextElementSibling;
                if (nextQuestion && nextQuestion.classList.contains('question-card')) {
                    setTimeout(() => {
                        nextQuestion.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }, 500);
                }
            });
        });

        // Form submission with loading state
        document.getElementById('quizForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const loadingSpinner = document.getElementById('loadingSpinner');
            const btnText = document.getElementById('btnText');
            
            // Validate all questions are answered
            const requiredQuestions = document.querySelectorAll('input[type="radio"][required]');
            const questionGroups = {};
            
            // Group radio buttons by name
            requiredQuestions.forEach(radio => {
                const name = radio.name;
                if (!questionGroups[name]) {
                    questionGroups[name] = [];
                }
                questionGroups[name].push(radio);
            });
            
            // Check if all question groups have at least one selected
            let allAnswered = true;
            let unansweredQuestions = [];
            
            Object.keys(questionGroups).forEach(groupName => {
                const isAnswered = questionGroups[groupName].some(radio => radio.checked);
                if (!isAnswered) {
                    allAnswered = false;
                    // Find question number
                    const questionCard = questionGroups[groupName][0].closest('.question-card');
                    const questionNum = questionCard.getAttribute('data-question');
                    unansweredQuestions.push(questionNum);
                }
            });
            
            if (!allAnswered) {
                e.preventDefault(); // Only prevent if validation fails
                
                // Remove previous error highlighting
                document.querySelectorAll('.question-card.error').forEach(card => {
                    card.classList.remove('error');
                });
                
                // Highlight unanswered questions
                unansweredQuestions.forEach(questionNum => {
                    const questionCard = document.querySelector(`[data-question="${questionNum}"]`);
                    if (questionCard) {
                        questionCard.classList.add('error');
                    }
                });
                
                // Show error message with better styling
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message';
                errorDiv.style.cssText = `
                    background: linear-gradient(135deg, #ef4444, #dc2626);
                    color: white;
                    padding: 1rem 1.5rem;
                    border-radius: 12px;
                    margin-bottom: 2rem;
                    text-align: center;
                    font-weight: 600;
                    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
                    animation: slideDown 0.5s ease-out;
                    position: fixed;
                    top: 20px;
                    left: 50%;
                    transform: translateX(-50%);
                    z-index: 1000;
                    max-width: 90%;
                `;
                errorDiv.innerHTML = `⚠️ Please answer all questions before submitting.<br><small>Unanswered questions: ${unansweredQuestions.join(', ')}</small>`;
                
                document.body.appendChild(errorDiv);
                
                // Remove error message after 5 seconds
                setTimeout(() => {
                    if (errorDiv.parentNode) {
                        errorDiv.remove();
                    }
                    // Remove error highlighting
                    document.querySelectorAll('.question-card.error').forEach(card => {
                        card.classList.remove('error');
                    });
                }, 5000);
                
                // Scroll to first unanswered question
                const firstUnanswered = document.querySelector(`[data-question="${unansweredQuestions[0]}"]`);
                if (firstUnanswered) {
                    firstUnanswered.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return false;
            }
            
            // All questions answered, proceed with submission
            // Show loading state immediately
            submitBtn.disabled = true;
            loadingSpinner.style.display = 'inline-block';
            btnText.textContent = '📊 Calculating Results...';
            
            // Stop timer
            clearInterval(timerInterval);
            
            // Show success confirmation
            const successDiv = document.createElement('div');
            successDiv.className = 'success-message';
            successDiv.style.cssText = `
                background: linear-gradient(135deg, #10b981, #059669);
                color: white;
                padding: 1rem 1.5rem;
                border-radius: 12px;
                text-align: center;
                font-weight: 600;
                box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
                animation: slideDown 0.5s ease-out;
                position: fixed;
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                z-index: 1000;
                max-width: 90%;
            `;
            successDiv.innerHTML = `✅ All questions answered! Submitting your quiz...`;
            document.body.appendChild(successDiv);
            
            // Add completion animation
            document.querySelector('.quiz-container').style.transform = 'scale(0.98)';
            document.querySelector('.quiz-container').style.opacity = '0.8';
            
            // Remove success message after a short delay
            setTimeout(() => {
                if (successDiv.parentNode) {
                    successDiv.remove();
                }
            }, 2000);
            
            // Form will submit normally since we didn't prevent it
        });

        // Animate question cards on scroll
        const observerOptions = {
            threshold: 0.2,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'slideInUp 0.6s ease-out';
                }
            });
        }, observerOptions);

        // Observe all question cards
        document.querySelectorAll('.question-card').forEach(card => {
            observer.observe(card);
        });

        // Initialize progress
        updateProgress();

        // Warn before leaving page
        window.addEventListener('beforeunload', function(e) {
            if (answeredQuestions > 0 && answeredQuestions < totalQuestions) {
                e.preventDefault();
                e.returnValue = 'You have unsaved progress. Are you sure you want to leave?';
            }
        });

        // Auto-save functionality (optional)
        let autoSaveTimeout;
        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                clearTimeout(autoSaveTimeout);
                autoSaveTimeout = setTimeout(() => {
                    // Could implement auto-save to localStorage here
                    console.log('Auto-saving progress...');
                }, 2000);
            });
        });
    </script>

    <style>
        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .question-card.answered {
            border-color: #667eea !important;
            background: linear-gradient(135deg, rgba(103, 126, 234, 0.05), rgba(118, 75, 162, 0.05)) !important;
            box-shadow: 0 8px 20px rgba(103, 126, 234, 0.15) !important;
        }

        .question-card.answered .question-number {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        /* Additional animations */
        .quiz-container {
            animation: slideUp 0.8s ease-out;
        }

        .question-card {
            animation: slideInUp 0.6s ease-out forwards;
        }

        .question-card:nth-child(1) { animation-delay: 0.1s; }
        .question-card:nth-child(2) { animation-delay: 0.2s; }
        .question-card:nth-child(3) { animation-delay: 0.3s; }
        .question-card:nth-child(4) { animation-delay: 0.4s; }
        .question-card:nth-child(5) { animation-delay: 0.5s; }
        
        /* Error state for questions */
        .question-card.error {
            border-color: #ef4444 !important;
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.05), rgba(220, 38, 38, 0.05)) !important;
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.15) !important;
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        /* Success message styles */
        .success-message {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            text-align: center;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
            animation: slideDown 0.5s ease-out;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</body>
</html>
