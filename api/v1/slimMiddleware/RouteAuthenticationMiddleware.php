<?php namespace API;

/* @author  Rachel L Carbone <hello@rachellcarbone.com> */

class RouteAuthenticationMiddleware {
    
    const APISESSIONNAME = 'API_AUTHENTICATED_USER_ID';

    /**
     * Slim PHP Middleware to turn API Responses into a clean formatted JSON oject.
     *
     * To use this class as a middleware, you can use ->add( new ExampleMiddleware() ); 
     * function chain after the $app, Route, or group(), which in the code below, 
     * any one of these, could represent $subject.
     * 
     * $subject->add( new ExampleMiddleware() );
     *
     * http://www.slimframework.com/docs/concepts/middleware.html
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        $response->getBody()->write('BEFORE');
        $response = $next($request, $response);
        $response->getBody()->write('AFTER');

        return $response;
    }
    
    static function isAuthorized($app, $role = 'public') {
        $user = self::authorizeApiToken($app);
        if($user) {
            // Save that user id
            $_SESSION[self::APISESSIONNAME] = $user;
            return true;
        } else if(strtolower($role) === 'public') {
            return true;
        } else {
            $response = array('data' => array(
                'msg' => 'Unauthorized API Access', 
                'sent' => $app->request->post()), 
                'user' => $user,
                'meta' => array('error' => true, 'status' => 401));
            //$response = array('data' => array('msg' => 'Unauthorized API Access'), 'meta' => array('error' => true, 'status' => 401));
            $app->halt(401, json_encode($response));
            return false;
        }
    }
    
    private static function authorizeApiToken($app) {
        if(!v::key('apiKey', v::stringType())->validate($app->request->post()) || 
           !v::key('apiToken', v::stringType())->validate($app->request->post())) {
            return false;
        }
        $user = AuthData::selectUserByIdentifierToken($app->request->post('apiKey'));
        if(!$user) {
            return "user";
        }
        if(!password_verify($app->request->post('apiToken'), $user->apiToken)) {
            return "password";
        }
        // Go now. Be free little brother.
        return $user->id;
    }
    
    static function getUserId() {        
        return (isset($_SESSION[self::APISESSIONNAME])) ? $_SESSION[self::APISESSIONNAME] : '0';
    }
}