<?php
include 'dbcon.php';
include 'header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $name = $_POST['name'] ?? '';
    $age = $_POST['age'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $skills = $_POST['skills'] ?? '';
    $cnic = $_POST['cnic'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $address = $_POST['address'] ?? '';

    // Check for required fields
    if (empty($name) || empty($age) || empty($gender) || empty($cnic) || empty($contact) || empty($email) || empty($password) || empty($address)) {
        $errorMessage = "All fields are required!";
    } else {
        // Insert volunteer into the database
        $volunteerSignup = "INSERT INTO volunteers (Name, Age, Gender, CNIC, Contact, Email, Password, Address)  
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($volunteerSignup);

        if ($stmt) {
            $stmt->bind_param("sissssss", $name, $age, $gender, $cnic, $contact, $email, $password, $address);

            if ($stmt->execute()) {
                $volunteerID = $stmt->insert_id; // Get the last inserted VolunteerID

                if (!empty($skills)) {
                    $skillsArray = explode(",", $skills);
                    $skillInsertQuery = "INSERT INTO volunteer_skills (VolunteerID, Skill) VALUES (?, ?)";

                    $skillStmt = $conn->prepare($skillInsertQuery);

                    foreach ($skillsArray as $skill) {
                        $skill = trim($skill); // Remove any extra spaces
                        $skillStmt->bind_param("is", $volunteerID, $skill);

                        if (!$skillStmt->execute()) {
                            $errorMessage = "Error inserting skill: " . $skillStmt->error;
                        }
                    }

                    $skillStmt->close();
                }

                $successMessage = "Volunteer registered successfully!";
            } else {
                $errorMessage = "Error registering volunteer: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $errorMessage = "Error preparing statement: " . $conn->error;
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer - Disaster Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    
<section class="banner">
    <h1>Join Us as a Volunteer</h1>
    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#signupModal">Sign Up</button>
</section>

<section class="section">
    <h2 class="text-center">Why Volunteer?</h2>
    <p>
        Volunteering with Disaster Management System is your chance to make a tangible difference in your community. Be a part of a dedicated team providing relief and rebuilding lives after disasters. Not only does volunteering help those in need, but it also fosters personal growth, builds leadership skills, and enhances your connection with the community.
    </p>

    <h2 class="text-center">Benefits of Volunteering</h2>
    <ul>
        <li>Contribute to a meaningful cause.</li>
        <li>Gain valuable skills and experience.</li>
        <li>Make lasting connections with like-minded individuals.</li>
        <li>Be part of a mission to build a resilient Pakistan.</li>
    </ul>
</section>

<!-- Volunteer Sign-Up Modal -->
<div class="modal fade" id="signupModal" tabindex="-1" aria-labelledby="signupModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="signupModalLabel">Volunteer Sign-Up</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="volunteer.php">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="name" id="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="age" class="form-label">Age</label>
                        <input type="number" class="form-control" name="age" id="age" required>
                    </div>
                    <div class="mb-3">
                        <label for="gender" class="form-label">Gender</label>
                        <select class="form-select" name="gender" id="gender" required>
                            <option value="">Select</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="skills" class="form-label">Skills</label>
                        <input type="text" class="form-control" name="skills" id="skills">
                    </div>
                    <div class="mb-3">
                        <label for="cnic" class="form-label">CNIC Number</label>
                        <input type="text" class="form-control" name="cnic" id="cnic" required>
                    </div>
                    <div class="mb-3">
                        <label for="contact" class="form-label">Contact Number</label>
                        <input type="text" class="form-control" name="contact" id="contact" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" id="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" name="address" id="address" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" name="submit">Sign Up</button>
                </form>
            </div>
        </div>
    </div>
</div>

<footer class="footer">
    <p>&copy; 2024 Disaster Management System. All rights reserved. Serving Pakistan with dedication.</p>
    <div class="social-icons">
        <a href="https://facebook.com" target="_blank" class="text-white mx-2">
            <i class="bi bi-facebook"></i> Facebook
        </a>
        <a href="https://instagram.com" target="_blank" class="text-white mx-2">
            <i class="bi bi-instagram"></i> Instagram
        </a>
        <a href="mailto:info@disastermanagement.pk" class="text-white mx-2">
            <i class="bi bi-envelope"></i> Email
        </a>
        <a href="tel:+921234567890" class="text-white mx-2">
            <i class="bi bi-telephone"></i> Phone
        </a>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
