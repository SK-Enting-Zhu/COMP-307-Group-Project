<?php
// Yu Fei Dong - 261239680
// events of a club - for the Events tab and the Upcoming events box of the Feed tab
// actions: list, add, edit, delete
// everyone in the club can list
// only execs can add, edit and delete
require "db.php";
require "check.php";
header("Content-Type: application/json");

$user = getUser($conn);
$club = intval($_POST["club"] ?? 0);
$role = getRole($conn, $user["id"], $club);
if ($role == "") {
	stop("You are not a member of this club.");
}
$exec = $role != "member";
$action = $_POST["action"] ?? "";

if ($action == "list") {
	// count who is interested in each event of this club
	$going = [];
	$mine = [];
	$stmt = $conn->prepare("SELECT rsvps.event_id, rsvps.user_id FROM rsvps JOIN events ON rsvps.event_id = events.id WHERE events.club_id = ?");
	$stmt->bind_param("i", $club);
	$stmt->execute();
	$result = $stmt->get_result();
	while ($row = $result->fetch_assoc()) {
		$id = $row["event_id"];
		$going[$id] = ($going[$id] ?? 0) + 1;
		if ($row["user_id"] == $user["id"]) {
			$mine[$id] = true;
		}
	}
	// only the events that are not over yet
	// the next one comes first
	$now = date("Y-m-d H:i:s");
	$events = [];
	$stmt = $conn->prepare("SELECT id, title, event_time, place, description, lat, lng FROM events WHERE club_id = ? AND event_time >= ? ORDER BY event_time");
	$stmt->bind_param("is", $club, $now);
	$stmt->execute();
	$result = $stmt->get_result();
	while ($row = $result->fetch_assoc()) {
		$time = strtotime($row["event_time"]);
		$events[] = [
			"id" => $row["id"],
			"title" => htmlspecialchars($row["title"]),
			"day" => date("D, M j", $time),
			"hour" => date("g:i A", $time),
			// format that the date and time input of the form needs
			"when" => date("Y-m-d\TH:i", $time),
			"place" => htmlspecialchars($row["place"]),
			"description" => htmlspecialchars($row["description"]),
			"lat" => $row["lat"],
			"lng" => $row["lng"],
			"going" => $going[$row["id"]] ?? 0,
			"rsvp" => isset($mine[$row["id"]])
		];
	}
	echo json_encode(["exec" => $exec, "events" => $events]);
}
else if (!$exec) {
	stop("Only execs can create, edit or delete events.");
}
else if ($action == "add" || $action == "edit") {
	$title = trim($_POST["title"] ?? "");
	$place = trim($_POST["place"] ?? "");
	$description = trim($_POST["description"] ?? "");
	$time = strtotime($_POST["time"] ?? "");
	if ($title == "" || $place == "" || !$time) {
		stop("Please fill in the title, the date and time and the meeting point.");
	}
	if (strlen($title) > 100 || strlen($place) > 200 || strlen($description) > 2000) {
		stop("The title, the meeting point or the description is too long.");
	}
	if ($time < time()) {
		stop("The event has to be in the future.");
	}
	$when = date("Y-m-d H:i:s", $time);
	// position of the meeting point on the map
	// stays empty when Google didnt find it
	$lat = null;
	$lng = null;
	if (is_numeric($_POST["lat"] ?? "") && is_numeric($_POST["lng"] ?? "") && abs($_POST["lat"]) <= 90 && abs($_POST["lng"]) <= 180) {
		$lat = floatval($_POST["lat"]);
		$lng = floatval($_POST["lng"]);
	}
	if ($action == "add") {
		$stmt = $conn->prepare("INSERT INTO events (club_id, title, event_time, place, description, lat, lng) VALUES (?, ?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("issssdd", $club, $title, $when, $place, $description, $lat, $lng);
	}
	else {
		// club id is in the WHERE so an exec cant edit the event of another club
		$id = intval($_POST["id"] ?? 0);
		$stmt = $conn->prepare("UPDATE events SET title = ?, event_time = ?, place = ?, description = ?, lat = ?, lng = ? WHERE id = ? AND club_id = ?");
		$stmt->bind_param("ssssddii", $title, $when, $place, $description, $lat, $lng, $id, $club);
	}
	$stmt->execute();
	echo json_encode(["ok" => true]);
}
else if ($action == "delete") {
	// the database also delete the RSVPs of the event (CASCADE)
	$id = intval($_POST["id"] ?? 0);
	$stmt = $conn->prepare("DELETE FROM events WHERE id = ? AND club_id = ?");
	$stmt->bind_param("ii", $id, $club);
	$stmt->execute();
	echo json_encode(["ok" => true]);
}
else {
	stop("Unknown action.");
}
?>
