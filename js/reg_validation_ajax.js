// Wait for the DOM content to be fully loaded
document.addEventListener("DOMContentLoaded", function () {
    // Get references to form inputs and result message element
    const usernameInput = document.getElementById("username");
    const passwordInput = document.getElementById("password");
    const cpasswordInput = document.getElementById("cpassword");
    const resultMessage = document.getElementById("result");

    // Function to display error messages and focus on the input field
    function displayMessage(element, message) {
        resultMessage.innerHTML = message;
        resultMessage.style.color = "red";
        element.focus();
    }

    // Function to check username availability through AJAX
    function checkUsernameAvailability() {
        const username = usernameInput.value.trim();
        if (username !== "") {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "reg_validation_ajax.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (!response.available) {
                        displayMessage(usernameInput, "This username is already taken.");
                        usernameInput.removeEventListener("blur", checkUsernameAvailability); // Remove the event listener after the first error
                    } else {
                        resultMessage.innerHTML = ""; // Clear previous error messages
                    }
                }
            };
            xhr.send("username=" + encodeURIComponent(username));
        }
    }

    // Function to check password strength through AJAX
    function checkPasswordStrength() {
        const password = passwordInput.value.trim();
        if (password !== "") {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "reg_validation_ajax.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (!response.strong) {
                        displayMessage(passwordInput, "Password must have at least 6 characters, one uppercase letter, one lowercase letter, and one digit.");
                        passwordInput.removeEventListener("blur", checkPasswordStrength); // Remove the event listener after the first error
                    } else {
                        resultMessage.innerHTML = ""; // Clear previous error messages
                    }
                }
            };
            xhr.send("password=" + encodeURIComponent(password));
        }
    }

    // Attach event listeners for input field blur events
    usernameInput.addEventListener("blur", checkUsernameAvailability);
    passwordInput.addEventListener("blur", checkPasswordStrength);

    // Attach event listener for form submission
    document.getElementById('regForm').addEventListener('submit', function (event) {
        // Prevent form submission if validation fails
        if (!validation()) {
            event.preventDefault();
        }
    });

    // Function for overall form validation
    function validation() {
        var username = usernameInput.value;
        var password = passwordInput.value;
        var cpassword = cpasswordInput.value;

        // Validate username
        if (username == "") {
            displayMessage(usernameInput, "Enter your Username*");
            return false;
        } else if (username.length < 6) {
            displayMessage(usernameInput, "Username must be at least six letters*");
            return false;
        }

        // Validate password
        if (password == "") {
            displayMessage(passwordInput, "Enter your Password*");
            return false;
        } else if (password.length < 6) {
            displayMessage(passwordInput, "Password must be at least 6 characters*");
            return false;
        }

        // Validate password confirmation
        if (cpassword == "") {
            displayMessage(cpasswordInput, "Confirm your Password*");
            return false;
        } else if (cpassword !== password) {
            displayMessage(cpasswordInput, "Passwords don't match");
            passwordInput.value = "";
            cpasswordInput.value = "";
            return false;
        }

        // Additional password complexity check
        var passwordRegex = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}$/;
        if (!passwordRegex.test(password)) {
            displayMessage(passwordInput, "Password must contain at least one digit, one lowercase and one uppercase letter, and be at least 6 characters long*");
            return false;
        }

        // Clear any previous error messages
        resultMessage.innerHTML = "";
        return true; // Return true if validation passes
    }
});
