<?php

use Cloudinary\Transformation\Extract;
use FFI\Exception;
    require_once '../includes/connexion.php';
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if(!isset($_SESSION["user"]) || ($_SESSION["user"]["statut"] !== "administrateur" && $_SESSION["user"]["statut"] !== "administratif" && $_SESSION["user"]["statut"] !== "enseignant")) {
        header("Location: login.php");
        exit;
    }
    $userStatut = $_SESSION['user']['statut'];

    $page = isset($_GET['page']) ? $_GET['page'] : '';

    $allowedPages = [];
    if ($userStatut === 'administrateur') {
        $allowedPages = ['board', 'register', 'films', 'users', 'manage_films'];
    } elseif ($userStatut === 'administratif') {

        $allowedPages = ['board', 'register'];
    } elseif ($userStatut === 'enseignant') {

        $allowedPages = ['board', 'films'];
    }

    if ($page && !in_array($page, $allowedPages)) {
        $redirectPage = $allowedPages[0] ?? 'board';
        header('Location: admin.php?page=' . $redirectPage);
        exit;
    }


    try {
        $stmtAllGenres = $conn->prepare("SELECT * FROM genre");
        $stmtAllGenres->execute();
        $allGenres = $stmtAllGenres->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $allGenres = [];
    }

    if ($_SERVER["REQUEST_METHOD"]=="POST")
    {
        if (isset($_POST["register-confirm"])) {
            usleep(700000);
            if($_POST["decision"] == "valider") {
                $id = $_POST["row-id"];
                $stmt = $conn->prepare("UPDATE utilisateur SET valide=1 WHERE id=?");
                $stmt->execute([$id]);
                header("Location: admin.php?page=register");
                exit;
            } elseif($_POST["decision"] == "rejeter") {
                $id = $_POST["row-id"];
                $stmt=$conn->prepare("DELETE FROM utilisateur WHERE id=?");
                $stmt->execute([$id]);
                header("Location: admin.php?page=register");
                exit;
            }
        }
        if (isset($_POST["films-confirm"])) {
            usleep(700000);
            if($_POST["decision"] == "valider") {
                $id = $_POST["row-id"];
                $stmt = $conn->prepare("UPDATE film SET valide=1 WHERE id=?");
                $stmt->execute([$id]);
                header("Location: admin.php?page=films");
                exit;
            } elseif($_POST["decision"] == "rejeter") {
                $id = $_POST["row-id"];
                $stmt=$conn->prepare("DELETE FROM film WHERE id=?");
                $stmt->execute([$id]);
                header("Location: admin.php?page=films");
                exit;
            }
        }
        if (isset($_POST["manage-register-confirm"])) {
            usleep(700000);
            if($_POST["decision"] == "modifier") {
                $id = $_POST["row-id"];
                $stmt = $conn->prepare("UPDATE utilisateur SET valide=1 WHERE id=?");
                $stmt->execute([$id]);
                header("Location: admin.php?page=users");
                exit;
            } elseif($_POST["decision"] == "supprimer") {
                $id = $_POST["row-id"];
                $checkStmt = $conn->prepare("SELECT statut FROM utilisateur WHERE id = ?");
                $checkStmt->execute([$id]);
                $targetStatut = $checkStmt->fetchColumn();
                if ($targetStatut === 'administrateur') {
                    $_SESSION['error_message'] = "Impossible de radier un administrateur.";
                } else {
                    $stmt = $conn->prepare("UPDATE utilisateur SET radier=1 WHERE id=?");
                    $stmt->execute([$id]);

                    if (!empty($user['email']) && filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
                        $to = strtolower($user['email']);
                        $subject = "Compte radié - ECE Ciné";
                        $message = "
                            Bonjour,<br><br>
                            Nous vous informons que votre compte a été <b>radié</b> par un administrateur.<br>
                            Vous n'avez désormais plus accès à la plateforme.<br><br>
                            Cordialement,<br>
                            L'équipe ECE Ciné
                        ";
                        $headers = "MIME-Version: 1.0" . "\r\n";
                        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                        $headers .= "From: noreply@ececine.com" . "\r\n";
        
                        mail($to, $subject, $message, $headers);
                    }
                }
                header("Location: admin.php?page=users");
                exit;
            }
        }
        if (isset($_POST["manage-films-confirm"])) {
            usleep(700000);
            if($_POST["decision"] == "modifier") {
                $id = $_POST["row-id"];
                $stmt = $conn->prepare("UPDATE film SET valide=1 WHERE id=?");
                $stmt->execute([$id]);
                header("Location: admin.php?page=manage_films");
                exit;
            } elseif($_POST["decision"] == "supprimer") {
                $id = $_POST["row-id"];
                $stmt=$conn->prepare("DELETE FROM film WHERE id=?");
                $stmt->execute([$id]);
                header("Location: admin.php?page=manage_films");
                exit;
            }
        }


        if (isset($_POST['edit_user'])) {
            usleep(700000);
            $editUserId = $_POST['edit_user_id'];
            $nom        = $_POST['nom'] ?? '';
            $prenom     = $_POST['prenom'] ?? '';
            $pseudo     = $_POST['pseudo'] ?? '';
            $email      = $_POST['email'] ?? '';
            $dateN      = $_POST['date'] ?? '';
            $statut     = $_POST['statut'] ?? '';

            if ($statut === 'administrateur' && $userStatut !== 'administrateur') {
                $existingStatutStmt = $conn->prepare("SELECT statut FROM utilisateur WHERE id=?");
                $existingStatutStmt->execute([$editUserId]);
                $statut = $existingStatutStmt->fetchColumn();
                $_SESSION['error_message'] = "Seul un administrateur peut attribuer le rôle administrateur.";
            }
            $oldAvatar  = $_POST['old_avatar'] ?? '';

            $newAvatar  = $oldAvatar;

            if (!empty($_POST['use_default_avatar'])) {
                $avatarFile = $_POST['use_default_avatar'];

                $newAvatar = 'assets/' . $avatarFile . '.png';
            }

            if (isset($_FILES['edit_avatar']) && $_FILES['edit_avatar']['error'] === 0 && $_FILES['edit_avatar']['size'] > 0) {
                require_once 'vendor/autoload.php';
                $cloudinary = new \Cloudinary\Cloudinary([
                    'cloud' => [
                        'cloud_name' => 'dghnwzbla',
                        'api_key'    => '285744794393346',
                        'api_secret' => 'znehOzX3fHZqQWo51nhU__BAgCY',
                    ],
                ]);
                $file       = $_FILES['edit_avatar'];
                $file_name  = uniqid('user_', true) . '_' . bin2hex(random_bytes(5));
                $uploadResult = $cloudinary->uploadApi()->upload($file['tmp_name'], [
                    'folder'      => 'ECE-Cinema/users_picture_profile',
                    'public_id'   => $file_name,
                    'overwrite'   => true,
                    'resource_type' => 'image'
                ]);
                $newAvatar  = $uploadResult['secure_url'];
            }

            $stmt = $conn->prepare("UPDATE utilisateur SET nom=?, prenom=?, pseudo=?, email=?, date_naissance=?, statut=?, avatar=? WHERE id=?");
            $stmt->execute([$nom, $prenom, $pseudo, $email, $dateN, $statut, $newAvatar, $editUserId]);

            if (isset($_SESSION['user']) && $_SESSION['user']['id'] == $editUserId) {
                $_SESSION['user']['pseudo'] = $pseudo;
                $_SESSION['user']['email']  = $email;
                $_SESSION['user']['statut'] = $statut;
                $_SESSION['user']['date_naissance'] = $dateN;
                $_SESSION['user']['avatar'] = $newAvatar;
                $_SESSION['user']['nom']   = $nom;
                $_SESSION['user']['prenom']= $prenom;
            }
            header('Location: admin.php?page=users');
            exit;
        }


        if (isset($_POST['edit_film'])) {
            usleep(700000);
            $filmId     = $_POST['film_id'];
            $titre      = $_POST['titre'] ?? '';
            $realisateur= $_POST['realisateur'] ?? '';
            $annee      = $_POST['annee'] ?? '';
            $description= $_POST['description'] ?? '';
            $trailer    = $_POST['trailer'] ?? '';
            $oldAffiche = $_POST['old_affiche'] ?? '';
            $genresSel  = $_POST['genre'] ?? [];
            $newAffiche = $oldAffiche;

            if (isset($_FILES['edit_file']) && $_FILES['edit_file']['error'] === 0 && $_FILES['edit_file']['size'] > 0) {
                require_once 'vendor/autoload.php';
                $cloudinary = new \Cloudinary\Cloudinary([
                    'cloud' => [
                        'cloud_name' => 'dghnwzbla',
                        'api_key'    => '285744794393346',
                        'api_secret' => 'znehOzX3fHZqQWo51nhU__BAgCY',
                    ],
                ]);
                $file      = $_FILES['edit_file'];
                $file_name = uniqid('film_', true) . '_' . bin2hex(random_bytes(5));
                $uploadResult = $cloudinary->uploadApi()->upload($file['tmp_name'], [
                    'folder'        => 'ECE-Cinema/affiches',
                    'public_id'     => $file_name,
                    'overwrite'     => true,
                    'resource_type' => 'image'
                ]);
                $newAffiche = $uploadResult['secure_url'];
            }

            $stmt = $conn->prepare("UPDATE film SET titre=?, realisateur=?, annee=?, description=?, trailer=?, affiche=? WHERE id=?");
            $stmt->execute([$titre, $realisateur, $annee, $description, $trailer, $newAffiche, $filmId]);

            $stmt = $conn->prepare("DELETE FROM film_genre WHERE film_id=?");
            $stmt->execute([$filmId]);
            foreach ($genresSel as $gSel) {

                $stmtIns = $conn->prepare("INSERT INTO film_genre (film_id, genre_id) VALUES (?, (SELECT id FROM genre WHERE libelle = ?))");
                $stmtIns->execute([$filmId, $gSel]);
            }
            header('Location: admin.php?page=manage_films');
            exit;
        }
    }
