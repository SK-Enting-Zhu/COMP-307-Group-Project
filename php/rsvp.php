<?php
// Yu Fei Dong - 261239680
// RSVP of the logged in user to an event - for the Events tab
// going=1 means interested in going
// going=0 takes it back
// answers with the new state and the new count of the event
require "db.php";
require "check.php";
header("Content-Type: application/json");

$user = getUser($conn);
$club = intval($_POST["club"] ?? 0);
if (getRole($conn, $user["id"], $club) == "") {
	stop("You are not a member of this club.");
}
$event = intval($_POST["event"] ?? 0);
$going = ($_POST["going"] ?? "") == "1";

// the event has to be one of this club
$stmt = $conn->prepare("SELECT id FROM events WHERE id = ? AND club_id = ?");
$stmt->bind_param("ii", $event, $club);
$stmt->execute();
if ($stmt->get_result()->num_rows == 0) {
	stop("This event was deleted.");
}

if ($going) {
	// IGNORE so asking twice doesnt give an error or count the user twice
	$stmt = $conn->prepare("INSERT IGNORE INTO rsvps (event_id, user_id) VALUES (?, ?)");
}
else {
	$stmt = $conn->prepare("DELETE FROM rsvps WHERE event_id = ? AND user_id = ?");
}
$stmt->bind_param("ii", $event, $user["id"]);
$stmt->execute();

$stmt = $conn->prepare("SELECT COUNT(*) AS n FROM rsvps WHERE event_id = ?");
$stmt->bind_param("i", $event);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
echo json_encode(["going" => $going, "count" => $row["n"]]);
?>
