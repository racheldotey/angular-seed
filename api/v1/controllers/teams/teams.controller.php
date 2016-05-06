<?php namespace API;
require_once dirname(__FILE__) . '/teams.data.php';
require_once dirname(dirname(__FILE__)) . '/games/games.data.php';
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
        $post = $app->request->post();
        if(!v::key('name', v::stringType())->validate($post) ||
           !v::key('homeVenueId', v::intVal())->validate($post)) {
            return $app->render(400, array('msg' => 'Insert failed. Check your parameters and try again.'));
        }
        
        $teamId = TeamData::insertTeam(array(
            ':name' => $post['name'],
            ':home_venue_id' => $post['homeVenueId'],
            ":created_user_id" => APIAuth::getUserId(),
            ":last_updated_by" => APIAuth::getUserId()
        ));            
        
        if($teamId) {
            $results = array();
            if(v::key('players', v::arrayType())->validate($post)) {
                $results = self::addPlayers($teamId, $post['name'], $post['players']);
            }
            
            if(v::key('gameId', v::intVal())->validate($post)) {
                $gameInsert = GameData::insertTeamIntoGame(array(
                    ':game_id' => $post['gameId'],
                    ':team_id' => $teamId,
                    ":created_user_id" => APIAuth::getUserId(),
                    ":last_updated_by" => APIAuth::getUserId()
                ));
            }
            
            $team = TeamData::getTeam($teamId);
            return $app->render(200, array('team' => $team, 'invites' => $results));
        } else {
            return $app->render(400,  array('msg' => 'Could not add team.'));
        }
    }
    
    private static function addPlayers($teamId, $teamName, $players) {
        $results = array();
        foreach($players AS $player) {
            if(v::key('userId', v::intVal())->validate($player)) {
                $results[] = self::addPlayerById($teamId, $teamName, $player['userId']);
            }  else if (v::key('email', v::email())->validate($player)) {
                $results[] = self::addPlayerByEmail($teamId, $teamName, $player['email']);
            } else {
                $results[] = "Could not send team invite to " . json_encode($player);
            }
            
        }
        return $results;
    }
    
    private static function addPlayerByEmail($teamId, $teamName, $email) {
        $found = TeamData::selectUserByEmail($email);
        return ($found) ? self::sendTeamInvite($teamId, $teamName, $email, $found->id, $found->displayName) : 
                            self::sendTeamInvite($teamId, $teamName, $email);
    }
    
    private static function addPlayerById($teamId, $teamName, $id) {
        $saved = TeamData::addTeamMember(array(
            ':team_id' => $teamId, 
            ':user_id' => $id,
            ":added_by" => APIAuth::getUserId()
        ));
        return ($saved) ? array('error' => false, 'msg' => "Team member added to '{$teamName}'.") : 
                    array('error' => true, 'msg' => "Could not add user to team '{$teamName}'.");
    }
    
    private static function sendTeamInvite($teamId, $teamName, $playerEmail, $playerId = NULL, $playerName = '') {
        return EmailController::silentlySendTeamInviteEmail($teamId, $teamName, $playerEmail, $playerId, $playerName);
    }
    
    static function saveTeam($app, $teamId) {
        $post = $app->request->post();
        
        if(!v::intVal()->validate($teamId) || 
           !v::key('name', v::stringType())->validate($post) ||
           !v::key('homeVenueId', v::intVal())->validate($post)) {
            return $app->render(400, array('msg' => 'Update failed. Check your parameters and try again.'));
        }
        
        $saved = TeamData::updateTeam(array(
            ':id' => $teamId, 
            ':name' => $post['name'],
            ':home_venue_id' => $post['homeVenueId'],
            ":last_updated_by" => APIAuth::getUserId()
        ));
        
        if($saved) {
            $results = array();
            if(v::key('players', v::arrayType())->validate($post)) {
                $results = self::addPlayers($teamId, $post['name'], $post['players']);
            }
            $team = TeamData::getTeam($teamId);
            return $app->render(200, array('team' => $team, 'invites' => $results));
        } else {
            return $app->render(400,  array('msg' => 'Could not add team.'));
        }            
        
    }
    
    static function addTeamMember($app, $teamId) {
        if(!v::intVal()->validate($teamId) || 
           !v::key('userId', v::intVal())->validate($app->request->post())) {
            return $app->render(400, array('msg' => 'Add team member failed. Check your parameters and try again.'));
        }
        
        if(TeamData::isUserATeamMember(array(
            ':team_id' => $teamId, 
            ':user_id' => $app->request->post('userId')
        ))) {
            return $app->render(400, array('msg' => 'User is already a member of this team'));
        }
        
        $saved = TeamData::addTeamMember(array(
            ':team_id' => $teamId, 
            ':user_id' => $app->request->post('userId'),
            ":added_by" => APIAuth::getUserId()
        ));
        
        if($saved) {
            $team = TeamData::getTeam($teamId);
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
