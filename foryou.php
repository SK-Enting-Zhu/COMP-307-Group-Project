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
$stmt = $conn->prepare("SELECT id, username, tags FROM users WHERE ticket = ?");
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
$userTags = $row["tags"];

//////////////////////////////////////////////////////////////

// Getting all clubs from db
$sql = "SELECT clubs.id, clubs.name, clubs.description, clubs.tags, clubs.image, users.full_name
        FROM clubs
        JOIN users
        ON clubs.president_id = users.id";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$recommendedClubs = [];

// Loop through each club
while ($club = $result->fetch_assoc()) {
    // Check if user already in club
    $checkMemberStmt = $conn->prepare("SELECT * FROM members WHERE club_id = ? AND user_id = ?");
    $checkMemberStmt->bind_param("ii", $club["id"], $userId);
    $checkMemberStmt->execute();
    $checkMemberResult = $checkMemberStmt->get_result();

 if ($checkMemberResult->num_rows !== 0) {
        continue;           // Skip club if user is already in club
    }
    
    // Keep track of common tags between user and club
    $sharedTagsCount = 0;
    $sharedTagsString = "";

    $userTagsArray = explode(",", $userTags);
    $clubTagsArray = explode(",", $club["tags"]);

    $sharedTagsArray = array_intersect($userTagsArray, $clubTagsArray);
    $sharedTagsCount = count($sharedTagsArray);
    $sharedTagsString = implode(", ", $sharedTagsArray);

    // Add data to club and append it to club recommendation
    $club["sharedTagsCount"] = $sharedTagsCount;
    $club["sharedTagsString"] = $sharedTagsString;

    $recommendedClubs[] = $club;
}

// Sort recommendedClubs (highest sharedTagsCount first)
usort($recommendedClubs, function($a, $b) {
    return $b["sharedTagsCount"] - $a["sharedTagsCount"];
});
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>McClub - For You</title>
		<link rel="stylesheet" href="css/foryou.css">
	</head>
	<body>
		<div class="nav">
			<a class="logo" href="myclubs.php">McClub</a>
			<a class="active" href="foryou.php">For You</a>
			<a href="myclubs.php">My Clubs</a>
			<a href="createclub.php">Create Club</a>
			<a class="pfp" href="profile.php"><?php echo substr($username, 0, 1); ?></a>
		</div>
		<div class="page">

            <?php
            if (count($recommendedClubs) === 0) {
                echo '<h2 style="text-align:center; margin-top: 50px;">No more clubs to show!</h2>';
            }
            else {
                $firstClub = $recommendedClubs[0];
                $tagsArray = explode(",", $firstClub["tags"]);
                
                echo '<div class="club" id="visibleClub">';     // Club container

                echo '<div class="clubImage">';
                echo '<img src="clubsImage/' . $firstClub["image"] . '" alt="Club Image">';
                echo '</div>';

                echo '<div class="clubName">';
                echo $firstClub["name"];
                echo '</div>';

                echo '<div class="clubTags">';
                foreach ($tagsArray as $tag) {
                    echo '<div class="tag">';
                    echo $tag;
                    echo '</div>';
                }
                echo '</div>';

                echo '<div class="clubDesc">';
                echo $firstClub["description"];
                echo '</div>';

                echo '<div class="clubPresident">';
                echo 'President: ' . $firstClub["full_name"];
                echo '</div>';

                echo '<div class="tagsIntersection">';
                if ($firstClub["sharedTagsCount"] === 0) {
                    echo 'No shared interests yet';
                }
                else {
                    echo $firstClub["sharedTagsCount"] . ' shared interest(s): ' . $firstClub["sharedTagsString"];
                }
                echo '</div>';

                echo '</div>';      // End of container

                // Buttons
                echo '<div class="buttonsContainer">';
                echo '<div class="passButton">&cross</div>';
                echo '<div class="joinButton">&check;</div>';
                echo '<div class="passText">pass</div>';
                echo '<div class="joinText">join</div>';
                echo '</div>';

                // Hidden container for rest of recommended clubs
                echo '<div id="hiddenClubs" style="display: none;">';

                for ($i = 1; $i < count($recommendedClubs); $i++) {
                    $club = $recommendedClubs[$i];
                    echo '<div class="clubData"
                          data-id="' . $club["id"] . '"
                          data-image="' . $club["image"] . '"
                          data-name="' . $club["name"] . '"
                          data-tags="' . $club["tags"] . '"
                          data-desc="' . $club["description"] . '"
                          data-president="' . $club["full_name"] . '"
                          data-sharedtagscount="' . $club["sharedTagsCount"] . '"
                          data-sharedtagsstring="' . $club["sharedTagsString"] . '">
                          </div>';
                }

                echo '</div>';
            }
            ?>
            
        </div>

        <script>
            // Get visible club container
            const visibleClub = document.getElementById("visibleClub");
            // Get all hidden clubs
            const hiddenClubs = document.querySelectorAll(".clubData");

            let currentHiddenClubIndex = 0;
            // ID of current visible club
            let currentClubId = <?php echo (count($recommendedClubs) !== 0) ? $recommendedClubs[0]["id"] : "null"; ?>;

            function showNextClub() {
                // No more hidden clubs
                if (currentHiddenClubIndex >= hiddenClubs.length) {
                    visibleClub.innerHTML = '<h2 style="text-align:center; margin-top: 40px;">No more clubs to show!</h2>';
                    document.querySelector(".buttonsContainer").style.display = "none";
                    return;
                }

                const nextClub = hiddenClubs[currentHiddenClubIndex];

                const id = nextClub.dataset.id;
                const image = nextClub.dataset.image;
                const name = nextClub.dataset.name;
                const tags = nextClub.dataset.tags;
                const desc = nextClub.dataset.desc;
                const president = nextClub.dataset.president;
                const sharedTagsCount = nextClub.dataset.sharedtagscount;
                const sharedTagsString = nextClub.dataset.sharedtagsstring;

                // Build html for tags
                let tagsHTML = "";
                const tagsArray = tags.split(",");
                tagsArray.forEach(tag => {
                    tagsHTML += `<div class="tag">${tag}</div>`;
                });

                // Build html for shared tags
                let sharedTagsHTML = "";
                if (sharedTagsCount > 0) {
                    sharedTagsHTML = `${sharedTagsCount} shared interest(s): ${sharedTagsString}`;
                }
                else {
                    sharedTagsHTML = "No shared interests yet";
                }

                visibleClub.innerHTML = `
                    <div class="clubImage">
                        <img src="clubsImage/${image}" alt="Club Image">
                    </div>
                    <div class="clubName">${name}</div>
                    <div class="clubTags">${tagsHTML}</div>
                    <div class="clubDesc">${desc}</div>
                    <div class="clubPresident">President: ${president}</div>
                    <div class="tagsIntersection">${sharedTagsHTML}</div>
                `;

                currentClubId = id;

                currentHiddenClubIndex++;
            }

            // Pass button
            document.querySelector(".passButton").addEventListener("click", showNextClub);

            // Join button
            document.querySelector(".joinButton").addEventListener('click', () => {
                if (currentClubId !== null) {
                    const xhr = new XMLHttpRequest();
                    xhr.open("POST", "joinclub.php", true);
                    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    xhr.send("clubId=" + currentClubId);
                    
                    showNextClub();
                }
            });

        </script>
		
	</body>
</html>
