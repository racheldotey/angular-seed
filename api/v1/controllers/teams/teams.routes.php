<?php namespace API;
 require_once dirname(__FILE__) . '/teams.controller.php';

class TeamRoutes {
    
    static function addRoutes($app, $authenticateForRole) {
        
        //* /team/ routes - registered users only
        
        
        $app->group('/team', $authenticateForRole('public'), function () use ($app) {
            
            /*
             * id
             */
            $app->map("/get/:teamId/", function ($teamId) use ($app) {
                TeamController::getTeam($app, $teamId);
            })->via('GET', 'POST');

            /*
             * name, players[], homeVenueId
             */
            $app->post("/insert/", function () use ($app) {
                TeamController::addTeam($app);
            });

            /*
             * id, name, players[], homeVenueId
             */
            $app->post("/update/:teamId/", function ($teamId) use ($app) {
                TeamController::saveTeam($app, $teamId);
            });

            /*
             * userId, teamId
             */
            $app->post("/add-member/:teamId/", function ($teamId) use ($app) {
                TeamController::addTeamMember($app, $teamId);
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