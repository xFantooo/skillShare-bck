<?php

declare(strict_types=1);

namespace App\core;

use App\core\attributes\Route;
use ReflectionClass;

class RouteResolver
{
    public static function getRoutes(): array {
        $routes = [];
        $controllerPath = __DIR__ . '/../controllers/';
        $controllerFiles = glob($controllerPath . '*Controller.php');
        foreach ($controllerFiles as $controllerFile) {
            $className = 'App\\controllers\\' . basename($controllerFile, '.php');
            $reflection = new \ReflectionClass($className);

            foreach ($reflection->getMethods() as $method) {
                $attributes = $method->getAttributes(Route::class);

                foreach ($attributes as $attribute) {
                   $route = $attribute->newInstance();
                   $routes [$route->method][$route->path] = [
                        'class' => $className,
                        'method' => $method->getName(),
                    ];

                }
            }
        }

        return $routes;
    }
}
