(function () {
  const form = document.getElementById("codeForm");
  const flashBox = document.getElementById("flashBox");
  const copyBtn = document.getElementById("copySetupKey");
  const setupKeyEl = document.getElementById("setupKey");

  if (!form) return;

  // Copy manual setup key (mobile fallback)
  if (copyBtn && setupKeyEl) {
    copyBtn.addEventListener("click", async () => {
      const key = (setupKeyEl.textContent || "").trim();
      if (!key) return;
      try {
        await navigator.clipboard.writeText(key);
        showFlash("success", "Setup key copied.");
      } catch (e) {
        // Fallback for older browsers
        const sel = window.getSelection();
        const range = document.createRange();
        range.selectNodeContents(setupKeyEl);
        sel.removeAllRanges();
        sel.addRange(range);
        try {
          document.execCommand("copy");
          showFlash("success", "Setup key copied.");
        } catch (err) {
          showFlash("error", "Could not copy. Please select and copy manually.");
        }
        sel.removeAllRanges();
      }
    });
  }

  function showFlash(type, text) {
    if (!flashBox) return;
    const div = document.createElement("div");
    div.className = "flash " + type;
    div.textContent = text;
    flashBox.appendChild(div);
  }

  function formatMinSec(totalSeconds) {
    var s = parseInt(totalSeconds, 10);
    if (isNaN(s) || s <= 0) return "";
    var m = Math.floor(s / 60);
    var r = s % 60;
    return m > 0 ? m + "m " + r + "s" : r + "s";
  }

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    if (flashBox) flashBox.innerHTML = "";

    const formData = new FormData(form);

    try {
      const response = await fetch("genqrcode.php", {
        method: "POST",
        body: formData,
        credentials: "same-origin", // keep PHP session
      });

      const data = await response.json();

      // Handle non-2xx (e.g. 429 rate limit) by showing server message.
      if (!response.ok) {
        var retryAfter =
          data && typeof data.retry_after === "number" ? data.retry_after : 0;
        if (retryAfter && retryAfter > 0) {
          showFlash(
            "error",
            "Too many requests. Please wait " +
              formatMinSec(retryAfter) +
              " before trying again."
          );
        } else {
          showFlash("error", "Request failed. Please try again.");
        }
        return;
      }

      if (flashBox && data.flash) {
        const div = document.createElement("div");
        div.className = "flash " + data.flash.type;
        div.textContent = data.flash.text;
        flashBox.appendChild(div);
      }

      if (data.ok) {
        // delay a moment so user sees success flash
        setTimeout(() => {
          window.location.href = data.redirect || "useraccount.php";
        }, 800);
      }
    } catch (err) {
      if (flashBox) {
        showFlash("error", "Network error. Please try again.");
      }
    }
  });
  document.addEventListener("DOMContentLoaded", function () {
    const logo = document.getElementById("appLogo");

    if (logo) {
      logo.addEventListener("click", function () {
        window.location.href = "index.php";
      });
    }
  });
})();
