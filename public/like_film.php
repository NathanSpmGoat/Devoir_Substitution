<?php

require_once '../includes/connexion.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');


if (!isset($_SESSION['user'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vous devez être connecté pour aimer ce film.'
    ]);
    exit;
}

$filmId = isset($_POST['film_id']) ? intval($_POST['film_id']) : 0;
if ($filmId <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Film invalide.'
    ]);
    exit;
}
$userId = $_SESSION['user']['id'];

try {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM film_like WHERE id_film = ? AND id_utilisateur = ?");
    $stmt->execute([$filmId, $userId]);
    $alreadyLiked = $stmt->fetchColumn() > 0;
    if ($alreadyLiked) {
        // Déliker
        $delStmt = $conn->prepare("DELETE FROM film_like WHERE id_film = ? AND id_utilisateur = ?");
        $delStmt->execute([$filmId, $userId]);
        $liked = false;
    } else {
        // Liker
        $insStmt = $conn->prepare("INSERT INTO film_like (id_film, id_utilisateur) VALUES (?, ?)");
        $insStmt->execute([$filmId, $userId]);
        $liked = true;

        try {

            $authorStmt = $conn->prepare("SELECT id_utilisateur FROM film WHERE id = ?");
            $authorStmt->execute([$filmId]);
            $filmAuthorId = $authorStmt->fetchColumn();
            if ($filmAuthorId && $filmAuthorId != $userId) {
                $notifStmt = $conn->prepare("INSERT INTO notification (id_utilisateur, id_film, id_auteur_action, type, lu) VALUES (?, ?, ?, 'like', 0)");
                $notifStmt->execute([$filmAuthorId, $filmId, $userId]);
            }
        } catch (Exception $notfErr) {
            
        }
    }

    $countStmt = $conn->prepare("SELECT COUNT(*) FROM film_like WHERE id_film = ?");
    $countStmt->execute([$filmId]);
    $count = $countStmt->fetchColumn();
    echo json_encode([
        'success' => true,
        'liked'   => $liked,
        'count'   => $count
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Une erreur est survenue.'.$e->getMessage()
    ]);
}
exit;