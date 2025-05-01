<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli("localhost", "root", "", "organ_management");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $name = $_POST['name'];
    $specialization = $_POST['specialization'];
    $phone = $_POST['phone'];

    // Insert into doctorAccount
    $stmt1 = $conn->prepare("INSERT INTO doctorAccount (username, password) VALUES (?, ?)");
    $stmt1->bind_param("ss", $username, $password);

    if ($stmt1->execute()) {
        $doctorID = $stmt1->insert_id;

        // Insert into doctorInfo
        $stmt2 = $conn->prepare("INSERT INTO doctorInfo (doctorID, name, specialization, phone) VALUES (?, ?, ?, ?)");
        $stmt2->bind_param("isss", $doctorID, $name, $specialization, $phone);

        if ($stmt2->execute()) {
            echo "Doctor registration successful!";
        } else {
            echo "Error in doctorInfo: " . $stmt2->error;
        }
        $stmt2->close();
    } else {
        echo "Error in doctorAccount: " . $stmt1->error;
    }

    $stmt1->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Registration</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Doctor Registration</h1>
    <form method="POST" action="">
        <input type="text" name="username" placeholder="Username (Email)" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <input type="text" name="name" placeholder="Name" required><br>

        <!-- Specialization Dropdown for fixed organ options -->
        <label for="specialization">Specialization:</label>
        <select name="specialization" required>
            <option value="Kidney" <?php echo (isset($specialization) && $specialization == 'Kidney') ? 'selected' : ''; ?>>Kidney</option>
            <option value="Liver" <?php echo (isset($specialization) && $specialization == 'Liver') ? 'selected' : ''; ?>>Liver</option>
            <option value="Heart" <?php echo (isset($specialization) && $specialization == 'Heart') ? 'selected' : ''; ?>>Heart</option>
            <option value="Lungs" <?php echo (isset($specialization) && $specialization == 'Lungs') ? 'selected' : ''; ?>>Lungs</option>
            <option value="Cornea" <?php echo (isset($specialization) && $specialization == 'Cornea') ? 'selected' : ''; ?>>Cornea</option>
        </select><br>

        <input type="text" name="phone" placeholder="Phone" required><br>
        <button type="submit">Register</button>
    </form>
</body>
</html>
