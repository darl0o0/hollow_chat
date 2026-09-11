<?php
//Initialization
include $_SERVER['DOCUMENT_ROOT'] . "/register/index.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="/src/css/home.css">
    <link rel="stylesheet" href="/src/css/global.css">
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
    <?php echo $requests_model ?>
    <script>
        // Listen for real-time presence changes broadcasted by server.js
        socket.on('user_status_change', (data) => {
            const statusDot = document.querySelector(`.u-${data.userId} .online-status`);
            
            if (statusDot) {
                statusDot.style.visibility = data.isOnline ? "visible" : "hidden";
            }
        });

        // List users
        window.onload = async function(){
            const res = await UI.sendGet('/src/api/users/');
            res.response.forEach(user => {
                const bio = user.bio ? user.bio : "No Bio."
                const isOnline = eval(user.isOnline) ? 'visible' : "hidden"

                document.querySelector('.users-list').innerHTML += `
                    <a class='user-card u-${user.id}' href='profile?u=${user.id}'>
                        <div class='pfp'>
                            <img class='pfp-img' src='/src/assets/pfps/${user.img}'>
                            <div class='online-status' style='visibility: ${isOnline}'></div>
                        </div>
                        <div class='label-wrapper'>
                            <div class='username-label'>${user.username}</div>
                            <span class='sub-label'>${bio}</span>
                        </div>
                    </a>
                `;
            });
        }
    </script>
</body>
</html>