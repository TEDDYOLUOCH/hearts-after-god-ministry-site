<?php
session_start();
session_destroy();
    header('Location: /hearts-after-god-ministry-site/backend/users/login.php');
exit;
?> 