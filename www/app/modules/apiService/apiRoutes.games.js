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
    api.getGameHost = function(hostId) {
        return API.get('trivia/host/get/' + hostId, 'Could not get game host.');
    };

    /* Select Game for Display */
    api.getGame = function(gameId, roundNumber) {
        return API.get('trivia/game/get/' + gameId + '/' + roundNumber, 'Could not get game.');
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
    
    /* Edit Round */
    api.editGameRound = function(round) {
        if(angular.isUndefined(round.roundId) || angular.isUndefined(round.gameId) || angular.isUndefined(round.name) || angular.isUndefined(round.defaultQuestionPoints)) {
            return API.reject('Invalid game round please check your parameters and try again.');
        }
        return API.post('trivia/update/round/' + round.roundId, round, 'Could not save game round.');
    };
    
    /* Add new round to game */
    api.deleteGameRound = function(round) {
        if(angular.isUndefined(round.roundId) || angular.isUndefined(round.gameId)) {
            return API.reject('Invalid game round please check your parameters and try again.');
        }
        return API.post('trivia/delete/round/' + round.roundId, round, 'Could not delete game round.');
    };
    
    /* Add new question to round */
    api.addGameRoundQuestion = function(question) {
        if(angular.isUndefined(question.gameId) || angular.isUndefined(question.roundId) || angular.isUndefined(question.question)) {
            return API.reject('Invalid game question please check your parameters and try again.');
        }
        return API.post('trivia/insert/question', question, 'Could not insert game question.');
    };
    
    /* Edit Round Question */
    api.editGameRoundQuestion = function(question) {
        if(angular.isUndefined(question.questionId) || angular.isUndefined(question.gameId) || angular.isUndefined(question.roundId) || angular.isUndefined(question.question)) {
            return API.reject('Invalid game question please check your parameters and try again.');
        }
        return API.post('trivia/update/question/' + question.questionId, question, 'Could not save game question.');
    };
    
    /* Add new question to round */
    api.deleteGameRoundQuestion = function(question) {
        if(angular.isUndefined(question.questionId) || angular.isUndefined(question.gameId) || angular.isUndefined(question.roundId)) {
            return API.reject('Invalid game question please check your parameters and try again.');
        }
        return API.post('trivia/delete/question/' + question.questionId, question, 'Could not delete game question.');
    };
    

    /* Add team to game */
    api.addTeamToGame = function(gameId, roundNumber, teamId) {
        if(angular.isUndefined(gameId) || angular.isUndefined(roundNumber) || angular.isUndefined(teamId)) {
            return API.reject('Invalid team or game please check your parameters and try again.');
        }
        return API.post('trivia/checkin-team/' + gameId + '/' + roundNumber, { 'teamId' : teamId }, 'Could not check team into game.');
    };

    /* Start game */
    api.startGame = function(gameId) {
        return API.get('trivia/start/' + gameId, 'Could not start game.');
    };

    /* End game */
    api.endGame = function(gameId, rounds) {
        return API.post('trivia/end/' + gameId, rounds, 'Could not end game.');
    };

    /* End game */
    api.saveScoreboard = function(gameId, data) {
        if(!data.questions) {
            return API.reject('Invalid scoreboard please verify your information and try again.');
        }
        return API.post('trivia/save/scoreboard/' + gameId, data, 'Could not save game.');
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


    // Team Admin Functions
    
    api.addTeam = function(team) {
        if(!team.name) {
            return API.reject('Invalid team please verify your information and try again.');
        }
        return API.post('team/insert', team, 'Could not insert team.');
    };
    
    api.saveTeam = function(team) {
        if(!team.id || 
                !team.name) {
            return API.reject('Invalid team please verify your information and try again.');
        }
        return API.post('team/update/' + team.id, team, 'Could not save team.');
    };
    
    return api;
}]);