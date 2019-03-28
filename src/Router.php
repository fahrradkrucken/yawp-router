<?php
/**
 * Created by PhpStorm.
 * User: Sebastian
 * Date: 023 23.03.2019
 * Time: 17:48
 */

namespace FahrradKruken\yawpRouter;

class Router
{
    /**
     * @var Router|null
     */
    private static $instance = null;
    /**
     * @var RouteGroup
     */
    private $rootRouteGroup = [];
    /**
     * @var Route[]
     */
    private $routeMap = [];

    /**
     * @var callable[]
     */
    private $actionsBefore = [];
    /**
     * @var callable[]
     */
    private $actionsAfter = [];
    /**
     * @var null|false|callable
     */
    private $actionCheckNonce = null;
    /**
     * @var array
     */
    private $ajaxRouteMap = [];
    /**
     * @var string
     */
    private $ajaxRouteMapName = 'wp_route_map';

    private function __construct($config = [])
    {
        $routerConfig = wp_parse_args($config, [
            'basePath' => '/',
            'actionCheckNonce' => null,
            'ajaxRouteMapName' => 'wp_route_map',
        ]);

        $this->rootRouteGroup = new RouteGroup($routerConfig['basePath']);
        $this->actionCheckNonce = $routerConfig['actionCheckNonce'];
        $this->ajaxRouteMapName = $routerConfig['ajaxRouteMapName'];
    }

    private function __clone() { }

    // --
    // -- Router Methods for Routes Creation
    // --

    /**
     * @param array $config
     *
     * [
     *      'basePath' => '/',
     *      'actionCheckNonce' => null,
     *      'ajaxRouteMapName' => 'wp_route_map',
     * ]
     *
     * @return Router
     */
    public static function init($config = [])
    {
        return empty(self::$instance) ? (self::$instance = new self($config)) : self::$instance;
    }

    /**
     * @param $path
     * @param $action
     *
     * @return Route
     */
    public function route($path, $action)
    {
        return $this->rootRouteGroup->route($path, $action);
    }

    /**
     * @param $path
     * @param $groupAction
     *
     * @return RouteGroup
     */
    public function group($path, $groupAction)
    {
        return $this->rootRouteGroup->group($path, $groupAction);
    }

    /**
     * @param $middleWareCallable
     *
     * @return RouteGroup
     */
    public function actionBefore($middleWareCallable)
    {
        $this->rootRouteGroup->actionBefore($middleWareCallable);
        return $this->rootRouteGroup;
    }

    /**
     * @param $middleWareCallable
     *
     * @return RouteGroup
     */
    public function actionsAfter($middleWareCallable)
    {
        $this->rootRouteGroup->actionAfter($middleWareCallable);
        return $this->rootRouteGroup;
    }

    /**
     * Start Routes Dispatching
     */
    public function dispatch()
    {
        add_action('init', [$this, '__dispatch']);
    }

    // --
    // -- Router Useful Methods
    // --

    /**
     * @param string $path
     *
     * @return false|RouteInfo
     */
    public function getRouteInfo($path = '')
    {
        if (!empty($this->routeMap)) {
            $routeKey = md5($path);
            if (!empty($this->routeMap[$routeKey]))
                return $this->routeMap[$routeKey]->routeInfo;
        }
        return false;
    }


    // --
    // -- Router Methods for WP
    // --

    public function __dispatch()
    {
        $this->makeRouteActionsInheritance($this->rootRouteGroup);
        $this->createRouteMap($this->rootRouteGroup);

        if (!empty($this->routeMap)) {
            foreach ($this->routeMap as $routeKey => $route) {
                if (!is_callable($route->action) || empty($route->path)) continue;
                switch ($route->routeType) {
                    case Route::ROUTE_TYPE_PUBLIC:
                        add_action('admin_post_' . $route->path, [$this, '__doAction']);
                        add_action('admin_post_nopriv_' . $route->path, [$this, '__doAction']);
                        break;
                    case Route::ROUTE_TYPE_PRIVATE:
                        add_action('admin_post_' . $route->path, [$this, '__doAction']);
                        break;
                    case Route::ROUTE_TYPE_AJAX_PUBLIC:
                        if (wp_doing_ajax()) {
                            add_action('wp_ajax_' . $route->path, [$this, '__doAction']);
                            add_action('wp_ajax_nopriv_' . $route->path, [$this, '__doAction']);
                        }
                        $this->ajaxRouteMap[$route->path] = $route->routeInfo;
                        break;
                    case Route::ROUTE_TYPE_AJAX_PRIVATE:
                        if (wp_doing_ajax())
                            add_action('wp_ajax_' . $route->path, [$this, '__doAction']);
                        $this->ajaxRouteMap[$route->path] = $route->routeInfo;
                        break;
                }
            }

            if (!empty($this->ajaxRouteMap)) {
                add_action('wp_print_scripts', [$this, '__attachAjaxRouteMap']);
            }
        }
    }

