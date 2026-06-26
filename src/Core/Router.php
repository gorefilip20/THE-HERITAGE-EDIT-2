<?php

declare(strict_types=1);

namespace HeritageEdit\Core;

use HeritageEdit\Core\Request;
use HeritageEdit\Core\Response;

final class Router
{
    private array $routes = [];
    private array $middleware = [];

    public function get(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    private function addRoute(string $method, string $path, callable|array $handler, array $middleware): void
    {
        $pattern = '#^' . preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path) . '$#';
        $this->routes[] = compact('method', 'path', 'pattern', 'handler', 'middleware');
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $uri    = strtok($request->uri(), '?');

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;

            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $request->setRouteParams($params);

                $this->runMiddleware($route['middleware'], $request, function () use ($route, $request) {
                    $this->callHandler($route['handler'], $request);
                });
                return;
            }
        }

        Response::json(['error' => 'Not Found'], 404);
    }

    private function runMiddleware(array $middleware, Request $request, callable $final): void
    {
        $chain = array_reduce(
            array_reverse($middleware),
            fn ($carry, $mw) => fn () => (new $mw())->handle($request, $carry),
            $final
        );
        $chain();
    }

    private function callHandler(callable|array $handler, Request $request): void
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            (new $class())->$method($request);
        } else {
            $handler($request);
        }
    }
}
