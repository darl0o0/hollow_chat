<?php
//Initialization
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/session.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";

ini_set('display_errors', '0');
error_reporting(E_ALL);

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

$user_data = check_login($con);
$id = $user_data['id'];

try{
    guard('POST');

    $input = json_decode(file_get_contents('php://input'), true);
    $group_id = bin2hex(random_bytes(8));

    $cols = [];
    $placeholders = [];
    $params = [$group_id, null, $id];
    $types = "sss";

    $members_ids = $input['members'];

    for($i = 0; $i < count($members_ids); $i++){
        $slot_number = $i + 1;
        $cols[] = "m{$slot_number}";
        $placeholders[] = "?";
        $params[] = $members_ids[$i];
        $types .= "s";
    }

    $query = "insert into groups (id, name, admin, ".implode(", ", $cols).") values (?, ?, ?, ".implode(", ", $placeholders).")";

    $stmt_insert_grp_members = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt_insert_grp_members, $types, ...$params);
    mysqli_stmt_execute($stmt_insert_grp_members);
    mysqli_stmt_close($stmt_insert_grp_members);

    $stmt_insert_conversation = mysqli_prepare($con, "insert into conversations (user1, user2, is_group) values (?, null, 1)");
    mysqli_stmt_bind_param($stmt_insert_conversation, "s", $group_id);
    mysqli_stmt_execute($stmt_insert_conversation);
    mysqli_stmt_close($stmt_insert_conversation);

    respondOk([
        'groupId' => $group_id
    ]);
}
catch(Throwable $e){
    writeLog("API Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());

    respondError("Internal Server Error.", 500);
}