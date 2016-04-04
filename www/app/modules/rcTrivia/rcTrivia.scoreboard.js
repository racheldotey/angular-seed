'use strict';

/* 
 * Trivia Scoreboard Display Service
 */

angular.module('rcTrivia.scoreboard', ['rcTrivia.game'])
    .factory('TriviaScoreboard', ['$q', 'ApiRoutesGames', 'TriviaGame',
        function($q, ApiRoutesGames, TriviaGame) {
            
        var api = {};        
        
        api.getGame = TriviaGame.getGame;
            
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
                        TriviaGame.init(result.game, roundNumber);
                        resolve(api.getGame());
                    }, function (error) {
                        reject(error);
                    });
                }
            });          
        };
        
        api.loadRound = function(roundNumber) {
            return $q(function (resolve, reject) {
                TriviaGame.viewRound(roundNumber);
                var loadedGame = api.getGame();
                if(loadedGame) {
                    resolve(loadedGame);
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
                            TriviaGame.update(result.game);
                            resolve(api.getGame());
                        }, function (error) {
                            reject(error);
                        });
                } else {
                    reject("No game is loaded.");
                }
                
            });          
        };
        
        api.editRound = function(round) {
            return $q(function (resolve, reject) {
                var loadedGame = api.getGame();
                if(loadedGame) {
                    ApiRoutesGames.editGameRound(round).then(
                        function (result) {
                            TriviaGame.update(result.game);
                            resolve(api.getGame());
                        }, function (error) {
                            reject(error);
                        });
                } else {
                    reject("No game is loaded.");
                }
            });          
        };
        
        api.deleteRound = function(roundId) {
            return $q(function (resolve, reject) {
                var loadedGame = api.getGame();
                if(loadedGame) {
                    ApiRoutesGames.deleteGameRound(roundId).then(
                        function (result) {
                            TriviaGame.update(result.game);
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
                            TriviaGame.update(result.game);
                            resolve(api.getGame());
                        }, function (error) {
                            reject(error);
                        });
                } else {
                    reject("No game is loaded.");
                }
            });          
        };
        
        api.editQuestion = function(question) {
            return $q(function (resolve, reject) {
                var loadedGame = api.getGame();
                if(loadedGame) {
                    ApiRoutesGames.editGameRoundQuestion(question).then(
                        function (result) {
                            TriviaGame.update(result.game);
                            resolve(api.getGame());
                        }, function (error) {
                            reject(error);
                        });
                } else {
                    reject("No game is loaded.");
                }
            });          
        };
        
        api.deleteQuestion = function(questionId) {
            return $q(function (resolve, reject) {
                var loadedGame = api.getGame();
                if(loadedGame) {
                    ApiRoutesGames.deleteGameRoundQuestion(questionId).then(
                        function (result) {
                            TriviaGame.update(result.game);
                            resolve(api.getGame());
                        }, function (error) {
                            reject(error);
                        });
                } else {
                    reject("No game is loaded.");
                }
            });          
        };
        
        api.addTeamToGame = function(teamId) {
            return $q(function (resolve, reject) {
                var loadedGame = api.getGame();
                if(loadedGame) {
                    ApiRoutesGames.addTeamToGame(loadedGame.id, loadedGame.currentRoundNumber, teamId).then(
                        function (result) {
                            TriviaGame.update(result.game);
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
                        TriviaGame.setStarted(result.started);
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
                        TriviaGame.setEnded(result.ended);
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
                    var data = TriviaGame.getChangedScores();
                    
                    if(data.length <= 0) {
                        resolve(api.getGame());
                    } else {
                        ApiRoutesGames.saveScoreboard(loadedGame.id, data).then(function (result) {
                            TriviaGame.update(result.game);
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
        
        api.updateTeamRankings = function(teamId) {            
            return $q(function (resolve, reject) {
                TriviaGame.updateTotals(teamId);
                resolve(api.getGame());
            });    
        };
        
        return api;
    }]);