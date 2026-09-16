<?php
//Initialization
require_once $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";
?>

<meta charset="UTF-8">
<meta name="csrf-token" content="<?php echo getCsrfToken(); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="/src/assets/logo/hollow_icon_light.png" type="image/x-icon">
<script src="https://kit.fontawesome.com/b4672b084e.js" crossorigin="anonymous"></script>
<script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>