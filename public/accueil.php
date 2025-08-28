<?php
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }


    require '../includes/connexion.php';
    try {
        $stmt = $conn->query("
            SELECT f.id, f.titre, f.affiche, f.description, f.annee, f.realisateur,
                u.pseudo AS auteur,
                GROUP_CONCAT(DISTINCT g.libelle SEPARATOR '|') AS genres,
                COUNT(DISTINCT fl.id_utilisateur) AS likes,
                f.trailer
            FROM film f
            LEFT JOIN film_like fl ON f.id = fl.id_film
            LEFT JOIN film_genre fg ON f.id = fg.film_id
            LEFT JOIN genre g ON fg.genre_id = g.id
            LEFT JOIN utilisateur u ON f.id_utilisateur = u.id
            WHERE f.valide = 1
            GROUP BY f.id, f.titre, f.affiche, f.description, f.annee, f.realisateur, auteur, f.trailer
            ORDER BY likes DESC
            LIMIT 10
        ");

        $popularFilms = $stmt->fetchAll();
        $stmtLiked = $conn->prepare("SELECT id_film FROM film_like WHERE id_utilisateur = ?");
        $stmtLiked->execute([$_SESSION['user']['id']]);
        $likedFilms = $stmtLiked->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        $popularFilms = [];
        $likedFilms = [];
    }
?>
<!DOCTYPE html>
<?php include '../includes/header.php'; ?>
<head>
    <link rel="stylesheet" href="Css/style.css">
    <style>
    .popular-films {
        overflow-x: auto;
        white-space: nowrap;
        padding-bottom: 1rem;
    }
    .popular-films::-webkit-scrollbar {
        display: none;
    }
    .film {
        display: inline-block;
        max-width: 270px;
        width: 270px;
        height: 150px;
        max-height: 150px;
        cursor: pointer;
        transform: scale(1);
        transition: transform 0.3s ease;
        position: relative;
        border-radius: 6px;
        overflow: hidden;
        background-color: #1f1f1f;
        margin-right: 1rem;
    }
    .film:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        z-index: 2;
    }
    .film_titre {
        position: absolute;
        bottom: 50%;
        transform: translateY(50%);
        right: 0;
        color: #ffffff;
        padding: 5px 0;
        margin: 0;
        font-size: 1.2rem;
        font-weight: 700;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
        background: rgba(0, 0, 0, 0.4);
        padding-left: 8px;
        padding-right: 8px;
        border-top-left-radius: 4px;
        border-bottom-left-radius: 4px;
    }

    .like-container {
        position: absolute;
        bottom: 8px;
        right: 8px;
        font-size: 1.3rem;
    }
    .like-visible {
        display: flex;
        align-items: center;
        gap: 4px;
        color: #FF007F;
    }
    .like-hover {
        display: none;
        color: #FF007F;
        cursor: pointer;
    }
    .film:hover .like-visible {
        display: none;
    }
    .film:hover .like-hover {
        display: block;
    }

    .more.film-modal {
        display: none;
    }
    .more.film-modal.active {
        position: fixed;
        right: 50%;
        top: 40%;
        transform: translate(50%, -50%);
        height: 100%;
        width: 100%;
        z-index: 1000;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .more.film-modal .contenu {
        width: 0%;
        height: 0%;
        background-color: rgb(31, 31, 31);
        margin-top: 6rem;
        border-radius: 3px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        border-bottom: 0;
        transition: width 1s ease, height 1s ease;
    }
    .more.film-modal.active .contenu {
        height: auto;
        max-height: 90vh;
        overflow-y: auto;
        width: 60%;
        transition: width 0.5s ease;
    }
    .affiche {
        width: 100%;
        aspect-ratio: 16 / 9;
        background-size: cover;
        background-position: center;
        box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.5);
        position: relative;
    }
    .contenu .titre {
        font-size: 2.0rem;
        font-weight: 800;
        margin: 0;
        color: white;
        margin-left: 1.4rem;
    }
    .description {
        color: white;
        width: 100%;
        padding: 0 15px;
        font-size: 1.0rem;
        font-weight: 500;
    }
    #modal-actions {
        position: absolute;
        bottom: 2rem;
        left: 2rem;
        z-index: 10;
    }
    .mute-btn {
        position: absolute;
        top: 50%;
        right: 20px;
        transform: translateY(-50%);
        z-index: 10;
    }
    .mute-btn i {
        font-size: 1.6rem;
        color: white;
    }
    </style>
