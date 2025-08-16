<?php
// Pour ce test, on peut simuler le rôle de l'utilisateur.
// Changez la valeur pour 'enseignant' ou 'administratif' pour voir les différences.
$user_role = 'administrateur'; 

// Logique pour déterminer la page active à afficher
$page = $_GET['page'] ?? 'dashboard'; // Par défaut, on affiche le tableau de bord

// Inclusion du header qui contient le début du HTML
include '../includes/header.php'; 
?>

<style>
    body {
        /* On s'assure que le fond est bien sombre pour cette page spécifique */
        background-color: #212529; 
    }

    .admin-wrapper {
        display: flex;
        min-height: 100vh;
    }

    .sidebar {
        width: 280px;
        background-color: #111; /* Un noir plus profond pour le contraste */
        padding: 20px;
        color: white;
        position: fixed; /* Le menu latéral reste fixe lors du défilement */
        height: 100%;
        overflow-y: auto; /* Permet de scroller le menu si nécessaire */
    }

    .sidebar h2 {
        color: #0d6efd; /* text-primary de Bootstrap */
        text-align: center;
        margin-bottom: 2rem;
        font-weight: bold;
    }

    .sidebar .nav-link {
        color: #adb5bd; /* Gris clair pour le texte des liens */
        font-size: 1.1rem;
        padding: 10px 15px;
        border-radius: 0.25rem;
        margin-bottom: 0.5rem;
        transition: all 0.2s ease-in-out;
    }

    .sidebar .nav-link.active,
    .sidebar .nav-link:hover {
        background-color: #0d6efd;
        color: white;
        box-shadow: 0 0 10px rgba(13, 110, 253, 0.5); /* Effet lumineux */
    }

    .admin-content {
        margin-left: 280px; /* On décale le contenu pour laisser la place au menu */
        padding: 40px;
        width: calc(100% - 280px); /* Le contenu prend le reste de la largeur */
    }

    .stat-card {
        background-color: #343a40; /* bg-secondary de Bootstrap */
        border: none;
        border-radius: 0.5rem;
        color: white;
        text-align: center;
        padding: 20px;
    }

    .stat-card .stat-number {
        font-size: 2.5rem;
        font-weight: bold;
        color: #0d6efd;
    }

    /* Style personnalisé pour les tableaux */
    .table-dark-custom {
        background-color: #343a40;
    }
    .table-dark-custom th {
        background-color: #212529;
    }
    .table-dark-custom .film-poster {
        width: 60px;
        height: 90px;
        object-fit: cover;
        border-radius: 4px;
    }
    .table-dark-custom .user-avatar {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 50%;
        margin-right: 10px;
    }
</style>

