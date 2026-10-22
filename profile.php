<?php
// Profilseite: alle Posts eines Users, z. B. profile.php?name=max
// Ohne ?name= wird das eigene Profil angezeigt
require __DIR__ . '/includes/init.php';

$name = $_GET['name'] ?? '';

if ($name === '') {
    requireLogin($user);
    $name = $user['name'];
}

$stmt = $pdo->prepare('
    SELECT u.id, u.username, u.created_at,
           (SELECT COUNT(*) FROM comments c WHERE c.user_id = u.id) AS comment_count,
           (SELECT COALESCE(SUM(v.value), 0) FROM votes v JOIN posts p ON p.id = v.post_id WHERE p.user_id = u.id) AS karma
    FROM users u
    WHERE u.username = ?
');
$stmt->execute([$name]);
$profile = $stmt->fetch();

if (!$profile) {
    http_response_code(404);
    $pageTitle = 'Profil nicht gefunden – Forensite';
    require __DIR__ . '/includes/header.php';
    ?>
    <div class="layout">
        <?php require __DIR__ . '/includes/sidebar.php'; ?>
        <main class="feed">
            <div class="empty">
                <h2>Profil nicht gefunden</h2>
                <p>Diesen Benutzer gibt es nicht.</p>
                <a href="index.php" class="button">Zur Startseite</a>
            </div>
        </main>
    </div>
</body>
</html>
    <?php
    exit;
}

$profilePosts = getPosts($pdo, 'p.user_id = ?', [$profile['id']], $user['id'] ?? null);
$isOwnProfile = $user && $user['id'] === $profile['id'];

// In welchen Foren wie oft gepostet wurde, z. B. ['Gaming' => 3, 'Musik' => 1]
$postsPerForum = array_count_values(array_column($profilePosts, 'forum'));
arsort($postsPerForum);

$pageTitle = 'u/' . $profile['username'] . ' – Forensite';
require __DIR__ . '/includes/header.php';
?>
    <div class="layout">
        <?php require __DIR__ . '/includes/sidebar.php'; ?>

        <main class="feed">
            <section class="forum-header">
                <span class="avatar avatar-large"><?= htmlspecialchars(strtoupper($profile['username'][0])) ?></span>
                <div class="forum-info">
                    <h1>u/<?= htmlspecialchars($profile['username']) ?></h1>
                    <p class="meta">
                        Dabei seit <?= date('d.m.Y', strtotime($profile['created_at'])) ?>
                        · <?= count($profilePosts) ?> Posts
                        · <?= $profile['comment_count'] ?> Kommentare
                        · <?= $profile['karma'] ?> Punkte
                    </p>
                    <?php if ($postsPerForum): ?>
                        <p class="meta">
                            Postet in:
                            <?php foreach ($postsPerForum as $forum => $count): ?>
                                <a href="forum.php?name=<?= urlencode($forum) ?>" class="forum-link">f/<?= htmlspecialchars($forum) ?></a> (<?= $count ?>)
                            <?php endforeach; ?>
                        </p>
                    <?php endif; ?>
                </div>
            </section>

            <?php if (empty($profilePosts)): ?>
                <div class="empty">
                    <h2>Noch keine Posts</h2>
                    <?php if ($isOwnProfile): ?>
                        <p>Du hast noch nichts gepostet.</p>
                        <a href="forums.php" class="button">Foren entdecken</a>
                    <?php else: ?>
                        <p>u/<?= htmlspecialchars($profile['username']) ?> hat noch nichts gepostet.</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($profilePosts as $post): ?>
                    <?php require __DIR__ . '/includes/post_card.php'; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
