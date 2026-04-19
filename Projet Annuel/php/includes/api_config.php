<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$api = getenv('API_URL') ?: 'http://localhost:9000';
$oneSignalAppID = getenv('ONESIGNAL_APP_ID') ?: '';
?>
<script>window.API_BASE = <?php echo json_encode($api); ?>;</script>
<script>window.ONESIGNAL_APP_ID = <?php echo json_encode($oneSignalAppID); ?>;</script>