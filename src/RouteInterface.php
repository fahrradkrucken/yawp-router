<?php

namespace FahrradKruken\YAWP\Router;


interface RouteInterface
{
    const ROUTE_TYPE_PUBLIC = 'public';
    const ROUTE_TYPE_PRIVATE = 'private';
    const ROUTE_TYPE_AJAX_PUBLIC = 'ajax_public';
    const ROUTE_TYPE_AJAX_PRIVATE = 'ajax_private';

    /**
     * Add callable before your action. This callable will accept 1 argument - Request Instance, and MUST return it
     *
     * @param callable $middleWareCallable
     *
     * @return RouteGroup
     */
    public function actionBefore($middleWareCallable);

    /**
     * Add callable after your action. This callable will accept 1 argument - Response Instance, and MUST return it
     *
     * @param callable $middleWareCallable
     *
     * @return RouteGroup
     */
    public function actionAfter($middleWareCallable);

    // --
    // -- Set Route Type
    // --

    /**
     * Equivalent of:
     * admin_post_nopriv_{action}
     */
    public function asPublic();

    /**
     * Equivalent of:
     * admin_post_{action}
     * admin_post_nopriv_{action}
     */
    public function asPrivate();

    /**
     * Equivalent of:
     * wp_ajax_nopriv_{action}
     */
    public function asPublicAjax();

    /**
     * Equivalent of:
     * wp_ajax_{action}
     * wp_ajax_nopriv_{action}
     */
    public function asPrivateAjax();
}