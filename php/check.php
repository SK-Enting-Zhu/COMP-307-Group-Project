<?php
// login check for the php scripts of the private pages
// the browser sends the ticket it got at login with every request (POST field "ticket")

// sends an error back to the browser as JSON then ends the script
function stop($error) {
	echo json_encode(["error" => $error]);
	exit;
}

// returns the row of the logged in user
// if the ticket is missing or wrong it ends the script with the error "login"
function getUser($conn) {
	$ticket = trim($_POST["ticket"] ?? "");
	if ($ticket == "") {
		stop("login");
	}
	$stmt = $conn->prepare("SELECT id, full_name FROM users WHERE ticket = ?");
	$stmt->bind_param("s", $ticket);
	$stmt->execute();
	$user = $stmt->get_result()->fetch_assoc();
	if (!$user) {
		stop("login");
	}
	return $user;
}

// returns the role of a user in a club - "member" or "exec"
// returns "" if the user is not in the club
function getRole($conn, $userId, $clubId) {
	$stmt = $conn->prepare("SELECT role FROM members WHERE club_id = ? AND user_id = ?");
	$stmt->bind_param("ii", $clubId, $userId);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_assoc();
	if (!$row) {
		return "";
	}
	return $row["role"];
}
?>
