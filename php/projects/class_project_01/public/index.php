<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

startSession();

// Redirect to courses page (public) - users can browse without login
redirect('../courses/index.php');
?>
