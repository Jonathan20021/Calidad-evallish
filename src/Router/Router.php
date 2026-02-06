<?php

namespace App\Router;

use App\Config\Config;

class Router
{
    private $routes = [];

    public function get($path, $callback)
    {
        $this->routes['GET'][$path] = $callback;
    }

    public function post($path, $callback)
    {
        $this->routes['POST'][$path] = $callback;
    }

    public function resolve()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Calculate base path relative to public/index.php and configured base URL.
        $scriptName = $_SERVER['SCRIPT_NAME']; // e.g., /Calidad-evallish/public/index.php
        $scriptBase = str_replace('/public/index.php', '', $scriptName);
        $configBase = rtrim(Config::BASE_URL, '/');

        $basePaths = array_filter([$scriptBase, $configBase], function ($candidate) {
            return $candidate !== '' && $candidate !== '/';
        });

        foreach ($basePaths as $basePath) {
            if (strpos($path, $basePath) === 0) {
                $path = substr($path, strlen($basePath));
                break;
            }
        }

        if ($path === '')
            $path = '/';

        // First, try exact match
        if (isset($this->routes[$method][$path])) {
            $callback = $this->routes[$method][$path];

            // If callback is array [Class, Method] and Class is string, instantiate it
            if (is_array($callback) && isset($callback[0]) && is_string($callback[0])) {
                $callback[0] = new $callback[0]();
            }

            return call_user_func($callback);
        }

        // Try matching routes with parameters (e.g., /users/permissions/{id})
        foreach ($this->routes[$method] ?? [] as $route => $callback) {
            $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';
            
            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches); // Remove full match
                
                // Extract parameter names from route
                preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $route, $paramNames);
                $params = array_combine($paramNames[1], $matches);
                
                // Set parameters in $_GET for backwards compatibility
                foreach ($params as $key => $value) {
                    $_GET[$key] = $value;
                }
                
                // If callback is array [Class, Method] and Class is string, instantiate it
                if (is_array($callback) && isset($callback[0]) && is_string($callback[0])) {
                    $callback[0] = new $callback[0]();
                }

                return call_user_func($callback);
            }
        }

        // 404
        http_response_code(404);
        echo "404 - Page not found";
    }
}
