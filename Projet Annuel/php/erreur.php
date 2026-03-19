<?php unset($_SESSION['state']);
$code = $_GET['code'] ?? 100;
if (!is_numeric($code)) {
    $code = 100;
}
http_response_code($code) ?>