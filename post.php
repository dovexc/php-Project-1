<?php
// Beitragsseite: Post mit allen Kommentaren, z. B. post.php?id=1
require __DIR__ . '/includes/init.php';

$postId = (int) ($_GET['id'] ?? 0);
$posts  = getPosts($pdo, 'p.id = ?', [$postId], $user['id'] ?? null);
$post   = $posts[0] ?? null;

if (!$post) {
    http_response_code(404);
    $pageTitle = 'Beitrag nicht gefunden – Forensite';
    require __DIR__ . '/includes/header.php';
    ?>
    <div class="layout">
        <?php require __DIR__ . '/includes/sidebar.php'; ?>
        <main class="feed">
            <div class="empty">
                <h2>Beitrag nicht gefunden</h2>
                <p>Dieser Beitrag existiert nicht oder wurde gelöscht.</p>
                <a href="index.php" class="button">Zur Startseite</a>
            </div>
        </main>
    </div>
</body>
</html>
    <?php
    exit;
}

$error     = null;
$errorFor  = null;   
$oldBody   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireLogin($user);

    $body     = trim($_POST['body'] ?? '');
    $parentId = ($_POST['parent_id'] ?? '') !== '' ? (int) $_POST['parent_id'] : null;
    $errorFor = $parentId ?? 'post';

    if (!validCsrf()) {
        $error = 'Sitzung abgelaufen. Bitte Seite neu laden und nochmal versuchen.';
    } elseif ($body === '') {
        $error = 'Der Kommentar darf nicht leer sein.';
    } elseif (mb_strlen($body) > 10000) {
        $error = 'Der Kommentar ist zu lang (maximal 10.000 Zeichen).';
    } elseif ($parentId !== null) {
        // Antwort nur auf Kommentare, die zu diesem Post gehören
        $stmt = $pdo->prepare('SELECT id FROM comments WHERE id = ? AND post_id = ?');
        $stmt->execute([$parentId, $post['id']]);
        if (!$stmt->fetch()) {
            $error    = 'Der Kommentar, auf den du antworten willst, existiert nicht mehr.';
            $errorFor = 'post';  
        }
    }

    if ($error === null) {
        $stmt = $pdo->prepare('INSERT INTO comments (post_id, user_id, parent_id, body) VALUES (?, ?, ?, ?)');
        $stmt->execute([$post['id'], $user['id'], $parentId, $body]);

        header('Location: post.php?id=' . $post['id'] . '#comment-' . $pdo->lastInsertId());
        exit;
    }

    $oldBody = $body;
}

$stmt = $pdo->prepare('
    SELECT c.id, c.parent_id, c.body, c.created_at, u.username AS author
    FROM comments c
    JOIN users u ON u.id = c.user_id
    WHERE c.post_id = ?
    ORDER BY c.created_at
');
$stmt->execute([$post['id']]);

$children = [];   // parent_id => Liste der Antworten ('' = direkt auf den Post)
foreach ($stmt->fetchAll() as $comment) {
    $children[$comment['parent_id'] ?? ''][] = $comment;
}

// Formular für Kommentar bzw. Antwort
function commentForm(int $postId, ?int $parentId, string $csrf, ?string $error, string $oldBody): void
{
    ?>
    <form method="post" action="post.php?id=<?= $postId ?><?= $parentId ? '#comment-' . $parentId : '#comments' ?>" class="comment-form">
        <input type="hidden" name="csrf" value="<?= $csrf ?>">
        <input type="hidden" name="parent_id" value="<?= $parentId ?? '' ?>">
        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <textarea name="body" rows="3" placeholder="<?= $parentId ? 'Deine Antwort …' : 'Was denkst du?' ?>" required><?= htmlspecialchars($oldBody) ?></textarea>
        <button type="submit" class="button"><?= $parentId ? 'Antworten' : 'Kommentieren' ?></button>
    </form>
    <?php
}

// $loggedIn = false: keine Antworten-Formulare anzeigen
function renderComments(array $children, string $parentKey, int $postId, string $csrf, ?string $error, $errorFor, string $oldBody, bool $loggedIn): void
{
    foreach ($children[$parentKey] ?? [] as $comment) {
        $hasError = $errorFor === $comment['id'];
        ?>
        <div class="comment" id="comment-<?= $comment['id'] ?>">
            <p class="meta">
                <strong><?= htmlspecialchars($comment['author']) ?></strong> · <?= formatDate($comment['created_at']) ?>
            </p>
            <p class="comment-body"><?= nl2br(htmlspecialchars($comment['body'])) ?></p>

            <?php if ($loggedIn): ?>
                <details class="reply" <?= $hasError ? 'open' : '' ?>>
                    <summary>Antworten</summary>
                    <?php commentForm($postId, $comment['id'], $csrf, $hasError ? $error : null, $hasError ? $oldBody : ''); ?>
                </details>
            <?php endif; ?>

            <?php if (!empty($children[$comment['id']])): ?>
                <div class="replies">
                    <?php renderComments($children, (string) $comment['id'], $postId, $csrf, $error, $errorFor, $oldBody, $loggedIn); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}

$currentForum = $post['forum'];
$pageTitle    = $post['title'] . ' – Forensite';
require __DIR__ . '/includes/header.php';
?>
    <div class="layout">
        <?php require __DIR__ . '/includes/sidebar.php'; ?>

        <main class="feed">
            <article class="post post-full" id="post-<?= $post['id'] ?>">
                <?php $voteNext = 'post.php?id=' . $post['id']; require __DIR__ . '/includes/vote_box.php'; ?>
                <div class="post-content">
                    <p class="meta">
                        <a href="forum.php?name=<?= urlencode($post['forum']) ?>" class="forum-link">f/<?= htmlspecialchars($post['forum']) ?></a>
                        · von <?= htmlspecialchars($post['author']) ?> · <?= formatDate($post['created_at']) ?>
                    </p>
                    <h1><?= htmlspecialchars($post['title']) ?></h1>
                    <?php if ($post['image_path']): ?>
                        <a href="<?= htmlspecialchars($post['image_path']) ?>" target="_blank" class="post-image">
                            <img src="<?= htmlspecialchars($post['image_path']) ?>" alt="Bild zu: <?= htmlspecialchars($post['title']) ?>">
                        </a>
                    <?php endif; ?>
                    <div class="post-body"><?= nl2br(htmlspecialchars($post['body'])) ?></div>
                </div>
            </article>

            <section class="comments" id="comments">
                <h3><?= $post['comments'] ?> Kommentare</h3>

                <?php if ($user): ?>
                    <?php commentForm($post['id'], null, $_SESSION['csrf'], $errorFor === 'post' ? $error : null, $errorFor === 'post' ? $oldBody : ''); ?>
                <?php else: ?>
                    <p class="login-hint">
                        <a href="login.php?next=<?= urlencode('post.php?id=' . $post['id']) ?>">Melde dich an</a>, um zu kommentieren und zu antworten.
                    </p>
                <?php endif; ?>

                <?php if (empty($children[''])): ?>
                    <p class="meta">Noch keine Kommentare. Schreib den ersten!</p>
                <?php else: ?>
                    <?php renderComments($children, '', $post['id'], $_SESSION['csrf'], $error, $errorFor, $oldBody, $user !== null); ?>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
