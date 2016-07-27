<?php namespace API;

require_once dirname(__FILE__) . '/authenticationDB.php';

use \Respect\Validation\Validator as v;
v::with('API\\Validation\\Rules');

class AuthenticationController extends RouteController {

    protected $SystemVariables;
    protected $AuthenticationDB;
   
    public function __construct(\Interop\Container\ContainerInterface $slimContainer) {
        parent::__construct($slimContainer);

        $this->SystemVariables = $slimContainer->get('SystemVariables');
        $this->AuthenticationDB = new AuthenticationDB($slimContainer->get('DBConn'));
    }
    
    public function isAuthenticated($request, $response, $args) {
        $post = $request->getParsedBody();

        // Validate sent params
        if(!v::key('apiKey', v::stringType())->validate($post) || 
           !v::key('apiToken', v::stringType())->validate($post)) {
            return $this->slimContainer->view->render($response, 401, array('authenticated' => false, 'msg' => 'Unauthenticated: Invalid request. Check your parameters and try again.'));
        }

        // Find user, validate password
        $user = $this->AuthenticationDB->selectUserByIdentifierToken($post['apiKey']);
        if(!$user) {
            return $this->slimContainer->view->render($response, 401, array('authenticated' => false, 'msg' => 'Unauthenticated: No User'));
        } else if (!password_verify($post['apiToken'], $user->apiToken)) {
            return $this->slimContainer->view->render($response, 401, array('authenticated' => false, 'msg' => 'Unauthenticated: Invalid Cookie'));
        }

        // Remove Api Key and Token from user object
        if(isset($user->apiKey)){ unset($user->apiKey); }
        if(isset($user->apiToken)) {  unset($user->apiToken); }

        // Return successful authenticated
        return $this->slimContainer->view->render($response, 200, array('authenticated' => true, 'user' => $user));
    }
    
    public function login($request, $response, $args) {
        $post = $request->getParsedBody();

        // If a logout token was sent, log out previous user
        $this->logoutToken($post);

        // Validate input parameters
        if(!v::key('testbool', v::POSTBooleanTrue())->validate($post)) {
            return $this->slimContainer->view->render($response, 401, array('authenticated' => false, 'msg' => 'testbool failed.', 'testbool' => $post['testbool']));
        }

        if(!v::key('email', v::email())->validate($post) || 
           !v::key('password', v::stringType())->validate($post)) {
            return $this->slimContainer->view->render($response, 401, array('authenticated' => false, 'msg' => 'Login failed. Check your parameters and try again.'));
        }

        // Validate the user email and password
        $user = $this->AuthenticationDB->selectUserAndPasswordByEmail($post['email']);
        if(!$user) {
            // Validate existing user
            return $this->slimContainer->view->render($response, 401, array('authenticated' => false, 'x' => $user, 'msg' => 'Login failed. A user with that email could not be found.'));
        } else if (!password_verify($post['password'], $user->password)) {
            // Validate Password
            return $this->slimContainer->view->render($response, 401, array('authenticated' => false, 'msg' => 'Login failed. Username and password combination did not match.'));
        }

        // Create logged in token
        $token = $this->createAuthToken($post, $user->id);
        if($token) {
            // User was found and has the correct password: Unset password
            unset($user->password);
            $user->apiKey = $token['apiKey'];
            $user->apiToken = $token['apiToken'];

            return $this->slimContainer->view->render($response, 200, array('authenticated' => true, 'user' => $user, 'sessionLifeHours' => $token['sessionLifeHours']));
        } else {
            return $this->slimContainer->view->render($response, 401, array('authenticated' => false, 'msg' => 'Login failed to create token.'));   
        }
    }
    
    private function createAuthToken($post, $userId) {
        // IF the remember flag was sent
        if(v::key('remember', v::stringType())->validate($post)) {
            $hours = intval($this->SystemVariables->get('AUTH_COOKIE_TIMEOUT_HOURS_REMEMBER'));
            $timeoutInHours = (!$hours) ? 24 : $hours;
        } else {
            $hours = intval($this->SystemVariables->get('AUTH_COOKIE_TIMEOUT_HOURS'));
            $timeoutInHours = (!$hours) ? 3 : $hours;
        }

        $token = array(
            'apiKey' => hash('sha512', uniqid()),
            'apiToken' => hash('sha512', uniqid()),
            'sessionLifeHours' => $timeoutInHours
        );

        // Save token to create session
        $saved = $this->AuthenticationDB->insertAuthToken(array(
            ':user_id' => $userId,
            ':identifier' => $token['apiKey'],
            ':token' => password_hash($token['apiToken'], PASSWORD_DEFAULT),
            ':ip_address' => $_SERVER['REMOTE_ADDR'],
            ':user_agent' => (!isset($_SERVER['HTTP_USER_AGENT'])) ? 'User Agent Not Set' : $_SERVER['HTTP_USER_AGENT'],
            ':expires' => date('Y-m-d H:i:s', time() + ($token['sessionLifeHours'] * 60 * 60))
        ));

        // Save login statistical info
        $this->AuthenticationDB->insertLoginLocation(array(
            ':user_id' => $userId,
            ':ip_address' => $_SERVER['REMOTE_ADDR'],
            ':user_agent' => (!isset($_SERVER['HTTP_USER_AGENT'])) ? 'User Agent Not Set' : $_SERVER['HTTP_USER_AGENT']
        ));

        return ($saved) ? $token : false;
    }
    
    public function facebookLogin($request, $response, $args) {
        $post = $request->getParsedBody();

        $result = AuthControllerFacebook::login($post);
        if ($result['authenticated']) {
            return $this->slimContainer->view->render($response, 200, $result);
        } else {
            return $this->slimContainer->view->render($response, 401, $result);
        }

        return $this->slimContainer->view->render($response, 200, 'facebook login');
    }
    
    public function logout($request, $response, $args) {
        $post = $request->getParsedBody();

        if ($this->logoutToken($post)) {
            return $this->slimContainer->view->render($response, 200, "User sucessfully logged out.");
        } else {
            return $this->slimContainer->view->render($response, 400, "User could not be logged out. Check your parameters and try again.");
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