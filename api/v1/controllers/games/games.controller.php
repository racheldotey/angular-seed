<?php namespace API;
require_once dirname(__FILE__) . '/games.data.php';
require_once dirname(dirname(dirname(__FILE__))) . '/services/api.auth.php';

use \Respect\Validation\Validator as v;


class GameController {

    static function getGameHost($app, $userId) {
        if(!v::intVal()->validate($userId)) {
            return $app->render(400,  array('msg' => 'Invalid game. Check your parameters and try again.'));
        }
        $hostData = GameData::selectGameHostData($userId);
        if($hostData) {
            return $app->render(200, $hostData);
        } else {
            return $app->render(400,  array('msg' => 'Could not select game host data.'));
        }
    }

    static function getGame($app, $gameId, $roundNumber = 1) {
        if(!v::intVal()->validate($gameId)) {
            return $app->render(400,  array('msg' => 'Invalid game. Check your parameters and try again.'));
        }
        $game = GameData::selectGame($gameId);
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
        
        $defaultPoints = (v::key('defaultQuestionPoints')->validate($app->request->post())) ? 
                $app->request->post('defaultQuestionPoints') : 5;
        $result = GameData::insertStartingRounds(1, $gameId, $defaultPoints, APIAuth::getUserId());
        
        if($gameId) {
            $saved = GameData::updateStartGame(array(
                ":id" => $gameId,
                ":last_updated_by" => APIAuth::getUserId()
            ));
            
            $game = GameData::selectGame($gameId);
            return $app->render(200, array('game' => $game, 'starting' => $result));
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
            $game = GameData::selectGame($gameId);
            return $app->render(200, array('msg' => 'Game has started.', 'game' => $game, 'saved' => $saved));
        } else {
            return $app->render(400,  array('msg' => 'System failed to start game.'));
        }
    }
    
    static function endGame($app, $gameId) {
        if(!v::intVal()->validate($gameId)) {
            return $app->render(400,  array('msg' => 'End game failed. Could not find game.'));
        }
        
        $saved = GameData::updateEndGame($gameId, APIAuth::getUserId());
        
        if($saved) {
            $game = GameData::selectGame($gameId);
            return $app->render(200, array('msg' => 'Game has ended.', 'game' => $game, 'saved' => $saved));
        } else {
            return $app->render(400,  array('msg' => 'System failed to end game.'));
        }
    }
    
    static function saveScoreboard($app, $gameId) {
        if(!v::intVal()->validate($gameId)) {
            return $app->render(400,  array('msg' => 'Save game failed. Could not find game.'));
        } else if(!v::key('questions')->validate($app->request->post())) {
            return $app->render(400,  array('msg' => 'Invalid scoreboard. Check your parameters and try again.'));
        }
        
        $saved = self::processScores($gameId, $app->request->post('questions'));
        if($saved) {
            
            if (v::key('endGame')->validate($app->request->post()) &&
               ($app->request->post('endGame') === 1 || 
                $app->request->post('endGame') === '1' || 
                $app->request->post('endGame') === true || 
                $app->request->post('endGame') === 'true')) {
                // TODO: Implement cusitom boolean Respect\Validator
                // Converting to boolean did not work well, 
                // This allows a wider range of true false values
                $saved = GameData::updateEndGame($gameId, APIAuth::getUserId());
            }
            
            $game = GameData::selectGame($gameId);
            return $app->render(200, array('saved' => $saved, 'game' => $game));
        } else {
            return $app->render(400,  array('msg' => 'Could not save scoreboard.'));
        }
    }
    
