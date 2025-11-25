<?php
// tools/dump_admin.php
// Simple local helper to inspect the admin user row.
// Usage: open in browser: http://localhost/perpustakaan/tools/dump_admin.php
// Remove this file after use.

require __DIR__ . '/../inc/config.php';

header('Content-Type: text/plain; charset=utf-8');

echo "Inspecting admin user row (username='admin' or email='admin@local')\n\n";

$q = "SELECT id, username, email, role, password, created_at FROM users WHERE username=? OR email=? LIMIT 1";
$stmt = $mysqli->prepare($q);
if(!$stmt){
    echo "Prepare failed: " . $mysqli->error . "\n";
    exit;
}
$userKey = 'admin';
$emailKey = 'admin@local';
$stmt->bind_param('ss', $userKey, $emailKey);
if(!$stmt->execute()){
    echo "Execute failed: " . $stmt->error . "\n";
    exit;
}
$res = $stmt->get_result();
if(!$res || $res->num_rows === 0){
    echo "No admin user found.\n";
    exit;
}
$u = $res->fetch_assoc();

// Print safe details (show hash length, not full hash)
echo "ID: " . intval($u['id']) . "\n";
echo "Username: " . $u['username'] . "\n";
echo "Email: " . $u['email'] . "\n";
echo "Role: " . $u['role'] . "\n";
echo "Created: " . $u['created_at'] . "\n";
echo "Password hash length: " . strlen($u['password']) . " chars\n";
echo "Password hash prefix (first 8 chars): " . substr($u['password'],0,8) . "\n";

echo "\nIf the user exists but login still fails, try running the reset script:\n";
echo "  http://localhost/perpustakaan/tools/reset_admin_password.php\n";
echo "After reset, login with identifier 'admin' (or 'admin@local') and password 'admin12345'\n";

$stmt->close();

?>
