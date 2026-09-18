<?php
//Initialization
require_once $_SERVER['DOCUMENT_ROOT'] . "/src/includes/main.php";
?>
<a class="logo" href="/">
    <img src="/src/assets/logo/light_logo.png">
</a>
<div class="panel">
    <a href="/" class="panel-action home-action">
        <i class="fa-solid fa-house"></i>
        <span>Home</span>
    </a>

    <a href="/inbox" class="panel-action inbox-action">
        <div class="icon-wrapper">        
            <i class="fa-solid fa-inbox"></i>
            <div class="num-of"><?php echo $num_of_unread_messages ?></div>
        </div>
        <span>Inbox</span>
    </a>

    <a class="panel-action goto-profile-mobile profile-action">
        <img>
    </a>

    <button class="panel-action notifications-action">
        <div class="icon-wrapper">        
            <i class="fa-solid fa-bell"></i>
            <div class="num-of"><?php echo $num_of_requests ?></div>
        </div>
        <span>Notifications</span>
    </button>
    
    <a href="/settings" class="panel-action settings-action"><i class="fa-solid fa-gear"></i><span>Settings</span></a>
    
    <a class="panel-action goto-profile-desktop profile-action">
        <div class="pfp">
            <img>
        </div>
        <div class="label-wrapper">
            <div class="username-label"></div>
            <span class="sub-label">My profile</span>
        </div>
    </a>
</div>