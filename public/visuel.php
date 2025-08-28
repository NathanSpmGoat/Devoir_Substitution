<?php
    if (session_status() == PHP_SESSION_NONE) { session_start(); }
    if (!isset($_SESSION['user'])) {
        $_SESSION['user'] = ['pseudo' => 'Visiteur', 'avatar' => 'https://i.pravatar.cc/40', 'role' => 'user'];
    }
    // Données du film avec plus de détails
    $film = [
        'id' => 1, 'titre' => 'The Flash', 'annee' => 2023, 'genre' => 'Action, Aventure, Fantastique',
        'realisateur' => 'Andy Muschietti',
        'description' => "Barry Allen utilise ses super-pouvoirs pour voyager dans le temps et changer le cours des événements. Sa tentative de sauver sa famille altère l'avenir...",
        'utilisateur_publication' => 'NathanSpm',
        'poster_url' => 'https://m.media-amazon.com/images/M/MV5BZWE2OTCyMDgtM2VhMy00NjA3LTg3NjYtNTIwMGExNGQ2ZWM3XkEyXkFqcGdeQXVyMTEyMjM2NDc2._V1_FMjpg_UX1000_.jpg',
        'trailer_url' => 'https://www.youtube.com/embed/hebWYacbdvc?autoplay=1&mute=1&loop=1&playlist=hebWYacbdvc&controls=0&showinfo=0',
        'logo_url' => 'https://image.tmdb.org/t/p/original/t5Tgmimvwe0SjC4VvvrLSvAnQG.png' // Logo du film en PNG
    ];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prototype Modal Netflix - ECE Ciné</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap');
        :root { --ece-red: #E50914; }
        body { font-family: 'Poppins', sans-serif; background-color: #141414; }
        .movie-card { border-radius: 8px; overflow: hidden; transition: transform 0.3s ease; cursor: pointer; }
        .movie-card:hover { transform: scale(1.05); }
        .card-poster { width: 100%; display: block; }

        /* --- Style du Modal Détaillé --- */
        .modal-dialog { max-width: 900px; }
        .modal-content { background-color: #181818; color: white; border-radius: 8px; overflow:hidden; }
        .modal-header { border-bottom: none; z-index: 10; }
        .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }
        .modal-body { padding: 0; }

        .modal-video-container { position: relative; height: 500px; }
        .modal-video-bg { position: absolute; top: 50%; left: 50%; width: 100%; height: 100%; object-fit: cover; transform: translate(-50%, -50%); z-index: 1; pointer-events: none; }
        .modal-content-overlay { position: absolute; bottom: 0; left: 0; right: 0; padding: 2rem; background: linear-gradient(to top, rgba(24,24,24,1) 10%, rgba(24,24,24,0.7) 50%, transparent 100%); z-index: 5; }
        
        .movie-logo { max-width: 400px; max-height: 150px; margin-bottom: 1.5rem; }
        .action-icon { font-size: 1.2rem; }
        .details-section { padding: 2rem; background-color: #181818; }
        .details-section .text-muted { color: #808080 !important; font-size: 0.9rem; }
        .details-section .genre-tag { display: inline-block; padding: 0.3rem 0.8rem; border: 1px solid #404040; border-radius: 20px; margin-right: 0.5rem; margin-bottom: 0.5rem; }
    </style>
</head>
<body class="text-white">

    <div class="container py-5">
        <h1>Clique sur l'affiche pour voir le modal</h1>
        <a href="#" data-bs-toggle="modal" data-bs-target="#modal-film-<?= $film['id'] ?>">
            <div class="movie-card bg-dark" style="max-width: 200px;"><img src="<?= htmlspecialchars($film['poster_url']) ?>" class="card-poster"></div>
        </a>
    </div>

    <div class="modal fade" id="modal-film-<?= $film['id'] ?>" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header position-absolute top-0 end-0">
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            
            <div class="modal-video-container">
              <iframe class="modal-video-bg" src="<?= htmlspecialchars($film['trailer_url']) ?>" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
              
              <div class="modal-content-overlay d-flex flex-column justify-content-end">
                <img src="<?= htmlspecialchars($film['logo_url']) ?>" alt="<?= htmlspecialchars($film['titre']) ?> Logo" class="movie-logo">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <button class="btn btn-light btn-lg d-flex align-items-center"><i class="bi bi-play-fill me-2"></i>Lecture</button>
                    <button class="btn btn-outline-light rounded-circle action-icon"><i class="bi bi-plus"></i></button>
                    <button class="btn btn-outline-light rounded-circle action-icon"><i class="bi bi-hand-thumbs-up"></i></button>
                </div>
                <p><?= htmlspecialchars($film['description']) ?></p>
              </div>
            </div>

            <div class="details-section">
                <div class="row">
                    <div class="col-md-8">
                        <p class="mb-1"><span class="text-muted">Année :</span> <?= htmlspecialchars($film['annee']) ?></p>
                        <p class="mb-1"><span class="text-muted">Réalisateur :</span> <?= htmlspecialchars($film['realisateur']) ?></p>
                        <p class="mb-1"><span class="text-muted">Publié par :</span> <?= htmlspecialchars($film['utilisateur_publication']) ?></p>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted mb-1">Genres :</p>
                        <div>
                            <?php foreach(explode(', ', $film['genre']) as $g): ?>
                                <span class="genre-tag"><?= htmlspecialchars($g) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                const iframe = modal.querySelector('iframe');
                if (!iframe) return;
                const originalSrc = iframe.src;
                modal.addEventListener('show.bs.modal', () => iframe.src = originalSrc);
                modal.addEventListener('hide.bs.modal', () => iframe.src = '');
            });
        });
    </script>
</body>
</html>