<?php
//Initialization
require_once $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";
getCsrfToken();
?>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta property="og:title" content="Hollow Chat" />
<meta property="og:description" content="A dark-themed real-time chat app inspired by Hollow Knight." />
<link rel="shortcut icon" href="/src/assets/logo/hollow_icon_light.png" type="image/x-icon">
<script src="https://kit.fontawesome.com/b4672b084e.js"  crossorigin="anonymous"></script>
<script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
<script type="module" src="/src/js/global.js"></script>