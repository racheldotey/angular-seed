'use strict';

app = angular.module('rcTrivia.directives', []);

app.constant('THIS_DIRECTORY', (function () {
    var scripts = document.getElementsByTagName("script");
    var scriptPath = scripts[scripts.length - 1].src;
    return scriptPath.substring(0, scriptPath.lastIndexOf('/') + 1);
})());

app.factory('LeaderboardResizing', [function() {
            
        var api = {};
        
        var padding = 10;
        var otherPageElementsHeight = 0;
        
        var initHeightVariables = function() {
            var pageHeaderHeight = ($('nav.navbar').height()) ? $('nav.navbar').height() : 0;
            var leaderboardHeaderHeight = ($('div.page.leaderboard > div.leaderboard-header').height()) ? $('div.page.leaderboard > div.leaderboard-header').height() : 0;
            var leaderboardFooterHeight = ($('div.page.leaderboard > div.leaderboard-footer').height()) ? $('div.page.leaderboard > div.leaderboard-footer').height() : 0;
            var footerContentHeight = ($('footer.footer').height()) ? $('footer.footer').height() : 0;
               
            otherPageElementsHeight = pageHeaderHeight + leaderboardHeaderHeight + leaderboardFooterHeight + footerContentHeight + padding;
            
            console.log(pageHeaderHeight + " + " + leaderboardHeaderHeight +  " + " + leaderboardFooterHeight +  " + " + footerContentHeight +  " + " + padding + " = " + otherPageElementsHeight);
        };
        
        api.getUIGridHeight = function() {
            if(otherPageElementsHeight === 0) {
                initHeightVariables();
            }
            
            var newHeight = $(window).height() - otherPageElementsHeight;
            newHeight = (newHeight > 100) ? parseInt(newHeight) : 100;

            // Change the inner scrollable tables height
            return newHeight;
        };
            
        return api;
    }]);

app.factory('ScoreboardResizing', [function() {
            
        var api = {};  
        
        api.setHeight = function() {
                // Height of the visible window area (screen size)
                var visibleWindowHeight = $(window).height();
                // Get the table header height (because its not part of dataTables_scrollBody)
                var tableHeaderHeight = $('div.dataTables_scrollHead').height();
                // Add a little padding
                var padding = 20;
                // Do the maths
                var newHeight = visibleWindowHeight - tableHeaderHeight - padding;
                // Change the inner scrollable tables height
                $('.dataTables_scrollBody').css('height', newHeight + 'px');
            };
            
        return api;
    }]);

