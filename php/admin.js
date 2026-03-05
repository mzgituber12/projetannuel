async function adminUser(token) {
    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + "/enligne", {
        method: "GET",
        headers: {"Token": token},
    });
    const data = await response.json();
    
        if (data.role != "admin"){
            window.location.href = "index.html";
        }
}