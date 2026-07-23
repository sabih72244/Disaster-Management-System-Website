<?php
// Start the session to store volunteer info
session_start();

// Include the database connection
include('dbcon.php');

// Ensure the user is logged in as a volunteer
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'volunteer') {
    // Redirect to login page if not logged in
    header("Location: index.php");
    exit;
}


// Fetch volunteer name from the session
$volunteerName = isset($_SESSION['username']) ? $_SESSION['username'] : "Unknown Volunteer";

// Ensure VolunteerID exists in the session
if (isset($_SESSION['VolunteerID']) && !empty($_SESSION['VolunteerID'])) {
    $VolunteerID = $_SESSION['VolunteerID'];
} else {
    // Redirect to login page if VolunteerID is not set
    header("Location: index.php");
    exit;
}

// Handle victim registration form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registerVictim'])) {
    $victimName = $_POST['victimName'];
    $victimAge = $_POST['victimAge'];
    $victimGender = $_POST['victimGender'];
    $victimLocation = $_POST['victimLocation'];
    $victimNeeds = $_POST['victimNeeds'];
    $victimContact = $_POST['victimContact'];
    $zoneid = $_POST['zoneid'];

    // Insert victim into the Victims table
    $insertVictim = "INSERT INTO Victims (Name, Age, Gender, Location, Needs, ContactInfo, ZoneID) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertVictim);
    $stmt->bind_param("sisssss", $victimName, $victimAge, $victimGender, $victimLocation, $victimNeeds, $victimContact, $zoneid);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $successMessage = "Victim registered successfully!";
    } else {
        $errorMessage = "Error registering victim.";
    }
}
// Handle task completion and logging
if (isset($_GET['assignment_id'])) {
    $assignmentID = intval($_GET['assignment_id']);

    // Check if the task is already marked as done
    $checkTaskQuery = "SELECT * FROM volunteerassignmentlog WHERE AssignmentID = ? AND Action = 'Task Done'";
    $checkTaskStmt = $conn->prepare($checkTaskQuery);
    $checkTaskStmt->bind_param("i", $assignmentID);
    $checkTaskStmt->execute();
    $checkTaskResult = $checkTaskStmt->get_result();

    if ($checkTaskResult->num_rows > 0) {
        $errorMessage = "Your task is already marked as done.";
    } else {
        // Retrieve ZoneID dynamically for logging
        $zoneQuery = "SELECT ZoneID FROM volunteerassignments WHERE AssignmentID = ?";
        $zoneStmt = $conn->prepare($zoneQuery);
        $zoneStmt->bind_param("i", $assignmentID);
        $zoneStmt->execute();
        $zoneResult = $zoneStmt->get_result();

        if ($zoneResult->num_rows > 0) {
            $row = $zoneResult->fetch_assoc();
            $zoneID = $row['ZoneID'];

            // Insert action into volunteerassignmentlog
            $action = "Task Done";
            $logInsert = "INSERT INTO volunteerassignmentlog (AssignmentID, VolunteerID, ZoneID, Action, Date) 
                          VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
            $logStmt = $conn->prepare($logInsert);
            $logStmt->bind_param("iiis", $assignmentID, $VolunteerID, $zoneID, $action);

            if ($logStmt->execute()) {
                $successMessage = "Task marked as done successfully!";
            } else {
                $errorMessage = "Error logging task completion.";
            }
        } else {
            $errorMessage = "Invalid Assignment ID or Zone not found.";
        }
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .header {
            background-color: #007bff;
            color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            color: #0056b3;
        }
        .table {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        footer {
            background-color: #e9ecef;
            color: #6c757d;
        }
        .btn-primary, .btn-primary:hover {
            background-color: #007bff;
            border-color: #007bff;
        }
        .sign-out {
            text-decoration: none;
            color: white;
            font-weight: bold;
            margin-left: 20px;
        }
        .sign-out:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<header class="header d-flex justify-content-between align-items-center p-3">
    <div class="logo">Volunteer Portal</div>
    <div class="user-info">
        Logged in as: <span id="volunteerName"><?= htmlspecialchars($volunteerName) ?></span>
        <a href="index.php" class="btn btn-outline-light btn-sm ms-3">Sign Out</a>
    </div>
</header>

<div class="container mt-4">
    <h1>Welcome to the Volunteer Portal</h1>

    <!-- Assigned Tasks Section -->
    <section id="assignedTasks" class="mt-5">
        <h2>Assigned Tasks</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Assignment ID</th>
                    <th>Volunteer ID</th>
                    <th>Zone ID</th>
                    <th>Disaster Name</th>
                    <th>Location</th>
                    <th>Task</th>
                    <th>Assign Date</th>
                    <th>Update Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Query to fetch the most recent assignment for the logged-in volunteer
                $sql = "SELECT va.AssignmentID, va.VolunteerID, va.ZoneID, va.TaskDescription, va.AssignmentDate, dz.DisasterName, dz.location
                        FROM volunteerassignments va
                        JOIN disasterzones dz ON va.ZoneID = dz.ZoneID
                        WHERE va.VolunteerID = ? 
                        ORDER BY va.AssignmentID DESC 
                        LIMIT 1";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $VolunteerID);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<tr>
                                <td>' . ($row['AssignmentID']) . '</td>
                                <td>' . ($row['VolunteerID']) . '</td>
                                <td>' . ($row['ZoneID']) . '</td>
                                <td>' . ($row['DisasterName']) . '</td>
                                <td>' . ($row['location']) . '</td>
                                <td>' . ($row['TaskDescription']) . '</td>
                                <td>' . ($row['AssignmentDate']) . '</td>
                                <td>
                                    <a href="?assignment_id=' .($row['AssignmentID']) . '" class="btn btn-danger text-light">Task Done</a>
                                </td>
                            </tr>';
                    }
                } else {
                    echo '<tr><td colspan="7">No task assigned.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </section>

    <!-- Register Victims Section -->
    <section id="registerVictims" class="mt-5">
        <h2>Register Victims</h2>
        <form method="POST" id="victimForm">
            <div class="mb-3">
                <label for="victimName" class="form-label">Name</label>
                <input type="text" class="form-control" id="victimName" name="victimName" required>
            </div>
            <div class="mb-3">
                <label for="victimAge" class="form-label">Age</label>
                <input type="number" class="form-control" id="victimAge" name="victimAge" required>
            </div>
            <div class="mb-3">
                <label for="victimGender" class="form-label">Gender</label>
                <select class="form-select" id="victimGender" name="victimGender" required>
                    <option value="" disabled selected>Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="victimLocation" class="form-label">Location</label>
                <input type="text" class="form-control" id="victimLocation" name="victimLocation" required>
            </div>
            <div class="mb-3">
                <label for="victimNeeds" class="form-label">Needs</label>
                <textarea class="form-control" id="victimNeeds" name="victimNeeds" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label for="victimContact" class="form-label">Contact Info</label>
                <input type="tel" class="form-control" id="victimContact" name="victimContact" required>
            </div>
            <div class="mb-3">
                <label for="zoneid" class="form-label">Zone ID</label>
                <input type="text" class="form-control" id="zoneid" name="zoneid" required>
            </div>
            <button type="submit" class="btn btn-primary" name="registerVictim">Add Victim</button>
        </form>
        <?php if (isset($successMessage)) : ?>
            <div class="alert alert-success mt-3"><?= htmlspecialchars($successMessage) ?></div>
        <?php elseif (isset($errorMessage)) : ?>
            <div class="alert alert-danger mt-3"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>
    </section>
</div>

<footer class="text-center py-3">
    <p>&copy; 2024 Disaster Management System. All Rights Reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