app.directive('rcTriviaScoreboard', function(THIS_DIRECTORY) {
        
    return {
        restrict: 'A',          // Must be a attributeon a html tag
        templateUrl: THIS_DIRECTORY + 'views/scoreboard.html',
        scope: {
            game: '=rcTriviaScoreboard'
        },
        controller: ['$rootScope', '$scope', '$state', '$stateParams', '$window', '$filter', 'TriviaScoreboard', 'ScoreboardResizing', 'AlertConfirmService', 'TriviaModalService', 'DTOptionsBuilder', 'DTColumnDefBuilder',
            function($rootScope, $scope, $state, $stateParams, $window, $filter, TriviaScoreboard, ScoreboardResizing, AlertConfirmService, TriviaModalService, DTOptionsBuilder, DTColumnDefBuilder) {
            
            // Prevent leaving without saving
            $rootScope.$on('$stateChangeStart',
                function (event, toState, toParams, fromState, fromParams) {
                    
                    if(fromState.name === 'app.host.game' && $scope.unsavedState) {
                        event.preventDefault();
                        AlertConfirmService.confirm('Wait! You have unsaved changes. Would you like to save the scoreboard before you leave?', 'Unsaved Changes!')
                            .result.then(function () {

                                TriviaScoreboard.saveScoreboard().then(function (result) {
                                    $scope.alertProxy.success("Game saved.");
                                    $scope.unsavedState = false;
                                    $state.go(toState.name, toParams);
                                }, function (error) {
                                    $scope.alertProxy.error(error);
                                });
                            }, function (declined) {
                                $scope.unsavedState = false;
                                $state.go(toState.name, toParams);
                            });
                    }
                });
            
            /* Used to restrict alert bars */
            $scope.alertProxy = {};
    
            $scope.game = TriviaScoreboard.getGame();
            if(!$scope.game) {
                console.log("Error loading game.");
                die();
            }

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
                .withOption('bSort', false)
                .withOption('ordering', false)
                .withFixedColumns({ leftColumns: 1 })
                .withOption('drawCallback', function() {
                    ScoreboardResizing.setHeight();
                });
                
            if(angular.isDefined($stateParams.sortBy) && angular.isNumber(parseInt($stateParams.sortBy))){
                var direction = (angular.isDefined($stateParams.sortDirection) && 
                    $stateParams.sortDirection.toLowerCase() === 'asc') ? 'asc' : 'desc';
            
                    $scope.sortOnTeamName = '';
                    $scope.sortOnTeamRoundRank = '';
                    $scope.sortOnTeamGameRank = '';
                        
                    switch($stateParams.sortBy) {
                        case '0':
                            $scope.sortOnTeamName = '-' + direction;
                            $scope.orderByThis = { 'attribute' : 'name', 'type' : 'string', 'direction' : direction };
                            break;
                        case '2':
                            $scope.sortOnTeamRoundRank = '-' + direction;
                            $scope.orderByThis = { 'attribute' : 'rounds[' + $scope.game.currentRoundNumber + '].roundRank', 'type' : 'int', 'direction' : direction };
                            break;
                        case '1':
                        default:
                            $scope.sortOnTeamGameRank = '-' + direction;
                            $scope.orderByThis = { 'attribute' : 'gameRank', 'type' : 'int', 'direction' : direction };
                            break;
                    }
                    $scope.game.teams = $filter('orderObjectBy')($scope.game.teams, $scope.orderByThis.attribute, $scope.orderByThis.type, $scope.orderByThis.direction);
            };
            
            $scope.sortScoreboardColumn = function(column) {
                var params = { 'gameId': $scope.game.id, 'roundNumber' : $scope.game.currentRoundNumber };
                switch(column) {
                    case 'name':
                        var direction = (angular.isDefined($stateParams.sortBy) && 
                                $stateParams.sortBy === '0' &&
                                angular.isDefined($stateParams.sortDirection) && 
                            $stateParams.sortDirection.toLowerCase() === 'desc') ? 'asc' : 'desc';
                        params.sortBy = 0;
                        params.sortDirection = direction;
                        break;
                    case 'round':
                        var direction = (angular.isDefined($stateParams.sortBy) && 
                                $stateParams.sortBy === '2' &&
                                angular.isDefined($stateParams.sortDirection) && 
                            $stateParams.sortDirection.toLowerCase() === 'desc') ? 'asc' : 'desc';
                        params.sortBy = 2;
                        params.sortDirection = direction;
                        break;
                    case 'game':
                    default:
                        var direction = (angular.isDefined($stateParams.sortBy) && 
                                $stateParams.sortBy === '1' &&
                                angular.isDefined($stateParams.sortDirection) && 
                            $stateParams.sortDirection.toLowerCase() === 'desc') ? 'asc' : 'desc';
                        params.sortBy = 1;
                        params.sortDirection = direction;
                        break;
                }
                TriviaScoreboard.saveScoreboard().then(function (result) {
                        $scope.alertProxy.success("Game saved.");
                        $state.go('app.host.game', params, { reload: true });
                        $scope.unsavedState = false;
                    }, function (error) {
                        $scope.alertProxy.error(error);
                    });
            };
            
            $scope.dtScoreboard.columns = [
                DTColumnDefBuilder.newColumnDef(0).notSortable(),
                DTColumnDefBuilder.newColumnDef(1).notSortable(),
                DTColumnDefBuilder.newColumnDef(2).notSortable()//,
                //DTColumnDefBuilder.newColumnDef(3).notSortable(),
                //DTColumnDefBuilder.newColumnDef(4).notSortable()
            ];
            var colNum = $scope.dtScoreboard.columns.length;
            for(var i = 0; i < Object.keys($scope.game.rounds[$scope.game.currentRoundNumber].questions).length; i++) {
                $scope.dtScoreboard.columns.push(DTColumnDefBuilder.newColumnDef(colNum).notSortable());
                colNum++;
            }
                
            // Responsive table height
            angular.element($window).on('resize', function () {
                ScoreboardResizing.setHeight();
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
            
            // View Sortable Scoreboard Modal
            $scope.buttonViewScoreboardModal = function() {
                AlertConfirmService.confirm('Unsaved changes need to be saved to show the sortable scoreboard. Would you like to save the scoreboard?', 'Unsaved Changes!')
                    .result.then(function () {
                        TriviaScoreboard.saveScoreboard().then(function (result) {
                            $scope.alertProxy.success("Game saved.");
                            $scope.unsavedState = false;
                            var modalInstance = TriviaModalService.openViewGameScoreboard($scope.game);
                            modalInstance.result.then(function (result) {
                                console.log(result);
                            }, function () {});
                        }, function (error) {
                            $scope.alertProxy.error(error);
                        });
                    }, function (declined) {
                        $scope.alertProxy.error("No changes were saved.");
                    });
                
            };
            
            // Add Trivia Team Modal
            $scope.buttonAddTeam = function() {
                var modalInstance = TriviaModalService.openAddTeam($scope.game);
                modalInstance.result.then(function (result) {
                    console.log(result);
                    $state.go('app.host.game', { 'gameId': $scope.game.id, 'roundNumber' : $scope.game.currentRoundNumber }, { reload: true });
                }, function () {});
                
            };
        
            $scope.buttonCreateTeam = function() {
                var modalInstance = TriviaModalService.openEditTeam(false, false, $scope.game.venueId, $scope.game.id);
                modalInstance.result.then(function (result) {
                    for(var i = 0; i < result.invites.length; i++) {
                        $scope.alertProxy.success(result.invites[i].msg);
                    }
                    $state.go('app.host.game', { 'gameId': $scope.game.id, 'roundNumber' : $scope.game.currentRoundNumber }, { reload: true });
                }, function () {});
            };
            
            // Add Trivia Round Modal
            $scope.buttonAddRound = function() {
                var modalInstance = TriviaModalService.openEditRound($scope.game.id);
                modalInstance.result.then(function (result) {
                    console.log(result);
                    $state.go('app.host.game', { 'gameId': $scope.game.id, 'roundNumber' : result.roundNumber }, { reload: true });
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
                $scope.calculateWagerScore(teamId, questionNumber);
            }; 
            
            $scope.buttonDecreaseTeamWager = function(teamId, questionNumber) {
                var teamScore = $scope.game.teams[teamId].rounds[$scope.game.currentRoundNumber].questions[questionNumber];
                teamScore.teamWager = parseFloat(teamScore.teamWager) - 10;
                $scope.calculateWagerScore(teamId, questionNumber);
            };
            
            $scope.calculateWagerScore = function(teamId, questionNumber) {
                var teamQScore = $scope.game.teams[teamId].rounds[$scope.game.currentRoundNumber].questions[questionNumber];
                
                var newWager = parseFloat(teamQScore.teamWager);
                
                teamQScore.questionScore = 0;
                $scope.updateTeamRankings(teamId);
                
                if (newWager < 0) {
                    $scope.alertProxy.error("Teams cannot wager less than 0 points.");
                    teamQScore.teamWager = 0;
                } else if(newWager > $scope.game.teams[teamId].gameScore) {
                    teamQScore.teamWager = (parseFloat($scope.game.teams[teamId].gameScore) <= 0) ? 0 : $scope.game.teams[teamId].gameScore;
                    $scope.alertProxy.error("Teams cannot wager more points than they have.");
                } else {
                    console.log("Wager accepted. Team Game Score: " + $scope.game.teams[teamId].gameScore + " - Team Wager: " + newWager);
                    teamQScore.teamWager = newWager;
                }
                var correct = (angular.isDefined(teamQScore.wagerChecked) && teamQScore.wagerChecked);
                teamQScore.questionScore =  (correct) ? teamQScore.teamWager : (teamQScore.teamWager * -1);
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
            
        }]
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
        controller: ['$scope', 'DTOptionsBuilder', '$window', 'TriviaScoreboard', 'ScoreboardResizing',
            function($scope, DTOptionsBuilder, $window, TriviaScoreboard, ScoreboardResizing) {
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
                .withOption('bSort', false)
                .withOption('ordering', false)
                .withOption('drawCallback', function() {
                    ScoreboardResizing.setHeight();
                });
                
                // Responsive table height
                angular.element($window).on('resize', function () {
                    ScoreboardResizing.setHeight();
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

app.directive('rcTriviaSelectListTeam', function(THIS_DIRECTORY) {
    return {
        restrict: 'A',          // Must be a element attribute
        templateUrl: THIS_DIRECTORY + 'views/selectList.teams.html',
        scope: {
            selected: '=rcTriviaSelectListTeam',    // $scope object REQUIRED
            dataArray: '=teamsListData',             // Data Array REQUIRED
            placeholderText: '@placeholderText'
        },
        link: function ($scope, element, attributes) {
            // Link - Programmatically modify resulting DOM element instances, 
            // add event listeners, and set up data binding. 
            $scope.selected = (angular.isObject($scope.selected)) ? { value : $scope.selected } : {};
            
            $scope.dataArray = angular.isArray($scope.dataArray) ? $scope.dataArray : new Array();
            
            $scope.placeholderText = angular.isString($scope.placeholderText) ? $scope.placeholderText : "Search for Trivia Team";
        },
        controller: ["$scope", function ($scope) {
            // Controller - Create a controller which publishes an API for 
            // communicating across directives.
        }]
    };
});

app.directive('rcTriviaSelectListVenue', function(THIS_DIRECTORY) {
    return {
        restrict: 'A',          // Must be a element attribute
        templateUrl: THIS_DIRECTORY + 'views/selectList.venues.html',
        scope: {
            selected: '=rcTriviaSelectListVenue',    // $scope object REQUIRED
            dataArray: '=teamsListData',             // Data Array REQUIRED
            placeholderText: '@placeholderText'
        },
        link: function ($scope, element, attributes) {
            // Link - Programmatically modify resulting DOM element instances, 
            // add event listeners, and set up data binding. 
            $scope.selected = (angular.isObject($scope.selected)) ? { value : $scope.selected } : {};
            
            $scope.dataArray = angular.isArray($scope.dataArray) ? $scope.dataArray : new Array();
            
            $scope.placeholderText = angular.isString($scope.placeholderText) ? $scope.placeholderText : "Search for Trivia Joint";
        },
        controller: ["$scope", function ($scope) {
            // Controller - Create a controller which publishes an API for 
            // communicating across directives.
        }]
    };
});

app.directive('rcTriviaSelectListGame', function(THIS_DIRECTORY) {
    return {
        restrict: 'A',          // Must be a element attribute
        templateUrl: THIS_DIRECTORY + 'views/selectList.games.html',
        scope: {
            selected: '=rcTriviaSelectListGame',    // $scope object REQUIRED
            dataArray: '=teamsListData',             // Data Array REQUIRED
            placeholderText: '@placeholderText'
        },
        link: function ($scope, element, attributes) {
            // Link - Programmatically modify resulting DOM element instances, 
            // add event listeners, and set up data binding. 
            $scope.selected = (angular.isObject($scope.selected)) ? { value : $scope.selected } : {};
            
            $scope.dataArray = angular.isArray($scope.dataArray) ? $scope.dataArray : new Array();
            
            $scope.placeholderText = angular.isString($scope.placeholderText) ? $scope.placeholderText : "Search for Trivia Game";
        },
        controller: ["$scope", function ($scope) {
            // Controller - Create a controller which publishes an API for 
            // communicating across directives.
        }]
    };
});