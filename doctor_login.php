<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli("localhost", "root", "", "organ_management");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT doctorID, password FROM doctorAccount WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($doctorID, $hashedPassword);
    $stmt->fetch();

    if ($doctorID && password_verify($password, $hashedPassword)) {
        $_SESSION['userID'] = $doctorID;
        $_SESSION['role'] = 'Doctor';
        header("Location: doctor_dashboard.php");
        exit;
    } else {
        echo "Doctor not found or invalid credentials.";
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Doctor Login</h1>
    <form method="POST">
        <input type="text" name="username" placeholder="Username (Email)" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
