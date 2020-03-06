<?php namespace API;

require_once dirname(dirname(dirname((dirname(__FILE__))))) . '/services/TempLinkTokens.php';
require_once dirname(__FILE__) . '/userPermissionsDB.php';

/* @author  Rachel L Carbone <hello@rachellcarbone.com> */

use \Respect\Validation\Validator as v;
v::with('API\\Validation\\Rules');

class UserPermissionsController extends RouteController {

    protected $UserPermissionsDB;
   
    public function __construct(\Interop\Container\ContainerInterface $slimContainer) {
        parent::__construct($slimContainer);

        $this->UserPermissionsDB = new UserPermissionsDB($slimContainer->get('ApiDBConn'));
    }

    public function assignGroup($app) {
        if(!v::key('groupId', v::stringType())->validate($app->request->post()) || 
           !v::key('userId', v::stringType())->validate($app->request->post())) {
            return $app->render(400,  array('msg' => 'Could not assign user from group. Check your parameters and try again.'));
        }
        
        $data = array (
            ':auth_group_id' => $app->request->post('groupId'),
            ':user_id' => $app->request->post('userId'),
            ":created_user_id" => APIAuth::getUserId()
        );
        
        if($this->UserPermissionsDB->insertGroupAssignment($data)) {
            return $app->render(200,  array('msg' => 'User has been assigned to group.'));
        } else {
            return $app->render(400,  array('msg' => 'Could not assign user to group.', 'data' => $data));
        }
    }

    public function unassignGroup($app) {
        if(!v::key('groupId', v::stringType())->validate($app->request->post()) || 
           !v::key('userId', v::stringType())->validate($app->request->post())) {
            return $app->render(400,  array('msg' => 'Could not unassign user from group. Check your parameters and try again.'));
        } 
        
        $data = array (
            ':auth_group_id' => $app->request->post('groupId'),
            ':user_id' => $app->request->post('userId')
        );
        
        if($this->UserPermissionsDB->deleteGroupAssignment($data)) {
            return $app->render(200,  array('msg' => 'User has been unassigned from group.'));
        } else {
            return $app->render(400,  array('msg' => 'Could not unassign user from group.'));
        }
    }
}
