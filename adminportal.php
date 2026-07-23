<?php

session_start();
include 'dbcon.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Redirect to login page if not logged in
    header("Location: index.php");
    exit;
}
// Fetch volunteer name from the session
$adminName = isset($_SESSION['username']) ? $_SESSION['username'] : "Unknown";

// Handle disaster news updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['updateDisasterNews'])) {
    $DisasterName = $_POST['DisasterName'];
    $Description = $_POST['Description'];
    $location = $_POST['location'];

    // Insert disaster update into the database
    $insertUpdate = "INSERT INTO DisasterZones (DisasterName, Description,location) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insertUpdate);
    $stmt->bind_param("sss", $DisasterName, $Description, $location);
    if ($stmt->execute()) {
        $newsMessage = "Disaster updated!";
    } else {
        $newsErrorMessage = "Error updating.";
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assigntask'])) { 
    $volunteerID = $_POST['volunteerID'];
    $ZoneID = $_POST['ZoneID'];
    $taskDescription = $_POST['taskDescription'];

    // Insert disaster update into the database
    $insertUpdate = "INSERT INTO volunteerassignments (VolunteerID, ZoneID, TaskDescription) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insertUpdate);
    $stmt->bind_param("iis", $volunteerID, $ZoneID, $taskDescription);

    if ($stmt->execute()) {
        $assignmentID = $conn->insert_id; // Get the newly inserted AssignmentID

        // Insert log entry into volunteerassignmentlog
        $action = "Task Assigned";
        // Insert log entry into volunteerassignmentlog
        $insertLog = "INSERT INTO volunteerassignmentlog (AssignmentID, VolunteerID, ZoneID) VALUES (?, ?, ?)";
        $logStmt = $conn->prepare($insertLog);
        $logStmt->bind_param("iii", $assignmentID, $volunteerID, $ZoneID);


        if ($logStmt->execute()) {
            $newsMessage = "Task assigned and log updated!";
        } else {
            $newsErrorMessage = "Task assigned, but log update failed.";
        }
    } else {
        $newsErrorMessage = "Error assigning task.";
    }
}



