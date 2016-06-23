<?php namespace API;
 require_once dirname(__FILE__) . '/leaderboards.controller.php';

class LeaderboardRoutes {
    
    static function addRoutes($app, $authenticateForRole) {
        
        $app->group('/leaderboard', $authenticateForRole('public'), function () use ($app) {
            
            $app->map("/global/players/score/", function () use ($app) {
                LeaderboardController::getGlobalPlayersLeaderboard($app);
            })->via('GET', 'POST');

            // Global Team Score Leaderboard
            $app->map("/global/teams/score/", function () use ($app) {
                LeaderboardController::getGlobalTeamsLeaderboard($app);
            })->via('GET', 'POST');

            // Per Joint Player Score Leaderboard
            $app->map("/joint/players/score/:venueId/", function ($venueId) use ($app) {
                LeaderboardController::getVenuePlayersLeaderboard($app, $venueId);
            })->via('GET', 'POST');

            // Per Joint Team Score Leaderboard
            $app->map("/joint/teams/score/:venueId/", function ($venueId) use ($app) {
                LeaderboardController::getVenueTeamsLeaderboard($app, $venueId);
            })->via('GET', 'POST');

            // Global Player Checkin Leaderboard
            $app->map("/global/players/checkins/", function () use ($app) {
                LeaderboardController::getGlobalPlayerCheckinsLeaderboard($app);
            })->via('GET', 'POST');

            // Global Team Checkin Leaderboard
            $app->map("/global/teams/checkins/", function () use ($app) {
                LeaderboardController::getGlobalTeamCheckinsLeaderboard($app);
            })->via('GET', 'POST');

            // Per Joint Player Checkins Leaderboard
            $app->map("/joint/players/checkins/:venueId/", function ($venueId) use ($app) {
                LeaderboardController::getVenuePlayerCheckinsLeaderboard($app, $venueId);
            })->via('GET', 'POST');

            // Per Joint Team Checkins Leaderboard
            $app->map("/joint/teams/checkins/:venueId/", function ($venueId) use ($app) {
                LeaderboardController::getVenueTeamCheckinsLeaderboard($app, $venueId);
            })->via('GET', 'POST');

            // List of Joints / Venues
            $app->map("/list-joints/local/", function () use ($app) {
                LeaderboardListsController::getLocalVenuesList($app);
            })->via('GET', 'POST');
            
            // List of Joints / Venues
            $app->map("/list-joints/hot-salsa/", function () use ($app) {
                LeaderboardListsController::getHotSalsaVenuesList($app);
            })->via('GET', 'POST');
            
            // List of Joints / Venues
            $app->map("/list-joints/", function () use ($app) {
                LeaderboardListsController::getMergedVenuesList($app);
            })->via('GET', 'POST');
        });
    }
}