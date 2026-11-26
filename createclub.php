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

$errorMessage = "";

$error = $_GET["error"] ?? "";
if ($error === "empty_fields") {
    $errorMessage = "Please fill out all fields and select at least one tag.";
}
else if ($error === "invalid_file") {
    $errorMessage = "Please upload a valid image (JPG or PNG only).";
}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>McClub - Create Club</title>
		<link rel="stylesheet" href="css/createclub.css">
        <script src="js/createclub.js" defer></script>
	</head>
	<body>
		<div class="nav">
			<a class="logo" href="myclubs.php">McClub</a>
			<a href="foryou.php">For You</a>
			<a href="myclubs.php">My Clubs</a>
			<a class="active" href="createclub.php">Create Club</a>
			<a class="pfp" href="profile.php"><?php echo substr($username, 0, 1); ?></a>
		</div>
		<div class="page">

			<div class="pageDesc">Start a new club</div>

            <?php
            if ($errorMessage !== "") {
                echo '<div style="color: red; font-weight: bold; margin-bottom: 15px;">';
                echo $errorMessage;
                echo "</div>";
            }
            ?>

            <form class="clubForm" id="clubForm" method="post" action="registerclub.php" enctype="multipart/form-data">
                <div class="clubFormRow">
                    <label for="clubName">Club name</label>
                    <input id="clubName" type="text" name="clubName" placeholder="Enter a club name">
                </div>
                <div class="clubFormRow">
                    <label for="clubDesc">Description</label>
                    <textarea id="clubDesc" name="clubDesc" placeholder="Enter a club description" rows="5"></textarea>
                </div>
                <div class="clubFormRow">
                    <label>Tags</label>
                    <div class="tagContainer">
                        <input class="tagCheckbox" id="tagAnime" type="checkbox" name="tags[]" value="Anime">
                        <label class="tagLabel" for="tagAnime">Anime</label>

                        <input class="tagCheckbox" id="tagMusic" type="checkbox" name="tags[]" value="Music">
                        <label class="tagLabel" for="tagMusic">Music</label>

                        <input class="tagCheckbox" id="tagReading" type="checkbox" name="tags[]" value="Reading">
                        <label class="tagLabel" for="tagReading">Reading</label>

                        <input class="tagCheckbox" id="tagChess" type="checkbox" name="tags[]" value="Chess">
                        <label class="tagLabel" for="tagChess">Chess</label>

                        <input class="tagCheckbox" id="tagGames" type="checkbox" name="tags[]" value="Games">
                        <label class="tagLabel" for="tagGames">Games</label>

                        <input class="tagCheckbox" id="tagBiology" type="checkbox" name="tags[]" value="Biology">
                        <label class="tagLabel" for="tagBiology">Biology</label>

                        <input class="tagCheckbox" id="tagFilm" type="checkbox" name="tags[]" value="Film">
                        <label class="tagLabel" for="tagFilm">Film</label>

                        <input class="tagCheckbox" id="tagDance" type="checkbox" name="tags[]" value="Dance">
                        <label class="tagLabel" for="tagDance">Dance</label>

                        <input class="tagCheckbox" id="tagBiking" type="checkbox" name="tags[]" value="Biking">
                        <label class="tagLabel" for="tagBiking">Biking</label>

                        <input class="tagCheckbox" id="tagHiking" type="checkbox" name="tags[]" value="Hiking">
                        <label class="tagLabel" for="tagHiking">Hiking</label>

                        <input class="tagCheckbox" id="tagCoding" type="checkbox" name="tags[]" value="Coding">
                        <label class="tagLabel" for="tagCoding">Coding</label>

                        <input class="tagCheckbox" id="tagSport" type="checkbox" name="tags[]" value="Sport">
                        <label class="tagLabel" for="tagSport">Sport</label>
                    </div>
                </div>
                <div class="clubFormRow">
                    <label for="clubImage">Upload club image (accepted formats: jpg, png)</label>
                    <input id="clubImage" type="file" name="clubImage" accept="image/png, image/jpeg">
                </div>
                <div class="clubFormRow">
                    <input type="submit" value="Create Club">
                </div>
            </form>
            
		</div>
	</body>
</html>
