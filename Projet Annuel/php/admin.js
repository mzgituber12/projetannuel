async function adminUser(token) {
    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + "/admin", {
        method: "GET",
        headers: {"Token": token},
    });
    
    if (!response.ok){
        const text = await response.text()
        alert(text);
        window.location.href = "erreur.php?code=" + response.status
        return false
    }

    return true
}

async function prestataireUser(token) {
    const base = (window.API_BASE || 'http://localhost:9000');
    let response;
    try {
        response = await fetch(base + "/enligne", {
            method: "GET",
            headers: {"Content-Type": "application/json", "Token": token},
        });
    } catch (e) {
        alert("Impossible de joindre l'API.");
        window.location.href = "erreur.php?code=503";
        return false;
    }
    if (!response.ok) {
        window.location.href = "connexion.php";
        return false;
    }
    let data;
    try {
        data = await response.json();
    } catch (e) {
        window.location.href = "erreur.php?code=" + response.status;
        return false;
    }
    if (data.message === "Pas identifié" || data.role !== "prestataire") {
        alert("Accès réservé aux prestataires.");
        window.location.href = "erreur.php?code=403";
        return false;
    }
    return true;
}

