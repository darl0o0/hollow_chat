<?php

//Initialization
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/session.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";
// requireStrictGet();
header('Content-Type: application/json; charset=utf-8');

$user_data = check_login($con);
$id = $user_data['id'];
$data = [];

// All revalent groups
$stmt_my_groups = mysqli_prepare($con, "
    select id from groups 
    where admin = ? or m1 = ? or m2 = ? or m3 = ? or m4 = ?
");
mysqli_stmt_bind_param($stmt_my_groups, "sssss", $id, $id, $id, $id, $id);
mysqli_stmt_execute($stmt_my_groups);
$result_my_groups = mysqli_stmt_get_result($stmt_my_groups);

$group_ids = [];
while($g = mysqli_fetch_assoc($result_my_groups)){
    $group_ids[] = $g['id'];
}
mysqli_stmt_close($stmt_my_groups);

//List of conversations
$group_placeholders = !empty($group_ids) ? implode(',', array_fill(0, count($group_ids), '?')) : 'null';
$query = "select * from conversations where (user1 = ? or user2 = ?) or (is_group = 1 and user1 in ($group_placeholders))";

$stmt_conversations = mysqli_prepare($con, $query);
if(!empty($group_ids)){
    $types = "ss" . str_repeat('s', count($group_ids));
    $params = array_merge([$id, $id], $group_ids);
    mysqli_stmt_bind_param($stmt_conversations, $types, ...$params);
}
else{
    mysqli_stmt_bind_param($stmt_conversations, 'ss', $id, $id);
}
mysqli_stmt_execute($stmt_conversations);
$result_conversations = mysqli_stmt_get_result($stmt_conversations);

$conversations = mysqli_fetch_all($result_conversations, MYSQLI_ASSOC);
mysqli_stmt_close($stmt_conversations);

if(count($conversations) > 0){

    foreach($conversations as $conversation){

        // Select user
        $isGroup = filter_var($conversation['is_group'], FILTER_VALIDATE_BOOL);
        if($isGroup){
            $id_target = $conversation['user1'];
        }
        else{
            $id_target = ($conversation['user1'] == $id)? $conversation['user2'] : $conversation['user1'];
        }

        $stmt_user = mysqli_prepare($con, "select id, username, bio, img, online from users where id = ?");
        mysqli_stmt_bind_param($stmt_user, "s", $id_target);
        mysqli_stmt_execute($stmt_user);
        $result_user = mysqli_stmt_get_result($stmt_user);

        $row_user2 = mysqli_fetch_assoc($result_user);
        mysqli_stmt_close($stmt_user);

        $members = [];

        if($row_user2){

            // Last message
            $stmt_last_msg = mysqli_prepare($con, "select * from messages where (sid = ? and rid = ?) or (sid = ? and rid = ?) order by created_at desc limit 1");
            mysqli_stmt_bind_param($stmt_last_msg, "ssss", $id, $id_target, $id_target, $id);
            mysqli_stmt_execute($stmt_last_msg);
            $result_last_msg = mysqli_stmt_get_result($stmt_last_msg);

            $row_last_msg = mysqli_fetch_assoc($result_last_msg);
            mysqli_stmt_close($stmt_last_msg);

            $username = $row_user2['username'];
            $bio = $row_user2['bio'];
            $img = $row_user2['img'];
            $isOnline = filter_var($row_user2['online'], FILTER_VALIDATE_BOOL);

            $data[] = [
                'isGroup' => $isGroup,
                'id' => $id_target,
                'username' => $username,
                'bio' => $bio,
                'img' => $img,
                'isOnline' => $isOnline,
                'lastMsg' => [
                    'sid' => $row_last_msg['sid'],
                    'msg' => $row_last_msg['msg'],
                    'isRead' => filter_var($row_last_msg['isRead'], FILTER_VALIDATE_BOOL),
                    'time' => $row_last_msg['created_at']
                ]
            ];
        }
        elseif($isGroup){
            
            // Last message
            $stmt_last_msg = mysqli_prepare($con, "select * from messages where sid = ? or rid = ? order by created_at desc limit 1");
            mysqli_stmt_bind_param($stmt_last_msg, "ss", $id_target, $id_target);
            mysqli_stmt_execute($stmt_last_msg);
            $result_last_msg = mysqli_stmt_get_result($stmt_last_msg);

            $row_last_msg = mysqli_fetch_assoc($result_last_msg);
            mysqli_stmt_close($stmt_last_msg);

            // Select group
            $stmt_group = mysqli_prepare($con, "select * from groups where id = ?");
            mysqli_stmt_bind_param($stmt_group, "s", $id_target);
            mysqli_stmt_execute($stmt_group);
            $result_group = mysqli_stmt_get_result($stmt_group);

            $row_group = mysqli_fetch_all($result_group, MYSQLI_NUM);
            mysqli_stmt_close($stmt_group);

            $name = $row_group[0][1];
            $img = $row_group[0][7];

            for($i = 2; $i < 5; $i++){
                $members[] = $row_group[0][$i];
            }

            $data[] = [
                'isGroup' => $isGroup,
                'id' => $id_target,
                'name' => $name,
                'img' => $img,
                'members' => $members,
                'lastMsg' => [
                    'sid' => $row_last_msg['sid'],
                    'msg' => $row_last_msg['msg'],
                    'isRead' => filter_var($row_last_msg['isRead'], FILTER_VALIDATE_BOOL),
                    'time' => $row_last_msg['created_at']
                ]
            ];
        }
        else{
            $username = null;
            $bio = null;
            $img = 'pfp.jpg';
            $isOnline = null;
        }
        
        // List conversations
    }
}
else{
    $data = null;
}
echo json_encode([
    'status' => 'ok',
    'response' => $data
]);
die();