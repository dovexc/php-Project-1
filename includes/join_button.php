<?php
// Beitreten-/Verlassen-Button. Erwartet: $joinForumId, $joinIsMember, $joinNext (Seite nach dem Klick)
?>
<?php if (!$user): ?>
    <a href="login.php?next=<?= urlencode($joinNext) ?>" class="button button-outline">Beitreten</a>
<?php else: ?>
    <form method="post" action="membership.php" class="join-form">
        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
        <input type="hidden" name="forum_id" value="<?= $joinForumId ?>">
        <input type="hidden" name="next" value="<?= htmlspecialchars($joinNext) ?>">
        <?php if ($joinIsMember): ?>
            <button type="submit" name="action" value="leave" class="button button-joined">
                <span class="label-default">Beigetreten</span><span class="label-hover">Verlassen</span>
            </button>
        <?php else: ?>
            <button type="submit" name="action" value="join" class="button button-outline">Beitreten</button>
        <?php endif; ?>
    </form>
<?php endif; ?>
