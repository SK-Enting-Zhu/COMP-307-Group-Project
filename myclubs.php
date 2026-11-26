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

// Getting the clubs that the user is in
$sql = "SELECT clubs.id, clubs.name, clubs.image
        FROM clubs
        JOIN members
        ON clubs.id = members.club_id
        WHERE members.user_id = ?
        ORDER BY members.joined DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>McClub - My Clubs</title>
		<link rel="stylesheet" href="css/myclubs.css">
	</head>
	<body>
		<div class="nav">
			<a class="logo" href="myclubs.php">McClub</a>
			<a href="foryou.php">For You</a>
			<a class="active" href="myclubs.php">My Clubs</a>
			<a href="createclub.php">Create Club</a>
			<a class="pfp" href="profile.php"><?php echo substr($username, 0, 1); ?></a>
		</div>
		<div class="page">
            <div class="clubsContainer">

                <?php
                if ($result->num_rows === 0) {
                    echo "<p></p>";              // used to take up 1st column space of clubsContainer
                    echo '<p style="border:1px solid black; border-radius: 3px; padding: 5px;">';
                    echo "You aren't a member of any clubs yet.<br><br>Join one or create one.</p>";
                    echo "<p></p>";              // used to take up 3rd column space of clubsContainer
                }
                else {
                    while ($club = $result->fetch_assoc()) {
                        $clubID = $club["id"];
                        $imagePath = "clubsImage/" . $club["image"];
                        $clubName = $club["name"];

                        echo '<div class="club">';

                        echo '<a href="clubabout.php?id=' . $clubID . '">';

                        echo '<div class="clubImage">';
                        echo '<img src="' . $imagePath . '" alt="Club Image">';
                        echo "</div>";

                        echo '<div class="clubName">';
                        echo $clubName;
                        echo "</div>";

                        echo "</a>";

                        echo "</div>";
                    }
                }
                ?>
                
            </div>
		</div>
	</body>
</html>
