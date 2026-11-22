<?php
// database connection
// every php script that needs the database starts with: require "db.php";
// then just use $conn

// XAMPP values
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mcclub";

// the school server has its own username, password and database name in db_server.php
// that file only exist on the server
// the repo is public so the password cant be in it
if (file_exists(__DIR__ . "/db_server.php")) {
	require __DIR__ . "/db_server.php";
}

// all dates and times of the site are in Montreal time
date_default_timezone_set("America/Toronto");

// makes mysqli throw an error when something fails
// without this older PHP versions just stay silent
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
	$conn = new mysqli($servername, $username, $password, $dbname);
	// needed for accents and emojis
	$conn->set_charset("utf8mb4");
}
catch (mysqli_sql_exception $e) {
	die(json_encode(["error" => "Could not connect to the database."]));
}
?>
