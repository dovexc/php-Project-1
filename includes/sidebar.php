<aside class="sidebar">
    <a href="forums.php" class="button">Foren entdecken</a>

    <?php if (!$user): ?>
        <p class="meta sidebar-hint">Melde dich an, um Foren beizutreten, zu posten und zu kommentieren.</p>
        <a href="login.php" class="button button-outline">Anmelden</a>
    <?php else: ?>
        <a href="create_forum.php" class="button button-outline">Forum erstellen</a>

        <h3>Meine Foren</h3>
        <?php if (empty($user['forums'])): ?>
            <p class="meta">Noch keinem Forum beigetreten.</p>
        <?php else: ?>
            <ul class="forum-list">
                <?php foreach ($user['forums'] as $forum): ?>
                    <li>
                        <a href="forum.php?name=<?= urlencode($forum) ?>" class="<?= ($currentForum ?? null) === $forum ? 'active' : '' ?>">
                            <span class="forum-icon"><?= htmlspecialchars(strtoupper($forum[0])) ?></span>
                            f/<?= htmlspecialchars($forum) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php endif; ?>
</aside>