if (isset($_GET['delete_id'])) {
    $deleteID = intval($_GET['delete_id']); // Get the ID from the URL
    $deleteQuery = "DELETE FROM volunteers WHERE volunteerid = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $deleteID);

    if ($stmt->execute()) {
        $deleteMessage = "Volunteer successfully removed!";
    } else {
        $deleteErrorMessage = "Error deleting volunteer.";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['addResource'])) {
    // Sanitize and retrieve form inputs
    $ResourceType = $_POST['ResourceType'] ?? '';
    $Quantity = $_POST['Quantity'] ?? '';
    $Location = $_POST['Location'] ?? '';

    // Check for required fields
    if (empty($ResourceType) || empty($Quantity)) {
        $errorMessage = "Resource Type and Quantity are required!";
    } else {
        // Insert resource into the database
        $insertResource = "INSERT INTO resources (ResourceType, Quantity, Location) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insertResource);

        if ($stmt) {
            $stmt->bind_param("sis", $ResourceType, $Quantity, $Location);

            if ($stmt->execute()) {
                $successMessage = "Resource added successfully!";
            } else {
                $errorMessage = "Error adding resource: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $errorMessage = "Error preparing statement: " . $conn->error;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['updateResource'])) {
    // Sanitize and retrieve form inputs
    $ResourceID = $_POST['ResourceID'] ?? '';
    $NewQuantity = $_POST['NewQuantity'] ?? '';

    // Check for required fields
    if (empty($ResourceID) || empty($NewQuantity)) {
        $errorMessage = "Both Resource ID and New Quantity are required!";
    } else {
        // Update the resource quantity in the database
        $updateQuantity =  "UPDATE resources SET Quantity = Quantity + ? , LastUpdated = CURRENT_TIMESTAMP WHERE ResourceID = ?";
        $stmt = $conn->prepare($updateQuantity);

        if ($stmt) {
            $stmt->bind_param("ii", $NewQuantity, $ResourceID);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $successMessage = "Resource quantity updated successfully!";
                } else {
                    $errorMessage = "Resource ID not found.";
                }
            } else {
                $errorMessage = "Error updating resource: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $errorMessage = "Error preparing statement: " . $conn->error;
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sendResource'])) {
    // Sanitize and retrieve form inputs
    $ResourceID = filter_input(INPUT_POST, 'ResourceID', FILTER_SANITIZE_NUMBER_INT);
    $NewQuantity = filter_input(INPUT_POST, 'NewQuantity', FILTER_SANITIZE_NUMBER_INT);

    // Check for required fields
    if (empty($ResourceID) || empty($NewQuantity)) {
        $errorMessage = "Both Resource ID and Quantity are required!";
    } else {
        // Subtract the quantity from the current quantity in the database
        $subtractQuantity = "UPDATE resources SET Quantity = Quantity - ? , LastUpdated = CURRENT_TIMESTAMP WHERE ResourceID = ?";
        $stmt = $conn->prepare($subtractQuantity);

        if ($stmt) {
            $stmt->bind_param("ii", $NewQuantity, $ResourceID);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $successMessage = "Resource sent successfully!";
                } else {
                    $errorMessage = "Resource ID not found or not enough stock.";
                }
            } else {
                $errorMessage = "Error sending resource: " . $stmt->error;
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
    <title>Admin Portal DMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header class="header d-flex justify-content-between align-items-center p-3 bg-primary text-white">
    <div class="logo">Disaster Management Admin Portal</div>
    <div class="user-info">
        Logged in as: <span id="adminName"><?= $adminName ?></span>
        <a href="index.php" class="btn btn-outline-light btn-sm ms-3">Sign Out</a>
    </div>
</header>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <nav class="nav flex-column bg-light p-3 rounded">
                <a href="#" class="nav-link active">Dashboard Overview</a>
                <a href="#updateDisasters" class="nav-link">Add Disasters</a>
                <a href="#disasterzones" class="nav-link">Disasters</a>
                <a href="#manageVolunteers" class="nav-link">Manage Volunteers</a>
                <a href="#assignTasks" class="nav-link">Assign Volunteer Tasks</a>
                <a href="#assignmentupdate" class="nav-link">Assignment Update </a>
                <a href="#Victims" class="nav-link">Victims information</a>
                <a href="#manageResources" class="nav-link">Manage Resources</a>
                <a href="#Donations" class="nav-link">Manage Donations</a>
            </nav>
        </div>

        <div class="col-md-9">
            <section id="updateDisasterNews" class="mt-5">
                <h2>Update Disaster News</h2>
                <form method="POST" id="disasterNewsForm">
                    <div class="mb-3">
                        <label for="Disastername" class="form-label">Disaster Name</label>
                        <input type="text" class="form-control" id="Disastername" name="DisasterName" required>
                    </div>
                    <div class="mb-3">
                        <label for="Description" class="form-label">Disaster Update</label>
                        <textarea class="form-control" id="Description" name="Description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <textarea class="form-control" id="location" name="location" rows="1" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" name="updateDisasterNews">Send Update</button>
                </form>
                <?php if (isset($newsMessage)) : ?>
                    <div class="alert alert-success mt-3"><?= $newsMessage ?></div>
                <?php elseif (isset($newsErrorMessage)) : ?>
                    <div class="alert alert-danger mt-3"><?= $newsErrorMessage ?></div>
                <?php endif; ?>
            </section>
            <section id="disasterzones" class="mt-5">
    <h2>Disasters</h2>
    <div style="max-height: 300px; overflow-y: auto;">
    <table class="table">
        <thead>
            <tr>
                <th>Zone ID</th>
                <th>Disaster Name</th>
                <th>Description</th>
                <th>Location</th>
                <th>Last Updated</th>
            </tr>
        </thead>
        <tbody>
            <!-- Dynamic Resource List Here -->
            <?php
            // Query to fetch resources
            $sql = "SELECT * FROM disasterzones";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $ZoneID = $row['ZoneID'];
                    $DisasterName = $row['DisasterName'];
                    $Description = $row['Description'];
                    $Location = $row['Location'];
                    $LastUpdated = $row['LastUpdated'];

                    echo '<tr>
                            <td>' . $ZoneID . '</td>
                            <td>' . $DisasterName . '</td>
                            <td>' . $Description . '</td>
                            <td>' . $Location . '</td>
                            <td>' . $LastUpdated . '</td>
                          </tr>';
                }
            } else {
                echo '<tr><td colspan="5">No Disasters found.</td></tr>';
            }
            ?>
        </tbody>
    </table>
    </div>
</section>
            <div class="container mt-5">
    <section id="manageVolunteers">
        <h2>Manage Volunteers</h2>
        
        <!-- Display messages -->
        <?php if (isset($deleteMessage)) : ?>
            <div class="alert alert-success"><?= $deleteMessage ?></div>
        <?php elseif (isset($deleteErrorMessage)) : ?>
            <div class="alert alert-danger"><?= $deleteErrorMessage ?></div>
        <?php endif; ?>
        <div style="max-height: 300px; overflow-y: auto;">
        <div class="table-container">
        <table class="table volunteer-table">
            <thead>
                <tr>
                    <th>Volunteer ID</th>
                    <th>Name</th>
                    <th>Age</th>
                    <th>Gender</th>
                    <th>Skills</th>
                    <th>CNIC</th>
                    <th>Contact Info</th>
                    <th>Email</th>
                    <th>Password</th>
                    <th>Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Query to fetch volunteers
                // Query to fetch volunteers along with their skills
                $sql = "SELECT 
                v.VolunteerID,
                v.Name,
                v.Age,
                v.Gender,
                GROUP_CONCAT(vs.Skill SEPARATOR ', ') AS Skills,    
                v.CNIC,
                v.Contact,
                v.Email,
                v.Password,
                v.Address
                FROM 
                volunteers v
                LEFT JOIN 
                volunteer_skills vs ON v.VolunteerID = vs.VolunteerID
                GROUP BY 
                v.VolunteerID";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
while ($row = $result->fetch_assoc()) {
$volunteerID = $row['VolunteerID'];
$name = $row['Name'];
$age = $row['Age'];
$gender = $row['Gender'];
$skills = $row['Skills']; 
$cnic = $row['CNIC'];
$contact = $row['Contact'];
$email = $row['Email'];
$password = $row['Password'];
$address = $row['Address'];

echo '<tr>
    <td>' . $volunteerID . '</td>
    <td>' . $name . '</td>
    <td>' . $age . '</td>
    <td>' . $gender . '</td>
    <td>' . $skills . '</td>
    <td>' . $cnic . '</td>
    <td>' . $contact . '</td>
    <td>' . $email . '</td>
    <td>' . $password . '</td>
    <td>' . $address . '</td>
    <td>
        <a href="?delete_id=' . $volunteerID . '" class="btn btn-danger text-light" onclick="return confirm(\'Are you sure you want to delete this volunteer?\')">Remove</a>
    </td>
  </tr>';
}
} else {
echo '<tr><td colspan="11">No volunteers found.</td></tr>';
}

                ?>
            </tbody>
        </table>
        </div>
        </div>
    </section>
</div>
<section id="assignTasks" class="mt-5">
                <h2>Assign Tasks to Volunteers</h2>
                <form method="POST" action="adminportal.php">
                    <div class="mb-3">
                        <label for="volunteerID" class="form-label">Volunteer ID</label>
                        <input type="text" class="form-control" name="volunteerID" id="volunteerID" required>
                    </div>
                    <div class="mb-3">
                        <label for="ZoneID" class="form-label">Zone ID</label>
                        <input type="text" class="form-control" name="ZoneID" id="ZoneID" required>
                    </div>
                    <div class="mb-3">
                        <label for="taskDescription" class="form-label">Task Description</label>
                        <textarea class="form-control" name="taskDescription" id="taskDescription" rows="3" required></textarea>
                    </div>
                    <button type="submit" name="assigntask" class="btn btn-primary">Assign Task</button>
                </form>
            </section>
            <div class="container mt-3">
            <section id="assignmentupdate" class="mt-5">
    <h2>Assignment Updates</h2>
    <div style="max-height: 250px; overflow-y: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>Assignment ID</th>
                    <th>Volunteer ID</th>
                    <th>Zone ID</th>
                    <th>Task status</th>
                    <th>Date & Time</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Query to fetch donations
                $sql = "SELECT AssignmentID,VolunteerID,ZoneID,Action,Date FROM volunteerassignmentlog";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $AssignmentID = $row['AssignmentID'];
                        $VolunteerID = $row['VolunteerID'];
                        $ZoneID = $row['ZoneID'];
                        $Action = $row['Action'];
                        $Date = $row['Date'];

                        echo '<tr>
                                <td>' . $AssignmentID . '</td>
                                <td>' . $VolunteerID . '</td>
                                <td>' . $ZoneID . '</td>
                                <td>' . $Action . '</td>
                                <td>' . $Date . '</td>
                              </tr>';
                    }
                } else {
                    echo '<tr><td colspan="4">No assigned assignments found.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</section>

            <section id="Victims">
        <h2>Victims</h2>       
        <div style="max-height: 300px; overflow-y: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>Victim ID</th>
                    <th>Name</th>
                    <th>Age</th>
                    <th>Gender</th>
                    <th>Location</th>
                    <th>Needs</th>
                    <th>Contact Info</th>
                    <th>Zone ID</th>

                </tr>
            </thead>
            <tbody>
                <?php
                // Query to fetch volunteers
                $sql = "SELECT * FROM victims";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $VictimID = $row['VictimID'];
                        $Name = $row['Name'];
                        $Age = $row['Age'];
                        $Gender = $row['Gender'];
                        $Location = $row['Location'];
                        $Needs = $row['Needs'];
                        $ContactInfo = $row['ContactInfo'];
                        $ZoneID = $row['ZoneID'];

                        echo '<tr>
                                <td>' . $VictimID . '</td>
                                <td>' . $Name . '</td>
                                <td>' . $Age . '</td>
                                <td>' . $Gender . '</td>
                                <td>' . $Location . '</td>
                                <td>' . $Needs . '</td>
                                <td>' . $ContactInfo . '</td>
                                <td>' . $ZoneID . '</td>
                              </tr>';
                    }
                } else {
                    echo '<tr><td colspan="11">No victims found.</td></tr>';
                }
                ?>
            </tbody>
        </table>
        </div>
    </section>
    </div>

            <section id="manageResources" class="mt-5">
    <h2>Manage Resources</h2>
    <button type="button" class="btn btn-success" id="addResourceButton">Add New Resource</button>
    <button type="button" class="btn btn-warning mb-3" id="updateResourceButton">Update Resource Quantity</button>
    <button type="button" class="btn btn-primary mb-3" id="SendResourceButton">Send Resource</button>
    <table class="table"  style="max-height: 250px; overflow-y: auto;">
        <thead>
            <tr>
                <th>Resource ID</th>
                <th>Resource Type</th>
                <th>Quantity</th>
                <th>Location</th>
            </tr>
        </thead>
        <tbody>
            <!-- Dynamic Resource List Here -->
            <?php
            // Query to fetch resources
            $sql = "SELECT * FROM resources";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $ResourceID = $row['ResourceID'];
                    $ResourceType = $row['ResourceType'];
                    $Quantity = $row['Quantity'];
                    $Location = $row['Location'];

                    echo '<tr>
                            <td>' . $ResourceID . '</td>
                            <td>' . $ResourceType . '</td>
                            <td>' . $Quantity . '</td>
                            <td>' . $Location . '</td>
                          </tr>';
                }
            } else {
                echo '<tr><td colspan="4">No Resource found.</td></tr>';
            }
            ?>
        </tbody>
    </table>
