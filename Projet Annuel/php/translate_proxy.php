<?php
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

function send_json(int $status, array $payload): void {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$mode = isset($_GET['mode']) ? strtolower(trim((string) $_GET['mode'])) : 'translate';

$base = getenv('LIBRETRANSLATE_INTERNAL_URL');
if (!is_string($base) || trim($base) === '') {
    $base = 'http://libretranslate:5000';
}
$base = rtrim($base, '/');

if ($mode === 'languages') {
    if ($method !== 'GET') {
        send_json(405, ['error' => 'Method not allowed']);
    }

    $ctx = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 10,
            'ignore_errors' => true,
            'header' => "Accept: application/json\r\n",
        ],
    ]);

    $raw = @file_get_contents($base . '/languages', false, $ctx);
    if ($raw === false) {
        send_json(502, ['error' => 'Translation service unreachable']);
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        send_json(502, ['error' => 'Invalid response from translation service']);
    }

    send_json(200, $decoded);
}

if ($method !== 'POST') {
    send_json(405, ['error' => 'Method not allowed']);
}

$rawBody = file_get_contents('php://input');
if ($rawBody === false || trim($rawBody) === '') {
    send_json(400, ['error' => 'Empty request body']);
}

$payload = json_decode($rawBody, true);
if (!is_array($payload)) {
    send_json(400, ['error' => 'Invalid JSON payload']);
}

$ctx = stream_context_create([
    'http' => [
        'method' => 'POST',
        'timeout' => 15,
        'ignore_errors' => true,
        'header' => "Content-Type: application/json\r\n",
        'content' => json_encode($payload, JSON_UNESCAPED_UNICODE),
    ],
]);

$raw = @file_get_contents($base . '/translate', false, $ctx);
if ($raw === false) {
    send_json(502, ['error' => 'Translation service unreachable']);
}

$decoded = json_decode($raw, true);
if (!is_array($decoded)) {
    send_json(502, ['error' => 'Invalid response from translation service']);
}

send_json(200, $decoded);
