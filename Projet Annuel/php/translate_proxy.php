<?php
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

function repondre(int $status, array $contenu): void {
    http_response_code($status);
    echo json_encode($contenu, JSON_UNESCAPED_UNICODE);
    exit;
}

function appeler_libretranslate(string $url, string $methode, array $corps = []): array {
    $options = [
        'method'        => $methode,
        'timeout'       => 30 ,
        'ignore_errors' => true,
        'header' => "Content-Type: application/json\r\nAccept: application/json\r\n",
    ];

    if (!empty($corps)) {
        $options['content'] = json_encode($corps, JSON_UNESCAPED_UNICODE);
    }

    $contexte = stream_context_create(['http' => $options]);
    $reponse  = file_get_contents($url, false, $contexte);

    if ($reponse == false) {
        repondre(502, ['error' => 'Translation service unreachable']);
    }

    $decode = json_decode($reponse, true);
    if (!is_array($decode)) {
        repondre(502, ['error' => 'Invalid response from translation service']);
    }

    return $decode;
}

$base = getenv('LIBRETRANSLATE_INTERNAL_URL');
if (!is_string($base) || trim($base) == '') {
    $base = 'http://libretranslate:5000';
}
$base = rtrim($base, '/');

$methode = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
$mode    = strtolower(trim((string) (isset($_GET['mode']) ? $_GET['mode'] : 'translate')));

if ($mode == 'languages') {
    if ($methode != 'GET') {
        repondre(405, ['error' => 'Method not allowed']);
    }
    repondre(200, appeler_libretranslate($base . '/languages', 'GET'));
}

if ($methode != 'POST') {
    repondre(405, ['error' => 'Method not allowed']);
}

$corpsBrut = file_get_contents('php://input');
if ($corpsBrut == false || trim($corpsBrut) == '') {
    repondre(400, ['error' => 'Empty request body']);
}

$payload = json_decode($corpsBrut, true);
if (!is_array($payload)) {
    repondre(400, ['error' => 'Invalid JSON payload']);
}

repondre(200, appeler_libretranslate($base . '/translate', 'POST', $payload));
