(function () {
  const codeForm = document.getElementById("codeForm");

  function getCsrf() {
    var el = document.querySelector('meta[name="csrf-token"]');
    return el ? el.getAttribute("content") : "";
  }

  codeForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    const codeInput = document.getElementById("codeInput").value;

    try {
        const csrfToken = getCsrf();
      const response = await fetch("check.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-Token": csrfToken,
        },
        body: JSON.stringify({
          code: codeInput,
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

          window.location.href = "logout.php";
        }
      } else {
        console.error("Failed to verify code:", result.message);
      }
    } catch (error) {
      console.error("Error occurred:", error);
    }
  });
})();
