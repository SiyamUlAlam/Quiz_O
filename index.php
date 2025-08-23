<?php
session_start();
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$isLoggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz App Home</title>
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
            margin: 0;
            overflow-x: hidden;
        }

        header {
            background: linear-gradient(135deg, #1a1a40, #2d2d5f);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }

        header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            animation: shimmer 3s ease-in-out infinite;
        }

        header h1 {
            font-size: 3rem;
            font-weight: 700;
            letter-spacing: -0.025em;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }

        header p {
            font-size: 1.2rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .user-info {
            background: rgba(255, 255, 255, 0.1);
            padding: 1rem 2rem;
            border-radius: 50px;
            display: inline-block;
            margin-top: 1rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .container {
            max-width: 800px;
            margin: 3rem auto;
            padding: 3rem;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            text-align: center;
            transform: translateY(0);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #6e8efb, #a777e3);
            border-radius: 20px 20px 0 0;
        }

        .container:hover {
            transform: translateY(-10px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
        }

        .welcome-text {
            font-size: 1.5rem;
            color: #1a1a40;
            margin-bottom: 2rem;
            font-weight: 600;
        }

        .btn-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .btn {
            display: block;
            padding: 1rem 2rem;
            border-radius: 12px;
            text-decoration: none;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: none;
            cursor: pointer;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .btn:active {
            transform: translateY(0);
        }

        .login { 
            background: linear-gradient(135deg, #667eea, #764ba2);
        }
        .register { 
            background: linear-gradient(135deg, #f093fb, #f5576c);
        }
        .quiz { 
            background: linear-gradient(135deg, #4facfe, #00f2fe);
        }
        .score { 
            background: linear-gradient(135deg, #a8edea, #fed6e3);
            color: #1a1a40;
        }
        .admin { 
            background: linear-gradient(135deg, #ffecd2, #fcb69f);
            color: #1a1a40;
        }
        .logout { 
            background: linear-gradient(135deg, #ff9a9e, #fecfef);
            color: #1a1a40;
        }

        footer {
            background: linear-gradient(135deg, #1a1a40, #2d2d5f);
            color: white;
            text-align: center;
            padding: 1rem;
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.2);
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .footer-info {
            text-align: left;
        }

        .footer-info h4 {
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0.2rem;
            color: #a777e3;
        }

        .footer-info p {
            font-size: 0.75rem;
            opacity: 0.9;
            margin: 0.1rem 0;
            line-height: 1.2;
        }

        .footer-copyright {
            text-align: right;
            font-size: 0.75rem;
            opacity: 0.8;
        }

        /* Add bottom margin to container to prevent footer overlap */
        .container {
            margin-bottom: 120px;
        }

        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
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
            right: 10%;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%) rotate(45deg); }
            100% { transform: translateX(100%) rotate(45deg); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        @media (max-width: 768px) {
            header h1 {
                font-size: 2rem;
            }
            
            .container {
                margin: 2rem 1rem;
                padding: 2rem;
                margin-bottom: 140px;
            }

            .btn-grid {
                grid-template-columns: 1fr;
            }

            .footer-content {
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
            }

            .footer-info {
                text-align: center;
            }

            .footer-copyright {
                text-align: center;
            }

            footer {
                padding: 0.8rem;
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
    </div>

<header>
    <h1>Welcome to Quiz App</h1>
    <?php if ($isLoggedIn): ?>
        <div class="user-info">
            <p>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo ucfirst($_SESSION['role']); ?>)</p>
        </div>
    <?php else: ?>
        <p>Discover knowledge through interactive quizzes</p>
    <?php endif; ?>
</header>

<div class="container">
    <?php if (!$isLoggedIn): ?>
        <div class="welcome-text">Join our QUIZ APP and enjoy interactive quizzes!</div>
        <div class="btn-grid">
            <a href="login.php" class="btn login">🔐 Login</a>
            <a href="register.php" class="btn register">📝 Register</a>
        </div>
    <?php else: ?>
        <div class="welcome-text">Ready to test your knowledge?</div>
        <div class="btn-grid">
            <a href="quiz.php" class="btn quiz">🧠 Take a Quiz</a>
            <a href="view_scores.php" class="btn score">📊 View Scores</a>

            <?php if ($isAdmin): ?>
                <a href="admin_dashboard.php" class="btn admin">⚙️ Admin Dashboard</a>
            <?php endif; ?>

            <a href="logout.php" class="btn logout">🚪 Logout</a>
        </div>
    <?php endif; ?>
</div>

<footer>
    <div class="footer-content">
        <div class="footer-info">
            <h4>Developed by</h4>
            <p><strong>Siyam Ul Alam</strong></p>
            <p>Gazipur Digital University</p>
            <p>📧 2002032@icte.bdu.ac.bd</p>
        </div>
        <div class="footer-copyright">
            <p>&copy; <?php echo date("Y"); ?> Quiz App. All rights reserved.</p>
        </div>
    </div>
</footer>

</body>
</html>