<?php

namespace App\Core;

abstract class Controller
{
    /**
     * Renderiza una vista pasando parámetros
     *
     * @param string $view Ruta relativa de la vista (ej: 'home/index')
     * @param array $data Variables a extraer en la vista
     */
    protected function render(string $view, array $data = []): void
    {
        extract($data);

        $viewPath = dirname(__DIR__) . '/Views/' . $view . '.php';

        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            echo "Error: La vista '{$view}' no existe en {$viewPath}";
        }
    }

    /**
     * Retorna una respuesta JSON para peticiones AJAX
     */
    protected function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}
