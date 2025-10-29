(function () {
  const codeForm = document.getElementById("codeForm");
  const codeInput = document.getElementById("codeInput")
  if (!codeForm || !codeInput) return; 

  function getCsrf() {
    var el = document.querySelector('meta[name="csrf-token"]');
    return el ? el.getAttribute("content") : "";
  }

  codeForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    

    

    try {
        const csrfToken = getCsrf();
      const response = await fetch("check.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-Token": csrfToken,
        },
        body: JSON.stringify({
          code: codeInput.value.trim(),
        }),
      });

      const result = await response.json();

      if (response.ok) {
        if (result.success) {
          window.alert("Code verification successful.");
          // Redirect or perform necessary actions upon success
          window.location.href = "useraccount.php";
        } else {
          window.alert("Incorrect code. Please try again.");

          document.getElementById("codeInput").value = "";
        }
      } else {
        window.alert("Error: " + result.message);
        console.error("Failed to verify code:", result.message);
      }
    } catch (error) {
      console.error("Error occurred:", error);
    }
  });
  document.addEventListener("DOMContentLoaded", () => {
    const logo = document.getElementById("appLogo");
    codeInput.addEventListener('keydown', function(event) {
      // Allow only numeric input and control keys
      if (!/^[0-9]$/.test(event.key) && !['Backspace', 'ArrowLeft', 'ArrowRight', 'Delete', 'Tab'].includes(event.key)) {
        event.preventDefault();
      }
    });

    if (logo) {
      logo.addEventListener("click", function () {
        window.location.href = "index.php";
      });
    }
  });
})();
