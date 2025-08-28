<?php 
    include '../includes/connexion.php';
    require 'vendor/autoload.php';

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if(!isset($_SESSION["user"])){
        header("Location: login.php");
        exit();
    }
    if(!isset($_GET["mesfilms"]) && !isset($_GET["mesinfos"])){
        header("location:compte.php?mesfilms");
        exit;}

    use Cloudinary\Cloudinary;

    $cloudinary = new Cloudinary([
    'cloud' => [
        'cloud_name' => 'dghnwzbla',
        'api_key'    => '285744794393346',
        'api_secret' => 'znehOzX3fHZqQWo51nhU__BAgCY',
    ],
    ]);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user']['id'])) {
            echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté.']);
            exit();
        }
        $userId = $_SESSION['user']['id'];

        $response = ['success' => false, 'message' => 'Aucune modification détectée.'];
        $updateHappened = false;

        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {

            $userId = $_SESSION['user']['id'];
            $stmt = $conn->prepare("SELECT avatar FROM utilisateur WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            $current_avatar_url = $user['avatar'] ?? null;
            
            $public_id_to_overwrite = null;

            if (!empty($current_avatar_url) && str_contains($current_avatar_url, 'cloudinary')) {

                $filename_with_ext = basename($current_avatar_url);
                

                $filename_without_ext = pathinfo($filename_with_ext, PATHINFO_FILENAME);
                

                $public_id_to_overwrite = 'ECE-Cinema/users_picture_profile/' . $filename_without_ext;
            } else {

                $public_id_to_overwrite = 'ECE-Cinema/users_picture_profile/user_' . $userId . '_' . uniqid();
            }


            $uploadResult = $cloudinary->uploadApi()->upload($_FILES['file']['tmp_name'], [
                'public_id' => $public_id_to_overwrite,
                'overwrite' => true
            ]);

            $new_avatar_url = $uploadResult['secure_url'];


            $stmt = $conn->prepare("UPDATE utilisateur SET avatar = ? WHERE id = ?");
            $stmt->execute([$new_avatar_url, $userId]);

            $_SESSION['user']['avatar'] = $new_avatar_url;
            
            $response = [
                'success' => true,
                'message' => 'Photo de profil mise à jour !',
                'newAvatar' => $new_avatar_url
            ];
            $updateHappened = true;
        }
        

        if (isset($_POST['pseudo']) && !empty(trim($_POST['pseudo'])) && $_POST['pseudo'] !== $_SESSION['user']['pseudo']) {
            if (strlen($_POST['pseudo']) >= 3) {
                $newPseudo = htmlspecialchars(trim($_POST['pseudo']));
                $stmt=$conn->prepare("SELECT * FROM utilisateur WHERE pseudo=?");
                $stmt->execute([$newPseudo]);
                $check_pseudo = $stmt->fetch();
                if (!$check_pseudo){
                    $stmt = $conn->prepare("UPDATE utilisateur SET pseudo = ? WHERE id = ?");
                    $stmt->execute([$newPseudo, $userId]);
                    $_SESSION['user']['pseudo'] = $newPseudo;
                    $updateHappened = true;
                    }
                else
                {
                    echo json_encode(['success' => false, 'message' => 'Ce pseudo est déjà attribué','pseudo'=>true]);
                    exit();
                }
            } else {
                 echo json_encode(['success' => false, 'message' => 'Le pseudo doit faire au moins 3 caractères.','pseudo'=>true]);
                 exit();
            }
        }

        if (!empty($_POST['newpassword'])) {
            if (empty($_POST['oldpassword']) || empty($_POST['confirmpassword'])) {
                echo json_encode(['success' => false, 'message' => 'Veuillez remplir tous les champs de mot de passe.','pseudo'=>false]);
                exit();
            }
            if ($_POST['newpassword'] !== $_POST['confirmpassword']) {
                echo json_encode(['success' => false, 'message' => 'Les nouveaux mots de passe ne correspondent pas.','pseudo'=>false]);
                exit();
            }

            $pwd = $_POST['newpassword'];
            if (strlen($pwd) < 8 || 
            !preg_match('/[A-Z]/', $pwd) || 
            !preg_match('/\d/', $pwd) || 
            !preg_match('/[^a-zA-Z0-9]/', $pwd)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Le mot de passe doit contenir : 8 caractères minimum, une majuscule, un chiffre et un caractère spécial.',
                    'pseudo' => false
                ]);
                exit();
            }

            $stmt = $conn->prepare("SELECT password FROM utilisateur WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if ($user && password_verify($_POST['oldpassword'], $user['password'])) {
                $newHashedPassword = password_hash($_POST['newpassword'], PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE utilisateur SET password = ? WHERE id = ?");
                $stmt->execute([$newHashedPassword, $userId]);
                $updateHappened = true;
            } else {
                echo json_encode(['success' => false, 'message' => 'Ancien mot de passe incorrect.','pseudo'=>false]);
                exit();
            }
        }

        if ($updateHappened && (!isset($response['success']) || $response['success'] === false)) {
            $response['success'] = true;
            $response['message'] = 'Modifications enregistrées avec succès !';
        }

        echo json_encode($response);
        exit();
    
    } 