    private static function processScores($gameId, $questions) {
        $currentUser = APIAuth::getUserId();
        $questionScores = array();
        
        foreach($questions as $question) {
            $questionScores[] = array(
                ':game_id' => $gameId, 
                ':round_id' => $question['roundId'],
                ':team_id' => $question['teamId'], 
                ':question_id' => $question['questionId'],
                ':created_user_id' => $currentUser,
                ':last_updated_by' => $currentUser,
                ':wager' => $question['teamWager'],
                ':dup_wager' => $question['teamWager'],
                ':answer' => $question['teamAnswer'],
                ':dup_answer' => $question['teamAnswer'],
                ':score' => $question['questionScore'],
                ':dup_score' => $question['questionScore']
            );
        }
        
        $saved = [];
        $saved[] = GameData::saveQuestionScores($questionScores);
        $saved[] = GameData::calculateGameScores($gameId);
        
        return $saved;
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

    static function checkTeamIntoGame($app, $gameId) {
        if(!v::intVal()->validate($gameId) || 
            !v::key('teamId', v::intVal())->validate($app->request->post())) {
            return $app->render(400,  array('msg' => 'Invalid game or team. Check your parameters and try again.'));
        }
        
        $validTeam = array(
            ":game_id" => $gameId,
            ":team_id" => $app->request->post('teamId'),
            ":created_user_id" => APIAuth::getUserId(),
            ":last_updated_by" => APIAuth::getUserId()
        );        
        $saved = GameData::insertTeamIntoGame($validTeam);
        if($saved) {
            $game = GameData::selectGame($gameId);
            return $app->render(200, array('game' => $game));
        } else {
            return $app->render(400,  array('msg' => 'Could not check team into game.'));
        }
    }
    
    static function addRound($app) {
        if(!v::key('name', v::stringType()->length(0,255))->validate($app->request->post()) ||
            !v::key('gameId', v::intVal())->validate($app->request->post())) {
            return $app->render(400,  array('msg' => 'Invalid round. Check your parameters and try again.'));
        }
        
        $count = GameData::getRoundCount($app->request->post('gameId'));
        $defaultPoints = (v::key('defaultQuestionPoints')->validate($app->request->post())) ? 
                $app->request->post('defaultQuestionPoints') : 5;
        
        $roundId = GameData::insertRound(array(
            ":name" => $app->request->post('name'),
            ":order" => $count + 1,
            ":game_id" => $app->request->post('gameId'),
            ":default_question_points" => $defaultPoints,
            ":created_user_id" => APIAuth::getUserId(),
            ":last_updated_by" => APIAuth::getUserId()
        ));
        if($roundId) {
            return $app->render(200, array('id' => $roundId, 'game' => GameData::selectGame($app->request->post('gameId'))));
        } else {
            return $app->render(400,  array('msg' => 'Could not add game round.'));
        }
    }
    
    static function editRound($app, $roundId) {
        if(!v::intVal()->validate($roundId) ||
            !v::key('name', v::stringType()->length(0,255))->validate($app->request->post()) ||
            !v::key('gameId', v::intVal())->validate($app->request->post())) {
            return $app->render(400,  array('msg' => 'Invalid round. Check your parameters and try again.'));
        }
        
        $defaultPoints = (v::key('defaultQuestionPoints')->validate($app->request->post())) ? 
                $app->request->post('defaultQuestionPoints') : 5;
        
        if(GameData::updateRound(array(
            ":id" => $roundId,
            ":name" => $app->request->post('name'),
            ":game_id" => $app->request->post('gameId'),
            ":default_question_points" => $defaultPoints,
            ":last_updated_by" => APIAuth::getUserId()
        ))) {
            return $app->render(200, array('game' => GameData::selectGame($app->request->post('gameId'))));
        } else {
            return $app->render(400,  array('msg' => 'Could not edit question.'));
        }
    }
    
    static function deleteRound($app, $roundId) {
        if(!v::intVal()->validate($roundId) ||
            !v::key('gameId', v::intVal())->validate($app->request->post())) {
            return $app->render(400,  array('msg' => 'Invalid round. Check your parameters and try again.'));
        } else if(GameData::getQuestionCount($app->request->post('roundId')) > 0) {
            return $app->render(400,  array('msg' => 'This round cannot be deleted because it has questions associated with it.'));
        }
        
        if(GameData::deleteRound(array(
            ":id" => $roundId,
            ":game_id" => $app->request->post('gameId')
        ))) {
            return $app->render(200, array('game' => GameData::selectGame($app->request->post('gameId'))));
        } else {
            return $app->render(400,  array('msg' => 'Could not delete question.'));
        }
    }
    
    static function addQuestion($app) {
        if(!v::key('question', v::stringType()->length(0,255))->validate($app->request->post()) ||
            !v::key('gameId', v::intVal())->validate($app->request->post()) || 
            !v::key('roundId', v::intVal())->validate($app->request->post())) {
            return $app->render(400,  array('msg' => 'Invalid question. Check your parameters and try again.'));
        }
        $count = GameData::getQuestionCount($app->request->post('roundId'));
        
        $points = (v::key('maxPoints', v::intVal())->validate($app->request->post())) ? $app->request->post('maxPoints') : '5.00';
        
        $wager = 0;
        if (v::key('wager')->validate($app->request->post())) {
            // TODO: Implement cusitom boolean Respect\Validator
            // Converting to boolean did not work well, 
            // This allows a wider range of true false values
            $wager = ($app->request->post('wager') === 1 || 
                        $app->request->post('wager') === '1' || 
                        $app->request->post('wager') === true || 
                        $app->request->post('wager') === 'true') ? 1 : 0;
        }
        
        $validQuestion = array(
            ":question" => $app->request->post('question'),
            ":order" => $count + 1,
            ":game_id" => $app->request->post('gameId'),
            ":round_id" => $app->request->post('roundId'),
            ":max_points" => $points,
            ":wager" => $wager,
            ":created_user_id" => APIAuth::getUserId(),
            ":last_updated_by" => APIAuth::getUserId()
        );        
        $questionId = GameData::insertQuestion($validQuestion);
        if($questionId) {
            $saved = GameData::selectGame($app->request->post('gameId'));
            return $app->render(200, array('game' => $saved));
        } else {
            return $app->render(400,  array('msg' => 'Could not add question.'));
        }
    }
    
    static function editQuestion($app, $questionId) {
        if(!v::intVal()->validate($questionId) ||
            !v::key('question', v::stringType()->length(0,255))->validate($app->request->post()) ||
            !v::key('gameId', v::intVal())->validate($app->request->post()) || 
            !v::key('roundId', v::intVal())->validate($app->request->post())) {
            return $app->render(400,  array('msg' => 'Invalid question. Check your parameters and try again.'));
        }
        
        $points = (v::key('maxPoints', v::intVal())->validate($app->request->post())) ? $app->request->post('maxPoints') : '5.00';
        
        $wager = 0;
        if (v::key('wager')->validate($app->request->post())) {
            // TODO: Implement cusitom boolean Respect\Validator
            // Converting to boolean did not work well, 
            // This allows a wider range of true false values
            $wager = ($app->request->post('wager') === 1 || 
                        $app->request->post('wager') === '1' || 
                        $app->request->post('wager') === true || 
                        $app->request->post('wager') === 'true') ? 1 : 0;
        }
        
        $validQuestion = array(
            ":id" => $questionId,
            ":question" => $app->request->post('question'),
            ":game_id" => $app->request->post('gameId'),
            ":round_id" => $app->request->post('roundId'),
            ":max_points" => $points,
            ":wager" => $wager,
            ":last_updated_by" => APIAuth::getUserId()
        );        
        $updated = GameData::updateQuestion($validQuestion);
        if($updated) {
            $saved = GameData::selectGame($app->request->post('gameId'));
            return $app->render(200, array('game' => $saved));
        } else {
            return $app->render(400,  array('msg' => 'Could not edit question.'));
        }
    }
    
    static function deleteQuestion($app, $questionId) {
        if(!v::intVal()->validate($questionId) ||
            !v::key('gameId', v::intVal())->validate($app->request->post()) || 
            !v::key('roundId', v::intVal())->validate($app->request->post())) {
            return $app->render(400,  array('msg' => 'Invalid question. Check your parameters and try again.'));
        }
        
        $validQuestion = array(
            ":id" => $questionId,
            ":game_id" => $app->request->post('gameId'),
            ":round_id" => $app->request->post('roundId')
        );        
        $deleted = GameData::deleteQuestion($validQuestion);
        if($deleted) {
            $saved = GameData::selectGame($app->request->post('gameId'));
            return $app->render(200, array('game' => $saved));
        } else {
            return $app->render(400,  array('msg' => 'Could not delete question.'));
        }
    }
}
