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
    
    if (isset($_POST["answers"]) && is_array($_POST["answers"]) && !empty($_POST["answers"])) {
        $score = 0;
        $total_questions = count($_POST["answers"]);
        
        foreach ($_POST["answers"] as $question_id => $answer) {
            // Validate question_id and answer
            if (!is_numeric($question_id) || empty($answer)) {
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
                
                // Save user answer
                $stmt = $conn->prepare("INSERT INTO user_answers (user_id, question_id, selected_option, is_correct) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iisi", $user_id, $question_id, $answer, $is_correct);
                $stmt->execute();
            }
        }
        
        // Save score
        $stmt = $conn->prepare("INSERT INTO scores (user_id, quiz_id, score) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $user_id, $quiz_id, $score);
        
        if ($stmt->execute()) {
            header("Location: result.php?quiz_id=$quiz_id");
            exit();
        } else {
            $error_message = "Failed to save quiz results. Please try again.";
            error_log("Database error: " . $conn->error);
        }
    } else {
        $error_message = "Please answer all questions before submitting the quiz.";
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

            <form method="POST" class="questions-form" id="quizForm">
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
            e.preventDefault(); // Prevent default submission initially
            
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
            
            // Stop timer
            clearInterval(timerInterval);
            
            // Show loading state
            submitBtn.disabled = true;
            loadingSpinner.style.display = 'inline-block';
            btnText.textContent = '📊 Calculating Results...';
            
            // Add completion animation
            document.querySelector('.quiz-container').style.transform = 'scale(0.98)';
            document.querySelector('.quiz-container').style.opacity = '0.8';
            
            // Submit the form after a brief delay to show loading state
            setTimeout(() => {
                successDiv.remove();
                this.submit();
            }, 2000);
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
