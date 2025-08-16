<!DOCTYPE html>
    <?php include '../includes/header.php'; ?>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>

    </style>
</head>
<body class="bg-dark">

<div class="d-flex justify-content-center align-items-center min-vh-100 pt-4">
    <div class="card shadow-lg rounded-4 p-4" style="width: 40rem;">
        <h1 class="text-center fw-bold mb-4">Inscription</h1>

        <div class="mb-3">
            <input type="text" class="form-control form-control-lg rounded-pill border-primary" placeholder="Entrez votre nom"/>
        </div>


        <div class="mb-3">
            <input type="text" class="form-control form-control-lg rounded-pill border-primary" placeholder="Entrez votre prénom"/>
        </div>

        <div class="mb-3">
            <input type="text" class="form-control form-control-lg rounded-pill border-primary" placeholder="Entrez votre pseudo"/>
        </div>

        <div class="mb-3">
            <input type="email" class="form-control form-control-lg rounded-pill border-primary" placeholder="Entrez votre email" />
        </div>

        
        <div class="mb-3">
            <input type="date" class="form-control form-control-lg rounded-pill border-primary" placeholder="Entrez votre date de naissance" />
        </div>

        <div class="mb-3">
            <input type="password" class="form-control form-control-lg rounded-pill border-primary" placeholder="Entrez votre mot de passe" />
        </div>

        <div class="mb-3">
            <input type="radio" class="form-check-input" name="statut" id="etudiant" value="etudiant" checked>
            <label for="etudiant" class="form-label">Étudiant</label><br>
            <input type="radio" class="form-check-input" name="statut" id="enseignant" value="enseignant">
            <label for="enseignant" class="form-label">Enseignant</label><br>
            <input type="radio" class="form-check-input" name="statut" id="administratif" value="personnel">
            <label for="administratif" class="form-label">Administratif</label><br>
        </div>

        <button class="btn btn-primary btn-lg w-100 rounded-pill mb-3">Inscription</button>

        <p class="text-center fs-6">
            Vous avez déjà un compte ?
            <a href="login.php" class="text-primary text-decoration-underline">Connectez-vous</a>
        </p>
    </div>
</div>

</body>
</html>
