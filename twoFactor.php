<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2FA Setup</title>
</head>

<body>


    <h3>Enter the code generated by Authenticator App</h3>
    <form id="codeForm">
        <label for="codeInput">Enter code here: </label>
        <input type="text" name="code" id="codeInput" required>
        <input type="submit" value="Confirm">
    </form>

    <script>
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
    </script>
</body>

</html>
