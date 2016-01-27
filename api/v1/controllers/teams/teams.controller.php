<?php namespace API;
require_once dirname(__FILE__) . '/teams.data.php';
require_once dirname(dirname(dirname(__FILE__))) . '/services/api.auth.php';

use \Respect\Validation\Validator as v;


class TeamController {

    static function getTeam($app, $teamId) {
        $team = TeamData::getTeam($teamId);
        if($team) {
            return $app->render(200, array('team' => $team));
        } else {
            return $app->render(400,  array('msg' => 'Could not select team.'));
        }
    }
    
    static function addTeam($app) {
        $team = TeamData::insertTeam();
        if($team) {
            return $app->render(200, array('team' => $team));
        } else {
            return $app->render(400,  array('msg' => 'Could not add team.'));
        }
    }
    
    static function saveTeam($app, $teamId) {
        $team = TeamData::updateTeam();
        if($team) {
            return $app->render(200, array('team' => $team));
        } else {
            return $app->render(400,  array('msg' => 'Could not update team.'));
        }
    }
    
    static function deleteTeam($app, $teamId) {
        if(TeamData::deleteTeam($teamId)) {
            return $app->render(200,  array('msg' => 'Team has been deleted.'));
        } else {
            return $app->render(400,  array('msg' => 'Could not delete team. Check your parameters and try again.'));
        }
    }
}
