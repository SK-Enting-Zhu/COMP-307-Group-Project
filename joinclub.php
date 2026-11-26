<?php 
// Stephen Chen 261233356

$conn = new mysqli("localhost", "root", "", "mcclub_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if brower has ticket
if (!isset($_COOKIE["ticket"])) {
    echo "unauthorized";
    exit();
}

// Check if browser ticket matches with db ticket
$ticket = $_COOKIE["ticket"];
$stmt = $conn->prepare("SELECT id, username FROM users WHERE ticket = ?");
$stmt->bind_param("s", $ticket);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "unauthorized";
    exit();
}

$row = $result->fetch_assoc();
$userId = $row["id"];
$username = $row["username"];

//////////////////////////////////////////////////////////////

$clubId = $_POST["clubId"] ?? 0;

if ($clubId > 0) {
    $stmt = $conn->prepare("INSERT IGNORE INTO members (club_id, user_id, role, joined) VALUES (?, ?, 'member', CURDATE())");
    $stmt->bind_param("ii", $clubId, $userId);
    $stmt->execute();
}

exit();
?>