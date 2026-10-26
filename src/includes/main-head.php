<?php
//Initialization
require_once $_SERVER['DOCUMENT_ROOT'] . "/src/includes/main.php";
?>

<meta name="num-of-unread" content="<?php echo htmlspecialchars($num_of_unread_messages, ENT_QUOTES, 'UTF-8'); ?>">
<script type="module" src="/src/js/init/main.js"></script>
<script type="module" src="/src/js/presence.js"></script>