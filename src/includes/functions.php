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
    if(empty($_COOKIE['csrf_token'])){
        $token = bin2hex(random_bytes(32));
        setcookie('csrf_token', $token, [
            'expires' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => false,
            'httponly' => false,
            'samesite' => 'Lax'
        ]);
        $_COOKIE['csrf_token'] = $token;
    }
    return $_COOKIE['csrf_token'];
}

// Validate incoming POST requests against the cookie
function verifyCsrfToken($submittedToken){
    if(empty($_COOKIE['csrf_token']) || empty($submittedToken)){
        return false;
    }
    return hash_equals($_COOKIE['csrf_token'], $submittedToken);
}

// Check headers and CSRF token
function checkHeadersCSRF(){
    header('Content-Type: application/json; charset=utf-8');

    $headers = getallheaders();
    $requestHeader = $headers['X-Requested-With'] ?? $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';

    if ($requestHeader !== 'XMLHttpRequest'){
        http_response_code(403);
        exit(json_encode(['status' => 'error', 'message' => 'Direct access forbidden']));
    }
    
    $clientToken = $headers['X-CSRF-TOKEN'] 
            ?? $headers['x-csrf-token'] 
            ?? $_SERVER['HTTP_X_CSRF_TOKEN'] 
            ?? $_POST['csrf_token'] 
            ?? '';

    if (!verifyCsrfToken($clientToken)){
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

// Strict GET Guard
// Validates custom header AND CSRF token
function requireStrictGet(){
    header('Content-Type: application/json; charset=utf-8');
    
    if($_SERVER['REQUEST_METHOD'] !== 'GET'){
        http_response_code(405);
        die(json_encode(['status' => 'error', 'message' => 'Method Not Allowed']));
    }

    $headers = getallheaders();
    $requestHeader = $headers['X-Requested-With'] ?? $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';

    if ($requestHeader !== 'XMLHttpRequest') {
        http_response_code(403);
        exit(json_encode(['status' => 'error', 'message' => 'Direct access forbidden']));
    }
}