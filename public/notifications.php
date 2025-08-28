<?php
require_once '../includes/connexion.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_SESSION['user'])) {

    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user']['id'];


if (isset($_GET['action']) && $_GET['action'] === 'markRead') {
    try {
        $stmt = $conn->prepare("UPDATE notification SET lu = 1 WHERE id_utilisateur = ? AND lu = 0");
        $stmt->execute([$userId]);
        echo 'ok';
    } catch (Exception $e) {
        echo 'error';
    }
    exit;
}

// Récupérer toutes les notifications de l'utilisateur
try {
    $notifStmt = $conn->prepare(
        "SELECT n.id, n.id_film, n.id_auteur_action, n.type, n.lu, n.created_at, f.titre AS film_titre, u.pseudo AS auteur_action
         FROM notification n
         JOIN film f ON n.id_film = f.id
         JOIN utilisateur u ON n.id_auteur_action = u.id
         WHERE n.id_utilisateur = ?
         ORDER BY n.created_at DESC"
    );
    $notifStmt->execute([$userId]);
    $allNotifications = $notifStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $allNotifications = [];
}

?>
<?php include 'header.php'; ?>
<style>
/* Styles simples pour la page des notifications */
.notif-page {
    max-width: 600px;
    margin: 2rem auto;
    padding: 1rem;
    background-color: #1f1f1f;
    color: #fff;
    border-radius: 6px;
}
.notif-item {
    padding: 0.75rem;
    border-bottom: 1px solid #333;
    font-size: 0.95rem;
}
.notif-item:last-child {
    border-bottom: none;
}
.notif-item.unread {
    background-color: #2a2a2a;
}
.notif-item .notif-time {
    font-size: 0.8rem;
    color: #999;
}
</style>

<div class="notif-page">
    <h2 class="mb-3">Vos notifications</h2>
    <?php if (empty($allNotifications)): ?>
        <p>Vous n'avez pas de notifications pour le moment.</p>
    <?php else: ?>
        <?php foreach ($allNotifications as $n): ?>
            <div class="notif-item <?=($n['lu'] == 0 ? 'unread' : '')?>">
                <div>
                    <strong><?= htmlspecialchars($n['auteur_action']) ?></strong>
                    <?php if ($n['type'] === 'like'): ?>
                        a aimé votre film <em><?= htmlspecialchars($n['film_titre']) ?></em>.
                    <?php else: ?>
                        a réalisé une action sur votre film <em><?= htmlspecialchars($n['film_titre']) ?></em>.
                    <?php endif; ?>
                </div>
                <div class="notif-time"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($n['created_at']))) ?></div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>