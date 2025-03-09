<?php
// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration for XAMPP (default settings)
$servername = "localhost";  // Usually 'localhost' for XAMPP
$username   = "root";       // Default username for XAMPP is 'root'
$password   = "";           // Default password for XAMPP is usually empty
$dbname     = "onlinevoting"; // The database name we created

// Create connection using MySQLi
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} //else {
   // echo "Connected successfully to the 'onlinevoting' database!";
//}
?>