?>
<!DOCTYPE html>
    <?php include '../includes/header.php'; ?>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body{
            background-color:rgb(37, 41, 46);
        }

        .sidebar{
            background-color:rgb(16, 16, 16);
            position: relative;
            left:0;
            width: 22%;
            height: 100vh;
        }

        .titre{
            margin-bottom: 2rem;
        }
        .menu-item{
            width: 100%;
            padding: 12px 18px;
            font-size:17px;
            position: relative;
            color:rgb(168, 168, 168);
            transition: background-color 0.3s ease-in-out, box-shadow 0.3s ease-in-out,color 0.3s ease-in-out;
        }
        
        .menu-item:hover{
            background-color:rgb(0, 108, 216);
            box-shadow: 0 0 8px rgb(0, 108, 216);
            color: white;
        }

        .menu-item::after{
            content: '';
            position:absolute;
            left: 0;
            bottom: -12px;
            width: 0;
            height: 2px;
            background-color: #E50914;
            transition: width 0.3s ease-in-out;
        }
        .menu-item:hover::after{
            width: 100%;
        }

        .menu-item.active{
            background-color:rgb(0, 108, 216);
            box-shadow: 0 0 8px rgb(0, 108, 216);
            color: white;
        }

        .infos{
            background-color: rgb(54, 58, 65);
            padding:16px 75px;
            transition: transform 0.3s ease-in-out, background-color 0.3s ease-in-out;
        }

        .infos:hover{
            transform: scale(1.09);
            background-color:rgb(189, 5, 14);
            cursor: pointer;
        }

        .header{
            padding:14px 30px;
            font-size:20px
        }

        .mytable{
            width:max-content
        }

        .modal-trigger:not(button){
            background-color:rgba(26, 26, 26, 0.7);
            position: fixed;
            z-index:1001;
            width: 100vw;
            height: 100vh;
        }

        .mymodal{
            display: none;
            position: fixed;
            z-index:1000;
            width: 100vw;
            height: 100vh;
            opacity: 0;
            transition : opacity 0.3s ease-in-out;  
        }

        .mymodal.active{
            display: block;
            opacity: 1;
        }

        .modal-container{
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index:1002;
            width: 550px;
            height: 230px;
            background-color: rgb(241, 241, 241);
            border-radius: 10px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            transition: top 0.3s ease-in-out;
        }

        .modal-container.active{
            top: 40%;
            
        }

        hr{
            background-color: #4a4848;
            height: 3px;
        }

        html, body {
        height: 100%; 
    }

        .modal-edit-user .modal-container-edit-user {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1002;
            width: 650px;
            max-width: 90%;
            background-color: rgb(241, 241, 241);
            border-radius: 10px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            height: auto;
            max-height: 90vh;
            overflow-y: auto;
            transition: top 0.3s ease-in-out;
        }

        .modal-edit-user .modal-container-edit-user.active {

            top: 45%;
        }

        .edit-main,
        .edit-second {
            width: 100%;
            transition: transform 0.3s ease-in-out;
        }

        .edit-main.show-step {
            transform: translateX(-100%);
        }

        .edit-second.show-step {
            transform: translateX(0%);
        }

        .edit-pp {
            border-radius: 50%;
            max-width: 190px;
            width: 190px;
            height: 190px;
            max-height: 190px;
            background-color: rgb(74,81,88);
            border: 4px solid #0d6efd;
            cursor: pointer;
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
        }

        .edit-barre {
            height: 2px;
            background-color: rgb(44, 46, 49);
        }

        .edit-pp::after {
            content: '🖼️';
            font-size: 3rem;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.4);
            border-radius: 50%;
            opacity: 0;
            transition: opacity 0.2s ease-in-out;
        }

        .edit-pp:hover::after {
            opacity: 1;
        }

        .edit-default-avatar {
            max-width: 100px;
            max-height: 100px;
            cursor: pointer;
            transition: transform 0.2s ease-in-out, border 0.2s ease-in-out;
            background-position: center;
            background-size: cover;
        }

        .edit-default-avatar:hover {
            transform: scale(1.1);
        }

        .edit-default-avatar.selected {
            border: 3px solid #0d6efd;
        }

        .modal-edit-film .modal-container-edit-film {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1002;
            width: 700px;
            max-width: 95%;
            background-color: rgb(32, 32, 32);
            color: white;
            border-radius: 10px;
            padding: 20px;
            height: auto;
            max-height: 90vh;
            overflow-y: auto;
            transition: top 0.3s ease-in-out;
        }

        .modal-edit-film .modal-container-edit-film.active {
            top: 45%;
        }

        .modal-edit-film label {
            color: white;
        }

        .modal-edit-film input,
        .modal-edit-film textarea {
            color: #000;
        }


        .film-info-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 1005;
            background-color: rgba(0, 0, 0, 0.6);
            align-items: center;
            justify-content: center;
        }

        .film-info-overlay.active {
            display: flex;
        }

        .film-info-container {
            width: 60%;
            max-height: 90vh;
            background-color: rgb(31, 31, 31);
            border-radius: 3px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
            overflow-y: auto;
        }

        .film-info-affiche {
            width: 100%;
            aspect-ratio: 16 / 9;
            background-size: cover;
            background-position: center;
            box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.5);
            position: relative;
        }

        .film-info-details {
            color: white;
            width: 100%;
            padding: 1rem 1rem;
            font-size: 1rem;
            font-weight: 500;
        }

        .film-info-title {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .film-info-description {
            color: white;
            margin-bottom: 1rem;
        }

        .film-info-meta p {
            color: rgb(180,180,180);
            margin-bottom: 0.3rem;
        }

        .film-info-meta span {
            color: white;
        }

        .film-info-genres .badge {
            background-color: #0d6efd;
            color: white;
            border-radius: 10px;
            padding: 0.2rem 0.5rem;
            font-size: 0.8rem;
        }
        
    </style>
</head>
<body class="d-flex flex-column pb-4">
    <div class="mymodal modal-register">
        <div class="modal-trigger"></div>
        <div class="modal-container modal-container-register">
            <h3 class="mb-1">Confirmer l'action</h3>
            <hr class="mb-2 pb-1">
            <form action="" method="post">
                <p class="fs-5 fw-normal mb-0">Voulez-vous vraiment <span id="text-register"></span> l'inscription de l'utilisateur <span class="fw-bold" id="username"></span>?</p>
                <input type="hidden" name="decision" id="decision-register">
                <hr class="mb-2 pb-1">
                <div class="d-flex flex-row justify-content-end align-items-center gap-3">
                    <button type="button" class="btn btn-secondary px-4 fs-5 modal-trigger">Annuler</button>
                    <button class="btn btn-success px-4 fs-5" name="register-confirm">Confirmer</button>
                    <input type="hidden" name="row-id" id="register-input">
                </div>
            </form>
         </div>
    </div>

    <div class="mymodal modal-films">
        <div class="modal-trigger"></div>
        <div class="modal-container modal-container-films">
            <h3 class="mb-1">Confirmer l'action</h3>
            <hr class="mb-2 pb-1">
            <form action="" method="post">
                <p class="fs-5 fw-normal mb-0">Voulez-vous vraiment <span id="text-film"></span> le film "<span id="titre-film" class="fw-bold">Flash</span>" de l'utilisateur <span class="fw-bold" id="username-film">NathanGoat</span>?</p>
                <input type="hidden" name="decision" id="decision-film">
                <hr class="mb-2 pb-1">
                <div class="d-flex flex-row justify-content-end align-items-center gap-3">
                    <button type="button" class="btn btn-secondary px-4 fs-5 modal-trigger">Annuler</button>
                    <button class="btn btn-success px-4 fs-5" name="films-confirm">Confirmer</button>
                    <input type="hidden" name="row-id" id="film-input">
                </div>
            </form>
         </div>
    </div>

    <div class="mymodal modal-manage-register">
        <div class="modal-trigger"></div>
        <div class="modal-container modal-container-manage-register">
            <h3 class="mb-1">Confirmer l'action</h3>
            <hr class="mb-2 pb-1">
            <form action="" method="post">
                <p class="fs-5 fw-normal mb-0">Voulez-vous vraiment <span id="text-manage-register"></span> l'inscription de l'utilisateur <span class="fw-bold" id="username-manage"></span>?</p>
                <input type="hidden" name="decision" id="decision-manage-register">
                <hr class="mb-2 pb-1">
                <div class="d-flex flex-row justify-content-end align-items-center gap-3">
                    <button type="button" class="btn btn-secondary px-4 fs-5 modal-trigger">Annuler</button>
                    <button class="btn btn-success px-4 fs-5" name="manage-register-confirm">Confirmer</button>
                    <input type="hidden" name="row-id" id="manage-register-input">
            </div>
            </form>
         </div>
    </div>

    <div class="mymodal modal-manage-films">
        <div class="modal-trigger"></div>
        <div class="modal-container modal-container-manage-films">
            <h3 class="mb-1">Confirmer l'action</h3>
            <hr class="mb-2 pb-1">
            <form action="" method="post">
                <p class="fs-5 fw-normal mb-0">Voulez-vous vraiment <span id="text-manage-film"></span> le film "<span id="titre-film-manage" class="fw-bold">Flash</span>" de l'utilisateur <span class="fw-bold" id="username-film-manage">NathanGoat</span>?</p>
                <input type="hidden" name="decision" id="decision-manage-film">
                <hr class="mb-2 pb-1">
                <div class="d-flex flex-row justify-content-end align-items-center gap-3">
                    <button type="button" class="btn btn-secondary px-4 fs-5 modal-trigger">Annuler</button>
                    <button class="btn btn-success px-4 fs-5" name="manage-films-confirm">Confirmer</button>
                    <input type="hidden" name="row-id" id="manage-film-input">
                </div>
            </form>
         </div>
    </div>

    <div class="mymodal modal-edit-user">
        <div class="modal-trigger"></div>
        <div class="modal-container modal-container-edit-user">
            <form action="" method="post" enctype="multipart/form-data" id="edit-user-form">
                <input type="hidden" name="edit_user" value="1">
                <input type="hidden" name="edit_user_id" id="edit_user_id">
                <input type="hidden" name="old_avatar" id="edit_old_avatar">
                <input type="hidden" name="use_default_avatar" id="edit_use_default_avatar" value="">
                <h2 class="text-center fw-bold mb-4">Modifier l'utilisateur</h2>
                <div class="mb-3">
                    <input type="text" name="nom" id="edit_nom" class="form-control form-control-lg rounded-pill border-primary" placeholder="Nom">
                </div>
                <div class="mb-3">
                    <input type="text" name="prenom" id="edit_prenom" class="form-control form-control-lg rounded-pill border-primary" placeholder="Prénom">
                </div>
                <div class="mb-3">
                    <input type="text" name="pseudo" id="edit_pseudo" class="form-control form-control-lg rounded-pill border-primary" placeholder="Pseudo">
                </div>
                <div class="mb-3">
                    <input type="email" name="email" id="edit_email" class="form-control form-control-lg rounded-pill border-primary" placeholder="Email">
                </div>
                <div class="mb-3">
                    <input type="date" name="date" id="edit_date" class="form-control form-control-lg rounded-pill border-primary">
                </div>
                <div class="mb-3">
                    <input type="radio" class="form-check-input" name="statut" id="edit_etudiant" value="etudiant">
                    <label for="edit_etudiant" class="form-label me-3">Étudiant</label>
                    <input type="radio" class="form-check-input" name="statut" id="edit_enseignant" value="enseignant">
                    <label for="edit_enseignant" class="form-label me-3">Enseignant</label>
                    <input type="radio" class="form-check-input" name="statut" id="edit_administratif" value="administratif">
                    <label for="edit_administratif" class="form-label me-3">Administratif</label>
                    <?php if ($userStatut === 'administrateur'): ?>
                    <input type="radio" class="form-check-input" name="statut" id="edit_administrateur" value="administrateur">
                    <label for="edit_administrateur" class="form-label">Administrateur</label>
                    <?php endif; ?>
                </div>
 
                <div class="mb-4">
                    <label class="mb-2 text-dark">Photo de profil</label>

                    <div class="edit-pp mb-3" id="edit_pp"></div>

                    <input type="file" id="edit_fileUpload" name="edit_avatar" class="d-none" accept="image/*">
                    <div class="w-100 px-5 mb-2">
                        <button type="button" id="edit_fileUploadButton" class="btn btn-outline-primary rounded-pill mb-3 w-100 py-2">
                            Choisir une photo
                        </button>
                    </div>
                    <hr class="w-100 edit-barre mb-3">
                    <p class="text-muted text-center">Ou choisissez un avatar par défaut :</p>
                    <div class="d-flex justify-content-center mb-3 flex-wrap">
                        <img src="assets/avatar1.png" alt="Avatar 1" class="edit-default-avatar rounded-circle m-2" data-avatar="avatar1">
                        <img src="assets/avatar2.png" alt="Avatar 2" class="edit-default-avatar rounded-circle m-2" data-avatar="avatar2">
                        <img src="assets/avatar3.png" alt="Avatar 3" class="edit-default-avatar rounded-circle m-2" data-avatar="avatar3">
                        <img src="assets/avatar4.png" alt="Avatar 4" class="edit-default-avatar rounded-circle m-2" data-avatar="avatar4">
                    </div>
                </div>

                <div class="d-flex flex-row gap-2">
                    <button type="button" class="btn btn-secondary w-50 rounded-pill modal-trigger">Annuler</button>
                    <button type="submit" class="btn btn-primary w-50 rounded-pill">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>


    <div class="mymodal modal-edit-film">
        <div class="modal-trigger"></div>
        <div class="modal-container modal-container-edit-film">
            <form action="" method="post" enctype="multipart/form-data" id="edit-film-form">
                <input type="hidden" name="edit_film" value="1">
                <input type="hidden" name="film_id" id="edit_film_id">
                <input type="hidden" name="old_affiche" id="edit_old_affiche">
                <h2 class="text-center fw-bold mb-4 text-white">Modifier le film</h2>
                <div class="mb-3">
                    <label for="edit_titre" class="mb-2 text-white">Titre du film<span class="text-danger">*</span></label>
                    <input type="text" name="titre" class="form-control form-control" id="edit_titre" />
                </div>
                <div class="mb-3">
                    <label for="edit_realisateur" class="mb-2 text-white">Réalisateur(s)<span class="text-danger">*</span></label>
                    <input type="text" name="realisateur" class="form-control form-control" id="edit_realisateur" />
                </div>
                <div class="mb-3">
                    <label for="edit_genre" class="mb-2 text-white">Genre<span class="text-danger">*</span></label>
                    <div class="d-flex flex-column" id="edit_genre_container">
                        <?php if (!empty($allGenres)): foreach ($allGenres as $g): ?>
                            <div class="d-flex justify-content-between w-50">
                                <label class="me-0 fs-6 text-white"><?= htmlspecialchars($g['libelle']) ?></label>
                                <input type="checkbox" name="genre[]" value="<?= htmlspecialchars($g['libelle']) ?>" class="form-check checkbox edit-genre-checkbox" />
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="edit_file" class="mb-2 text-white">Affiche du film<span class="text-danger">*</span></label>
                    <input type="file" name="edit_file" class="form-control form-control" id="edit_file" accept="image/*" />
                    <small class="text-muted">Laisser vide pour conserver l'affiche actuelle.</small>
                </div>
                <div class="mb-3">
                    <label for="edit_annee" class="mb-2 text-white">Année de sortie<span class="text-danger">*</span></label>
                    <input type="number" name="annee" class="form-control form-control" id="edit_annee" min="1888" max="2025" />
                </div>
                <div class="mb-3">
                    <label for="edit_trailer" class="mb-2 text-white">URL du trailer (optionnel)</label>
                    <input type="text" name="trailer" class="form-control form-control" id="edit_trailer" />
                </div>
                <div class="mb-3">
                    <label for="edit_description" class="mb-2 text-white">Description du film<span class="text-danger">*</span></label>
                    <textarea class="form-control" name="description" id="edit_description" rows="4"></textarea>
                </div>
                <div class="d-flex flex-row gap-2">
                    <button type="submit" class="btn btn-primary w-50 rounded-pill">Enregistrer</button>
                    <button type="button" class="btn btn-light w-50 rounded-pill border-dark modal-trigger">Annuler</button>
                </div>
            </form>
        </div>
    </div>


    <div class="film-info-overlay" id="film-info-overlay">
        <div class="film-info-container">
            <div class="film-info-affiche" id="film-info-affiche"></div>
            <div class="film-info-details">
                <h3 class="film-info-title text-danger" id="film-info-title"></h3>
                <p class="film-info-description" id="film-info-description"></p>
                <div class="d-flex flex-row justify-content-between px-2">
                    <div class="film-info-meta">
                        <p>Année : <span id="film-info-annee"></span></p>
                        <p>Réalisateur : <span id="film-info-realisateur"></span></p>
                        <p>Publié par : <span id="film-info-auteur"></span></p>
                    </div>
                    <div class="film-info-meta">
                        <p>Genres :</p>
                        <div class="film-info-genres" id="film-info-genres"></div>
                    </div>
                    <div class="film-info-meta d-flex align-items-end">
                        <button type="button" class="btn btn-outline-primary" id="film-info-edit">Modifier</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content d-flex flex-row w-100 flex-grow-1 h-100">
    
        <aside class="sidebar d-flex flex-column align-items-center pt-4 px-2">
            <div>
                <h3 class="titre text-danger fw-bolder fs-2 text-center">ECE Ciné Admin</h3>
                <ul class="text-white w-100 list-unstyled d-flex flex-column gap-2 px-3">
                    <?php if (in_array('board', $allowedPages)): ?>
                    <li><a href="admin.php?page=board" class="menu-item nav-link text-start rounded-1 <?=($page=="board")?'active':''?>">📊 Tableau de bord</a></li>
                    <?php endif; ?>
                    <?php if (in_array('register', $allowedPages)): ?>
                    <li><a href="admin.php?page=register" class="menu-item nav-link text-start rounded-1 <?=($page=="register")?'active':''?>">👤 Validation des Inscriptions</a></li>
                    <?php endif; ?>
                    <?php if (in_array('films', $allowedPages)): ?>
                    <li><a href="admin.php?page=films" class="menu-item nav-link text-start rounded-1 <?=($page=="films")?'active':''?>">🎬 Validation des Films</a></li>
                    <?php endif; ?>
                    <?php if (in_array('users', $allowedPages)): ?>
                    <li><a href="admin.php?page=users" class="menu-item nav-link text-start rounded-1 <?=($page=="users")?'active':''?>">⚙️ Gestion <br>des Utilisateurs</a></li>
                    <?php endif; ?>
                    <?php if (in_array('manage_films', $allowedPages)): ?>
                    <li><a href="admin.php?page=manage_films" class="menu-item nav-link text-start rounded-1 <?=($page=="manage_films")?'active':''?>">⚙️ Gestion des films</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </aside>
        <?php
            if (isset($_SESSION['error_message'])) {
                echo '<div class="w-100 text-center text-danger fw-bold py-2">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
                unset($_SESSION['error_message']);
            }
        ?>
    <?php 
        if (isset($page) && $page !== ''):
            switch ($page):
                case 'board':?>
                    <div class="board p-5 text-white">
                        <h2 class="fs-1 mb-5">Tableau de bord</h2>
                        <div class="d-flex flex-column gap-4">
                            <div class="d-flex flex-row justify-content-between gap-3">
                                <div class="infos d-flex flex-column text-center gap-2 rounded-2">
                                    <h5>Inscriptions en attente</h5>
                                    <h2 class="text-danger fs-1 fw-bold">
                                    <?php
                                        $res = $conn->query("SELECT COUNT(*) AS nb FROM utilisateur WHERE valide=0");
                                        echo $res->fetch()['nb'];
                                    ?>
                                    </h2>
                                </div>

                                <div class="infos d-flex flex-column text-center gap-2 rounded-2">
                                    <h5>Inscriptions validées</h5>
                                    <h2 class="text-danger fs-1 fw-bold">
                                    <?php
                                        $res = $conn->query("SELECT COUNT(*) AS nb FROM utilisateur WHERE valide=1");
                                        echo $res->fetch()['nb'];
                                    ?>
                                    </h2>
                                </div>

                                <div class="infos d-flex flex-column text-center gap-2 rounded-2">
                                    <h5>Nombre total d'utilisateurs</h5>
                                    <h2 class="text-danger fs-1 fw-bold">
                                        <?php
                                            $res = $conn->query("SELECT COUNT(*) AS nb FROM utilisateur");
                                            echo $res->fetch()['nb'];
                                        ?>
                                        </h2>
                                </div>
                            </div>

                            <div class="d-flex flex-row justify-content-center gap-5">
                                
                            <div class="infos d-flex flex-column text-center gap-2 rounded-2 text-nowrap">
                                <h5>Films à valider</h5>
                                <h2 class="text-danger fs-1 fw-bold">
                                    <?php
                                        $res = $conn->query("SELECT COUNT(*) AS nb FROM film WHERE valide=0");
                                        echo $res->fetch()['nb'];
                                    ?>
                                </h2>
                            </div>

                                <div class="infos d-flex flex-column text-center gap-2 rounded-2 text-nowrap">
                                    <h5>Films validés</h5>
                                    <h2 class="text-danger fs-1 fw-bold">
                                    <?php
                                        $res = $conn->query("SELECT COUNT(*) AS nb FROM film WHERE valide=1");
                                        echo $res->fetch()['nb'];
                                    ?>
                                    </h2>
                                </div>
                            </div>
                        </div>

                        
                        <div class="d-flex gap-5 mt-5 w-100">
                        <?php  
                            $res = $conn->query("SELECT * FROM utilisateur WHERE valide=0 ORDER BY Created_at DESC LIMIT 2");
                            if($res->rowCount()):?>
                                <div class="w-50">
                                    <h4 class="fs-4 mb-3">Dernières inscriptions à valider</h4>
                                    <table class="table-bordered w-100">
                                        <tbody>
                                            <?php 
                                                foreach($res as $user):?>
                                            <tr>
                                                <td class="p-2"><?=$user["pseudo"]?></td>
                                                <td class="d-flex justify-content-end">
                                                    <a class="btn btn-danger me-2" href="admin.php?page=register">Voir</a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif?>
                            <?php
                                $res = $conn->query("SELECT * FROM film WHERE valide=0 ORDER BY Created_at DESC LIMIT 2");
                                if ($res->rowCount()):?>
                                    <div class="w-50">
                                        <h4 class="fs-4 mb-3">Derniers films à valider</h4>
                                        <table class="table-bordered w-100">
                                            <tbody>
                                                <?php 
                                                foreach($res as $film):?>
                                                <tr>
                                                    <td class="p-2"><?=$film["titre"]?></td>
                                                    <td class="d-flex justify-content-end">
                                                        <a class="btn btn-danger me-2" href="admin.php?page=films">Voir</a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif?>
                        </div>

                    </div>

                    </div>
    <?php break;
          case 'films':?>
                    <div class="films p-5 text-white">
                        <h2 class="fs-1 mb-5">Films à valider</h2>
                        <div class="d-flex flex-column gap-4">
                            <div class="d-flex flex-row justify-content-between gap-5">
                                <?php 
                                    $res = $conn->query("SELECT * FROM film WHERE valide=0 ORDER BY Created_at DESC");
                                    $stmt = $conn->prepare("SELECT pseudo FROM utilisateur WHERE id = ?");
                                    $stmt->execute([$_SESSION["user"]["id"]]);
                                    try{
                                        $auteur=$stmt->fetch()['pseudo'];
                                    }
                                    catch (Exception $e){
                                        $auteur="Utilisateur indisponible" ;}
                                    foreach($res as $film):?>
                                        <div class="infos d-flex flex-column gap-2 rounded-2">
                                            <img src="<?=$film["affiche"]?>" style="width:220px;height: fit-content;max-width: 220px;max-height: 320px;" alt="">
                                            <h2 class="text-light fs-5 fw-bold mb-0">Auteur : <span><?=$auteur?></span></h2>
                                            <h2 class="text-light fs-5 fw-bold">Titre : <span><?=$film["titre"]?></span></h2>
                                            <button class="btn btn-success films_modal" data-auteur="<?=$auteur?>" data-titre="<?=$film["titre"]?>" data-id="<?=$film["id"]?>">Valider</button>
                                            <button class="btn btn-danger films_modal" data-auteur="<?=$auteur?>" data-titre="<?=$film["titre"]?>" data-id="<?=$film["id"]?>">Rejeter</button>
                                        </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

    <?php break;
        case "register":?>

            <div class="register p-5 text-white">
                <h2 class="fs-1 mb-5">Inscriptions à valider</h2>
                <?php 
                    if ($userStatut === 'administratif') {
                        $stmtPending = $conn->prepare("SELECT * FROM utilisateur WHERE valide=0 AND (statut = 'etudiant' OR statut = 'enseignant')");
                        $stmtPending->execute();
                        $infos = $stmtPending;
                    } else {
                        $stmtPending = $conn->prepare("SELECT * FROM utilisateur WHERE valide=0");
                        $stmtPending->execute();
                        $infos = $stmtPending;
                    }
                    if($infos->rowCount()):?>
                    <table class="table-bordered mytable text-center">
                        <thead class="headers">
                            <th scope="col" class="header">Pseudo</th>
                            <th scope="col" class="header">Email</th>
                            <th scope="col" class="header">Statut</th>
                            <th scope="col" class="header">Date de naissance</th>
                            <th scope="col" class="header">Action</th>
                        </thead>

                        <tbody>
                            <?php 
                                foreach($infos as $res):?>
                            <tr>

                                <td class="p-3"><?=$res["pseudo"]?></td>
                                <td class="px-2"><?=$res["email"]?></td>
                                <td><?=$res["statut"]?></td>
                                <td><?=$res["date_naissance"]?></td>
                                
                                <td>
                                    <div class="d-flex flex-row justify-content-center gap-3 p-3">
                                        <button class="btn btn-success px-3 py-2 user_modal" data-username="<?=$res["pseudo"]?>" data-id="<?=$res["id"]?>">Valider</button>
                                        <button class="btn btn-danger px-3 py-2 user_modal" data-username="<?=$res["pseudo"]?>" data-id="<?=$res["id"]?>">Rejeter</button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif?>
            </div>
        <?php break;
        case "users":?>

            <div class="users p-5 text-white">
                <h2 class="fs-1 mb-5">Gestion des utilisateurs</h2>
                <?php 
                    $infos = $conn->query("SELECT * FROM utilisateur WHERE valide=1");
                    if ($infos->rowCount()):?>
                    <table class="table-bordered mytable text-center">
                        <thead class="headers">
                            <th scope="col" class="header">Pseudo</th>
                            <th scope="col" class="header">Email</th>
                            <th scope="col" class="header">Statut</th>
                            <th scope="col" class="header">Date de naissance</th>
                            <th scope="col" class="header">Action</th>
                        </thead>

                        <tbody>
                        <?php 
                            foreach($infos as $res):
                                if ($res["radier"]!="1"):
                                ?>
                            <tr>
                                <td class="p-3"><?=$res["pseudo"]?></td>
                                <td class="px-2"><?=$res["email"]?></td>
                                <td><?=$res["statut"]?></td>
                                <td><?=$res["date_naissance"]?></td>
                                <td>
                                    <div class="d-flex flex-row justify-content-center gap-3 p-3">
                                        <button
                                            class="btn btn-primary px-4 py-2 manage_users_modal"
                                            data-action="edit-user"
                                            data-id="<?=$res["id"]?>"
                                            data-nom="<?=htmlspecialchars($res["nom"]??'', ENT_QUOTES)?>"
                                            data-prenom="<?=htmlspecialchars($res["prenom"]??'', ENT_QUOTES)?>"
                                            data-pseudo="<?=htmlspecialchars($res["pseudo"], ENT_QUOTES)?>"
                                            data-email="<?=htmlspecialchars($res["email"], ENT_QUOTES)?>"
                                            data-date="<?=htmlspecialchars($res["date_naissance"], ENT_QUOTES)?>"
                                            data-statut="<?=htmlspecialchars($res["statut"], ENT_QUOTES)?>"
                                            data-avatar="<?=htmlspecialchars($res["avatar"]??'', ENT_QUOTES)?>"
                                        >Modifier</button>
                                        <button
                                            class="btn btn-danger px-3 py-2 manage_users_modal"
                                            data-action="delete-user"
                                            data-id="<?=$res["id"]?>"
                                            data-username="<?=htmlspecialchars($res["pseudo"], ENT_QUOTES)?>"
                                        >Radier</button>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                            endif;
                            endforeach; 
                            ?>
                        </tbody>
                    </table>
                <?php endif?>
            </div>

        <?php break;
            case "manage_films":?>
                <div class="films p-5 text-white">
                    <h2 class="fs-1 mb-5">Getion des films</h2>
                    <div class="d-flex flex-column gap-4">
                        <div class="d-flex flex-row justify-content-between gap-5 flex-wrap">
                            <?php
                                $res = $conn->query("SELECT * FROM film WHERE valide=1 ORDER BY Created_at DESC");
                                $stmt = $conn->prepare("SELECT pseudo FROM utilisateur WHERE id = ?");
                                $stmt->execute([$_SESSION["user"]["id"]]);
                                try{
                                    $auteur=$stmt->fetch()['pseudo'];
                                }
                                catch (Exception $e){
                                    $auteur="Utilisateur indisponible" ;}
                                foreach($res as $film):
                                    $genreStmt = $conn->prepare("SELECT g.libelle FROM film_genre fg JOIN genre g ON fg.genre_id = g.id WHERE fg.film_id = ?");
                                    $genreStmt->execute([$film['id']]);
                                    $filmGenresArray = $genreStmt->fetchAll(PDO::FETCH_COLUMN);
                                    $dataGenres = htmlspecialchars(implode('|', $filmGenresArray), ENT_QUOTES);
                                ?>
                                    <div
                                        class="infos d-flex flex-column gap-2 rounded-2 film-card"
                                        data-film-id="<?=$film['id']?>"
                                        data-film-titre="<?=htmlspecialchars($film['titre'], ENT_QUOTES)?>"
                                        data-film-realisateur="<?=htmlspecialchars($film['realisateur']??'', ENT_QUOTES)?>"
                                        data-film-annee="<?=htmlspecialchars($film['annee']??'', ENT_QUOTES)?>"
                                        data-film-description="<?=htmlspecialchars($film['description']??'', ENT_QUOTES)?>"
                                        data-film-trailer="<?=htmlspecialchars($film['trailer']??'', ENT_QUOTES)?>"
                                        data-film-auteur="<?=htmlspecialchars($auteur, ENT_QUOTES)?>"
                                        data-film-genres="<?=$dataGenres?>"
                                        data-film-affiche="<?=htmlspecialchars($film['affiche'], ENT_QUOTES)?>"
                                    >
                                        <img src="<?=$film['affiche']?>" style="width:220px;height: fit-content;max-width: 220px;max-height: 320px;" alt="">
                                        <h2 class="text-light fs-5 fw-bold mb-0">Auteur : <span><?=$auteur?></span></h2>
                                        <h2 class="text-light fs-5 fw-bold">Titre : <span><?=$film['titre']?></span></h2>
                                        <button
                                            class="btn btn-primary manage_films_modal"
                                            data-action="edit-film"
                                            data-id="<?=$film['id']?>"
                                            data-titre="<?=htmlspecialchars($film['titre'], ENT_QUOTES)?>"
                                            data-realisateur="<?=htmlspecialchars($film['realisateur']??'', ENT_QUOTES)?>"
                                            data-annee="<?=htmlspecialchars($film['annee']??'', ENT_QUOTES)?>"
                                            data-description="<?=htmlspecialchars($film['description']??'', ENT_QUOTES)?>"
                                            data-trailer="<?=htmlspecialchars($film['trailer']??'', ENT_QUOTES)?>"
                                            data-affiche="<?=htmlspecialchars($film['affiche'], ENT_QUOTES)?>"
                                            data-genres="<?=$dataGenres?>"
                                            data-auteur="<?=htmlspecialchars($auteur, ENT_QUOTES)?>"
                                        >Modifier</button>
                                        <button
                                            class="btn btn-danger manage_films_modal"
                                            data-action="delete-film"
                                            data-id="<?=$film['id']?>"
                                            data-titre="<?=htmlspecialchars($film['titre'], ENT_QUOTES)?>"
                                            data-auteur="<?=htmlspecialchars($auteur, ENT_QUOTES)?>"
                                        >Supprimer</button>
                                    </div>
                                <?php endforeach; ?>
                        </div>
                    </div>
                </div>
    <?php 
    endswitch;
    endif;
    ?>
    </div>
    <script src="js/admin_modals.js"></script>
    <?php include '../includes/footer.php'; ?>
</body>
</html>