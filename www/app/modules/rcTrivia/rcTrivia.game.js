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

            var me = this;
            var _game = {};

            me.getGame = function() {
                if(angular.isDefined(_game.id)) {
                    var ga = {
                        'id' : _game.id,
                        'name' : _game.name,
                        'scheduled' : _game.scheduled,
                        'started' : _game.started,
                        'ended' : _game.ended,
                        'totalRounds' : _game.totalRounds,
                        'maxPoints' : _game.maxPoints,

                        'host' : _game.host,
                        'venue' : _game.venue,
                        'teams' : _game.teams,
                        'round' : _game.round,
                        'rounds' : _game.rounds
                    };
                    return angular.copy(ga);
                } else {
                    return false;
                }
            };
            
            me.updateTeamScores = function(round) {
                _game.round = round;
                var found = me.findRoundIndexByNumber(round.roundNumber);
                if(found !== false) {
                    _game.rounds[found] = round;
                }
            };

            me.findRoundIndexByNumber = function(roundNumber) {
                var found = false;
                for(var i = 0; i < _game.rounds.length; i++) {
                    if(found === false && 
                            parseInt(_game.rounds[i].roundNumber) === parseInt(roundNumber)) {
                        found = i;
                        break;
                    }
                }
                return found;
            };

            me.addRound = function(round) {
                var found = me.findRoundIndexByNumber(round.roundNumber);
                if(found === false) {
                    _game.rounds.push(round);
                } else {
                    _game.rounds[found] = round;
                }
            };

            me.viewRound = function(roundNumber, newRound) {
                if(newRound) {
                    me.addRound(newRound);
                }                    
                var found = me.findRoundIndexByNumber(roundNumber);                    
                if(angular.isNumber(found) && angular.isDefined(_game.rounds[found].questions)) {
                    _game.round = _game.rounds[found];
                    return true;
                } else {
                    _game.round = {};
                    return false;
                }
            };

            /* Init */
            _game.id = newGame.id || 0;
            _game.name = newGame.name || 0;
            _game.scheduled = newGame.scheduled || 0;
            _game.started = newGame.started || 0;
            _game.ended = newGame.ended || 0;
            _game.totalRounds = (newGame.rounds) ? newGame.rounds.length : 0;
            _game.maxPoints = parseFloat(newGame.maxPoints) || 0.00;

            _game.host = newGame.host || {};
            _game.venue = newGame.venue || {};
            _game.teams = newGame.teams || [];
            _game.rounds = newGame.rounds || [];

            // Current Round - If one was sent set it as current round
            if (angular.isDefined(newGame.round)) {
                _game.round = newGame.round;
                var roundIndex = me.findRoundIndexByNumber(newGame.round.roundNumber);
                if (angular.isNumber(roundIndex)) {
                    _game.rounds[roundIndex] = newGame.round;
                }
            } else {
                _game.round = {};
            }
        } // END: Game()
        
            
        api.getGame = function() {
            // Safely return the game
            return (self.game) ? self.game.getGame() : false;
        };
            
        api.loadGame = function(gameId, roundNumber) {
            return $q(function (resolve, reject) {
                // Has the game been loaded
                var loadedGame = api.getGame();
                if(loadedGame && parseInt(gameId) === parseInt(loadedGame.id)) {
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
                        resolve(api.getGame());
                    }, function (error) {
                        reject(error);
                    });
                }
            });          
        };
        
        api.loadRound = function(roundNumber) {
            return $q(function (resolve, reject) {
                var loadedGame = api.getGame();
                if(loadedGame) {
                    var round = self.game.viewRound(roundNumber);
                    if(round) {
                        resolve(api.getGame());
                    } else {
                        ApiRoutesGames.getRound(loadedGame.id, roundNumber).then(function (result) {
                            self.game.viewRound(result.round.roundNumber, result.round);
                            resolve(api.getGame());
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
                var loadedGame = api.getGame();
                if(loadedGame) {
                    ApiRoutesGames.addGameRound(round).then(
                        function (result) {
                            self.game.viewRound(result.round.roundNumber, result.round);
                            resolve(api.getGame());
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
                var loadedGame = api.getGame();
                if(loadedGame) {
                    ApiRoutesGames.addGameRoundQuestion(question).then(
                        function (result) {
                            self.game.viewRound(result.round.roundNumber, result.round);
                            resolve(api.getGame());
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
                var loadedGame = api.getGame();
                if(loadedGame) {
                    ApiRoutesGames.startGame(loadedGame.id).then(function (result) {
                        self.game.setStarted(result.started);
                        resolve(api.getGame());
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
                var loadedGame = api.getGame();
                if(loadedGame) {
                    ApiRoutesGames.endGame(loadedGame.id).then(function (result) {
                        self.game.setEnded(result.ended);
                        resolve(api.getGame());
                    }, function (error) {
                        reject(error);
                    });
                } else {
                    reject("No game is loaded.");
                }
            });    
        };
        
        api.updateRoundScores = function(round) {
            console.log("Update team scores, ", round);
            self.game.updateTeamScores(round);
        };
        
        return api;
    }]);