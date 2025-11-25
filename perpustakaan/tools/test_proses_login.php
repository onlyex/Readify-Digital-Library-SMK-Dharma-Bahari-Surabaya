<?php
// tools/test_proses_login.php
// Usage: php tools/test_proses_login.php [identifier] [password]
// Defaults to: admin / admin12345

require __DIR__ . '/../inc/config.php';

$identifier = $argv[1] ?? 'admin';
$password = $argv[2] ?? 'admin12345';

echo "Testing login for identifier='$identifier' with provided password\n";

$stmt = $mysqli->prepare('SELECT id,username,email,password,role FROM users WHERE username=? OR email=? LIMIT 1');
if(!$stmt){
    echo "DB prepare failed: " . $mysqli->error . "\n";
    exit(2);
}
$stmt->bind_param('ss', $identifier, $identifier);
$stmt->execute();
$res = $stmt->get_result();
if(!$res || $res->num_rows === 0){
    echo "User not found for identifier: $identifier\n";
    exit(1);
}
$user = $res->fetch_assoc();

echo "Found user: id={$user['id']} username={$user['username']} role={$user['role']} created={$user['created_at']}\n";

$ok = password_verify($password, $user['password']);
if($ok){
    echo "Password verify: OK\n";
    exit(0);
} else {
    echo "Password verify: FAILED\n";
    exit(1);
}

?>
