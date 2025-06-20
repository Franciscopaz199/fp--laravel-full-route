<?php

namespace Fp\RoutingKit\Routes;



use Illuminate\Support\Facades\Route;
use Fp\RoutingKit\Entities\FpRoute;
use Illuminate\Support\Collection;


/**
 * Registra una ruta y sus hijos de forma recursiva
 * @param RoutingKit $route
 */
class FpRegisterRouter
{
    public static function registerRoutes(array|Collection|null $routes = null)
    {
        if (is_null($routes)) {
            $routes = FpRoute::all();
        }
        $routes->each(function ($route) {
            if ($route instanceof FpRoute)
                static::registerRoutingKit($route);
        });
    }

    public static function registerRoutingKit(FpRoute $route)
    {
        //echo "Registering route: {$route->id} ({$route->urlMethod})\n";
        $hasItems = !empty($route->items);
        $method = strtolower($route->urlMethod);
        $url = '/' . ltrim($route->getUrl(), '/');


        $middleware = $route->urlMiddleware ?? [];

        // si existe el permiso de la ruta entonces se agrega al middleware
        if ($route->accessPermission)
            $middleware[] = 'permission:' . $route->accessPermission;


        // Ruta simple
        if (!$route->isGroup) {
            Route::match(
                [$method],
                $url,
                $route->urlController
            )->name($route->id)
                ->middleware($middleware);
        }


        // Registrar rutas hijas dentro del grupo con middleware del padre
        if ($hasItems)
            Route::middleware($middleware)->group(function () use ($route) {
                foreach ($route->items as $childRoute)
                    if ($childRoute instanceof FpRoute)
                        static::registerRoutingKit($childRoute);
            });
    }


}
