'use strict';

/* 
 * Trivia Game Service
 */

angular.module('rcTrivia.game', [])
    .factory('TriviaGame', ['$q', 'ApiRoutesGames', function($q, ApiRoutesGames) {
        var self = this;
        var api = {};
        
            function Game(newGame) {

                this.id = newGame.id | 0;
                this.name = newGame.name | 0;
                this.scheduled = newGame.scheduled | 0;
                this.started = newGame.started | 0;
                this.ended = newGame.ended | 0;
                this.totalRounds = newGame.totalRounds | 0;
                this.maxPoints = newGame.maxPoints | 0.00;

                this.host = newGame.host | {};
                this.venue = newGame.venue | {};
                this.teams = newGame.teams | {};
                this.rounds = newGame.rounds | {};
                
                this.getGame = function(id) {
                    return {
                        'id' : this.id,
                        'name' : this.name,
                        'scheduled' : this.scheduled,
                        'started' : this.started,
                        'ended' : this.ended,
                        'totalRounds' : this.totalRounds,
                        'maxPoints' : this.maxPoints,

                        'host' : this.host,
                        'venue' : this.venue
                    };
                };
                
                this.getTeams = function(id) {
                    return this.rounds[id] | false;
                };
                
                this.getRound = function(id) {
                    return this.rounds[id] | false;
                };
                
                this.addRound = function(round) {
                    this.rounds[round.id] = round;
                    return this.rounds[round.id];
                };
            }
        
        self.game = new Game({});
    
    
    
        
        api.loadGame = function(id, round) {
            return $q(function (resolve, reject) {
                ApiRoutesGames.getGame(id).then(function (result) {
                        self.game = new Game({});
                    }, function (error) {
                        reject(error);
                    });
            });          
        };
        
        api.loadRound = function(id) {
                ApiRoutesGames.getRound(id).then(function(result) {

                    }, function(error) {

                    });
        };
        
        return api;
    }]);