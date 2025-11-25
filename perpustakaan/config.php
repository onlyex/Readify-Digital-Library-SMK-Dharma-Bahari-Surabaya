<?php
// Root shim config — includes the real config inside `inc/`
$inc_cfg = __DIR__ . '/inc/config.php';
if(file_exists($inc_cfg)){
    require_once $inc_cfg;
} else {
    // fallback: try local config
    $local = __DIR__ . '/config.local.php';
    if(file_exists($local)){
        require_once $local;
    } else {
        http_response_code(500);
        echo "<h1>Configuration error</h1><p>Missing configuration file: <code>inc/config.php</code>.</p>";
        exit;
    }
}

?>
