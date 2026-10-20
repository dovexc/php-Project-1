<?php
// Up-/Downvote für einen Post. Nochmal auf denselben Pfeil klicken = Stimme zurücknehmen.
// Antwortet mit JSON, wenn per JavaScript (fetch) aufgerufen, sonst Weiterleitung zurück.
require __DIR__ . '/includes/init.php';

$wantsJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
$next      = safeRedirectTarget($_POST['next'] ?? 'index.php');
$postId    = (int) ($_POST['post_id'] ?? 0);

function respond(bool $wantsJson, int $status, array $data, string $location): never
{
    if ($wantsJson) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
    } else {
        header('Location: ' . $location);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond($wantsJson, 405, ['error' => 'Nur POST erlaubt.'], 'index.php');
}

if (!$user) {
    $login = 'login.php?next=' . urlencode($next);
    respond($wantsJson, 401, ['error' => 'Nicht eingeloggt.', 'login' => $login], $login);
}

if (!validCsrf()) {
    respond($wantsJson, 403, ['error' => 'Sitzung abgelaufen. Bitte Seite neu laden.'], $next);
}

$value = (int) ($_POST['value'] ?? 0);

$stmt = $pdo->prepare('SELECT id FROM posts WHERE id = ?');
$stmt->execute([$postId]);

if (!in_array($value, [1, -1], true) || !$stmt->fetch()) {
    respond($wantsJson, 400, ['error' => 'Ungültige Abstimmung.'], $next);
}

// Bisherige Stimme des Users
$stmt = $pdo->prepare('SELECT value FROM votes WHERE user_id = ? AND post_id = ?');
$stmt->execute([$user['id'], $postId]);
$current = $stmt->fetchColumn();

if ($current !== false && (int) $current === $value) {
    // Gleicher Pfeil nochmal: Stimme entfernen
    $stmt = $pdo->prepare('DELETE FROM votes WHERE user_id = ? AND post_id = ?');
    $stmt->execute([$user['id'], $postId]);
    $myVote = 0;
} else {
    // Neue Stimme oder Wechsel von Up zu Down (bzw. umgekehrt)
    $stmt = $pdo->prepare('INSERT INTO votes (user_id, post_id, value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)');
    $stmt->execute([$user['id'], $postId, $value]);
    $myVote = $value;
}

$stmt = $pdo->prepare('SELECT COALESCE(SUM(value), 0) FROM votes WHERE post_id = ?');
$stmt->execute([$postId]);
$score = (int) $stmt->fetchColumn();

respond($wantsJson, 200, ['score' => $score, 'myVote' => $myVote], $next . '#post-' . $postId);
