<?php

class router
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
		$method = $_SERVER['REQUEST_METHOD'];

		$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

		// Remove index.php da URL
		$uri = str_replace('/index.php', '', $uri);

		// Remove public caso exista na URL
		$uri = str_replace('/public', '', $uri);

		if ($uri === '') {
			$uri = '/';
		}

		foreach ($this->routes as $route) {

			if ($route['method'] !== $method) {
				continue;
			}

			$pattern = preg_replace('/\{[^\/]+\}/', '([^/]+)', $route['route']);
			$pattern = '#^' . $pattern . '$#';

			if (preg_match($pattern, $uri, $matches)) {

				array_shift($matches);

				[$controller, $method] = $route['action'];

				$instance = new $controller();

				call_user_func_array([$instance, $method], $matches);

				return;
			}
		}

		Response::notFound();
	}
}