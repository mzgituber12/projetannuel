<?php
$api = getenv('API_URL') ?: 'http://localhost:9000';
?>
<script>window.API_BASE = <?php echo json_encode($api); ?>;</script>