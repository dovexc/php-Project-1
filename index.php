<?php



$user = [
    'name'   => 'max',
    'avatar' => null,   
    'forums' => ['Programmieren', 'Gaming'],   
];

$posts = [
    
];


$feed = array_filter($posts, fn($post) => in_array($post['forum'], $user['forums']));
usort($feed, fn($a, $b) => strcmp($b['created'], $a['created']));
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forensite</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <a href="index.php" class="logo">Forensite</a>
        <a href="profile.php" class="avatar" title="Mein Profil">
            <?php if ($user['avatar']): ?>
                <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Profilbild">
            <?php else: ?>
                <?= htmlspecialchars(strtoupper($user['name'][0])) ?>
            <?php endif; ?>
        </a>
    </header>

    <main class="container">
        <?php if (empty($user['forums'])): ?>
            <div class="empty">
                <h2>Du bist noch keinem Forum beigetreten</h2>
                <p>Tritt Foren bei, um hier ihre neuesten Posts zu sehen.</p>
                <a href="forums.php" class="button">Durchsuche Foren</a>
            </div>
        <?php elseif (empty($feed)): ?>
            <div class="empty">
                <h2>Noch keine Posts</h2>
                <p>In deinen Foren wurde noch nichts gepostet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($feed as $post): ?>
                <article class="post">
                    <div class="score"><?= $post['score'] ?></div>
                    <div>
                        <p class="meta">f/<?= htmlspecialchars($post['forum']) ?> · von <?= htmlspecialchars($post['author']) ?></p>
                        <h2><?= htmlspecialchars($post['title']) ?></h2>
                        <p class="meta"><?= $post['comments'] ?> Kommentare</p>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</body>
</html>
