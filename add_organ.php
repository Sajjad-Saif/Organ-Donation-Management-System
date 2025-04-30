<?php
session_start();
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Donor') {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "organ_management");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$organ = $_POST['organ'];  // Organ type (e.g., kidney, liver)
$availability = $_POST['availability'];  // Availability (Yes/No)
$donorID = $_SESSION['userID'];  // Get donor's ID from session

// Insert the new organ into the organs table
$stmt = $conn->prepare("INSERT INTO organs (donorID, organType, availability) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $donorID, $organ, $availability);
if ($stmt->execute()) {
    echo "Organ added successfully.";

    // Debugging - Check if the donor status update is being executed
    echo "<br>Attempting to update donor status...<br>";

    // Update the donor_status table to reflect the availability of the organ
    $updateStatusStmt = $conn->prepare("INSERT INTO donor_status (donorID, organType, availability) VALUES (?, ?, ?) 
                                       ON DUPLICATE KEY UPDATE availability = ?");
    if ($updateStatusStmt === false) {
        die('MySQL prepare error: ' . $conn->error);
    }

    $updateStatusStmt->bind_param("isss", $donorID, $organ, $availability, $availability);
    if ($updateStatusStmt->execute()) {
        echo " Donor status updated successfully.";
    } else {
        echo " Error updating donor status: " . $updateStatusStmt->error;
    }
    $updateStatusStmt->close();

} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>

<a href="donor_dashboard.php">Back to Dashboard</a>
