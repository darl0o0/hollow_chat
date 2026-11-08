<!DOCTYPE html>
<html>
<head>
    <title>Forbidden Page</title>
    <link rel="shortcut icon" href="/src/assets/logo/hollow_icon_light.png" type="image/x-icon">
    <script src="https://kit.fontawesome.com/b4672b084e.js" crossorigin="anonymous"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..600');
        *{
            font-family: "Roboto";
            height: 100%;
            box-sizing: border-box;
            padding: 0;
            margin: 0;
            outline: 0;
        }
        body{
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .background{
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-image: url("../assets/forbidden_wallpaper.png");
            background-repeat: no-repeat;
            background-size: 100% 100%;
            filter: blur(1px);
        }
        .container{
            font-weight: 400;
            color: #e9e4c8;
            height: max-content;
            text-align: center;
            margin-top: -50px;
            z-index: 1;
        }
        .logo img{
            max-width: 450px;
        }
        .br{
            width: 100%;
            height: 1px;
            background: #464646ff;
            margin: 10px 0;
        }
        .container h1{
            font-size: 25px;
            margin: 40px 0;
        }
        .container a{
            display: flex;
            width: 100%;
            padding: 15px;
            color: #e9e4c8;
            font-size: 20px;
            font-weight: 500;
            border: 2px solid #e9e4c8;
            border-radius: 7px;
            justify-content: center;
            cursor: pointer;
            text-decoration: none;
            transition: 0.05s;
        }
        .container a:hover{
            background: #e9e4c8;
            color: #161B37;
        }
        .container a:active{
            border: 2px solid #b7b39d;
            background: #b7b39d;
            color: #161B37;
        }
    </style>
</head>
<body>
    <div class="background"></div>
    <div class="container">
        <div class="logo">
            <img src="/src/assets/logo/light_logo_forbidden.png">
        </div>
        <div class="br"></div>
        <h1>Looks like you reached a forbidden page!</h1>
        <a href="/">Return to home page</a>
    </div>
</body>
</html>