<?php
// Startseite: Posts nach Votes aus den Foren, denen der User beigetreten ist
// Nicht eingeloggt: Posts nach Votes aus allen Foren
require __DIR__ . '/includes/init.php';

if ($user) {
    $feed = getPosts($pdo, 'p.forum_id IN (SELECT forum_id FROM forum_members WHERE user_id = ?)', [$user['id']], $user['id']);
} else {
    $feed = getPosts($pdo, '1 = 1', [], null);
}

require __DIR__ . '/includes/header.php';
?>
    <div class="layout">
        <?php require __DIR__ . '/includes/sidebar.php'; ?>

        <main class="feed">
            <?php if (!$user): ?>
                <div class="card welcome">
                    <h2>Willkommen bei Forensite</h2>
                    <p class="meta">Melde dich an, um Foren beizutreten und hier nur deren Posts zu sehen.</p>
                    <a href="register.php" class="button">Registrieren</a>
                    <a href="login.php" class="button button-outline">Anmelden</a>
                </div>
                <?php foreach ($feed as $post): ?>
                    <?php require __DIR__ . '/includes/post_card.php'; ?>
                <?php endforeach; ?>
            <?php elseif (empty($user['forums'])): ?>
                <div class="empty">
                    <h2>Du bist noch keinem Forum beigetreten</h2>
                    <p>Tritt Foren bei, um hier ihre neuesten Posts zu sehen.</p>
                    <a href="forums.php" class="button">Durchsuche Foren</a>
                    <a href="create_forum.php" class="button button-outline">Forum erstellen</a>
                </div>
            <?php elseif (empty($feed)): ?>
                <div class="empty">
                    <h2>Noch keine Posts</h2>
                    <p>In deinen Foren wurde noch nichts gepostet.</p>
                    <a href="forums.php" class="button">Weitere Foren finden</a>
                    <a href="create_forum.php" class="button button-outline">Forum erstellen</a>
                </div>
            <?php else: ?>
                <?php foreach ($feed as $post): ?>
                    <?php require __DIR__ . '/includes/post_card.php'; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
