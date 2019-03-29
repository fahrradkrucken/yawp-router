<?php

namespace FahrradKruken\YAWP\Router;

/**
 * Class RouteGroup
 * @package FahrradKruken\YAWP\Router
 */
class RouteGroup
{
    private $basePath = '/';

    /**
     * @var Route[]|RouteGroup[]
     */
    public $routes = [];
    public $actionsBefore = [];
    public $actionsAfter = [];

    /**
     * RouteGroup constructor.
     *
     * @param string $basePath
     */
    public function __construct($basePath = '/')
    {
        $this->basePath = $basePath;
    }

    /**
     * @see Router::route()
     *
     * @param $path
     * @param $action
     *
     * @return Route
     */
    public function route($path, $action)
    {
        $path = self::normalizePath($this->basePath, $path);
        $routeKey = md5($path);

        $this->routes[$routeKey] = new Route($path, $action);
        return $this->routes[$routeKey];
    }

    /**
     * @see Router::group()
     *
     * @param $path
     * @param $groupAction
     *
     * @return RouteGroup
     */
    public function group($path, $groupAction)
    {
        $groupPath = self::normalizePath($this->basePath, $path) . '/';
        $groupKey = md5($groupPath);
        $this->routes[$groupKey] = new RouteGroup($groupPath);

        call_user_func($groupAction, $this->routes[$groupKey]);
        return $this->routes[$groupKey];
    }

    /**
     * All group before-actions will be executed BEFORE the child $routes
     *
     * @see Router::actionBefore()
     *
     * @param callable $middleWareCallable
     *
     * @return $this
     */
    public function actionBefore($middleWareCallable)
    {
        $this->actionsBefore[] = $middleWareCallable;
        return $this;
    }

    /**
     * All group after-actions will be executed AFTER the child $routes
     *
     * @see Router::actionAfter()
     *
     * @param callable $middleWareCallable
     *
     * @return $this
     */
    public function actionAfter($middleWareCallable)
    {
        $this->actionsAfter[] = $middleWareCallable;
        return $this;
    }

    private static function normalizePath($basePath, $path)
    {
        return trim($path, '/') === '' ? $basePath : ($basePath . trim($path, '/'));
    }
}