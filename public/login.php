<?php 
    require_once '../includes/connexion.php';
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if(isset($_SESSION["user"])){
        header("Location: accueil.php");
        exit();
    }
?>
<!DOCTYPE html>
    <?php include '../includes/header.php'; ?>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>

    </style>
</head>
<body class="bg-dark">

<div class="d-flex flex-column justify-content-center align-items-center min-vh-100">
    <div class="alert alert-success bi bi-check2-circle d-none" style="width: 40rem;" id="success"> Connection reussie...</div>
    <div class="card shadow-lg rounded-4 p-4" style="width: 40rem;">
        <h1 class="text-center fw-bold mb-4">Connexion</h1>
        <form action="" method="post" id="login-form" novalidate>
            <div class="mb-3 info-content">
                <input type="email" name="infos" class="form-control form-control-lg rounded-pill border-primary infos" id="infos" placeholder="Entrez votre email ou pseudo" />
            </div>

            <div class="mb-4 info-content">
                <input type="password" name="password" class="form-control form-control-lg rounded-pill border-primary pwd" id="password" placeholder="Entrez votre mot de passe" />
                <span class="text-danger ms-3 d-none mt-2" id="server-error">Veuillez remplir ce champ</span>
            </div>

            <button class="btn btn-primary btn-lg w-100 rounded-pill mb-3" id="login">Se connecter</button>

            <p class="text-center fs-6">
                Vous n'avez pas encore de compte ? 
                <a href="register.php" class="text-primary text-decoration-underline">Inscrivez-vous</a>
            </p>
        </form>
    </div>
</div>
<script src="js/login.js"></script>
</body>
</html>
