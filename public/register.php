<?php 
     include '../includes/connexion.php';
     require 'vendor/autoload.php';

     if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if(isset($_SESSION["user"])){
        header("Location: index.php");
        exit();
    }

    use Cloudinary\Cloudinary;

    $cloudinary = new Cloudinary([
    'cloud' => [
        'cloud_name' => 'dghnwzbla',
        'api_key'    => '285744794393346',
        'api_secret' => 'znehOzX3fHZqQWo51nhU__BAgCY',
    ],
    ]);

    $infos=$conn->query("SELECT * FROM utilisateur");

    if($_SERVER["REQUEST_METHOD"]=="POST"){
        usleep(1000000);
        if(isset($_POST["register"])){
            $nom = trim($_POST["nom"]);
            $prenom = trim($_POST["prenom"]);
            $pseudo = trim($_POST["pseudo"]);
            $email = trim($_POST["email"]);
            $date = trim($_POST["date"]);
            $password = password_hash(trim($_POST["password"]), PASSWORD_DEFAULT);
            $statut = $_POST["statut"];
            $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $file_name = "avatar_" . $userId . "_" . time() . "." . $extension;
            if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $file= $_FILES['file'];
            }
            else{
                $url = __DIR__ . '/assets/avatar3.png';
                $file = [
                    'name' => 'avatar3.png',
                    'tmp_name' => $url,
                    'size' => filesize($url),
                    'type' => mime_content_type($url),
                    'error' => UPLOAD_ERR_OK,
                ];
            }
            
            $uploadResult=$cloudinary->uploadApi()->upload($file['tmp_name'], [
                'folder' => 'ECE-Cinema/users_picture_profile',
                'public_id' => $file_name,
                'overwrite' => true,
            ]);

            
            $url = $uploadResult['secure_url'];
            $stmt = $conn->prepare("INSERT INTO utilisateur (nom, prenom, pseudo, email, date_naissance, password, statut, avatar) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $prenom, $pseudo, $email, $date, $password, $statut, $url]);

            header("location:login.php");
            exit;
        }
        else{
            usleep(1000000);
            if(isset($_POST["register"])){
                $nom = trim($_POST["nom"]);
                $prenom = trim($_POST["prenom"]);
                $pseudo = trim($_POST["pseudo"]);
                $email = trim($_POST["email"]);
                $date = trim($_POST["date"]);
                $password = password_hash(trim($_POST["password"]), PASSWORD_DEFAULT);
                $statut = $_POST["statut"];
                $stmt = $conn->prepare("INSERT INTO utilisateur (nom, prenom, pseudo, email, date_naissance, password, statut) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nom, $prenom, $pseudo, $email, $date, $password, $statut, $url]);
                header("location:login.php");
                exit;
            }
        }
    }


?>
<!DOCTYPE html>
    <?php include '../includes/header.php'; ?>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
        .pp{
            border-radius: 50%;
            max-width: 190px;
            width: 190px;
            height: 190px;
            max-height: 190px;
            background-color:rgb(74, 81, 88);
            border: 4px solid #0d6efd;
            cursor:pointer;
            background-image: url("assets/avatar3.png");
            background-position: center;
            background-size: cover;
        }

        .barre{
            height: 2px;
            background-color:rgb(44, 46, 49);
        }

        .pp::after{
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

        .pp:hover::after{
            opacity: 1;
        }

        .default-avatar {
            max-width: 100px;
            max-height: 100px;
            cursor: pointer;
            transition: transform 0.2s ease-in-out,border 0.2s ease-in-out;
            background-position: center;
            background-size: cover;
        }

        .default-avatar:hover {
            transform: scale(1.1);
        }

        .default-avatar.selected {
            border: 3px solid #0d6efd;
        }

        .main{
            transition: transform 0.3s ease-in-out;
            transform: translateX(0);
        }

        .second{
            transition: transform 0.3s ease-in-out;
            transform: translateX(100%);
        }

        .main.valider{
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out
        }

        .second.show{
            transform: translateX(0);
            display: flex;
            width: 40rem;
            transition:transform 0.3s ease-in-out;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-justify: center;
        }
    </style>
</head>
<body class="bg-dark">
<div class="d-none">
    <?php
        foreach($infos as $info):?>
        <span class="d-none emails"><?=$info['email'];?></span>
        <span class="d-none pseudos"><?=$info['pseudo'];?></span>
        <?php endforeach; ?>
</div>

<div class="d-flex justify-content-center align-items-center min-vh-100 pt-4">
    <form action="" method="post" enctype="multipart/form-data" novalidate>
        <div class="main card shadow-lg rounded-4 p-4" id="main" style="width: 40rem;">
            <h1 class="text-center fw-bold mb-4">Inscription</h1>
            <div class="mb-3 info-content">
                <input type="text" name="nom" class="form-control form-control-lg rounded-pill border-primary" placeholder="Entrez votre nom"/>
                <span class="text-danger ms-3 d-none error" id="error-nom">Veuillez remplir ce champ</span>
            </div>

            <div class="mb-3 info-content">
                <input type="text" name="prenom" class="form-control form-control-lg rounded-pill border-primary" placeholder="Entrez votre prénom"/>
                <span class="text-danger ms-3 d-none error" id="error-prenom">Veuillez remplir ce champ</span>
            </div>

            <div class="mb-3 info-content">
                <input type="text" name="pseudo" class="form-control form-control-lg rounded-pill border-primary pseudo" placeholder="Entrez votre pseudo"/>
                <span class="text-danger ms-3 d-none error" id="error-pseudo">Veuillez remplir ce champ</span>
            </div>

            <div class="mb-3 info-content">
                <input type="email" name="email" class="form-control form-control-lg rounded-pill border-primary email" placeholder="Entrez votre email" />
                <span class="text-danger ms-3 d-none error" id="error-email">Veuillez remplir ce champ</span>
            </div>

                
            <div class="mb-3 info-content">
                <input type="date" name="date" class="form-control form-control-lg rounded-pill border-primary" placeholder="Entrez votre date de naissance" />
                <span class="text-danger ms-3 d-none error" id="error-date">Veuillez remplir ce champ</span>
            </div>

            <div class="mb-3 info-content position-relative">
                <input type="password" name="password" class="form-control form-control-lg rounded-pill border-primary pwd" placeholder="Entrez votre mot de passe" />
                <span class="text-danger ms-3 d-none error" id="error-password">Veuillez remplir ce champ</span>
                <div class="d-flex justify-content-end w-full gap-2 me-3 mt-2">
                    <span class="">Afficher le mot de passe </span>
                    <input type="checkbox" id="show_pwd" class="form-check-input d-inline">
                </div>
            </div>

            <div class="mb-3">
                <input type="radio" class="form-check-input" name="statut" id="etudiant" value="etudiant" checked>
                <label for="etudiant" class="form-label">Étudiant</label><br>
                <input type="radio" class="form-check-input" name="statut" id="enseignant" value="enseignant">
                <label for="enseignant" class="form-label">Enseignant</label><br>
                <input type="radio" class="form-check-input" name="statut" id="administratif" value="administratif">
                <label for="administratif" class="form-label">Administratif</label><br>
            </div>

            <button type="button" class="btn btn-primary btn-lg w-100 rounded-pill mb-3" id="next">Inscription</button>
            <p class="text-center fs-6">
                Vous avez déjà un compte ?
                <a href="login.php" class="text-primary text-decoration-underline">Connectez-vous</a>
            </p>
        </div>


        <div class="second card shadow-lg rounded-4 p-4" id="second" style="display:none">
            <h1 class="fw-bold mb-3 text-dark">Presque terminé !</h1>
            <p class="text-muted mb-4">Choisissez votre photo de profil pour personnaliser votre expérience.</p>
            <div class="pp mb-3" id="pp"></div>
            <input type="file" id="fileUpload" name="file" class="d-none" accept="image/*">
            <div class="w-100 px-5 mb-2">
                <button type="button" id="fileUploadButton" class="btn btn-outline-primary rounded-pill mb-3 w-100 py-2">
                    <i class="bi bi-upload"></i> Télécharger une photo
                </button>
            </div>
            <hr class="w-100 barre mb-3">

            <p class="text-muted text-center">Ou choisissez un avatar par défaut :</p>
            <div class="d-flex justify-content-center mb-3">
                <img src="assets/avatar1.png" alt="Avatar 1" class="default-avatar rounded-circle m-2" data-avatar="avatar1">
                <img src="assets/avatar2.png" alt="Avatar 2" class="default-avatar rounded-circle m-2" data-avatar="avatar2">
                <img src="assets/avatar3.png" alt="Avatar 3" class="default-avatar rounded-circle m-2 selected" data-avatar="avatar3" id="default">
                <img src="assets/avatar4.png" alt="Avatar 4" class="default-avatar rounded-circle m-2" data-avatar="avatar4">
            </div>

            <button class="btn btn-primary btn-lg w-100 rounded-pill mb-2" name="register" id="register">Terminer l'inscription</button>
            <button type="button" class="btn btn-light btn-lg w-100 rounded-pill mb-3 border-2 border-dark" id="back">Retour</button>

            <div class="text-center">
                <button class="border-0 bg-transparent text-muted text-decoration-underline skip" id="skip">Passer cette étape pour l'instant</button>
            </div>
        </div>   
    </form>
</div>
<script src="js/register.js"></script>
</body>
</html>
