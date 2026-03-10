<?php session_start(); include 'api_config.php'; ?>
<script src="online.js"></script>

<?php $_SESSION['state'] = True ?>

<script>
async function signoutUser(token) {
    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + "/deconnexion", {
        method: "PATCH",
        headers: {"Token": token}
    });

    if (!response.ok){
            const text = await response.text();
            alert(text)
            window.location.href = "erreur.php?code=" + response.status
            return;
        }

    localStorage.removeItem('token');
    window.location.href = "index.php?message=Déconnexion réussie";
}

async function init() {
        const token = localStorage.getItem('token')
        if (!await loginUser("online", token)) return
        signoutUser(token)
    }

init()
</script>