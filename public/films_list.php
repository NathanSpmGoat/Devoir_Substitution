<?php
    include '../includes/connexion.php';

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }


    $query = isset($_GET['q']) ? trim($_GET['q']) : '';
    $selectedGenre = isset($_GET['genre']) ? intval($_GET['genre']) : 0;

    $genres = [];
    try {
        $genreStmt = $conn->query("SELECT id, libelle FROM genre ORDER BY libelle ASC");
        $genres = $genreStmt->fetchAll();
    } catch (Exception $e) {
        die($e);
    }

    $sql = "SELECT f.id, f.titre, f.affiche, f.description, f.annee, f.realisateur, u.pseudo AS auteur, f.trailer, ";
    $sql .= "GROUP_CONCAT(DISTINCT g.libelle SEPARATOR '|') AS genres\n";
    $sql .= "FROM film f\n";
    $sql .= "LEFT JOIN utilisateur u ON f.id_utilisateur = u.id\n";
    $sql .= "LEFT JOIN film_genre fg ON f.id = fg.film_id\n";
    $sql .= "LEFT JOIN genre g ON fg.genre_id = g.id\n";

    $params = [];
    $conditions = [];
    // Si un genre est sélectionné, on filtre via un join supplémentaire
    if ($selectedGenre > 0) {
        $sql .= "JOIN film_genre fg_filter ON f.id = fg_filter.film_id AND fg_filter.genre_id = ?\n";
        $params[] = $selectedGenre;
    }

    if ($query !== '') {
        $conditions[] = "((f.titre LIKE ? OR f.description LIKE ?))";
        $params[] = '%' . $query . '%';
        $params[] = '%' . $query . '%';
    }

    $conditions[] = "f.valide = 1";
    if (!empty($conditions)) {
        $sql .= "WHERE " . implode(" AND ", $conditions) . "\n";
    }

    $sql .= "GROUP BY f.id, f.titre, f.affiche, f.description, f.annee, f.realisateur, auteur, f.trailer\n";

    $sql .= "ORDER BY f.titre ASC";

    $films = [];
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $films = $stmt->fetchAll();
    } catch (Exception $e) {
        // ignorer les erreurs de récupération des films
    }

    $likesCount = [];
    $likedFilms = [];
    try {
        $stmtLikes = $conn->query("SELECT id_film, COUNT(*) as likes FROM film_like GROUP BY id_film");
        $likesCount = $stmtLikes->fetchAll(PDO::FETCH_KEY_PAIR);
        if (isset($_SESSION['user'])) {
            $userId = $_SESSION['user']['id'];
            $stmtLiked = $conn->prepare("SELECT id_film FROM film_like WHERE id_utilisateur = ?");
            $stmtLiked->execute([$userId]);
            $likedFilms = $stmtLiked->fetchAll(PDO::FETCH_COLUMN);
        }
    } catch (Exception $e) {
        
    }
