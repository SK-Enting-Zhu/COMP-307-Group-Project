// Yvan Yin - 261238020
// form of a club - used by Create Club and by Edit club on the About tab
// the browser checks the fields first, the server checks them again (someone could skip the page)

// returns the CGI string of the form, like "name=Chess&category=Other&..."
// returns "" and shows a message if something is missing
function clubFormData() {
	var name = document.getElementById("name").value.trim();
	var category = document.getElementById("category").value;
	var description = document.getElementById("clubDescription").value.trim();
	var requirement = document.getElementById("requirement").value.trim();
	var contact = document.getElementById("contactEmail").value.trim();
	if (name == "" || description == "") {
		showMessage("Please fill in the name and the description of the club.");
		return "";
	}
	if (category == "") {
		showMessage("Please pick a category.");
		return "";
	}
	// same check as the browser does for <input type="email">
	if (contact != "" && !document.getElementById("contactEmail").checkValidity()) {
		showMessage("The contact has to be an email address.");
		return "";
	}
	return "name=" + encodeURIComponent(name) +
		"&category=" + encodeURIComponent(category) +
		"&description=" + encodeURIComponent(description) +
		"&requirement=" + encodeURIComponent(requirement) +
		"&contact=" + encodeURIComponent(contact);
}
