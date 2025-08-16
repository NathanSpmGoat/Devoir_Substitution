<?php 
    $currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <title>ECE Ciné</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

        html{
            font-family: 'Poppins', sans-serif;
        }
        .nav-item{
            position: relative;
            color: white;
            font-weight: 400;
            font-size: 1rem;
            transition:color 0.2s ease-in-out
        }

        .nav-list{
            margin-block-end: 0.5rem;
        }

        .title{
            color:white;
            font-weight:700;
            font-size: 1.6rem;
            text-wrap: nowrap;
            transition: color 0.2s ease-in-out;
        }

        .title:hover{
            color:#E50914
        }
        .main{
            height: fit-content;
            gap:60%;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        }


        .nav-item::after {
            content: "";
            position: absolute;
            bottom: -4px;  
            left: 0;   
            width: 0%;
            height: 2px;
            background-color: #E50914;
            transition: width 0.3s ease;
        }

        .nav-item:hover {
            color:#000
        }

        .nav-item:hover::after {
            width: 100%;
        }

        .nav-item.active::after {
            content: "";
            position: absolute;
            bottom: -4px;  
            left: 0;   
            width: 100%;
            height: 2px;
            background-color: #E50914;
        }

    </style>
</head>
<nav class="main navbar navbar-expand-lg navbar-light bg-light py-3 d-flex justify-content-end pe-5 bg-black">
    <a class="title text-decoration-none" href="accueil.php">ECE Ciné</a>
    <ul class="nav-list list-unstyled d-flex justify-content-between gap-4 pt-1">
        <li><a href="../public/accueil.php" class="text-decoration-none nav-item <?=($currentPage=="accueil.php")?'active':''?>">Accueil</a></li>
        <li><a href="../public/films_list.php" class="text-decoration-none nav-item <?=($currentPage=="films_list.php")?'active':''?>">Tout parcourir</a></li>
        <li><a href="../public/partager.php" class="text-decoration-none nav-item <?=($currentPage=="partager.php")?'active':''?>">Partager</a></li>
        <li><a href="../public/compte.php" class="text-decoration-none nav-item <?=($currentPage=="compte.php")?'active':''?>">Compte</a></li>
        <li><a href="../public/admin.php" class="text-decoration-none nav-item <?=($currentPage=="admin.php")?'active':''?>">Admin</a></li>
    </ul>
</nav>
</html>