// Yvan Yin - 261238020
// About tab of a club page - description, upcoming events, Join / Leave, and Edit / Delete for execs
// needs common.js, club.js and clubform.js first
// texts coming from the server already went through htmlspecialchars

// builds the About tab with the info that showHeader() got
function showAbout(data) {
	document.getElementById("description").innerHTML = data.description;

	// next events of the club, like the Upcoming events box of the wireframe
	var html = "";
	for (var i = 0; i < data.events.length; i++) {
		var e = data.events[i];
		html += '<div class="next"><div class="date">' + e.month + '<br>' + e.day + '</div>' +
			'<div><b>' + e.title + '</b><br><span class="time">' + e.hour + '</span></div></div>';
	}
	if (html == "") {
		html = '<p class="empty">No upcoming events.</p>';
	}
	else if (data.role != "") {
		html += '<a class="more" href="events.html?club=' + encodeURIComponent(club) + '">See all events</a>';
	}
	document.getElementById("upcoming").innerHTML = html;

	// the buttons that fit this user
	var member = data.role != "";
	show("contact", data.contact != "");
	document.getElementById("contact").href = "mailto:" + data.contact;
	show("join", !member);
	show("leave", member && !data.president);
	show("edit", data.role == "exec");
	show("delete", data.president);

	// Feed, Events and Members are only for people in the club
	show("feedTab", member);
	show("eventsTab", member);
	show("membersTab", member);
}

// shows or hides an element of the page
// the buttons are hidden at first (club.css) until we know who the user is
function show(id, visible) {
	if (visible) {
		document.getElementById(id).style.display = "inline-block";
	}
	else {
		document.getElementById(id).style.display = "none";
	}
}

function join() {
	send("php/members.php", "action=join", function(data) {
		showHeader(showAbout);
	});
}

function leave() {
	if (confirm("Leave this club?")) {
		send("php/members.php", "action=leave", function(data) {
			closeForm();
			showHeader(showAbout);
		});
	}
}

// shows the form filled with the club so an exec can change it
function openForm() {
	document.getElementById("name").value = decode(clubInfo.name);
	var tags = [];
	for (var i = 0; i < clubInfo.tags.length; i++) {
		tags.push(decode(clubInfo.tags[i]));
	}
	showTagChoices(tags);
	document.getElementById("clubDescription").value = decode(clubInfo.description);
	document.getElementById("requirement").value = decode(clubInfo.requirement);
	document.getElementById("contactEmail").value = decode(clubInfo.contact);
	document.getElementById("clubForm").style.display = "block";
	document.getElementById("clubForm").scrollIntoView();
}

function closeForm() {
	document.getElementById("clubForm").style.display = "none";
}

// runs when the edit form is submitted
// returns false so the page doesnt reload
function saveClub() {
	var data = clubFormData();
	if (data != "") {
		send("php/club.php", "action=edit&" + data, function(answer) {
			closeForm();
			showHeader(showAbout);
		});
	}
	return false;
}

// only the president sees this button
function deleteClub() {
	if (confirm("Delete this club? Its members, announcements and events will be deleted too. This cannot be undone.")) {
		send("php/club.php", "action=delete", function(data) {
			window.location.href = "myclubs.php";
		});
	}
}

// runs when the page opens
setTabs();
showHeader(showAbout);
