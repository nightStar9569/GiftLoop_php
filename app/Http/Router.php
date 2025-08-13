<?php
namespace App\Http;

class Router
{
    /** @var array<string,array<int,array{pattern:string,regex:string,handler:mixed,middleware:array}>> */
    private array $routes = [];

    public function add(string $method, string $pattern, $handler, array $middleware = []): void
    {
        $method = strtoupper($method);
        $pattern = trim($pattern, '/');
        $regex = $this->compilePatternToRegex($pattern);
        $this->routes[$method][] = [
            'pattern' => $pattern,
            'regex' => $regex,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function get(string $pattern, $handler, array $middleware = []): void
    {
        $this->add('GET', $pattern, $handler, $middleware);
    }
    public function post(string $pattern, $handler, array $middleware = []): void
    {
        $this->add('POST', $pattern, $handler, $middleware);
    }
    public function put(string $pattern, $handler, array $middleware = []): void
    {
        $this->add('PUT', $pattern, $handler, $middleware);
    }
    public function patch(string $pattern, $handler, array $middleware = []): void
    {
        $this->add('PATCH', $pattern, $handler, $middleware);
    }
    public function delete(string $pattern, $handler, array $middleware = []): void
    {
        $this->add('DELETE', $pattern, $handler, $middleware);
    }

    public function dispatch(string $route, string $method): void
    {
        $route = trim($route, '/');
        $method = strtoupper($method);
        $candidates = $this->routes[$method] ?? [];
        foreach ($candidates as $entry) {
            $params = [];
            if ($this->match($entry['regex'], $route, $params)) {
                $handler = $entry['handler'];
                // Run middleware chain
                foreach ($entry['middleware'] as $mw) {
                    if (is_callable($mw)) {
                        $mw();
                    } elseif (is_string($mw) && class_exists($mw)) {
                        (new $mw())->handle();
                    }
                }
                // Resolve controller handler if [ClassName, 'method'] given
                if (is_array($handler) && isset($handler[0], $handler[1]) && is_string($handler[0])) {
                    $class = $handler[0];
                    $methodName = $handler[1];
                    $instance = new $class();
                    $handler = [$instance, $methodName];
                }
                if (!is_callable($handler)) {
                    http_response_code(500);
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['error' => 'Route handler not callable']);
                    return;
                }
                if (!empty($params)) {
                    call_user_func($handler, $params);
                } else {
                    call_user_func($handler);
                }
                return;
            }
        }
        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Not found', 'route' => $route]);
    }

    private function compilePatternToRegex(string $pattern): string
    {
        $escaped = preg_replace('#/#', '\\/', $pattern);
        $regex = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', function ($m) {
            $name = $m[1];
            return '(?P<' . $name . '>[^\/]+)';
        }, $escaped);
        return '#^' . $regex . '$#';
    }

    private function match(string $regex, string $route, array &$params): bool
    {
        if (!preg_match($regex, $route, $matches))
            return false;
        foreach ($matches as $key => $value) {
            if (!is_int($key)) {
                $params[$key] = $value;
            }
        }
        return true;
    }
}