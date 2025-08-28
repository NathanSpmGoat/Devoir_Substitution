<?php 
    require "../includes/connexion.php";
    require 'vendor/autoload.php';
    
    if (session_status() == PHP_SESSION_NONE) 
        session_start();

    if(!isset($_SESSION["user"])){
        header("Location: login.php");
        exit;
    }

    use Cloudinary\Cloudinary;

    $cloudinary = new Cloudinary([
    'cloud' => [
        'cloud_name' => 'dghnwzbla',
        'api_key'    => '285744794393346',
        'api_secret' => 'znehOzX3fHZqQWo51nhU__BAgCY',
    ],
    ]);

    $stmt=$conn->prepare("SELECT * FROM genre");
    $stmt->execute();
    $genres=$stmt->fetchAll(PDO::FETCH_ASSOC);

    if (isset($_POST["share"]))
    {
      usleep(500000);
      $titre = $_POST['titre'];
      $realisateur = $_POST['realisateur'];
      $annee = $_POST['annee'];
      $description = $_POST['description'];
      $trailer = $_POST['trailer'] ?? "";
      $genre = $_POST['genre'] ?? [];
      $user = $_SESSION['user']['id'];

      $file_name=uniqid("user_",true).'_'. bin2hex(random_bytes(5));
      $uploadResult= $cloudinary->uploadApi()->upload($_FILES['file']['tmp_name'], [
          'folder' => 'ECE-Cinema/affiches',
          'public_id' => $file_name,
          'overwrite' => true,
          'resource_type' => 'image'
      ]);

      $stmt = $conn->prepare("INSERT INTO film (titre, realisateur, annee, description, trailer, affiche, id_utilisateur) VALUES (?, ?, ?, ?, ?, ?, ?)");
      $stmt->execute([
            $titre,
            $realisateur,
            $annee,
            $description,
            $trailer,
            $uploadResult['secure_url'],
            $user,
        ]);
    $lastid=$conn->lastInsertId();

      foreach($genre as $g){
          $stmt = $conn->prepare("INSERT INTO film_genre (film_id, genre_id) VALUES (?, (SELECT id FROM genre WHERE libelle = ?))");
          $stmt->execute([
              $lastid, $g
          ]);
      }

      header("Location: accueil.php");
      exit;
    }
    else if (isset($_GET["edit"]))
    {
      $stmt = $conn->prepare("SELECT * FROM film WHERE id = ?");
      $stmt->execute([$_GET['id_film']]);
      $film_infos=$stmt->fetch(PDO::FETCH_ASSOC);
      $stmt = $conn->prepare("SELECT g.libelle FROM genre g JOIN film_genre fg ON g.id = fg.genre_id WHERE fg.film_id = ?");
      $stmt->execute([$_GET['id_film']]);
      $film_genres=$stmt->fetchAll(PDO::FETCH_COLUMN);

      if (isset($_POST["share"]))
      {
        usleep(500000);
        $titre = $_POST['titre'];
        $realisateur = $_POST['realisateur'];
        $annee = $_POST['annee'];
        $description = $_POST['description'];
        $trailer = $_POST['trailer'] ?? "";
        $genre = $_POST['genre'] ?? [];

        if ($_FILES['file']['size'] && $_FILES['file']['size']>0){
            $file_name=uniqid("user_",true).'_'. bin2hex(random_bytes(5));
            $uploadResult= $cloudinary->uploadApi()->upload($_FILES['file']['tmp_name'], [
                'folder' => 'ECE-Cinema/affiches',
                'public_id' => $file_name,
                'overwrite' => true,
                'resource_type' => 'image'
            ]);
            $stmt = $conn->prepare("UPDATE film SET titre=?, realisateur=?, annee=?, description=?, trailer=?, affiche=? WHERE id=?");
            $stmt->execute([
                $titre,
                $realisateur,
                $annee,
                $description,
                $trailer,
                $uploadResult['secure_url'],
                $_GET['id_film']
            ]);
        } else {
            $stmt = $conn->prepare("UPDATE film SET titre=?, realisateur=?, annee=?, description=?, trailer=? WHERE id=?");
            $stmt->execute([
                $titre,
                $realisateur,
                $annee,
                $description,
                $trailer,
                $_GET['id_film']
            ]);
        }
        
        $stmt = $conn->prepare("DELETE FROM film_genre WHERE film_id = ?");
        $stmt->execute([$_GET['id_film']]);
        foreach($genre as $g){
            $stmt = $conn->prepare("INSERT INTO film_genre (film_id, genre_id) VALUES (?, (SELECT id FROM genre WHERE libelle = ?))");
            $stmt->execute([
            $_GET['id_film'], $g
            ]);
        }

        header("Location: accueil.php");
        exit;
      }
    }
?>
<!DOCTYPE html>
    <?php include '../includes/header.php'; ?>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        html, body {
        height: 100%;
     }

     body{
        background-color: rgb(32, 32, 32);
     }

     .checkbox{
        transform: scale(1.5);
     }
    </style>
