// function selectimage() {
function selectimage() {
    document.getElementById("fileInput").click();
}

function loadImage(event) {
    const user_image = event.target.files[0];
    if (user_image) {
        document.getElementById("profile-pic").src = URL.createObjectURL(user_image);
    }
}
// function for mobile or password validation 
function myfun() {
    let password = document.getElementById("password").value;
    let password_error = document.getElementById("password_error");
    let mypassword = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[@]).{8,}$/;

    let mobile = document.getElementById("mobile").value;
    let mobile_error = document.getElementById("mobile_error");
    let mymobile = /^[0-9]{10}$/;

    let isvalid = true;

    password_error.innerHTML = "";
    mobile_error.innerHTML = "";

    if (!mypassword.test(password)) {
        password_error.style.color = "red";
        password_error.innerHTML =
            "Password must contain 8+ chars, uppercase, lowercase, number & @";
        isvalid = false;
    }

    if (!mymobile.test(mobile)) {
        mobile_error.style.color = "red";
        mobile_error.innerHTML = "Mobile must be exactly 10 digits";
        isvalid = false;
    }

    return isvalid;
}

// Check if we are on index.html and need to edit
if (window.location.pathname.includes("index.html") || window.location.pathname.endsWith("/")) {
    const urlParams = new URLSearchParams(window.location.search);
    const editId = urlParams.get('id');
    if (editId) {
        // Fetch user data
        let formData = new FormData();
        formData.append('action', 'get_one');
        formData.append('id', editId);

        fetch('form.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data) {
                    document.getElementById('userId').value = data.id;
                    document.getElementById('formAction').value = 'update';
                    document.querySelector('input[name="firstname"]').value = data.firstname;
                    document.querySelector('input[name="lastname"]').value = data.lastname;
                    document.querySelector('input[name="email"]').value = data.email;
                    document.querySelector('input[name="mobile"]').value = data.mobile;
                    document.querySelector('input[name="password"]').required = false;

                    // Radio button
                    let genderRadios = document.getElementsByName('gender');
                    for (let radio of genderRadios) {
                        if (radio.value === data.gender) radio.checked = true;
                    }
                    // Image
                    if (data.image) {
                        document.getElementById("profile-pic").src = data.image;
                    }

                    let submitBtn = document.getElementById("submitBtn");
                    if (submitBtn) submitBtn.innerText = "UPDATE";

                    let cancelBtn = document.getElementById("cancelBtn");
                    if (cancelBtn) cancelBtn.style.display = "block"; // Flex child needs to be block/box
                }
            })
            .catch(err => console.error(err));
    }
}

function resetForm() {
    window.location.href = "index.html";
}

// Form Submit Handler
const form = document.getElementById("myForm");
if (form) {
    form.addEventListener("submit", function (e) {
        e.preventDefault();

        if (!myfun()) return;

        let formData = new FormData(this);

        let xhr = new XMLHttpRequest();
        xhr.open("POST", "form.php", true);

        xhr.onload = function () {
            if (xhr.status === 200) {
                let res = xhr.responseText.trim();
                if (res === "success" || res.includes("Data Successfully inserted")) {
                    window.location.href = "display.html";
                } else {
                    // Show the actual error from PHP
                    alert("Failed to add user:\n" + res);
                }
            } else {
                alert("Error submitting form: " + xhr.statusText);
            }
        };

        xhr.send(formData);
    });
}


// Functions for display.html
function loadData() {
    let tbody = document.getElementById("userTableBody");
    if (!tbody) return; // Exit if not on display page

    let xhr = new XMLHttpRequest();
    // Use GET for data fetching as it is standard and cacheable/debuggable
    xhr.open("GET", "form.php?action=fetch", true);

    xhr.onload = function () {
        if (xhr.status === 200) {
            let response = xhr.responseText.trim();
            if (response === "") {
                // Empty response usually means no rows or PHP error suppressing output
                tbody.innerHTML = "<tr><td colspan='6' style='text-align:center; padding: 20px; color: #777;'>No employees found. Add one to see it here!</td></tr>";
            } else {
                tbody.innerHTML = response;
            }
        } else {
            tbody.innerHTML = "<tr><td colspan='6' style='text-align:center; color: red;'>Server Error: " + xhr.status + "</td></tr>";
        }
    };

    xhr.onerror = function () {
        tbody.innerHTML = "<tr><td colspan='6' style='text-align:center; color: red;'>Connection Error. Please check your server.</td></tr>";
    };

    xhr.send();
}

// Make functions global
window.deleteUser = function (id) {
    // console.log("Delete clicked for ID:", id);
    if (confirm('Are you sure you want to delete this record?')) {
        let formData = new FormData();
        formData.append('id', id);
        formData.append('action', 'delete');

        fetch('form.php', {
            method: 'POST',
            body: formData
        })
            .then(res => res.text())
            .then(data => {
                // alert(data); // Debug response
                loadData(); // Reload table
            });
    }
};

window.editUser = function (id) {
    // console.log("Edit clicked for ID:", id);
    window.location.href = "index.html?id=" + id;
};

// Auto-init for display page
if (document.getElementById("userTableBody")) {
    loadData();
}
