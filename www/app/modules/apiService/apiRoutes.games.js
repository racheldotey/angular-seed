'use strict';

/* 
 * API Routes for Games
 * 
 * API calls related to games and the scoreboard.
 */

angular.module('apiRoutes.games', [])
.factory('ApiRoutesGames', ['ApiService', function (API) {
        
    var api = {};
    
    // Host Functions

    /* Select Game for Display */
    api.getGame = function(gameId, roundNumber) {
        return API.get('trivia/get/' + gameId + '/' + roundNumber, 'Could not get game.');
    };

    /* Select round for game */
    api.getRound = function(gameId, roundNumber) {
        return API.get('trivia/round/get/' + gameId + '/' + roundNumber, 'Could not get game round.');
    };

    /* Insert game into database */
    api.addGame = function(game) {
        if(angular.isUndefined(game.venueId) || 
            angular.isUndefined(game.hostId) ||
            angular.isUndefined(game.scheduled) || 
            angular.isUndefined(game.name)) {
            return API.reject('Invalid game please check your parameters and try again.');
        }
        return API.post('trivia/insert/game', game, 'Could not insert game.');
    };

    /* Add new round to game */
    api.addGameRound = function(round) {
        if(angular.isUndefined(round.gameId) || angular.isUndefined(round.name) || angular.isUndefined(round.defaultQuestionPoints)) {
            return API.reject('Invalid game round please check your parameters and try again.');
        }
        return API.post('trivia/insert/round', round, 'Could not insert game round.');
    };
    
    /* Add new question to round */
    api.addGameRoundQuestion = function(question) {
        if(angular.isUndefined(question.gameId) || angular.isUndefined(question.roundId) || angular.isUndefined(question.question)) {
            return API.reject('Invalid game question please check your parameters and try again.');
        }
        return API.post('trivia/insert/question', question, 'Could not insert game question.');
    };

    /* Add team to game */
    api.addTeamToGame = function(gameId, roundNumber) {
        return API.get('trivia/round/get/' + gameId + '/' + roundNumber, 'Could not get game round.');
    };

    /* Start game */
    api.startGame = function(gameId) {
        return API.get('trivia/start/' + gameId, 'Could not start game.');
    };

    /* End game */
    api.endGame = function(gameId) {
        return API.get('trivia/end/' + gameId, 'Could not end game.');
    };


    // Trivia Admin Functions
    
    api.addVenue = function(venue) {
        if(!venue.venueName || 
                !venue.address || 
                !venue.city || 
                !venue.state || 
                !venue.zip) {
            return API.reject('Invalid venue please verify your information and try again.');
        }
        return API.post('venue/insert', venue, 'Could not insert venue.');
    };
    
    api.saveVenue = function(venue) {
        if(!venue.id || 
                !venue.venueName || 
                !venue.address || 
                !venue.city || 
                !venue.state || 
                !venue.zip) {
            return API.reject('Invalid venue please verify your information and try again.');
        }
        return API.post('venue/update/' + venue.id, venue, 'Could not save venue.');
    };
    
    return api;
}]);