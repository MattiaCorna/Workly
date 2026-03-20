<?php
session_start();
session_destroy();
header("Location: /SITO/BPIC/login.php");
exit;
?>