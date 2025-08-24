<?php
require 'config.php';

$username = "admin";
$email = "2002032@icte.bdu.ac.bd";
$password = "1234";
$role = "admin";

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert into database
$stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $username, $email, $hashedPassword, $role);

if ($stmt->execute()) {
    echo "✅ Admin created successfully.";
} else {
    echo "❌ Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
