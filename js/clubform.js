// Yvan Yin - 261238020
// form of a club - used by Create Club and by Edit club on the About tab
// the browser checks the fields first, the server checks them again (someone could skip the page)

// the tags a club can have - same list as php/club.php and the Create Club page of P2
var tagNames = ["Anime", "Music", "Reading", "Chess", "Games", "Biology", "Film", "Dance", "Biking", "Hiking", "Coding", "Sport"];

// puts one checkbox per tag in <div id="tagChoices">
// checked is the list of tags to tick (used when an exec opens the edit form)
function showTagChoices(checked) {
	var html = "";
	for (var i = 0; i < tagNames.length; i++) {
		var tick = "";
		if (checked.indexOf(tagNames[i]) != -1) {
			tick = " checked";
		}
		html += '<label><input type="checkbox" value="' + tagNames[i] + '"' + tick + '> ' + tagNames[i] + '</label>';
	}
	document.getElementById("tagChoices").innerHTML = html;
}

// returns the CGI string of the form, like "name=Chess&tags=Chess%2CGames&..."
// the tags are sent as one string "tag1,tag2" - the way they are saved in the database
// returns "" and shows a message if something is missing
function clubFormData() {
	var name = document.getElementById("name").value.trim();
	var description = document.getElementById("clubDescription").value.trim();
	var requirement = document.getElementById("requirement").value.trim();
	var contact = document.getElementById("contactEmail").value.trim();
	var tags = [];
	var boxes = document.querySelectorAll("#tagChoices input:checked");
	for (var i = 0; i < boxes.length; i++) {
		tags.push(boxes[i].value);
	}
	if (name == "" || description == "") {
		showMessage("Please fill in the name and the description of the club.");
		return "";
	}
	if (tags.length == 0) {
		showMessage("Please pick at least one tag.");
		return "";
	}
	// same check as the browser does for <input type="email">
	if (contact != "" && !document.getElementById("contactEmail").checkValidity()) {
		showMessage("The contact has to be an email address.");
		return "";
	}
	return "name=" + encodeURIComponent(name) +
		"&tags=" + encodeURIComponent(tags.join(",")) +
		"&description=" + encodeURIComponent(description) +
		"&requirement=" + encodeURIComponent(requirement) +
		"&contact=" + encodeURIComponent(contact);
}

// the empty checkboxes are there as soon as the page opens
showTagChoices([]);