</head>
<body class="d-flex flex-column pb-4">
    <div class="d-flex justify-content-center align-items-start mb-5 flex-grow-1">
        <div class="card shadow-lg rounded-4 p-4" style="width: 50rem;">
            <h1 class="text-center fw-bold mb-3">Partager un nouveau film</h1>
            <p class="text-center text-muted mb-4">Partagez un film qui vous a marqué et qui n'est pas encore sur la plateforme. Il sera visible par la <br>communauté après validation.</p>
            <form action="" method="POST" enctype="multipart/form-data" id="formulaire"> <!-- POST -->
                <input type="hidden" name="share" value="1"> <!-- pour déclencher la branche PHP -->
                <div class="mb-3">
                    <label for="titre" class="mb-2">Titre du film<span class="text-danger">*</span></label>
                    <input type="text" name="titre" class="form-control form-control" value="<?=$film_infos["titre"]??''?>" id="titre" />
                </div>

                <div class="mb-3">
                    <label for="realisateur" class="mb-2">Réalisateur(s)<span class="text-danger">*</span></label>
                    <input type="text" name="realisateur" class="form-control form-control" placeholder="ex : (Nathan , Kevin , Igor)" value="<?=$film_infos['realisateur']??''?>" id="realisateur" />
                </div>

                <div class="mb-3">
                    <label for="genre" class="mb-2">Genre<span class="text-danger">*</span></label>
                    <div class="d-flex flex-column">
                        <?php   
                            foreach($genres as $g):
                        ?>
                        <div class="d-flex justify-content-between w-25">
                            <label for="genre" class="me-3 fs-5 text-muted"><?= htmlspecialchars($g['libelle'])?></label>
                            <input type="checkbox" name="genre[]" id="genre" value="<?= htmlspecialchars($g['libelle']) ?>" <?= isset($film_genre)?? array_filter($film_genres, fn($f) => stripos($f,$g['libelle']) !== false) ? "checked":'' ?> class="genre form-check checkbox"/>                
                        </div>
                            <?php endforeach; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="affiche" class="mb-2">Affiche du film<span class="text-danger">*</span></label>
                    <input type="file" name="file" class="form-control form-control" id="affiche" accept="image/" />
                </div>

                <div class="mb-3">
                    <label for="annee" class="mb-2">Année de sortie<span class="text-danger">*</span></label>
                    <input type="number" name="annee" class="form-control form-control" id="annee" min="1888" max="2025" value="<?=$film_infos['annee']??''?>" placeholder="1888-2025"/>
                </div>
                
                <div class="mb-4">
                    <label for="trailer" class="mb-2">URL du trailer (optionnel)</label>
                    <input type="text" name="trailer" class="form-control form-control" id="trailer" value="<?=$film_infos['trailer']??''?>" placeholder="https://youtube.com/watch?v=..."/>
                </div>

                <div class="mb-1">
                    <label for="description" class="mb-2">Description du film<span class="text-danger">*</span></label>
                    <textarea class="form-control" name="description" id="description" rows="4"><?=$film_infos['description']??''?></textarea>
                </div>
                <span class="d-none text-danger error">Veuillez remplir tous les champs*</span>
                
                <button class="btn btn-primary btn-lg w-100 rounded-pill mt-3" id="share" name="share">Partager</button>
            </form>
        </div>  
    </div>
    <?php include '../includes/footer.php'; ?>  
<script>
    const afficheInput = document.getElementById('affiche');
    const shareButton = document.getElementById('share');

    shareButton.addEventListener('click', function(event) {
        event.preventDefault();
        const titre = document.getElementById('titre').value.trim();
        const realisateur = document.getElementById('realisateur').value.trim();
        const annee = document.getElementById('annee').value.trim();
        const description = document.getElementById('description').value.trim();
        const genre = document.querySelectorAll('.genre')
        var checked = false

        const params = new URLSearchParams(window.location.search);
        let edit_mode=false;
        if (params.has("edit")){
            edit_mode=true;
        }

        genre.forEach((g) => {
            if (g.checked) {
                checked = true;
            }
        });
        
        
        if (!titre || !realisateur || !annee || !description || (!afficheInput.files.length && !edit_mode) || !checked) {
            document.querySelector('.error').classList.remove('d-none');
            return;
        } else {
            setTimeout(() => {
                document.querySelector('.error').classList.remove('d-none');
                if (!edit_mode){
                    document.querySelector('.error').textContent = 'Film ajouté avec succès , en attente de validation !';
                    document.querySelector('.error').classList.remove('text-danger');
                    document.querySelector('.error').classList.add('text-success');
                    }
                else{
                    document.querySelector('.error').textContent = 'Film modifié avec succès !';
                    document.querySelector('.error').classList.remove('text-danger');
                    document.querySelector('.error').classList.add('text-success');
                    }
                    setTimeout(() => {
                        document.getElementById('formulaire').submit();
                    }, 500);
                }, 600);
        }
    });

</script>
</body>
</html>
