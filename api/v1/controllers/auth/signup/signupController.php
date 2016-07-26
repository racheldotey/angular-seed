<?php namespace API;

require_once dirname(__FILE__) . '/signupDB.php';

use \Respect\Validation\Validator as v;

class SignupController {

    protected $slimContainer;
   
    public function __construct(\Interop\Container\ContainerInterface $slimContainer) {
        $this->slimContainer = $slimContainer;
    }
    
    public function signup($request, $response, $args) {
        
        $result = AuthControllerNative::signup($app);
        if ($result['registered']) {
            AuthHooks::signup($app, $result);
            if (isset($result['user']->teams[0])) {
                ApiMailer::sendWebsiteSignupJoinTeamConfirmation($result['user']->teams[0]->name, $result['user']->email, "{$result['user']->nameFirst} {$result['user']->nameLast}");
            } else {
                ApiMailer::sendWebsiteSignupConfirmation($result['user']->email, "{$result['user']->nameFirst} {$result['user']->nameLast}");
            }
            return $app->render(200, $result);
        } else {
            return $app->render(400, $result);
        }

        return $this->slimContainer->view->render($response, 200, 'signup');
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
            return $app->render(200, $result);
        } else {
            return $app->render(400, $result);
        }
        
        return $this->slimContainer->view->render($response, 200, 'facebookSignup');
    }

}