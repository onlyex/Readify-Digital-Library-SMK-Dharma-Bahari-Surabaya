<?php
require_once __DIR__ . '/inc/config.php';
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header('Location: /perpustakaan/login.php'); exit;
}

$identifier = isset($_POST['identifier'])? trim($_POST['identifier']) : '';
$password = isset($_POST['password'])? $_POST['password'] : '';

if($identifier === '' || $password === ''){
    $_SESSION['msg'] = 'Identifier and password are required.';
    header('Location: /perpustakaan/login.php'); exit;
}

$stmt = $mysqli->prepare('SELECT id,username,email,password,role FROM users WHERE username=? OR email=? LIMIT 1');
if(!$stmt){
    // log and return
    if(!is_dir(__DIR__.'/inc/logs')) @mkdir(__DIR__.'/inc/logs',0755,true);
    @file_put_contents(__DIR__.'/inc/logs/auth.log', date('c')." - prepare_failed\n", FILE_APPEND);
    $_SESSION['msg'] = 'Database error.';
    header('Location: /perpustakaan/login.php'); exit;
}
$stmt->bind_param('ss', $identifier, $identifier);
$stmt->execute();
$res = $stmt->get_result();
if(!$res || $res->num_rows === 0){
    // log missing user for debugging
    if(!is_dir(__DIR__.'/inc/logs')) @mkdir(__DIR__.'/inc/logs',0755,true);
    @file_put_contents(__DIR__.'/inc/logs/auth.log', date('c')." - identifier_not_found: {$identifier}\n", FILE_APPEND);
    $_SESSION['msg'] = 'Invalid credentials.';
    header('Location: /perpustakaan/login.php'); exit;
}
$user = $res->fetch_assoc();
// verify password and log result for debugging (logs are local only)
$pwok = password_verify($password, $user['password']);
if(!$pwok){
    if(!is_dir(__DIR__.'/inc/logs')) @mkdir(__DIR__.'/inc/logs',0755,true);
    @file_put_contents(__DIR__.'/inc/logs/auth.log', date('c')." - password_verify_failed for identifier: {$identifier}\n", FILE_APPEND);
    $_SESSION['msg'] = 'Invalid credentials.';
    header('Location: /perpustakaan/login.php'); exit;
}

// success
session_regenerate_id(true);
$_SESSION['user'] = ['id'=> (int)$user['id'], 'username'=>$user['username'], 'email'=>$user['email'], 'role'=>$user['role']];
$_SESSION['msg'] = 'Login successful.';
if($user['role'] === 'admin'){
    header('Location: /perpustakaan/admin/dashboard.php'); exit;
} else {
    header('Location: /perpustakaan/index.php'); exit;
}
