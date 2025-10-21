(function () {
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
                    body: JSON.stringify({
                        code: codeInput
                    })
                });

                const result = await response.json();

                if (response.ok) {
                    if (result.success) {
                        window.alert("Code verification successful.");
                        // Redirect or perform necessary actions upon success
                        window.location.href = 'useraccount.php';
                    } else {
                        
                        window.alert("Incorrect code. Please try again.");
                        
                        window.location.href = 'logout.php';
                    }
                } else {
                    console.error('Failed to verify code:', result.message);
                }
            } catch (error) {
                console.error('Error occurred:', error);
            }
        });
})();
