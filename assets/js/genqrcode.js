(function () {
document.addEventListener("DOMContentLoaded", function () {
    const codeForm = document.getElementById('codeForm');

        codeForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const codeInput = document.getElementById('codeInput').value;

            try {
                const response = await fetch('check.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ code: codeInput })
                });

                const result = await response.json();

                if (response.ok) {
                    if (result.success) {
                        window.alert("Code verification successful.");
                        window.location.href = 'useraccount.php';
                        // Redirect or perform necessary actions upon success
                    } else {
                        window.alert("Incorrect code. Please try again.");
                    }
                } else {
                    console.error('Failed to verify code:', result.message);
                }
            } catch (error) {
                console.error('Error occurred:', error);
            }
        });
})
})(); 