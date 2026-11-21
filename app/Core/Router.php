<?php

namespace App\Core;

class Router
{
    protected array $routes = [];

    public function get(string $path, array|callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, array|callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    protected function addRoute(string $method, string $path, array|callable $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => '/' . trim($path, '/'),
            'handler' => $handler
        ];
    }

    public function dispatch(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        // Soporte para ejecución en subcarpetas de Apache/XAMPP
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        if ($scriptDir !== '/' && !empty($scriptDir) && str_starts_with($requestUri, $scriptDir)) {
            $requestUri = substr($requestUri, strlen($scriptDir));
        }

        // Normalizar URI
        $requestUri = '/' . trim($requestUri, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && $route['path'] === $requestUri) {
                $handler = $route['handler'];

                if (is_callable($handler)) {
                    call_user_func($handler);
                    return;
                }

                if (is_array($handler) && count($handler) === 2) {
                    [$controllerClass, $action] = $handler;

                    if (class_exists($controllerClass)) {
                        $controller = new $controllerClass();
                        if (method_exists($controller, $action)) {
                            $controller->$action();
                            return;
                        }
                    }
                }
            }
        }

        // 404 Not Found
        http_response_code(404);
        echo "<div style='font-family:sans-serif;text-align:center;padding:50px;'>";
        echo "<h1>404 - Página no encontrada</h1>";
        echo "<p>La ruta solicitada no existe.</p>";
        echo "<a href='/'>Volver al inicio</a>";
        echo "</div>";
    }
}
