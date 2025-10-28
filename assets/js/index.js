(function () {
  function setupPasswordToggle(inputId, buttonId) {
    var pwInput = document.getElementById(inputId);
    var toggleBtn = document.getElementById(buttonId);

    if (!pwInput || !toggleBtn) return;

    //get references to the child elements inside the button

    var pwText = document.getElementById("pwText");
    var pwIcon = document.getElementById("pwIcon");

    toggleBtn.addEventListener("click", function () {
      var isHidden = pwInput.getAttribute("type") === "password";

      pwInput.setAttribute("type", isHidden ? "text" : "password");

      // update JUST the text span
      if (pwText) {
        pwText.textContent = isHidden ? "Hide Password" : "Show Password";
      }

      // Toggle the slash visibility inside SVG
      var pwSlash = document.getElementById("pwSlash");
      if (pwSlash) {
        pwSlash.style.display = isHidden ? "block" : "none";
      }

      //
      toggleBtn.setAttribute(
        "aria-label",
        isHidden ? "Hide Password" : "Show Password"
      );
    });
  }

  // run on DOMReady equivalent
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", function () {
      setupPasswordToggle("password", "togglePassword");
    });
  } else {
    setupPasswordToggle("password", "togglePassword");
  }
})();
