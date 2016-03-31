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

            self.update = function(updatedGame) {
                // Game Details
                _game.name = updatedGame.name || _game.name;
                _game.scheduled = updatedGame.scheduled || _game.scheduled;
                _game.started = updatedGame.started || _game.started;
                _game.ended = updatedGame.ended || _game.ended;
                _game.maxPoints = parseFloat(updatedGame.maxPoints) || _game.maxPoints;
                // Host
                _game.hostId = updatedGame.hostId || _game.hostId;
                _game.hostName = updatedGame.hostName || _game.hostName;
                // Venue
                _game.venueId = updatedGame.venueId || _game.venueId;
                _game.venue = updatedGame.venue || _game.venue;
                // Scoreboard Data
                _game.teams = updatedGame.teams || _game.teams;
                _game.rounds = updatedGame.rounds || _game.rounds;
                
                _game.numberOfRounds = Object.keys(_game.rounds).length;

                // Current Round 
                _game.currentRoundNumber = updatedGame.currentRoundNumber || _game.currentRoundNumber;
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
                    var allTeamGameScores = new Array();
                    var allTeamRoundScores = new Array();
                    var teamRoundScores = new Array();
                    
                    for(var teamKey in _game.teams) { // For every team
                        
                        for(var teamRoundKey in _game.teams[teamKey].rounds) { // For every team round
                            if(!angular.isDefined(allTeamRoundScores[teamRoundKey])) {
                                allTeamRoundScores[teamRoundKey] = new Array();
                            }
                            
                            // Get all unique round scores for each team
                            var score = parseFloat(_game.teams[teamKey].rounds[teamRoundKey].roundScore);
                            if(allTeamRoundScores[teamRoundKey].indexOf(score) < 0) {
                                allTeamRoundScores[teamRoundKey].push(score);
                            }
                            // Get all scores for this team
                            teamRoundScores.push(score);
                        }
                        ///// GAME SCORE
                        // This teams total round score
                        _game.teams[teamKey].gameScore = teamRoundScores.reduce(function(pv, cv) { return pv + cv; }, 0);
                        
                        // Get all unique team game scores
                        if(allTeamGameScores.indexOf(_game.teams[teamKey].gameScore) < 0) {
                            allTeamGameScores.push(_game.teams[teamKey].gameScore);
                        }
                    }
                    
                    // Sort all the game scores
                    allTeamGameScores.sort(function(a, b){ 
                        var result = b-a; 
                        console.log("allTeamGameScores.sort: " + b + " - " + a + " = " + result);
                        return b-a; 
                    });
                    
                    for(var teamKey in _game.teams) { // For every team
                        
                        for(var teamRoundKey in _game.teams[teamKey].rounds) { // For every team round
                            // Sort the scores in this round
                            allTeamRoundScores[teamRoundKey].sort(function(a, b){ 
                                var result = b-a; 
                                console.log("allTeamRoundScores[teamRoundKey].sort: [" + teamRoundKey + "] " + b + " - " + a + " = " + result);
                                return b-a; 
                            });
                            ///// ROUND RANK
                            var score = parseFloat(_game.teams[teamKey].rounds[teamRoundKey].roundScore);
                            var index = allTeamRoundScores[teamRoundKey].indexOf(score);
                            _game.teams[teamKey].rounds[teamRoundKey].roundRank = (index < 0) ? 0 : index + 1;
                        }
                    
                        ///// GAME RANK
                        // Calculate Game Scores
                        var score = parseFloat(_game.teams[teamKey].gameScore);
                        var index = allTeamGameScores.indexOf(score);
                        _game.teams[teamKey].gameRank = (index < 0) ? 0 : index + 1;
                        
                    }
                }
            };
        
    }]); // END: Game()
