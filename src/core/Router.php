<?php

declare(strict_types=1);

namespace App\core;

use Exception;

class Router
{
    private array $routes;

    public function __construct()
    {
        $this->routes = RouteResolver::getRoutes();
    }

    public function dispatch(string $uri, string $method): void {
        if (!isset($this->routes[$method][$uri])) {
            throw new Exception("Route not found for $method $uri");

        }
        $route = $this->routes[$method][$uri];
        if (!isset($route['class'], $route['method'])) {
            throw new Exception("Invalid route configuration for $method $uri");
        }

        $controllerClass = $route['class'];
        $action = $route['method'];
        $controller = new $controllerClass();
        $controller->$action();
    }
}
