<?php
// Bild-Upload für Posts prüfen und speichern

const IMAGE_MAX_BYTES = 5 * 1024 * 1024;   // 5 MB
const IMAGE_MAX_SIDE  = 6000;              // Pixel – größere Bilder brauchen zu viel Speicher beim Verarbeiten
const IMAGE_TYPES     = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
    'image/webp' => 'webp',
];

// Gibt [Pfad, Fehler] zurück. Kein Bild ausgewählt: [null, null]
function saveUploadedImage(array $file): array
{
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE || $file['size'] > IMAGE_MAX_BYTES) {
        return [null, 'Das Bild ist zu groß (maximal 5 MB).'];
    }
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        return [null, 'Das Bild konnte nicht hochgeladen werden. Bitte nochmal versuchen.'];
    }

    // Echten Dateityp am Inhalt erkennen – Dateiname und Browser-Angabe kann man fälschen
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $size = @getimagesize($file['tmp_name']);

    if (!isset(IMAGE_TYPES[$mime]) || $size === false) {
        return [null, 'Nur Bilder im Format JPG, PNG, GIF oder WebP sind erlaubt.'];
    }
    if ($size[0] > IMAGE_MAX_SIDE || $size[1] > IMAGE_MAX_SIDE) {
        return [null, 'Das Bild ist zu groß (maximal 6000 × 6000 Pixel).'];
    }

    // Zufälliger Dateiname – nie den Namen vom User übernehmen
    $relativePath = 'uploads/posts/' . bin2hex(random_bytes(16)) . '.' . IMAGE_TYPES[$mime];
    $target       = __DIR__ . '/../' . $relativePath;

    $saved = match ($mime) {
        'image/jpeg' => saveJpegWithoutMetadata($file['tmp_name'], $target),
        'image/png'  => savePngWithoutMetadata($file['tmp_name'], $target),
        default      => move_uploaded_file($file['tmp_name'], $target),   // GIF (Animation erhalten), WebP
    };

    if (!$saved) {
        return [null, 'Das Bild konnte nicht gespeichert werden.'];
    }

    return [$relativePath, null];
}

// JPG neu speichern: entfernt Metadaten (z. B. GPS-Standort von Handyfotos)
function saveJpegWithoutMetadata(string $source, string $target): bool
{
    $image = @imagecreatefromjpeg($source);
    if (!$image) {
        return false;
    }

    // Handyfotos sind oft nur per Metadaten gedreht – Drehung vorher ins Bild übernehmen
    $orientation = @exif_read_data($source)['Orientation'] ?? 1;
    $rotated = match ($orientation) {
        3       => imagerotate($image, 180, 0),
        6       => imagerotate($image, -90, 0),
        8       => imagerotate($image, 90, 0),
        default => $image,
    };

    return imagejpeg($rotated, $target, 85);
}

// PNG neu speichern, Transparenz bleibt erhalten
function savePngWithoutMetadata(string $source, string $target): bool
{
    $image = @imagecreatefrompng($source);
    if (!$image) {
        return false;
    }
    imagesavealpha($image, true);

    return imagepng($image, $target, 6);
}
