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