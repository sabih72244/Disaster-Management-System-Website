<?php
include 'dbcon.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];

    $query = "INSERT INTO users (username, password, email) VALUES ('$username', '$password', '$email')";

    if ($conn->query($query) === TRUE) {
        echo "Registration successful!";
        header("Location: ../index.html");
    } else {
        echo "Error: " . $query . "<br>" . $conn->error;
    }
}
?>
