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
        controller: ['$scope', '$state', '$window', 'TriviaScoreboard', 'AlertConfirmService', 'TriviaModalService', 'DTOptionsBuilder', 'DTColumnDefBuilder',
            function($scope, $state, $window, TriviaScoreboard, AlertConfirmService, TriviaModalService, DTOptionsBuilder, DTColumnDefBuilder) {
            
            /* Used to restrict alert bars */
            $scope.alertProxy = {};
    
            $scope.game = TriviaScoreboard.getGame();
            if(!$scope.game) {
                console.log("Error loading game.");
                die();
            }
            
            
            var values = {name: 'misko', gender: 'male'};
            var log = [];
            angular.forEach(values, function(value, key) {
              this.push(key + ': ' + value);
            }, log);
            expect(log).toEqual(['name: misko', 'gender: male']);
            

            $scope.updateTeamRankings = function(teamId) {
                $scope.unsavedState = true;
                TriviaScoreboard.updateTeamRankings(teamId);
            };
            
            $scope.unsavedState = false;
            $scope.displayQuickScoreButtons = true;
                
            $scope.scoreboardNavHamburger = { isopen: false };
            
            $scope.dtScoreboard = {};
            
            /* Object to hold DataTableInstance */
            //dt.instance = {};
            $scope.dtScoreboard.instance = function (instance) {
                $scope.dtScoreboard.instance = instance;
            };
        
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
                
            $scope.dtScoreboard.columns = [
                DTColumnDefBuilder.newColumnDef(0),
                DTColumnDefBuilder.newColumnDef(1),
                DTColumnDefBuilder.newColumnDef(2),
                DTColumnDefBuilder.newColumnDef(3),
                DTColumnDefBuilder.newColumnDef(4)
            ];
            var colNum = 5;
            for(var i = 0; i < Object.keys($scope.game.rounds[$scope.game.currentRoundNumber].questions).length; i++) {
                $scope.dtScoreboard.columns.push(DTColumnDefBuilder.newColumnDef(colNum).notSortable());
                colNum++;
            }
                
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
            
            $scope.buttonStartGame = function() {
                AlertConfirmService.confirm('Are you sure you want to start this game? It cannot be paused once started.', 'Confirm Start Game.')
                    .result.then(function () {
                        TriviaScoreboard.startGame().then(function (result) {
                        }, function (error) {
                            $scope.alertProxy.error(error);
                        });
                    }, function (declined) {});
            };
            
            $scope.buttonEndGame = function() {
                AlertConfirmService.confirm('Are you sure you want to end this game? It cannot be started again once it has been closed.', 'Confirm End Game.')
                    .result.then(function () {
                        AlertConfirmService.confirm('Are you sure you positive you would like to close this game? It will finalize team scores.', 'Warning! Closing Game.')
                            .result.then(function () {
                                TriviaScoreboard.endGame().then(function (result) {
                                    console.log($scope.game);
                                    $state.go('app.member.game', { 'gameId': $scope.game.id, 'roundNumber' : 1 });
                                }, function (error) {
                                    $scope.alertProxy.error(error);
                                });
                            }, function (declined) {});
                    }, function (declined) {});
            };
            
            $scope.buttonSaveGame = function() {
                TriviaScoreboard.saveScoreboard().then(function (result) {
                        $scope.alertProxy.success("Game saved.");
                        $scope.unsavedState = false;
                    }, function (error) {
                        $scope.alertProxy.error(error);
                    });
            };
            
            // Add Trivia Team Modal
            $scope.buttonAddTeam = function() {
                var modalInstance = TriviaModalService.openAddTeam($scope.game);
                modalInstance.result.then(function (result) {
                    console.log(result);
                }, function () {});
                
            };
            
            // Add Trivia Player Modal
            $scope.buttonAddPlayer = function() {
                var modalInstance = TriviaModalService.openAddPlayer($scope.game.id);
                modalInstance.result.then(function (result) {
                    console.log(result);
                }, function () {});
                
            };
            
            // Add Trivia Round Modal
            $scope.buttonAddRound = function() {
                var modalInstance = TriviaModalService.openEditRound($scope.game.id);
                modalInstance.result.then(function (result) {
                    console.log(result);
                }, function () {});
            };
            
            // Edit Trivia Round Modal
            $scope.buttonEditRound = function(roundNumber) {
                var round = $scope.game.rounds[roundNumber];
                var modalInstance = TriviaModalService.openEditRound(round);
                modalInstance.result.then(function (result) {
                    console.log(result);
                }, function () {});
            };
            
            // Add Trivia Round Question Modal
            $scope.buttonAddQuestion = function() {
                var modalInstance = TriviaModalService.openEditQuestion();
                modalInstance.result.then(function (result) {
                    console.log(result);
                }, function () {});
            };
            
            // Edit Trivia Round Question Modal
            $scope.buttonEditQuestion = function(questionNumber) {
                var question = $scope.game.rounds[$scope.game.currentRoundNumber].questions[questionNumber];
                var modalInstance = TriviaModalService.openEditQuestion(question);
                modalInstance.result.then(function (result) {
                    console.log(result);
                }, function () {});
            };
            
            // Right and Wrong speed buttons
            
            $scope.buttonQuestionWrong = function(teamId, questionNumber) {
                var teamScore = $scope.game.teams[teamId].rounds[$scope.game.currentRoundNumber].questions[questionNumber];
                teamScore.questionScore = parseFloat(teamScore.questionScore) - parseFloat(teamScore.maxPoints);
                $scope.updateTeamRankings(teamId);
            };
        
            $scope.buttonQuestionCorrect = function(teamId, questionNumber) {
                var teamScore = $scope.game.teams[teamId].rounds[$scope.game.currentRoundNumber].questions[questionNumber];
                teamScore.questionScore = parseFloat(teamScore.questionScore) + parseFloat(teamScore.maxPoints);
                $scope.updateTeamRankings(teamId);
            }; 
            
            // Wager Buttons
            
            $scope.buttonIncreaseTeamWager = function(teamId, questionNumber) {
                var teamScore = $scope.game.teams[teamId].rounds[$scope.game.currentRoundNumber].questions[questionNumber];
                teamScore.teamWager = parseFloat(teamScore.teamWager) + 10;
                $scope.checkboxCorrectWagerQuestion(teamId, questionNumber);
            }; 
            
            $scope.buttonDecreaseTeamWager = function(teamId, questionNumber) {
                var teamScore = $scope.game.teams[teamId].rounds[$scope.game.currentRoundNumber].questions[questionNumber];
                teamScore.teamWager = parseFloat(teamScore.teamWager) - 10;
                $scope.checkboxCorrectWagerQuestion(teamId, questionNumber);
            };
            
            $scope.checkboxCorrectWagerQuestion = function(teamId, questionNumber) {
                var teamScore = $scope.game.teams[teamId].rounds[$scope.game.currentRoundNumber].questions[questionNumber];
                var correct = (angular.isDefined(teamScore.wagerChecked) && teamScore.wagerChecked);
                var wager = parseFloat(teamScore.teamWager);
                teamScore.questionScore =  (correct) ? wager : (wager* -1);
                $scope.updateTeamRankings(teamId);
            };  
            
            $scope.getQuestionType = function(questionNumber) {
                var question = $scope.game.rounds[$scope.game.currentRoundNumber].questions[questionNumber];
                if(question.wager === '1') {
                    return 'wager';
                } else {
                    return 'default';
                }
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
        link: function ($scope, element, attributes) {
            // Link - Programmatically modify resulting DOM element instances, 
            // add event listeners, and set up data binding. 
            
            $scope.currentRoundNumber = (angular.isDefined($scope.game.currentRoundNumber)) ? parseInt($scope.game.currentRoundNumber) : 1;
            $scope.totalRounds = (angular.isDefined($scope.game.numberOfRounds)) ? parseInt($scope.game.numberOfRounds) : 1;
        },
        controller: ["$scope", '$state', function ($scope, $state) {
            // Controller - Create a controller which publishes an API for 
            // communicating across directives.
            $scope.paginationChange = function() {
                $state.go($state.$current, {gameId: $scope.game.id, roundNumber: $scope.currentRoundNumber});
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
        controller: ['$scope', 'DTOptionsBuilder', '$window', 'TriviaScoreboard',
            function($scope, DTOptionsBuilder, $window, TriviaScoreboard) {
            /* Used to restrict alert bars */
            $scope.alertProxy = {};
    
            $scope.game = TriviaScoreboard.getGame();
            if(!$scope.game) {
                console.log("Error loading game.");
                die();
            }
            
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
            
            $scope.getQuestionType = function(questionNumber) {
                var question = $scope.game.rounds[$scope.game.currentRoundNumber].questions[questionNumber];
                if(question.wager === '1') {
                    return 'wager';
                } else {
                    return 'default';
                }
            };
            
        }]
    };
});