// Mise en place des éléments du DOM
const download = document.getElementById("fileUpload");
const buttonuploadfile = document.getElementById("fileUploadButton");
const picture_profile = document.getElementById("pp");
const defaultAvatar = document.querySelectorAll(".default-avatar");
const next_page = buttonNext = document.getElementById("next");
const previous_page = buttonPrevious = document.getElementById("back");
const main_page = document.getElementById("main");
const second_page = document.getElementById("second");
const register = document.getElementById("register");
const infos_container = document.querySelectorAll(".info-content");
const error_message = document.querySelectorAll(".error");

function showError(element, message) {
    setTimeout(() => {
        element.classList.remove("d-none");
        element.classList.add("d-inline");
        element.innerHTML = message;
    }, 500); 
}
function hideError(element) {
    setTimeout(() => {
        element.classList.remove("d-inline");
        element.classList.add("d-none");
        element.innerText = "";
    }, 500); 
}

// Mise en place des événements pour le téléchargement du fichier
buttonuploadfile.addEventListener("click", () => { download.click() });
picture_profile.addEventListener("click", () => { download.click() });

download.addEventListener("change", (event) => {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            picture_profile.style.backgroundImage = `url(${e.target.result})`;
            picture_profile.style.backgroundSize = "cover";
            picture_profile.style.backgroundPosition = "center";
        };
        reader.readAsDataURL(file);
    }
});

defaultAvatar.forEach((avatar) => {
    avatar.addEventListener("click", (e) => {
        picture_profile.style.backgroundImage = `url(${avatar.src})`;
        avatar.classList.add("selected");
        defaultAvatar.forEach((otherAvatar) => {
            if (otherAvatar !== avatar) {
                otherAvatar.classList.remove("selected");
            }
        });
    });
});

next_page.addEventListener("click", switchPage);
previous_page.addEventListener("click", switchPage);

function switchPage(page) {
    let valid = true;
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    infos_container.forEach((info) => {
        const input = info.children[0];
        const error = info.children[1];
        const value = input.value.trim();


        if (value === "") {
            valid = false;
            showError(error, "Veuillez remplir ce champ");
            return;
        } else {
            hideError(error);
        }

        if (input.classList.contains("email")) {
            let emailTaken = false;
            document.querySelectorAll(".emails").forEach((email) => {
                if (email.innerText.trim().toLowerCase() === value.toLowerCase() && email.innerText.trim() !== "") {
                    emailTaken = true;
                }
            });

            if (emailTaken) {
                valid = false;
                showError(error, "Email déjà utilisé");
            } else if (!regex.test(value)) {
                valid = false;
                showError(error, "Email invalide (ex: goat@gmail.com)");
            } else {
                hideError(error);
            }
        }

        if (input.classList.contains("pseudo")) {
            let pseudoTaken = false;
            document.querySelectorAll(".pseudos").forEach((pseudo) => {
                if (pseudo.innerText.trim().toLowerCase() === value.toLowerCase() && pseudo.innerText.trim() !== "") {
                    pseudoTaken = true;
                }
            });

            if (pseudoTaken) {
                valid = false;
                showError(error, "Pseudo déjà utilisé");
            } else {
                hideError(error);
            }
        }

        // Mot de passe
        if (input.classList.contains("pwd")) {
            if (value.length < 8 ||
                !/[A-Z]/.test(value) ||
                !/\d/.test(value) ||
                !/[!@#$%^&*(),.?":{}|<>]/.test(value)) {
                valid = false;
                showError(error, `
                    • 8 caractères minimum<br>
                    • Au moins une majuscule<br>
                    • Au moins un chiffre<br>
                    • Au moins un caractère spécial
                `);
            } else {
                hideError(error);
            }
        }
    });

    if (valid) {
        if (main_page.classList.contains("valider")) {
            main_page.classList.remove("valider");
            second_page.classList.remove("show");
            setTimeout(() => {
                main_page.style.display = "block";
                second_page.style.display = "none";
            }, 300);
        } else {
            main_page.classList.add("valider");
            second_page.classList.add("show");
            setTimeout(() => {
                main_page.style.display = "none";
                second_page.style.display = "flex";
            }, 300);
        }
    }
}

document.getElementById("show_pwd").addEventListener("change", (e) => {
    const passwordField = document.querySelector(".pwd");
    passwordField.type = e.target.checked ? "text" : "password";
});
