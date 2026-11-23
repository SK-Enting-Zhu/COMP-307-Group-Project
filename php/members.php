<?php
// Yvan Yin - 261238020
// members of a club - for the Members tab and the Join / Leave buttons of the About tab
// actions: join, leave, list, promote, demote, remove
// anyone logged in can join, members can leave and see the list
// only execs can promote, demote and remove
// nobody can demote or remove the president
require "db.php";
require "check.php";
header("Content-Type: application/json");

$user = getUser($conn);
$club = intval($_POST["club"] ?? 0);
$action = $_POST["action"] ?? "";

$stmt = $conn->prepare("SELECT president_id FROM clubs WHERE id = ?");
$stmt->bind_param("i", $club);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
if (!$row) {
	stop("This club does not exist.");
}
$presidentId = $row["president_id"];
$role = getRole($conn, $user["id"], $club);

if ($action == "join") {
	if ($role != "") {
		stop("You are already a member of this club.");
	}
	$today = date("Y-m-d");
	$stmt = $conn->prepare("INSERT INTO members (club_id, user_id, role, joined) VALUES (?, ?, 'member', ?)");
	$stmt->bind_param("iis", $club, $user["id"], $today);
	$stmt->execute();
	echo json_encode(["ok" => true]);
	exit;
}

// everything else is only for people in the club
if ($role == "") {
	stop("You are not a member of this club.");
}

if ($action == "leave") {
	// a club always needs its president
	if ($user["id"] == $presidentId) {
		stop("The president cannot leave the club. You can delete the club instead.");
	}
	$stmt = $conn->prepare("DELETE FROM members WHERE club_id = ? AND user_id = ?");
	$stmt->bind_param("ii", $club, $user["id"]);
	$stmt->execute();
	echo json_encode(["ok" => true]);
}
else if ($action == "list") {
	// president first, then the execs, then the members - oldest first in each group
	$members = [];
	$stmt = $conn->prepare("SELECT users.id, users.full_name, users.username, users.photo, members.role, members.joined FROM members JOIN users ON members.user_id = users.id WHERE members.club_id = ? ORDER BY users.id = ? DESC, members.role = 'exec' DESC, members.joined, users.full_name");
	$stmt->bind_param("ii", $club, $presidentId);
	$stmt->execute();
	$result = $stmt->get_result();
	while ($m = $result->fetch_assoc()) {
		$members[] = [
			"id" => $m["id"],
			"name" => htmlspecialchars($m["full_name"]),
			"username" => htmlspecialchars($m["username"]),
			"photo" => htmlspecialchars($m["photo"] ?? ""),
			"role" => $m["role"],
			"president" => $m["id"] == $presidentId,
			"joined" => date("M j, Y", strtotime($m["joined"])),
			"mine" => $m["id"] == $user["id"]
		];
	}
	echo json_encode(["exec" => $role == "exec", "members" => $members]);
}
else if ($role != "exec") {
	stop("Only execs can manage the members.");
}
else if ($action == "promote" || $action == "demote" || $action == "remove") {
	$id = intval($_POST["id"] ?? 0);
	if ($id == $presidentId) {
		stop("The president cannot be changed or removed.");
	}
	if ($action == "remove") {
		$stmt = $conn->prepare("DELETE FROM members WHERE club_id = ? AND user_id = ?");
		$stmt->bind_param("ii", $club, $id);
	}
	else {
		$newRole = "member";
		if ($action == "promote") {
			$newRole = "exec";
		}
		// club id is in the WHERE so an exec cant change someone in another club
		$stmt = $conn->prepare("UPDATE members SET role = ? WHERE club_id = ? AND user_id = ?");
		$stmt->bind_param("sii", $newRole, $club, $id);
	}
	$stmt->execute();
	echo json_encode(["ok" => true]);
}
else {
	stop("Unknown action.");
}
?>
