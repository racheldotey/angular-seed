<?php namespace API;

require_once dirname(__FILE__) . '/signupDB.php';
require_once dirname(__FILE__) . '/signupEmails.php';

/* @author  Rachel L Carbone <hello@rachellcarbone.com> */

use \Respect\Validation\Validator as v;
v::with('API\\Validation\\Rules');

class SignupController extends RouteController {

    protected $SignupEmails;
    protected $SignupDB;
    protected $SystemVariables;
    protected $AuthSessionGenerator;
    protected $passwordRegex;
    protected $passwordRegexDescription;
   
    public function __construct(\Interop\Container\ContainerInterface $slimContainer) {
        parent::__construct($slimContainer);

        $this->SignupEmails = new SignupEmails($slimContainer->get('ApiDBConn'), $slimContainer->get('SystemVariables'), $slimContainer->get('ApiLogging'));

        $this->SignupDB = new SignupDB($slimContainer->get('ApiDBConn'));
        $this->SystemVariables = $slimContainer->get('SystemVariables');
        $this->AuthSessionGenerator = new AuthSessionGenerator($slimContainer->get('ApiDBConn'), $slimContainer->get('SystemVariables'), $slimContainer->get('ApiLogging'));

        $this->passwordRegex = $this->SystemVariables->get('USER_PASSWORD_REGEX');
        $this->passwordRegexDescription = $this->SystemVariables->get('USER_PASSWORD_REGEX_DESCRIPTION');
    }    

    // Signup Function
    public function signup($request, $response, $args) {
        $post = $request->getParsedBody();

        // Validate Sent Input
        if (!v::key('email', v::email())->validate($post) || !v::key('password', v::stringType())->validate($post)) {
            return $this->render($response, 400, array('registered' => false, 'msg' => 'Signup failed. Check your parameters and try again.'));
        } else if(!v::key('password', v::stringType()->regex($this->passwordRegex))->validate($post)) {
            return $this->render($response, 400, array('registered' => false, 'msg' => "Signup failed. {$this->passwordRegexDescription}"));
        }

        // Look for user with that email
        if($this->SignupDB->selectUserByEmail($post['email'])) { 
            /// FAIL - If a user with that email already exists
            return $this->render($response, 400, array('registered' => false, 'msg' => 'Signup failed. A user with that email already exists.'));        
        }

        // Create and insert a new user
        $userId = $this->SignupDB->insertUser(array(
            ':email' => $post['email'],
            ':name_first' => (v::key('nameFirst', v::stringType())->validate($post)) ? $post['nameFirst'] : '',
            ':name_last' => (v::key('nameLast', v::stringType())->validate($post)) ? $post['nameLast'] : '',
            ':phone' => (v::key('phone', v::stringType())->validate($post)) ? $post['phone'] : NULL,
            ':password' => password_hash($post['password'], PASSWORD_DEFAULT),
            ':accepted_terms' => (v::key('acceptTerms', v::POSTBooleanTrue())->validate($post)) ? 1 : 0,
            ':recieve_newsletter' => (v::key('acceptNewsletter', v::POSTBooleanTrue())->validate($post)) ? 1 : 0
            ));
        if(!$userId) {
            /// FAIL - If Inserting the user failed
            return $this->render($response, 400, array('registered' => false, 'msg' => 'Signup failed. Could not save user.'));
        }
        
        // Select our new user
        $user = $this->SignupDB->selectUserById($userId);
        if(!$user) { 
            /// FAIL - If Inserting the user failed (hopefully this is redundant)
            return $this->render($response, 400, array('registered' => false, 'msg' => 'Signup failed. Could not select user.'));    
        }
        
        // If a token was sent, update token status
        if(v::key('inviteToken', v::stringType())->validate($post)) {
            $this->SignupDB->updateAcceptSignupInvite(array(':user_id' => $userId, ':token' => $post['inviteToken']));
        }
         
        // Create an authorization
        $token = $this->AuthSessionGenerator->createAuthToken($post, $user->id);
        if($token) {
            // Create the return object
            $found = array('user' => $user);
            $found['user']->apiKey = $token['apiKey'];
            $found['user']->apiToken = $token['apiToken'];
            $found['sessionLifeHours'] = $token['sessionLifeHours'];
            $found['registered'] = true;
            
            $email = $this->SignupEmails->sendWebsiteSignupEmailConfirmation($found['user']->email, $found['user']->nameFirst, $found['user']->nameLast);
            if ($email['error']) {
                // log the error
                $this->ApiLogging->write("EMAIL FAILURE\n Params:" . json_encode($found), LOG_ALERT);
            }

            return $this->render($response, 200, $found);  
        } else {
            /// FAIL - If the auth token couldnt be created and saved
            return $this->render($response, 400, array('registered' => false, 'msg' => 'Signup failed to create auth token.'));     
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
            return $this->render($response, 200, $result);
        } else {
            return $this->render($response, 400, $result);
        }
        
        return $this->render($response, 200, 'facebookSignup');
    }

}