(function () {
  const form = document.getElementById("codeForm");
  const flashBox = document.getElementById("flashBox");

  if (!form) return;

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
        const div = document.createElement("div");
        div.className = "flash error";
        div.textContent = "Network error. Please try again.";
        flashBox.appendChild(div);
      }
    }
  });
})();
