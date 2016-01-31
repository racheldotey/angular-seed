<?php namespace API;
require_once dirname(__FILE__) . '/teams.data.php';
require_once dirname(dirname(dirname(__FILE__))) . '/services/api.auth.php';

use \Respect\Validation\Validator as v;


class TeamController {

    static function getTeam($app, $teamId) {
        if(!v::intVal()->validate($teamId)) {
            return $app->render(400,  array('msg' => 'Could not select team. Check your parameters and try again.'));
        }
        $team = TeamData::getTeam($teamId);
        if($team) {
            return $app->render(200, array('team' => $team));
        } else {
            return $app->render(400,  array('msg' => 'Could not select team.'));
        }
    }
    
    static function addTeam($app) {
        if(!v::key('name', v::stringType())->validate($app->request->post())) {
            return $app->render(400, array('msg' => 'Insert failed. Check your parameters and try again.'));
        }
        
        $teamId = TeamData::insertTeam(array(
            ':name' => $app->request->post('name'),
            ":created_user_id" => APIAuth::getUserId(),
            ":last_updated_by" => APIAuth::getUserId()
        ));
        
        if($teamId) {
            $team = TeamData::getTeam($teamId);
            return $app->render(200, array('team' => $team));
        } else {
            return $app->render(400,  array('msg' => 'Could not add team.'));
        }
    }
    
    static function saveTeam($app, $teamId) {
        if(!v::intVal()->validate($teamId) || 
           !v::key('name', v::stringType())->validate($app->request->post())) {
            return $app->render(400, array('msg' => 'Update failed. Check your parameters and try again.'));
        }
        
        $saved = TeamData::updateTeam(array(
            ':id' => $teamId, 
            ':name' => $app->request->post('name'),
            ":last_updated_by" => APIAuth::getUserId()
        ));
        
        if($saved) {
            $team = TeamData::getTeam($teamId);
            return $app->render(200, array('team' => $team));
        } else {
            return $app->render(400,  array('msg' => 'Could not update team.'));
        }
    }
    
    static function deleteTeam($app, $teamId) {
        if(!v::intVal()->validate($teamId)) {
            return $app->render(400,  array('msg' => 'Could not delete team. Check your parameters and try again.'));
        }
        if(TeamData::deleteTeam($teamId)) {
            return $app->render(200,  array('msg' => 'Team has been deleted.'));
        } else {
            return $app->render(400,  array('msg' => 'Could not delete team.'));
        }
    }
}
