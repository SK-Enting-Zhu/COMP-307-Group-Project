// Yu Fei Dong - 261239680
// Events tab of a club page - list of events, RSVP, map of the meeting point and the form for execs
// texts coming from the server already went through htmlspecialchars

// Google Maps key
// used to draw the maps and to find the position of a meeting point
var mapKey = "AIzaSyAiyHMVgJnhrXoU1W3cxcagJ5ZhlDmJO6s";

// events of the last load
// the buttons of an event give its position in this list
var events = [];

// id of the event being edited in the form
// 0 when the form is creating a new event
var editing = 0;

// gets the events of the club and build the list
function loadEvents() {
	send("php/events.php", "action=list", function(data) {
		events = data.events;
		// only execs can create an event
		if (data.exec) {
			document.getElementById("newEvent").style.display = "inline-block";
		}
		var html = "";
		var i;
		for (i = 0; i < events.length; i++) {
			html += eventHtml(i, data.exec);
		}
		if (html == "") {
			html = '<p class="empty">No upcoming events.</p>';
		}
		document.getElementById("events").innerHTML = html;
		for (i = 0; i < events.length; i++) {
			showRsvp(events[i]);
		}
	});
}

// HTML of one event - the line you click then the panel with the details
function eventHtml(i, exec) {
	var e = events[i];
	var html = '<button class="accordion" onclick="toggleEvent(' + i + ')">' +
		'<span><b id="title' + e.id + '">' + e.title + '</b><br><span class="time">' + e.day + ' - ' + e.hour + '</span></span>' +
		'<span class="sign" id="sign' + e.id + '">+</span></button>' +
		'<div class="panel" id="panel' + e.id + '"><div class="details"><div class="info">' +
		'<p>Meeting point: <span id="place' + e.id + '">' + e.place + '</span></p>' +
		'<p class="text" id="description' + e.id + '">' + e.description + '</p></div>' +
		'<div class="map" id="map' + e.id + '">No map for this meeting point.</div></div>' +
		'<button id="rsvp' + e.id + '" onclick="rsvp(' + i + ')"></button><span class="going" id="going' + e.id + '"></span>';
	if (exec) {
		html += '<span class="actions"><a onclick="editEvent(' + i + ')">Edit</a><a onclick="deleteEvent(' + i + ')">Delete</a></span>';
	}
	return html + '</div>';
}

// RSVP button and count of an event
// the button turns white when the user already said yes
function showRsvp(e) {
	var button = document.getElementById("rsvp" + e.id);
	if (e.rsvp) {
		button.innerHTML = "Cancel RSVP";
		button.className = "light";
	}
	else {
		button.innerHTML = "RSVP";
		button.className = "";
	}
	document.getElementById("going" + e.id).innerHTML = e.going + " interested";
}

// says yes if the user was not interested yet
// otherwise takes it back
function rsvp(i) {
	var e = events[i];
	var going = 1;
	if (e.rsvp) {
		going = 0;
	}
	send("php/rsvp.php", "event=" + e.id + "&going=" + going, function(data) {
		e.rsvp = data.going;
		e.going = data.count;
		showRsvp(e);
	});
}

// opens or closes the panel of an event (same idea as the accordion example)
function toggleEvent(i) {
	var e = events[i];
	var panel = document.getElementById("panel" + e.id);
	if (panel.style.display == "block") {
		panel.style.display = "none";
		document.getElementById("sign" + e.id).innerHTML = "+";
	}
	else {
		panel.style.display = "block";
		document.getElementById("sign" + e.id).innerHTML = "&minus;";
		drawMap(e);
	}
}

// draws the Google map of the meeting point with a marker on it
// only done the first time the event is opened
function drawMap(e) {
	// nothing to draw if no position was saved for the event or if the Google script didnt load
	if (e.lat === null || e.drawn || typeof google == "undefined") {
		return;
	}
	var pos = new google.maps.LatLng(e.lat, e.lng);
	var map = new google.maps.Map(document.getElementById("map" + e.id), {center: pos, zoom: 15});
	var marker = new google.maps.Marker({position: pos});
	marker.setMap(map);
	e.drawn = true;
}

// shows the empty form to create an event
function openForm() {
	editing = 0;
	document.getElementById("eventForm").reset();
	document.getElementById("formTitle").innerHTML = "Create event";
	document.getElementById("saveButton").innerHTML = "Create event";
	document.getElementById("eventForm").style.display = "block";
}

function closeForm() {
	document.getElementById("eventForm").style.display = "none";
}

// shows the form filled with an event so an exec can change it
function editEvent(i) {
	var e = events[i];
	openForm();
	editing = e.id;
	document.getElementById("formTitle").innerHTML = "Edit event";
	document.getElementById("saveButton").innerHTML = "Save changes";
	// texts are read from the page bc the ones in the list went through htmlspecialchars
	document.getElementById("title").value = document.getElementById("title" + e.id).textContent;
	document.getElementById("time").value = e.when;
	document.getElementById("place").value = document.getElementById("place" + e.id).textContent;
	document.getElementById("description").value = document.getElementById("description" + e.id).textContent;
	document.getElementById("eventForm").scrollIntoView();
}

// runs when the form is submitted
// returns false so the page doesnt reload
// first ask Google for the position of the meeting point (external API) then save the event
function saveEvent() {
	var place = document.getElementById("place").value.trim();
	if (document.getElementById("title").value.trim() == "" || document.getElementById("time").value == "" || place == "") {
		showMessage("Please fill in the title, the date and time and the meeting point.");
		return false;
	}
	if (busy) {
		return false;
	}
	// blocks a second click while Google answers
	busy = true;
	fetch("https://geocode.googleapis.com/v4/geocode/address/" + encodeURIComponent(place) + "?key=" + mapKey)
	.then(response => response.json())
	.then(data => {
		// Google answers with a list of places
		// the first one is the best match
		if (data.results && data.results.length > 0) {
			sendEvent(data.results[0].location.latitude, data.results[0].location.longitude);
		}
		else {
			sendEvent("", "");
		}
	})
	.catch(error => {
		// Google couldnt be reached so the event is saved without a map
		sendEvent("", "");
	});
	return false;
}

// sends the form to the server with the position found by Google
// lat and lng are empty when there is none
function sendEvent(lat, lng) {
	var data = "action=add";
	if (editing != 0) {
		data = "action=edit&id=" + editing;
	}
	data += "&title=" + encodeURIComponent(document.getElementById("title").value.trim()) +
		"&time=" + encodeURIComponent(document.getElementById("time").value) +
		"&place=" + encodeURIComponent(document.getElementById("place").value.trim()) +
		"&description=" + encodeURIComponent(document.getElementById("description").value.trim()) +
		"&lat=" + lat + "&lng=" + lng;
	busy = false;
	send("php/events.php", data, function(answer) {
		closeForm();
		loadEvents();
	});
}

function deleteEvent(i) {
	if (confirm("Delete this event?")) {
		send("php/events.php", "action=delete&id=" + events[i].id, function(data) {
			closeForm();
			loadEvents();
		});
	}
}

// loads the Google Maps script
// same as the script tag of the Google example but using the key from above
var script = document.createElement("script");
script.src = "https://maps.googleapis.com/maps/api/js?key=" + mapKey;
document.body.appendChild(script);

// runs when the page opens
setTabs();
loadEvents();
