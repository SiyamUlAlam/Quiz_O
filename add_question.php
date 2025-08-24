<?php
require 'config.php';
require 'auth.php';
redirectIfNotAdmin();

$quiz_id = $_GET["quiz_id"] ?? 0;
$success_message = "";

// Get quiz title for better context
$quiz_title = "";
if ($quiz_id) {
    $quiz_stmt = $conn->prepare("SELECT title FROM quizzes WHERE id = ?");
    $quiz_stmt->bind_param("i", $quiz_id);
    $quiz_stmt->execute();
    $quiz_result = $quiz_stmt->get_result();
    if ($quiz_result && $quiz_result->num_rows > 0) {
        $quiz_title = $quiz_result->fetch_assoc()['title'];
    }
    $quiz_stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $question = trim($_POST["question"]);
    $options = array_map('trim', $_POST["options"]);
    $correct = trim($_POST["correct"]);
    
    // Validation
    $errors = [];
    if (empty($question)) {
        $errors[] = "Question text is required";
    }
    if (count(array_filter($options)) < 2) {
        $errors[] = "At least 2 options are required";
    }
    if (!in_array($correct, $options)) {
        $errors[] = "Correct answer must match one of the options exactly";
    }
    
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO questions (quiz_id, question_text, correct_answer) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $quiz_id, $question, $correct);
        $stmt->execute();
        $question_id = $stmt->insert_id;
        
        $optStmt = $conn->prepare("INSERT INTO options (question_id, option_text) VALUES (?, ?)");
        foreach ($options as $opt) {
            if (!empty($opt)) {
                $optStmt->bind_param("is", $question_id, $opt);
                $optStmt->execute();
            }
        }
        $success_message = "Question added successfully! 🎉";
        // Clear form data
        $_POST = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Question - Quiz Management</title>
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
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            position: relative;
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
            padding: 1.5rem 2rem;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo h1 {
            font-size: 1.8rem;
            font-weight: 800;
            color: white;
            background: linear-gradient(45deg, #fff, #f0f9ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .logo-icon {
            font-size: 2rem;
        }

        nav {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        nav a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: 1px solid transparent;
        }

        nav a:hover, nav a.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
        }

        main {
            flex: 1;
            position: relative;
            z-index: 10;
            padding: 2rem;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 3rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            animation: slideUp 0.8s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .page-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            font-size: 1.2rem;
            color: #6b7280;
            font-weight: 500;
        }

        .quiz-context {
            background: linear-gradient(135deg, rgba(103, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            border: 1px solid rgba(103, 126, 234, 0.2);
            border-radius: 12px;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .quiz-context-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }

        .quiz-context-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: #667eea;
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            font-size: 1rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .required {
            color: #ef4444;
        }

        textarea, input[type="text"] {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            color: #1f2937;
            background: white;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        textarea:focus, input[type="text"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(103, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        textarea::placeholder, input[type="text"]::placeholder {
            color: #9ca3af;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .option-input {
            position: relative;
        }

        .option-input::before {
            content: attr(data-label);
            position: absolute;
            top: -8px;
            left: 12px;
            background: white;
            padding: 0 8px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #667eea;
            z-index: 1;
        }

        .correct-answer-section {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1));
            border: 2px solid rgba(16, 185, 129, 0.2);
            border-radius: 12px;
            padding: 1.5rem;
        }

        .correct-answer-section label {
            color: #065f46;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .help-text {
            font-size: 0.9rem;
            color: #6b7280;
            margin-top: 0.5rem;
            font-style: italic;
        }

        .submit-section {
            margin-top: 2rem;
            text-align: center;
        }

        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            cursor: pointer;
            margin: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 4px 15px rgba(103, 126, 234, 0.3);
            min-width: 200px;
        }

        .btn-secondary {
            background: rgba(103, 126, 234, 0.1);
            color: #667eea;
            border: 2px solid rgba(103, 126, 234, 0.2);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .btn:active {
            transform: translateY(-1px);
        }

        .success-message {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1));
            border: 2px solid rgba(16, 185, 129, 0.3);
            border-radius: 12px;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            text-align: center;
            animation: slideDown 0.5s ease-out;
        }

        .success-message-text {
            font-size: 1.1rem;
            font-weight: 600;
            color: #065f46;
            margin-bottom: 0.5rem;
        }

        .success-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .error-message {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.1));
            border: 2px solid rgba(239, 68, 68, 0.3);
            border-radius: 12px;
            padding: 1rem 1.5rem;
            margin-bottom: 1rem;
            color: #dc2626;
        }

        .error-list {
            list-style: none;
            padding: 0;
        }

        .error-list li {
            margin-bottom: 0.25rem;
        }

        .error-list li::before {
            content: "❌ ";
            margin-right: 0.5rem;
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
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 2rem;
            align-items: center;
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

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }

            nav {
                gap: 1rem;
            }

            .container {
                padding: 2rem;
                margin: 1rem;
            }

            .options-grid {
                grid-template-columns: 1fr;
            }

            .success-actions {
                flex-direction: column;
            }

            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 1rem;
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
        <div class="header-content">
            <div class="logo">
                <span class="logo-icon">🎯</span>
                <h1>Quiz Admin</h1>
            </div>
            <nav>
                <a href="admin_dashboard.php">📊 Dashboard</a>
                <a href="create_quiz.php">➕ Create Quiz</a>
                <a href="add_question.php?quiz_id=<?= $quiz_id ?>" class="active">❓ Add Questions</a>
                <a href="view_scores.php">📈 View Scores</a>
                <a href="logout.php">🚪 Logout</a>
            </nav>
            <div class="user-info">
                <span>👨‍💼</span>
                <span><?= htmlspecialchars($_SESSION['username']) ?></span>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Add New Question</h1>
                <p class="page-subtitle">Create engaging questions to challenge your students</p>
            </div>

            <?php if ($quiz_title): ?>
                <div class="quiz-context">
                    <div class="quiz-context-title">Adding question to:</div>
                    <div class="quiz-context-name"><?= htmlspecialchars($quiz_title) ?></div>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="success-message">
                    <div class="success-message-text"><?= $success_message ?></div>
                    <div class="success-actions">
                        <a href="add_question.php?quiz_id=<?= $quiz_id ?>" class="btn btn-primary">
                            ➕ Add Another Question
                        </a>
                        <a href="admin_dashboard.php" class="btn btn-secondary">
                            📊 Back to Dashboard
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <strong>Please fix the following errors:</strong>
                    <ul class="error-list">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" id="questionForm">
                <div class="form-section">
                    <div class="form-group">
                        <label for="question">
                            📝 Question Text <span class="required">*</span>
                        </label>
                        <textarea 
                            name="question" 
                            id="question"
                            rows="4" 
                            placeholder="Enter your question here. Be clear and specific..."
                            required
                        ><?= htmlspecialchars($_POST['question'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>🔤 Answer Options <span class="required">*</span></label>
                        <div class="options-grid">
                            <div class="option-input" data-label="Option A">
                                <input 
                                    name="options[]" 
                                    type="text" 
                                    placeholder="Enter option A" 
                                    value="<?= htmlspecialchars($_POST['options'][0] ?? '') ?>"
                                    required
                                >
                            </div>
                            <div class="option-input" data-label="Option B">
                                <input 
                                    name="options[]" 
                                    type="text" 
                                    placeholder="Enter option B" 
                                    value="<?= htmlspecialchars($_POST['options'][1] ?? '') ?>"
                                    required
                                >
                            </div>
                            <div class="option-input" data-label="Option C">
                                <input 
                                    name="options[]" 
                                    type="text" 
                                    placeholder="Enter option C (optional)" 
                                    value="<?= htmlspecialchars($_POST['options'][2] ?? '') ?>"
                                >
                            </div>
                            <div class="option-input" data-label="Option D">
                                <input 
                                    name="options[]" 
                                    type="text" 
                                    placeholder="Enter option D (optional)" 
                                    value="<?= htmlspecialchars($_POST['options'][3] ?? '') ?>"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="correct-answer-section">
                            <label for="correct">
                                ✅ Correct Answer <span class="required">*</span>
                            </label>
                            <input 
                                name="correct" 
                                id="correct"
                                type="text" 
                                placeholder="Type the exact correct answer from options above" 
                                value="<?= htmlspecialchars($_POST['correct'] ?? '') ?>"
                                required
                            >
                            <div class="help-text">
                                💡 Tip: Copy and paste the correct option exactly as written above
                            </div>
                        </div>
                    </div>
                </div>

                <div class="submit-section">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <span id="submitText">✨ Add Question</span>
                        <span id="loadingSpinner" style="display: none;">⏳ Adding...</span>
                    </button>
                    <a href="admin_dashboard.php" class="btn btn-secondary">
                        🔙 Cancel
                    </a>
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
        // Form submission loading state
        document.getElementById('questionForm').addEventListener('submit', function() {
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const loadingSpinner = document.getElementById('loadingSpinner');
            
            submitBtn.disabled = true;
            submitText.style.display = 'none';
            loadingSpinner.style.display = 'inline';
        });

        // Auto-suggest correct answer when options are typed
        const optionInputs = document.querySelectorAll('input[name="options[]"]');
        const correctInput = document.getElementById('correct');
        
        optionInputs.forEach((input, index) => {
            input.addEventListener('input', function() {
                // If correct answer field is empty and this is the first option being filled
                if (!correctInput.value && index === 0 && this.value) {
                    correctInput.value = this.value;
                }
            });
        });

        // Visual feedback for form validation
        document.querySelectorAll('input, textarea').forEach(field => {
            field.addEventListener('blur', function() {
                if (this.value.trim()) {
                    this.style.borderColor = '#10b981';
                } else if (this.hasAttribute('required')) {
                    this.style.borderColor = '#ef4444';
                }
            });

            field.addEventListener('focus', function() {
                this.style.borderColor = '#667eea';
            });
        });

        // Add entrance animation
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.querySelector('.container');
            container.style.animation = 'slideUp 0.8s ease-out';
        });
    </script>
</body>
</html>

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
