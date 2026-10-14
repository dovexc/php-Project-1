<article class="post">
    <div class="score"><?= $post['score'] ?></div>
    <div>
        <p class="meta">
            <a href="forum.php?name=<?= urlencode($post['forum']) ?>" class="forum-link">f/<?= htmlspecialchars($post['forum']) ?></a>
            · von <?= htmlspecialchars($post['author']) ?> · <?= formatDate($post['created_at']) ?>
        </p>
        <h2><a href="post.php?id=<?= $post['id'] ?>"><?= htmlspecialchars($post['title']) ?></a></h2>
        <p class="meta"><a href="post.php?id=<?= $post['id'] ?>#comments"><?= $post['comments'] ?> Kommentare</a></p>
    </div>
</article>
