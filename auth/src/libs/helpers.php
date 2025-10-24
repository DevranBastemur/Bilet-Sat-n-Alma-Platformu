<?php


if (!defined('BASE_URL')) {
    define('BASE_URL', '/OTOBUS/auth/public'); 
}

function view(string $filename, array $data = []): void
{
    foreach ($data as $key => $value) {
        $$key = $value;
    }
    
    $auth_dir = __DIR__ . '/../..'; 
    
    if (strpos($filename, 'admin/inc/') !== false) {

        $file_path = $auth_dir . '/public/' . $filename . '.php';
    } 
    
    else {
        
        $file_path = $auth_dir . '/src/inc/' . $filename . '.php';
    }

    if (!file_exists($file_path)) {
        throw new Error("View dosyası bulunamadı: " . $file_path);
    }

    require_once $file_path;
}



function error_class(array $errors, string $field): string
{
    return isset($errors[$field]) ? 'error' : '';
}


function is_post_request(): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD']) === 'POST';
}


function is_get_request(): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD']) === 'GET';
}


function redirect_to(string $url): void
{
    
    $final_url = (strpos($url, 'http') === 0 || strpos($url, '/') === 0) 
        ? $url 
        : BASE_URL . '/' . $url; 
    
    header('Location: ' . $final_url);
    exit;
}


function redirect_with(string $url, array $items): void
{
    foreach ($items as $key => $value) {
        $_SESSION[$key] = $value;
    }

    redirect_to($url);
}


function redirect_with_message(string $url, string $message, string $type = FLASH_SUCCESS)
{
    flash('flash_' . uniqid(), $message, $type);
    redirect_to($url);
}


function session_flash(...$keys): array
{
    $data = [];
    foreach ($keys as $key) {
        if (isset($_SESSION[$key])) {
            $data[] = $_SESSION[$key];
            unset($_SESSION[$key]); 
        } else {
            $data[] = [];
        }
    }
    return $data;
}
function generate_unique_id(): string
{
    return uniqid('', true); 
}  
