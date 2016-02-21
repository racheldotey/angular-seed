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
        return API.get('trivia/get/' + gameId + '/' + roundNumber, 'Could not get game.');
    };

    api.getRound = function(gameId, roundNumber) {
        return API.get('trivia/round/get/' + gameId + '/' + roundNumber, 'Could not get game round.');
    };



    api.startGame = function(gameId) {
        return API.get('trivia/start/' + gameId, 'Could not start game.');
    };

    api.endGame = function(gameId) {
        return API.get('trivia/end/' + gameId, 'Could not end game.');
    };



    api.addGameRound = function(gameId, roundNumber) {
        return API.get('trivia/round/get/' + gameId + '/' + roundNumber, 'Could not get game round.');
    };
    
    api.addGameRoundQuestion = function(gameId, roundId, roundName) {
        return API.get('trivia/round/get/' + gameId + '/' + roundNumber, 'Could not get game round.');
    };

    api.addTeamToGame = function(gameId, roundNumber) {
        return API.get('trivia/round/get/' + gameId + '/' + roundNumber, 'Could not get game round.');
    };

    api.getRound = function(gameId, roundNumber) {
        return API.get('trivia/round/get/' + gameId + '/' + roundNumber, 'Could not get game round.');
    };
    
    return api;
}]);