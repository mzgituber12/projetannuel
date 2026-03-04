async function loginUser(state, token) {
    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + "/enligne", {
        method: "GET",
        headers: {"Token": token},
    });
    const data = await response.json();
    
        if ((state == "offline" && data.message == "Identifié") || (state == "online" && data.message == "Pas identifié")){
            window.location.href = "index.php"
    }
}