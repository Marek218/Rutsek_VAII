<?php

namespace Framework\Core;

/**
 * Class Router
 *
 * The Router class is responsible for handling URL routing in the application. It processes incoming requests,
 * determines the appropriate controller and action to invoke, and creates an instance of the specified controller.
 * By default, it routes to a "Home" controller and "index" action when no specific controller or action is provided
 * in the URL.
 *
 * @package App\Core
 */
class Router
{
    private object $controller;
    private string $controllerName;
    private string $action;

    /**
     * Processes the current URL to determine the controller and action to run. This method initializes the controller
     * instance based on the parsed controller name and sets the action to be executed.
     *
     * @return void
     */
    public function processURL(): void
    {
        $fullControllerName = $this->getFullControllerName();
        $this->controller = new $fullControllerName();

        $this->controllerName = $this->getControllerName();
        $this->action = $this->getAction();
    }

    /**
     * Constructs and returns the fully qualified name of the controller by appending the namespace to the controller
     * name obtained from the URL. Defaults to the "HomeController" if no controller is specified in the URL.
     *
     * @return string The full controller name including namespace.
     */
    public function getFullControllerName(): string
    {
        return 'App\\Controllers\\' . $this->getControllerName() . "Controller";
    }

    /**
     * Retrieves the controller name from the URL parameters or pretty path. If no controller is specified, defaults to "Home".
     */
    public function getControllerName(): string
    {
        // Prefer explicit query parameter
        if (isset($_GET['c']) && trim((string)@$_GET['c']) !== '') {
            return trim(ucfirst((string)$_GET['c']));
        }

        // Fallback: parse from REQUEST_URI (pretty URL like /admin or /home/services)
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $segments = array_values(array_filter(explode('/', $path), fn($s) => $s !== ''));
        $controller = $segments[0] ?? 'Home';
        // Optionally expose path id as GET param for convenience (/admin/edit/5)
        if (!isset($_GET['id']) && isset($segments[2]) && ctype_digit($segments[2])) {
            $_GET['id'] = $segments[2];
        }
        return trim(ucfirst($controller));
    }

    /**
     * Retrieves the action name from the URL parameters or pretty path. If no action is specified, defaults to "index".
     */
    public function getAction(): string
    {
        if (isset($_GET['a']) && trim((string)@$_GET['a']) !== '') {
            return (string)$_GET['a'];
        }

        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $segments = array_values(array_filter(explode('/', $path), fn($s) => $s !== ''));
        return $segments[1] ?? 'index';
    }

    /**
     * Returns the instance of the controller determined from the URL. This instance can be used to invoke the
     * specified action.
     *
     * @return object The instantiated controller object.
     */
    public function getController(): object
    {
        return $this->controller;
    }
}
