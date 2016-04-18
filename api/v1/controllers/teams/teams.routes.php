<?php namespace API;
 require_once dirname(__FILE__) . '/teams.controller.php';

class TeamRoutes {
    
    static function addRoutes($app, $authenticateForRole) {
        
        //* /team/ routes - registered users only
        
        $app->group('/team', $authenticateForRole('registered-user'), function () use ($app) {
            
            /*
             * id
             */
            $app->map("/get/:teamId/", function ($teamId) use ($app) {
                TeamController::getTeam($app, $teamId);
            })->via('GET', 'POST');

            /*
             * name, players[]
             */
            $app->post("/insert/", function () use ($app) {
                TeamController::addTeam($app);
            });

            /*
             * id, name, players[]
             */
            $app->post("/update/:teamId/", function ($teamId) use ($app) {
                TeamController::saveTeam($app, $teamId);
            });

            /*
             * id, name
             */
            $app->post("/member/add/", function () use ($app) {
                TeamController::addTeamMember($app);
            });

            /*
             * id
             */
            $app->post("/member/remove/", function () use ($app) {
                TeamController::removeTeamMember($app);
            });

            /*
             * id
             */
            $app->map("/delete/:teamId/", function ($teamId) use ($app) {
                TeamController::deleteTeam($app, $teamId);
            })->via('DELETE', 'POST');
            
        });
    }
}