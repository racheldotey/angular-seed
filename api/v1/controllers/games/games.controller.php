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
            ":venueId" => $app->request->post('venueId'),
            ":hostId" => $app->request->post('hostId'),
            ":scheduled" => $app->request->post('scheduled'),
            ":created_user_id" => APIAuth::getUserId()
        );
        
        $game = GameData::insertGame();
        if($game) {
            return $app->render(200, array('game' => $game));
        } else {
            return $app->render(400,  array('msg' => 'Could not add game.'));
        }
    }
    
    static function saveGame($app, $gameId) {
        if(!v::key('name', v::stringType()->length(1,255))->validate($app->request->post()) ||
            !v::key('venueId', v::intVal())->validate($app->request->post()) || 
            !v::key('hostId', v::intVal())->validate($app->request->post()) || 
            !v::key('scheduled', v::stringType())->validate($app->request->post())) {
            return $app->render(400,  array('msg' => 'Invalid game. Check your parameters and try again.'));
        }
        
        $validGame = array(
            ":id" => $gameId,
            ":name" => $app->request->post('name'),
            ":venueId" => $app->request->post('venueId'),
            ":hostId" => $app->request->post('hostId'),
            ":scheduled" => $app->request->post('scheduled'),
            ":last_updated_by" => APIAuth::getUserId()
        );
        
        $game = GameData::updateGame($validGame);
        if($game) {
            return $app->render(200, array('game' => $game));
        } else {
            return $app->render(400,  array('msg' => 'Could not update game.'));
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
