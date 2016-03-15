'use strict';

/* 
 * Trivia Game Service
 */

angular.module('rcTrivia.game', [])
    .factory('TriviaGame', ['$q', '$filter', 'ApiRoutesGames', 
        function($q, $filter, ApiRoutesGames) {
            
        var self = this;
        self.game = false;
        var api = {};
        
            function Game(newGame) {
                var game = this;
                
                game.getGame = function() {
                    if(angular.isDefined(game.id)) {
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
                        return angular.copy(ga);
                    } else {
                        return false;
                    }
                };
                
                game.findRoundIndexByNumber = function(roundNumber) {
                    var found = false;
                    for(var i = 0; i < game.rounds.length; i++) {
                        if(found === false && game.rounds[i].roundNumber == roundNumber) {
                            found = i;
                            console.log(">>>Found, ", game.rounds[i]);
                            break;
                        }
                    }
                    return found;
                };
                
                game.addRound = function(round) {
                    var found = game.findRoundIndexByNumber(round.roundNumber);
                    if(found === false) {
                        game.rounds.push(round);
                    } else {
                        game.rounds[found] = round;
                    }
                };
                
                game.viewRound = function(roundNumber, newRound) {
                    if(newRound) {
                        game.addRound(newRound);
                    }                    
                    var found = game.findRoundIndexByNumber(roundNumber);                    
                    if(angular.isNumber(found) && angular.isDefined(game.rounds[found].questions)) {
                        game.round = game.rounds[found];
                        return true;
                    } else {
                        game.round = {};
                        console.log("Error, could not find round #" + roundNumber + " to display.");
                        return false;
                    }
                };
                
                /* Init */
                game.id = newGame.id || 0;
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
                    game.round = newGame.round;
                    var roundIndex = game.findRoundIndexByNumber(newGame.round.roundNumber);
                    if (angular.isNumber(roundIndex)) {
                        game.rounds[roundIndex] = newGame.round;
                    }
                } else {
                    game.round = {};
                }
            }
        
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