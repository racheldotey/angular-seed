<?php namespace API;
require_once dirname(__FILE__) . '/teams.data.php';
require_once dirname(dirname(dirname(__FILE__))) . '/services/api.mailer.php';

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
        
        $teamName = $app->request->post('name');
        $teamId = TeamData::insertTeam(array(
            ':name' => $teamName,
            ":created_user_id" => APIAuth::getUserId(),
            ":last_updated_by" => APIAuth::getUserId()
        ));
        
        if($teamId) {
            $results = array();
            if(v::key('players', v::arrayType())->validate($app->request->post())) {
                $results = self::addPlayers($teamId, $teamName, $app->request->post('players'));
            }
            $team = TeamData::getTeam($teamId);
            return $app->render(200, array('team' => $team, 'adds' => $results));
        } else {
            return $app->render(400,  array('msg' => 'Could not add team.'));
        }
    }
    
    private static function addPlayers($teamId, $teamName, $players) {
        $results = array();
        foreach($players AS $player) {      
            
            if(isset($player['id'])) {
                $results[] = 'id';// self::addPlayerById($teamId, $teamName, $player['id']);
            }  else if (isset($player['email'])) {
                $results[] = self::addPlayerByEmail($teamId, $teamName, $player['email']);
            } else {
                $results[] = "Could not send team invite to " . $player;
            }
        }
        return $results;
    }
    
    private static function addPlayerByEmail($teamId, $teamName, $email) {
        $found = TeamData::selectUserIdByEmail($email);
        return ($found) ? self::addPlayerById($teamId, $teamName, $found) : 
                            self::sendTeamInvite($teamId, $teamName, $email);
    }
    
    private static function addPlayerById($teamId, $teamName, $id) {
        $found = TeamData::selectUserById($id);
        return ($found) ? self::sendTeamInvite($teamId, $teamName, $found->email, $found->displayName) : false;
    }
    
    private static function sendTeamInvite($teamId, $teamName, $playerEmail, $playerName = '') {
        return ApiMailer::sendTeamInvite($teamId, $teamName, $playerEmail, $playerName);
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
    
    static function addTeamMember($app) {
        if(!v::key('teamId', v::intVal())->validate($app->request->post()) || 
           !v::key('userId', v::intVal())->validate($app->request->post())) {
            return $app->render(400, array('msg' => 'Add team member failed. Check your parameters and try again.'));
        }
        
        if(TeamData::isUserATeamMember(array(
            ':team_id' => $app->request->post('teamId'), 
            ':user_id' => $app->request->post('userId')
        ))) {
            return $app->render(400, array('msg' => 'User is already a member of this team'));
        }
        
        $saved = TeamData::addTeamMember(array(
            ':team_id' => $app->request->post('teamId'), 
            ':user_id' => $app->request->post('userId'),
            ":added_by" => APIAuth::getUserId()
        ));
        
        if($saved) {
            $team = TeamData::getTeam($app->request->post('teamId'));
            return $app->render(200, array('msg' => 'Team member successfully added.', 'team' => $team));
        } else {
            return $app->render(400,  array('msg' => 'Could not add team member.'));
        }
    }
    
    static function removeTeamMember($app) {
        if(!v::key('teamId', v::intVal())->validate($app->request->post()) || 
           !v::key('userId', v::intVal())->validate($app->request->post())) {
            return $app->render(400, array('msg' => 'Remove team member failed. Check your parameters and try again.'));
        }
        
        $saved = TeamData::deleteTeamMember(array(
            ':team_id' => $app->request->post('teamId'), 
            ':user_id' => $app->request->post('userId')
        ));
        
        if($saved) {
            $team = TeamData::getTeam($app->request->post('teamId'));
            return $app->render(200, array('msg' => 'Team member successfully removed.', 'team' => $team));
        } else {
            return $app->render(400,  array('msg' => 'Could not remove team member team.'));
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
