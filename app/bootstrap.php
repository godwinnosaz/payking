<?php

try {
    // Custom exception handler to display errors as JSON
    set_exception_handler(function ($exception) {
        header('Content-Type: application/json');
        http_response_code(500); // Internal Server Error
        print_r( json_encode([
            'status' => 'error',
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]));
        exit; // Stop further script execution
    });

    // Load Headers
    require_once 'helpers/headers.php';

    // Load Config
    require_once 'config/config.php';

    // Load Helper Files
    require_once 'helpers/Mobile/src/vendor/autoload.php';
    require_once 'helpers/Mobile/src/autoload.php';
    require_once 'helpers/url_helper.php';
    require_once 'helpers/phpMailer/PHPMailerAutoload.php';
    require_once 'helpers/php/src/AES256.php';

    // Autoload Core Libraries
    spl_autoload_register(function ($className) {
        require_once 'libraries/' . $className . '.php';
    });
} catch (Throwable $th) {
    // Catch block to handle any runtime errors
    header('Content-Type: application/json');
    http_response_code(500); // Internal Server Error
    print_r( json_encode([
        'status' => 'error',
        'message' => $th->getMessage(),
        'file' => $th->getFile(),
        'line' => $th->getLine(),
    ]));
    exit;
}

 