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



    api.addGame = function(game) {
        if(angular.isUndefined(game.venueId) || 
            angular.isUndefined(game.hostId) ||
            angular.isUndefined(game.scheduled) || 
            angular.isUndefined(game.name)) {
            return API.reject('Invalid game please check your parameters and try again.');
        }
        return API.post('trivia/insert/game', game, 'Could not insert game.');
    };

    api.addGameRound = function(round) {
        if(angular.isUndefined(round.gameId) || angular.isUndefined(round.name)) {
            return API.reject('Invalid game round please check your parameters and try again.');
        }
        return API.post('trivia/insert/round', round, 'Could not insert game round.');
    };
    
    api.addGameRoundQuestion = function(question) {
        if(angular.isUndefined(question.gameId) || angular.isUndefined(question.roundId) || angular.isUndefined(question.question)) {
            return API.reject('Invalid game question please check your parameters and try again.');
        }
        return API.post('trivia/insert/question', question, 'Could not insert game question.');
    };




    api.addTeamToGame = function(gameId, roundNumber) {
        return API.get('trivia/round/get/' + gameId + '/' + roundNumber, 'Could not get game round.');
    };

    api.getRound = function(gameId, roundNumber) {
        return API.get('trivia/round/get/' + gameId + '/' + roundNumber, 'Could not get game round.');
    };
    
    return api;
}]);