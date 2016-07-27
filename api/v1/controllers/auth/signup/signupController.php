<?php namespace API;

require_once dirname(__FILE__) . '/signupDB.php';

use \Respect\Validation\Validator as v;

class SignupController {

    protected $SystemVariables;
    protected $SignupDB;
    protected $passwordRegex;
    protected $passwordRegexDescription;
   
    public function __construct(\Interop\Container\ContainerInterface $slimContainer) {
        $this->SignupDB = new SignupDB($slimContainer->get('DBConn'));
        $this->SystemVariables = $slimContainer->get('SystemVariables');

        $this->passwordRegex = $this->SystemVariables->get('USER_PASSWORD_REGEX');
        $this->passwordRegexDescription = $this->SystemVariables->get('USER_PASSWORD_REGEX_DESCRIPTION');
    }
    
    public function signupb($request, $response, $args) {
        $post = $request->getParsedBody();
        
        $result = AuthControllerNative::signup($app);
        if ($result['registered']) {
            AuthHooks::signup($app, $result);
            if (isset($result['user']->teams[0])) {
                ApiMailer::sendWebsiteSignupJoinTeamConfirmation($result['user']->teams[0]->name, $result['user']->email, "{$result['user']->nameFirst} {$result['user']->nameLast}");
            } else {
                ApiMailer::sendWebsiteSignupConfirmation($result['user']->email, "{$result['user']->nameFirst} {$result['user']->nameLast}");
            }
            return $this->slimContainer->view->render($response, 200, $result);
        } else {
            return $this->slimContainer->view->render($response, 400, $result);
        }

        return $this->slimContainer->view->render($response, 200, 'signup');
    }
    

    // Signup Function
    public function signup($request, $response, $args) {
        $post = $request->getParsedBody();

        // Validate Sent Input
        if (!v::key('email', v::email())->validate($post) || !v::key('password', v::stringType())->validate($post)) {
            return $this->slimContainer->view->render($response, 400, array('registered' => false, 'msg' => 'Signup failed. Check your parameters and try again.'));
        } else if(!v::key('password', v::stringType()->regex($this->passwordRegex))->validate($post)) {
            return $this->slimContainer->view->render($response, 400, array('registered' => false, 'msg' => "Signup failed. {$this->passwordRegexDescription}"));
        }

        // Look for user with that email
        if($this->SignupDB->selectUserByEmail($post['email'])) { 
            /// FAIL - If a user with that email already exists
            return $this->slimContainer->view->render($response, 400, array('registered' => false, 'msg' => 'Signup failed. A user with that email already exists.'));        
        }

        // Create and insert a new user
        $userId = $this->SignupDB->insertUser(array(
            ':email' => $post['email'],
            ':name_first' => (v::key('nameFirst', v::stringType())->validate($post)) ? $post['nameFirst'] : '',
            ':name_last' => (v::key('nameLast', v::stringType())->validate($post)) ? $post['nameLast'] : '',
            ':phone' => (v::key('phone', v::stringType())->validate($post)) ? $post['phone'] : NULL,
            ':password' => password_hash($post['password'], PASSWORD_DEFAULT),
            ':accepted_terms' => (v::key('acceptTerms')->validate($post) && (bool)$post['acceptTerms']) ? 1 : 0,
            ':recieve_newsletter' => (v::key('acceptNewsletter')->validate($post) && (bool)$post['acceptNewsletter']) ? 1 : 0
            ));
        if(!$userId) {
            /// FAIL - If Inserting the user failed
            return $this->slimContainer->view->render($response, 400, array('registered' => false, 'msg' => 'Signup failed. Could not save user.'));
        }
        
        // Select our new user
        $user = $this->SignupDB->selectUserById($userId);
        if(!$user) { 
            /// FAIL - If Inserting the user failed (hopefully this is redundant)
            return $this->slimContainer->view->render($response, 400, array('registered' => false, 'msg' => 'Signup failed. Could not select user.'));    
        }
        
        // If a token was sent, update token status
        if(v::key('token', v::stringType())->validate($post)) {
            $inviteTeamId = $this->SignupDB->selectSignupInvite($post['token']);
            $this->SignupDB->updateAcceptSignupPlayerInvite(array(':user_id' => $userId, ':token' => $post['token']));
        }
         
        // Save "Where did you hear about us" and any other additional questions
        // This is "quiet" in that it may not execute if no paramters match
        // And it doesnt set the response for the api call
        InfoController::quietlySaveAdditional($post, $user->id);
        // Create an authorization
        $token = self::createAuthToken($app, $user->id);
        if($token) {
            // Create the return object
            $found = array('user' => $user);
            $found['user']->apiKey = $token['apiKey'];
            $found['user']->apiToken = $token['apiToken'];
            $found['sessionLifeHours'] = $token['sessionLifeHours'];
            $found['registered'] = true;
            return $found;
        } else {
            /// FAIL - If the auth token couldnt be created and saved
            return array('registered' => false, 'msg' => 'Signup failed to create auth token.');    
        }
    }


    public function facebookSignup($request, $response, $args) {
        
        $result = AuthControllerFacebook::signup($app);
        if ($result['registered']) {
            AuthHooks::signup($app, $result);
            if (isset($result['user']->teams[0])) {
                ApiMailer::sendWebsiteSignupJoinTeamConfirmation($result['user']->teams[0]->name, $result['user']->email, "{$result['user']->nameFirst} {$result['user']->nameLast}");
            } else {
                ApiMailer::sendWebsiteSignupConfirmation($result['user']->email, "{$result['user']->nameFirst} {$result['user']->nameLast}");
            }
            return $this->slimContainer->view->render($response, 200, $result);
        } else {
            return $this->slimContainer->view->render($response, 400, $result);
        }
        
        return $this->slimContainer->view->render($response, 200, 'facebookSignup');
    }

}