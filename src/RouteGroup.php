<?php
/**
 * Created by PhpStorm.
 * User: Sebastian
 * Date: 024 24.03.2019
 * Time: 17:11
 */

namespace FahrradKruken\yawpRouter;


class RouteGroup
{
    private $basePath = '/';

    /**
     * @var Route[]|RouteGroup[]
     */
    public $routes = [];
    public $actionsBefore = [];
    public $actionsAfter = [];

    public function __construct($basePath = '/')
    {
        $this->basePath = $basePath;
    }

    /**
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
     * @param $middleWareCallable
     *
     * @return $this
     */
    public function actionBefore($middleWareCallable)
    {
        $this->actionsBefore[] = $middleWareCallable;
        return $this;
    }

    /**
     * @param $middleWareCallable
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