</section>

<section id="addResourceForm" class="mt-3" style="display: none;">
    <h3>Add New Resource</h3>
    <form method="POST" action="">
        <div class="form-group">
            <label for="ResourceType">Resource Type</label>
            <input type="text" class="form-control" id="ResourceType" name="ResourceType" placeholder="Enter Resource Type" required>
        </div>
        <div class="form-group">
            <label for="Quantity">Quantity</label>
            <input type="number" class="form-control" id="Quantity" name="Quantity" placeholder="Enter Quantity" required>
        </div>
        <div class="form-group">
            <label for="Location">Location</label>
            <input type="text" class="form-control" id="Location" name="Location" placeholder="Enter Location (optional)">
        </div>
        <button type="submit" class="btn btn-success" name="addResource">Add Resource</button>
        <button type="button" class="btn btn-secondary" id="cancelAddResource">Cancel</button>
    </form>
</section>
<section id="updateResourceForm" class="mt-3" style="display: none;">
    <h3>Update Resource Quantity</h3>
    <form method="POST" action="">
        <div class="form-group">
            <label for="ResourceID">Resource ID</label>
            <input type="number" class="form-control" id="ResourceID" name="ResourceID" placeholder="Enter Resource ID" required>
        </div>
        <div class="form-group">
            <label for="NewQuantity">New Quantity</label>
            <input type="number" class="form-control" id="NewQuantity" name="NewQuantity" placeholder="Enter New Quantity" required>
        </div>
        <button type="submit" class="btn btn-warning" name="updateResource">Update Quantity</button>
        <button type="button" class="btn btn-secondary" id="cancelUpdateResource">Cancel</button>
    </form>
