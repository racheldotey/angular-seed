<?php namespace API;

require_once dirname(__FILE__) . '/updateUserDB.php';
require_once dirname(__FILE__) . '/updateUserEmails.php';

/* @author  Rachel L Carbone <hello@rachellcarbone.com> */

use \Respect\Validation\Validator as v;
v::with('API\\Validation\\Rules');

class UpdateUserController extends RouteController {

    protected $UpdateUserDB;
    protected $SystemVariables;
    protected $passwordRegex;
    protected $passwordRegexDescription;
   
    public function __construct(\Interop\Container\ContainerInterface $slimContainer) {
        parent::__construct($slimContainer);

        $this->UpdateUserDB = new UpdateUserDB($slimContainer->get('ApiDBConn'));

        $this->SystemVariables = $slimContainer->get('SystemVariables');
        $this->passwordRegex = $this->SystemVariables->get('USER_PASSWORD_REGEX');
        $this->passwordRegexDescription = $this->SystemVariables->get('USER_PASSWORD_REGEX_DESCRIPTION');
    }  
    
    public function adminInsertUser($app) {
        if(!v::key('nameFirst', v::stringType()->length(0,255))->validate($app->request->post()) ||
            !v::key('nameLast', v::stringType()->length(0,255), false)->validate($app->request->post()) || 
            !v::key('email', v::email())->validate($app->request->post())) {
            return $app->render(400,  array('msg' => 'Invalid user. Check your parameters and try again.'));
        } else if(!self::validatePassword($app->request->post())) {
            return $app->render(400,  array('msg' => "Passwords must be at least 8 characters "
                    . "long, contain no whitespace, have at least one letter and one number. "
                    . "Check your parameters and try again."));
        } 
        
        $found = $this->UpdateUserDB->selectOtherUsersWithEmail($app->request->post('email'));
        
        if ($found && count($found) > 0) {
            return $app->render(400,  array('msg' => 'An account with that email already exists. No two users may have the same email address.'));
        } else {
            $data = array(
                ':name_first' => $app->request->post('nameFirst'),
                ':name_last' => $app->request->post('nameLast'),
                ':email' => $app->request->post('email'),
                ':password' => password_hash($app->request->post('password'), PASSWORD_DEFAULT)
            );
            $userId = $this->UpdateUserDB->insertUser($data);
            $user = $this->UpdateUserDB->selectUserById($userId);
            return $app->render(200, array('user' => $user ));
        }
    }
    
    private function validatePassword($post, $key = 'password') {
        return (v::key($key, v::stringType()->length(8,255)->noWhitespace()->alnum('!@#$%^&*_+=-')->regex('/^(?=.*[a-zA-Z])(?=.*[0-9])/'))->validate($post));
    }

    public function updateUser($app, $userId) {
        $post = $app->request->post();
        if(!v::intVal()->validate($userId) ||
            !v::key('nameFirst', v::stringType()->length(0,255))->validate($post) ||
            !v::key('nameLast', v::stringType()->length(0,255), false)->validate($post) || 
            !v::key('phone', v::stringType()->length(0,20), false)->validate($post) || 
            !v::key('email', v::email())->validate($post)) {
            return $app->render(400,  array('msg' => 'Invalid user. Check your parameters and try again.'));
        } 
        
        $found = $this->UpdateUserDB->selectOtherUsersWithEmail($post['email'], $userId);
        
        if ($found && count($found) > 0) {
            return $app->render(400,  array('msg' => 'An account with that email already exists. No two users may have the same email address.'));
        }
        
        $data = array(
            ':id' => $userId,
            ':name_first' => $post['nameFirst'],
            ':name_last' => $post['nameLast'],
            ':email' => $post['email'],
            ':phone' => $post['phone']
        );
        $this->UpdateUserDB->updateUser($data);
        
        if((v::key('disabled', v::stringType()->length(1,5))->validate($post)) && 
                ($post['disabled'] === true || $post['disabled'] === 'true')) {
            $this->UpdateUserDB->disableUser($userId);
        } else if((v::key('disabled', v::stringType()->length(1,5))->validate($post)) && 
                ($post['disabled'] === false || $post['disabled'] === 'false')) {
            $this->UpdateUserDB->enableUser($userId);
        }
        
        $user = $this->UpdateUserDB->selectUserById($userId);
        return $app->render(200, array('user' => $user));
    }

    // TODO: Delete user from any look up tables
    // TODO: Add hooks for events such as deleting  auser so I dont have to import other controllers
    public function adminDeleteUser($app, $userId) {
        if(!v::intVal()->validate($userId)) {
            return $app->render(400,  array('msg' => 'Could not find user. Check your parameters and try again.'));
        }
        if($this->UpdateUserDB->deleteUser($userId)) {
            return $app->render(200,  array('msg' => 'User has been deleted.'));
        } else {
            return $app->render(400,  array('msg' => 'Could not delete user.'));
        }
    }

}
