<?php

namespace FahrradKruken\YAWP\Router;

/**
 * Class RouteGroup
 * @package FahrradKruken\YAWP\Router
 */
class RouteGroup implements RouteGroupInterface, RouteInterface
{
    private $basePath = '/';

    /**
     * @var Route[]|RouteGroup[]
     */
    public $routes = [];
    public $actionsBefore = [];
    public $actionsAfter = [];

    public $routeType = self::ROUTE_TYPE_PUBLIC;

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
     * @inheritdoc
     */
    public function route($path, $action)
    {
        $path = self::normalizePath($this->basePath, $path);
        $routeKey = md5($path);

        $this->routes[$routeKey] = new Route($path, $action);
        return $this->routes[$routeKey];
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function actionBefore($middleWareCallable)
    {
        $this->actionsBefore[] = $middleWareCallable;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function actionAfter($middleWareCallable)
    {
        $this->actionsAfter[] = $middleWareCallable;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function asPublic()
    {
        $this->routeType = self::ROUTE_TYPE_PUBLIC;
    }

    /**
     * @inheritdoc
     */
    public function asPrivate()
    {
        $this->routeType = self::ROUTE_TYPE_PRIVATE;
    }

    /**
     * @inheritdoc
     */
    public function asPublicAjax()
    {
        $this->routeType = self::ROUTE_TYPE_AJAX_PUBLIC;
    }

    /**
     * @inheritdoc
     */
    public function asPrivateAjax()
    {
        $this->routeType = self::ROUTE_TYPE_AJAX_PRIVATE;
    }

    private static function normalizePath($basePath, $path)
    {
        return trim($path, '/') === '' ? $basePath : ($basePath . trim($path, '/'));
    }
}