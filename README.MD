So, why do you need this thing?

The answer is - **you don't**. 

It's just a wrapper, a syntax sugar for standard wordpress functions 
like wp_ajax_{action} / wp_ajax_nopriv_{action} / admin_post_{action} / 
admin_post_nopriv_{action}.


## Installation

Through Composer:

```bash
composer install fahrradkrucken/yawp-router
```

If you don't use Composer:

```php
require '{path_to_router_files}/RouteInfo.php';
require '{path_to_router_files}/Route.php';
require '{path_to_router_files}/RouteGroup.php';
require '{path_to_router_files}/Request.php';
require '{path_to_router_files}/Response.php';
require '{path_to_router_files}/Router.php';
```

## Usage

#### Quick Start

```php
use FahrradKruken\yawpRouter\Request;
use FahrradKruken\yawpRouter\Response;
use FahrradKruken\yawpRouter\RouteGroup;
use FahrradKruken\yawpRouter\Router;

$router = Router::init([
    'basePath' => 'app/', // base path for all routes
]);

$returnCurrentAction = function (Request $request, Response $response) {
    $response->data = $request->getParam('action');
    return $response; // Actions returns response
};
$checkCapabilities = function (Request $request) {
    // some code to check user capabilities
    return $request; // before-Actions returns request
};
$someResponseFormatter = function (Response $response) {
    // some code to change Response
    return $response; // after-Actions returns response
};

$router->group('user', function (RouteGroup $userGroup) use ($returnCurrentAction) {
    $userGroup->route('', $returnCurrentAction)->asPublic(); // main group action. Let's imagine it returns all users
    $userGroup->route('view', $returnCurrentAction)->asPublic(); // like admin_post_nopriv_app/user/view
    $userGroup->route('update', $returnCurrentAction)->asPrivate(); // like admin_post_app/user/view
})->actionAfter($someResponseFormatter); // runs after each action in group

$router->group('api', function (RouteGroup $apiGroup) use ($returnCurrentAction, $checkCapabilities) {

    $apiGroup->group('project', function (RouteGroup $userGroup) use ($returnCurrentAction) {
        $userGroup->route('view', $returnCurrentAction)->asPublicAjax();
        $userGroup->route('view-all', $returnCurrentAction)->asPublicAjax();
    });
    
    $apiGroup->group('user', function (RouteGroup $userGroup) use ($returnCurrentAction) {
        $userGroup->route('view', $returnCurrentAction)->asPublicAjax(); // like wp_ajax_nopriv_app/api/user/view
        $userGroup->route('update', $returnCurrentAction)
            ->actionBefore(function ($request) {}) // runs only before this action
            ->asPrivateAjax(); // like wp_ajax_app/api/user/view
    })->actionBefore($checkCapabilities); // runs before each action in group
    
});

$router->dispatch();
```

#### Configuration

#### Routes

#### Route Groups

#### Before Action Handlers

#### After Action Handlers

#### Request

#### Response

#### Nonces

#### Routes Info

#### In Theme / Plugin

#### Ajax Route Map