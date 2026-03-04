<?php session_start(); include 'api_config.php'; ?>
<script src="online.js"></script>
<script>
loginUser("online", localStorage.getItem('token')); 
</script>

<?php $_SESSION['state'] = True ?>

<script>
async function signoutUser(token) {
    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + "/deconnexion", {
        method: "PATCH",
        headers: {"Token": token}
    });

    if (!response.ok) {
        const html = await response.text();
        document.getElementById("error").innerHTML = "<h1>Erreur " + response.status + "</h1>" + html;
        return
    }

    localStorage.removeItem('token');
    window.location.href = "index.php?message=Déconnexion réussie";
}

signoutUser(localStorage.getItem('token'))
</script>

<div id = "error"></div>