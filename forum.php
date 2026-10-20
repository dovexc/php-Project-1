<?php

require __DIR__ . '/includes/init.php';

$currentForum = $_GET['name'] ?? '';

$stmt = $pdo->prepare('SELECT id, name, description FROM forums WHERE name = ?');
$stmt->execute([$currentForum]);
$forumData = $stmt->fetch();

if (!$forumData) {
    http_response_code(404);
    $pageTitle = 'Forum nicht gefunden – Forensite';
    require __DIR__ . '/includes/header.php';
    ?>
    <div class="layout">
        <?php require __DIR__ . '/includes/sidebar.php'; ?>
        <main class="feed">
            <div class="empty">
                <h2>Forum nicht gefunden</h2>
                <p>Dieses Forum gibt es nicht.</p>
                <a href="forums.php" class="button">Foren entdecken</a>
            </div>
        </main>
    </div>
</body>
</html>
    <?php
    exit;
}

$currentForum = $forumData['name'];   // Schreibweise aus der DB übernehmen
$forumPosts  = getPosts($pdo, 'p.forum_id = ?', [$forumData['id']], $user['id'] ?? null);
$isMember    = $user && in_array($currentForum, $user['forums']);
$createLink  = 'create_post.php?forum=' . urlencode($currentForum);

$pageTitle = 'f/' . $currentForum . ' – Forensite';
require __DIR__ . '/includes/header.php';
?>
    <div class="layout">
        <?php require __DIR__ . '/includes/sidebar.php'; ?>

        <main class="feed">
            <section class="forum-header">
                <span class="forum-icon forum-icon-large"><?= htmlspecialchars(strtoupper($currentForum[0])) ?></span>
                <div class="forum-info">
                    <h1>f/<?= htmlspecialchars($currentForum) ?></h1>
                    <p class="meta"><?= htmlspecialchars($forumData['description'] ?? '') ?></p>
                </div>
                <div class="forum-actions">
                    <?php
                    $joinForumId  = $forumData['id'];
                    $joinIsMember = $isMember;
                    $joinNext     = 'forum.php?name=' . urlencode($currentForum);
                    require __DIR__ . '/includes/join_button.php';
                    ?>
                    <a href="<?= $createLink ?>" class="button">+ Post erstellen</a>
                </div>
            </section>

            <?php if (empty($forumPosts)): ?>
                <div class="empty">
                    <h2>Noch keine Posts</h2>
                    <p>Sei der Erste, der hier etwas postet.</p>
                    <a href="<?= $createLink ?>" class="button">+ Post erstellen</a>
                </div>
            <?php else: ?>
                <?php foreach ($forumPosts as $post): ?>
                    <?php require __DIR__ . '/includes/post_card.php'; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
