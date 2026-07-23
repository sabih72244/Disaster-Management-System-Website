<?php
include 'dbcon.php';
include 'header.php';
$responseMessage = "DONATION SUCCESSFUL";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle donation form submission
    $DonorName = $_POST['DonorName'];
    $AccountNO = $_POST['AccountNO'];
    $Amount = $_POST['Amount'];

    $sql = "INSERT INTO donations (DonorName, AccountNO, Amount, DonationDate) 
            VALUES ('$DonorName', '$AccountNO', $Amount, CURRENT_TIMESTAMP)";
    
    if ($conn->query($sql)) {
        $responseMessage = "success";
    } else {
        $responseMessage = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate Us - Disaster Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        #thankYouMessage {
            display: none;
            text-align: center;
            margin-top: 20px;
            padding: 20px;
            border: 1px solid #28a745;
            background-color: #d4edda;
            color: #155724;
            font-size: 1.2rem;
            border-radius: 8px;
        }
        #thankYouMessage.show {
            display: block;
            animation: fadeOut 5s forwards;
        }
        @keyframes fadeOut {
            0% {
                opacity: 1;
            }
            100% {
                opacity: 0;
            }
        }
        .bannerd {
    background: url('./pics/donate.jpg') no-repeat center center;
    background-size: cover;
    color: white;
    text-align: center;
    padding: 100px 20px;
}

.bannerd h1 {
    font-size: 48px;
    font-weight: bold;
}
    </style>
</head>
<body>

    <section class="bannerd">
        <h1>Donate Us</h1>
        <p>Your contribution makes a big difference!</p>
    </section>

    <section class="section">
        <h2 class="text-center">Make a Donation</h2>
        <?php if ($responseMessage === "success"): ?>
            <div id="thankYouMessage" class="show">
                <h2>Thank You!</h2>
                <p>Your generous donation is greatly appreciated.</p>
            </div>
        <?php elseif ($responseMessage === "error"): ?>
            <div id="thankYouMessage" style="display: block; background-color: #f8d7da; color: #721c24; border-color: #f5c6cb;">
                <h2>Oops!</h2>
                <p>Something went wrong. Please try again.</p>
            </div>
        <?php endif; ?>

        <form id="donationForm" action="donateus.php" method="POST">
            <div class="mb-3">
                <label for="DonorName" class="form-label">Full Name</label>
                <input type="text" class="form-control" name="DonorName" id="DonorName" required>
            </div>
            <div class="mb-3">
                <label for="AccountNO" class="form-label">ACCOUNT Number</label>
                <input type="text" class="form-control" name="AccountNO" id="AccountNO" required>
            </div>
            <div class="mb-3">
                <label for="Amount" class="form-label">Amount (PKR)</label>
                <input type="number" class="form-control" name="Amount" id="Amount" required>
            </div>
            <button type="submit" class="btn btn-primary">Donate</button>
        </form>
    </section>

    <footer class="footer">
        <p>&copy; 2024 Disaster Management System. All rights reserved.</p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
