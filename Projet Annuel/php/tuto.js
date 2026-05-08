const TUTO_PAGES = {
};

window.initPopovers = function() {
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]:not([data-popover-init])')
    popoverTriggerList.forEach(el => {
        el.setAttribute('data-popover-init', 'true');
        new bootstrap.Popover(el, {
            trigger: 'manual',
            html: true,
            sanitize: false,
            content: el.getAttribute('data-bs-content')
        });
    });
}

document.addEventListener('DOMContentLoaded', async function () {
    initPopovers();

    const base = (window.API_BASE || 'http://localhost:9000');

    let count = parseInt(localStorage.getItem("tuto_step")) || 1;

    window.tuto = function() {
        if (count < 11) {
            const element_en_cour = document.getElementById(`popover${count}`);
            if (element_en_cour) {
                bootstrap.Popover.getInstance(element_en_cour).hide();
            }

            count++;
            localStorage.setItem("tuto_step", count);

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

    if (data == 1) {
        const currentElement = document.getElementById(`popover${count}`);
        if (currentElement) {
            const instance = bootstrap.Popover.getInstance(currentElement);
            if (instance) instance.show();
        }
    }
});


async function fin_tutoriel() {
    await fin_tuto();
    localStorage.removeItem("tuto_step");
    alert("Ce tutoriel est terminé. Vous allez être redirigé vers l'accueil. N'oubliez pas vous pouvez à tout moment relancer ce tutoriel via votre Profil");
    window.location.href = 'index.php';
}



async function fin_tuto() {
    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + "/fin_tuto", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Token": localStorage.getItem("token")
        },
    });

    if (response.ok){
            window.location.href = "index.php"
        }
}