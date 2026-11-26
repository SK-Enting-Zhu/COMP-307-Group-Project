// Stephen Chen 261233356

function validateForm(event) {
    const clubName = document.getElementById("clubName").value.trim();
    const clubDesc = document.getElementById("clubDesc").value.trim();
    const clubImage = document.getElementById("clubImage").value;
    const checkedTags = document.querySelectorAll('input[name="tags[]"]:checked');

    // Check if club name and desc are entered
    if (clubName === "" || clubDesc === "") {
        event.preventDefault();
        alert("Please fill out the club name and description.");
    } 
    // Check if at least one tag is selected
    else if (checkedTags.length === 0) {
        event.preventDefault();
        alert("Please select at least one tag.");
    }
    // Check if image is uploaded
    else if (clubImage === "") {
        event.preventDefault();
        alert("Please upload a club image.");
    } 
}

const form = document.getElementById("clubForm");
form.addEventListener("submit", validateForm);