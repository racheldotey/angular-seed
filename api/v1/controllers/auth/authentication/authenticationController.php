<?php namespace API;

require_once dirname(__FILE__) . '/authenticationDB.php';

/* @author  Rachel L Carbone <hello@rachellcarbone.com> */

use \Respect\Validation\Validator as v;

class AuthenticationController extends RouteController {

    protected $SystemVariables;
    protected $AuthenticationDB;
    protected $AuthSessionGenerator;
   
    public function __construct(\Interop\Container\ContainerInterface $slimContainer) {
        parent::__construct($slimContainer);

        $this->SystemVariables = $slimContainer->get('SystemVariables');
        $this->AuthenticationDB = new AuthenticationDB($slimContainer->get('ApiDBConn'));
        $this->AuthSessionGenerator = new AuthSessionGenerator($slimContainer->get('ApiDBConn'), $slimContainer->get('SystemVariables'), $slimContainer->get('ApiLogging'));
    }
    
    public function isAuthenticated($request, $response, $args) {
        $post = $request->getParsedBody();

        // Validate sent params
        if(!v::key('apiKey', v::stringType())->validate($post) || 
            !v::key('apiToken', v::stringType())->validate($post)) {
            return $this->render($response, 401, array('authenticated' => false, 'msg' => 'Unauthenticated: Invalid request. Check your parameters and try again.'));
        }

        // Find user, validate password
        $user = $this->AuthenticationDB->selectUserByIdentifierToken($post['apiKey']);
        if(!$user) {
            return $this->render($response, 401, array('authenticated' => false, 'msg' => 'Unauthenticated: No User'));
        } else if (!password_verify($post['apiToken'], $user->apiToken)) {
            return $this->render($response, 401, array('authenticated' => false, 'msg' => 'Unauthenticated: Invalid Cookie'));
        }

        // Remove Api Key and Token from user object
        if(isset($user->apiKey)) { unset($user->apiKey); }
        if(isset($user->apiToken)) {  unset($user->apiToken); }

        // Return successful authenticated
        return $this->render($response, 200, array('authenticated' => true, 'user' => $user));
    }
    
    public function login($request, $response, $args) {
        $post = $request->getParsedBody();

        // If a logout token was sent, log out previous user
        $this->logoutToken($post);

        // Validate input parameters
        if(!v::key('email', v::email())->validate($post) || 
           !v::key('password', v::stringType())->validate($post)) {
            return $this->render($response, 401, array('authenticated' => false, 'msg' => 'Login failed. Check your parameters and try again.'));
        }

        // Validate the user email and password
        $user = $this->AuthenticationDB->selectUserAndPasswordByEmail($post['email']);
        if(!$user) {
            // Validate existing user
            return $this->render($response, 401, array('authenticated' => false, 'x' => $user, 'msg' => 'Login failed. A user with that email could not be found.'));
        } else if (!password_verify($post['password'], $user->password)) {
            // Validate Password
            
            $attempts = $this->SystemVariables->get('AUTH_MAX_LOGIN_ATTEMPTS');
            $minutes = $this->SystemVariables->get('AUTH_MAX_LOGIN_ATTEMPTS_TIMEOUT_MINUTES');

            $result = array(
                'authenticated' => false, 
                'msg' => 'Login failed. Username and password combination did not match.',
                'attempts' => ($attempts) ? intval($attempts) : 0,
                'timeoutMin' => ($minutes) ? intval($minutes) : 0
            );
            return $this->render($response, 401, $result);
        }

        // Create logged in token
        $token = $this->AuthSessionGenerator->createAuthToken($post, $user->id);
        if($token) {
            // User was found and has the correct password: Unset password
            unset($user->password);
            $user->apiKey = $token['apiKey'];
            $user->apiToken = $token['apiToken'];

            return $this->render($response, 200, array('authenticated' => true, 'user' => $user, 'sessionLifeHours' => $token['sessionLifeHours']));
        } else {
            return $this->render($response, 401, array('authenticated' => false, 'msg' => 'Login failed to create token.'));   
        }
    }
    
    public function facebookLogin($request, $response, $args) {
        $post = $request->getParsedBody();

        $result = AuthControllerFacebook::login($post);
        if ($result['authenticated']) {
            return $this->render($response, 200, $result);
        } else {
            return $this->render($response, 401, $result);
        }

        return $this->render($response, 200, 'facebook login');
    }
    
    public function logout($request, $response, $args) {
        $post = $request->getParsedBody();

        if ($this->logoutToken($post)) {
            return $this->render($response, 200, "User sucessfully logged out.");
        } else {
            return $this->render($response, 400, "User could not be logged out. Check your parameters and try again.");
        }
    }

    private function logoutToken($post) {
        if(v::key('logout', v::stringType())->validate($post)) {
            $this->AuthenticationDB->deleteAuthToken(array(':identifier' => $post['logout']));
            return true;
        }
        return false;
    }
}