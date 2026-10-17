<?php
//Initialization
require_once $_SERVER['DOCUMENT_ROOT'] . "/src/includes/main.php";
header('Content-Type: application/json; charset=utf-8');

requireStrictPost();
$user_data = check_login($con);

$id = $user_data['id'];
$data = [];

$input = json_decode(file_get_contents('php://input'), true);
$q = $input['q'] ?? null;

if(is_array($q)){
    foreach($q as $qid){
        $query = "select id, username, img, online, bio, private, date from users where id = ?";
        $stmt_user = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt_user, 's', $qid);
        mysqli_stmt_execute($stmt_user);
        $result_user = mysqli_stmt_get_result($stmt_user);
    
        $row_user = mysqli_fetch_assoc($result_user);
        mysqli_stmt_close($stmt_user);

        $query1 = "select * from groups where id = ?";
        $stmt_group = mysqli_prepare($con, $query1);
        mysqli_stmt_bind_param($stmt_group, 's', $qid);
        mysqli_stmt_execute($stmt_group);
        $result_group = mysqli_stmt_get_result($stmt_group);
    
        $row_group = mysqli_fetch_assoc($result_group);
        mysqli_stmt_close($stmt_group);

        if($row_user){
            $data[] = [
                'isGroup' => false,
                'id' => $row_user['id'],
                'username' => $row_user['username'],
                'bio' => $row_user['bio'],
                'img' => $row_user['img'],
                'isOnline' => filter_var($row_user['online'], FILTER_VALIDATE_BOOLEAN)
            ];
        }
        elseif($row_group){
            $raw_members = [$row_group['admin'], $row_group['m1'], $row_group['m2'], $row_group['m3'], $row_group['m4']];

            $members = [];

            foreach($raw_members as $raw_member){
                if(strlen($raw_member) > 0){
                    array_push($members, $raw_member);
                }
            }

            $data[] = [
                'isGroup' => true,
                'id' => $row_group['id'],
                'name' => $row_group['name'],
                'img' => $row_group['img'],
                'admin' => $row_group['admin'],
                'members' => $members,
            ];
        }
        else{
            $data[] = null;
        }
    }
}
else{
    $query = "select id, username, img, online, bio, private, date from users where id = ?";
    $stmt_user = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt_user, 's', $q);
    mysqli_stmt_execute($stmt_user);
    $result_user = mysqli_stmt_get_result($stmt_user);

    $row_user = mysqli_fetch_assoc($result_user);
    mysqli_stmt_close($stmt_user);

    $query1 = "select * from groups where id = ?";
    $stmt_group = mysqli_prepare($con, $query1);
    mysqli_stmt_bind_param($stmt_group, 's', $q);
    mysqli_stmt_execute($stmt_group);
    $result_group = mysqli_stmt_get_result($stmt_group);

    $row_group = mysqli_fetch_assoc($result_group);
    mysqli_stmt_close($stmt_group);

    if($row_user){
        $data = [
            'isGroup' => false,
            'id' => $row_user['id'],
            'username' => $row_user['username'],
            'bio' => $row_user['bio'],
            'img' => $row_user['img'],
            'isOnline' => filter_var($row_user['online'], FILTER_VALIDATE_BOOLEAN)
        ];
    }
    elseif($row_group){
        $raw_members = [$row_group['admin'], $row_group['m1'], $row_group['m2'], $row_group['m3'], $row_group['m4']];

        $members = [];

        foreach($raw_members as $raw_member){
            if(strlen($raw_member) > 0){
                array_push($members, $raw_member);
            }
        }

        $data = [
            'isGroup' => true,
            'id' => $row_group['id'],
            'name' => $row_group['name'],
            'img' => $row_group['img'],
            'admin' => $row_group['admin'],
            'members' => $members,
        ];
    }
    else{
        $data = null;
    }
}

ok(true, $data);