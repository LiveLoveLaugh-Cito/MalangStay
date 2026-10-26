<?php
session_start();
session_destroy();
header('Location: ../malangstay.php');
exit;
?>
