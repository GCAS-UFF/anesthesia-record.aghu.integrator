<?php

class Router
{
    private array $routes = [];

    public function get(string $route, array $action): void
    {
        $this->addRoute('GET', $route, $action);
    }

    public function post(string $route, array $action): void
    {
        $this->addRoute('POST', $route, $action);
    }

    public function put(string $route, array $action): void
    {
        $this->addRoute('PUT', $route, $action);
    }

    public function delete(string $route, array $action): void
    {
        $this->addRoute('DELETE', $route, $action);
    }

    private function addRoute(string $method, string $route, array $action): void
    {
        $this->routes[] = [
            'method' => $method,
            'route' => $route,
            'action' => $action
        ];
    }

  public function dispatch(): void
{
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    var_dump($uri);
    die();
}
}