<?php
$conn = new mysqli("localhost", "root", "", "campus_service_hub");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>