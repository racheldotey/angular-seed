<?php namespace API;

/* Middleware to add or remove the trailing slash.
 * 
 * @author  Rachel L Carbone <hello@rachellcarbone.com> */

class RouteTrailingSlashMiddleware {
    /**
     * @var bool Add or remove the slash
     */
    private $addSlash;

    /**
     * Configure whether add or remove the slash.
     *
     * @param bool $addSlash
     */
    public function __construct($addSlash = false) {
        // If addSlash is true then all routes will have a trailing slash 
        // If addSlash is false then all routes will have no trailing slash
        $this->addSlash = (boolean) $addSlash;
    }

    /**
     * Add or Remove api request route trailing slash. "/users/" becomes "/users"
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next) {        
        $uri = $request->getUri();
        $path = $uri->getPath();

        // If this isnt the API root
        if ($path != '/') {
            
            if ($this->addSlash && substr($path, -1) !== '/') {
                // If were supposed to add the '/' and the path
                // doesn't already have the '/' as the last character
                $uri = $uri->withPath($path . '/');
            } else if (substr($path, -1) === '/') {
                // Remove the Slash
                // If we're supposed to remove the '/' and the path
                // does already have the '/' as the last character
                $uri = $uri->withPath(substr($path, 0, -1));
            }
            
        } else if ($path === '') {
            // Make sure the root has at least a trailing slash
            $uri = $uri->withPath('/');
        }
        
        if ($uri->getPath() !== $path) {
            // Permanently redirect paths with a trailing slash
            // to their non-trailing counterpart
            return $response->withRedirect((string)$uri, 301);
        }

        // Continue with route middleware
        return $next($request->withUri($uri->withPath($path)), $response);
    }
}