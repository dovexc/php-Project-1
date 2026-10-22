<?php
// Eigenen Post löschen (Formular auf post.php). Kommentare und Stimmen löscht die Datenbank mit (ON DELETE CASCADE).
require __DIR__ . '/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

requireLogin($user);

$postId = (int) ($_POST['post_id'] ?? 0);

if (!validCsrf()) {
    header('Location: post.php?id=' . $postId);
    exit;
}

// Nur der Autor darf löschen – die Prüfung steckt direkt im WHERE
$stmt = $pdo->prepare('
    SELECT p.image_path, f.name AS forum
    FROM posts p
    JOIN forums f ON f.id = p.forum_id
    WHERE p.id = ? AND p.user_id = ?
');
$stmt->execute([$postId, $user['id']]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: post.php?id=' . $postId);
    exit;
}

$stmt = $pdo->prepare('DELETE FROM posts WHERE id = ? AND user_id = ?');
$stmt->execute([$postId, $user['id']]);

// Hochgeladenes Bild mitlöschen (nur Dateien aus unserem Upload-Ordner)
if ($post['image_path'] && preg_match('#^uploads/posts/[a-f0-9]{32}\.(jpg|png|gif|webp)$#', $post['image_path'])) {
    @unlink(__DIR__ . '/' . $post['image_path']);
}

header('Location: forum.php?name=' . urlencode($post['forum']));
exit;
