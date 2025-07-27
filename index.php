<?php
session_start();
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$isLoggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Quiz App Home</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef2f3;
            margin: 0;
            padding: 0;
        }
        header {
            background: #0078D7;
            color: white;
            padding: 20px;
            text-align: center;
        }
        footer {
            background: #222;
            color: white;
            text-align: center;
            padding: 12px;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        .container {
            max-width: 700px;
            margin: 80px auto 100px;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .btn {
            display: inline-block;
            margin: 10px;
            padding: 12px 25px;
            border-radius: 6px;
            text-decoration: none;
            color: white;
            font-weight: bold;
        }
        .login { background: #0078D7; }
        .register { background: #28a745; }
        .quiz { background: #17a2b8; }
        .score { background: #6f42c1; }
        .admin { background: #fd7e14; }
        .logout { background: #dc3545; }
    </style>
</head>
<body>

<header>
    <h1>Welcome to Quiz App</h1>
    <?php if ($isLoggedIn): ?>
        <p>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo $_SESSION['role']; ?>)</p>
    <?php else: ?>
        <p>Please login or register to continue</p>
    <?php endif; ?>
</header>

<div class="container">
    <?php if (!$isLoggedIn): ?>
        <a href="login.php" class="btn login">Login</a>
        <a href="register.php" class="btn register">Register</a>
    <?php else: ?>
        <a href="take_quiz.php" class="btn quiz">Take a Quiz</a>
        <a href="view_scores.php" class="btn score">View Scores</a>

        <?php if ($isAdmin): ?>
            <a href="admin_dashboard.php" class="btn admin">Admin Dashboard</a>
        <?php endif; ?>

        <a href="logout.php" class="btn logout">Logout</a>
    <?php endif; ?>
</div>

<footer>
    &copy; <?php echo date("Y"); ?> Quiz App. All rights reserved.
</footer>

</body>
</html>
