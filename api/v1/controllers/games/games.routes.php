<?php namespace API;
 require_once dirname(__FILE__) . '/games.controller.php';

class GameRoutes {
    
    static function addRoutes($app, $authenticateForRole) {
            
        //* /game/ routes - for registered users
        
        $app->group('/game', $authenticateForRole('registered-user'), function () use ($app) {
            
            /*
             * id
             */
            $app->map("/get/:gameId/", function ($gameId) use ($app) {
                GameController::getGame($app, $gameId);
            })->via('GET', 'POST');

            $app->map("/get/:gameId/:roundNumber/", function ($gameId, $roundNumber) use ($app) {
                GameController::getGame($app, $gameId, $roundNumber);
            })->via('GET', 'POST');

            $app->map("/round/get/:gameId/:roundNumber/", function ($gameId, $roundNumber) use ($app) {
                GameController::getGameRound($app, $gameId, $roundNumber);
            })->via('GET', 'POST');
                        
            /*
             * name, venueId, hostId, scheduled
             */
            $app->post("/insert/", function () use ($app) {
                GameController::addGame($app);
            });

            /*
             * id, name, venueId, hostId, scheduled
             */
            $app->post("/update/:gameId/", function ($gameId) use ($app) {
                GameController::saveGame($app, $gameId);
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
            $app->post("/finish/:gameId/", function ($gameId) use ($app) {
                GameController::endGame($app, $gameId);
            });

            /*
             * id
             */
            $app->map("/delete/:gameId/", function ($gameId) use ($app) {
                GameController::deleteGame($app, $gameId);
            })->via('DELETE', 'POST');
            
        });
    }
}