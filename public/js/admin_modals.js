document.addEventListener('DOMContentLoaded', function() {

    const userEditModal = document.querySelector('.modal-edit-user');
    const userEditContainer = userEditModal.querySelector('.modal-container-edit-user');
    const editPP = document.getElementById('edit_pp');
    const editOldAvatarInput = document.getElementById('edit_old_avatar');
    const editUseDefaultInput = document.getElementById('edit_use_default_avatar');
    const editFileUpload = document.getElementById('edit_fileUpload');
    const editFileUploadBtn = document.getElementById('edit_fileUploadButton');
    const defaultAvatars = userEditModal.querySelectorAll('.edit-default-avatar');

    document.querySelectorAll('.films_modal').forEach(btn => {
        btn.addEventListener('click', () => {
            const modal_films = document.querySelector('.modal-films');
            const modal_container_films = document.querySelector('.modal-container-films');
            if (!modal_films || !modal_container_films) return;

            modal_films.classList.add('active');
            modal_container_films.classList.add('active');

            document.getElementById('username-film').textContent = btn.dataset.auteur || '';
            document.getElementById('titre-film').textContent = btn.dataset.titre || '';
            document.getElementById('film-input').value = btn.dataset.id || '';

            if ((btn.innerText || '').toLowerCase().includes('valider')) {
                document.getElementById('text-film').textContent = "valider";
                document.getElementById('decision-film').value = "valider";
            } else {
                document.getElementById('text-film').textContent = "rejeter";
                document.getElementById('decision-film').value = "rejeter";
            }

            document.body.style.overflow = 'hidden';
        });
    });

    // ========== UTILISATEURS A VALIDER (boutons .user_modal) ==========
    document.querySelectorAll('.user_modal').forEach(btn => {
        btn.addEventListener('click', () => {
            const modal_register = document.querySelector('.modal-register');
            const modal_container_register = document.querySelector('.modal-container-register');
            if (!modal_register || !modal_container_register) return;

            modal_register.classList.add('active');
            modal_container_register.classList.add('active');

            document.getElementById('username').textContent = btn.dataset.username || '';
            document.getElementById('register-input').value = btn.dataset.id || '';

            if ((btn.innerText || '').toLowerCase().includes('valider')) {
                document.getElementById('text-register').textContent = "valider";
                document.getElementById('decision-register').value = "valider";
            } else {
                document.getElementById('text-register').textContent = "rejeter";
                document.getElementById('decision-register').value = "rejeter";
            }

            document.body.style.overflow = 'hidden';
        });
    });


    document.querySelectorAll('.manage_users_modal').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const action = this.dataset.action;
            if (action === 'edit-user') {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                document.getElementById('edit_user_id').value = this.dataset.id;
                document.getElementById('edit_nom').value = this.dataset.nom || '';
                document.getElementById('edit_prenom').value = this.dataset.prenom || '';
                document.getElementById('edit_pseudo').value = this.dataset.pseudo || '';
                document.getElementById('edit_email').value = this.dataset.email || '';
                document.getElementById('edit_date').value = this.dataset.date || '';
                editOldAvatarInput.value = this.dataset.avatar || '';
                const statut = this.dataset.statut || '';

                const statutList = ['etudiant', 'enseignant', 'administratif', 'administrateur'];
                statutList.forEach(s => {
                    const radio = document.getElementById('edit_' + s);
                    if (radio) {
                        radio.checked = (s === statut);
                    }
                });

                const avatarPath = this.dataset.avatar || '';
                if (avatarPath) {
                    editPP.style.backgroundImage = `url('${avatarPath}')`;
                }

                defaultAvatars.forEach(img => img.classList.remove('selected'));
                editUseDefaultInput.value = '';

                document.querySelectorAll('.mymodal').forEach(modal => {
                    if (!modal.classList.contains('modal-edit-user') && !modal.classList.contains('modal-edit-film')) {
                        modal.classList.remove('active');
                        const cont = modal.querySelector('.modal-container');
                        if (cont) cont.classList.remove('active');
                    }
                });

                userEditModal.classList.add('active');
                userEditContainer.classList.add('active');
            } else if (action === 'delete-user') {

                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                const id = this.dataset.id;
                const username = this.dataset.username || '';
                // Préparer le texte et les champs cachés de la modale de gestion
                const manageTextEl = document.getElementById('text-manage-register');
                const manageUserEl = document.getElementById('username-manage');
                const decisionInput = document.getElementById('decision-manage-register');
                const idInput = document.getElementById('manage-register-input');
                if (manageTextEl) manageTextEl.textContent = 'radier';
                if (manageUserEl) manageUserEl.textContent = username;
                if (decisionInput) decisionInput.value = 'supprimer';
                if (idInput) idInput.value = id;
                // Masquer toutes les autres modales ouvertes pour éviter la superposition
                document.querySelectorAll('.mymodal').forEach(modal => {
                    if (!modal.classList.contains('modal-manage-register')) {
                        modal.classList.remove('active');
                        const cont = modal.querySelector('.modal-container');
                        if (cont) cont.classList.remove('active');
                    }
                });
                // Afficher la modale de gestion des utilisateurs
                const modalRegister = document.querySelector('.modal-manage-register');
                if (modalRegister) {
                    modalRegister.classList.add('active');
                    const cont = modalRegister.querySelector('.modal-container');
                    if (cont) cont.classList.add('active');
                }
            }
        });
    });


    if (editFileUploadBtn) {
        editFileUploadBtn.addEventListener('click', function() {
            editFileUpload.click();
        });
    }
    if (editFileUpload) {
        editFileUpload.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const url = URL.createObjectURL(file);
                editPP.style.backgroundImage = `url('${url}')`;

                editUseDefaultInput.value = '';
                defaultAvatars.forEach(img => img.classList.remove('selected'));
            }
        });
    }

    defaultAvatars.forEach(img => {
        img.addEventListener('click', function() {
            defaultAvatars.forEach(i => i.classList.remove('selected'));
            this.classList.add('selected');
            const name = this.dataset.avatar;
            editPP.style.backgroundImage = `url('assets/${name}.png')`;
            editUseDefaultInput.value = name;
        });
    });

    userEditModal.querySelectorAll('.modal-trigger').forEach(tr => {
        tr.addEventListener('click', function() {
            userEditModal.classList.remove('active');
            userEditContainer.classList.remove('active');
        });
    });

    const filmEditModal = document.querySelector('.modal-edit-film');
    const filmEditContainer = filmEditModal.querySelector('.modal-container-edit-film');
    document.querySelectorAll('.manage_films_modal').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const action = this.dataset.action;
            if (action === 'edit-film') {

                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                document.getElementById('edit_film_id').value = this.dataset.id;
                document.getElementById('edit_old_affiche').value = this.dataset.affiche || '';
                document.getElementById('edit_titre').value = this.dataset.titre || '';
                document.getElementById('edit_realisateur').value = this.dataset.realisateur || '';
                document.getElementById('edit_annee').value = this.dataset.annee || '';
                document.getElementById('edit_trailer').value = this.dataset.trailer || '';
                document.getElementById('edit_description').value = this.dataset.description || '';
                // Genres : on décoche tout puis on coche ceux contenus dans data-genres
                const selectedGenres = (this.dataset.genres || '').split('|');
                document.querySelectorAll('#edit_genre_container .edit-genre-checkbox').forEach(cb => {
                    cb.checked = selectedGenres.includes(cb.value);
                });
                // Masquer toutes les autres modales ouvertes pour éviter la superposition
                document.querySelectorAll('.mymodal').forEach(modal => {
                    if (!modal.classList.contains('modal-edit-user') && !modal.classList.contains('modal-edit-film')) {
                        modal.classList.remove('active');
                        const cont = modal.querySelector('.modal-container');
                        if (cont) cont.classList.remove('active');
                    }
                });
                filmEditModal.classList.add('active');
                filmEditContainer.classList.add('active');
            } else if (action === 'delete-film') {
                // Gérer la suppression d'un film : ouvrir la modale de confirmation
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                const id = this.dataset.id;
                const titre = this.dataset.titre || '';
                const auteur = this.dataset.auteur || '';
                // Préparer le texte et les champs cachés de la modale de gestion des films
                const textEl = document.getElementById('text-manage-film');
                const titreEl = document.getElementById('titre-film-manage');
                const auteurEl = document.getElementById('username-film-manage');
                const decisionInput = document.getElementById('decision-manage-film');
                const idInput = document.getElementById('manage-film-input');
                if (textEl) textEl.textContent = 'supprimer';
                if (titreEl) titreEl.textContent = titre;
                if (auteurEl) auteurEl.textContent = auteur;
                if (decisionInput) decisionInput.value = 'supprimer';
                if (idInput) idInput.value = id;
                // Masquer toutes les autres modales ouvertes
                document.querySelectorAll('.mymodal').forEach(modal => {
                    if (!modal.classList.contains('modal-manage-films')) {
                        modal.classList.remove('active');
                        const cont = modal.querySelector('.modal-container');
                        if (cont) cont.classList.remove('active');
                    }
                });
                // Fermer également l'overlay d'informations si ouvert
                const overlay = document.getElementById('film-info-overlay');
                if (overlay) {
                    overlay.classList.remove('active');
                }
                // Afficher la modale de gestion des films
                const modalFilm = document.querySelector('.modal-manage-films');
                if (modalFilm) {
                    modalFilm.classList.add('active');
                    const cont = modalFilm.querySelector('.modal-container');
                    if (cont) cont.classList.add('active');
                }
            }
        });
    });

    filmEditModal.querySelectorAll('.modal-trigger').forEach(tr => {
        tr.addEventListener('click', function() {
            filmEditModal.classList.remove('active');
            filmEditContainer.classList.remove('active');
        });
    });


    const filmInfoOverlay = document.getElementById('film-info-overlay');
    const filmInfoAffiche = document.getElementById('film-info-affiche');
    const filmInfoTitle   = document.getElementById('film-info-title');
    const filmInfoDesc    = document.getElementById('film-info-description');
    const filmInfoAnnee   = document.getElementById('film-info-annee');
    const filmInfoReal    = document.getElementById('film-info-realisateur');
    const filmInfoAuteur  = document.getElementById('film-info-auteur');
    const filmInfoGenres  = document.getElementById('film-info-genres');
    const filmInfoEditBtn = document.getElementById('film-info-edit');

    function renderGenres(genres) {
        filmInfoGenres.innerHTML = '';
        genres.forEach(g => {
            const span = document.createElement('span');
            span.classList.add('badge');
            span.textContent = g;
            filmInfoGenres.appendChild(span);
        });
    }

    document.querySelectorAll('.film-card').forEach(card => {
        card.addEventListener('click', function(e) {
            if (e.target.closest('button')) {
                return;
            }
            const title = this.dataset.filmTitre || '';
            const desc  = this.dataset.filmDescription || '';
            const annee = this.dataset.filmAnnee || '';
            const real  = this.dataset.filmRealisateur || '';
            const auteur = this.dataset.filmAuteur || '';
            const genres = (this.dataset.filmGenres || '').split('|').filter(g => g);
            const affiche = this.dataset.filmAffiche || '';
            filmInfoTitle.textContent = title;
            filmInfoDesc.textContent  = desc;
            filmInfoAnnee.textContent = annee;
            filmInfoReal.textContent  = real;
            filmInfoAuteur.textContent= auteur;
            if (affiche) {
                filmInfoAffiche.style.backgroundImage = `url('${affiche}')`;
            }
            renderGenres(genres);
            // enregistrer l'id pour modification ultérieure
            filmInfoEditBtn.dataset.filmId = this.dataset.filmId;
            filmInfoEditBtn.dataset.titre = title;
            filmInfoEditBtn.dataset.realisateur = real;
            filmInfoEditBtn.dataset.annee = annee;
            filmInfoEditBtn.dataset.description = desc;
            filmInfoEditBtn.dataset.trailer = this.dataset.filmTrailer || '';
            filmInfoEditBtn.dataset.genres = (this.dataset.filmGenres || '');
            filmInfoEditBtn.dataset.affiche= affiche;
            filmInfoEditBtn.dataset.auteur = auteur;
            filmInfoOverlay.classList.add('active');
        });
    });
    // Fermer la fenêtre d'infos en cliquant en dehors du conteneur
    filmInfoOverlay.addEventListener('click', function(e) {
        if (e.target === filmInfoOverlay) {
            filmInfoOverlay.classList.remove('active');
        }
    });

    filmInfoEditBtn.addEventListener('click', function() {
        filmInfoOverlay.classList.remove('active');

        document.getElementById('edit_film_id').value    = this.dataset.filmId;
        document.getElementById('edit_old_affiche').value= this.dataset.affiche || '';
        document.getElementById('edit_titre').value      = this.dataset.titre || '';
        document.getElementById('edit_realisateur').value= this.dataset.realisateur || '';
        document.getElementById('edit_annee').value      = this.dataset.annee || '';
        document.getElementById('edit_trailer').value    = this.dataset.trailer || '';
        document.getElementById('edit_description').value= this.dataset.description || '';
        const selectedGenres = (this.dataset.genres || '').split('|');
        document.querySelectorAll('#edit_genre_container .edit-genre-checkbox').forEach(cb => {
            cb.checked = selectedGenres.includes(cb.value);
        });

        document.querySelectorAll('.mymodal').forEach(modal => {
            if (!modal.classList.contains('modal-edit-user') && !modal.classList.contains('modal-edit-film')) {
                modal.classList.remove('active');
                const cont = modal.querySelector('.modal-container');
                if (cont) cont.classList.remove('active');
            }
        });
        filmEditModal.classList.add('active');
        filmEditContainer.classList.add('active');
    });

    document.querySelectorAll('.modal-trigger').forEach(tr => {
        tr.addEventListener('click', function() {
            document.querySelectorAll('.mymodal').forEach(modal => {
                modal.classList.remove('active');
            });
            document.querySelectorAll('.modal-container').forEach(cont => {
                cont.classList.remove('active');
            });
            document.body.style.overflow = 'auto';
        });
    });
});