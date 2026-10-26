<?php

// Check if user has session cookies
function check_login($con){
    if(isset($_SESSION['id'])){
        $id = $_SESSION['id'];
        $stmt_user_data = mysqli_prepare($con, "select * from users where id = ? limit 1");
        mysqli_stmt_bind_param($stmt_user_data, "s", $id);
        mysqli_stmt_execute($stmt_user_data);
        $result_user_data = mysqli_stmt_get_result($stmt_user_data);

        $row_user_data = mysqli_fetch_assoc($result_user_data);
        mysqli_stmt_close($stmt_user_data);
        
        return $row_user_data;
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

// Function that returns an HTTP response
function ok($isOk, $response = null){
    if($isOk){
        echo json_encode([
            'status' => 'ok',
            'response' => $response
        ]);
    }
    else{
        echo json_encode([
            'status' => 'error',
            'response' => $response
        ]);
    }
    die();
}

// Read .env secret data
function getEnvValue($key){
    $envFile = __DIR__ . '/../../.env';

    if(!file_exists($envFile)) return null;

    $lines = file($envFile, FILE_IGNORE_NEW_LINES);
    foreach($lines as $line){
        if(strpos(trim($line), '#') === 0) continue;

        if(strpos(trim($line), '=') === false) continue;

        list($name, $value) = explode('=', $line, 2);
        if(trim($name) == $key){
            return trim($value);
        }
    }
    return null;
}

// Profile picture processing
function processAndSaveProfilePic($file){
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $file_type = mime_content_type($file['tmp_name']);

    if(!in_array($file_type, $allowed_types)){
        ok(false, 'Please upload a valid image.');
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if(in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])){
        $extension = 'jpg';
    }

    $unique_file_name = bin2hex(random_bytes(16)).'.'.$extension;
    $destination = $_SERVER['DOCUMENT_ROOT'] . "/src/assets/pfps/" . $unique_file_name;

    switch($file_type){
        case 'image/jpeg':
        case 'image/jpg':
            $source_image = imagecreatefromjpeg($file['tmp_name']);
        break;
        case 'image/png':
            $source_image = imagecreatefrompng($file['tmp_name']);
        break;
        case 'image/webp':
            $source_image = imagecreatefromwebp($file['tmp_name']);
        break;
        default:
            ok(false, 'Unsupported image format.');
    }

    $img_width = imagesx($source_image);
    $img_height = imagesy($source_image);

    $crop_size = min($img_width, $img_height);
    $src_x = ($img_width - $crop_size) / 2;
    $src_y = ($img_height - $crop_size) / 2;

    $output_size = 500;
    $square_img = imagecreatetruecolor($output_size, $output_size);

    if($file_type == 'image/png' ||$file_type == 'image/webp'){
        imagealphablending($square_img, false);
        imagesavealpha($square_img, true);
        $transparent = imagecolorallocatealpha($square_img, 255, 255, 255, 127);
        imagefilledrectangle($square_img, 0, 0, $output_size, $output_size, $transparent);
    }

    imagecopyresampled(
        $square_img, $source_image, 0, 0, (int)$src_x, (int)$src_y,
        $output_size, $output_size, $crop_size, $crop_size
    );

    $success = false;
    switch($file_type){
        case 'image/jpeg':
        case 'image/jpg':
            $success = imagejpeg($square_img, $destination, 85);
        break;
        case 'image/png':
            $success = imagepng($square_img, $destination, 6);
        break;
        case 'image/webp':
            $success = imagewebp($square_img, $destination, 85);
        break;
    }

    imagedestroy($source_image);
    imagedestroy($square_img);

    if(!$success){
        ok(false, 'Failed process profile picture.');
    }

    return $unique_file_name;
}