<?php

// Check if user has session cookies
function check_login($con){
    if(isset($_SESSION['id'])){
        $id = $_SESSION['id'];
        $query = "select * from users where id = '$id' limit 1";
        $result = mysqli_query($con, $query);
        if($result && mysqli_num_rows($result)>0){
            $user_data = mysqli_fetch_assoc($result);
            return $user_data;
        }
    }
    else{
        header("Location: /signin");
        die();
    }
}

// Generate CSRF token
function getCsrfToken(){
    if(empty($_SESSION['csrf_token'])){
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Check headers and CSRF token
function checkHeadersCSRF(){
    header('Content-Type: application/json; charset=utf-8');

    $headers = getallheaders();
    $requestHeader = $headers['X-Requested-With'] ?? $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';

    if ($requestHeader !== 'XMLHttpRequest') {
        http_response_code(403);
        exit(json_encode(['status' => 'error', 'message' => 'Direct access forbidden']));
    }
    
    $clientToken = $headers['X-CSRF-TOKEN'] ?? $_POST['csrf_token'] ?? '';

    if (empty($clientToken) || !hash_equals($_SESSION['csrf_token'] ?? '', $clientToken)) {
        http_response_code(403);
        exit(json_encode(['status' => 'error', 'message' => 'Invalid or missing CSRF token']));
    }
}

// Strict POST Guard
// Validates custom header AND CSRF token
function requireStrictPost(){
    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        http_response_code(405);
        die(json_encode(['status' => 'error', 'message' => 'Method Not Allowed']));
    }
    checkHeadersCSRF();
}