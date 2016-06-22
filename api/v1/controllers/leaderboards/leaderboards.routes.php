<?php namespace API;
 require_once dirname(__FILE__) . '/leaderboards.controller.php';

class LeaderboardRoutes {
    
    static function addRoutes($app, $authenticateForRole) {
        
        $app->group('/leaderboard', $authenticateForRole('public'), function () use ($app) {
            
            $app->map("/global/players/score/:count/", function ($count) use ($app) {
                LeaderboardController::getGlobalPlayersLeaderboard($app, $count);
            })->via('GET', 'POST');

            // Global Team Score Leaderboard
            $app->map("/global/teams/score/:count/", function ($count) use ($app) {
                LeaderboardController::getGlobalTeamsLeaderboard($app, $count);
            })->via('GET', 'POST');

            // Per Joint Player Score Leaderboard
            $app->map("/joint/players/score/:venueId/:count/", function ($venueId, $count) use ($app) {
                LeaderboardController::getVenuePlayersLeaderboard($app, $venueId, $count);
            })->via('GET', 'POST');

            // Per Joint Team Score Leaderboard
            $app->map("/joint/teams/score/:venueId/:count/", function ($venueId, $count) use ($app) {
                LeaderboardController::getVenueTeamsLeaderboard($app, $venueId, $count);
            })->via('GET', 'POST');

            // Global Player Checkin Leaderboard
            $app->map("/global/players/checkins/:count/", function ($count) use ($app) {
                LeaderboardController::getGlobalPlayerCheckinsLeaderboard($app, $count);
            })->via('GET', 'POST');

            // Global Team Checkin Leaderboard
            $app->map("/global/teams/checkins/:count/", function ($count) use ($app) {
                LeaderboardController::getGlobalTeamCheckinsLeaderboard($app, $count);
            })->via('GET', 'POST');

            // Per Joint Player Checkins Leaderboard
            $app->map("/joint/players/checkins/:venueId/:count/", function ($venueId, $count) use ($app) {
                LeaderboardController::getVenuePlayerCheckinsLeaderboard($app, $venueId, $count);
            })->via('GET', 'POST');

            // Per Joint Team Checkins Leaderboard
            $app->map("/joint/teams/checkins/:venueId/:count/", function ($venueId, $count) use ($app) {
                LeaderboardController::getVenueTeamCheckinsLeaderboard($app, $venueId, $count);
            })->via('GET', 'POST');

            // List of Joints / Venues
            $app->map("/list-joints/local/", function () use ($app) {
                LeaderboardController::getLocalVenuesList($app);
            })->via('GET', 'POST');
            
            // List of Joints / Venues
            $app->map("/list-joints/hot-salsa/", function () use ($app) {
                LeaderboardController::getHotSalsaVenuesList($app);
            })->via('GET', 'POST');
        });
    }
}