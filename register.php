<?php
// For development - remove in production
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'config.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    // Insert with role as 'user'
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
    $stmt->bind_param("sss", $username, $email, $hashedPassword);
    if ($stmt->execute()) {
        echo "<script>alert('Registration successful!'); window.location='login.php';</script>";
    } else {
        if ($conn->errno == 1062) {
            echo "<script>alert('Email already registered!'); window.location='register.php';</script>";
        } else {
            echo "Error: " . $stmt->error;
        }
    }
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Quiz App</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #667eea, #764ba2);
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
            animation: float 10s ease-in-out infinite;
        }

        .shape:nth-child(1) {
            width: 120px;
            height: 120px;
            top: 15%;
            left: 15%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 180px;
            height: 180px;
            top: 60%;
            right: 10%;
            animation-delay: 3s;
        }

        .shape:nth-child(3) {
            width: 90px;
            height: 90px;
            bottom: 25%;
            left: 75%;
            animation-delay: 6s;
        }

        .shape:nth-child(4) {
            width: 140px;
            height: 140px;
            top: 40%;
            left: 8%;
            animation-delay: 9s;
        }

        .shape:nth-child(5) {
            width: 70px;
            height: 70px;
            bottom: 15%;
            right: 25%;
            animation-delay: 2s;
        }

        .shape:nth-child(6) {
            width: 100px;
            height: 100px;
            top: 80%;
            left: 40%;
            animation-delay: 5s;
        }

        .register-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 480px;
            margin: 0 1rem;
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
            animation: slideInDown 0.8s ease-out;
        }

        .logo {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, #f093fb, #f5576c);
            border-radius: 50%;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: bounce 2s infinite;
        }

        form {
            background: rgba(255, 255, 255, 0.95);
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
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
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 20px 20px 0 0;
        }

        form:hover {
            transform: translateY(-8px);
            box-shadow: 0 35px 70px rgba(0, 0, 0, 0.25);
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

        .success-message {
            color: #059669;
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 0.9rem;
            border-left: 4px solid #059669;
            animation: slideInDown 0.5s ease-out;
        }

        .error-message {
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
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            background: #ffffff;
            transform: translateY(-2px);
        }

        input:focus + .input-icon {
            color: #667eea;
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
            color: #667eea;
        }

        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }

        .strength-weak { color: #e63946; }
        .strength-medium { color: #f59e0b; }
        .strength-strong { color: #059669; }

        .submit-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
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
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
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

        .login-link {
            display: block;
            text-align: center;
            color: #667eea;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 500;
            padding: 0.75rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .login-link:hover {
            color: #764ba2;
            background: rgba(102, 126, 234, 0.1);
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
            50% { transform: translateY(-40px) rotate(180deg); }
        }

        @keyframes bounce {
            0%, 100% { transform: scale(1) rotate(0deg); }
            50% { transform: scale(1.1) rotate(5deg); }
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
                width: 70px;
                height: 70px;
                font-size: 2rem;
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
        <div class="shape"></div>
    </div>

    <div class="register-container">
        <div class="form-header">
            <div class="logo">✨</div>
        </div>

        <form method="POST" action="" id="registerForm">
            <h2>Join Us</h2>
            <p class="subtitle">Create your account and start your quiz adventure</p>
            
            <div class="input-group">
                <div class="input-icon">👤</div>
                <input type="text" name="username" id="username" placeholder="Choose a username" required />
            </div>
            
            <div class="input-group">
                <div class="input-icon">📧</div>
                <input type="email" name="email" id="email" placeholder="Enter your email address" required />
            </div>
            
            <div class="input-group">
                <div class="input-icon">🔒</div>
                <input type="password" name="password" id="password" placeholder="Create a strong password" required />
                <button type="button" class="password-toggle" onclick="togglePassword()">👁️</button>
                <div class="password-strength" id="passwordStrength"></div>
            </div>
            
            <button type="submit" class="submit-btn" id="submitBtn">
                <span class="loading-spinner" id="loadingSpinner"></span>
                <span id="btnText">Create Account</span>
            </button>
            
            <div class="divider">
                <span>Already have an account?</span>
            </div>
            
            <a href="login.php" class="login-link">🚪 Sign in here</a>
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

        // Password strength checker
        function checkPasswordStrength(password) {
            const strengthIndicator = document.getElementById('passwordStrength');
            let strength = 0;
            let message = '';

            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;

            if (password.length === 0) {
                strengthIndicator.textContent = '';
                return;
            }

            switch (strength) {
                case 0:
                case 1:
                case 2:
                    message = '🔴 Weak password';
                    strengthIndicator.className = 'password-strength strength-weak';
                    break;
                case 3:
                case 4:
                    message = '🟡 Medium strength';
                    strengthIndicator.className = 'password-strength strength-medium';
                    break;
                case 5:
                    message = '🟢 Strong password';
                    strengthIndicator.className = 'password-strength strength-strong';
                    break;
            }
            strengthIndicator.textContent = message;
        }

        // Form submission with loading state
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const loadingSpinner = document.getElementById('loadingSpinner');
            const btnText = document.getElementById('btnText');
            
            // Show loading state
            submitBtn.disabled = true;
            loadingSpinner.style.display = 'inline-block';
            btnText.textContent = 'Creating Account...';
            
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

        // Password strength checking
        document.getElementById('password').addEventListener('input', function() {
            checkPasswordStrength(this.value);
        });

        // Username validation
        document.getElementById('username').addEventListener('input', function() {
            const username = this.value;
            if (username.length > 0 && username.length < 3) {
                this.style.borderColor = '#e63946';
            } else if (username.length >= 3) {
                this.style.borderColor = '#059669';
            } else {
                this.style.borderColor = '#e2e8f0';
            }
        });

        // Email validation
        document.getElementById('email').addEventListener('input', function() {
            const email = this.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email.length > 0 && !emailRegex.test(email)) {
                this.style.borderColor = '#e63946';
            } else if (emailRegex.test(email)) {
                this.style.borderColor = '#059669';
            } else {
                this.style.borderColor = '#e2e8f0';
            }
        });

        // Smooth animations on page load
        window.addEventListener('load', function() {
            document.querySelector('.register-container').style.opacity = '1';
        });
    </script>
</body>
</html>
