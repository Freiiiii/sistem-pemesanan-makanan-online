<?php
require_once 'includes/config.php';
session_destroy(); // stop session
header('Location: login.php'); // redirect to login page
exit();
