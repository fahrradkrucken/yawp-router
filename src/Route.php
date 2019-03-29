<?php

namespace FahrradKruken\YAWP\Router;

/**
 * Class Route
 * @package FahrradKruken\YAWP\Router
 */
class Route
{
    const ROUTE_TYPE_PUBLIC = 'public';
    const ROUTE_TYPE_PRIVATE = 'private';
    const ROUTE_TYPE_AJAX_PUBLIC = 'ajax_public';
    const ROUTE_TYPE_AJAX_PRIVATE = 'ajax_private';

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

    // --
    // -- Set Route Type
    // --

    /**
     * Equivalent of:
     * admin_post_nopriv_{action}
     */
    public function asPublic()
    {
        $this->routeType = self::ROUTE_TYPE_PUBLIC;
    }

    /**
     * Equivalent of:
     * admin_post_{action}
     * admin_post_nopriv_{action}
     */
    public function asPrivate()
    {
        $this->routeType = self::ROUTE_TYPE_PRIVATE;
    }

    /**
     * Equivalent of:
     * wp_ajax_nopriv_{action}
     */
    public function asPublicAjax()
    {
        $this->routeType = self::ROUTE_TYPE_AJAX_PUBLIC;
    }

    /**
     * Equivalent of:
     * wp_ajax_{action}
     * wp_ajax_nopriv_{action}
     */
    public function asPrivateAjax()
    {
        $this->routeType = self::ROUTE_TYPE_AJAX_PRIVATE;
    }
}