(function () {
  function formatMinSec(totalSeconds) {
    var s = parseInt(totalSeconds, 10);
    if (isNaN(s) || s <= 0) return "";
    var m = Math.floor(s / 60);
    var r = s % 60;
    return m > 0 ? m + "m " + r + "s" : r + "s";
  }

  function enhanceRateLimitFlash() {
    var flash = document.querySelector(".flash[data-retry-after]");
    if (!flash) return;

    var seconds = parseInt(flash.getAttribute("data-retry-after") || "0", 10);
    if (!seconds || seconds <= 0) return;

    flash.textContent =
      "Too many failed attempts. Please wait " +
      formatMinSec(seconds) +
      " before trying again.";
  }

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
      enhanceRateLimitFlash();

    });
  } else {
    setupPasswordToggle("password", "togglePassword");
    enhanceRateLimitFlash();
  }

  document.addEventListener("DOMContentLoaded", function () {
    const logo = document.getElementById("appLogo");
    if (logo) {
        logo.addEventListener("click", function () {
            window.location.reload();
        });
    }
  });
})();
