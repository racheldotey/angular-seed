<?php namespace API;
require_once dirname(__FILE__) . '/games.data.php';
require_once dirname(dirname(dirname(__FILE__))) . '/services/api.auth.php';

use \Respect\Validation\Validator as v;


class GameController {

    static function getGame($app, $gameId, $roundNumber = 1) {
        $game = GameData::selectGame($gameId, $roundNumber);
        if($game) {
            return $app->render(200, array('game' => $game));
        } else {
            return $app->render(400,  array('msg' => 'Could not select game.'));
        }
    }

    static function getGameRound($app, $gameId, $roundNumber) {
        $round = GameData::selectGameRound($gameId, $roundNumber);
        if($round) {
            return $app->render(200, array('round' => $round));
        } else {
            return $app->render(400,  array('msg' => 'Could not select game round.'));
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
        
        $defaultPoints = (v::key('defaultQuestionPoints')->validate($app->request->post())) ? 
                $app->request->post('defaultQuestionPoints') : 5;
        $result = self::addRoundsToGame(1, $gameId, $defaultPoints);
        if($gameId) {
            $game = GameData::selectGame($gameId);
            return $app->render(200, array('game' => $game));
        } else {
            return $app->render(400,  array('msg' => 'Could not add game.'));
        }
    }
    
    private static function addRoundsToGame($count, $gameId, $defaultPoints = 5) {
        for($i = 1; $i <= $count; $i++) {
            $validRound = array(
                ":name" => "Round #" + $i,
                ":order" => $i,
                ":game_id" => $gameId,
                ":default_question_points" => $defaultPoints,
                ":created_user_id" => APIAuth::getUserId(),
                ":last_updated_by" => APIAuth::getUserId()
            );
            $result = GameData::insertRound($validRound);
            if(!$result) {
                return false;
            }
        }
        return true;
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
            $game = GameData::selectGame($gameId);
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
            $started = GameData::selectStarted($gameId);
            return $app->render(200, array('msg' => 'Game has started.', 'started' => $started));
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
            $ended = GameData::selectEnded($gameId);
            return $app->render(200, array('msg' => 'Game has ended.', 'ended' => $ended));
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

    
    
    static function addRound($app) {
        if(!v::key('name', v::stringType()->length(1,255))->validate($app->request->post()) ||
            !v::key('gameId', v::intVal())->validate($app->request->post())) {
            return $app->render(400,  array('msg' => 'Invalid round. Check your parameters and try again.'));
        }
        $count = GameData::getRoundCount($app->request->post('gameId'));
        $defaultPoints = (v::key('defaultQuestionPoints')->validate($app->request->post())) ? 
                $app->request->post('defaultQuestionPoints') : 5;
        $validRound = array(
            ":name" => $app->request->post('name'),
            ":order" => ($count <= 0) ? 1 : $count + 1,
            ":game_id" => $app->request->post('gameId'),
            ":default_question_points" => $defaultPoints,
            ":created_user_id" => APIAuth::getUserId(),
            ":last_updated_by" => APIAuth::getUserId()
        );
        
        $roundId = GameData::insertRound($validRound);
        if($roundId) {
            $game = GameData::selectGame($app->request->post('gameId'), $roundId);
            return $app->render(200, array('game' => $game));
        } else {
            return $app->render(400,  array('msg' => 'Could not add game round.'));
        }
    }
    
    static function addQuestion($app) {
        if(!v::key('question', v::stringType()->length(1,255))->validate($app->request->post()) ||
            !v::key('gameId', v::intVal())->validate($app->request->post()) || 
            !v::key('roundId', v::intVal())->validate($app->request->post())) {
            return $app->render(400,  array('msg' => 'Invalid question. Check your parameters and try again.'));
        }
        $count = (!v::key('roundNumber', v::intVal())->validate($app->request->post())) ?
                GameData::getQuestionCount($app->request->post('roundId')) :
                $app->request->post('roundNumber');
        
        $points = (v::key('maxPoints', v::intVal())->validate($app->request->post())) ? $app->request->post('maxPoints') : '5.00';
        $validQuestion = array(
            ":question" => $app->request->post('question'),
            ":order" => ($count <= 0) ? 1 : $count + 1,
            ":game_id" => $app->request->post('gameId'),
            ":round_id" => $app->request->post('roundId'),
            ":max_points" => $points,
            ":created_user_id" => APIAuth::getUserId(),
            ":last_updated_by" => APIAuth::getUserId()
        );
        
        $questionId = GameData::insertQuestion($validQuestion);
        if($questionId) {
            $game = GameData::selectGame($app->request->post('gameId'), $app->request->post('roundId'));
            return $app->render(200, array('game' => $game));
        } else {
            return $app->render(400,  array('msg' => 'Could not add question.'));
        }
    }
}
