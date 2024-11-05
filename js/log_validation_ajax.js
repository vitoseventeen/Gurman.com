// Event listener for form submission
document.getElementById('logForm').addEventListener('submit', function(event) {
    // Prevent the default form submission
    event.preventDefault();

    // Validate form input
    if (validationLogin()) {
        // If validation passes, make an AJAX request
        var username = document.formFill.username.value;
        var password = document.formFill.password.value;
        
        var xhr = new XMLHttpRequest();
        var url = 'log_validation_ajax.php';
        var params = 'username=' + encodeURIComponent(username) + '&password=' + encodeURIComponent(password);
        
        // Configure and send the AJAX request
        xhr.open('POST', url, true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                // Handle the response from the server
                var response = xhr.responseText;
                if (response.trim() === 'success') {
                    // Redirect to the profile page on successful login
                    window.location.href = 'profile.php';
                } else {
                    // Display an error message
                    document.getElementById('result').innerHTML = response;
                    
                    // Clear the password input if the error is related to incorrect credentials
                    if (response.trim() === 'Username or password is incorrect.') {
                        document.formFill.password.value = '';
                    }
                }
            }
        };
        
        xhr.send(params); // Send the request with parameters
    }
});

// Login form validation
function validationLogin() {
    var username = document.formFill.username.value;
    var password = document.formFill.password.value;

    // Regular expressions for username and password validation
    var usernameRegex = /^(?=.*[A-z]).{6,}$/;
    var passwordRegex = /^(?=.*[a-z])(?=.*[A-Z]).{6,}$/;

    // Validation checks
    if (username == "") {
        document.getElementById("result").innerHTML = "Enter your Username*";
        return false;
    } else if (!usernameRegex.test(username)) {
        document.getElementById("result").innerHTML = "Username must be at least six characters*";
        return false;
    } else if (password == "") {
        document.getElementById("result").innerHTML = "Enter your Password*";
        return false;
    } else if (!passwordRegex.test(password)) {
        document.getElementById("result").innerHTML = "Password must be at least six characters and contain both uppercase and lowercase letters*";
        return false;
    }

    // Clear any previous error messages
    document.getElementById("result").innerHTML = "";
    return true; // Return true if validation passes
}
