<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dms";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Check if the user is an admin
    $sql_admin = "SELECT * FROM admins WHERE Email = ? AND Password = ?";
    $stmt = $conn->prepare($sql_admin);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result_admin = $stmt->get_result();
    
    // Check if the user is a volunteer
    $sql_volunteer = "SELECT * FROM volunteers WHERE Email = ? AND Password = ?";
    $stmt->prepare($sql_volunteer);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result_volunteer = $stmt->get_result();
    
    if ($result_admin->num_rows > 0) {
        // Admin credentials are correct
        $admin = $result_admin->fetch_assoc();
        $_SESSION['role'] = 'admin';
        $_SESSION['AdminId'] = $admin['AdminID'];
        $_SESSION['username'] = $email;
        
        // Redirect to admin portal
        header("Location: adminportal.php");
        exit;
    } elseif ($result_volunteer->num_rows > 0) {
        // Volunteer credentials are correct
        $volunteer = $result_volunteer->fetch_assoc();
        $_SESSION['role'] = 'volunteer';
        $_SESSION['VolunteerID'] = $volunteer['VolunteerID']; // Correct field from the database
        $_SESSION['username'] = $email;

        // Redirect to volunteer portal
        header("Location: volunteerportal.php");
        exit;
    } else {
        $error = "Invalid credentials!";
    }
}

// $conn->close();
?>
