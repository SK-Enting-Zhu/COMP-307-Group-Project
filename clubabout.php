<?php
// Yvan Yin - 261238020
// the My Clubs page (myclubs.php) links to a club with clubabout.php?id=3
// the club pages use ?club=3 (see js/common.js), so this just sends the browser to the About tab
// intval keeps only a number so nothing else can be put in the address
$id = intval($_GET["id"] ?? 0);
header("Location: about.html?club=" . $id);
exit;
?>
