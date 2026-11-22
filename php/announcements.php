<?php
// Yu Fei Dong - 261239680
// announcements of a club - for the Feed tab
// actions: list, add, edit, delete
// everyone in the club can list
// only execs can add, edit and delete
require "db.php";
require "check.php";
header("Content-Type: application/json");

// turns a date into something like "3 hours ago"
function ago($date) {
	$time = strtotime($date);
	$sec = time() - $time;
	if ($sec < 60) {
		return "just now";
	}
	if ($sec < 3600) {
		$n = floor($sec / 60);
		$unit = "minute";
	}
	else if ($sec < 86400) {
		$n = floor($sec / 3600);
		$unit = "hour";
	}
	else if ($sec < 604800) {
		$n = floor($sec / 86400);
		$unit = "day";
	}
	else {
		// older than a week so just show the date
		return date("M j, Y", $time);
	}
	if ($n > 1) {
		$unit .= "s";
	}
	return "$n $unit ago";
}

// someone could call this file without using the page
// so everything gets checked here first
$user = getUser($conn);
$club = intval($_POST["club"] ?? 0);
$role = getRole($conn, $user["id"], $club);
if ($role == "") {
	stop("You are not a member of this club.");
}
$exec = $role != "member";
$action = $_POST["action"] ?? "";

if ($action == "list") {
	// all the comments of this club grouped by the id of their announcement
	$comments = [];
	$stmt = $conn->prepare("SELECT comments.id, comments.announcement_id, comments.user_id, comments.content, users.full_name, users.photo FROM comments JOIN announcements ON comments.announcement_id = announcements.id JOIN users ON comments.user_id = users.id WHERE announcements.club_id = ? ORDER BY comments.id");
	$stmt->bind_param("i", $club);
	$stmt->execute();
	$result = $stmt->get_result();
	while ($row = $result->fetch_assoc()) {
		$comments[$row["announcement_id"]][] = [
			"id" => $row["id"],
			"name" => htmlspecialchars($row["full_name"]),
			"photo" => htmlspecialchars($row["photo"] ?? ""),
			"content" => htmlspecialchars($row["content"]),
			"mine" => $row["user_id"] == $user["id"]
		];
	}
	// the announcements - newest first
	$posts = [];
	$stmt = $conn->prepare("SELECT announcements.id, announcements.content, announcements.created, users.full_name, users.photo FROM announcements JOIN users ON announcements.user_id = users.id WHERE announcements.club_id = ? ORDER BY announcements.id DESC");
	$stmt->bind_param("i", $club);
	$stmt->execute();
	$result = $stmt->get_result();
	while ($row = $result->fetch_assoc()) {
		$posts[] = [
			"id" => $row["id"],
			"name" => htmlspecialchars($row["full_name"]),
			"photo" => htmlspecialchars($row["photo"] ?? ""),
			"time" => ago($row["created"]),
			"content" => htmlspecialchars($row["content"]),
			"comments" => $comments[$row["id"]] ?? []
		];
	}
	echo json_encode(["exec" => $exec, "posts" => $posts]);
}
else if (!$exec) {
	stop("Only execs can post, edit or delete announcements.");
}
else if ($action == "add" || $action == "edit") {
	$content = trim($_POST["content"] ?? "");
	if ($content == "") {
		stop("The announcement cannot be empty.");
	}
	if (strlen($content) > 2000) {
		stop("The announcement is too long, the limit is 2000 characters.");
	}
	if ($action == "add") {
		$now = date("Y-m-d H:i:s");
		$stmt = $conn->prepare("INSERT INTO announcements (club_id, user_id, content, created) VALUES (?, ?, ?, ?)");
		$stmt->bind_param("iiss", $club, $user["id"], $content, $now);
	}
	else {
		// club id is in the WHERE so an exec cant edit the announcement of another club
		$id = intval($_POST["id"] ?? 0);
		$stmt = $conn->prepare("UPDATE announcements SET content = ? WHERE id = ? AND club_id = ?");
		$stmt->bind_param("sii", $content, $id, $club);
	}
	$stmt->execute();
	echo json_encode(["ok" => true]);
}
else if ($action == "delete") {
	$id = intval($_POST["id"] ?? 0);
	$stmt = $conn->prepare("DELETE FROM announcements WHERE id = ? AND club_id = ?");
	$stmt->bind_param("ii", $id, $club);
	$stmt->execute();
	echo json_encode(["ok" => true]);
}
else {
	stop("Unknown action.");
}
?>
