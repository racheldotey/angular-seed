<?php
namespace API;

// Custom Respect Validators
require_once dirname(dirname(__FILE__)) . '/customValidators/POSTBooleanTrue.php';

// API Abstract Classes
require_once dirname(dirname(__FILE__)) . '/abstractClasses/RouteController.php';
require_once dirname(dirname(__FILE__)) . '/abstractClasses/RouteDBController.php';
require_once dirname(dirname(__FILE__)) . '/abstractClasses/RouteEmailsController.php';

// Services (common methods used by controllers)
require_once dirname(dirname(__FILE__)) . '/services/ApiConfig.php';                // API Coifg File (Add your settings!)
require_once dirname(dirname(__FILE__)) . '/services/ApiDBConn.php';                // API DB Conection File
require_once dirname(dirname(__FILE__)) . '/services/ApiLogging.php';               // Router Module
require_once dirname(dirname(__FILE__)) . '/services/AuthSessionGenerator.php';     // Login Session Generator used by several controllers
require_once dirname(dirname(__FILE__)) . '/services/EmailService.php';             // Email Sender
require_once dirname(dirname(__FILE__)) . '/services/SystemVariables.php';          // System Variable from the database

// Custom Slim PHP Middleware
require_once dirname(dirname(__FILE__)) . '/slimMiddleware/ApiAuthMiddleware.php';  // Slim PHP Middleware to authenticate incomming requests for individual routes
require_once dirname(dirname(__FILE__)) . '/slimMiddleware/JsonResponseView.php';   // Response middleware to neatly format API responses to JSON

// API Route Controllers 
require_once dirname(__FILE__) . '/auth/auth.routes.php';

use Psr7Middlewares\Middleware\TrailingSlash;
use Psr7Middlewares\Middleware\Gzip;

/* @author  Rachel L Carbone <hello@rachellcarbone.com> */

class V1Controller {

    public function run() {
        /* Get our api config */
        $apiConfig = new ApiConfig();
        $debug = $apiConfig->get('debugMode');
        
        /* Create new Slim PHP API App */
        // http://www.slimframework.com/docs/objects/application.html       
        $slimSettings = $this->getSlimConfig($debug);
        $slimContainer = $this->getSlimContainer($slimSettings, $debug);
        $slimApp = new \Slim\App($slimContainer);

        /* 301 redirect routes with trailing slashes to the non slashed option ("/user" instead of "/user/") */
        $slimApp->add(new \Psr7Middlewares\Middleware\TrailingSlash(false)); // true adds the trailing slash (false removes it)
        //$slimApp->add(new \Psr7Middlewares\Middleware\Gzip()); 

        /* Add API Routes */
        $this->addDefaultRoutes($slimApp, $slimContainer);
        $this->addApiRoutes($slimApp);

        /* Start Slim */
        $slimApp->run();
    }

    private function getSlimConfig($debugEnabled) {
        /* Slim PHP Congif Settings */
        // http://www.slimframework.com/docs/objects/application.html

        $slimSettings = [
            'settings' => [
                /* If false, then no output buffering is enabled. If 'append' or 'prepend', 
                 * then any echo or print statements are captured and are either appended 
                 * or prepended to the Response returned from the route callable. 
                 * (Default: 'append') */
                'outputBuffering' => 'append',
                /* When true, the route is calculated before any middleware is executed. 
                 * This means that you can inspect route parameters in middleware if you need to. 
                 * (Default: false) */
                'determineRouteBeforeAppMiddleware' => true,
                /* When true, additional information about exceptions are displayed by the default error handler. 
                 * (Default: false) */
                'displayErrorDetails' => true,
                /* Filename for caching the FastRoute routes. Must be set to to a valid filename 
                 * within a writeable directory. If the file does not exist, then it is created 
                 * with the correct cache information on first run.
                 * Set to false to disable the FastRoute cache system. 
                 * (Default: false) */
                'routerCacheFile' => false,
                /* When true, Slim will add a Content-Length header to the response. If you are using a 
                 * runtime analytics tool, such as New Relic, then this should be disabled. 
                 * (Default: true) */
                'addContentLengthHeader' => true
            ]
        ];

        /* Dev Mode / Debug Settings */
        /* if($debugEnabled) { } */
        
        return $slimSettings;
    }

    private function getSlimContainer($slimSettings, $debugEnabled) {
        /* Create new Slim PHP Dependency Container */
        // http://www.slimframework.com/docs/concepts/di.html
        $slimContainer = new \Slim\Container($slimSettings);

        /* Add app services to the container */

        $slimContainer['ApiConfig'] = function($this) {
            // Return the System Config Class */
            return new \API\ApiConfig();
        };

        $slimContainer['ApiLogging'] = function($container) {
            // Set PHP Error Handler to ApiLogging */
            return new \API\ApiLogging($container->get('ApiConfig'), 'api');
        };

        $slimContainer['ApiDBConn'] = function($container) {
            // Return the Database Connection Class */
            return new \API\ApiDBConn($container->get('ApiConfig'), $container->get('ApiLogging'));
        };

        $slimContainer['SystemVariables'] = function($container) {
            // Return the System Config Class
            return new \API\SystemVariables($container->get('ApiDBConn'), $container->get('ApiLogging'));
        }; 

        
        /* Add our JSON Response view middleware 
         *
         * Allows us to use $this->view->render($response, 200, 'Data to return'); inside of routes.
         */
        $slimContainer['view'] = new \API\JsonResponseView($slimContainer);

        /* Dev Mode / Debug Settings */
        /* if(!$debugEnabled) { 
            $this->addErrorHandlers($slimContainer);
        } */

        $this->addErrorHandlers($slimContainer);
        
        return $slimContainer;
    }
    
