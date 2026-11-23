// Yvan Yin - 261238020
// Members tab of a club page - list of the members, and Make exec / Make member / Remove for execs
// needs common.js and club.js first
// texts coming from the server already went through htmlspecialchars

// members of the last load and if the user is an exec
var members = [];
var exec = false;

// round picture of a user
// if there is no photo it shows the first letter of the name
function pfp(u) {
	if (u.photo != "") {
		return '<img class="pfp" src="' + u.photo + '" alt="">';
	}
	return '<span class="pfp">' + u.name.charAt(0) + '</span>';
}

// gets the members of the club then shows them
function loadMembers() {
	send("php/members.php", "action=list", function(data) {
		members = data.members;
		exec = data.exec;
		showMembers();
	});
}

// builds the list - only the members whose name or username has the text of the search box
function showMembers() {
	var search = document.getElementById("search").value.trim().toLowerCase();
	var html = "";
	for (var i = 0; i < members.length; i++) {
		var m = members[i];
		if (m.name.toLowerCase().indexOf(search) == -1 && m.username.toLowerCase().indexOf(search) == -1) {
			continue;
		}
		var role = "Member";
		if (m.president) {
			role = "President";
		}
		else if (m.role == "exec") {
			role = "Exec";
		}
		html += '<div class="member">' + pfp(m) +
			'<div class="who"><b>' + m.name + '</b> <span class="time">@' + m.username + '</span><br>' +
			'<span class="time">Joined ' + m.joined + '</span></div>' +
			'<span class="tag">' + role + '</span>';
		// execs manage everyone except the president and themselves (they use Leave club)
		if (exec && !m.president && !m.mine) {
			html += '<span class="actions">';
			if (m.role == "exec") {
				html += '<a onclick="changeMember(' + i + ', \'demote\')">Make member</a>';
			}
			else {
				html += '<a onclick="changeMember(' + i + ', \'promote\')">Make exec</a>';
			}
			html += '<a onclick="changeMember(' + i + ', \'remove\')">Remove</a></span>';
		}
		html += '</div>';
	}
	if (html == "") {
		html = '<p class="empty">No members found.</p>';
	}
	document.getElementById("members").innerHTML = html;
}

// action is promote, demote or remove
function changeMember(i, action) {
	var m = members[i];
	if (action == "remove" && !confirm("Remove " + decode(m.name) + " from the club?")) {
		return;
	}
	send("php/members.php", "action=" + action + "&id=" + m.id, function(data) {
		// the member count of the header changes when someone is removed
		showHeader(loadMembers);
	});
}

// runs when the page opens
setTabs();
showHeader(loadMembers);
