<?php
require_once 'includes/db.php';

// Clear the session and send the user back home.
$_SESSION = [];
session_destroy();

header('Location: index.php');
exit;
