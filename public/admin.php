<?php 
    // if (session_status() === PHP_SESSION_NONE) {
    //     session_start();
    // }
    $page = isset($_GET['page']) ? $_GET['page'] :'';
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
    </style>
</head>
<body>
    <div class="content d-flex flex-row w-100">
    
    <aside class="sidebar d-flex flex-column align-items-center pt-4 px-2">
        <div>
            <h3 class="titre text-danger fw-bolder fs-2 text-center">ECE Ciné Admin</h3>
            <ul class="text-white w-100 list-unstyled d-flex flex-column gap-2 px-3">
                <li><a href="admin.php?page=board" class="menu-item nav-link text-start rounded-1 <?=($page=="board")?'active':''?>">📊 Tableau de bord</a></li>
                <li><a href="admin.php?page=films" class="menu-item nav-link text-start rounded-1 <?=($page=="films")?'active':''?>">🎬 Validation des Films</a></li>
                <li><a href="admin.php?page=register" class="menu-item nav-link text-start rounded-1 <?=($page=="register")?'active':''?>">👤 Validation des Inscriptions</a></li>
                <li><a href="admin.php?page=users" class="menu-item nav-link text-start rounded-1 <?=($page=="users")?'active':''?>">⚙️ Gestion <br>des Utilisateurs</a></li>
                <li><a href="admin.php?page=manage_films" class="menu-item nav-link text-start rounded-1 <?=($page=="manage_films")?'active':''?>">⚙️ Gestion des films</a></li>

            </ul>
        </div>
    </aside>
    <?php 
        if (isset($page) && $page !== ''):
            switch ($page):
                case 'board':?>
                    <div class="board p-5 text-white">
                        <h2 class="fs-1 mb-5">Tableau de bord</h2>
                        <div class="d-flex flex-column gap-4">
                            <div class="d-flex flex-row justify-content-between gap-5">
                                <div class="infos d-flex flex-column text-center gap-2 rounded-2">
                                    <h5>Inscriptions en attente</h5>
                                    <h2 class="text-danger fs-1 fw-bold">10</h2>
                                </div>

                                <div class="infos d-flex flex-column text-center gap-2 rounded-2 text-nowrap">
                                    <h5>Films à valider</h5>
                                    <h2 class="text-danger fs-1 fw-bold">10</h2>
                                </div>

                                <div class="infos d-flex flex-column text-center gap-2 rounded-2">
                                    <h5>Nombre total d'utilisateurs</h5>
                                    <h2 class="text-danger fs-1 fw-bold">10</h2>
                                </div>
                            </div>

                            <div class="d-flex flex-row justify-content-center gap-5">
                                <div class="infos d-flex flex-column text-center gap-2 rounded-2">
                                    <h5>Inscriptions validées</h5>
                                    <h2 class="text-danger fs-1 fw-bold">10</h2>
                                </div>

                                <div class="infos d-flex flex-column text-center gap-2 rounded-2 text-nowrap">
                                    <h5>Films validés</h5>
                                    <h2 class="text-danger fs-1 fw-bold">10</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    </div>
    <?php break;
          case 'films':?>

                    <div class="films p-5 text-white">
                        <h2 class="fs-1 mb-5">Films à valider</h2>
                        <div class="d-flex flex-column gap-4">
                            <div class="d-flex flex-row justify-content-between gap-5">
                                <div class="infos d-flex flex-column gap-2 rounded-2">
                                    <img src="https://placehold.co/200x130" style="width:fit-content;height: fit-content;max-width: 200px;max-height: 130px;" alt="">
                                    <h2 class="text-light fs-5 fw-bold mb-0">Auteur : Nathan</h2>
                                    <h2 class="text-light fs-5 fw-bold">Titre : Titre du film</h2>
                                    <button class="btn btn-success">Valider</button>
                                    <button class="btn btn-danger">Rejeter</button>
                                </div>
                            </div>
                        </div>
                    </div>

    <?php break;
        case "register":?>

            <div class="register p-5 text-white">
                <h2 class="fs-1 mb-5">Films à valider</h2>
                <table class="table-bordered mytable text-center">
                    <thead class="headers">
                        <th scope="col" class="header">Pseudo</th>
                        <th scope="col" class="header">Email</th>
                        <th scope="col" class="header">Statut</th>
                        <th scope="col" class="header">Date de naissance</th>
                        <th scope="col" class="header">Action</th>
                    </thead>

                    <tbody>
                        <tr>
                            <td class="p-3">NathanGoat</td>
                            <td>NathanGoat</td>
                            <td>NathanGoat</td>
                            <td>NathanGoat</td>
                            <td>
                                <div class="d-flex flex-row justify-content-center gap-3 p-3">
                                    <button class="btn btn-success px-3 py-2">Valider</button>
                                    <button class="btn btn-danger px-3 py-2">Rejeter</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php break;
        case "users":?>

            <div class="users p-5 text-white">
                <h2 class="fs-1 mb-5">Gestion des utilisateurs</h2>
                <table class="table-bordered mytable text-center">
                    <thead class="headers">
                        <th scope="col" class="header">Pseudo</th>
                        <th scope="col" class="header">Email</th>
                        <th scope="col" class="header">Statut</th>
                        <th scope="col" class="header">Date de naissance</th>
                        <th scope="col" class="header">Action</th>
                    </thead>

                    <tbody>
                        <tr>
                            <td class="p-3">NathanGoat</td>
                            <td>NathanGoat</td>
                            <td>NathanGoat</td>
                            <td>NathanGoat</td>
                            <td>
                                <div class="d-flex flex-row justify-content-center gap-3 p-3">
                                    <button class="btn btn-primary px-4 py-2">Modifier</button>
                                    <button class="btn btn-danger px-3 py-2">supprimer</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        <?php break;
            case "manage_films":?>
                <div class="films p-5 text-white">
                        <h2 class="fs-1 mb-5">Films validés</h2>
                        <div class="d-flex flex-column gap-4">
                            <div class="d-flex flex-row justify-content-between gap-5">
                                <div class="infos d-flex flex-column gap-2 rounded-2">
                                    <img src="https://placehold.co/200x130" style="width:fit-content;height: fit-content;max-width: 200px;max-height: 130px;" alt="">
                                    <h2 class="text-light fs-5 fw-bold mb-0">Auteur : Nathan</h2>
                                    <h2 class="text-light fs-5 fw-bold">Titre : Titre du film</h2>
                                    <button class="btn btn-primary">Modifier</button>
                                    <button class="btn btn-danger">Supprimer</button>
                                </div>
                            </div>
                        </div>
                    </div>
    <?php 
    endswitch;
    endif;
    ?>



</body>
</html>