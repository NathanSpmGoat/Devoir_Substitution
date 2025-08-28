<?php
header("content-type: application/json");
require_once '../includes/connexion.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$response = [];

$infos = $_POST["infos"] ?? '';
$password = $_POST["password"] ?? '';

$stmt = $conn->prepare("SELECT id, pseudo,email,nom,prenom,radier,valide,password,avatar,statut FROM utilisateur WHERE email = ? OR pseudo = ?");
$stmt->execute([$infos, $infos]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(["success"=>false,"message"=>"Compte introuvable."]); exit;
  }
  if (!password_verify($password, $user['password'])) {
    echo json_encode(["success"=>false,"message"=>"Mot de passe incorrect."]); exit;
  }
  if ($user['radier'] == '1') {
    echo json_encode(["success"=>false,"message"=>"Compte radié."]); exit;
  }
  if ((int)$user['valide'] !== 1) {
    echo json_encode(["success"=>false,"message"=>"Compte pas encore validé."]); exit;
  }
  
    $time = 3600*24*365; // 365 jours
    $value=bin2hex(random_bytes(5))."-".$user['id'];
    setcookie(
        "remember_me",
        $value,
        [
            "expires" => time() + $time,
            "path" => "/",
            "secure" => true,
            "httponly" => true,
            "samesite" => "Strict"
        ]
    );

    $_SESSION["user"] = [
        'id'     => $user['id'],
        'pseudo' => $user['pseudo'],
        'email'  => $user['email'],
        'avatar' => $user['avatar'],
        'statut'   => $user['statut'],
        'nom'    => $user['nom'],
        'prenom' => $user['prenom']
    ];
    echo json_encode(["success"=>true,"message"=>"Connecté avec succès."]); exit;

?>