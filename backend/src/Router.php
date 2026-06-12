<?php 

class Router { 
    private array $routes = []; 

    public function get(string $uri, callable $action): void { 
        $this->routes[] = [ 'method' => 'GET', 'uri' => $uri, 'action' => $action ]; 
    }

    public function post(string $uri, callable $action): void {
        $this->routes[] = ['method' => 'POST', 'uri' => $uri, 'action' => $action];
    }

    public function put(string $uri, callable $action): void { 
        $this->routes[] = ['method' => "PUT", 'uri' => $uri, 'action' => $action]; 
    }

    public function patch(string $uri, callable $action): void { 
        $this->routes[] = ['method' => "PATCH", 'uri' => $uri, 'action' => $action]; 
    }

    public function delete(string $uri, callable $action): void { 
        $this->routes[] = ['method' => "DELETE", 'uri' => $uri, 'action' => $action]; 
    }

    public function dispatch(string $requestMethod, string $requestUri): void {
        foreach ($this->routes as $route) {
            $pattern = preg_replace('/\{([a-zA-Z0-9\-]+)\}/', '([a-zA-Z0-9_.\-]+)', $route['uri']);
            $pattern = "#^" . $pattern . "$#";

            if ($route['method'] === $requestMethod && preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches);
                
                call_user_func_array($route['action'], $matches);
                return;
            }
        }

        header("HTTP/1.0 404 Not Found");
        echo "404 - Pagina nu a fost găsită.";
    }
}