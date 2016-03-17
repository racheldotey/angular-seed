'use strict';

app = angular.module('rcTrivia.directives', []);

app.constant('THIS_DIRECTORY', (function () {
    var scripts = document.getElementsByTagName("script");
    var scriptPath = scripts[scripts.length - 1].src;
    return scriptPath.substring(0, scriptPath.lastIndexOf('/') + 1);
})());

app.directive('rcTriviaScoreboard', function(THIS_DIRECTORY) {
        
    return {
        restrict: 'A',          // Must be a attributeon a html tag
        templateUrl: THIS_DIRECTORY + 'views/scoreboard.html',
        scope: {
            game: '=rcTriviaScoreboard'
        },
        controller: ['$scope', '$state', '$window', 'TriviaGame', 'AlertConfirmService', 'TriviaModalService', 'DTOptionsBuilder', 'DTColumnDefBuilder',
            function($scope, $state, $window, TriviaGame, AlertConfirmService, TriviaModalService, DTOptionsBuilder, DTColumnDefBuilder) {
                
                $scope.unsavedState = false;
                $scope.displayQuickScoreButtons = false;
                
            $scope.scoreboardNavHamburger = { isopen: false };
            
            $scope.dtScoreboard = {};
            $scope.dtScoreboard.options = DTOptionsBuilder.newOptions()
                .withDOM('t')
                .withOption('scrollX', '100%')
                .withOption('scrollCollapse', true)
                .withOption('deferRender', true)
                .withOption('paging', false)
                .withFixedColumns({ leftColumns: 1 })
                .withOption('responsive', false)
                .withOption('drawCallback', function() {
                    $scope.setScoreboardHeight();
                });
                
                // Responsive table height
                
                $scope.setScoreboardHeight = function() {
                    // Get the height of everything that is not the table
                    var otherHeight = $('body').height() - $('.dataTables_scrollBody').height();
                    // Subtract the height of everything but the table from the
                    // height of the window to get whats left for the table
                    var tableHeight = $(window).height() - otherHeight - 1;
                    
                    // Max height on table
                    var scoreboardTable = $('table#scoreboard').height();
                    tableHeight = (tableHeight < scoreboardTable) ? tableHeight : scoreboardTable;
                    // Min Height on table
                    tableHeight = (tableHeight >= 300) ? tableHeight : 300;
                    
                    // Set the datatables wrapper to that height
                    $('.dataTables_scrollBody').css('height', tableHeight + 'px');
                };
                angular.element($window).on('resize', function () {
                    $scope.setScoreboardHeight();
                });
                
            $scope.buttonViewRound = function(roundNumber) {
                TriviaGame.loadRound(roundNumber).then(function (result) {
                        // Change the State (URL) parameters without reloading the page
                        // Used for deep linking
                        $scope.game = result;
                        $state.go($state.$current, {gameId: $scope.game.id, roundNumber: roundNumber}, {notify: false});
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
            
            var getMaxScore = function(questionId) {
                var maxPoints = 1;
                var questions = $scope.game.round.questions;
                
                for (var i = 0; i < questions.length; i++) {
                    if (parseInt(questions[i].questionId) === parseInt(questionId)) {
                        maxPoints = parseFloat(questions[i].maxPoints);
                        break;
                    }
                }
                return maxPoints;
            };
            
            var addToScore = function(teamId, questionId, maxScore) {
                
                var teams = $scope.game.round.teams;

                for (var i = 0; i < teams.length; i++) {
                    if (parseInt(teams[i].teamId) === parseInt(teamId)) {
                        for (var q = 0; q < teams[i].scores.length; q++) {
                            if (parseInt(teams[i].scores[q].questionId) === parseInt(questionId)) {
                                $scope.game.round.teams[i].scores[q].questionScore = maxScore + parseFloat(teams[i].scores[q].questionScore);
                                break;
                            }
                        }
                        break;
                    }
                }
            };
            
            $scope.buttonQuestionWrong = function(teamId, questionId) {
                $scope.unsavedState = true;
                var maxPoints = getMaxScore(questionId);
                addToScore(teamId, questionId, (maxPoints * -1));
                TriviaGame.updateRoundScores($scope.game.round);
            };
        
            $scope.buttonQuestionCorrect = function(teamId, questionId) {
                $scope.unsavedState = true;
                var maxPoints = getMaxScore(questionId);
                addToScore(teamId, questionId, maxPoints);
                TriviaGame.updateRoundScores($scope.game.round);
            }; 
            
        }],
        link: function(scope, element, attrs) {
            
        }
    };
    
});

app.directive('rcTriviaScoreboardRoundNavigation', function(THIS_DIRECTORY) {
    return {
        restrict: 'A',          // Must be a element attribute
        templateUrl: THIS_DIRECTORY + 'views/scoreboard.roundNavigation.html',
        scope: {
            totalRounds: '=totalRounds',
            currentRoundNumber: '=currentRoundNumber',
            buttonViewRound: '=viewRoundEvent'
        },
        link: function ($scope, element, attributes) {
            // Link - Programmatically modify resulting DOM element instances, 
            // add event listeners, and set up data binding. 
            
            $scope.currentRoundNumber = (angular.isDefined($scope.currentRoundNumber)) ? parseInt($scope.currentRoundNumber) : 1;
            $scope.totalRounds = (angular.isDefined($scope.totalRounds)) ? parseInt($scope.totalRounds) : 1;
        },
        controller: ["$scope", function ($scope) {
            // Controller - Create a controller which publishes an API for 
            // communicating across directives.
            $scope.paginationChange = function() {
                if(angular.isFunction($scope.buttonViewRound)) {
                    $scope.buttonViewRound($scope.currentRoundNumber);
                }
            };
        }]
    };
});

app.directive('rcTriviaScoreboardReadonly', function(THIS_DIRECTORY) {
    return {
        restrict: 'A',          // Must be a element attribute
        templateUrl: THIS_DIRECTORY + 'views/scoreboard.readonly.html',
        scope: {
            game: '=rcTriviaScoreboardReadonly'
        },
        controller: ['$scope', 'DTOptionsBuilder', '$window', 
            function($scope, DTOptionsBuilder, $window) {
                console.log($scope.game);
            $scope.dtScoreboard = {};
            $scope.dtScoreboard.options = DTOptionsBuilder.newOptions()
                .withDOM('t')
                .withOption('scrollX', '100%')
                .withOption('scrollCollapse', true)
                .withOption('deferRender', true)
                .withOption('paging', false)
                .withFixedColumns({ leftColumns: 1 })
                .withOption('responsive', false)
                .withOption('drawCallback', function() {
                    $scope.setScoreboardHeight();
                });
                
                // Responsive table height
                
                $scope.setScoreboardHeight = function() {
                    // Get the height of everything that is not the table
                    var otherHeight = $('body').height() - $('.dataTables_scroll').height();
                    // Subtract the height of everything but the table from the
                    // height of the window to get whats left for the table
                    var tableHeight = $(window).height() - otherHeight - 1;
                    // Max height on table
                    tableHeight = (tableHeight < $('.dataTables_scroll').height()) ? tableHeight : $('.dataTables_scroll').height();
                    // Min Height on table
                    tableHeight = (tableHeight >= 200) ? tableHeight : 200;
                    // Set the datatables wrapper to that height
                    $('.dataTables_scroll').css('height', tableHeight + 'px');
                };
                angular.element($window).on('resize', function () {
                    $scope.setScoreboardHeight();
                });
            
        }]
    };
});