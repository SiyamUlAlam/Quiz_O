<?php
require 'config.php';
session_start();
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $username, $hashedPassword, $role);
        $stmt->fetch();
        if (password_verify($password, $hashedPassword)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
            if ($role === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: user_dashboard.php");
            }
            exit;
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "No user found with that email.";
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Quiz App</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
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
            animation: float 8s ease-in-out infinite;
        }

        .shape:nth-child(1) {
            width: 100px;
            height: 100px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 150px;
            height: 150px;
            top: 70%;
            right: 15%;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            width: 80px;
            height: 80px;
            bottom: 30%;
            left: 80%;
            animation-delay: 4s;
        }

        .shape:nth-child(4) {
            width: 120px;
            height: 120px;
            top: 50%;
            left: 5%;
            animation-delay: 6s;
        }

        .shape:nth-child(5) {
            width: 60px;
            height: 60px;
            bottom: 10%;
            right: 30%;
            animation-delay: 1s;
        }

        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 450px;
            margin: 0 1rem;
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
            animation: slideInDown 0.8s ease-out;
        }

        .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #ff9a9e, #fecfef);
            border-radius: 50%;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            animation: pulse 2s infinite;
        }

        form {
            background: rgba(255, 255, 255, 0.95);
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(15px);
            transform: translateY(0);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            animation: slideInUp 0.8s ease-out;
        }

        form::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #6e8efb, #a777e3);
            border-radius: 20px 20px 0 0;
        }

        form:hover {
            transform: translateY(-8px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
        }

        h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1a1a40;
            text-align: center;
            margin-bottom: 0.5rem;
            letter-spacing: -0.025em;
        }

        .subtitle {
            text-align: center;
            color: #64748b;
            margin-bottom: 2rem;
            font-size: 1rem;
        }

        .error {
            color: #e63946;
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 0.9rem;
            border-left: 4px solid #e63946;
            animation: shake 0.5s ease-in-out;
        }

        .input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.2rem;
            transition: color 0.3s ease;
            z-index: 1;
        }

        input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            color: #1a1a40;
            background: #f8fafc;
            transition: all 0.3s ease;
            position: relative;
        }

        input:focus {
            outline: none;
            border-color: #6e8efb;
            box-shadow: 0 0 0 4px rgba(110, 142, 251, 0.1);
            background: #ffffff;
            transform: translateY(-2px);
        }

        input:focus + .input-icon {
            color: #6e8efb;
        }

        input::placeholder {
            color: #94a3b8;
            transition: opacity 0.3s ease;
        }

        input:focus::placeholder {
            opacity: 0.7;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            font-size: 1.1rem;
            transition: color 0.3s ease;
            z-index: 2;
        }

        .password-toggle:hover {
            color: #6e8efb;
        }

        .submit-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #6e8efb, #a777e3);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-bottom: 1.5rem;
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
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(110, 142, 251, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none !important;
        }

        .divider {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e2e8f0;
            z-index: 1;
        }

        .divider span {
            background: rgba(255, 255, 255, 0.95);
            padding: 0 1rem;
            color: #64748b;
            font-size: 0.9rem;
            position: relative;
            z-index: 2;
        }

        .register-link {
            display: block;
            text-align: center;
            color: #6e8efb;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 500;
            padding: 0.75rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .register-link:hover {
            color: #a777e3;
            background: rgba(110, 142, 251, 0.1);
            transform: translateY(-1px);
        }

        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-right: 8px;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(180deg); }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        @keyframes slideInDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 480px) {
            form {
                margin: 1rem;
                padding: 2rem;
            }

            h2 {
                font-size: 2rem;
            }

            .logo {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
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

    <div class="login-container">
        <div class="form-header">
            <div class="logo">🧠</div>
        </div>

        <form method="POST" action="" id="loginForm">
            <h2>Welcome Back</h2>
            <p class="subtitle">Sign in to continue your quiz journey</p>
            
            <?php if (!empty($error)) echo "<div class='error'>⚠️ $error</div>"; ?>
            
            <div class="input-group">
                <div class="input-icon">📧</div>
                <input type="email" name="email" id="email" placeholder="Enter your email address" required />
            </div>
            
            <div class="input-group">
                <div class="input-icon">🔒</div>
                <input type="password" name="password" id="password" placeholder="Enter your password" required />
                <button type="button" class="password-toggle" onclick="togglePassword()">👁️</button>
            </div>
            
            <button type="submit" class="submit-btn" id="submitBtn">
                <span class="loading-spinner" id="loadingSpinner"></span>
                <span id="btnText">Sign In</span>
            </button>
            
            <div class="divider">
                <span>New to Quiz App?</span>
            </div>
            
            <a href="register.php" class="register-link">✨ Create your account</a>
        </form>
    </div>

    <script>
        // Password toggle functionality
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.querySelector('.password-toggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                toggleBtn.textContent = '👁️';
            }
        }

        // Form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const loadingSpinner = document.getElementById('loadingSpinner');
            const btnText = document.getElementById('btnText');
            
            // Show loading state
            submitBtn.disabled = true;
            loadingSpinner.style.display = 'inline-block';
            btnText.textContent = 'Signing In...';
            
            // Note: Form will submit normally, this just provides visual feedback
        });

        // Input focus animations
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-2px)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });

        // Smooth animations on page load
        window.addEventListener('load', function() {
            document.querySelector('.login-container').style.opacity = '1';
        });
    </script>
</body>
</html>
