document.addEventListener('DOMContentLoaded', async function () {
    const myDefaultAllowList = bootstrap.Tooltip.Default.allowList;
    const base = (window.API_BASE || 'http://localhost:9000');
    
    
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
    const popoverList = [...popoverTriggerList].map(el => new bootstrap.Popover(el, {
        trigger: 'manual',
        html: true,
    }));

    let count = 1;

    window.tuto = function() {
        if(count < 11) {
            const element_en_cour = document.getElementById(`popover${count}`);
            if (element_en_cour) { 
                bootstrap.Popover.getInstance(element_en_cour).hide();
            }
            
            count++;
            
            const prochain_element = document.getElementById(`popover${count}`);
            if (prochain_element) {
                const instance = bootstrap.Popover.getInstance(prochain_element);
                if (instance) instance.show();
            }
        }
    };

    const response = await fetch(base + "/get_tuto", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Token": localStorage.getItem("token")
        },
    });

    const data = await response.json();

    if (data === 0) {
        const firstElement = document.getElementById('popover1');
        const instance = bootstrap.Popover.getInstance(firstElement);
        if (instance) instance.show();
    }
});