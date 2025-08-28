<?php 
    $currentPage = basename($_SERVER['PHP_SELF']);
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    
    $islogined = isset($_SESSION['user']);


    if($islogined) {
        // Compter les notifications non lues
        $stmtNotif = $conn->prepare("SELECT COUNT(*) FROM notification WHERE id_utilisateur = ? AND lu = 0");
        $stmtNotif->execute([$_SESSION['user']['id']]);
        $notifCount = $stmtNotif->fetchColumn();
        $stmtList = $conn->prepare(
            "SELECT
                n.id,
                n.id_film,
                n.id_auteur_action,
                n.type,
                n.lu,
                n.created_at,
                COALESCE(f.titre, 'Film supprimé')            AS film_titre,
                COALESCE(u.pseudo, 'Utilisateur supprimé')    AS auteur_action
             FROM notification n
             LEFT JOIN film f          ON n.id_film = f.id
             LEFT JOIN utilisateur u   ON n.id_auteur_action = u.id
             WHERE n.id_utilisateur = ?
             ORDER BY n.created_at DESC
             LIMIT 6"
        );
        $stmtList->execute([$_SESSION['user']['id']]);
        $notifications = $stmtList->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $notifCount = 0;
        $notifications = [];
    }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>ECE Ciné</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #141414;
        }

        .navbar {
            background-color: #000 !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .navbar .navbar-brand {
            color: white !important;
            font-weight: 700;
            font-size: 1.5rem;
            transition: color 0.2s ease-in-out;
        }

        .navbar .navbar-brand:hover {
            color: #E50914 !important;
        }

        .navbar .nav-link {
            position: relative;
            color: #adb5bd !important;
            font-weight: 500;
            transition: color 0.2s ease-in-out, font-weight 0.2s ease-in-out;
            white-space: nowrap;
        }
        
        .navbar .nav-link:hover,
        .navbar .nav-link.active {
            color: #fff !important;
            font-weight: 700;
        }

        .navbar .nav-link::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0%;
            height: 2px;
            background-color: #E50914;
            transition: width 0.3s ease;
        }

        .navbar .nav-link:hover::after,
        .navbar .nav-link.active::after {
            width: 100%;
        }
        

        .navbar .user-avatar {
            transition: transform 0.2s ease-in-out, border-color 0.2s ease-in-out;
            width: 40px; 
            height: 40px;
            border: 2px solid transparent;
        }

        .navbar .dropdown-toggle:hover .user-avatar {
            cursor: pointer;
            transform: scale(1.15);
            border-color: #E50914;
        }

        .notif{
            cursor:pointer;
            font-size: 1.25rem;
        }

        .notif-container {
            position: relative;
        }
        .notif-container .notif-count-indicator {
            position: absolute;
            top: 2px;
            right: 0px;
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background-color: #E50914;
        }
        .notif-container .notif-dropdown {
            position: absolute;
            top: 110%;
            right: 0;
            min-width: 320px;
            max-width: 360px;
            background-color: #1f1f1f;
            color: #fff;
            border: 1px solid #333;
            border-radius: 4px;
            z-index: 1000;
            display: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.5);
        }
        .notif-dropdown .notif-header,
        .notif-dropdown .notif-footer {
            padding: 0.5rem 0.75rem;
            font-weight: 600;
            border-bottom: 1px solid #333;
        }
        .notif-dropdown .notif-list {
            max-height: 300px;
            overflow-y: auto;
        }
        .notif-dropdown .notif-item {
            padding: 0.5rem 0.75rem;
            border-bottom: 1px solid #333;
            font-size: 0.9rem;
        }
        .notif-dropdown .notif-item:last-child {
            border-bottom: none;
        }
        .notif-dropdown .notif-item.unread {
            background-color: #2a2a2a;
        }
        .notif-dropdown .like-author {
            font-weight: 700;
            color: #E50914;
        }
        .notif-dropdown .like-film {
            font-style: italic;
        }
        .notif-dropdown .notif-footer a {
            color: #E50914;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .notif-dropdown .notif-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg py-2">
  <div class="container-fluid">
    
    <a class="navbar-brand ms-4 me-5" href="accueil.php">ECE Ciné</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNavbar">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0 d-flex flex-row align-items-center gap-3">
        <li class="nav-item">
          <a class="nav-link <?=($currentPage=="accueil.php")?'active':''?>" href="../public/accueil.php">Accueil</a>
        </li>

        <li class="nav-item">
          <a class="nav-link <?=($currentPage=="films_list.php")?'active':''?>" href="../public/films_list.php">Tout parcourir</a>
        </li>

        <li class="nav-item">
          <a class="nav-link <?=($currentPage=="partager.php")?'active':''?>" href="../public/partager.php">Partager</a>
        </li>
      </ul>

      <div class="d-flex pe-4 gap-3 h-100 align-items-center">
      <?php if (isset($_SESSION["user"]["statut"])):?>
        <div class="notif-container w-100 h-100 d-flex align-items-center position-relative">
            <i class="notif bi bi-bell-fill text-white position-relative"></i>
            <?php if($notifCount > 0): ?>
                <span class="notif-count-indicator"></span>
            <?php endif; ?>
            <div class="notif-dropdown">
                <div class="notif-header">
                    <span>NOTIFICATIONS</span>
                </div>
                <div class="notif-list">
                    <?php if (empty($notifications)): ?>
                        <div class="notif-item text-center">Vous n'avez pas de notification</div>
                    <?php else: ?>
                        <?php foreach($notifications as $n): ?>
                            <div class="notif-item <?=($n['lu']==0?'unread':'')?>">
                                <span class="like-author"><?= htmlspecialchars($n['auteur_action'])??"Utilisateur indisponible" ?></span>
                                <?php if ($n['type'] === 'like'): ?> a aimé votre film
                                <?php else: ?> a réalisé une action sur votre film
                                <?php endif; ?>
                                <span class="like-film"><?= htmlspecialchars($n['film_titre'])??"film indisponible" ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif ?>
        <?php if($islogined): ?>
            <div class="dropdown">
                <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
                    <img src="<?=htmlspecialchars($_SESSION["user"]["avatar"]) ?>" alt="Profil" class="rounded-circle user-avatar">
                </a>
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
                    <li><a class="dropdown-header text-decoration-none">Connecté : <span class="fw-semibold"><?=$_SESSION["user"]['pseudo']?></span></a></li>
                    <li><a class="dropdown-header text-decoration-none text-secondary">Statut : <span class="fw-semibold"><?=htmlspecialchars(strtolower($_SESSION["user"]["statut"])) ?></span></a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="../public/compte.php"><i class="bi bi-person-circle"></i> Mon Compte</a></li>
                    <?php if (isset($_SESSION["user"]["statut"]) && ($_SESSION["user"]["statut"] == "administrateur"||$_SESSION["user"]["statut"] == "administratif" || $_SESSION["user"]["statut"] == "etudiant")): ?>
                        <li><a class="dropdown-item" href="../public/admin.php"><i class="bi bi-shield-lock-fill"></i> Gérer</a></li>
                    <?php endif; ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="deconnexion.php"><i class="bi bi-box-arrow-right"></i> Déconnexion</a></li>
                </ul>
            </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const notifContainer = document.querySelector('.notif-container');
    if (!notifContainer) return;
    const bellIcon = notifContainer.querySelector('.notif');
    const dropdown = notifContainer.querySelector('.notif-dropdown');
    const markReadUrl = 'notifications.php?action=markRead';

    bellIcon.addEventListener('click', function(e) {
        e.stopPropagation();
        const isVisible = dropdown.style.display === 'block';
        dropdown.style.display = isVisible ? 'none' : 'block';
        console.log("lu")
        if (!isVisible) {

            const indicator = notifContainer.querySelector('.notif-count-indicator');
            if (indicator) {
                fetch(markReadUrl, {method: 'GET'})
                    .then(() => {
                        indicator.remove();
                    })
                    .catch(() => {});
            }
        }
    });

    document.addEventListener('click', function() {
        dropdown.style.display = 'none';
    });

    dropdown.addEventListener('click', function(e) {
        e.stopPropagation();
    });
});
</script>