?>
<!DOCTYPE html>
    <?php include '../includes/header.php'; ?>
<head>
    <script src="https://www.youtube.com/iframe_api"></script>
   <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
    .main {
        margin-top: 3rem;
        padding-inline: 10rem;
    }

    .main .nav .nav-item .nav-link{
        color:rgb(123, 123, 123);
        border: solid rgb(68, 68, 68) 1px;
        font-weight: 400;
        cursor: pointer;
    }

    .main .nav .nav-item .nav-link:hover, .main .nav .nav-item .nav-link.active{
        color:rgb(255, 255, 255);
        font-weight: 600;
        border-color: #fff;
        background-color: rgb(34, 34, 34);
    }

    .pp{
        background-image:url("<?=htmlspecialchars($_SESSION['user']['avatar'])?>");
        background-size: cover;
        background-position: center;
        cursor: pointer;
        border-radius: 50%;
        max-width: 190px;
        width: 190px;
        height: 190px;
        max-height: 190px;
    }

    .change{
        height:fit-content
    }

    .content{
        <?=isset($_GET["mesinfos"])?"padding-inline:10rem":''?>
    }
    
    input:disabled{
        background-color:rgb(136, 147, 162) !important;
    }

    html, body {
        height: 100%;
        font-family: 'Poppins', sans-serif;
    }

    .film{
        max-width: 270px;
        width: 270px;
        height: 150px;
        max-height: 150px;
        cursor: pointer;
        transform: scale(1);
        transition:transform 0.3s ease;
        position: relative;
        border-radius: 6px;
    }

    .film:hover{
        transform: scale(1.05);
        transition: transform 0.3s ease;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        z-index: 2;
    }

    .film::after
    {
        content: "";
        position: absolute;
        top: 0;
        left: 1%;
        width: 70px;
        height: 30px;
        background-image: url("assets/ECE_logo.png");
        background-size: cover;
        background-position: center;
    }

    .film_titre{
        position: absolute;
        bottom: 50%;
        transform: translateY(50%);
        right:0px;
        color: white;
        padding:5px 0;
        margin: 0;
        font-size: 1.5rem;
        font-weight: 800;
        border-bottom-left-radius: 4px;
        border-bottom-right-radius: 4px;
        opacity: 0;
        transition:opacity 0.3s ease;
    }

    .like-container{
        font-size: 1.8rem;
        color:rgb(255, 0, 136);
        position: absolute;
        bottom:0;
        right: 10px;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .film:hover .like-container ,.film:hover .film_titre{
        opacity: 1;
        transition: opacity 0.3s ease;
    }

    .more{
        display:none;
    }

    .more.active{
        position:absolute;
        right:50%;
        top:50%;
        transform: translate(50%, -50%);
        height: 100%;
        width: 100%;
        z-index: 1000;
        background-color: rgba(0, 0, 0, 0.5);
        display:flex;
        justify-content: center;
        align-items: center;
    }

    .more .contenu{
        width: 0%;
        height: 0%;
        background-color: rgb(31, 31, 31);
        margin-top:6rem;
        border-radius:3px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        border-bottom: 0;
        transition:width 1s ease, height 1s ease;
    }

    .affiche{
        width: 100%;
        aspect-ratio: 16 / 9;
        background-size: cover;
        background-position: center;
        box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.5);
        position: relative;
    }

    .more.active .contenu{
        height: auto;
        max-height: 90vh;
        overflow-y: auto; 
        width: 60%;
        transition:width 0.5s ease
    }

    .description{
        color: white;
        width: 100%;
        padding:0 15px;
        font-size: 1.0rem;
        font-weight: 500;
    }

    .contenu .titre{
        font-size: 2.0rem;
        font-weight: 800;
        margin: 0;
        color:white;
        margin-left:1.4rem
    }

    #modal-actions {
    position: absolute;
    bottom: 2rem;
    left: 2rem; 
    z-index: 10;
    }

    .mute-btn {
    position: absolute;
    top: 50%;
    right: 20px;
    transform: translateY(-50%);
    z-index: 10;
    }
    .mute-btn i {
        font-size: 1.6rem;
        color: white;
    }
   </style>
