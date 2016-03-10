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
                
                
            $scope.scoreboardNavHamburger = { isopen: false };
            
            $scope.dtScoreboard = {};
            $scope.dtScoreboard.options = DTOptionsBuilder.newOptions()
                .withDOM('t')
                .withOption('scrollY', '1000')
                .withOption('scrollX', '100%')
                .withOption('scrollCollapse', true)
                .withOption('deferRender', true)
                .withOption('paging', false)
                .withFixedColumns({ leftColumns: 1 })
                .withOption('responsive', false);
                
                // Responsive table height
                angular.element($window).on('resize', function () {
                    $scope.setScoreboardHeight($window.innerHeight);
                });
                
                $scope.setScoreboardHeight = function(height) {
                    var newHeight = height - 200;
                    console.log(newHeight);
                    console.log($scope.dtScoreboard);
                    //$('.dataTables_scrollBody').css('height', newHeight);
                    //$('div.dataTables_scrollBody').height(newHeight);
                    
                    var otherHeight = $('body').height() - $('.dataTables_scrollBody').height();
                    var tableHeight = $(window).height() - otherHeight - 1;
                    $('.dataTables_scrollBody').css('height', tableHeight + 'px');
                };
                
            $scope.buttonViewRound = function(roundNumber) {
                TriviaGame.loadRound(roundNumber).then(function (result) {
                        // Change the State (URL) parameters without reloading the page
                        // Used for deep linking
                        $state.go($state.$current, {gameId: $scope.game.id, roundNumber: roundNumber}, {notify: false});
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
        controller: ['$scope', 'DTOptionsBuilder', 'DTColumnDefBuilder', 
            function($scope, DTOptionsBuilder, DTColumnDefBuilder) {
                console.log($scope.game);
            $scope.dtScoreboard = {};
            $scope.dtScoreboard.options = DTOptionsBuilder.newOptions()
                .withDOM('t')
                .withOption('scrollY', '100%')
                .withOption('scrollX', '100%')
                .withOption('scrollCollapse', true)
                .withOption('paging', false)
                .withFixedColumns({ leftColumns: 1 })
                .withOption('responsive', false);
            
        }]
    };
});