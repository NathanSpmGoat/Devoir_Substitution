<!DOCTYPE html>
    <?php include '../includes/header.php'; ?>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>

    </style>
</head>
<body class="bg-dark">

<div class="d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-lg rounded-4 p-4" style="width: 40rem;">
        <h1 class="text-center fw-bold mb-4">Connexion</h1>

        <div class="mb-3">
            <input type="email" class="form-control form-control-lg rounded-pill border-primary" placeholder="Entrez votre email" />
        </div>

        <div class="mb-4">
            <input type="password" class="form-control form-control-lg rounded-pill border-primary" placeholder="Entrez votre mot de passe" />
        </div>

        <button class="btn btn-primary btn-lg w-100 rounded-pill mb-3">Se connecter</button>

        <p class="text-center fs-6">
            Vous n'avez pas encore de compte ? 
            <a href="register.php" class="text-primary text-decoration-underline">Inscrivez-vous</a>
        </p>
    </div>
</div>

</body>
</html>
