-- Bild zu Posts (optional). Einspielen:
-- /Applications/XAMPP/xamppfiles/bin/mysql -uroot forensite < database/migrations/001_post_image.sql
ALTER TABLE posts ADD COLUMN image_path VARCHAR(255) NULL AFTER body;