<div class="admin-wrapper">
    <aside class="sidebar">
        <h2>ECE Ciné Admin</h2>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($page == 'dashboard') ? 'active' : ''; ?>" href="admin.php?page=dashboard">
                    📊 Tableau de bord
                </a>
            </li>

            <?php if (in_array($user_role, ['enseignant', 'administratif', 'administrateur'])): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo ($page == 'films') ? 'active' : ''; ?>" href="admin.php?page=films">
                    🎬 Validation des Films
                </a>
            </li>
            <?php endif; ?>

            <?php if (in_array($user_role, ['administratif', 'administrateur'])): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo ($page == 'inscriptions') ? 'active' : ''; ?>" href="admin.php?page=inscriptions">
                    👤 Validation des Inscriptions
                </a>
            </li>
            <?php endif; ?>

            <?php if ($user_role == 'administrateur'): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo ($page == 'utilisateurs') ? 'active' : ''; ?>" href="admin.php?page=utilisateurs">
                    ⚙️ Gestion des Utilisateurs
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </aside>

    <main class="admin-content">
        <?php
        // Affichage de la section demandée via le paramètre 'page' dans l'URL
        if ($page == 'dashboard'):
        ?>
            <h1 class="text-white mb-5">Tableau de bord</h1>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="stat-card">
                        <h5>Inscriptions en attente</h5>
                        <p class="stat-number">4</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <h5>Films à valider</h5>
                        <p class="stat-number">3</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <h5>Nombre total d'utilisateurs</h5>
                        <p class="stat-number">142</p>
                    </div>
                </div>
            </div>
            <div class="row mt-5">
                <div class="col-md-6">
                    <h4 class="text-white mb-3">Dernières inscriptions à valider</h4>
                     <ul class="list-group">
                        <li class="list-group-item bg-dark text-white d-flex justify-content-between align-items-center">Chloé Dubois <a href="admin.php?page=inscriptions" class="btn btn-primary btn-sm">Voir</a></li>
                        <li class="list-group-item bg-dark text-white d-flex justify-content-between align-items-center">Lucas Garcia <a href="admin.php?page=inscriptions" class="btn btn-primary btn-sm">Voir</a></li>
                    </ul>
                </div>
                 <div class="col-md-6">
                    <h4 class="text-white mb-3">Derniers films à valider</h4>
                     <ul class="list-group">
                        <li class="list-group-item bg-dark text-white d-flex justify-content-between align-items-center">"Oppenheimer" par user22 <a href="admin.php?page=films" class="btn btn-primary btn-sm">Voir</a></li>
                        <li class="list-group-item bg-dark text-white d-flex justify-content-between align-items-center">"Anatomie d'une chute" par cinephile75 <a href="admin.php?page=films" class="btn btn-primary btn-sm">Voir</a></li>
                    </ul>
                </div>
            </div>

        <?php
        elseif ($page == 'films' && in_array($user_role, ['enseignant', 'administratif', 'administrateur'])):
        ?>
            <h1 class="text-white mb-5">Validation des films partagés</h1>
            <p class="text-white-50">Voici la liste des films proposés par les utilisateurs et en attente de validation.</p>
            <table class="table table-dark-custom table-hover align-middle">
                <thead>
                    <tr>
                        <th>Affiche</th>
                        <th>Titre</th>
                        <th>Proposé par</th>
                        <th>Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><img src="https://via.placeholder.com/60x90?text=Film1" class="film-poster" alt="Affiche"></td>
                        <td>Inception</td>
                        <td>jd_cinephile</td>
                        <td>2025-08-12</td>
                        <td class="text-center">
                            <button class="btn btn-success btn-sm">Approuver</button>
                            <button class="btn btn-danger btn-sm">Refuser</button>
                        </td>
                    </tr>
                     <tr>
                        <td><img src="https://via.placeholder.com/60x90?text=Film2" class="film-poster" alt="Affiche"></td>
                        <td>Oppenheimer</td>
                        <td>user22</td>
                        <td>2025-08-11</td>
                        <td class="text-center">
                            <button class="btn btn-success btn-sm">Approuver</button>
                            <button class="btn btn-danger btn-sm">Refuser</button>
                        </td>
                    </tr>
                     <tr>
                        <td><img src="https://via.placeholder.com/60x90?text=Film3" class="film-poster" alt="Affiche"></td>
                        <td>Anatomie d'une chute</td>
                        <td>cinephile75</td>
                        <td>2025-08-10</td>
                        <td class="text-center">
                            <button class="btn btn-success btn-sm">Approuver</button>
                            <button class="btn btn-danger btn-sm">Refuser</button>
                        </td>
                    </tr>
                </tbody>
            </table>

        <?php
        elseif ($page == 'inscriptions' && in_array($user_role, ['administratif', 'administrateur'])):
        ?>
            <h1 class="text-white mb-5">Validation des nouvelles inscriptions</h1>
            <div class="alert alert-info">Rappel : Toute inscription incomplète sera automatiquement refusée.</div>
            <table class="table table-dark-custom table-hover align-middle">
                 <thead>
                    <tr>
                        <th>Nom & Prénom</th>
                        <th>Email</th>
                        <th>Statut demandé</th>
                        <th>Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (in_array($user_role, ['administratif', 'administrateur'])): ?>
                    <tr>
                        <td>Alice MARTIN</td>
                        <td>alice.martin@ece.fr</td>
                        <td><span class="badge bg-primary">Étudiant</span></td>
                        <td>2025-08-11</td>
                        <td class="text-center">
                            <button class="btn btn-success btn-sm">Valider</button>
                            <button class="btn btn-danger btn-sm">Refuser</button>
                        </td>
                    </tr>
                    <tr>
                        <td>Marc DUBOIS</td>
                        <td>marc.dubois@ece.fr</td>
                        <td><span class="badge bg-info text-dark">Enseignant</span></td>
                        <td>2025-08-09</td>
                        <td class="text-center">
                            <button class="btn btn-success btn-sm">Valider</button>
                            <button class="btn btn-danger btn-sm">Refuser</button>
                        </td>
                    </tr>
                    <?php endif; ?>
                     <?php if ($user_role == 'administrateur'): ?>
                    <tr>
                        <td>Bob LEFEBVRE</td>
                        <td>bob.lefebvre@ece.fr</td>
                        <td><span class="badge bg-warning text-dark">Administratif</span></td>
                        <td>2025-08-10</td>
                        <td class="text-center">
                            <button class="btn btn-success btn-sm">Valider</button>
                            <button class="btn btn-danger btn-sm">Refuser</button>
                        </td>
                    </tr>
                     <tr>
                        <td>Carole PETIT</td>
                        <td>carole.petit@ece.fr</td>
                        <td><span class="badge bg-warning text-dark">Administratif</span></td>
                        <td>2025-08-08</td>
                        <td class="text-center">
                            <button class="btn btn-success btn-sm">Valider</button>
                            <button class="btn btn-danger btn-sm">Refuser</button>
                        </td>
                    </tr>
                     <?php endif; ?>
                </tbody>
            </table>

        <?php
        elseif ($page == 'utilisateurs' && $user_role == 'administrateur'):
        ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="text-white mb-0">Gestion de la communauté ECE Ciné</h1>
                <button class="btn btn-primary">➕ Ajouter un utilisateur</button>
            </div>
            <p class="text-white-50 mb-4">Recherchez, filtrez, modifiez ou radiez un membre de la communauté.</p>
            
            <div class="card bg-dark mb-4">
                <div class="card-body d-flex gap-3">
                    <input type="text" class="form-control" placeholder="Rechercher par nom, pseudo ou email...">
                    <select class="form-select" style="max-width: 200px;">
                        <option selected>Filtrer par statut</option>
                        <option value="etudiant">Étudiant</option>
                        <option value="enseignant">Enseignant</option>
                        <option value="administratif">Administratif</option>
                    </select>
                    <button class="btn btn-primary">Filtrer</button>
                </div>
            </div>

            <table class="table table-dark-custom table-hover align-middle">
                 <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Email</th>
                        <th>Statut</th>
                        <th>Membre depuis</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="https://via.placeholder.com/40" class="user-avatar" alt="Avatar">
                                Jean DUPONT (jd_cinephile)
                            </div>
                        </td>
                        <td>jean.dupont@ece.fr</td>
                        <td><span class="badge bg-primary">Étudiant</span></td>
                        <td>2024-09-01</td>
                        <td class="text-center">
                             <button class="btn btn-outline-light btn-sm">Éditer</button>
                            <button class="btn btn-danger btn-sm">Radier</button>
                        </td>
                    </tr>
                     <tr>
                         <td>
                            <div class="d-flex align-items-center">
                                <img src="https://via.placeholder.com/40" class="user-avatar" alt="Avatar">
                                Marie CURIE (mc_sciences)
                            </div>
                        </td>
                        <td>marie.curie@ece.fr</td>
                        <td><span class="badge bg-info text-dark">Enseignant</span></td>
                        <td>2023-10-20</td>
                        <td class="text-center">
                            <button class="btn btn-outline-light btn-sm">Éditer</button>
                            <button class="btn btn-danger btn-sm">Radier</button>
                        </td>
                    </tr>
                    <tr>
                         <td>
                            <div class="d-flex align-items-center">
                                <img src="https://via.placeholder.com/40" class="user-avatar" alt="Avatar">
                                Louis PASTEUR (lp_admin)
                            </div>
                        </td>
                        <td>louis.pasteur@ece.fr</td>
                        <td><span class="badge bg-warning text-dark">Administratif</span></td>
                        <td>2023-09-15</td>
                        <td class="text-center">
                            <button class="btn btn-outline-light btn-sm">Éditer</button>
                            <button class="btn btn-danger btn-sm">Radier</button>
                        </td>
                    </tr>
                     <tr>
                         <td>
                            <div class="d-flex align-items-center">
                                <img src="https://via.placeholder.com/40" class="user-avatar" alt="Avatar">
                                Léa MOREAU (lili24)
                            </div>
                        </td>
                        <td>lea.moreau@ece.fr</td>
                        <td><span class="badge bg-primary">Étudiant</span></td>
                        <td>2024-11-05</td>
                        <td class="text-center">
                            <button class="btn btn-outline-light btn-sm">Éditer</button>
                            <button class="btn btn-danger btn-sm">Radier</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item disabled"><a class="page-link" href="#">Précédent</a></li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">Suivant</a></li>
                </ul>
            </nav>
            
        <?php
        else:
        ?>
            <h1 class="text-danger">Accès non autorisé</h1>
            <p class="text-white">Vous n'avez pas les droits nécessaires pour accéder à cette page.</p>
        <?php endif; ?>
    </main>
</div>

<?php include '../includes/footer.php'; ?>