    public function __attachAjaxRouteMap()
    {
        ob_start(); ?>
        <script id="wp-ajax-route-map">
            ;var <?= $this->ajaxRouteMapName ?> = <?= json_encode($this->ajaxRouteMap,
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT | JSON_PRETTY_PRINT) ?>;
        </script>
        <?php $res = ob_get_clean();
        echo $res;
    }

    public function __doAction()
    {
        $path = filter_var($_REQUEST['action'], FILTER_SANITIZE_STRING);
        $routeKey = md5($path);

        if (empty($this->routeMap[$routeKey])) return;

        $currentRoute = $this->routeMap[$routeKey];

        $request = new Request($currentRoute);
        $response = new Response();

        $this->runActionCheckNonce($request, $response);

        if (!empty($currentRoute->actionsBefore)) {
            foreach ($currentRoute->actionsBefore as $callable) {
                if (is_callable($callable)) {
                    $modifiedRequest = call_user_func($callable, $request);
                    if ($modifiedRequest instanceof Request) $request = $modifiedRequest;
                }
            }
        }

        $modifiedResponse = call_user_func($currentRoute->action, $request, $response);
        if ($modifiedResponse instanceof Response) $response = $modifiedResponse;

        if (!empty($currentRoute->actionsAfter)) {
            foreach ($currentRoute->actionsAfter as $callable) {
                if (is_callable($callable)) {
                    $modifiedResponse = call_user_func($callable, $response);
                    if ($modifiedResponse instanceof Response) $response = $modifiedResponse;
                }
            }
        }

        $response->send();

        die();
    }

    // --
    // -- Router Internal Methods
    // --

    /**
     * @param Request  $request
     * @param Response $response
     */
    private function runActionCheckNonce($request, $response)
    {
        if ($this->actionCheckNonce === false) {
            return;
        } elseif (is_callable($this->actionCheckNonce)) {
            call_user_func($this->actionCheckNonce, $request, $response);
        } else {
            $nonceName = $request->getCurrentRoute()->routeInfo->nonceName;
            $nonce = $request->getParam($nonceName);

            if (!wp_verify_nonce($nonce, $nonceName)) {
                $response->data = new \WP_Error(Response::STATUS_UNAUTHORIZED, 'NONCE ERROR');
                $response->send();
            }
        }
    }

    /**
     * Creates RoutesMap From Routes Tree and adds RouteInfo to each Route
     *
     * @param RouteGroup $group
     */
    private function createRouteMap($group)
    {
        if (empty($group->routes) || !($group instanceof RouteGroup)) return;

        foreach ($group->routes as $routeKey => &$route) {
            if ($route instanceof RouteGroup) {
                $this->createRouteMap($route);
            } elseif ($route instanceof Route) {
                $this->routeMap[$routeKey] = $route;
                $this->routeMap[$routeKey]->routeInfo->url = $this->getRouteUrl($this->routeMap[$routeKey]);
                $this->routeMap[$routeKey]->routeInfo->nonceName = '_nonce_' . $routeKey;
                $this->routeMap[$routeKey]->routeInfo->nonce = wp_create_nonce($this->routeMap[$routeKey]->routeInfo->nonceName);
                $this->routeMap[$routeKey]->routeInfo->urlWithNonce = $this->getRouteUrl($this->routeMap[$routeKey],
                    [$this->routeMap[$routeKey]->routeInfo->nonceName => $this->routeMap[$routeKey]->routeInfo->nonce]);
            }
        }
    }

    /**
     * Applies normal inheritance of actionsBefore & actionsAfter for Routes inside RouteGroups
     *
     * @param RouteGroup $group
     */
    private function makeRouteActionsInheritance(&$group)
    {
        if (empty($group->routes) || !($group instanceof RouteGroup)) return;

        if (!empty($group->actionsBefore))
            foreach ($group->routes as &$route)
                $route->actionsBefore = array_merge($group->actionsBefore, $route->actionsBefore);
        if (!empty($group->actionsAfter))
            foreach ($group->routes as &$route)
                $route->actionsAfter = array_merge($route->actionsAfter, $group->actionsAfter);

        foreach ($group->routes as $routeKey => &$route)
            if ($route instanceof RouteGroup)
                $this->makeRouteActionsInheritance($route);
    }

    /**
     * @param Route $route
     * @param array urlParams
     *
     * @return string
     */
    private function getRouteUrl($route, $urlParams = [])
    {
        $urlRoot = ($route->routeType === Route::ROUTE_TYPE_PUBLIC || $route->routeType === Route::ROUTE_TYPE_PRIVATE) ?
            'admin-post.php' : 'admin-ajax.php';
        $urlParams['action'] = $route->path;
        return admin_url($urlRoot . '?' . http_build_query($urlParams));
    }
}