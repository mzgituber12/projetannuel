<?php session_start(); include 'api_config.php'; ?>
<script src="online.js"></script>
<script>
loginUser("offline", localStorage.getItem('token')); 
</script>

<?php $_SESSION['state'] = true; ?>

<script>
async function signupUser(email, password) {
    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + "/inscription", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({email: email, password: password})
    });

    if (!response.ok) {
        const html = await response.text();
        document.getElementById("error").innerHTML = "<h1>Erreur " + response.status + "</h1>" + html;
        return
    }

    const data = await response.json();
    if (!data.token || data.token == "") {
        window.location.href = "inscription.php?message=" + encodeURIComponent(data.message);
    } else {
        localStorage.setItem('token', data.token);
        window.location.href = "index.php?message=" + encodeURIComponent(data.message);
    }
}
signupUser(<?php echo json_encode($_POST['email']); ?>, <?php echo json_encode($_POST['password']); ?>);
</script>

<div id="error"></div>