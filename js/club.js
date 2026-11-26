// Yvan Yin - 261238020
// header of a club page - banner, name, tags, role, president, founded, members, requirement
// it goes in the <div id="clubHeader"> of the About, Feed, Events and Members tabs
// needs common.js first (send and club)
// texts coming from the server already went through htmlspecialchars

// what the server sent about the club the last time
var clubInfo = null;

// turns a text that went through htmlspecialchars back into the real text
// needed for form fields and confirm boxes (otherwise "&" would show up as "&amp;")
function decode(text) {
	var box = document.createElement("textarea");
	box.innerHTML = text;
	return box.value;
}

// gets the club info and builds the header
// done(data) is called after, so a page can load its own part next
// (send() only does one request at a time so the requests have to follow each other)
function showHeader(done) {
	send("php/club.php", "action=info", function(data) {
		clubInfo = data;
		var badge = "";
		if (data.president) {
			badge = '<span class="tag">&check; president</span>';
		}
		else if (data.role != "") {
			badge = '<span class="tag">&check; ' + data.role + '</span>';
		}
		var requirement = data.requirement;
		if (requirement == "") {
			requirement = "None";
		}
		var founded = data.founded;
		if (founded == "") {
			founded = "-";
		}
		// one small box per tag of the club
		var tags = "";
		for (var i = 0; i < data.tags.length; i++) {
			tags += '<span class="tag">' + data.tags[i] + '</span>';
		}
		// the picture of the club (from Create Club) - a grey banner like the wireframe if there is none
		var banner = '<div class="banner"></div>';
		if (data.image != "") {
			banner = '<img class="banner" src="clubsImage/' + data.image + '" alt="Picture of the club">';
		}
		document.getElementById("clubHeader").innerHTML =
			banner +
			'<h1 class="clubName" id="clubName">' + data.name + '</h1>' +
			'<div class="tags" id="clubTags">' + tags + badge + '</div>' +
			'<div class="facts">' +
			'<div class="fact"><span class="time">President</span><br><b>' + data.presidentName + '</b></div>' +
			'<div class="fact"><span class="time">Founded</span><br><b>' + founded + '</b></div>' +
			'<div class="fact"><span class="time">Members</span><br><b>' + data.members + '</b></div>' +
			'<div class="fact"><span class="time">Requirement</span><br><b id="clubRequirement">' + requirement + '</b></div>' +
			'</div>';
		document.title = "McClub - " + document.getElementById("clubName").textContent;
		if (done) {
			done(data);
		}
	});
}
