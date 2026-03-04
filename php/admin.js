async function adminUser(token) {
    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + "/enligne", {
        method: "GET",
        headers: {"Token": token},
    });
    const data = await response.json();
    
        if (data.role != "admin"){
            document.getElementById("page_title").innerHTML = "Accès refusé";
            document.getElementById("admin").innerHTML = "<h2>Vous n'avez pas les droits pour accéder à cette page.</h2>";
        }
}