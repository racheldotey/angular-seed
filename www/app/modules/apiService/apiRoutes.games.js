'use strict';

/* 
 * API Routes for Games
 * 
 * API calls related to games and the scoreboard.
 */

angular.module('apiRoutes.games', [])
.factory('ApiRoutesGames', ['ApiService', function (API) {
        
    var api = {};

    api.getGame = function(id) {
        return API.get('game/get/' + id, 'Could not get game.');
    };
    
    return api;
}]);