?>
<!DOCTYPE html>
<?php include '../includes/header.php'; ?>
<head>
    <style>

    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;700&display=swap');
    body {
        background-color: #121212;
        color: #ffffff;
        font-family: 'Poppins', sans-serif;
    }
    .browse-title {
        font-weight: 700;
        color: #e50914; 
    }
    .search-form .form-control,
    .search-form .form-select {
        background-color: #1f1f1f;
        border: 1px solid #444;
        color: #fff;
    }
    .search-form .form-control::placeholder {
        color: #888;
    }

    .film {
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
    .btn-danger {
        background-color: #e50914;
        border-color: #e50914;
    }
    .btn-danger:hover {
        background-color: #f6121d;
        border-color: #f6121d;
    }

    html,body{
        height: 100%;
    }

    /* Styles pour l'affichage des likes */
    .like-container {
        position: absolute;
        bottom: 8px;
        right: 8px;
        font-size: 1.1rem;
    }
    /* Affichage par défaut : nombre de likes et cœur rempli */
    .like-visible {
        display: flex;
        align-items: center;
        gap: 4px;
        color: #FF007F;
    }
    /* Cœur vide à afficher au survol */
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


    .more{
        display:none;
    }
    .more.active{
        position:fixed;
        right:50%;
        top:50%;
        transform: translate(50%, -50%);
        height: 100%;
        width: 100%;
        z-index: 1000;
        background-color: rgba(0, 0, 0, 0.5);
        display:flex;
        justify-content: center;
        align-items: center;
    }
    .more .contenu{
        width: 0%;
        height: 0%;
        background-color: rgb(31, 31, 31);
        margin-top:6rem;
        border-radius:3px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        border-bottom: 0;
        transition:width 1s ease, height 1s ease;
    }
    .more.active .contenu{
        height: auto;
        max-height: 90vh;
        overflow-y: auto; 
        width: 60%;
        transition:width 0.5s ease;
    }
    .affiche{
        width: 100%;
        aspect-ratio: 16 / 9;
        background-size: cover;
        background-position: center;
        box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.5);
        position: relative;
    }
    .contenu .titre{
        font-size: 2.0rem;
        font-weight: 800;
        margin: 0;
        color:white;
        margin-left:1.4rem;
    }
    .description{
        color: white;
        width: 100%;
        padding:0 15px;
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
<body class="d-flex flex-column">
    <div class="container mt-5 flex-grow-1">
        <h2 class="browse-title mb-4">Parcourir les films</h2>
        <form method="get" class="row g-3 mb-4 search-form">
            <div class="col-md-6">
                <input type="text" name="q" class="form-control" placeholder="Rechercher un film..." value="<?= htmlspecialchars($query) ?>">
            </div>
            <div class="col-md-4">
                <select name="genre" class="form-select">
                    <option value="0">Tous les genres</option>
                    <?php foreach ($genres as $genre): ?>
                        <option value="<?= $genre['id'] ?>" <?= $selectedGenre == $genre['id'] ? 'selected' : '' ?>><?= htmlspecialchars($genre['libelle']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-danger w-100">Rechercher</button>
            </div>
        </form>

        <div class="row g-4">
            <?php if (empty($films)): ?>
                <div class="col-12">
                    <p>Aucun film trouvé pour cette recherche.</p>
                </div>
            <?php else: ?>
                <?php foreach ($films as $film): ?>
                    <div class="col-sm-6 col-md-4 col-lg-3 d-flex justify-content-center">
                        <!-- Carte de film interactive -->
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
                                    <span class="like-count"><?= isset($likesCount[$film['id']]) ? $likesCount[$film['id']] : 0 ?></span>
                                    <i class="bi bi-heart-fill"></i>
                                </div>
                                <div class="like-hover" data-film-id="<?= $film['id'] ?>" data-liked="<?= in_array($film['id'], $likedFilms) ? '1' : '0' ?>">
                                    <i class="bi <?= in_array($film['id'], $likedFilms) ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.like-hover').forEach(function(btn) {
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
                            const countSpan = likeVisible.querySelector('.like-count');
                            if (countSpan) {
                                countSpan.textContent = data.count;
                            }
                        }

                        this.dataset.liked = data.liked ? '1' : '0';

                        const iconElem = this.querySelector('i');
                        if (iconElem) {
                            if (data.liked) {
                                iconElem.classList.remove('bi-heart');
                                iconElem.classList.add('bi-heart-fill');
                            } else {
                                iconElem.classList.remove('bi-heart-fill');
                                iconElem.classList.add('bi-heart');
                            }
                        }
                    } else {
                        alert(data.message || 'Vous devez être connecté pour aimer ce film.');
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du like :', error);
                });
            });
        });
    });
    </script>

    <!-- Fenêtre de détails pour les films (modale) -->
    <div class="more">
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
                                <p class="text-secondary">Année : <span class="text-white" id="annee"></span></p>
                                <p class="text-secondary">Réalisateur : <span class="text-white" id="realisateur"></span></p>
                                <p class="text-secondary">Publié par : <span class="text-white" id="auteur"></span></p>
                            </div>
                            <div class="me-5">
                                <p class="text-secondary me-5 m-0">Genres : </p>
                                <div class="d-flex flex-wrap gap-2" id="container-genre"></div>
                            </div>
                            <div class="d-flex align-items-end gap-2">
                                <!-- Espace réservé pour des actions futures -->
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Charger l'API YouTube pour lire les trailers -->
    <script src="https://www.youtube.com/iframe_api"></script>
    <!-- Script d'ouverture et fermeture de la modale de film -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const filmsCard = document.querySelectorAll('.film-card');
        const modal      = document.querySelector('.more');
        let player = null;

        function clearPlayer() {
            if (player) {
                try {
                    player.stopVideo();
                    player.destroy();
                } catch(e) {}
                player = null;
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
            const titreElem = modal.querySelector('.titre');
            const descElem  = modal.querySelector('.description');
            const anneeElem = modal.querySelector('#annee');
            const realElem  = modal.querySelector('#realisateur');
            const auteurElem= modal.querySelector('#auteur');
            const genreContainer = modal.querySelector('#container-genre');
            titreElem.textContent = titre;
            descElem.textContent  = description;
            anneeElem.textContent = annee;
            realElem.textContent  = realisateur;
            auteurElem.textContent= auteur;
            genreContainer.innerHTML = '';
            const genres = genresStr ? genresStr.split('|') : [];
            if (genres.length) {
                genres.forEach(function(g) {
                    const span = document.createElement('span');
                    span.className = 'badge rounded-pill text-white bg-secondary text-decoration-none py-2 px-2 fs-5 fw-normal';
                    span.textContent = g;
                    genreContainer.appendChild(span);
                });
            } else {
                const span = document.createElement('span');
                span.className = 'text-secondary fst-italic';
                span.textContent = 'Aucun genre';
                genreContainer.appendChild(span);
            }
            const afficheContainer = modal.querySelector('.affiche');
            // Remettre le contenu par défaut et effacer la vidéo
            clearPlayer();
            afficheContainer.innerHTML = '<div id="player-container" style="width:100%; height:100%;"></div><div id="modal-actions" class="d-flex align-items-center gap-2"></div>';

            // Gestion de la vidéo ou de l'image
            let videoId = null;
            if (trailer) {
                try {
                    const url = new URL(trailer);
                    if (url.hostname.includes('youtube.com')) {
                        videoId = url.searchParams.get('v');
                    } else if (url.hostname.includes('youtu.be')) {
                        videoId = url.pathname.slice(1);
                    }
                } catch(err) {
                    videoId = null;
                }
            }
            if (videoId) {
                player = new YT.Player('player-container', {
                    height:'100%',
                    width:'100%',
                    videoId: videoId,
                    playerVars: {
                        autoplay: 1,
                        controls: 0,
                        showinfo: 0,
                        modestbranding: 1,
                        loop: 1,
                        playlist: videoId,
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
                playBtn.id = 'play-pause-btn';
                playBtn.className = 'btn btn-light btn-lg d-flex align-items-center';
                playBtn.innerHTML = '<i class="bi bi-play-fill me-2"></i>Lecture';
                playBtn.addEventListener('click', function() {
                    if (player && typeof player.getPlayerState === 'function') {
                        const state = player.getPlayerState();
                        if (state === YT.PlayerState.PLAYING) player.pauseVideo();
                        else player.playVideo();
                    }
                });
                actions.appendChild(playBtn);
                const muteBtn = document.createElement('button');
                muteBtn.className = 'btn btn-outline-light rounded-circle';
                muteBtn.innerHTML = '<i class="bi bi-volume-mute-fill"></i>';
                muteBtn.addEventListener('click', function() {
                    if (!player || typeof player.isMuted !== 'function') return;
                    if (player.isMuted()) {
                        player.unMute();
                        muteBtn.innerHTML = '<i class="bi bi-volume-up-fill"></i>';
                    } else {
                        player.mute();
                        muteBtn.innerHTML = '<i class="bi bi-volume-mute-fill"></i>';
                    }
                });
                actions.appendChild(muteBtn);
            } else {
                afficheContainer.style.backgroundImage = `url('${image}')`;
            }
            modal.classList.add('active');
        }

        filmsCard.forEach(function(card) {
            card.addEventListener('click', function(e) {
                // ne pas ouvrir si on clique sur le bouton de like
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