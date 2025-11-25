<?php
require_once __DIR__ . '/config.php';
// logout and redirect
session_unset();
session_destroy();
header('Location: index.php');
exit;
?>
