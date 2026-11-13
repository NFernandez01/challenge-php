<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');
header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS');

// Preflight de CORS: el navegador pregunta antes de PUT/DELETE/POST con JSON
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Core\Router;
use Dotenv\Dotenv;

// Cualquier excepción no capturada termina acá: se loguea el detalle y
// el cliente recibe un 500 genérico (sin stack trace ni datos internos).
set_exception_handler(function (Throwable $e): void {
    error_log((string) $e);
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor']);
});

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Fallar rápido si falta configuración, en lugar de romper a mitad de un request
$dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'PRECIO_USD'])->notEmpty();
$dotenv->required('DB_PASS');

if (!is_numeric($_ENV['PRECIO_USD']) || (float) $_ENV['PRECIO_USD'] <= 0) {
    throw new RuntimeException('PRECIO_USD debe ser un número mayor a 0');
}

(new Router())->run();
