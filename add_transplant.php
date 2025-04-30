<?php
session_start();
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Doctor') {
    header("Location: doctor_login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli("localhost", "root", "", "organ_management");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $donorID = $_POST['donorID'];
    $recipientID = $_POST['recipientID'];
    $organType = $_POST['organType'];
    $date = $_POST['date'];
    $status = $_POST['status'];
    $doctorID = $_SESSION['userID'];  // Doctor's ID from session

    // Insert new transplant record
    $stmt = $conn->prepare("INSERT INTO transplantInfo (donorID, recipientID, doctorID, organType, date, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisss", $donorID, $recipientID, $doctorID, $organType, $date, $status);
    
    if ($stmt->execute()) {
        echo "Transplant record added successfully!";
    } else {
        echo "Error adding transplant record: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();
}
?>

<a href="doctor_dashboard.php">Back to Dashboard</a>
