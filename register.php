<?php
// Registrieren: neues Konto anlegen und direkt einloggen
require __DIR__ . '/includes/init.php';

$next = safeRedirectTarget($_GET['next'] ?? $_POST['next'] ?? 'index.php');

if ($user) {
    header('Location: ' . $next);
    exit;
}

$errors   = [];
$username = '';
$email    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['password_confirm'] ?? '';

    if (!validCsrf()) {
        $errors[] = 'Sitzung abgelaufen. Bitte nochmal versuchen.';
    }
    if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
        $errors[] = 'Benutzername: 3–30 Zeichen, nur Buchstaben, Zahlen und _.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 255) {
        $errors[] = 'Bitte gib eine gültige E-Mail-Adresse ein.';
    }
    if (mb_strlen($password) < 8) {
        $errors[] = 'Das Passwort muss mindestens 8 Zeichen lang sein.';
    } elseif ($password !== $confirm) {
        $errors[] = 'Die Passwörter stimmen nicht überein.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT username, email FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        foreach ($stmt->fetchAll() as $existing) {
            if (strcasecmp($existing['username'], $username) === 0) {
                $errors[] = 'Dieser Benutzername ist schon vergeben.';
            }
            if (strcasecmp($existing['email'], $email) === 0) {
                $errors[] = 'Mit dieser E-Mail gibt es schon ein Konto.';
            }
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)');
        $stmt->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT)]);

        // Direkt einloggen
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $pdo->lastInsertId();

        header('Location: ' . $next);
        exit;
    }
}

$pageTitle = 'Registrieren – Forensite';
require __DIR__ . '/includes/header.php';
?>
    <main class="auth">
        <form method="post" class="card post-form">
            <h1>Registrieren</h1>

            <?php foreach ($errors as $error): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>

            <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
            <input type="hidden" name="next" value="<?= htmlspecialchars($next) ?>">

            <label for="username">Benutzername</label>
            <input type="text" name="username" id="username" required autofocus autocomplete="username"
                   minlength="3" maxlength="30" pattern="[A-Za-z0-9_]+" value="<?= htmlspecialchars($username) ?>">

            <label for="email">E-Mail</label>
            <input type="email" name="email" id="email" required autocomplete="email" maxlength="255" value="<?= htmlspecialchars($email) ?>">

            <label for="password">Passwort <span class="meta">(mindestens 8 Zeichen)</span></label>
            <input type="password" name="password" id="password" required minlength="8" autocomplete="new-password">

            <label for="password_confirm">Passwort wiederholen</label>
            <input type="password" name="password_confirm" id="password_confirm" required minlength="8" autocomplete="new-password">

            <div class="form-actions">
                <button type="submit" class="button">Konto erstellen</button>
            </div>

            <p class="meta auth-switch">Schon ein Konto? <a href="login.php?next=<?= urlencode($next) ?>">Anmelden</a></p>
        </form>
    </main>
</body>
</html>
