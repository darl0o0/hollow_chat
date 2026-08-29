<?php
//Starting session and definition part
session_start();
include("connection.php");
include("functions.php");
$user_data = check_login($con);
$isError = "none";
$error = "";

//Listening to post request
if(isset($_SESSION['user'])){
    if($_SERVER['REQUEST_METHOD'] == "POST"){
    
        //Uploading a profile picture
        $raw_img = $_FILES['img'];
        $file_name = $raw_img['name'];
        $tmp_name = $raw_img['tmp_name'];
        $file_dir = "assets/pfps/".$file_name;
        move_uploaded_file($tmp_name, $file_dir);
    
        //Database insert parameters
        $username = $_POST['username'];
        $img = $raw_img['name'];
        $email = $user_data['email'];
    
        //Checking credentials
        if(!empty($username)){
            if(empty($img)){
                $img = $user_data['img'];
            }
            $query = "update users set username = '$username', img = '$img' where email = '$email'";
            mysqli_query($con, $query);
            $_SESSION['user'] = $username;
            header("Location: ./");
            die;
        }
    }
}
else{
    header("Location: ./");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit your profile</title>
    <script src="https://kit.fontawesome.com/b4672b084e.js" crossorigin="anonymous"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..600');
        *{
            font-family: "Roboto";
        }
        .container{
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 40%;
            padding: 10px 0px 10px 0px;
            border-radius: 10px;
            font-weight: 400;
        }
        .container h1{
            font-weight: bolder;
            font-size: 34px;
        }
        .container input, .container .img-input{
            margin: 10px 1px;
            padding: 10px 15px;
            border: 1px ridge #818181ff;
            width: calc(100% - 30px);
            font-size: 17px;
            border-radius: 1px;
        }
        .container form input:focus{
            outline: none;
        }
        .container .img-input{
            border: 1px dashed #1d1d1dff;
            cursor: pointer;
            color: #7a7a7aff;
            text-align: center;
            padding-right: 8px;
            padding-top: 8px;
            width: calc(100% - 23px);
        }
        .container .img-input img{
            margin-top: 8px;
        }
        .container .img-input .fa-x{
            font-size: 12px;
            color: #666666ff;
            float: right;
            z-index: 1;
        }
        .container .img-input .fa-image{
            font-size: 40px;
            margin-top: 20px;
        }
        .pfp{
            display: none;
        }
        .container form input:last-child{
            width: 100%;
            margin-top: 25px;
            border: none;
            background-color: #0061FF;
            color: #fff;
            cursor: pointer;
            padding: 10px 0px;
        }
        .container p a{
            color: #0061FF;
        }
        .container p{
            font-size: 18px;
        }
        .container a{
            display: flex;
            justify-content: center;
            margin: 10px 1px;
            padding: 5px 10px;
            border: 1px ridge rgb(129, 129, 129);
            width: 100%;
            font-size: 17px;
            border-radius: 1px;
            background-color: #fff;
            color: #000;
            text-decoration: none;
            cursor: pointer;
            padding: 10px 0px;
        }
        .error{
            text-align: center;
            background-color: #f9b5bd;
            color: #861B26;
            padding: 10px 5px;
            margin-bottom: 10px;
            border-radius: 1px;
            width: calc(100% - 7px);
            border: 1px ridge #c46f7c;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form" >
            <h1>Edit your profile</h1>
            <div class="error" style="display: <?php echo $isError; ?>"><?php echo "$error"; ?></div>
            <form method="post" enctype="multipart/form-data">
                <label>Username:</label>
                <input type="username" class="username-input" name="username" placeholder="Username" value="<?php echo $user_data['username']; ?>">
                <label>Profile picture:</label>
                <div class="img-input">
                    <i class="fa-solid fa-x"></i>
                    <img src='./assets/pfps/<?php echo $user_data['img']; ?>' style='object-fit: cover;width: 100px;height: 100px;'>
                </div>
                <input type="file" name="img" class="pfp" accept="image/jpg, image/jpeg, image/png">
                <input type="submit" class="submit" value="Update">
            </form>
            <a href="./">Cancel</a>
        </div>
    </div>
    <script>
        document.querySelector('.submit').addEventListener('click', (event)=>{
            //If the inputs are empty
            if(eval(document.querySelector('.email-input').value.length*document.querySelector('.password').value.length*document.querySelector('.username-input').value.length) == 0){
                event.preventDefault();
                document.querySelector('.error').style.display = "block";
                document.querySelector('.error').innerHTML = "All inputs are required";
            }
        });
        //To click on the drag and drop field instead of the file input
        document.querySelector(".img-input").addEventListener("click", (e)=>{
            if(document.querySelector('.img-input i').contains(e.target)){
                document.querySelector(".img-input").innerHTML = "Choose a profile picture<br><i class='fa-solid fa-image'></i>";
                document.querySelector(".pfp").value = "";
            }
            else{
                document.querySelector(".pfp").click();
            }
        });
        document.querySelector(".pfp").onchange = function (){
            document.querySelector(".img-input").innerHTML = "<i class='fa-solid fa-x'></i><img src='"+URL.createObjectURL(document.querySelector(".pfp").files[0])+"' style='object-fit: cover;width: 100px;height: 100px;'>";
        }
    </script>
</body>
</html>