    private function addApiRoutes(\Slim\App $slimApp) {
        // Authentication
        $authRoutes = new AuthRoutes();
        $authRoutes->addRoutes($slimApp);

        /*
        ActionRoutes::addRoutes($slimApp, $authenticateForRole);
        DatatableRoutes::addRoutes($slimApp, $authenticateForRole);
        EmailRoutes::addRoutes($slimApp, $authenticateForRole);
        FieldRoutes::addRoutes($slimApp, $authenticateForRole);
        GroupRoutes::addRoutes($slimApp, $authenticateForRole);
        RoleRoutes::addRoutes($slimApp, $authenticateForRole);
        ListRoutes::addRoutes($slimApp, $authenticateForRole);
        SystemRoutes::addRoutes($slimApp, $authenticateForRole);
        ConfigRoutes::addRoutes($slimApp, $authenticateForRole);
        UserRoutes::addRoutes($slimApp, $authenticateForRole);
        */
    }
    
    private function addDefaultRoutes(\Slim\App $slimApp, \Interop\Container\ContainerInterface $slimContainer) {
        $slimApp->any('/', function ($request, $response, $args) {
            return $this->view->render($response, 200, 'Congratulations, you have reached the Slim PHP API v1.1!');
        })->add(new \API\ApiAuthMiddleware($slimContainer, 'admin'));
        
        $slimApp->any('/about', function ($request, $response, $args) {
            $data = array(
                'title' => $this->ApiConfig->get('repoTitle'),
                'version' => $this->ApiConfig->get('apiVersion'),
                'codeRepoUrl' => $this->ApiConfig->get('codeRepoUrl'),
                'author' => $this->ApiConfig->get('author'),
                'authorWebsite' => $this->ApiConfig->get('authorWebsite')
            );
            return $this->view->render($response, 200, $data);
        })->add(new \API\ApiAuthMiddleware($slimContainer, 'public'));
        
    }
    
    private function addErrorHandlers(\Interop\Container\ContainerInterface $slimContainer) {
        /*
         * Override the default Not Found Handler
         *
         * http://www.slimframework.com/docs/handlers/error.html
         */
        $slimContainer['errorHandler'] = function ($container) {
            return function ($request, $response, $exception) use ($container) {
                // Build log message
                $msg = '[500] System Error';
                // https://github.com/php-fig/http-message/blob/master/src/ServerRequestInterface.php
                if($request->getAttribute('routeInfo')) {
                    $req = $request->getAttribute('routeInfo');
                    // Add the type (GET, POST, etc) and Route (http://api.seed.com/path/path)
                    if(isset($req['request'])) {
                        $type = (isset($req['request'][0])) ? $req['request'][0] : 'unknown';
                        $path = (isset($req['request'][1])) ? $req['request'][1] : 'unknown';
                        $msg .= " - [{$type}] $path";
                    }
                }
                // Log the 500
                $container->ApiLogging->log($msg, LOG_ERR, 'api_errors');
                $container->ApiLogging->logException($exception, LOG_ERR, 'api_errors');

                // Return nice JSON 500 Message
                return $container['view']->render($response, 500, 'Unknown System Error');
            };
        };

        /*
         * If your Slim Framework application has a route that matches the current HTTP request URI 
         * but NOT the HTTP request method, the application invokes its Not Allowed handler and 
         * returns a HTTP/1.1 405 Not Allowed response to the HTTP client.
         *
         * http://www.slimframework.com/docs/handlers/not-allowed.html
         */
        $slimContainer['notAllowedHandler'] = function ($container) {
            return function ($request, $response, $methods) use ($container) {                    
                // Build log message
                $msg = '[405] Invalid method requested for app route.';
                // https://github.com/php-fig/http-message/blob/master/src/ServerRequestInterface.php
                if($request->getAttribute('routeInfo')) {
                    $req = $request->getAttribute('routeInfo');
                    // Add the type (GET, POST, etc) and Route (http://api.seed.com/path/path)
                    if(isset($req['request'])) {
                        $type = (isset($req['request'][0])) ? $req['request'][0] : 'unknown';
                        $path = (isset($req['request'][1])) ? $req['request'][1] : 'unknown';
                        $msg .= " - [{$type}] $path";
                    }
                }
                $msg .= ' Accepted Methods: ' . implode(', ', $methods);

                // Log the 405
                $container->ApiLogging->log($msg, LOG_DEBUG);

                // Return nice JSON 405 Message
                return $container['view']->render($response, 405, 'This header method is not defined for this route. Accepted method(s) are: ' . implode(', ', $methods));
            };
        };

        /*
         * Override the default Not Found Handler
         *
         * http://www.slimframework.com/docs/handlers/not-found.html
         */
        $slimContainer['notFoundHandler'] = function ($container) {
            return function ($request, $response) use ($container) {
                // Build log message
                $msg = '[404] Undefined app route was requested';
                // https://github.com/php-fig/http-message/blob/master/src/ServerRequestInterface.php
                if($request->getAttribute('routeInfo')) {
                    $req = $request->getAttribute('routeInfo');
                    // Add the type (GET, POST, etc) and Route (http://api.seed.com/path/path)
                    if(isset($req['request'])) {
                        $type = (isset($req['request'][0])) ? $req['request'][0] : 'unknown';
                        $path = (isset($req['request'][1])) ? $req['request'][1] : 'unknown';
                        $msg .= " - [{$type}] $path";
                    }
                }
                // Log the 404
                $container->ApiLogging->log($msg, LOG_DEBUG);

                // Return nice JSON 404 Message
                return $container['view']->render($response, 404, 'This API route is not defined.');
            };
        };

        return $slimContainer;
    }
}