<?php namespace API;
 require_once dirname(__FILE__) . '/teams.controller.php';

class TeamRoutes {
    
    static function addRoutes($app, $authenticateForRole) {
        
        //* /team/ routes - registered users only
        
        $app->group('/team', $authenticateForRole('registered-user'), function () use ($app) {
            
            $app->map("/get/:teamId/", function ($teamId) use ($app) {
                TeamController::getTeam($app, $teamId);
            })->via('GET', 'POST');

            $app->post("/insert/", function () use ($app) {
                TeamController::addTeam($app);
            });

            $app->post("/member/add/", function () use ($app) {
                TeamController::addTeamMember($app);
            });

            $app->post("/member/remove/", function () use ($app) {
                TeamController::removeTeamMember($app);
            });

            $app->post("/update/:teamId/", function ($teamId) use ($app) {
                TeamController::saveTeam($app, $teamId);
            });

            $app->map("/delete/:teamId/", function ($teamId) use ($app) {
                TeamController::deleteTeam($app, $teamId);
            })->via('DELETE', 'POST');
            
        });
    }
}