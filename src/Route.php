<?php

namespace FahrradKruken\YAWP\Router;

/**
 * Class Route
 * @package FahrradKruken\YAWP\Router
 */
class Route implements RouteInterface
{
    public $path = '';
    public $action = null;

    public $routeInfo = null;

    public $actionsBefore = [];
    public $actionsAfter = [];

    public $routeType = self::ROUTE_TYPE_PUBLIC;

    /**
     * Route constructor.
     *
     * @param string $path
     * @param callable|null   $action
     */
    public function __construct($path = '', $action = null)
    {
        $this->path = $path;
        $this->action = $action;
        $this->routeInfo = new RouteInfo();
        $this->routeInfo->path = $this->path;
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
}