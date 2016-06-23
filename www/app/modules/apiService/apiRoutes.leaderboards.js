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
    api.getGlobalPlayersLeaderboard = function (limit, startDate, endDate) {
        var queryData = {};
        if(limit) { queryData.limit = limit; }
        if(startDate) { queryData.startDate = startDate; }
        if(endDate) { queryData.endDate = endDate; }
        return API.get('leaderboard/global/players/score', 'Could not get leaderboard.', queryData);
    };
    
    // Global Team Score Leaderboard
    api.getGlobalTeamsLeaderboard = function (limit, startDate, endDate) {
        var queryData = {};
        if(limit) { queryData.limit = limit; }
        if(startDate) { queryData.startDate = startDate; }
        if(endDate) { queryData.endDate = endDate; }
        return API.get('leaderboard/global/teams/score', 'Could not get leaderboard.', queryData);
    };
    
    // Per Joint Player Score Leaderboard
    api.getVenuePlayersLeaderboard = function (venueId, limit, startDate, endDate) {
        var queryData = {};
        if(limit) { queryData.limit = limit; }
        if(startDate) { queryData.startDate = startDate; }
        if(endDate) { queryData.endDate = endDate; }
        return API.get('leaderboard/joint/players/score/' + venueId + '', 'Could not get leaderboard.', queryData);
    };
    
    // Per Joint Team Score Leaderboard
    api.getVenueTeamsLeaderboard = function (venueId, limit, startDate, endDate) {
        var queryData = {};
        if(limit) { queryData.limit = limit; }
        if(startDate) { queryData.startDate = startDate; }
        if(endDate) { queryData.endDate = endDate; }
        return API.get('leaderboard/joint/teams/score/' + venueId + '', 'Could not get leaderboard.', queryData);
    };
    
    // Global Player Checkin Leaderboard
    api.getGlobalPlayerCheckinsLeaderboard = function (limit, startDate, endDate) {
        var queryData = {};
        if(limit) { queryData.limit = limit; }
        if(startDate) { queryData.startDate = startDate; }
        if(endDate) { queryData.endDate = endDate; }
        return API.get('leaderboard/global/players/checkins', 'Could not get leaderboard.', queryData);
    };
    
    // Global Team Checkin Leaderboard
    api.getGlobalTeamCheckinsLeaderboard = function (limit, startDate, endDate) {
        var queryData = {};
        if(limit) { queryData.limit = limit; }
        if(startDate) { queryData.startDate = startDate; }
        if(endDate) { queryData.endDate = endDate; }
        return API.get('leaderboard/global/teams/checkins', 'Could not get leaderboard.', queryData);
    };
    
    // Per Joint Player Checkins Leaderboard
    api.getVenuePlayerCheckinsLeaderboard = function (venueId, limit, startDate, endDate) {
        var queryData = {};
        if(limit) { queryData.limit = limit; }
        if(startDate) { queryData.startDate = startDate; }
        if(endDate) { queryData.endDate = endDate; }
        return API.get('leaderboard/joint/players/checkins/'  + venueId, 'Could not get leaderboard.', queryData);
    };
    
    // Per Joint Team Checkins Leaderboard
    api.getVenueTeamCheckinsLeaderboard = function (venueId, limit, startDate, endDate) {
        var queryData = {};
        if(limit) { queryData.limit = limit; }
        if(startDate) { queryData.startDate = startDate; }
        if(endDate) { queryData.endDate = endDate; }
        return API.get('leaderboard/joint/teams/checkins/'  + venueId, 'Could not get leaderboard.', queryData);
    };
    
    // List of Joints / Venues
    api.getListOfJoints = function () {
        return API.get('leaderboard/list-joints/hot-salsa', 'Could not get list of joints.');
    };

    return api;
}]);