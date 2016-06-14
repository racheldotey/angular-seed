'use strict';

/* 
 * Host Pages Module
 * 
 * Include controllers and other modules required on authenticated host pages.
 */

angular.module('app.leaderboards', [
    'app.leaderboards.globalPlayerCheckins',
    'app.leaderboards.globalPlayers',
    'app.leaderboards.globalTeamCheckins',
    'app.leaderboards.globalTeams',
    'app.leaderboards.venuePlayerCheckins',
    'app.leaderboards.venuePlayers',
    'app.leaderboards.venueTeamCheckins',
    'app.leaderboards.venueTeams'
]);