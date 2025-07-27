<?php
$password = "1111"; // Replace this with your desired admin password
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Hashed password: " . $hash;
?>
