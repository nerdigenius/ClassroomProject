(function () {
    var form = document.getElementById("codeForm");
    var errorBox = document.getElementById("errorBox");
    var successBox = document.getElementById("successBox");

    if (!form) {
        return;
    }

    form.addEventListener("submit", function (e) {
        e.preventDefault(); // block normal form POST (page reload)

        // Clear old messages
        if (errorBox) errorBox.textContent = "";
        if (successBox) successBox.textContent = "";

        // Collect form data, including csrf_token and code
        var formData = new FormData(form);

        fetch("genqrcode.php", {
            method: "POST",
            body: formData,
            credentials: "same-origin" // send cookies for PHP session
        })
            .then(function (res) {
                return res.json(); // we always send JSON in POST branch
            })
            .then(function (data) {
                if (data.ok) {
                    // success
                    if (successBox) {
                        successBox.textContent = "2FA enabled 🎉 Redirecting...";
                    }

                    // go to account page (server told us where)
                    window.location.href = data.redirect || "useraccount.php";
                } else {
                    // failure (wrong code, bad length, db err, etc.)
                    if (errorBox) {
                        errorBox.textContent = data.error || "Something went wrong.";
                    }
                }
            })
            .catch(function () {
                if (errorBox) {
                    errorBox.textContent = "Network error. Please try again.";
                }
            });
    });
})();
