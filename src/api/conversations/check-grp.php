<?php

//Initialization
require_once $_SERVER['DOCUMENT_ROOT'] . "/src/includes/main.php";
// requireStrictGet();

$user_data = check_login($con);
header('Content-Type: application/json; charset=utf-8');

$id = $user_data['id'];
$members = [];
$data = [
    'isGroup' => false
];

if(isset($_GET['q'])){
    $qid = $_GET['q'];

    $stmt_user = mysqli_prepare($con, "select user1 from conversations where user1 = ? and is_group = 1");
    mysqli_stmt_bind_param($stmt_user, "s", $qid);
    mysqli_stmt_execute($stmt_user);
    $result_user = mysqli_stmt_get_result($stmt_user);

    $row_user = mysqli_fetch_assoc($result_user);
    mysqli_stmt_close($stmt_user);
    
    
    if($row_user){

        $group_id = $row_user['user1'];

        $stmt_group = mysqli_prepare($con, "select * from groups where id = ?");
        mysqli_stmt_bind_param($stmt_group, "s", $group_id);
        mysqli_stmt_execute($stmt_group);
        $result_group = mysqli_stmt_get_result($stmt_group);

        $row_group = mysqli_fetch_all($result_group, MYSQLI_NUM);
        
        for($i = 2; $i < 5; $i++){
            $members[] = $row_group[0][$i];
        }

        $data = [
            'isGroup' => true,
            'group' => [
                'id' => $row_group[0][0],
                'name' => $row_group[0][1] ?? null,
                'img' => $row_group[0][7]
            ],
            'members' => $members
        ];
        mysqli_stmt_close($stmt_group);
    }
}

echo json_encode([
    'status' => 'ok',
    'response' => $data
]);
die();