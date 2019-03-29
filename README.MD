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

All public methods described in code with phpDocBlocks.

#### Quick Start

```php
use FahrradKruken\yawpRouter\Request;
use FahrradKruken\yawpRouter\Response;
use FahrradKruken\yawpRouter\RouteGroup;
use FahrradKruken\yawpRouter\Router;

// --
// -- Configuration at the first initiation
// --
$router = Router::init([
    // base path / prefix for all routes
    'basePath' => 'app/',
    
    // How to check nonce (internal nonce check - enabled by default): 
    // - FALSE - do not check nonce, disable internal check
    // - function($request, $response) - your own method to check nonce and do smth with that
    'actionCheckNonce' => null, 
    
    // If you have some ajax Routes, JSON object with routes will be included to scripts at
    // the top of the page. You can change the default object name
    'ajaxRouteMapName' => 'wp_route_map',
]);

// -- 
// -- ACTION CALLABLE
// -- 
$returnCurrentAction = function (Request $request, Response $response) {
    $response->data = $request->getParam('action');
    return $response; // Actions returns response
};

// -- 
// -- BEFOER ACTION CALLABLE
// -- 
$checkCapabilities = function (Request $request) {
    // some code to check user capabilities
    return $request; // before-Actions returns request
};

// -- 
// -- AFTER ACTION CALLABLE
// -- 
$someResponseFormatter = function (Response $response) {
    // some code to change Response
    return $response; // after-Actions returns response
};

// -- 
// -- You can use it as singleton or as an object
// -- 
Router::init()->route('info-basic', $returnCurrentAction)->asPublic();
$router->route('info-extended', $returnCurrentAction)->asPublic();

// -- 
// -- You can put routes to a groups
// -- 
$router->group('user', function (RouteGroup $userGroup) use ($returnCurrentAction) {
    $userGroup->route('', $returnCurrentAction)->asPublic(); // main group action. Let's imagine it returns all users
    $userGroup->route('view', $returnCurrentAction)->asPublic(); // like admin_post_nopriv_app/user/view
    $userGroup->route('update', $returnCurrentAction)->asPrivate(); // like admin_post_app/user/view
})->actionAfter($someResponseFormatter); // runs after each action in group

// -- 
// -- Or Even Put A group inside a Group (and make as deep routes tree as you want)
// -- 
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

// -- 
// -- After all routes enabled, just run this
// -- 
$router->dispatch();
```

#### What's next?

Let's see a simple example, where we creating 2 routes for some very custom page with form.

```php
Router::init()->group('some-form', function($formRouteGroup) {
    // -- 
    // -- One action will display form
    // -- 
    $formRouteGroup->route('display', function($req, $res) {
        $res->renderView('path/to/form-view.php', ['view' => 'params']);
        return $res;
    });
    
    // -- 
    // -- Other will process form
    // -- 
    $formRouteGroup->route('process', function($req, $res) {
        // do smth important
        return $res;
    });    
});
```

Than, if you need a link to your form page, yo will do something like:

```php
echo '<a href="' . Router::init()->getRouteInfo('/some-form/display')->urlWithNonce . '"></a>';
```

Than, when you creating the form:

```php
     
      $someRoute = Router::init()->getRouteInfo('/some-form/process');
     
      ?>
      <form type="post" action="<?= $someRoute->url ?>">
           <input type="hidden"
                   name="<?= $someRoute->nonceName ?>"
                   value="<?= $someRoute->nonce ?>"/>
           <!-- --- -->
           <!-- Rest of your form -->
           <!-- --- -->
      </form>
     
      <?php
```

#### What about Ajax Actions?

Object with al routes, urls, nonces etc. will be displayed on the top of your HTML page 
automatically after you'll add at least one AJAX route. Default Object name is
"wp_route_map".