'use strict';

/* 
 * API Routes for Trivia Leaderboards
 * 
 * API calls related to trivia leaderboards.
 */

angular.module('apiRoutes.Leaderboards', [])
.factory('ApiRoutesLeaderboards', ['ApiService', function (API) {

    var api = {};
    
    // Global Player Score Leaderboard
    api.getGlobalPlayersLeaderboard = function (count) {
        return API.get('leaderboard/global/players/score/' + count, 'Could not get leaderboard.');
    };
    
    // Global Team Score Leaderboard
    api.getGlobalTeamsLeaderboard = function (count) {
        return API.get('leaderboard/global/teams/score/' + count, 'Could not get leaderboard.');
    };
    
    // Per Joint Player Score Leaderboard
    api.getVenuePlayersLeaderboard = function (venueId, count) {
        return API.get('leaderboard/joint/players/score/' + venueId + '/' + count, 'Could not get leaderboard.');
    };
    
    // Per Joint Team Score Leaderboard
    api.getVenueTeamsLeaderboard = function (venueId, count) {
        return API.get('leaderboard/joint/teams/score/' + venueId + '/' + count, 'Could not get leaderboard.');
    };
    
    // Global Player Checkin Leaderboard
    api.getGlobalPlayerCheckinsLeaderboard = function (count) {
        return API.get('leaderboard/global/players/checkins/' + count, 'Could not get leaderboard.');
    };
    
    // Global Team Checkin Leaderboard
    api.getGlobalTeamCheckinsLeaderboard = function (count) {
        return API.get('leaderboard/global/teams/checkins/' + count, 'Could not get leaderboard.');
    };
    
    // Per Joint Player Checkins Leaderboard
    api.getVenuePlayerCheckinsLeaderboard = function (venueId, count) {
        return API.get('leaderboard/joint/players/checkins/'  + venueId + '/' + count, 'Could not get leaderboard.');
    };
    
    // Per Joint Team Checkins Leaderboard
    api.getVenueTeamCheckinsLeaderboard = function (venueId, count) {
        return API.get('leaderboard/joint/teams/checkins/'  + venueId + '/' + count, 'Could not get leaderboard.');
    };
    
    // List of Joints / Venues
    api.getListOfJoints = function () {
        return API.get('leaderboard/list-joints/hot-salsa', 'Could not get list of joints.');
    };

    return api;
}]);