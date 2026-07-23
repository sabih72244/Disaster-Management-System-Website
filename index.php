<?php
include 'dbcon.php';
include 'login.php';

$sql = "SELECT dz.ZoneID, 
               dz.DisasterName AS Disastername, 
               COUNT(v.VictimID) AS total_victims, 
               MAX(v.Lastupdated) AS Lastupdated
        FROM disasterzones dz
        LEFT JOIN victims v ON dz.ZoneID = v.ZoneID
        GROUP BY dz.ZoneID, dz.DisasterName
        ORDER BY dz.ZoneID DESC
        LIMIT 3;";
$result = mysqli_query($conn, $sql);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

$jsonData = json_encode($data);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disaster Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #343a40;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
        }
        .header .logo {
            font-size: 24px;
            font-weight: bold;
        }
        .banner {
            background: url('./pics/disaster.jpg') no-repeat center center;
            background-size: cover;
            color: white;
            text-align: center;
            padding: 100px 20px;
        }
        .banner h1 {
            font-size: 48px;
            font-weight: bold;
        }
        .footer {
            background-color: #343a40;
            color: white;
            text-align: center;
            padding: 10px;
        }
        .section {
            padding: 40px 20px;
        }
        .card {
            margin: 20px 0;
        }
        .card {
            border: none;
            border-radius: 15px;
        }
        .card h5 {
            background-color: #007bff;
        }
        .card-body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .bg-primary {
            background-color: #007bff !important;
            color: #ffffff;
        }
        .card h5 {
            font-weight: bold;
        }
        .card p {
            margin-bottom: 0.5rem;
        }
        #chartContainer {
            max-width: 800px;
            margin: auto;
        }
        .chart {
            width: 1200px;
            height: 650px;
            padding: 50px;
            margin: 0 auto;
        }
    </style>
</head>
<body>

    <header class="header d-flex justify-content-between align-items-center">
        <div class="logo">Disaster Management System</div>
        <nav>
            <ul class="nav">
                <li class="nav-item"><a href="index.php" class="nav-link text-white">Home</a></li>
                <li class="nav-item"><a href="aboutus.php" class="nav-link text-white">About Us</a></li>
                <li class="nav-item"><a href="volunteer.php" class="nav-link text-white">Volunteer</a></li>
                <li class="nav-item"><a href="donateus.php" class="nav-link text-white">Donate Us</a></li>
            </ul>
        </nav>
        <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
    </header>
    

    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">Login</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">


                <form action="index.php" method="POST">
                    <div class="mb-3">
                        <label for="userType" class="form-label">Login as</label>
                        <select class="form-select" name="userType" id="userType">
                            <option value="admin">Admin</option>
                            <option value="volunteer">Volunteer</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="email" placeholder="Enter your email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" id="password" placeholder="Enter your password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Login</button>
                </form>
                </div>
            </div>
        </div>
    </div>
    <?php if (isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>

    <section class="banner">
        <h1>Disaster Management System</h1>
        <p>Efficiently coordinate and manage relief efforts during emergencies.</p>
    </section>

   
<section class="section">
    <h2 class="text-center">Disaster Updates</h2>
    <div class="row">
        <?php
        // Fetch disaster updates from the disasterzones table
        $sql = "SELECT DisasterName, Description, Lastupdated, location FROM disasterzones ORDER BY ZoneID DESC 
        LIMIT 3";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                ?>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $row['DisasterName']; ?></h5>
                            <p class="card-text">Update: <?php echo $row['Description']; ?></p>
                            <p class="card-text"><strong>Location:</strong> <?php echo $row['location']; ?></p>
                            <p class="card-text"><small class="text-muted">Last updated: <?php echo $row['Lastupdated']; ?></small></p>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo '<p class="text-center">No disaster updates available at the moment.</p>';
        }
        ?>
    </div>
</section>


<section>
    <div class="chart">
        <h2 class="text-center">Disaster Statistics</h2>
        <canvas id="myChart" class="chart"></canvas>
    </div>
    <script>
        // Data fetched from PHP
        const chartData = <?php echo $jsonData; ?>;
        const ctx = document.getElementById('myChart').getContext('2d');

        // Create chart with fetched data
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.map(row => row.Disastername),
                datasets: [{
                    label: 'Number of Victims',
                    data: chartData.map(row => row.total_victims),
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</section>

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

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
 
</body>
</html>
