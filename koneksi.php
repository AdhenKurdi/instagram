<?php
$servername = "localhost";
$username = "root";
$password = ""; // Password MySQL Anda (jika ada)
$dbname = "instagram";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
    print("error");
}
?>