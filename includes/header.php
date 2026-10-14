<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Forensite') ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <a href="index.php" class="logo">Forensite</a>
        <nav class="header-nav">
            <?php if ($user): ?>
                <form method="post" action="logout.php">
                    <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                    <button type="submit" class="link-button">Abmelden</button>
                </form>
                <a href="profile.php" class="avatar" title="Mein Profil (<?= htmlspecialchars($user['name']) ?>)">
                    <?php if ($user['avatar']): ?>
                        <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Profilbild">
                    <?php else: ?>
                        <?= htmlspecialchars(strtoupper($user['name'][0])) ?>
                    <?php endif; ?>
                </a>
            <?php else: ?>
                <a href="login.php" class="button button-outline">Anmelden</a>
                <a href="register.php" class="button">Registrieren</a>
            <?php endif; ?>
        </nav>
    </header>
