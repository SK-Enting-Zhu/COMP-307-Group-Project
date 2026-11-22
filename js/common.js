// helpers for the pages of a club
// a club page is opened like feed.html?club=3

// id of the club (taken from the URL)
var club = new URLSearchParams(window.location.search).get("club");

// true while waiting for the server
// this way a double click doesnt send the same thing twice
var busy = false;

// sends a POST to a php script in the background
// data is a CGI string like "action=add&content=hello"
// the club id and the login ticket get added to it
// done(data) is called with the JSON answer if everything went well
function send(url, data, done) {
	if (busy) {
		return;
	}
	busy = true;
	fetch(url, {
		method: "POST",
		headers: {"Content-Type": "application/x-www-form-urlencoded"},
		body: data + "&club=" + encodeURIComponent(club) + "&ticket=" + encodeURIComponent(localStorage.getItem("ticket") || "")
	})
	.then(response => response.json())
	.then(data => {
		busy = false;
		if (data.error == "login") {
			// not logged in or the ticket is not good anymore
			window.location.href = "login.html";
		}
		else if (data.error) {
			showMessage(data.error);
		}
		else {
			document.getElementById("message").style.display = "none";
			done(data);
		}
	})
	.catch(error => {
		busy = false;
		showMessage("Something went wrong, please try again.");
	});
}

// shows a message at the bottom of the screen
// it stays there until the next thing that works
function showMessage(text) {
	var box = document.getElementById("message");
	box.innerHTML = text;
	box.style.display = "block";
}

// the tabs of the club page need to keep the club id in their links
function setTabs() {
	var tabs = ["about", "feed", "events", "members"];
	for (var i = 0; i < tabs.length; i++) {
		document.getElementById(tabs[i] + "Tab").href = tabs[i] + ".html?club=" + encodeURIComponent(club);
	}
}
