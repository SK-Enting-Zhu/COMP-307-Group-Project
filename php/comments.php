<?php
// Yu Fei Dong - 261239680
// comments under an announcement - for the Feed tab
// actions: add, edit, delete
// members and execs can both comment
// you can only edit or delete your own comment
require "db.php";
require "check.php";
header("Content-Type: application/json");

$user = getUser($conn);
$club = intval($_POST["club"] ?? 0);
if (getRole($conn, $user["id"], $club) == "") {
	stop("You are not a member of this club.");
}
$action = $_POST["action"] ?? "";
$id = intval($_POST["id"] ?? 0);

if ($action == "add" || $action == "edit") {
	$content = trim($_POST["content"] ?? "");
	if ($content == "") {
		stop("The comment cannot be empty.");
	}
	if (strlen($content) > 500) {
		stop("The comment is too long, the limit is 500 characters.");
	}
	if ($action == "add") {
		// the announcement has to be one of this club
		$post = intval($_POST["post"] ?? 0);
		$stmt = $conn->prepare("SELECT id FROM announcements WHERE id = ? AND club_id = ?");
		$stmt->bind_param("ii", $post, $club);
		$stmt->execute();
		if ($stmt->get_result()->num_rows == 0) {
			stop("This announcement was deleted.");
		}
		$now = date("Y-m-d H:i:s");
		$stmt = $conn->prepare("INSERT INTO comments (announcement_id, user_id, content, created) VALUES (?, ?, ?, ?)");
		$stmt->bind_param("iiss", $post, $user["id"], $content, $now);
	}
	else {
		// user id is in the WHERE so nobody can edit someone else comment
		$stmt = $conn->prepare("UPDATE comments SET content = ? WHERE id = ? AND user_id = ?");
		$stmt->bind_param("sii", $content, $id, $user["id"]);
	}
	$stmt->execute();
	echo json_encode(["ok" => true]);
}
else if ($action == "delete") {
	$stmt = $conn->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
	$stmt->bind_param("ii", $id, $user["id"]);
	$stmt->execute();
	echo json_encode(["ok" => true]);
}
else {
	stop("Unknown action.");
}
?>
