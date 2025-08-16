<!DOCTYPE html>
    <?php include '../includes/header.php'; ?>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>

    </style>
</head>
<body class="bg-dark">

<div class="d-flex justify-content-center align-items-start min-vh-100">
    <div class="card shadow-lg rounded-4 p-4" style="width: 50rem;">
        <h1 class="text-center fw-bold mb-3">Partager un nouveau film</h1>
        <p class="text-center text-muted mb-4">Partagez un film qui vous a marqué et qui n'est pas encore sur la plateforme. Il sera visible par la <br>communauté après validation.</p>
        
        <div class="mb-3">
            <label for="titre" class="mb-2">Titre du film</label>
            <input type="text" class="form-control form-control" id="titre" />
        </div>

        <div class="mb-3">
            <label for="realisateur" class="mb-2">Réalisateur(s)</label>
            <input type="text" class="form-control form-control" id="realisateur" />
        </div>

        <div class="mb-3">
            <label for="affiche" class="mb-2">Affiche du film</label>
            <input type="file" class="form-control form-control" id="affiche" />
        </div>
        
        <div class="mb-4">
            <label for="trailer" class="mb-2">URL du trailer (optionnel)</label>
            <input type="text" class="form-control form-control" id="trailer" placeholder="https://youtube.com/watch?v=..."/>
        </div>
        
        
        <button class="btn btn-primary btn-lg w-100 rounded-pill mb-3">Partager</button>
    </div>
</div>

</body>
</html>
