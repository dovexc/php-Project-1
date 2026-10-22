<article class="post" id="post-<?= $post['id'] ?>">
    <?php require __DIR__ . '/vote_box.php'; ?>
    <div class="post-content">
        <p class="meta">
            <a href="forum.php?name=<?= urlencode($post['forum']) ?>" class="forum-link">f/<?= htmlspecialchars($post['forum']) ?></a>
            · von <a href="profile.php?name=<?= urlencode($post['author']) ?>" class="user-link"><?= htmlspecialchars($post['author']) ?></a> · <?= formatDate($post['created_at']) ?>
        </p>
        <h2><a href="post.php?id=<?= $post['id'] ?>"><?= htmlspecialchars($post['title']) ?></a></h2>
        <p class="meta"><a href="post.php?id=<?= $post['id'] ?>#comments"><?= $post['comments'] ?> Kommentare</a></p>
    </div>
    <?php if ($post['image_path']): ?>
        <a href="post.php?id=<?= $post['id'] ?>" class="post-thumb">
            <img src="<?= htmlspecialchars($post['image_path']) ?>" alt="" loading="lazy">
        </a>
    <?php endif; ?>
</article>
