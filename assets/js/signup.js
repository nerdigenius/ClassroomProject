(function () {
  function isValidEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
  }

  function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute("content") : "";
  }

  function validate() {
    let username = document.getElementById("name").value.trim();
    let password = document.getElementById("password").value;
    let email = document.getElementById("email").value.trim();
    let retype_password = document.getElementById("retype_password").value;
    let enableAuthenticator = document.getElementById(
      "enableAuthenticator"
    ).checked;

    if (
      username != "" &&
      password != "" &&
      retype_password != "" &&
      email != "" &&
      password == retype_password
    ) {
      //minimum password length
      if (password.length < 8) {
        window.alert("Password must be at least 8 characters !!!");
        return;
      }
      if (isValidEmail(email)) {
        // build payload for server
        const payload = {
          username: username,
          password: password,
          retype_password: retype_password,
          email: email,
          enableAuthenticator: enableAuthenticator,
          csrf_token: getCsrfToken(), // include CSRF token
        };

        // Send an HTTP request to the server-side script
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
          if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
              var response;
              try {
                response = JSON.parse(xhr.responseText);
              } catch (err) {
                console.error(
                  "Invalid JSON from server:",
                  err,
                  xhr.responseText
                );
                window.alert("Server error. Please try again.");
                return;
              }
              if (response.success) {
                location.href = enableAuthenticator
                  ? "genqrcode.php"
                  : "useraccount.php";
              } else {
                window.alert(response["message"]);
              }

              //console.log(xhr.responseText)
              //location.href = 'useraccount.php'
              // Insertion successful, update the UI accordingly
            } else {
              console.error(xhr.statusText);
              console.log("send failed!!!");
              // Insertion failed, show an error message
            }
          } 
        };

        xhr.open("POST", "signupValidation.php");
        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.send(JSON.stringify(payload));
      } else {
        window.alert("Invalid Email!!!");
      }
    } else {
      window.alert("Field left empty or Password do not match !!!");
    }
  }

  document.addEventListener("DOMContentLoaded", () => {
    const pw = document.getElementById("password");
    const re = document.getElementById("retype_password");
    const err = document.getElementById("error");
    const logo = document.getElementById("appLogo");

    if (logo) {
      logo.addEventListener("click", function () {
        window.location.href = "index.php";
      });
    }

    // on submit button click
    document.getElementById("submitBtn").addEventListener("click", (e) => {
      e.preventDefault();
      validate();
    });

    // show the span only while typing in the second input AND it doesn't match
    re.addEventListener("input", () => {
      if (!re.value) {
        err.style.display = "none";
        return;
      }
      err.style.display = re.value !== pw.value ? "block" : "none";
    });
  });
})();
