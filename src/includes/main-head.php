<?php
//Initialization
require_once $_SERVER['DOCUMENT_ROOT'] . "/src/includes/main.php";
?>

<meta name="num-of-unread" content="<?php echo $num_of_unread_messages; ?>">
<script type="module" src="/src/js/init/main.js"></script>
<script type="module" src="/src/js/presence.js"></script>