</head>
<body class="position-relative">
    <div class="header">
        <div class="welcome">
            <h1 class="welcome-text">Bienvenue sur <span class="text-detail">Ece Ciné</span></h1>
            <p class="texte mb-4">Découvrez les films préférés de la communauté ECE et partagez vos <br>coups de cœur !</p>
            <button class="btn more btn-danger rounded-4 py-2 px-5 fs-5 fw-medium">Voir tous les films</button>
        </div>
    </div>

    <div class="content text-white pt-5 ps-5">
        <h2>🎥 Films les plus populaires</h2>
        <div class="popular-films">
            <?php foreach ($popularFilms as $film): ?>
                <div class="film film-card"
                    style="background-image:url('<?= htmlspecialchars($film['affiche']) ?>'); background-size:cover; background-position:center;"
                    data-film-id="<?= $film['id'] ?>"
                    data-titre="<?= htmlspecialchars($film['titre']) ?>"
                    data-description="<?= htmlspecialchars($film['description']) ?>"
                    data-annee="<?= htmlspecialchars($film['annee']) ?>"
                    data-realisateur="<?= htmlspecialchars($film['realisateur']) ?>"
                    data-auteur="<?= htmlspecialchars($film['auteur'])??"Utilisateur indisponible" ?>"
                    data-genres="<?= htmlspecialchars($film['genres']) ?>"
                    data-image="<?= htmlspecialchars($film['affiche']) ?>"
                    data-trailer="<?= htmlspecialchars($film['trailer']) ?>"
                >
                    <h5 class="film_titre"><?= htmlspecialchars($film['titre']) ?></h5>
                    <div class="like-container">
                        <div class="like-visible">
                            <span class="like-count"><?= $film['likes'] ?? 0 ?></span>
                            <i class="bi bi-heart-fill"></i>
                        </div>
                        <div class="like-hover" data-film-id="<?= $film['id'] ?>" data-liked="<?= in_array($film['id'], $likedFilms) ? '1' : '0' ?>">
                            <i class="bi <?= in_array($film['id'], $likedFilms) ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
    <div class="more film-modal" id="filmModal">
        <div class="contenu">
            <div class="affiche mt-2 mb-2">
                <div id="player-container"></div>
                <div id="modal-actions" class="d-flex align-items-center gap-2"></div>
            </div>
            <div class="details p-4">
                <form action="" id="form-film">
                    <h3 class="titre text-danger mb-1"></h3>
                    <p class="description ms-2 mb-4 mt-0"></p>
                    <div>
                        <div class="d-flex flex-row justify-content-between px-4 h-100">
                            <div class="d-flex flex-column">
                                <p class="text-secondary">Année : <span class="text-white" id="annee"></span></p>
                                <p class="text-secondary">Réalisateur : <span class="text-white" id="realisateur"></span></p>
                                <p class="text-secondary">Publié par : <span class="text-white" id="auteur"></span></p>
                            </div>
                            <div class="me-5">
                                <p class="text-secondary me-5 m-0">Genres : </p>
                                <div class="d-flex flex-wrap gap-2" id="container-genre"></div>
                            </div>
                            <div class="d-flex align-items-end gap-2">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://www.youtube.com/iframe_api"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.popular-films .like-hover').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const filmId = this.dataset.filmId;
                fetch('like_film.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'film_id=' + encodeURIComponent(filmId)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {

                        const likeVisible = this.parentElement.querySelector('.like-visible');
                        if (likeVisible) {
                            const span = likeVisible.querySelector('.like-count');
                            if (span) span.textContent = data.count;
                        }

                        this.dataset.liked = data.liked ? '1' : '0';
                        const icon = this.querySelector('i');
                        if (icon) {
                            if (data.liked) {
                                icon.classList.remove('bi-heart');
                                icon.classList.add('bi-heart-fill');
                            } else {
                                icon.classList.remove('bi-heart-fill');
                                icon.classList.add('bi-heart');
                            }
                        }
                    } else {
                        alert(data.message || 'Vous devez être connecté pour aimer ce film.');
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du like :', error);
                });
            });
        });

        const cards = document.querySelectorAll('.popular-films .film-card');

        const modal  = document.getElementById('filmModal');
        let playerInstance = null;
        function clearPlayer() {
            if (playerInstance) {
                try {
                    playerInstance.stopVideo();
                    playerInstance.destroy();
                } catch(e) {}
                playerInstance = null;
            }
        }
        function openModal(el) {
            const data = el.dataset;
            const titre = data.titre || '';
            const description = data.description || '';
            const annee = data.annee || '';
            const realisateur = data.realisateur || '';
            const auteur = data.auteur || '';
            const genresStr = data.genres || '';
            const image = data.image || '';
            const trailer = data.trailer || '';
            // Récupérer les éléments du modal
            const titreElem  = modal.querySelector('.titre');
            const descElem   = modal.querySelector('.description');
            const anneeElem  = modal.querySelector('#annee');
            const realElem   = modal.querySelector('#realisateur');
            const auteurElem = modal.querySelector('#auteur');
            const genresDiv  = modal.querySelector('#container-genre');
            titreElem.textContent  = titre;
            descElem.textContent   = description;
            anneeElem.textContent  = annee;
            realElem.textContent   = realisateur;
            auteurElem.textContent = auteur;
            genresDiv.innerHTML = '';
            const genres = genresStr ? genresStr.split('|') : [];
            if (genres.length) {
                genres.forEach(function(g) {
                    const span = document.createElement('span');
                    span.className = 'badge rounded-pill text-white bg-secondary text-decoration-none py-2 px-2 fs-5 fw-normal';
                    span.textContent = g;
                    genresDiv.appendChild(span);
                });
            } else {
                const span = document.createElement('span');
                span.className = 'text-secondary fst-italic';
                span.textContent = 'Aucun genre';
                genresDiv.appendChild(span);
            }
            const afficheCont = modal.querySelector('.affiche');
            clearPlayer();
            afficheCont.innerHTML = '<div id="player-container" style="width:100%; height:100%;"></div><div id="modal-actions" class="d-flex align-items-center gap-2"></div>';
            // Vidéo ou image
            let vid = null;
            if (trailer) {
                try {
                    const url = new URL(trailer);
                    if (url.hostname.includes('youtube.com')) vid = url.searchParams.get('v');
                    else if (url.hostname.includes('youtu.be')) vid = url.pathname.slice(1);
                } catch(err) {
                    vid = null;
                }
            }
            if (vid) {
                playerInstance = new YT.Player('player-container', {
                    height: '100%',
                    width: '100%',
                    videoId: vid,
                    playerVars: {
                        autoplay: 1,
                        controls: 0,
                        showinfo: 0,
                        modestbranding: 1,
                        loop: 1,
                        playlist: vid,
                        rel: 0
                    },
                    events: {
                        onReady: function(e) {
                            e.target.mute();
                            e.target.playVideo();
                            e.target.setPlaybackQuality('highres');
                            e.target.getIframe().style.pointerEvents = 'none';
                        }
                    }
                });
                const actions = modal.querySelector('#modal-actions');
                const playBtn = document.createElement('button');
                playBtn.className = 'btn btn-light btn-lg d-flex align-items-center';
                playBtn.innerHTML = '<i class="bi bi-play-fill me-2"></i>Lecture';
                playBtn.addEventListener('click', function() {
                    if (playerInstance && typeof playerInstance.getPlayerState === 'function') {
                        const state = playerInstance.getPlayerState();
                        if (state === YT.PlayerState.PLAYING) playerInstance.pauseVideo();
                        else playerInstance.playVideo();
                    }
                });
                actions.appendChild(playBtn);
                const muteBtn = document.createElement('button');
                muteBtn.className = 'btn btn-outline-light rounded-circle';
                muteBtn.innerHTML = '<i class="bi bi-volume-mute-fill"></i>';
                muteBtn.addEventListener('click', function() {
                    if (!playerInstance || typeof playerInstance.isMuted !== 'function') return;
                    if (playerInstance.isMuted()) {
                        playerInstance.unMute();
                        muteBtn.innerHTML = '<i class="bi bi-volume-up-fill"></i>';
                    } else {
                        playerInstance.mute();
                        muteBtn.innerHTML = '<i class="bi bi-volume-mute-fill"></i>';
                    }
                });
                actions.appendChild(muteBtn);
            } else {
                afficheCont.style.backgroundImage = `url('${image}')`;
            }
            modal.classList.add('active');
        }
        cards.forEach(function(card) {
            card.addEventListener('click', function(e) {
                // Ne pas ouvrir la modale si on clique sur le like
                if (e.target.closest('.like-hover') || e.target.closest('.like-visible') || e.target.closest('i.bi-heart') || e.target.closest('i.bi-heart-fill')) {
                    return;
                }
                openModal(this);
            });
        });
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('active');
                clearPlayer();
            }
        });
    });
    </script>
</body>
</html>