<?php
// Forum beitreten oder verlassen (Formular aus includes/join_button.php)
require __DIR__ . '/includes/init.php';

$next = safeRedirectTarget($_POST['next'] ?? 'forums.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: forums.php');
    exit;
}

requireLogin($user);

if (validCsrf()) {
    $forumId = (int) ($_POST['forum_id'] ?? 0);
    $action  = $_POST['action'] ?? '';

    if ($action === 'join') {
        // INSERT IGNORE: kein Fehler, falls schon Mitglied; Fremdschlüssel verhindert unbekannte Foren
        $stmt = $pdo->prepare('INSERT IGNORE INTO forum_members (forum_id, user_id) SELECT id, ? FROM forums WHERE id = ?');
        $stmt->execute([$user['id'], $forumId]);
    } elseif ($action === 'leave') {
        $stmt = $pdo->prepare('DELETE FROM forum_members WHERE forum_id = ? AND user_id = ?');
        $stmt->execute([$forumId, $user['id']]);
    }
}

header('Location: ' . $next);
exit;
