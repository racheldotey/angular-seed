'use strict';

/* 
 * API Routes for Games
 * 
 * API calls related to games and the scoreboard.
 */

angular.module('apiRoutes.games', [])
.factory('ApiRoutesGames', ['ApiService', function (API) {
        
    var api = {};

    api.getGame = function(gameId, roundNumber) {
        return API.get('game/get/' + gameId + '/' + roundNumber, 'Could not get game.');
    };

    api.getRound = function(gameId, roundNumber) {
        return API.get('game/round/get/' + gameId + '/' + roundNumber, 'Could not get game round.');
    };
    
    return api;
}]);