<?php namespace API;
require_once dirname(__FILE__) . '/games.data.php';
require_once dirname(dirname(dirname(__FILE__))) . '/services/api.auth.php';

use \Respect\Validation\Validator as v;


class GameController {

    static function getGame($app, $gameId) {
        $game = GameData::getGame($gameId);
        if($game) {
            return $app->render(200, array('game' => $game));
        } else {
            return $app->render(400,  array('msg' => 'Could not select game.'));
        }
    }
    
    static function addGame($app) {
        if(!v::key('name', v::stringType()->length(1,255))->validate($app->request->post()) ||
            !v::key('venueId', v::intVal())->validate($app->request->post()) || 
            !v::key('hostId', v::intVal())->validate($app->request->post()) || 
            !v::key('scheduled', v::stringType())->validate($app->request->post())) {
            return $app->render(400,  array('msg' => 'Invalid game. Check your parameters and try again.'));
        }
        $validGame = array(
            ":name" => $app->request->post('name'),
            ":venue_id" => $app->request->post('venueId'),
            ":host_user_id" => $app->request->post('hostId'),
            ":scheduled" => $app->request->post('scheduled'),
            ":created_user_id" => APIAuth::getUserId(),
            ":last_updated_by" => APIAuth::getUserId()
        );
        
        $gameId = GameData::insertGame($validGame);
        if($gameId) {
            $game = GameData::getGame($gameId);
            return $app->render(200, array('game' => $game));
        } else {
            return $app->render(400,  array('msg' => 'Could not add game.'));
        }
    }
    
    static function saveGame($app, $gameId) {
        if(!v::intVal()->validate($gameId) ||
            !v::key('name', v::stringType()->length(1,255))->validate($app->request->post()) ||
            !v::key('venueId', v::intVal())->validate($app->request->post()) || 
            !v::key('hostId', v::intVal())->validate($app->request->post()) || 
            !v::key('scheduled', v::stringType())->validate($app->request->post())) {
            return $app->render(400,  array('msg' => 'Invalid game. Check your parameters and try again.'));
        }
        
        $validGame = array(
            ":id" => $gameId,
            ":name" => $app->request->post('name'),
            ":venue_id" => $app->request->post('venueId'),
            ":host_user_id" => $app->request->post('hostId'),
            ":scheduled" => $app->request->post('scheduled'),
            ":last_updated_by" => APIAuth::getUserId()
        );
        
        $saved = GameData::updateGame($validGame);
        if($saved) {
            $game = GameData::getGame($gameId);
            return $app->render(200, array('game' => $game));
        } else {
            return $app->render(400,  array('msg' => 'Could not update game.'));
        }
    }
    
    static function startGame($app, $gameId) {
        if(!v::intVal()->validate($gameId)) {
            return $app->render(400,  array('msg' => 'Start game failed. Could not find game.'));
        }
        
        $saved = GameData::updateStartGame(array(
            ":id" => $gameId,
            ":last_updated_by" => APIAuth::getUserId()
        ));
        
        if($saved) {
            $game = GameData::getGame($gameId);
            return $app->render(200, array('game' => $game));
        } else {
            return $app->render(400,  array('msg' => 'System failed to start game.'));
        }
    }
    
    static function endGame($app, $gameId) {
        if(!v::intVal()->validate($gameId)) {
            return $app->render(400,  array('msg' => 'End game failed. Could not find game.'));
        }
        
        $saved = GameData::updateEndGame(array(
            ":id" => $gameId,
            ":last_updated_by" => APIAuth::getUserId()
        ));
        
        if($saved) {
            $game = GameData::getGame($gameId);
            return $app->render(200, array('game' => $game));
        } else {
            return $app->render(400,  array('msg' => 'System failed to end game.'));
        }
    }
    
    static function deleteGame($app, $gameId) {
        if(GameData::didGameStart($gameId)) {
            return $app->render(400,  array('msg' => 'Ths game has already started and can no longer be deleted.'));
        } else if(GameData::deleteGame($gameId)) {
            return $app->render(200,  array('msg' => 'Game has been deleted.'));
        } else {
            return $app->render(400,  array('msg' => 'Could not delete game. Check your parameters and try again.'));
        }
    }
}
