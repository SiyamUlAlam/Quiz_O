<?php
require 'config.php';
require 'auth.php';
redirectIfNotAdmin();

$message = "";
$message_type = "";
$created_quiz_id = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"] ?? "");
    $created_by = $_SESSION["user_id"];
    
    // Validation
    if (empty($title)) {
        $message = "Quiz title is required";
        $message_type = "error";
    } elseif (strlen($title) < 3) {
        $message = "Quiz title must be at least 3 characters long";
        $message_type = "error";
    } else {
        $stmt = $conn->prepare("INSERT INTO quizzes (title, description, created_by) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $title, $description, $created_by);
        
        if ($stmt->execute()) {
            $created_quiz_id = $stmt->insert_id;
            $message = "Quiz created successfully! 🎉";
            $message_type = "success";
        } else {
            $message = "Error creating quiz: " . $stmt->error;
            $message_type = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Quiz - Quiz Management</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            max-width: 600px;
            width: 100%;
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

        input[type="text"], textarea {
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

        input[type="text"]:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(103, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        input[type="text"]::placeholder, textarea::placeholder {
            color: #9ca3af;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .char-counter {
            text-align: right;
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 0.25rem;
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

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .message {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            text-align: center;
            animation: slideDown 0.5s ease-out;
        }

        .message.success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1));
            border: 2px solid rgba(16, 185, 129, 0.3);
            color: #065f46;
        }

        .message.error {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.1));
            border: 2px solid rgba(239, 68, 68, 0.3);
            color: #dc2626;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .success-actions {
            margin-top: 1rem;
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .quiz-preview {
            background: linear-gradient(135deg, rgba(103, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
            border: 1px solid rgba(103, 126, 234, 0.1);
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1rem;
        }

        .quiz-preview-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .quiz-preview-description {
            color: #6b7280;
            font-size: 0.9rem;
            font-style: italic;
        }

        .tips-section {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(147, 51, 234, 0.05));
            border: 1px solid rgba(59, 130, 246, 0.1);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .tips-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e40af;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .tips-list {
            list-style: none;
            padding: 0;
        }

        .tips-list li {
            color: #4b5563;
            margin-bottom: 0.5rem;
            padding-left: 1.5rem;
            position: relative;
        }

        .tips-list li::before {
            content: "💡";
            position: absolute;
            left: 0;
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
                <a href="create_quiz.php" class="active">➕ Create Quiz</a>
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
                <h1 class="page-title">Create New Quiz</h1>
                <p class="page-subtitle">Design engaging quizzes to test knowledge and skills</p>
            </div>

            <?php if ($message): ?>
                <div class="message <?= $message_type ?>">
                    <div style="font-weight: 600; margin-bottom: 0.5rem;">
                        <?= htmlspecialchars($message) ?>
                    </div>
                    <?php if ($message_type === "success" && $created_quiz_id): ?>
                        <div class="success-actions">
                            <a href="add_question.php?quiz_id=<?= $created_quiz_id ?>" class="btn btn-primary">
                                ❓ Add Questions
                            </a>
                            <a href="create_quiz.php" class="btn btn-secondary">
                                ➕ Create Another Quiz
                            </a>
                            <a href="admin_dashboard.php" class="btn btn-secondary">
                                📊 Back to Dashboard
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (!$created_quiz_id): ?>
                <div class="tips-section">
                    <div class="tips-title">
                        <span>🌟</span>
                        Tips for Creating Great Quizzes
                    </div>
                    <ul class="tips-list">
                        <li>Choose a clear, descriptive title that tells students what to expect</li>
                        <li>Write a brief description explaining the quiz purpose and difficulty level</li>
                        <li>Plan your questions before creating the quiz for better organization</li>
                        <li>Consider the time students will need to complete the quiz</li>
                    </ul>
                </div>

                <form method="POST" id="createQuizForm">
                    <div class="form-section">
                        <div class="form-group">
                            <label for="title">
                                📚 Quiz Title <span class="required">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="title" 
                                name="title" 
                                placeholder="e.g., Mathematics Fundamentals Quiz"
                                value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                                maxlength="100"
                                required
                            >
                            <div class="char-counter">
                                <span id="titleCounter">0</span>/100 characters
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">
                                📝 Quiz Description (Optional)
                            </label>
                            <textarea 
                                id="description" 
                                name="description" 
                                placeholder="Provide a brief description of what this quiz covers, difficulty level, or any special instructions..."
                                maxlength="500"
                                rows="4"
                            ><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                            <div class="char-counter">
                                <span id="descCounter">0</span>/500 characters
                            </div>
                        </div>

                        <div id="quizPreview" class="quiz-preview" style="display: none;">
                            <div class="quiz-preview-title" id="previewTitle">Quiz Title</div>
                            <div class="quiz-preview-description" id="previewDescription">Quiz Description</div>
                        </div>
                    </div>

                    <div class="submit-section">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span id="submitText">🚀 Create Quiz</span>
                            <span id="loadingSpinner" style="display: none;">⏳ Creating...</span>
                        </button>
                        <a href="admin_dashboard.php" class="btn btn-secondary">
                            🔙 Cancel
                        </a>
                    </div>
                </form>
            <?php endif; ?>
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
        // Character counters
        const titleInput = document.getElementById('title');
        const descInput = document.getElementById('description');
        const titleCounter = document.getElementById('titleCounter');
        const descCounter = document.getElementById('descCounter');
        const quizPreview = document.getElementById('quizPreview');
        const previewTitle = document.getElementById('previewTitle');
        const previewDescription = document.getElementById('previewDescription');

        function updateCounters() {
            if (titleInput && titleCounter) {
                titleCounter.textContent = titleInput.value.length;
            }
            if (descInput && descCounter) {
                descCounter.textContent = descInput.value.length;
            }
        }

        function updatePreview() {
            if (titleInput.value.trim() || descInput.value.trim()) {
                quizPreview.style.display = 'block';
                previewTitle.textContent = titleInput.value.trim() || 'Quiz Title';
                previewDescription.textContent = descInput.value.trim() || 'No description provided';
                previewDescription.style.display = descInput.value.trim() ? 'block' : 'none';
            } else {
                quizPreview.style.display = 'none';
            }
        }

        // Event listeners
        if (titleInput) {
            titleInput.addEventListener('input', function() {
                updateCounters();
                updatePreview();
            });
        }

        if (descInput) {
            descInput.addEventListener('input', function() {
                updateCounters();
                updatePreview();
            });
        }

        // Form submission
        const form = document.getElementById('createQuizForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const submitBtn = document.getElementById('submitBtn');
                const submitText = document.getElementById('submitText');
                const loadingSpinner = document.getElementById('loadingSpinner');
                
                if (submitBtn && submitText && loadingSpinner) {
                    submitBtn.disabled = true;
                    submitText.style.display = 'none';
                    loadingSpinner.style.display = 'inline';
                }
            });
        }

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

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateCounters();
            updatePreview();
            
            // Add entrance animation
            const container = document.querySelector('.container');
            container.style.animation = 'slideUp 0.8s ease-out';
        });
    </script>
</body>
</html>

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
