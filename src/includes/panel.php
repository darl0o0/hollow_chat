<?php
//Initialization
require_once $_SERVER['DOCUMENT_ROOT'] . "/src/includes/main.php";
?>
<a class="logo" href="/">
    <img class="asset" src="/src/assets/logo/light_logo.png">
    <img class="asset" src="/src/assets/logo/hollow_icon_light.png">
</a>
<div class="panel">
    <div class="wrap-search">
        <label for="search" class="search-icon">
            <i class="fa-solid fa-magnifying-glass"></i>
        </label>
        <input type="text" class="search" placeholder="Search">
    </div>
    <a href="/" class="panel-action home-action">
        <i class="fa-solid fa-house"></i>
        <span>Home</span>
    </a>

    <a href="/inbox/" class="panel-action inbox-action">
        <div class="wrap-icon-count">        
            <i class="fa-solid fa-inbox"></i>
            <div class="count display-none"></div>
        </div>
        <span>Inbox</span>
    </a>

    <a class="panel-action goto-profile-mobile profile-action wrap-pfp">
        <img>
    </a>

    <button class="panel-action notifications-action modal-show" link-modal="modal-requests">
        <div class="wrap-icon-count">        
            <i class="fa-solid fa-bell"></i>
            <div class="count display-none"></div>
        </div>
        <span>Notifications</span>
    </button>
    
    <a href="/settings/" class="panel-action settings-action"><i class="fa-solid fa-gear"></i><span>Settings</span></a>
    
    <a class="panel-action goto-profile-desktop profile-action">
        <div class="wrap-pfp">
            <img>
        </div>
        <div class="wrap-label">
            <div class="username-label"></div>
            <span class="sub-label">My profile</span>
        </div>
    </a>
</div>