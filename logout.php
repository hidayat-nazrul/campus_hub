<?php
session_start();

// Secure session handling (clearing all variables and destroying session)[cite: 11]
session_unset();
session_destroy();

// Redirect safely back to login/index page
header("Location: login.php");
exit;
?>