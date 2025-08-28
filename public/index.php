<?php   
    require_once '../includes/connexion.php';
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_COOKIE['remember_me'])) {
        list($token, $userId) = explode("-", $_COOKIE['remember_me'], 2);
        $stmt = $conn->prepare("SELECT id, pseudo,nom,prenom, email, avatar, statut FROM utilisateur WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION["user"] = [
                'id'     => $user['id'],
                'pseudo' => $user['pseudo'],
                'email'  => $user['email'],
                'avatar' => $user['avatar'],
                'statut' => $user['statut'],
                'nom'    => $user['nom'],
                'prenom' => $user['prenom']
            ];
            header("Location: accueil.php");
        } else {
            setcookie("remember_me", "", time() - 3600, "/");
            unset($_COOKIE['remember_me']);
            header("Location: login.php");
        }
    }
    else
       {
        header("Location: login.php");
        exit;
       }
       
