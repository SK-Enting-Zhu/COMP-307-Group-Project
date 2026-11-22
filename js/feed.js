// Yu Fei Dong - 261239680
// Feed tab of a club page - announcements, comments and the Upcoming events box
// texts coming from the server already went through htmlspecialchars

// round picture of a user
// if there is no photo it shows the first letter of the name
function pfp(u) {
	if (u.photo != "") {
		return '<img class="pfp" src="' + u.photo + '" alt="">';
	}
	return '<span class="pfp">' + u.name.charAt(0) + '</span>';
}

// gets the announcements of the club and build the list
function loadFeed() {
	send("php/announcements.php", "action=list", function(data) {
		// only execs can write an announcement
		if (data.exec) {
			document.getElementById("postBox").style.display = "block";
		}
		var html = "";
		for (var i = 0; i < data.posts.length; i++) {
			html += postHtml(data.posts[i], data.exec);
		}
		if (html == "") {
			html = '<p class="empty">No announcements yet.</p>';
		}
		document.getElementById("posts").innerHTML = html;
	});
}

// HTML of one announcement with its comments
function postHtml(p, exec) {
	var html = '<div class="post box"><div class="postTop">' + pfp(p) +
		'<div><b>' + p.name + '</b><br><span class="time">' + p.time + '</span></div>';
	if (exec) {
		html += '<span class="actions"><a onclick="editPost(' + p.id + ')">Edit</a><a onclick="deletePost(' + p.id + ')">Delete</a></span>';
	}
	html += '</div><div class="text" id="post' + p.id + '">' + p.content + '</div><div class="comments">';
	for (var i = 0; i < p.comments.length; i++) {
		var c = p.comments[i];
		html += '<div class="comment">' + pfp(c) + '<div><b>' + c.name + '</b> <span class="text" id="comment' + c.id + '">' + c.content + '</span></div>';
		// you can only change your own comments
		if (c.mine) {
			html += '<span class="actions"><a onclick="editComment(' + c.id + ')">Edit</a><a onclick="deleteComment(' + c.id + ')">Delete</a></span>';
		}
		html += '</div>';
	}
	// its a form so pressing enter also sends the comment
	html += '<form class="commentForm" onsubmit="return addComment(' + p.id + ')">' +
		'<input type="text" id="newComment' + p.id + '" placeholder="Write a comment..." maxlength="500">' +
		'<button type="submit">Comment</button></form></div></div>';
	return html;
}

function addPost() {
	var text = document.getElementById("postText").value.trim();
	if (text == "") {
		showMessage("The announcement cannot be empty.");
		return;
	}
	send("php/announcements.php", "action=add&content=" + encodeURIComponent(text), function(data) {
		document.getElementById("postText").value = "";
		loadFeed();
	});
}

// replaces the text of the announcement by a text box with Save and Cancel
function editPost(id) {
	// already being edited
	if (document.getElementById("editPost" + id)) {
		return;
	}
	var box = document.getElementById("post" + id);
	var text = box.textContent;
	box.innerHTML = '<textarea id="editPost' + id + '" maxlength="2000"></textarea>' +
		'<button onclick="savePost(' + id + ')">Save</button> <button class="light" onclick="loadFeed()">Cancel</button>';
	document.getElementById("editPost" + id).value = text;
}

function savePost(id) {
	var text = document.getElementById("editPost" + id).value.trim();
	if (text == "") {
		showMessage("The announcement cannot be empty.");
		return;
	}
	send("php/announcements.php", "action=edit&id=" + id + "&content=" + encodeURIComponent(text), loadFeed);
}

function deletePost(id) {
	if (confirm("Delete this announcement and its comments?")) {
		send("php/announcements.php", "action=delete&id=" + id, loadFeed);
	}
}

// returns false so the form doesnt reload the page
function addComment(post) {
	var text = document.getElementById("newComment" + post).value.trim();
	if (text == "") {
		showMessage("The comment cannot be empty.");
		return false;
	}
	send("php/comments.php", "action=add&post=" + post + "&content=" + encodeURIComponent(text), loadFeed);
	return false;
}

// replaces the text of the comment by a text box with Save and Cancel
function editComment(id) {
	if (document.getElementById("editComment" + id)) {
		return;
	}
	var box = document.getElementById("comment" + id);
	var text = box.textContent;
	box.innerHTML = '<input type="text" id="editComment' + id + '" maxlength="500">' +
		'<span class="actions"><a onclick="saveComment(' + id + ')">Save</a><a onclick="loadFeed()">Cancel</a></span>';
	document.getElementById("editComment" + id).value = text;
}

function saveComment(id) {
	var text = document.getElementById("editComment" + id).value.trim();
	if (text == "") {
		showMessage("The comment cannot be empty.");
		return;
	}
	send("php/comments.php", "action=edit&id=" + id + "&content=" + encodeURIComponent(text), loadFeed);
}

function deleteComment(id) {
	if (confirm("Delete this comment?")) {
		send("php/comments.php", "action=delete&id=" + id, loadFeed);
	}
}

// next 3 events of the club for the box on the side
// only needed once when the page opens
// after that it loads the announcements
function loadUpcoming() {
	send("php/events.php", "action=list", function(data) {
		var html = "";
		for (var i = 0; i < data.events.length && i < 3; i++) {
			html += '<div class="next">' + data.events[i].title + '<br><span class="time">' + data.events[i].day + '</span></div>';
		}
		if (html == "") {
			html = '<p class="empty">No upcoming events.</p>';
		}
		document.getElementById("upcoming").innerHTML = html;
		loadFeed();
	});
}

// runs when the page opens
setTabs();
loadUpcoming();
