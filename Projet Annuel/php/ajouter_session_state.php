<?php session_start(); 
if (($_SERVER['REQUEST_METHOD'] !== 'POST')) {
    header("Location: index.php");
    exit;
}
$_SESSION['state'] = true; 
?>
