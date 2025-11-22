<?php
// FILE: /app/core/Router.php

class Router {
    private $routes = [];
    private $notFoundCallback;

    public function get($path, $callback) {
        $this->addRoute('GET', $path, $callback);
    }

    public function post($path, $callback) {
        $this->addRoute('POST', $path, $callback);
    }

    public function put($path, $callback) {
        $this->addRoute('PUT', $path, $callback);
    }

    public function delete($path, $callback) {
        $this->addRoute('DELETE', $path, $callback);
    }

    public function any($path, $callback) {
        $this->addRoute('ANY', $path, $callback);
    }

    private function addRoute($method, $path, $callback) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'callback' => $callback
        ];
    }

    public function notFound($callback) {
        $this->notFoundCallback = $callback;
    }

    public function resolve(Request $request, Response $response) {
        $path = $request->getPath();
        $method = $request->getMethod();

        foreach ($this->routes as $route) {
            if ($route['method'] !== 'ANY' && $route['method'] !== $method) {
                continue;
            }

            $pattern = $this->convertPathToRegex($route['path']);

            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);

                $params = $this->extractParams($route['path'], $matches);
                $request->setParams($params);

                return $this->executeCallback($route['callback'], $request, $response);
            }
        }

        if ($this->notFoundCallback) {
            return call_user_func($this->notFoundCallback, $request, $response);
        }

        $response->notFound();
    }

    private function convertPathToRegex($path) {
        $path = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_-]+)', $path);
        return '#^' . $path . '$#';
    }

    private function extractParams($path, $matches) {
        $params = [];
        preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $path, $paramNames);

        foreach ($paramNames[1] as $index => $name) {
            $params[$name] = $matches[$index] ?? null;
        }

        return $params;
    }

    private function executeCallback($callback, $request, $response) {
        if (is_array($callback)) {
            list($controller, $method) = $callback;

            if (is_string($controller)) {
                $controller = new $controller();
            }

            return call_user_func_array([$controller, $method], [$request, $response]);
        }

        return call_user_func($callback, $request, $response);
    }
}