</head>
<body class="d-flex flex-column">
    <div class="more">
        <div class="contenu">
        <div class="affiche mt-2 affiche mb-2">
            <div id="player-container"></div> 
            <div id="modal-actions" class="d-flex align-items-center gap-2"></div>
        </div>
            <div class="details p-4">
                <form action="" id="form-film">
                    <h3 class="titre text-danger mb-1"></h3>
                    <p class="description ms-2 mb-4 mt-0">Lorem ipsum dolor sit amet consectetur adipisicing elit. Fuga assumenda perferendis at quam fugiat, voluptatum odio sapiente alias cumque deserunt!</p>
                    <div>
                        <div class="d-flex flex-row justify-content-between px-4 h-100">
                            <div class="d-flex flex-column">
                                <p class="text-secondary">Année : <span class="text-white" id="annee">2030</span></p>
                                <p class="text-secondary">Réalisateur : <span class="text-white" id="realisateur">John Doe</span></p>
                                <p class="text-secondary">Publié par : <span class="text-white" id="auteur">Jane Smith</span></p>
                            </div>

                            <div class="me-5">
                                <p class="text-secondary me-5 m-0">Genres : </span></p>
                                <div class="d-flex flex-wrap gap-2" id="container-genre">
                                    
                                </div>
                            </div>

                            <div class="d-flex align-items-end gap-2">
                                <button type="button" class="btn btn-outline-primary px-5" name="share" id="share">Modifier</button>
                            </div>
                    </div>
                        
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="main flex-grow-1 position-relative">
        
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link <?=isset($_GET["mesfilms"])?"active":''?>" href="compte.php?mesfilms">Mes films</a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?=isset($_GET["mesinfos"])?"active":''?>" href="compte.php?mesinfos" >Mon compte</a>
            </li>
        </ul>

        <?php if(isset($_GET["mesinfos"])):?>
            <div class="w-100 h-100 bg-transparent mt-3 text-white py-3 content">
                <h2 class="fs-1 ms-5">Mon compte</h2>
                <form class="w-100" id="form" enctype="multipart/form-data" method="POST">
                    <div class="w-100 d-flex justify-content-center align-items-center gap-1">
                        <div class="pp mt-3" id="pp"></div>
                        <button type="button" class="btn btn-outline-light change mt-5 px-4">Changer la photo</button>
                        <input type="file" id="fileUpload" name="file" class="d-none" accept="image/*">
                    </div>

                    <div class="mt-5 px-5">
                        <label for="nom" name="nom" class="mb-2 mt-3">Nom</label>
                        <input type="text" class="form-control bg-dark text-white" disabled id="nom" value="<?=htmlspecialchars($_SESSION['user']['nom'])?>">
                    </div>

                    <div class="mt-3 px-5">
                        <label for="prenom" class="mb-2 mt-3">Prenom</label>
                        <input type="text" name="prenom" class="form-control bg-dark text-white" disabled id="prenom" value="<?=htmlspecialchars($_SESSION['user']['prenom'])?>">
                    </div>

                    <div class="mt-3 px-5">
                        <label for="email" class="mb-2 mt-3">Email</label>
                        <input type="email" class="form-control bg-dark text-white" disabled id="email" value="<?=htmlspecialchars($_SESSION['user']['email'])?>">
                    </div>

                    <div class="mt-3 px-5">
                        <label for="statut" class="mb-2 mt-3">Statut</label>
                        <input type="text" class="form-control bg-dark text-white" disabled id="statut" value="<?=htmlspecialchars($_SESSION['user']['statut'])?>">
                    </div>

                    <div class="mt-3 px-5">
                        <label for="pseudo" class="mb-2 mt-3">Pseudo</label>
                        <input type="text" class="form-control bg-dark text-white" id="pseudo" name="pseudo" value="<?=htmlspecialchars($_SESSION['user']['pseudo'])?>">
                    </div>
                    
                    <div class="d-flex justify-content-end pt-1 mb-5">
                        <span class="text-danger mx-5 d-none" id="error_pseudo">Veuillez saisir un pseudo d'au moins 3 caractères</span>
                    </div>

                    <div class="px-5">
                        <hr>
                    </div>

                    <h3 class="px-5 mt-5">Changer le mot de passe</h3>

                    <div class="mt-3 px-5 mb-5">
                        <label for="oldpassword" class="mb-2 mt-3">Ancien mot de passe</label>
                        <input type="password" class="form-control bg-dark text-white" id="oldpassword" name="oldpassword" placeholder="Entrez votre ancien mot de passe">
                    </div>

                    <div class="mt-3 px-5 mb-5">
                        <label for="newpassword" class="mb-2">Nouveau mot de passe</label>
                        <input type="password" class="form-control bg-dark text-white" id="newpassword" name="newpassword" placeholder="Entrez votre nouveau mot de passe">
                    </div>

                    <div class="mt-3 px-5">
                        <label for="confirmpassword" class="mb-2">Confirmer le nouveau mot de passe</label>
                        <input type="password" class="form-control bg-dark text-white" id="confirmpassword" name="confirmpassword" placeholder="Confirmez votre nouveau mot de passe">
                    </div>

                    <div class="d-flex justify-content-end pt-1 mb-4">
                        <span class="text-danger mx-5 d-none" id="error_pwd">Les mots de passe ne correspondent pas</span>
                    </div>

                    <div class="d-flex justify-content-center px-5">
                        <button class="btn btn-danger w-100 py-2 fw-medium fs-5" id="register">Enregistrer les modifications</button>
                    </div>

                    <div class="d-flex justify-content-end pt-1">
                        <span class="text-success mx-5 d-none" id="info_register">Modifications effectués avec succès</div>
                    </div>
                </form>
            </div>
        <?php elseif(isset($_GET["mesfilms"])): ?>
            <div class="w-100 h-100 bg-transparent mt-3 text-white py-3 content">
                <h2 class="fs-1">Mes films</h2>
                <div class="w-100 d-flex flex-row flex-wrap gap-4 mt-4 w-100">
                    <?php 
                        $sql = "
                        SELECT 
                            f.*, 
                            u.pseudo AS auteur_pseudo,
                            (SELECT COUNT(*) FROM film_like WHERE id_film = f.id) AS Nb_likes
                        FROM film f
                        JOIN utilisateur u ON f.id_utilisateur = u.id
                        WHERE f.id_utilisateur = ? AND f.valide=1";
                        $stmt=$conn->prepare($sql);
                        $stmt->execute([$_SESSION['user']['id']]);
                        $films=$stmt->fetchAll();
                        if(count($films) == 0){
                            echo "<p class='fs-4 text-center mt-5'>Vous n'avez pas encore partagé de films. <a href='partager.php' class='text-decoration-none text-danger'>Partagez-en un maintenant !</a></p>";
                        }
                        foreach($films as $film): 
                            $like=$film['Nb_likes'];
                            $auteur=$film['auteur_pseudo'];
                            $trailer=htmlspecialchars($film['trailer']);
                            $stmt=$conn->prepare("SELECT `libelle` FROM genre WHERE id IN (SELECT genre_id FROM film_genre WHERE film_id = ?)");
                            $stmt->execute([$film["id"]]);
                            $genres = $stmt->fetchAll(PDO::FETCH_COLUMN);
                            $base_embed_url = str_replace("watch?v=", "embed/", $trailer);
                            $embed_url = $base_embed_url . '?autoplay=1&mute=1&controls=0&showinfo=0&modestbranding=1&rel=0&loop=1&playlist=' . basename($base_embed_url);
                            ?>
                            <div class="film" data-id="<?=$film["id"]?>" data-auteur="<?=$auteur?>" data-titre="<?=$film["titre"]?>" data-annee="<?=$film["annee"]?>" data-realisateur="<?=$film["realisateur"]?>" data-image="<?=$film["affiche"]?>" data-description="<?=$film["description"]?>" data-genres='<?= json_encode($genres, JSON_HEX_APOS | JSON_HEX_QUOT) ?>' data-trailer="<?=($film["trailer"]==null)?'':$embed_url?>" style="background-image:url('<?=htmlspecialchars($film['affiche'])?>'); background-size: cover; background-position: center;">
                                <h5 class="film_titre"><?=$film["titre"]?></h5>
                                <div class="like-container">
                                    <div><span class=text-white><?=$film['Nb_likes']?></span> <i class="bi bi-heart-fill like"></i></div>
                                </div>
                            </div>
                        <?php endforeach;
                    ?>
                </div>
            </div>

    <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script>
    const films = document.querySelectorAll('.film');
    const modal = document.querySelector('.more');

    const form = document.getElementById('form');
    const form_film = document.getElementById('form-film');

    const registerButton = document.getElementById('register');
    const fileUpload = document.getElementById('fileUpload');
    const ppDiv = document.getElementById('pp');
    const changeButton = document.querySelector('.change');

    const pseudoInput = document.getElementById('pseudo');
    const oldPasswordInput = document.getElementById('oldpassword');
    const newPasswordInput = document.getElementById('newpassword');
    const confirmPasswordInput = document.getElementById('confirmpassword');

    const errorPseudo = document.getElementById('error_pseudo');
    const errorPwd = document.getElementById('error_pwd');
    const infoRegister = document.getElementById('info_register');
    let current_film = null; // <-- était const '' : à corriger

    if (ppDiv && fileUpload) ppDiv.addEventListener('click', () => fileUpload.click());
    if (changeButton && fileUpload) changeButton.addEventListener('click', () => fileUpload.click());

    if (fileUpload && ppDiv){
        fileUpload.addEventListener('change', () => {
            const file = fileUpload.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => {
                    ppDiv.style.backgroundImage = `url(${e.target.result})`;
                    ppDiv.style.backgroundSize = "cover";
                    ppDiv.style.backgroundPosition = "center";
                };
                reader.readAsDataURL(file);
            }
        });
    }

    let player;

    function openModal(filmElement) {
        const data = filmElement.dataset;
        // si tes URLs sont absolues ça va; sinon tu peux passer une base: new URL(data.trailer, location.origin)
        const videoId = data.trailer ? new URL(data.trailer).pathname.split('/').pop() : null;

        const afficheContainer = modal.querySelector('.affiche');
        const detailsContainer = modal.querySelector('.details');

        afficheContainer.innerHTML =
            '<div id="player-container" style="width:100%; height:100%;"></div>' +
            '<div id="modal-actions" class="d-flex align-items-center gap-2"></div>';
        afficheContainer.style.backgroundImage = 'none';

        const actionsContainer   = modal.querySelector('#modal-actions');
        const titreModal         = detailsContainer.querySelector('.titre');
        const descriptionModal   = detailsContainer.querySelector('.description');
        const anneeModal         = detailsContainer.querySelector('#annee');
        const realisateurModal   = detailsContainer.querySelector('#realisateur');
        const auteurModal        = detailsContainer.querySelector('#auteur');

        // Scope sur le modal pour éviter collisions
        const genre_container = detailsContainer.querySelector('#container-genre');
        if (genre_container) genre_container.innerHTML = '';

        // Parse JSON en sécurité + gestion vide
        let genres = [];
        try { genres = JSON.parse(data.genres ?? '[]'); } catch {}
        if (Array.isArray(genres) && genre_container) {
            genres.forEach((genre, index) => {
                const genre_span = document.createElement("span");
                genre_span.classList.add(
                    'badge','rounded-pill','text-white','bg-secondary',
                    'text-decoration-none','py-2','px-2','fs-5','fw-normal'
                );
                genre_span.textContent = genre;
                genre_container.appendChild(genre_span);
                // console.log(index, genre);
            });
            if (!genres.length) {
                const span = document.createElement('span');
                span.classList.add('text-secondary', 'fst-italic');
                span.textContent = 'Aucun genre';
                genre_container.appendChild(span);
            }
        }

        titreModal.textContent       = data.titre || '';
        descriptionModal.textContent = data.description || '';
        anneeModal.textContent       = data.annee || '';
        realisateurModal.textContent = data.realisateur || '';
        auteurModal.textContent      = data.auteur || '';

        if (videoId) {
            if (player) player.destroy();
            player = new YT.Player('player-container', {
                height: '100%',
                width: '100%',
                videoId: videoId,
                playerVars: {
                    autoplay: 1, controls: 0, showinfo: 0, modestbranding: 1,
                    loop: 1, playlist: videoId, rel: 0
                },
                events: { onReady: onPlayerReady }
            });

            const playBtn = document.createElement('button');
            playBtn.id = 'play-pause-btn';
            playBtn.className = 'btn btn-light btn-lg d-flex align-items-center';
            playBtn.innerHTML = '<i class="bi bi-play-fill me-2"></i>Lecture';
            playBtn.addEventListener('click', () => {
                if (player && typeof player.getPlayerState === 'function') {
                    const state = player.getPlayerState();
                    if (state === YT.PlayerState.PLAYING) player.pauseVideo();
                    else player.playVideo();
                }
            });
            actionsContainer.appendChild(playBtn);

            const muteBtn = document.createElement('button');
            muteBtn.className = 'btn btn-outline-light rounded-circle';
            muteBtn.innerHTML = '<i class="bi bi-volume-mute-fill"></i>';
            muteBtn.addEventListener('click', () => {
                if (!player || typeof player.isMuted !== 'function') return;
                if (player.isMuted()) {
                    player.unMute();
                    muteBtn.innerHTML = '<i class="bi bi-volume-up-fill"></i>';
                } else {
                    player.mute();
                    muteBtn.innerHTML = '<i class="bi bi-volume-mute-fill"></i>';
                }
            });
            actionsContainer.appendChild(muteBtn);
        } else {
            afficheContainer.style.backgroundImage = `url('${data.image}')`;
        }

        modal.classList.add('active');
    }

    function onPlayerReady(event) {
        event.target.mute();
        event.target.playVideo();
        event.target.setPlaybackQuality('highres');
        event.target.getIframe().style.pointerEvents = 'none';
    }

    function closeModal() {
        modal.classList.remove('active');
        if (player) {
            player.stopVideo();
            player.destroy();
            player = null;
        }
    }

    films.forEach(film => {
        film.addEventListener('click', () => {
            openModal(film);
            current_film = film; // <-- clair et sans l’opérateur virgule
        });
    });

    modal.addEventListener('click', (e) => {
        if (e.target.classList.contains('more')) {
            closeModal();
        }
    });

    const closeModalBtn = modal.querySelector('.close-modal-btn');
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeModal);
    }

    if (form){
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            errorPseudo.classList.add('d-none');
            errorPwd.classList.add('d-none');
            infoRegister.classList.add('d-none');
            errorPseudo.textContent = 'Veuillez saisir un pseudo d\'au moins 3 caractères';

            let isValid = true;

            if (pseudoInput.value.length < 3 || pseudoInput.value.trim() === '') {
                errorPseudo.classList.remove('d-none');
                isValid = false;
            }

            if (newPasswordInput.value !== '' || confirmPasswordInput.value !== '') {
                if (newPasswordInput.value !== confirmPasswordInput.value) {
                    setTimeout(() => {
                        errorPwd.classList.remove('d-none');
                        isValid = false;
                    }, 800);
                }
            }

            if (newPasswordInput.value !== '') {
                const value = newPasswordInput.value.trim();
                const regexMaj = /[A-Z]/;
                const regexNum = /\d/;
                const regexSpec = /[!@#$%^&*(),.?":{}|<>]/;

                if (value.length < 8 || !regexMaj.test(value) || !regexNum.test(value) || !regexSpec.test(value)) {
                    setTimeout(() => {
                        errorPwd.innerHTML = `
                            • 8 caractères minimum<br>
                            • Au moins une majuscule<br>
                            • Au moins un chiffre<br>
                            • Au moins un caractère spécial
                        `;
                        errorPwd.classList.remove('d-none');
                    }, 600);
                    isValid = false;
                }
            }

            if (oldPasswordInput.value !== '' || newPasswordInput.value !== '' || confirmPasswordInput.value !== '') {
                if (oldPasswordInput.value === '' || newPasswordInput.value === '' || confirmPasswordInput.value === '') {
                    errorPwd.textContent = 'Veuillez remplir les 3 champs pour changer de mot de passe.';
                    errorPwd.classList.remove('d-none');
                    isValid = false;
                }
            }

            if (!isValid) return;

            const formData = new FormData(form);
            try {
                const response = await fetch('compte.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    setTimeout(() => {
                        infoRegister.textContent = result.message;
                        infoRegister.classList.remove('d-none');
                        setTimeout(() => {
                            window.location.href = "compte.php?mesinfos";
                        }, 800);
                    }, 600);

                    if (result.newAvatar) {
                        const avatar = document.querySelector('.user-avatar');
                        if (avatar) avatar.src = result.newAvatar;
                    }

                } else {
                    if (!result.pseudo) {
                        setTimeout(() => {
                            errorPwd.textContent = result.message;
                            errorPwd.classList.remove('d-none');
                            errorPwd.scrollIntoView({ behavior: "smooth", block: "center" }); // <-- fix: errorPwd (pas errorpwd)
                        }, 600);
                    } else {
                        setTimeout(() => {
                            errorPseudo.textContent = result.message;
                            errorPseudo.classList.remove('d-none');
                            errorPseudo.scrollIntoView({ behavior: "smooth", block: "center" });
                        }, 600);
                    }
                }
            } catch (error) {
                console.error('Erreur:', error);
                errorPwd.textContent = 'Une erreur est survenue. Veuillez réessayer.';
                errorPwd.classList.remove('d-none');
            }
        });
    }

    const shareBtn = document.getElementById('share');
    if (shareBtn) {
        shareBtn.addEventListener('click', () => {
            if (!current_film) return; // sécurité
            const params = new URLSearchParams({
                id_film: current_film.dataset.id,
                edit: '1'
            });
            window.location.href = "partager.php?" + params.toString();
        });
    }
</script>
</body>
</html>