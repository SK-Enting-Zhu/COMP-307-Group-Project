<?php
// Yvan Yin - 261238020
// a club itself - for the club header, the About tab and the Create Club page
// actions: info, create, edit, delete
// anyone logged in can see the info (so they can decide to join)
// anyone logged in can create a club and becomes its president
// only execs can edit, only the president can delete
require "db.php";
require "check.php";
header("Content-Type: application/json");

// the categories are the interests of the sign up page
// the club category has to be one of them so For You can match it
$categories = ["Coding", "Music", "Sports", "Debate", "Volunteering", "Art", "Gaming", "Dance", "Other"];

// checks the fields of the create and edit forms
// ends the script with a message if something is wrong
function checkFields($categories) {
	$name = trim($_POST["name"] ?? "");
	$category = trim($_POST["category"] ?? "");
	$description = trim($_POST["description"] ?? "");
	$requirement = trim($_POST["requirement"] ?? "");
	$contact = trim($_POST["contact"] ?? "");
	if ($name == "" || $description == "") {
		stop("Please fill in the name and the description of the club.");
	}
	if (!in_array($category, $categories)) {
		stop("Please pick a category.");
	}
	// mb_strlen counts characters like the browser (maxlength) and MySQL (VARCHAR) do
	// strlen would count bytes, so a name with accents (é = 2 bytes) would look too long
	if (mb_strlen($name) > 100 || mb_strlen($description) > 2000 || mb_strlen($requirement) > 200) {
		stop("The name, the description or the requirement is too long.");
	}
	if ($contact != "" && (mb_strlen($contact) > 100 || !filter_var($contact, FILTER_VALIDATE_EMAIL))) {
		stop("The contact has to be an email address.");
	}
	return [$name, $category, $description, $requirement, $contact];
}

// two clubs cant have the same name
// $id is the club being edited (0 when creating) so it doesnt count itself
function checkName($conn, $name, $id) {
	$stmt = $conn->prepare("SELECT id FROM clubs WHERE name = ? AND id <> ?");
	$stmt->bind_param("si", $name, $id);
	$stmt->execute();
	if ($stmt->get_result()->num_rows > 0) {
		stop("There is already a club with this name.");
	}
}

$user = getUser($conn);
$club = intval($_POST["club"] ?? 0);
$action = $_POST["action"] ?? "";

if ($action == "create") {
	list($name, $category, $description, $requirement, $contact) = checkFields($categories);
	checkName($conn, $name, 0);
	$today = date("Y-m-d");
	$stmt = $conn->prepare("INSERT INTO clubs (name, description, president_id, category, requirement, contact, founded) VALUES (?, ?, ?, ?, ?, ?, ?)");
	$stmt->bind_param("ssissss", $name, $description, $user["id"], $category, $requirement, $contact, $today);
	$stmt->execute();
	$id = $conn->insert_id;
	// the president is also an exec of the club (see README)
	$stmt = $conn->prepare("INSERT INTO members (club_id, user_id, role, joined) VALUES (?, ?, 'exec', ?)");
	$stmt->bind_param("iis", $id, $user["id"], $today);
	$stmt->execute();
	echo json_encode(["id" => $id]);
	exit;
}

// every other action is about an existing club
$stmt = $conn->prepare("SELECT clubs.id, clubs.name, clubs.description, clubs.category, clubs.requirement, clubs.contact, clubs.founded, clubs.president_id, users.full_name FROM clubs JOIN users ON clubs.president_id = users.id WHERE clubs.id = ?");
$stmt->bind_param("i", $club);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
if (!$row) {
	stop("This club does not exist.");
}
$role = getRole($conn, $user["id"], $club);
$president = $row["president_id"] == $user["id"];
$exec = $role == "exec";

if ($action == "info") {
	$stmt = $conn->prepare("SELECT COUNT(*) AS n FROM members WHERE club_id = ?");
	$stmt->bind_param("i", $club);
	$stmt->execute();
	$count = $stmt->get_result()->fetch_assoc()["n"];
	// next 3 events of the club for the Upcoming events box (events table of the Events tab)
	$events = [];
	$now = date("Y-m-d H:i:s");
	$stmt = $conn->prepare("SELECT title, event_time FROM events WHERE club_id = ? AND event_time >= ? ORDER BY event_time LIMIT 3");
	$stmt->bind_param("is", $club, $now);
	$stmt->execute();
	$result = $stmt->get_result();
	while ($e = $result->fetch_assoc()) {
		$time = strtotime($e["event_time"]);
		$events[] = [
			"title" => htmlspecialchars($e["title"]),
			"month" => date("M", $time),
			"day" => date("j", $time),
			"hour" => date("g:i A", $time)
		];
	}
	$founded = "";
	if ($row["founded"]) {
		$founded = date("M j, Y", strtotime($row["founded"]));
	}
	echo json_encode([
		"name" => htmlspecialchars($row["name"]),
		"description" => htmlspecialchars($row["description"]),
		"category" => htmlspecialchars($row["category"]),
		"requirement" => htmlspecialchars($row["requirement"]),
		"contact" => htmlspecialchars($row["contact"]),
		"founded" => $founded,
		"presidentName" => htmlspecialchars($row["full_name"]),
		"members" => $count,
		"role" => $role,
		"president" => $president,
		"events" => $events
	]);
}
else if ($action == "edit") {
	if (!$exec) {
		stop("Only execs can edit the club.");
	}
	list($name, $category, $description, $requirement, $contact) = checkFields($categories);
	checkName($conn, $name, $club);
	$stmt = $conn->prepare("UPDATE clubs SET name = ?, category = ?, description = ?, requirement = ?, contact = ? WHERE id = ?");
	$stmt->bind_param("sssssi", $name, $category, $description, $requirement, $contact, $club);
	$stmt->execute();
	echo json_encode(["ok" => true]);
}
else if ($action == "delete") {
	if (!$president) {
		stop("Only the president can delete the club.");
	}
	// the database also deletes the members, announcements, events... of the club (CASCADE)
	$stmt = $conn->prepare("DELETE FROM clubs WHERE id = ?");
	$stmt->bind_param("i", $club);
	$stmt->execute();
	echo json_encode(["ok" => true]);
}
else {
	stop("Unknown action.");
}
?>
