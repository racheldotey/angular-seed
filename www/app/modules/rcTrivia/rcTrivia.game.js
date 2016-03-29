'use strict';

/* 
 * Trivia Game Service
 */

angular.module('rcTrivia.game', [])
    .service('TriviaGame', [function() {

            var self = this;
            var _game = false;

            self.init = function(newGame, viewRound) {
                if(!angular.isDefined(newGame.id)) {
                    return;
                }
                
                _game = {};
                // Game Details
                _game.id = newGame.id || 0;
                _game.name = newGame.name || 0;
                _game.scheduled = newGame.scheduled || false;
                _game.started = newGame.started || false;
                _game.ended = newGame.ended || false;
                _game.maxPoints = parseFloat(newGame.maxPoints) || 0.00;
                // Host
                _game.hostId = newGame.hostId || 0;
                _game.hostName = newGame.hostName || '';
                // Venue
                _game.venueId = newGame.venueId || 0;
                _game.venue = newGame.venue || '';
                // Scoreboard Data
                _game.teams = newGame.teams || [];
                _game.rounds = newGame.rounds || [];
                _game.numberOfRounds = Object.keys(_game.rounds).length;

                // Current Round 
                _game.currentRoundNumber = viewRound || 0;
                _game.round = _game.rounds[_game.currentRoundNumber] || {};
            };
            
            self.getGame = function() {
                if(angular.isDefined(_game.id)) {
                    return _game;
                } else {
                    return false;
                }
            };

            self.addRound = function(round) {
                console.log("cant do this. just refresh");
            };

            self.viewRound = function(roundNumber, newRound) {
                _game.currentRoundNumber = roundNumber;
                _game.round = _game.rounds[_game.currentRoundNumber];
            };
            
            // Setup update totals event            
            self.updateTotals = function(teamId) {                
                // Find and update the team who's score has been changed
                if(angular.isDefined(_game.teams[teamId])) {
                    
                    var changedTeamRound = _game.teams[teamId].rounds[_game.currentRoundNumber];
                    var roundScore = 0.0;
                    
                    for(var key in changedTeamRound.questions) {
                        roundScore = roundScore + parseFloat(changedTeamRound.questions[key].questionScore);
                    }
                    
                    var doCalculations = (roundScore !== parseFloat(changedTeamRound.roundScore)) ? true : false;
                    changedTeamRound.roundScore = roundScore;
                    
                }
                
                // If a change was made - (if not skip for speed)
                if(angular.isDefined(doCalculations) && doCalculations) {
                    
                    // Calculate Round Ranking
                    for(var key in changedTeamRound.questions) {
                        roundScore = roundScore + parseFloat(changedTeamRound.questions[key].questionScore);
                    }
                    
                    // Calculate Gamne Scores
                    
                    // Calculate Gamne Ranking
                }
                
                /*
                if(found) {
                    // Sort Round Scores
                    teams.sort(function (a, b) {
                        var x = parseFloat(a.roundScore);
                        var y = parseFloat(b.roundScore);
                        return (y > 0 && y > x) ? 1 : -1;
                    });
                    var lastScore = false;
                    var rank = 0;
                    for(var i = 0; i < teams.length; i++) {
                        // If the score was different last time increase rank
                        // if not tie them for rank
                        if(lastScore !== teams[i].roundScore) {
                            rank++;
                        }
                        lastScore = teams[i].roundScore;
                        teams[i].roundRank = rank;
                    }
                    
                    // Sort Game Scores
                    teams.sort(function (a, b) {
                        var x = parseFloat(a.gameScore);
                        var y = parseFloat(b.gameScore);
                        return (y > 0 && y > x) ? 1 : -1;
                    });
                    var lastScore = false;
                    var rank = 0;
                    for(var i = 0; i < teams.length; i++) {
                        // If the score was different last time increase rank
                        // if not tie them for rank
                        if(lastScore !== teams[i].gameScore) {
                            rank++;
                        }
                        lastScore = teams[i].gameScore;
                        teams[i].gameRank = rank;
                    }

                }
                */
            };
        
    }]); // END: Game()
