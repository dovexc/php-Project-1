<?php
// Neuen Post erstellen, z. B. create_post.php?forum=Gaming (Forum ist dann vorausgewählt)
require __DIR__ . '/includes/init.php';
require __DIR__ . '/includes/image_upload.php';

requireLogin($user);

// Alle Foren für die Auswahl
$allForums = $pdo->query('SELECT id, name FROM forums ORDER BY name')->fetchAll();

$errors   = [];
$forumId  = null;
$title    = '';
$body     = '';

// Vorauswahl aus dem Link (?forum=Name)
foreach ($allForums as $forum) {
    if ($forum['name'] === ($_GET['forum'] ?? '')) {
        $forumId = $forum['id'];
    }
}

// Upload größer als post_max_size: PHP verwirft dann das komplette Formular
$tooLarge = $_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0;

if ($tooLarge) {
    $errors[] = 'Das Bild ist zu groß (maximal 5 MB).';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $forumId = (int) ($_POST['forum_id'] ?? 0);
    $title   = trim($_POST['title'] ?? '');
    $body    = trim($_POST['body'] ?? '');

    if (!validCsrf()) {
        $errors[] = 'Sitzung abgelaufen. Bitte Seite neu laden und nochmal versuchen.';
    }
    if (!in_array($forumId, array_column($allForums, 'id'), true)) {
        $errors[] = 'Bitte wähle ein Forum aus.';
    }
    if ($title === '') {
        $errors[] = 'Bitte gib einen Titel ein.';
    } elseif (mb_strlen($title) > 200) {
        $errors[] = 'Der Titel ist zu lang (maximal 200 Zeichen).';
    }
    if (mb_strlen($body) > 40000) {
        $errors[] = 'Der Text ist zu lang (maximal 40.000 Zeichen).';
    }

    // Bild erst speichern, wenn der Rest stimmt – sonst bleiben verwaiste Dateien liegen
    $imagePath = null;
    if (empty($errors)) {
        [$imagePath, $imageError] = saveUploadedImage($_FILES['image'] ?? ['error' => UPLOAD_ERR_NO_FILE]);
        if ($imageError) {
            $errors[] = $imageError;
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare('INSERT INTO posts (forum_id, user_id, title, body, image_path) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$forumId, $user['id'], $title, $body, $imagePath]);
        } catch (PDOException $e) {
            if ($imagePath) {
                unlink(__DIR__ . '/' . $imagePath);   // Speichern fehlgeschlagen: Bild wieder löschen
            }
            throw $e;
        }

        // Direkt zum neuen Post weiterleiten
        header('Location: post.php?id=' . $pdo->lastInsertId());
        exit;
    }
}

// Zurück-Link: zum gewählten Forum, sonst Startseite
$cancelLink = 'index.php';
foreach ($allForums as $forum) {
    if ($forum['id'] === $forumId) {
        $cancelLink   = 'forum.php?name=' . urlencode($forum['name']);
        $currentForum = $forum['name'];   // in der Sidebar hervorheben
    }
}

$pageTitle = 'Post erstellen – Forensite';
require __DIR__ . '/includes/header.php';
?>
    <div class="layout">
        <?php require __DIR__ . '/includes/sidebar.php'; ?>

        <main class="feed">
            <form method="post" enctype="multipart/form-data" class="card post-form">
                <h1>Post erstellen</h1>

                <?php foreach ($errors as $error): ?>
                    <p class="error"><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>

                <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">

                <label for="forum_id">Forum</label>
                <select name="forum_id" id="forum_id" required>
                    <option value="">Forum auswählen …</option>
                    <?php foreach ($allForums as $forum): ?>
                        <option value="<?= $forum['id'] ?>" <?= $forum['id'] === $forumId ? 'selected' : '' ?>>
                            f/<?= htmlspecialchars($forum['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="title">Titel</label>
                <input type="text" name="title" id="title" maxlength="200" required value="<?= htmlspecialchars($title) ?>">

                <label for="body">Text <span class="meta">(optional)</span></label>
                <textarea name="body" id="body" rows="8"><?= htmlspecialchars($body) ?></textarea>

                <label for="image">Bild <span class="meta">(optional, JPG/PNG/GIF/WebP, max. 5 MB)</span></label>
                <?php if (!empty($errors) && $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                    <p class="meta">Falls du ein Bild ausgewählt hattest: bitte erneut auswählen.</p>
                <?php endif; ?>
                <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/gif,image/webp">
                <img id="image-preview" class="image-preview" alt="Vorschau" hidden>

                <div class="form-actions">
                    <a href="<?= $cancelLink ?>" class="button button-outline">Abbrechen</a>
                    <button type="submit" class="button">Posten</button>
                </div>
            </form>
        </main>
    </div>
    <script src="assets/js/image_preview.js?v=<?= filemtime(__DIR__ . '/assets/js/image_preview.js') ?>" defer></script>
</body>
</html>
