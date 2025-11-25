<?php
require_once __DIR__ . '/../config.php';
$res = $mysqli->query("SELECT id, username, email, password FROM users WHERE username='admin' OR email='admin@local' LIMIT 1");
$u = $res ? $res->fetch_assoc() : null;
if(!$u){ echo "no user\n"; exit; }
$ok = password_verify('admin123', $u['password']) ? 'OK' : 'FAIL';
echo "user: {$u['username']} / {$u['email']} -> $ok\n";
?>