<?php
// Wird von jeder Seite als Erstes geladen: Datenbank, Session, eingeloggter User
require __DIR__ . '/../config/db.php';

// Session-Cookie: nicht per JavaScript lesbar, nicht bei fremden Seiten mitgeschickt
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

// Token gegen CSRF – jedes Formular schickt es mit
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

// Eingeloggter User aus der Session, null = nicht eingeloggt
$user = null;

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare('SELECT id, username AS name FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch() ?: null;
}

if ($user) {
    $user['avatar'] = null;   // Pfad zum Profilbild, null = Anfangsbuchstabe anzeigen

    // Foren, denen der User beigetreten ist (für Sidebar und Feed)
    $stmt = $pdo->prepare('
        SELECT f.name
        FROM forum_members m
        JOIN forums f ON f.id = m.forum_id
        WHERE m.user_id = ?
        ORDER BY f.name
    ');
    $stmt->execute([$user['id']]);
    $user['forums'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
} else {
    unset($_SESSION['user_id']);   // User wurde gelöscht
}

// Seiten, die nur für eingeloggte User sind: sonst zum Login, danach zurück hierher
function requireLogin(?array $user): void
{
    if (!$user) {
        $here = basename($_SERVER['SCRIPT_NAME']) . (empty($_SERVER['QUERY_STRING']) ? '' : '?' . $_SERVER['QUERY_STRING']);
        header('Location: login.php?next=' . urlencode($here));
        exit;
    }
}

// Nach dem Login nur auf eigene Seiten weiterleiten (z. B. "post.php?id=1"), nie auf fremde Domains
function safeRedirectTarget(string $next): string
{
    return preg_match('/^[a-z_]+\.php(\?[^\s]*)?$/', $next) ? $next : 'index.php';
}

// Prüft das CSRF-Token eines abgeschickten Formulars
function validCsrf(): bool
{
    return hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '');
}

// Posts laden, neueste zuerst. $where z. B. 'p.forum_id = ?'
// $userId: für wen my_vote geladen wird (1 = Upvote, -1 = Downvote, null = keine Stimme)
function getPosts(PDO $pdo, string $where, array $params, ?int $userId): array
{
    $stmt = $pdo->prepare("
        SELECT p.id, p.title, p.body, p.image_path, p.created_at,
               f.name AS forum, u.username AS author,
               COALESCE((SELECT SUM(v.value) FROM votes v WHERE v.post_id = p.id), 0) AS score,
               (SELECT v.value FROM votes v WHERE v.post_id = p.id AND v.user_id = ?) AS my_vote,
               (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comments
        FROM posts p
        JOIN forums f ON f.id = p.forum_id
        JOIN users u ON u.id = p.user_id
        WHERE $where
        ORDER BY p.created_at DESC
    ");
    $stmt->execute(array_merge([$userId], $params));
    return $stmt->fetchAll();
}

// Datum aus der DB schön anzeigen: 14.10.2026, 09:30
function formatDate(string $datetime): string
{
    return date('d.m.Y, H:i', strtotime($datetime));
}
