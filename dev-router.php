<?php
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$file = __DIR__ . $path;

// Block direct access to sensitive extensions
if (preg_match('/\.(sql|env|log|bat|sh)$/i', $path)) {
    http_response_code(403);
    echo "403 Forbidden: Access Denied";
    exit;
}

// Redirect/alias relative admin diagnostic requests
if (preg_match('#^/admin/(diagnostics_[a-z]+|health_check)\.php$#i', $path, $m)) {
    $target = __DIR__ . '/' . $m[1] . '.php';
    if (file_exists($target)) {
        require $target;
        exit;
    }
}

// Serve existing non-PHP static files (CSS, JS, images, fonts)
if ($path !== '/' && is_file($file) && !preg_match('/\.php$/i', $path)) {
    return false;
}

// Execute standalone PHP diagnostic or utility scripts directly
if ($path !== '/' && is_file($file) && preg_match('/\.php$/i', $path) && $path !== '/index.php') {
    require $file;
    exit;
}

// Route all other requests through index.php
require __DIR__ . '/index.php';
