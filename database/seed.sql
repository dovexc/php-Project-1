-- Testdaten für die lokale Entwicklung
-- Einspielen: phpMyAdmin > forensite > Importieren, oder:
-- /Applications/XAMPP/xamppfiles/bin/mysql -uroot forensite < database/seed.sql
-- Passwort aller Test-User: passwort

INSERT INTO users (id, username, email, password_hash) VALUES
(1, 'max',  'max@example.com',  '$2y$10$TO940gufgPQV6iqr86ZyBOcf4f8BZ3GjI.JbxFSqDPjM9dOUUDmhK'),
(2, 'lisa', 'lisa@example.com', '$2y$10$TO940gufgPQV6iqr86ZyBOcf4f8BZ3GjI.JbxFSqDPjM9dOUUDmhK'),
(3, 'tom',  'tom@example.com',  '$2y$10$TO940gufgPQV6iqr86ZyBOcf4f8BZ3GjI.JbxFSqDPjM9dOUUDmhK');

INSERT INTO forums (id, name, description, created_by) VALUES
(1, 'Programmieren', 'Fragen und Tipps rund ums Coden.', 1),
(2, 'Gaming',        'Alles über Spiele, Konsolen und PC.', 2),
(3, 'Musik',         'Alben, Konzerte und Empfehlungen.', 3);

INSERT INTO forum_members (forum_id, user_id, role) VALUES
(1, 1, 'moderator'), (2, 1, 'member'),
(2, 2, 'moderator'), (1, 2, 'member'),
(3, 3, 'moderator');

INSERT INTO posts (id, forum_id, user_id, title, body, created_at) VALUES
(1, 1, 1, 'Wie fange ich mit PHP an?', 'Ich will eine eigene Website mit PHP bauen. Welche Grundlagen sollte ich zuerst lernen und welche Tutorials könnt ihr empfehlen?', NOW() - INTERVAL 5 HOUR),
(2, 2, 2, 'Welches Spiel spielt ihr gerade?', 'Ich suche was Neues zum Zocken. Was spielt ihr im Moment?', NOW() - INTERVAL 3 HOUR),
(3, 3, 3, 'Eure Lieblingsalben 2026', 'Welche Alben haben euch dieses Jahr am meisten begeistert?', NOW() - INTERVAL 1 DAY);

INSERT INTO comments (id, post_id, user_id, parent_id, body, created_at) VALUES
(1, 1, 2, NULL, 'Fang mit Variablen, Arrays und Schleifen an. Danach Formulare und $_POST.', NOW() - INTERVAL 270 MINUTE),
(2, 1, 1, 1,    'Danke! Und wann sollte ich mit Datenbanken anfangen?', NOW() - INTERVAL 250 MINUTE),
(3, 1, 2, 2,    'Sobald Formulare klappen. PDO mit Prepared Statements lernen.', NOW() - INTERVAL 225 MINUTE),
(4, 1, 3, NULL, 'Die offizielle Doku auf php.net ist richtig gut.', NOW() - INTERVAL 210 MINUTE),
(5, 2, 1, NULL, 'Gerade viel Minecraft mit Freunden.', NOW() - INTERVAL 150 MINUTE);

INSERT INTO votes (user_id, post_id, value) VALUES
(1, 2, 1), (2, 1, 1), (3, 1, 1), (3, 2, 1), (1, 3, 1);
