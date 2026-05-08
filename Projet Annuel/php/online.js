async function loginUser(state, token) {

    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + "/enligne", {
        method: "GET",
        headers: {"Token": token},
    });
    
    if (!response.ok){
        const text = await response.text()
        alert(text)
        window.location.href = "erreur.php?code=" + response.status
        return false
    }
    const data = await response.json();
    if ((state == "offline" && data.message == "Identifié") || (state == "online" && data.message == "Pas identifié")){
            window.location.href = "index.php"
            return false
    }
    const page = (window.location.pathname.split('/').pop() || '').toLowerCase();
    const autoriser = ['banni.php', 'deconnexion.php', 'connexion.php', 'erreur.php'];
    if (!autoriser.includes(page) && (data.statut_user == "banni" || data.statut_user == "suspendu")) {
        const params = new URLSearchParams();
        params.set("statut", data.statut_user || "banni");
        params.set("motif", data.motif_sanction || "Aucun motif renseigné");
        if (data.type_sanction){
            params.set("type", data.type_sanction);
        } 
        if (data.fin_susp){
            params.set("fin", data.fin_susp);
        } 
        window.location.href = "banni.php?" + params.toString();
        return false;
    }

    return true
}
