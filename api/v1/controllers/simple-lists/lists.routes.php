<?php namespace API;
 require_once dirname(__FILE__) . '/lists.controller.php';

class ListRoutes {
    
    static function addRoutes($app, $authenticateForRole) {
        
        //* /simple-lists/ routes - authenticated members only
        
        $app->group('/simple-list', $authenticateForRole('registered-user'), function () use ($app) {

            $app->map("/users", function () use ($app) {
                ListsController::getUsersList($app);
            })->via('GET', 'POST');

            $app->map("/user-groups", function () use ($app) {
                ListsController::getGroupsList($app);
            })->via('GET', 'POST');

            $app->map("/group-roles", function () use ($app) {
                ListsController::getRolesList($app);
            })->via('GET', 'POST');

            $app->map("/visibility-fields", function () use ($app) {
                ListsController::getVisibilityFieldsList($app);
            })->via('GET', 'POST');
            
            
            $app->map("/venues", function () use ($app) {
                ListsController::getVenuesList($app);
            })->via('GET', 'POST');
            
            $app->map("/teams", function () use ($app) {
                ListsController::getTeamsList($app);
            })->via('GET', 'POST');
            
            $app->map("/teams/all", function () use ($app) {
                ListsController::getAllTeamsList($app);
            })->via('GET', 'POST');
            
            $app->map("/games", function () use ($app) {
                ListsController::getGamesList($app);
            })->via('GET', 'POST');
            
        });
    }
}