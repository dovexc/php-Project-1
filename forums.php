<?php
// Foren entdecken: alle Foren durchsuchen, beitreten und verlassen
require __DIR__ . '/includes/init.php';

$search = trim($_GET['q'] ?? '');

// % und _ sind Platzhalter in LIKE – escapen, damit nach ihnen wörtlich gesucht wird
$like = '%' . addcslashes($search, '%_\\') . '%';

$stmt = $pdo->prepare('
    SELECT f.id, f.name, f.description,
           (SELECT COUNT(*) FROM forum_members m WHERE m.forum_id = f.id) AS members,
           (SELECT COUNT(*) FROM posts p WHERE p.forum_id = f.id) AS posts
    FROM forums f
    WHERE f.name LIKE ? OR f.description LIKE ?
    ORDER BY members DESC, f.name
');
$stmt->execute([$like, $like]);
$allForums = $stmt->fetchAll();

$here      = 'forums.php' . ($search !== '' ? '?q=' . urlencode($search) : '');
$pageTitle = 'Foren entdecken – Forensite';
require __DIR__ . '/includes/header.php';
?>
    <div class="layout">
        <?php require __DIR__ . '/includes/sidebar.php'; ?>

        <main class="feed">
            <div class="card discover-head">
                <h1>Foren entdecken</h1>
                <form method="get" class="search-form">
                    <input type="search" name="q" placeholder="Foren durchsuchen …" value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="button">Suchen</button>
                </form>
            </div>

            <?php if (empty($allForums)): ?>
                <div class="empty">
                    <h2>Keine Foren gefunden</h2>
                    <p>Zu „<?= htmlspecialchars($search) ?>“ gibt es noch kein Forum.</p>
                    <a href="create_forum.php" class="button">Forum erstellen</a>
                </div>
            <?php else: ?>
                <?php foreach ($allForums as $forum): ?>
                    <article class="forum-row">
                        <span class="forum-icon forum-icon-large"><?= htmlspecialchars(strtoupper($forum['name'][0])) ?></span>
                        <div class="forum-info">
                            <h2><a href="forum.php?name=<?= urlencode($forum['name']) ?>">f/<?= htmlspecialchars($forum['name']) ?></a></h2>
                            <p class="meta"><?= htmlspecialchars($forum['description'] ?? '') ?></p>
                            <p class="meta"><?= $forum['members'] ?> <?= $forum['members'] == 1 ? 'Mitglied' : 'Mitglieder' ?> · <?= $forum['posts'] ?> <?= $forum['posts'] == 1 ? 'Post' : 'Posts' ?></p>
                        </div>
                        <?php
                        $joinForumId  = $forum['id'];
                        $joinIsMember = $user && in_array($forum['name'], $user['forums']);
                        $joinNext     = $here;
                        require __DIR__ . '/includes/join_button.php';
                        ?>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
