// Yvan Yin - 261238020
// Create a Club page
// needs common.js and clubform.js first

// runs when the form is submitted
// returns false so the page doesnt reload
// when the club is made, opens its About tab
function createClub() {
	var data = clubFormData();
	if (data != "") {
		send("php/club.php", "action=create&" + data, function(answer) {
			window.location.href = "about.html?club=" + answer.id;
		});
	}
	return false;
}
