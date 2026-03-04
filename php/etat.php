<?php session_start(); include 'api_config.php'; ?>
<script src="online.js"></script>
<script>
loginUser("online", localStorage.getItem('token')); 
</script>

<?php
$type = $_GET['type'];
$state = $_GET['state'];
$id = $_GET['id'];
$_SESSION['state'] = true;
?>

<script>
async function updateUserEvent(token) {
    const base = (window.API_BASE || 'http://localhost:9000');
    const response = await fetch(base + "/<?php echo $type; ?>/<?php echo $id; ?>", {
        method: "POST",
        headers: {"Content-Type": "application/json", "Token": token},
        body: JSON.stringify({state: "<?php echo $state; ?>"})
    });
    const data = await response.json();
    window.location.href = "catalogue.php?message=" + data.message;
}
updateUserEvent(localStorage.getItem('token'));
</script>