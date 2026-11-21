<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cargar el autoloader generado por Composer
require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Core\Router;
use App\Controllers\HomeController;

// Instanciar el enrutador
$router = new Router();

// Rutas de Navegación
$router->get('/', [HomeController::class, 'index']);
$router->get('/logout', [HomeController::class, 'logout']);

// Rutas de Autenticación, Registro y Recuperación (AJAX)
$router->post('/login', [HomeController::class, 'login']);
$router->post('/registro', [HomeController::class, 'registro']);
$router->post('/recuperar-password', [HomeController::class, 'recuperarPassword']);

// Rutas del CRUD de Usuarios (AJAX)
$router->post('/usuarios/crear', [HomeController::class, 'crearUsuario']);
$router->post('/usuarios/actualizar', [HomeController::class, 'actualizarUsuario']);
$router->post('/usuarios/eliminar', [HomeController::class, 'eliminarUsuario']);

// Ejecutar el enrutador
$router->dispatch();
