<?php
$host = "quiz-db-server.mysql.database.azure.com";
$user = "siyam";
$pass = "Siyam@32"; 
$db   = "quizdb"; 

// Enable exceptions for mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $db);
    $conn->set_charset("utf8mb4"); 
} catch (mysqli_sql_exception $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