</section>
<section id="SendResourceForm" class="mt-3" style="display: none;">
    <h3>Send Resource</h3>
    <form method="POST" action="">
        <div class="form-group">
            <label for="ResourceID">Resource ID</label>
            <input type="number" class="form-control" id="ResourceID" name="ResourceID" placeholder="Enter Resource ID" required>
        </div>
        <div class="form-group">
            <label for="NewQuantity">Quantity</label>
            <input type="number" class="form-control" id="NewQuantity" name="NewQuantity" placeholder="Enter Quantity" required>
        </div>
        <button type="submit" class="btn btn-warning" name="sendResource">Send</button>
        <button type="button" class="btn btn-secondary" id="cancelsendResource">Cancel</button>
    </form>

    <!-- Displaying success or error messages -->
    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success mt-3">
            <?php echo $successMessage; ?>
        </div>
    <?php elseif (isset($errorMessage)): ?>
        <div class="alert alert-danger mt-3">
            <?php echo $errorMessage; ?>
        </div>
    <?php endif; ?>
</section>

<section id="Donations" class="mt-5">
    <h2>Donations</h2>
    <div style="max-height: 250px; overflow-y: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>DONOR ID</th>
                    <th>Donor NAME</th>
                    <th>ACCOUNT NO</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Query to fetch donations
                $sql = "SELECT * FROM donations";
                $result = $conn->query($sql);
                $totalAmount = 0; // Variable to store the total amount

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $DonationID = $row['DonationID'];
                        $DonorName = $row['DonorName'];
                        $AccountNO = $row['AccountNO'];
                        $Amount = $row['Amount'];

                        // Add the current donation amount to the total
                        $totalAmount += $Amount;

                        echo '<tr>
                                <td>' . $DonationID . '</td>
                                <td>' . $DonorName . '</td>
                                <td>' . $AccountNO . '</td>
                                <td>' . $Amount . '</td>
                              </tr>';
                    }
                    // Display the total donation amount in a new row at the bottom
                    echo '<tr>
                            <td colspan="3" class="text-right"><strong>Total Amount</strong></td>
                            <td><strong>' . number_format($totalAmount, 2) . '</strong></td>
                          </tr>';
                } else {
                    echo '<tr><td colspan="4">No Donations found.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</section>


        </div>
    </div>
</div>

<footer class="footer mt-5 text-center p-3 bg-light">
    <p>&copy; 2024 Disaster Management System. All rights reserved.</p>
</footer>
<script>
    // Get elements
    const addResourceButton = document.getElementById('addResourceButton');
    const addResourceForm = document.getElementById('addResourceForm');
    const cancelAddResource = document.getElementById('cancelAddResource');

    // Show the form when the button is clicked
    addResourceButton.addEventListener('click', () => {
        addResourceForm.style.display = 'block';
        addResourceButton.style.display = 'none'; // Hide the button
    });

    // Hide the form when the cancel button is clicked
    cancelAddResource.addEventListener('click', () => {
        addResourceForm.style.display = 'none';
        addResourceButton.style.display = 'block'; // Show the button again
    });
</script>
<script>
    // Get elements
    const updateResourceButton = document.getElementById('updateResourceButton');
    const updateResourceForm = document.getElementById('updateResourceForm');
    const cancelUpdateResource = document.getElementById('cancelUpdateResource');

    // Show the form when the button is clicked
    updateResourceButton.addEventListener('click', () => {
        updateResourceForm.style.display = 'block';
        updateResourceButton.style.display = 'none'; // Hide the button
    });

    // Hide the form when the cancel button is clicked
    cancelUpdateResource.addEventListener('click', () => {
        updateResourceForm.style.display = 'none';
        updateResourceButton.style.display = 'block'; // Show the button again
    });
</script>
<script>
    // Get elements
    const SendResourceButton = document.getElementById('SendResourceButton');
    const SendResourceForm = document.getElementById('SendResourceForm');
    const cancelsendResource = document.getElementById('cancelsendResource');

    // Check if the SendResourceButton exists before adding event listener
    if (SendResourceButton) {
        // Show the form when the button is clicked
        SendResourceButton.addEventListener('click', () => {
            SendResourceForm.style.display = 'block';
            SendResourceButton.style.display = 'none'; // Hide the button
        });
    }

    // Hide the form when the cancel button is clicked
    cancelsendResource.addEventListener('click', () => {
        SendResourceForm.style.display = 'none';
        if (SendResourceButton) {
            SendResourceButton.style.display = 'block'; // Show the button again
        }
    });
</script>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
