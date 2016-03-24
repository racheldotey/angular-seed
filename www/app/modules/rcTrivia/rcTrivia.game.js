'use strict';

/* 
 * Trivia Game Service
 */

angular.module('rcTrivia.game', [])
    .factory('TriviaGame', ['$q', 'ApiRoutesGames', 
        function($q, ApiRoutesGames) {
            
        var self = this;
        var api = {};
        api.game = false;
        
        function Game(newGame) {

            var me = this;
            var _game = {};

            me.getGame = function() {
                if(angular.isDefined(_game.id)) {
                    // var ga = {
                    //    'id' : _game.id,
                    //    'name' : _game.name,
                    //    'scheduled' : _game.scheduled,
                    //    'started' : _game.started,
                    //    'ended' : _game.ended,
                    //    'maxPoints' : _game.maxPoints,
                    //
                    //    'host' : _game.host,
                    //    'venue' : _game.venue,
                    //    'teams' : _game.teams,
                    //    'round' : _game.round,
                    //    'rounds' : _game.rounds
                    // };
                    return _game;
                } else {
                    return false;
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
                if(angular.isDefined(_game.round.roundNumber)) {
                    // Save current round progress
                    var currentRoundIndex = me.findRoundIndexByNumber(_game.round.roundNumber);
                    if (angular.isNumber(currentRoundIndex)) {
                        _game.rounds[currentRoundIndex] = _game.round;
                    }
                }
                    
                // If its a new round add it
                if(newRound) {
                    me.addRound(newRound);
                }
                
                // Set the current round to the new round number
                var found = me.findRoundIndexByNumber(roundNumber);                    
                if(angular.isNumber(found) && angular.isDefined(_game.rounds[found].questions)) {
                    _game.round = _game.rounds[found];
                    return true;
                } else {
                    return false;
                }
            };
            
            // Setup update totals event            
            me.updateTotals = function(teamId) {
                var teams = _game.round.teams;
                var found = false;
                
                // Find and update the team who's score has been changed
                for(var i = 0; i < teams.length; i++) {
                    // Search for the team that was changed
                    if(found === false && parseInt(teams[i].teamId) === parseInt(teamId)) {
                    
                        // Update that teams round score
                        var roundScore = 0.0;
                        for(var s = 0; s < teams[i].scores.length; s++) {
                            roundScore = roundScore + parseFloat(teams[i].scores[s].questionScore);
                        }
                        var difference = roundScore - teams[i].roundScore;
                        teams[i].roundScore = roundScore;
                        teams[i].gameScore = parseFloat(teams[i].gameScore) + difference;
                        found = true;
                        break;
                    }
                }
                
                // If a change was made - (if not skip for speed)
                if(found) {
                    // Sort Round Scores
                    teams.sort(function (a, b) {
                        var x = parseFloat(a.roundScore);
                        var y = parseFloat(b.roundScore);
                        return (y > 0 && y > x) ? 1 : -1;
                    });
                    var lastScore = 0;
                    var rank = 1;
                    for(var i = 0; i < teams.length; i++) {
                        // If the score was different last time increase rank
                        // if not tie them for rank
                        if(lastScore !== teams[i].roundScore) {
                            lastScore = teams[i].roundScore;
                            teams[i].roundRank = rank;
                            rank++;
                        } else {
                            teams[i].roundRank = rank;
                        }
                    }
                    
                    // Sort Game Scores
                    teams.sort(function (a, b) {
                        var x = parseFloat(a.gameScore);
                        var y = parseFloat(b.gameScore);
                        return (y > 0 && y > x) ? 1 : -1;
                    });
                    var lastScore = 0;
                    var rank = 1;
                    for(var i = 0; i < teams.length; i++) {
                        // If the score was different last time increase rank
                        // if not tie them for rank
                        if(lastScore !== teams[i].gameScore) {
                            lastScore = teams[i].gameScore;
                            teams[i].gameRank = rank;
                            rank++;
                        } else {
                            teams[i].gameRank = rank;
                        }
                    }

                }
            };

            /* ***********
             * Init ******
             * ***********/
            _game.id = newGame.id || 0;
            _game.name = newGame.name || 0;
            _game.scheduled = newGame.scheduled || 0;
            _game.started = newGame.started || 0;
            _game.ended = newGame.ended || 0;
            _game.maxPoints = parseFloat(newGame.maxPoints) || 0.00;

            _game.host = newGame.host || {};
            _game.venue = newGame.venue || {};
            _game.teams = newGame.teams || [];
            _game.rounds = newGame.rounds || [];

            // Current Round - If one was sent set it as current round
            if (angular.isDefined(newGame.round)) {
                _game.round = newGame.round;
                var i = me.findRoundIndexByNumber(newGame.round.roundNumber);
                if (angular.isNumber(i)) {
                    _game.rounds[i] = newGame.round;
                }
                // If teams are set and the round is set
                // Update the totals
                if(angular.isDefined(_game.teams[0])) {
                    me.updateTotals(_game.teams[0].id);
                }
            } else {
                _game.round = {};
            }
            
        } // END: Game()
        
        api.getGame = function() {
            // Safely return the game
            return (api.game) ? api.game.getGame() : false;
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
                        api.game = new Game(result.game);
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
                if(loadedGame && loadedGame.round.roundNumber == roundNumber) {
                    resolve(api.getGame());
                } else if(loadedGame) {
                    var round = api.game.viewRound(roundNumber);
                    if(round) {
                        resolve(api.getGame());
                    } else {
                        ApiRoutesGames.getRound(loadedGame.id, roundNumber).then(function (result) {
                            api.game.viewRound(result.round.roundNumber, result.round);
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
                            api.game.viewRound(result.round.roundNumber, result.round);
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
                            api.game.viewRound(result.round.roundNumber, result.round);
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
                        api.game.setStarted(result.started);
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
                    var data = { 'rounds' : [] };
                    for(var i = 0; i < loadedGame.rounds.length; i++) {
                        if(angular.isDefined(loadedGame.rounds[i].teams)) {
                            data.rounds.push(loadedGame.rounds[i]);                            
                        }
                    }
                    ApiRoutesGames.endGame(loadedGame.id, data).then(function (result) {
                        api.game.setEnded(result.ended);
                        resolve(api.getGame());
                    }, function (error) {
                        reject(error);
                    });
                } else {
                    reject("No game is loaded.");
                }
            });    
        };
        
        api.saveScoreboard = function() {
            return $q(function (resolve, reject) {
                var loadedGame = api.getGame();
                if(loadedGame) {
                    var data = { 'rounds' : [] };
                    for(var i = 0; i < loadedGame.rounds.length; i++) {
                        if(angular.isDefined(loadedGame.rounds[i].teams)) {
                            data.rounds.push(loadedGame.rounds[i]);                            
                        }
                    }
                    ApiRoutesGames.saveScoreboard(loadedGame.id, data).then(function (result) {
                        console.log(result);
                        resolve(result);
                    }, function (error) {
                        reject(error);
                    });
                } else {
                    reject("No game is loaded.");
                }
            });    
        };
        
        api.updateTeamRankings = function(teamId) {            
            return $q(function (resolve, reject) {
                api.game.updateTotals(teamId);
                console.log(api.getGame());
                resolve(api.getGame());
            });    
        };
        
        return api;
    }]);