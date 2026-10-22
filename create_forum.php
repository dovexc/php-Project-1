<?php
// Neues Forum erstellen. Der Ersteller tritt automatisch bei und wird Moderator.
require __DIR__ . '/includes/init.php';

requireLogin($user);

$errors      = [];
$name        = '';
$description = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (!validCsrf()) {
        $errors[] = 'Sitzung abgelaufen. Bitte Seite neu laden und nochmal versuchen.';
    }
    // Name steht in Links (forum.php?name=…) – daher nur Buchstaben, Ziffern und _
    if (!preg_match('/^[A-Za-z0-9_]{3,21}$/', $name)) {
        $errors[] = 'Der Name muss 3 bis 21 Zeichen lang sein und darf nur Buchstaben, Ziffern und _ enthalten.';
    }
    if ($description === '') {
        $errors[] = 'Bitte gib eine Beschreibung ein.';
    } elseif (mb_strlen($description) > 500) {
        $errors[] = 'Die Beschreibung ist zu lang (maximal 500 Zeichen).';
    }

    if (empty($errors)) {
        // Groß-/Kleinschreibung egal: "gaming" gibt es schon, wenn "Gaming" existiert
        $stmt = $pdo->prepare('SELECT id FROM forums WHERE name = ?');
        $stmt->execute([$name]);
        if ($stmt->fetch()) {
            $errors[] = 'Ein Forum mit diesem Namen gibt es schon.';
        }
    }

    if (empty($errors)) {
        // Forum anlegen und Ersteller als Moderator eintragen – beides oder nichts
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('INSERT INTO forums (name, description, created_by) VALUES (?, ?, ?)');
            $stmt->execute([$name, $description, $user['id']]);

            $stmt = $pdo->prepare("INSERT INTO forum_members (forum_id, user_id, role) VALUES (?, ?, 'moderator')");
            $stmt->execute([$pdo->lastInsertId(), $user['id']]);

            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            if ($e->getCode() !== '23000') {
                throw $e;
            }
            // 23000 = doppelter Name (jemand war gleichzeitig schneller)
            $errors[] = 'Ein Forum mit diesem Namen gibt es schon.';
        }
    }

    if (empty($errors)) {
        header('Location: forum.php?name=' . urlencode($name));
        exit;
    }
}

$pageTitle = 'Forum erstellen – Forensite';
require __DIR__ . '/includes/header.php';
?>
    <div class="layout">
        <?php require __DIR__ . '/includes/sidebar.php'; ?>

        <main class="feed">
            <form method="post" class="card post-form">
                <h1>Forum erstellen</h1>

                <?php foreach ($errors as $error): ?>
                    <p class="error"><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>

                <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">

                <label for="name">Name <span class="meta">(3–21 Zeichen: Buchstaben, Ziffern, _)</span></label>
                <input type="text" name="name" id="name" maxlength="21" pattern="[A-Za-z0-9_]{3,21}" required autofocus placeholder="z. B. Kochen" value="<?= htmlspecialchars($name) ?>">

                <label for="description">Beschreibung</label>
                <textarea name="description" id="description" rows="4" maxlength="500" required placeholder="Worum geht es in deinem Forum?"><?= htmlspecialchars($description) ?></textarea>

                <p class="meta">Du trittst dem Forum automatisch bei und wirst Moderator.</p>

                <div class="form-actions">
                    <a href="forums.php" class="button button-outline">Abbrechen</a>
                    <button type="submit" class="button">Forum erstellen</button>
                </div>
            </form>
        </main>
    </div>
</body>
</html>
