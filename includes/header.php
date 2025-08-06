<!DOCTYPE html>
<html lang="fr">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <title>ECE Ciné</title>
    <style>
        .nav-item{
            color: white;
            font-weight: 600;
            font-size: 1rem;
        }

        .nav-list{
            margin-block-end: 0.5rem;
        }

        .title{
            color:white;
            font-weight:700;
            font-size: 1.4rem;
            text-wrap: nowrap;
        }

        .title:hover{
            color:rgb(148, 3, 3);
        }
        .main{
            height: fit-content;
            gap:64%
        }

        .nav-item:hover{
            color:rgb(148, 3, 3);
        }
    </style>
</head>
<nav class="main navbar navbar-expand-lg navbar-light bg-light pt-2 d-flex justify-content-start pe-3 ps-3 bg-black">
    <a class="title text-decoration-none" href="accueil.php">🎬 ECE Ciné</a>
    <ul class="nav-list list-unstyled d-flex justify-content-between gap-4 pt-1">
        <li><a href="../public/accueil.php" class="text-decoration-none nav-item" >Accueil</a></li>
        <li><a href="../public/films_list.php" class="text-decoration-none nav-item">Tout parcourir</a></li>
        <li><a href="../public/partager.php" class="text-decoration-none nav-item">Partager</a></li>
        <li><a href="../public/compte.php" class="text-decoration-none nav-item">Compte</a></li>
    </ul>
</nav>