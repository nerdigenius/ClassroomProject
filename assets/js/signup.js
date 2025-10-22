(function () {
  function isValidEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
  }
  function validate() {
    var selectedRows = [];
    let username = document.getElementById("name").value;
    let password = document.getElementById("password").value;
    let email = document.getElementById("email").value;
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
      if (isValidEmail(email)) {
        selectedRows.push({
          username: username,
          password: password,
          retype_password: retype_password,
          email: email,
          enableAuthenticator: enableAuthenticator,
        });
        console.log(selectedRows);

        // Send an HTTP request to the server-side script
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
          if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
              var response = JSON.parse(xhr.responseText);
              console.log(response["success"]);
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
        xhr.send(JSON.stringify(selectedRows));
        console.log(selectedRows);
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
