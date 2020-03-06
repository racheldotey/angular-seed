<?php namespace API;

require_once dirname(__FILE__) . '/getUserDB.php';

/* @author  Rachel L Carbone <hello@rachellcarbone.com> */

use \Respect\Validation\Validator as v;
v::with('API\\Validation\\Rules');

class GetUserController extends RouteController {

    protected $GetUserDB;
   
    public function __construct(\Interop\Container\ContainerInterface $slimContainer) {
        parent::__construct($slimContainer);

        $this->GetUserDB = new GetUserDB($slimContainer->get('ApiDBConn'));
    }   

    public function getMemberDataObject($app, $userId) {
        if(!v::intVal()->validate($userId)) {
            return $app->render(400,  array('msg' => 'Could not find user. Check your parameters and try again.'));
        }

        $user = $this->GetUserDB->selectMemberDataByUserId($userId);
        
        if($user) {
            return $app->render(200, array('user' => $user ));
        } else {
            return $app->render(400,  array('msg' => 'User could not be found.'));
        }
    }

}
