<?php

namespace FahrradKruken\YAWP\Router;


interface RouteGroupInterface
{
    /**
     * Create new Route
     *
     * @param string $path
     * @param callable $action
     *
     * @return Route
     */
    public function route($path, $action);

    /**
     * Create New Route Group
     *
     * @param string $path
     * @param callable $groupAction
     *
     * @return RouteGroup
     */
    public function group($path, $groupAction);
}