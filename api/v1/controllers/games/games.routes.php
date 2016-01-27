<?php namespace API;
 require_once dirname(__FILE__) . '/games.controller.php';

class GameRoutes {
    
    static function addRoutes($app, $authenticateForRole) {
            
        //* /game/ routes - for registered users
        
        $app->group('/game', $authenticateForRole('registered-user'), function () use ($app) {
            
            $app->map("/get/:gameId/", function ($gameId) use ($app) {
                GameController::getGame($app, $gameId);
            })->via('GET', 'POST');

            $app->post("/insert/", function () use ($app) {
                GameController::addGame($app);
            });

            $app->post("/update/:gameId/", function ($gameId) use ($app) {
                GameController::saveGame($app, $gameId);
            });

            $app->post("/start/:gameId/", function ($gameId) use ($app) {
                GameController::saveGame($app, $gameId);
            });

            $app->post("/finish/:gameId/", function ($gameId) use ($app) {
                GameController::saveGame($app, $gameId);
            });

            $app->map("/delete/:gameId/", function ($gameId) use ($app) {
                GameController::deleteGame($app, $gameId);
            })->via('DELETE', 'POST');
            
        });
    }
}