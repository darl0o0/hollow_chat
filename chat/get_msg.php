<?php
session_start();
include("../connection.php");
include("../functions.php");
$user_data = check_login($con);
$id = $user_data['id'];
$messages = "";
if($_SERVER['REQUEST_METHOD'] == "POST"){
    $rid = $_POST['rid'];
    $query1 = "select * from messages where (sid = '$id' and rid = '$rid') or (sid = '$rid' and rid = '$id')";
    $num_of_msgs = mysqli_num_rows(mysqli_query($con, $query1));
    if($num_of_msgs > 0){
        $query = "select * from messages where (sid = '$id' and rid = '$rid') or (sid = '$rid' and rid = '$id')";
        $result = mysqli_fetch_all(mysqli_query($con, $query));
        for($i = 0; $i < mysqli_num_rows(mysqli_query($con, $query)); $i++){
            if($result[$i][0] == $id){
                $isRead = $result[$i][3]? '#e9e4c8':'none';
                $messages .= "<div class='msg-wrap'><div class='isRead unread' style='background: $isRead;'></div><div class='s m'>".$result[$i][2]."</div></div>";
            }
            elseif($result[$i][0] == $rid){
                $messages .= "<div class='r m'>".$result[$i][2]."</div>";
            }
        }
    }
    else{
        $messages = "<center>Start chatting now.</center>";
    }
}
echo $messages;