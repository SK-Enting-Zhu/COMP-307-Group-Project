<?php 
// Stephen Chen 261233356

$conn = new mysqli("localhost", "root", "", "mcclub_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if brower has ticket
if (!isset($_COOKIE["ticket"])) {
    header("Location: login.html");
    exit();
}

// Check if browser ticket matches with db ticket
$ticket = $_COOKIE["ticket"];
$stmt = $conn->prepare("SELECT id, username FROM users WHERE ticket = ?");
$stmt->bind_param("s", $ticket);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: login.html");
    exit();
}

$row = $result->fetch_assoc();
$userId = $row["id"];
$username = $row["username"];

//////////////////////////////////////////////////////////////

$clubName = $_POST["clubName"] ?? "";
$clubDesc = $_POST["clubDesc"] ?? "";
$tagsArray = $_POST["tags"] ?? [];
$fileName = $_FILES["clubImage"]["name"] ?? "";

if ($clubName === "" || $clubDesc === "" || $fileName === "" || empty($tagsArray)) {
    header("Location: createclub.php?error=empty_fields");
    exit();
}

// Convert tagsArray to a string
$tags = implode(",", $tagsArray);

// Move file from temp to clubsImage
$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
if ($fileExtension !== "jpg" && $fileExtension !== "jpeg" && $fileExtension !== "png") {
    header("Location: createclub.php?error=invalid_file");
    exit();
}
$newFileName = bin2hex(random_bytes(16)) . "." . $fileExtension;
$destPath = "clubsImage/" . $newFileName;
move_uploaded_file($_FILES['clubImage']['tmp_name'], $destPath);

// Save club to db
$stmt = $conn->prepare("INSERT INTO clubs (name, description, tags, image, president_id) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssssi", $clubName, $clubDesc, $tags, $newFileName, $userId);
$stmt->execute();

$clubId = $conn->insert_id;
$stmt2 = $conn->prepare("INSERT INTO members (club_id, user_id, role, joined) VALUES (?, ?, 'exec', CURDATE())");
$stmt2->bind_param("ii", $clubId, $userId);
$stmt2->execute();

header("Location: myclubs.php");
exit();
?>