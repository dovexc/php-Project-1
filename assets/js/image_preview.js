// Vorschau des ausgewählten Bildes im Formular "Post erstellen"
const input = document.getElementById('image');
const preview = document.getElementById('image-preview');

input.addEventListener('change', () => {
    const file = input.files[0];
    if (preview.src) {
        URL.revokeObjectURL(preview.src);
    }
    if (file) {
        preview.src = URL.createObjectURL(file);
        preview.hidden = false;
    } else {
        preview.removeAttribute('src');
        preview.hidden = true;
    }
});
