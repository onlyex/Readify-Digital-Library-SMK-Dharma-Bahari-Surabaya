<?php
// inc/config.php - main configuration and DB connection
if(session_status()===PHP_SESSION_NONE) session_start();

define('DB_HOST','localhost');
define('DB_USER','root');
define('DB_PASS','');
define('DB_NAME','library_db');

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if($mysqli->connect_errno){
    // show friendly message
    http_response_code(500);
    echo '<h1>Database connection error</h1><p>'.$mysqli->connect_error.'</p>';
    exit;
}

// helper
function esc($s){ return htmlspecialchars($s, ENT_QUOTES); }

// Ensure users table exists (safe for first run)
$createUsers = "CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$mysqli->query($createUsers);

// add avatar_path column if not exists
$c = $mysqli->query("SHOW COLUMNS FROM users LIKE 'avatar_path'");
if(!$c || $c->num_rows === 0){
    $mysqli->query("ALTER TABLE users ADD COLUMN avatar_path VARCHAR(255) NULL AFTER email");
}

// ensure notifications table exists
$createNotifications = "CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(255) DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$mysqli->query($createNotifications);

// seed admin for local testing if missing
$adminEmail = 'admin@local';
$adminUser = 'admin';
$check = $mysqli->query("SELECT id FROM users WHERE email='".$mysqli->real_escape_string($adminEmail)."' OR username='".$mysqli->real_escape_string($adminUser)."' LIMIT 1");
if(!$check || $check->num_rows===0){
    $pw = password_hash('12345678910', PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare('INSERT INTO users (username,email,password,role) VALUES (?,?,?,"admin")');
    if($stmt){
        $stmt->bind_param('sss',$adminUser,$adminEmail,$pw);
        $stmt->execute();
        $stmt->close();
    }
}

// seed a demo user for testing if missing
$demoUser = 'user';
$demoEmail = 'user@local';
$checku = $mysqli->query("SELECT id FROM users WHERE email='".$mysqli->real_escape_string($demoEmail)."' OR username='".$mysqli->real_escape_string($demoUser)."' LIMIT 1");
if(!$checku || $checku->num_rows===0){
    $pw2 = password_hash('user123', PASSWORD_DEFAULT);
    $st = $mysqli->prepare('INSERT INTO users (username,email,password,role) VALUES (?,?,?,"user")');
    if($st){
        $st->bind_param('sss',$demoUser,$demoEmail,$pw2);
        $st->execute();
        $st->close();
    }
}

?>
