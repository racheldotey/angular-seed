'use strict';

/* 
 * Host Scoreboard Page
 * 
 * Controller the game for the host.
 */

angular.module('app.host.scoreboard', [])
    .controller('HostScoreboardDashboardCtrl', ['$scope', 'AlertConfirmService', 'TriviaHost', 'currentGame', 
        function($scope, AlertConfirmService, TriviaHost, currentGame) {
            $scope.game = currentGame;
            
            // Button Click Events
            
            $scope.buttonStartGame = function() {
                AlertConfirmService.confirm('Are you sure you want to start this game? It cannot be paused once started.', 'Confirm Start Game.')
                    .result.then(function () {
                    }, function (declined) {});
            };
            
            $scope.buttonEndGame = function() {
                AlertConfirmService.confirm('Are you sure you want to end this game? It cannot be started again once it has been closed.', 'Confirm End Game.')
                    .result.then(function () {
                        AlertConfirmService.confirm('Are you sure you positive you would like to close this game? It will finalize team scores.', 'Warning! Closing Game.')
                            .result.then(function () {
                                
                            }, function (declined) {});
                    }, function (declined) {});
            };
            
            $scope.buttonAddRound = function() {
                
            };
            
            $scope.buttonAddQuestion = function() {
                
            };
            
            $scope.buttonAddTeam = function() {
                
            };
            
            $scope.buttonAddPlayer = function() {
                
            };
    }]);