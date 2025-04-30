<?php
session_start();
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Doctor') {
    header("Location: doctor_login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your CSS file -->
</head>
<body>

<div class="container">
    <h1>Doctor Dashboard</h1>

    <!-- Button to go to Search Donors page -->
    <form action="search_donors.php" method="GET">
        <button type="submit">Search Available Donors with Blood Groups</button>
    </form>

    <!-- Button to view organ requests -->
    <form action="view_organ_requests.php" method="GET">
        <button type="submit">View Organ Requests</button>
    </form>

    <!-- Button to view registered users -->
    <form action="view_users.php" method="GET">
        <button type="submit">View Registered Users</button>
    </form>

    <!-- Button to navigate to Transplant Records -->
    <form action="transplant_records.php" method="GET">
        <button type="submit">View and Manage Transplant Records</button>
    </form>

    <!-- Link for doctor to logout -->
    <div class="links">
        <a href="logout.php">Logout</a>
    </div>
</div>

</body>
</html>
