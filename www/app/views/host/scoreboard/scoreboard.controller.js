'use strict';

/* 
 * Player Scoreboard Page
 * 
 * Controller the game for the player.
 */

angular.module('app.host.scoreboard', [])
    .controller('HostScoreboardDashboardCtrl', ['$scope', 'currentGame', 'TriviaGame', 'AlertConfirmService', 'TriviaModalService', 'DTOptionsBuilder', 'DTColumnDefBuilder',
        function($scope, currentGame, TriviaGame, AlertConfirmService, TriviaModalService, DTOptionsBuilder, DTColumnDefBuilder) {
            $scope.game = currentGame;
                
            $scope.dtScoreboard = {};
            $scope.dtScoreboard.dtOptions = DTOptionsBuilder.newOptions()
                .withDOM('t')
                .withOption('scrollY', '300px')
                .withOption('scrollX', '100%')
                .withOption('scrollCollapse', true)
                .withOption('paging', false)
                .withFixedColumns({ leftColumns: 1 });
            $scope.dtScoreboard.dtColumnDefs = [
                DTColumnDefBuilder.newColumnDef(0),
                DTColumnDefBuilder.newColumnDef(1),
                DTColumnDefBuilder.newColumnDef(2),
                DTColumnDefBuilder.newColumnDef(3),
                DTColumnDefBuilder.newColumnDef(4),
                DTColumnDefBuilder.newColumnDef(5),
                DTColumnDefBuilder.newColumnDef(6),
                DTColumnDefBuilder.newColumnDef(7),
                DTColumnDefBuilder.newColumnDef(8)
            ];
                
            $scope.buttonViewRound = function(roundNumber) {
                TriviaGame.loadRound(roundNumber).then(function (result) {
                        $scope.game = result;
                        console.log($scope.game);
                    }, function (error) {
                        console.log(error);
                    });
            };
            
            $scope.buttonStartGame = function() {
                AlertConfirmService.confirm('Are you sure you want to start this game? It cannot be paused once started.', 'Confirm Start Game.')
                    .result.then(function () {
                        TriviaGame.startGame().then(function (result) {
                            $scope.game = result;
                            console.log($scope.game);
                        }, function (error) {
                            console.log(error);
                        });
                    }, function (declined) {});
            };
            
            $scope.buttonEndGame = function() {
                AlertConfirmService.confirm('Are you sure you want to end this game? It cannot be started again once it has been closed.', 'Confirm End Game.')
                    .result.then(function () {
                        AlertConfirmService.confirm('Are you sure you positive you would like to close this game? It will finalize team scores.', 'Warning! Closing Game.')
                            .result.then(function () {
                                TriviaGame.endGame().then(function (result) {
                                    $scope.game = result;
                                    console.log($scope.game);
                                }, function (error) {
                                    console.log(error);
                                });
                            }, function (declined) {});
                    }, function (declined) {});
            };
            
            // Add Trivia Team Modal
            $scope.buttonAddTeam = function() {
                var modalInstance = TriviaModalService.openAddTeam($scope.game.id);
                modalInstance.result.then(function (result) {
                    console.log(result);
                    $scope.game = result;
                }, function () {});
                
            };
            
            // Add Trivia Player Modal
            $scope.buttonAddPlayer = function() {
                var modalInstance = TriviaModalService.openAddPlayer($scope.game.id);
                modalInstance.result.then(function (result) {
                    console.log(result);
                    $scope.game = result;
                }, function () {});
                
            };
            
            // Add Trivia Round Modal
            $scope.buttonAddRound = function() {
                var modalInstance = TriviaModalService.openEditRound($scope.game.id);
                modalInstance.result.then(function (result) {
                    console.log(result);
                    $scope.game = result;
                }, function () {});
            };
            
            // Add Trivia Round Question Modal
            $scope.buttonAddQuestion = function() {
                var modalInstance = TriviaModalService.openEditQuestion();
                modalInstance.result.then(function (result) {
                    console.log(result);
                    $scope.game = result;
                }, function () {});
            };
            
            $scope.buttonQuestionWrong = function(teamId, questionId) {
                console.log('buttonQuestionWrong');
                $scope.game = TriviaGame.teamAnsweredIncorrectly(teamId, questionId);      
            };
            
            $scope.buttonQuestionCorrect = function(teamId, questionId) {
                console.log('buttonQuestionCorrect');
                $scope.game = TriviaGame.teamAnsweredCorrectly(teamId, questionId);                
            };
    }]);