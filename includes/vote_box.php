<?php
// Up-/Downvote-Buttons mit Score. Erwartet: $post, optional $voteNext (Seite nach dem Abstimmen)
$voteNext = $voteNext ?? basename($_SERVER['SCRIPT_NAME']) . (empty($_SERVER['QUERY_STRING']) ? '' : '?' . $_SERVER['QUERY_STRING']);
$myVote   = (int) ($post['my_vote'] ?? 0);
?>
<form method="post" action="vote.php" class="vote-form <?= $myVote === 1 ? 'voted-up' : ($myVote === -1 ? 'voted-down' : '') ?>">
    <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
    <input type="hidden" name="next" value="<?= htmlspecialchars($voteNext) ?>">
    <button type="submit" name="value" value="1" class="vote-up" title="<?= $myVote === 1 ? 'Upvote zurücknehmen' : 'Upvote' ?>" aria-label="Upvote" aria-pressed="<?= $myVote === 1 ? 'true' : 'false' ?>">▲</button>
    <span class="score"><?= $post['score'] ?></span>
    <button type="submit" name="value" value="-1" class="vote-down" title="<?= $myVote === -1 ? 'Downvote zurücknehmen' : 'Downvote' ?>" aria-label="Downvote" aria-pressed="<?= $myVote === -1 ? 'true' : 'false' ?>">▼</button>
</form>
<?php unset($voteNext); ?>
