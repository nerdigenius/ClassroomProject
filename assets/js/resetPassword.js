(function () {
  function TextCheck() {
    let password = document.getElementById("password").value;
    let retype_password = document.getElementById("retype_password").value;
    if (password === retype_password) {
      document.getElementById("error").style.display = "none";
    } else {
      document.getElementById("error").style.display = "block";
    }
  }

  function isValidEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
  }

  function validate() {
    var selectedRows = [];
    let password = document.getElementById("password").value;
    let email = document.getElementById("email").value;
    let retype_password = document.getElementById("retype_password").value;

    if (password != "" && retype_password != "" && email != "") {
      if (isValidEmail(email)) {
        selectedRows.push({
          password: password,
          retype_password: retype_password,
        });
        console.log(selectedRows);

        //read the CSRF token from the meta tag we added

        var tokenEl = document.querySelector('meta[name="csrf-token"]');
        var csrfToken = tokenEl ? tokenEl.getAttribute("content") : "";

        // Send an HTTP request to the server-side script
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
          if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
              var response = JSON.parse(xhr.responseText);
              console.log(response["success"]);
              if (response.success) {
                location.href = "useraccount.php";
              } else {
                window.alert(response["message"]);
              }

              // console.log(xhr.responseText)
              //location.href = 'useraccount.php'
              // Insertion successful, update the UI accordingly
            } else {
              console.error(xhr.statusText);
              console.log("send failed!!!");
              // Insertion failed, show an error message
            }
          }
        };
        xhr.open("POST", "updatePassword.php");
        xhr.setRequestHeader("Content-Type", "application/json");
        //send the CSRF token in the header that csrf.php expects
        xhr.setRequestHeader("X-CSRF-Token", csrfToken);
        xhr.send(JSON.stringify(selectedRows));
        console.log(selectedRows);
      } else {
        window.alert("Invalid Email!!!");
      }
    } else {
      window.alert("Field left empty!!!");
    }
  }
  var retypeField = document.getElementById("retype_password");
  if (retypeField) {
    retypeField.addEventListener("keyup", TextCheck);
  }

  // this replaces onclick="validate()"
  var submitBtn = document.getElementById("submitBtn");
  if (submitBtn) {
    submitBtn.addEventListener("click", function () {
      validate();
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    const logo = document.getElementById("appLogo");

    if (logo) {
      logo.addEventListener("click", function () {
        window.location.href = "useraccount.php";
      });
    }
  });
})();
