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

    return true
}