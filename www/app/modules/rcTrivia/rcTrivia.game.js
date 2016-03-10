'use strict';

/* 
 * Trivia Game Service
 */

angular.module('rcTrivia.game', [])
    .factory('TriviaGame', ['$q', '$filter', 'ApiRoutesGames', 
        function($q, $filter, ApiRoutesGames) {
            
        var self = this;
        var api = {};
        
            function Game(newGame) {
                var game = this;
                
                game.getGame = function() {
                    var ga = {
                        'id' : game.id,
                        'name' : game.name,
                        'scheduled' : game.scheduled,
                        'started' : game.started,
                        'ended' : game.ended,
                        'totalRounds' : game.totalRounds,
                        'maxPoints' : game.maxPoints,

                        'host' : game.host,
                        'venue' : game.venue,
                        'teams' : game.teams,
                        'round' : game.round,
                        'rounds' : game.rounds
                    };
                    return ga;
                };
                
                game.getRound = function(roundNumber) {
                    var found = $filter('filter')(game.rounds, {'roundNumber': roundNumber}, true);
                    return (angular.isDefined(found[0]) && angular.isDefined(found[0].questions)) ? found[0] : false;
                };
                
                game.addRound = function(round) {
                    var cont = true;
                    for(var i = 0; i < game.rounds.length; i++) {
                        if(cont && game.rounds[i].roundNumber == round.roundNumber) {
                            game.rounds[i] = round;
                            cont = false;
                            break;
                        }
                    }
                    
                    if(cont) {
                        game.rounds.push(round);
                    }
                };
                
                game.viewRound = function(roundNumber, newRound) {
                    if(newRound) {
                        game.addRound(newRound);
                    }
                    var found = game.getRound(roundNumber);
                    game.round = (found) ? found : {};
                    
                };
                
                /* Init */
                game.id = parseInt(newGame.id) || 0;
                game.name = newGame.name || 0;
                game.scheduled = newGame.scheduled || 0;
                game.started = newGame.started || 0;
                game.ended = newGame.ended || 0;
                game.totalRounds = (newGame.rounds) ? newGame.rounds.length : 0;
                game.maxPoints = parseFloat(newGame.maxPoints) || 0.00;

                game.host = newGame.host || {};
                game.venue = newGame.venue || {};
                game.teams = newGame.teams || [];
                game.rounds = newGame.rounds || [];
                
                // Current Round - If one was sent set it as current round
                if (angular.isDefined(newGame.round)) {
                    game.viewRound(newGame.round.roundNumber, newGame.round);
                } else {
                    game.round = {};
                }
            }
        
        api.getGame = function() {
            return self.game.getGame();
        };
    
        api.loadGame = function(gameId, roundNumber) {
            return $q(function (resolve, reject) {
                    ApiRoutesGames.getGame(gameId, roundNumber).then(function (result) {
                            self.game = new Game(result.game);
                            resolve(self.game.getGame());
                        }, function (error) {
                            reject(error);
                        });
                });          
        };
        
        api.loadRound = function(roundNumber) {
            return $q(function (resolve, reject) {
                var round = self.game.viewRound(roundNumber);
                
                if(round) {
                    resolve(round);
                } else {
                    ApiRoutesGames.getRound(self.game.id, roundNumber).then(function (result) {
                        self.game.viewRound(result.round.roundNumber, result.round);
                        resolve(self.game.getGame());
                    }, function (error) {
                        reject(error);
                    });
                }
            });          
        };
        
        api.startGame = function() {
            return $q(function (resolve, reject) {
                ApiRoutesGames.startGame(self.game.id).then(function (result) {
                    self.game.started = result.started;
                    resolve(self.game.getGame());
                }, function (error) {
                    reject(error);
                });
            });    
        };
        
        api.endGame = function() {
            return $q(function (resolve, reject) {
                ApiRoutesGames.endGame(self.game.id).then(function (result) {
                    self.game.ended = result.ended;
                    resolve(self.game.getGame());
                }, function (error) {
                    reject(error);
                });
            });    
        };
        
        api.teamAnsweredIncorrectly = function(teamId, questionId) {
            for(var i = 0; i < self.game.round.teams.length; i++) {
                if(self.game.round.teams[i].teamId == teamId) {
                    for (var q = 0; q < self.game.round.teams[i].scores.length; q++) {
                        if (self.game.round.teams[i].scores[q].questionId == questionId) {
                            self.game.round.teams[i].scores[q].questionScore = 0;
                            break;
                        }
                    }
                    break;
                }
            }
            return self.game.getGame();
        };
        
        api.teamAnsweredCorrectly = function(teamId, questionId) {
            var maxPoints = 1;
            for(var i = 0; i < self.game.round.questions.length; i++) {
                if(self.game.round.questions[i].questionId == questionId) {
                    maxPoints = self.game.round.questions[i].maxPoints;
                    break;
                }
            }
            
            for(var i = 0; i < self.game.round.teams.length; i++) {
                if(self.game.round.teams[i].teamId == teamId) {
                    for (var q = 0; q < self.game.round.teams[i].scores.length; q++) {
                        if (self.game.round.teams[i].scores[q].questionId == questionId) {
                            self.game.round.teams[i].scores[q].questionScore = maxPoints;
                            break;
                        }
                    }
                    break;
                }
            }
            return self.game.getGame();
        };
        
        return api;
    }]);