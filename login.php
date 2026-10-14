<?php
// Anmelden mit Benutzername oder E-Mail und Passwort
require __DIR__ . '/includes/init.php';

$next = safeRedirectTarget($_GET['next'] ?? $_POST['next'] ?? 'index.php');

// Schon eingeloggt? Dann direkt weiter
if ($user) {
    header('Location: ' . $next);
    exit;
}

$error = null;
$login = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login    = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!validCsrf()) {
        $error = 'Sitzung abgelaufen. Bitte nochmal versuchen.';
    } else {
        $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$login, $login]);
        $found = $stmt->fetch();

        if ($found && password_verify($password, $found['password_hash'])) {
            // Neue Session-ID nach dem Login, damit eine alte ID nicht übernommen werden kann
            session_regenerate_id(true);
            $_SESSION['user_id'] = $found['id'];

            header('Location: ' . $next);
            exit;
        }

        // Absichtlich ungenau: nicht verraten, ob es den User gibt
        $error = 'Benutzername/E-Mail oder Passwort ist falsch.';
    }
}

$pageTitle = 'Anmelden – Forensite';
require __DIR__ . '/includes/header.php';
?>
    <main class="auth">
        <form method="post" class="card post-form">
            <h1>Anmelden</h1>

            <?php if ($error): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
            <input type="hidden" name="next" value="<?= htmlspecialchars($next) ?>">

            <label for="login">Benutzername oder E-Mail</label>
            <input type="text" name="login" id="login" required autofocus autocomplete="username" value="<?= htmlspecialchars($login) ?>">

            <label for="password">Passwort</label>
            <input type="password" name="password" id="password" required autocomplete="current-password">

            <div class="form-actions">
                <button type="submit" class="button">Anmelden</button>
            </div>

            <p class="meta auth-switch">Noch kein Konto? <a href="register.php?next=<?= urlencode($next) ?>">Jetzt registrieren</a></p>
        </form>
    </main>
</body>
</html>
