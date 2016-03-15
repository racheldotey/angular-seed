'use strict';

/* 
 * Trivia Scoreboard Display Service
 */

angular.module('rcTrivia.scoreboard', [])
    .factory('TriviaScoreboard', ['ApiRoutesGames', function(ApiRoutesGames) {
        var self = this;
        var api = {};
        
        api.getGame = function() {
            // Safely return the game
            return (self.game) ? self.game.getGame() : false;
        };
    
        api.loadGame = function(gameId, roundNumber) {
            return $q(function (resolve, reject) {
                // Has the game been loaded
                var loaded = api.getGame();
                if(loaded && angular.equals(gameId, loaded.id)) {
                    // If the game has already been loaded
                    // Just load the requested round
                    api.loadRound(roundNumber).then(function (result) {
                        resolve(result);
                    }, function (error) {
                        reject(error);
                    });
                } else {
                    // If no game (or a different game) is loaded
                    // Then reload the whole game
                    ApiRoutesGames.getGame(gameId, roundNumber).then(function (result) {
                        self.game = new Game(result.game);
                        resolve(self.game.getGame());
                    }, function (error) {
                        reject(error);
                    });
                }
            });          
        };
        
        api.loadRound = function(roundNumber) {
            return $q(function (resolve, reject) {
                var loaded = api.getGame();
                if(loaded) {
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
                } else {
                    reject("No game is loaded.");
                }
            });          
        };
        
        api.newRound = function(round) {
            return $q(function (resolve, reject) {
                var loaded = api.getGame();
                if(loaded) {
                    ApiRoutesGames.addGameRound(round).then(
                        function (result) {
                            self.game.viewRound(result.round.roundNumber, result.round);
                            resolve(self.game.getGame());
                        }, function (error) {
                            reject(error);
                        });
                } else {
                    reject("No game is loaded.");
                }
                
            });          
        };
        
        api.newQuestion = function(question) {
            return $q(function (resolve, reject) {
                var loaded = api.getGame();
                if(loaded) {
                    ApiRoutesGames.addGameRoundQuestion(question).then(
                        function (result) {
                            self.game.viewRound(result.round.roundNumber, result.round);
                            resolve(self.game.getGame());
                        }, function (error) {
                            reject(error);
                        });
                } else {
                    reject("No game is loaded.");
                }
                
            });          
        };
        
        api.startGame = function() {
            return $q(function (resolve, reject) {
                var loaded = api.getGame();
                if(loaded) {
                    ApiRoutesGames.startGame(self.game.id).then(function (result) {
                        self.game.started = result.started;
                        resolve(self.game.getGame());
                    }, function (error) {
                        reject(error);
                    });
                } else {
                    reject("No game is loaded.");
                }
            });    
        };
        
        api.endGame = function() {
            return $q(function (resolve, reject) {
                var loaded = api.getGame();
                if(loaded) {
                    ApiRoutesGames.endGame(self.game.id).then(function (result) {
                        self.game.ended = result.ended;
                        resolve(self.game.getGame());
                    }, function (error) {
                        reject(error);
                    });
                } else {
                    reject("No game is loaded.");
                }
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