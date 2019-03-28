<?php
/**
 * Created by PhpStorm.
 * User: Sebastian
 * Date: 024 24.03.2019
 * Time: 17:11
 */

namespace FahrradKruken\yawpRouter;


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

    public function __construct($path = '', $action = null)
    {
        $this->path = $path;
        $this->action = $action;
        $this->routeInfo = new RouteInfo();
        $this->routeInfo->path = $this->path;
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

    // --
    // -- Set Route Type
    // --

    public function asPublic()
    {
        $this->routeType = self::ROUTE_TYPE_PUBLIC;
    }

    public function asPrivate()
    {
        $this->routeType = self::ROUTE_TYPE_PRIVATE;
    }

    public function asPublicAjax()
    {
        $this->routeType = self::ROUTE_TYPE_AJAX_PUBLIC;
    }

    public function asPrivateAjax()
    {
        $this->routeType = self::ROUTE_TYPE_AJAX_PRIVATE;
    }
}