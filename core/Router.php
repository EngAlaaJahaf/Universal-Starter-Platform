<?php

class Router
{
    private $routes = array();

    public function get($pattern, $handler) { return $this->add('GET', $pattern, $handler); }
    public function post($pattern, $handler) { return $this->add('POST', $pattern, $handler); }
    public function put($pattern, $handler) { return $this->add('PUT', $pattern, $handler); }
    public function delete($pattern, $handler) { return $this->add('DELETE', $pattern, $handler); }

    public function add($method, $pattern, $handler)
    {
        $this->routes[] = array('method' => strtoupper($method), 'pattern' => $pattern, 'handler' => $handler);
        return $this;
    }

    public function dispatch($uri = null, $method = null)
    {
        $method = strtoupper($method ?: ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $uri = $this->normalizeUri($uri ?: ($_SERVER['REQUEST_URI'] ?? '/'));

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;
            $names = array();
            $patternWithTokens = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', function ($match) use (&$names) {
                $names[] = $match[1];
                return '___PARAM_PLACEHOLDER___';
            }, $route['pattern']);
            $escaped = preg_quote($patternWithTokens, '#');
            $regex = str_replace('___PARAM_PLACEHOLDER___', '([^/]+?)', $escaped);
            $regex = '#^' . rtrim($regex, '/') . '/?$#u';

            if (preg_match($regex, $uri, $matches)) {
                array_shift($matches);
                $params = array();
                foreach ($matches as $index => $value) $params[$names[$index]] = rawurldecode($value);
                return $this->callHandler($route['handler'], $params);
            }
        }

        http_response_code(404);
        return $this->callHandler('ErrorController@notFound', array());
    }

    private function normalizeUri($uri)
    {
        $path = parse_url($uri, PHP_URL_PATH);
        $path = rawurldecode((string) $path);
        $path = '/' . trim($path, '/');
        $scriptDirectory = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        if ($scriptDirectory !== '' && $scriptDirectory !== '/' && strpos($path, $scriptDirectory) === 0) {
            $path = '/' . trim(substr($path, strlen($scriptDirectory)), '/');
        }
        return ($path === '/index.php' || $path === '') ? '/' : rtrim($path, '/');
    }

    private function callHandler($handler, array $parameters)
    {
        list($controllerName, $method) = explode('@', $handler, 2);
        if (!class_exists($controllerName)) throw new RuntimeException('Controller not found: ' . $controllerName);
        $controller = new $controllerName();
        return call_user_func_array(array($controller, $method), array_values($parameters));
    }
}
