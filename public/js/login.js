const login_form = document.getElementById("login-form");

login_form.addEventListener("submit", async (e) => {
    
    e.preventDefault();

    const infos_field = document.getElementById("infos");
    const password_field = document.getElementById("password");
    const errorContainer = document.getElementById("server-error");
    
    if (errorContainer) {
        errorContainer.classList.add("d-none");
    }

    if (infos_field.value.trim() === "" || password_field.value.trim() === "") {
        if (errorContainer) {
            setTimeout(() => {
                errorContainer.innerText = "Veuillez remplir tous les champs.";
                errorContainer.classList.remove("d-none");
            }, 1000);
            
        }
        return;
    }
    
    try {
        const formData = new FormData(login_form);

        const response = await fetch("check_login.php", {
            method: "POST",
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            setTimeout(() => {
                document.getElementById("success").classList.remove("d-none");
                document.getElementById("success").classList.add("d-inline");
                setTimeout(() => {
                location.href = "accueil.php";
                }, 600);
            }, 600);
        } else {
            if (errorContainer) {
                setTimeout(() => {
                    errorContainer.innerText = result.message;
                    errorContainer.classList.remove("d-none");
                }, 1000);
            }
        }
    } catch (error) {
        console.error("Erreur de communication:", error);
        if (errorContainer) {
            setTimeout(() => {
                errorContainer.innerText = "Impossible de contacter le serveur.";
                errorContainer.classList.remove("d-none");
            }, 1000);
        }
    }
});