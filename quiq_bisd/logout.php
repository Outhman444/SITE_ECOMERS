<?php

setcookie("user_information", "", time() - 3600, "/");


header("Location: login.php");
exit();
?>
