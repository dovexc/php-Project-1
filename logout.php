<?php
// Abmelden – nur per Formular (POST mit CSRF-Token), damit fremde Links niemanden ausloggen können
require __DIR__ . '/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && validCsrf()) {
    $_SESSION = [];
    session_regenerate_id(true);
}

header('Location: index.php');
exit;
