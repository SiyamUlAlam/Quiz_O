
<?php
require 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin - Quiz App</title>
    <style>
        body {
            font-family: 'Inter', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea, #764ba2);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        h1 {
            color: #1f2937;
            text-align: center;
            margin-bottom: 30px;
            font-weight: 800;
            font-size: 2rem;
        }
        .status {
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
            font-weight: 600;
        }
        .success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        .error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }
        .info {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
        }
        .warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }
        .credentials {
            background: #f9fafb;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .credentials h3 {
            color: #374151;
            margin-bottom: 15px;
        }
        .credentials ul {
            list-style: none;
            padding: 0;
        }
        .credentials li {
            background: white;
            padding: 10px 15px;
            margin: 8px 0;
            border-radius: 8px;
            border-left: 4px solid #667eea;
            font-family: monospace;
        }
        .btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            margin: 10px 5px;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(103, 126, 234, 0.3);
        }
        .diagnostic {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .diagnostic h3 {
            color: #374151;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Admin Setup</h1>
        
        <?php
        // Admin credentials
        $username = "admin";
        $email = "2002032@icte.bdu.ac.bd";
        $password = "1234";
        $role = "admin";

        echo '<div class="diagnostic">';
        echo '<h3>🔍 System Diagnostics</h3>';
        
        // Test database connection
        try {
            echo '<div class="status success">✅ Database connection successful</div>';
            
            // Check if users table exists
            $result = $conn->query("SHOW TABLES LIKE 'users'");
            if ($result->num_rows > 0) {
                echo '<div class="status success">✅ Users table exists</div>';
            } else {
                echo '<div class="status error">❌ Users table does not exist</div>';
                echo '<div class="status info">Creating users table...</div>';
                
                $create_table = "CREATE TABLE users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) NOT NULL,
                    email VARCHAR(100) UNIQUE NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    role ENUM('admin', 'user') DEFAULT 'user',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
                
                if ($conn->query($create_table)) {
                    echo '<div class="status success">✅ Users table created successfully</div>';
                } else {
                    echo '<div class="status error">❌ Error creating users table: ' . $conn->error . '</div>';
                }
            }
            
            // Check if admin already exists
            $stmt = $conn->prepare("SELECT id, username, email, role FROM users WHERE email = ? OR role = 'admin'");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $admin_check = $stmt->get_result();
            
            if ($admin_check->num_rows > 0) {
                echo '<div class="status warning">⚠️ Admin user already exists</div>';
                
                while ($existing_admin = $admin_check->fetch_assoc()) {
                    echo '<div class="credentials">';
                    echo '<h3>Existing Admin Details:</h3>';
                    echo '<ul>';
                    echo '<li><strong>ID:</strong> ' . $existing_admin['id'] . '</li>';
                    echo '<li><strong>Username:</strong> ' . $existing_admin['username'] . '</li>';
                    echo '<li><strong>Email:</strong> ' . $existing_admin['email'] . '</li>';
                    echo '<li><strong>Role:</strong> ' . $existing_admin['role'] . '</li>';
                    echo '</ul>';
                    echo '</div>';
                }
                
                // Test login with existing admin
                $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->store_result();
                
                if ($stmt->num_rows === 1) {
                    $stmt->bind_result($id, $username_db, $hashedPassword, $role_db);
                    $stmt->fetch();
                    
                    if (password_verify($password, $hashedPassword)) {
                        echo '<div class="status success">✅ Admin login test successful</div>';
                    } else {
                        echo '<div class="status error">❌ Password verification failed - updating password...</div>';
                        
                        // Update password
                        $newHashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
                        $update_stmt->bind_param("ss", $newHashedPassword, $email);
                        
                        if ($update_stmt->execute()) {
                            echo '<div class="status success">✅ Admin password updated successfully</div>';
                        } else {
                            echo '<div class="status error">❌ Error updating password: ' . $update_stmt->error . '</div>';
                        }
                        $update_stmt->close();
                    }
                }
                
            } else {
                echo '<div class="status info">📝 Creating new admin user...</div>';
                
                // Hash the password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert into database
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $username, $email, $hashedPassword, $role);
                
                if ($stmt->execute()) {
                    echo '<div class="status success">✅ Admin created successfully!</div>';
                } else {
                    echo '<div class="status error">❌ Error creating admin: ' . $stmt->error . '</div>';
                }
            }
            
            // Show login credentials
            echo '<div class="credentials">';
            echo '<h3>🔑 Admin Login Credentials:</h3>';
            echo '<ul>';
            echo '<li><strong>Email:</strong> ' . $email . '</li>';
            echo '<li><strong>Password:</strong> ' . $password . '</li>';
            echo '<li><strong>Role:</strong> ' . $role . '</li>';
            echo '</ul>';
            echo '</div>';
            
            // File system check
            echo '<h3>📁 File System Check</h3>';
            $files_to_check = [
                'login.php' => 'Login page',
                'admin_dashboard.php' => 'Admin dashboard',
                'auth.php' => 'Authentication functions',
                'config.php' => 'Database configuration'
            ];
            
            foreach ($files_to_check as $file => $description) {
                if (file_exists($file)) {
                    echo '<div class="status success">✅ ' . $description . ' (' . $file . ') exists</div>';
                } else {
                    echo '<div class="status error">❌ ' . $description . ' (' . $file . ') missing</div>';
                }
            }
            
        } catch (Exception $e) {
            echo '<div class="status error">❌ Database error: ' . $e->getMessage() . '</div>';
        }
        
        echo '</div>';
        
        // Navigation buttons
        echo '<div style="text-align: center; margin-top: 30px;">';
        echo '<a href="login.php" class="btn">🚀 Go to Login</a>';
        echo '<a href="admin_dashboard.php" class="btn">📊 Admin Dashboard</a>';
        echo '<a href="index.php" class="btn">🏠 Home</a>';
        echo '</div>';
        
        $conn->close();
        ?>
    </div>
</body>
</html>
