<?php
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

function send_json(int $status, array $payload): void {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

set_error_handler(function ($severity, $message, $file, $line) {
    send_json(500, [
        'success' => false,
        'message' => 'Erreur PHP pendant l\'upload: ' . $message,
    ]);
});

if (!isset($_FILES['file'])) {
    send_json(400, ['success' => false, 'message' => 'Aucun fichier fourni']);
}

$file = $_FILES['file'];
$uploadType = isset($_POST['uploadType']) ? $_POST['uploadType'] : 'image';
$uploadType = preg_replace('/[^a-zA-Z0-9_-]/', '', strtolower($uploadType));
if ($uploadType === '') {
    $uploadType = 'image';
}

if ($file['error'] !== UPLOAD_ERR_OK) {
    $uploadErrors = [
        UPLOAD_ERR_INI_SIZE => 'Fichier trop volumineux (limite PHP upload_max_filesize)',
        UPLOAD_ERR_FORM_SIZE => 'Fichier trop volumineux (limite formulaire)',
        UPLOAD_ERR_PARTIAL => 'Upload partiel, veuillez reessayer',
        UPLOAD_ERR_NO_FILE => 'Aucun fichier fourni',
        UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant',
        UPLOAD_ERR_CANT_WRITE => 'Ecriture disque impossible',
        UPLOAD_ERR_EXTENSION => 'Upload bloqué par une extension PHP',
    ];

    $msg = $uploadErrors[$file['error']] ?? 'Erreur lors de l\'upload';
    send_json(400, ['success' => false, 'message' => $msg]);
}

if (!is_uploaded_file($file['tmp_name'])) {
    send_json(400, ['success' => false, 'message' => 'Fichier temporaire invalide']);
}

$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$originalExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($originalExtension, $allowedExtensions, true)) {
    send_json(400, ['success' => false, 'message' => 'Extension non autorisée']);
}

if ($file['size'] > 5 * 1024 * 1024) {
    send_json(400, ['success' => false, 'message' => 'Fichier trop volumineux (max 5MB)']);
}

$uploadDir = dirname(__FILE__) . '/upload/';
if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
    send_json(500, ['success' => false, 'message' => 'Impossible de créer le dossier upload']);
}

$safeExt = $originalExtension !== '' ? $originalExtension : 'jpg';
$fileName = $uploadType . '_' . time() . '.' . $safeExt;

$uploadPath = $uploadDir . $fileName;

$counter = 1;
$originalFileName = $fileName;
while (file_exists($uploadPath)) {
    $pathInfo = pathinfo($originalFileName);
    $base = $pathInfo['filename'] ?? ($uploadType . '_' . time());
    $ext = isset($pathInfo['extension']) && $pathInfo['extension'] !== '' ? ('.' . $pathInfo['extension']) : '';
    $fileName = $base . '_' . $counter . $ext;
    $uploadPath = $uploadDir . $fileName;
    $counter++;
}

if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
    send_json(200, ['success' => true, 'fileName' => $fileName]);
}

send_json(500, ['success' => false, 'message' => 'Impossible de sauvegarder le fichier']);
