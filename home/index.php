<?php
//Initialization
include $_SERVER['DOCUMENT_ROOT'] . "/register/index.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="/src/css/home.css">
    <script type="module" src="/src/js/init/home.js"></script>
    <title>Hollow Chat</title>
</head>
<body>
    <div class="main">
        <?php echo $panel ?>
        <div class="preview">
            <span class="header-title">Home</span>
            <div class="search-wrapper">
                <label for="search" class="search-icon">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </label>
                <input type="text" class="search" id="search" placeholder="Search user...">
            </div>
            <div class="users-list"></div>
        </div>
    </div>
</body>
</html>