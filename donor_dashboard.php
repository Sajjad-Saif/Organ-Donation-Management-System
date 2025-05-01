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

$userID = $_SESSION['userID'];
$sql = "SELECT * FROM accountInfo WHERE userID = ? AND userType = 'Donor'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$userInfo = $result->fetch_assoc();
$stmt->close();

// Fetch transplant records for the donor, including Transplant ID, Organ Type, Doctor's Name, Recipient's Name, and IDs
$transplantRecordsSql = "SELECT t.transplantID, t.organType, t.date, t.status, 
                                r.firstname AS recipientFirstName, r.lastname AS recipientLastName, 
                                d.name AS doctorName, t.recipientID, t.doctorID
                         FROM transplantInfo t 
                         JOIN accountInfo r ON t.recipientID = r.userID
                         JOIN doctorInfo d ON t.doctorID = d.doctorID
                         WHERE t.donorID = ?";
$transplantStmt = $conn->prepare($transplantRecordsSql);
$transplantStmt->bind_param("i", $userID);
$transplantStmt->execute();
$transplantResult = $transplantStmt->get_result();
$transplantRecords = [];
while ($row = $transplantResult->fetch_assoc()) {
    $transplantRecords[] = $row;
}
$transplantStmt->close();

// Fetch available organs the donor has added
$organSql = "SELECT organType, availability FROM organs WHERE donorID = ?";
$organStmt = $conn->prepare($organSql);
$organStmt->bind_param("i", $userID);
$organStmt->execute();
$organResult = $organStmt->get_result();
$organList = [];
while ($row = $organResult->fetch_assoc()) {
    $organList[] = $row;
}
$organStmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['organ']) && isset($_POST['availability'])) {
        $organ = $_POST['organ'];
        $availability = $_POST['availability'];
        $donorID = $_SESSION['userID'];

        // Insert new organ or update availability if already exists
        $stmt = $conn->prepare("INSERT INTO organs (donorID, organType, availability) 
                                VALUES (?, ?, ?) 
                                ON DUPLICATE KEY UPDATE availability = ?");
        $stmt->bind_param("isss", $donorID, $organ, $availability, $availability);
        if ($stmt->execute()) {
            echo "Organ information updated successfully.";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Please select an organ and set its availability.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Dashboard</title>
</head>
<body>
    <h1>Donor Dashboard</h1>

    <!-- Display donor's personal information in a table -->
    <h2>About Me</h2>
    <?php if ($userInfo): ?>
        <table border="1">
            <tr>
                <th>ID</th>
                <td><?php echo htmlspecialchars($userInfo['userID']); ?></td>
            </tr>
            <tr>
                <th>Name</th>
                <td><?php echo htmlspecialchars($userInfo['firstname'] . ' ' . $userInfo['lastname']); ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?php echo htmlspecialchars($userInfo['email']); ?></td>
            </tr>
            <tr>
                <th>Age</th>
                <td><?php echo htmlspecialchars($userInfo['age']); ?></td>
            </tr>
            <tr>
                <th>Gender</th>
                <td><?php echo htmlspecialchars($userInfo['gender']); ?></td>
            </tr>
            <tr>
                <th>Blood Group</th>
                <td><?php echo htmlspecialchars($userInfo['bloodGroup']); ?></td>
            </tr>
            <tr>
                <th>Phone</th>
                <td><?php echo htmlspecialchars($userInfo['phone']); ?></td>
            </tr>
            <tr>
                <th>Address</th>
                <td><?php echo htmlspecialchars($userInfo['address']); ?></td>
            </tr>
        </table>
    <?php else: ?>
        <p>Information not available.</p>
    <?php endif; ?>

    <!-- Display transplant records -->
    <h2>Transplant Records</h2>
    <?php if (count($transplantRecords) > 0): ?>
        <table border="1">
            <tr>
                <th>Transplant ID</th>
                <th>Organ Type</th>
                <th>Transplant Date</th>
                <th>Recipient Name</th>
                <th>Recipient ID</th>
                <th>Doctor Name</th>
                <th>Doctor ID</th>
                <th>Status</th>
            </tr>
            <?php foreach ($transplantRecords as $record): ?>
                <tr>
                    <td><?php echo htmlspecialchars($record['transplantID']); ?></td>
                    <td><?php echo htmlspecialchars($record['organType']); ?></td>
                    <td><?php echo htmlspecialchars($record['date']); ?></td>
                    <td><?php echo htmlspecialchars($record['recipientFirstName'] . ' ' . $record['recipientLastName']); ?></td>
                    <td><?php echo htmlspecialchars($record['recipientID']); ?></td>
                    <td><?php echo htmlspecialchars($record['doctorName']); ?></td>
                    <td><?php echo htmlspecialchars($record['doctorID']); ?></td>
                    <td><?php echo htmlspecialchars($record['status']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No transplant records available.</p>
    <?php endif; ?>

    <!-- Display available organs -->
    <h2>My Available Organs</h2>
    <?php if (!empty($organList)): ?>
        <table border="1">
            <tr>
                <th>Organ Type</th>
                <th>Availability</th>
            </tr>
            <?php foreach ($organList as $organ): ?>
                <tr>
                    <td><?php echo htmlspecialchars($organ['organType']); ?></td>
                    <td><?php echo htmlspecialchars($organ['availability']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No organs available.</p>
    <?php endif; ?>

    <h2>Donate Organ</h2>
    <form method="POST">
        <label for="organ">Organ Type:</label>
        <select id="organ" name="organ" required>
            <option value="Kidney">Kidney</option>
            <option value="Liver">Liver</option>
            <option value="Heart">Heart</option>
            <option value="Lungs">Lungs</option>
            <option value="Cornea">Cornea</option>
        </select><br>
        <label for="availability">Availability:</label>
        <select id="availability" name="availability" required>
            <option value="Yes">Yes</option>
            <option value="No">No</option>
        </select><br>
        <button type="submit">Submit</button>
    </form>

    <a href="logout.php">Logout</a>
</body>
</html>
