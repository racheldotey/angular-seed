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

                // Current Round 
                _game.currentRoundNumber = viewRound || 0;
                _game.numberOfRounds = Object.keys(_game.rounds).length;
            };
            
            self.updateGameScoreReferences = function() {
                if(angular.isDefined(_game.round.roundNumber)) {
                    
                }
            };
            
            self.getGame = function() {
                if(angular.isDefined(_game.id)) {
                    return _game;
                } else {
                    return false;
                }
            };

            self.findRoundIndexByNumber = function(roundNumber) {
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

            self.addRound = function(round) {
                var found = self.findRoundIndexByNumber(round.roundNumber);
                if(found === false) {
                    _game.rounds.push(round);
                } else {
                    _game.rounds[found] = round;
                }
            };

            self.viewRound = function(roundNumber, newRound) {
                _game.currentRoundNumber = roundNumber;
                if(angular.isDefined(_game.round.roundNumber)) {
                    // Save current round progress
                    var currentRoundIndex = self.findRoundIndexByNumber(_game.round.roundNumber);
                    if (angular.isNumber(currentRoundIndex)) {
                        _game.rounds[currentRoundIndex] = _game.round;
                    }
                }
                    
                // If its a new round add it
                if(newRound) {
                    self.addRound(newRound);
                }
                
                // Set the current round to the new round number
                var found = self.findRoundIndexByNumber(roundNumber);                    
                if(angular.isNumber(found) && angular.isDefined(_game.rounds[found].questions)) {
                    _game.round = _game.rounds[found];
                    _game.currentRoundNumber = angular.copy(_game.round.roundNumber);
                    return true;
                } else {
                    return false;
                }
            };
            
            // Setup update totals event            
            self.updateTotals = function(teamId) {
                var teams = _game.round.teams || [];
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
            };
        
    }]); // END: Game()
