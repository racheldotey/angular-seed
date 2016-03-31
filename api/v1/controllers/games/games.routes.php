<?php namespace API;
 require_once dirname(__FILE__) . '/games.controller.php';

class GameRoutes {
    
    static function addRoutes($app, $authenticateForRole) {
            
        //* /game/ routes - for registered users
        
        $app->group('/trivia', $authenticateForRole('registered-user'), function () use ($app) {
            
            /*
             * id
             */
            $app->map("/get/:gameId/", function ($gameId) use ($app) {
                GameController::getGame($app, $gameId);
            })->via('GET', 'POST');

            $app->map("/get/:gameId/:roundNumber/", function ($gameId, $roundNumber) use ($app) {
                GameController::getGame($app, $gameId, $roundNumber);
            })->via('GET', 'POST');

            /*
            $app->map("/round/get/:gameId/:roundNumber/", function ($gameId, $roundNumber) use ($app) {
                GameController::getGameRound($app, $gameId, $roundNumber);
            })->via('GET', 'POST');
           */
            /*
             * name, venueId, hostId, scheduled, defaultQuestionPoints
             */
            $app->post("/insert/game", function () use ($app) {
                GameController::addGame($app);
            });

            /*
             * id, name, venueId, hostId, scheduled
             */
            $app->post("/update/:gameId/", function ($gameId) use ($app) {
                GameController::saveGame($app, $gameId);
            });

            /*
             * gameId, roundNumber, name, venueId, hostId, scheduled
             */
            $app->post("/update/scoreboard/:gameId/:roundNumber/", function ($gameId, $roundNumber) use ($app) {
                GameController::saveScoreboard($app, $gameId, $roundNumber);
            });

            /*
             * id
             */
            $app->post("/start/:gameId/", function ($gameId) use ($app) {
                GameController::startGame($app, $gameId);
            });

            /*
             * id
             */
            $app->post("/end/:gameId/", function ($gameId) use ($app) {
                GameController::endGame($app, $gameId);
            });

            /*
             * id
             */
            $app->map("/delete/:gameId/", function ($gameId) use ($app) {
                GameController::deleteGame($app, $gameId);
            })->via('DELETE', 'POST');
            
                        
            /*
             * gameId, roundNumber, teamId
             */
            $app->post("/checkin-team/:gameId/:roundNumber/", function ($gameId, $roundNumber) use ($app) {
                GameController::checkTeamIntoGame($app, $gameId, $roundNumber);
            });
                        
            /*
             * gameId, name, defaultQuestionPoints
             */
            $app->post("/insert/round", function () use ($app) {
                GameController::addRound($app);
            });
                    
            /*
             * gameId, roundId, question
             */
            $app->post("/insert/question", function () use ($app) {
                GameController::addQuestion($app);
            });
        });
    }
}