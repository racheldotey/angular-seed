'use strict';

app = angular.module('rcTrivia.directives', []);

app.constant('THIS_DIRECTORY', (function () {
    var scripts = document.getElementsByTagName("script");
    var scriptPath = scripts[scripts.length - 1].src;
    return scriptPath.substring(0, scriptPath.lastIndexOf('/') + 1);
})());

app.factory('LeaderboardResizing', [function() {
            
        var api = {};
        
        var padding = 20;
        var otherPageElementsHeight = 0;
        
        var initHeightVariables = function() {
            var pageHeaderHeight = ($('nav.navbar').outerHeight(true)) ? Math.ceil($('nav.navbar').outerHeight(true)) : 0;
            var leaderboardHeaderHeight = ($('div.page.leaderboard > div.leaderboard-header').outerHeight(true)) ? Math.ceil($('div.page.leaderboard > div.leaderboard-header').outerHeight(true)) : 0;
            var leaderboardFooterHeight = ($('div.page.leaderboard > div.leaderboard-footer').outerHeight(true)) ? Math.ceil($('div.page.leaderboard > div.leaderboard-footer').outerHeight(true)) : 0;
            var footerContentHeight = ($('footer.footer').outerHeight(true)) ? Math.ceil($('footer.footer').outerHeight(true)) : 0;
               
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

app.directive('rcTriviaScoreboard', function (THIS_DIRECTORY) {
    return {
        restrict: 'A',          // Must be a attributeon a html tag
        templateUrl: THIS_DIRECTORY + 'views/scoreboard.html',
        scope: {
            game: '=rcTriviaScoreboard'
        },
        controller: ['$rootScope', '$scope', '$state', '$stateParams', '$window', '$filter', 'TriviaScoreboard', 'ScoreboardResizing', 'AlertConfirmService', 'TriviaModalService', 'DTOptionsBuilder', 'DTColumnDefBuilder', 'uiGridConstants',
            function ($rootScope, $scope, $state, $stateParams, $window, $filter, TriviaScoreboard, ScoreboardResizing, AlertConfirmService, TriviaModalService, DTOptionsBuilder, DTColumnDefBuilder, uiGridConstants) {

                // Prevent leaving without saving
                $rootScope.$on('$stateChangeStart',
                    function (event, toState, toParams, fromState, fromParams) {
                        if (fromState.name === 'app.host.game' && $scope.unsavedState) {
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
                if (!$scope.game) {
                    console.log("Error loading game.");
                    die();
                }


                $scope.updateTeamRankings = function (teamId) {
                    $scope.unsavedState = true;
                    TriviaScoreboard.updateTeamRankings(teamId);
                    //for precoessing new data and creating grid from that
                    createAndDisplayUiGridOptions(false);
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
                    .withOption('drawCallback', function () {
                        ScoreboardResizing.setHeight();
                    });

                if (angular.isDefined($stateParams.sortBy) && angular.isNumber(parseInt($stateParams.sortBy))) {
                    var direction = (angular.isDefined($stateParams.sortDirection) &&
                        $stateParams.sortDirection.toLowerCase() === 'asc') ? 'asc' : 'desc';

                    $scope.sortOnTeamName = '';
                    $scope.sortOnTeamRoundRank = '';
                    $scope.sortOnTeamGameRank = '';

                    switch ($stateParams.sortBy) {
                        case '0':
                            $scope.sortOnTeamName = '-' + direction;
                            $scope.orderByThis = { 'attribute': 'name', 'type': 'string', 'direction': direction };
                            break;
                        case '2':
                            $scope.sortOnTeamRoundRank = '-' + direction;
                            $scope.orderByThis = { 'attribute': 'rounds[' + $scope.game.currentRoundNumber + '].roundRank', 'type': 'int', 'direction': direction };
                            break;
                        case '1':
                        default:
                            $scope.sortOnTeamGameRank = '-' + direction;
                            $scope.orderByThis = { 'attribute': 'gameRank', 'type': 'int', 'direction': direction };
                            break;
                    }
                    $scope.game.teams = $filter('orderObjectBy')($scope.game.teams, $scope.orderByThis.attribute, $scope.orderByThis.type, $scope.orderByThis.direction);
                };

                $scope.sortScoreboardColumn = function (column) {
                    var params = { 'gameId': $scope.game.id, 'roundNumber': $scope.game.currentRoundNumber };
                    switch (column) {
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
                for (var i = 0; i < Object.keys($scope.game.rounds[$scope.game.currentRoundNumber].questions).length; i++) {
                    $scope.dtScoreboard.columns.push(DTColumnDefBuilder.newColumnDef(colNum).notSortable());
                    colNum++;
                }

                // Responsive table height
                angular.element($window).on('resize', function () {
                    ScoreboardResizing.setHeight();
                });

                $scope.buttonStartGame = function () {
                    AlertConfirmService.confirm('Are you sure you want to start this game? It cannot be paused once started.', 'Confirm Start Game.')
                        .result.then(function () {
                            TriviaScoreboard.startGame().then(function (result) {
                            }, function (error) {
                                $scope.alertProxy.error(error);
                            });
                        }, function (declined) { });
                };

                $scope.buttonEndGame = function () {
                    AlertConfirmService.confirm('Are you sure you want to end this game? It cannot be started again once it has been closed.', 'Confirm End Game.')
                        .result.then(function () {
                            AlertConfirmService.confirm('Are you sure you positive you would like to close this game? It will finalize team scores.', 'Warning! Closing Game.')
                                .result.then(function () {
                                    TriviaScoreboard.endGame().then(function (result) {
                                        console.log($scope.game);
                                        $state.go('app.member.game', { 'gameId': $scope.game.id, 'roundNumber': 1 });
                                    }, function (error) {
                                        $scope.alertProxy.error(error);
                                    });
                                }, function (declined) { });
                        }, function (declined) { });
                };

                $scope.buttonSaveGame = function () {
                    TriviaScoreboard.saveScoreboard().then(function (result) {
                        $scope.alertProxy.success("Game saved.");
                        $scope.unsavedState = false;
                    }, function (error) {
                        $scope.alertProxy.error(error);
                    });
                };

                // View Sortable Scoreboard Modal
                $scope.buttonViewScoreboardModal = function () {
                    AlertConfirmService.confirm('Unsaved changes need to be saved to show the sortable scoreboard. Would you like to save the scoreboard?', 'Unsaved Changes!')
                        .result.then(function () {
                            TriviaScoreboard.saveScoreboard().then(function (result) {
                                $scope.alertProxy.success("Game saved.");
                                $scope.unsavedState = false;
                                var modalInstance = TriviaModalService.openViewGameScoreboard($scope.game);
                                modalInstance.result.then(function (result) {
                                    console.log(result);
                                }, function () { });
                            }, function (error) {
                                $scope.alertProxy.error(error);
                            });
                        }, function (declined) {
                            $scope.alertProxy.error("No changes were saved.");
                        });

                };

                // Add Trivia Team Modal
                $scope.buttonAddTeam = function () {
                    var modalInstance = TriviaModalService.openAddTeam($scope.game);
                    modalInstance.result.then(function (result) {
                        console.log(result);
                        $state.go('app.host.game', { 'gameId': $scope.game.id, 'roundNumber': $scope.game.currentRoundNumber }, { reload: true });
                    }, function () { });

                };

                $scope.buttonCreateTeam = function () {
                    var modalInstance = TriviaModalService.openEditTeam(false, false, $scope.game.venueId, $scope.game.id);
                    modalInstance.result.then(function (result) {
                        for (var i = 0; i < result.invites.length; i++) {
                            $scope.alertProxy.success(result.invites[i].msg);
                        }
                        $state.go('app.host.game', { 'gameId': $scope.game.id, 'roundNumber': $scope.game.currentRoundNumber }, { reload: true });
                    }, function () { });
                };

                // Add Trivia Round Modal
                $scope.buttonAddRound = function () {
                    var modalInstance = TriviaModalService.openEditRound($scope.game.id);
                    modalInstance.result.then(function (result) {
                        console.log(result);
                        $state.go('app.host.game', { 'gameId': $scope.game.id, 'roundNumber': result.roundNumber }, { reload: true });
                    }, function () { });
                };

                // Edit Trivia Round Modal
                $scope.buttonEditRound = function (roundNumber) {
                    var round = $scope.game.rounds[roundNumber];
                    var modalInstance = TriviaModalService.openEditRound(round);
                    modalInstance.result.then(function (result) {
                        console.log(result);
                    }, function () { });
                };

                // Add Trivia Round Question Modal
                $scope.buttonAddQuestion = function () {
                    var modalInstance = TriviaModalService.openEditQuestion();
                    modalInstance.result.then(function (result) {
                        console.log(result);
                        //for precoessing new data and creating grid from that
                        createAndDisplayUiGridOptions(false);
                    }, function () { });
                };

                // Edit Trivia Round Question Modal
                $scope.buttonEditQuestion = function (questionNumber) {
                    var question = $scope.game.rounds[$scope.game.currentRoundNumber].questions[questionNumber];
                    var modalInstance = TriviaModalService.openEditQuestion(question);
                    modalInstance.result.then(function (result) {
                        console.log(result);
                        //for precoessing new data and creating grid from that
                        createAndDisplayUiGridOptions(true);
                    }, function () {  });
                    
                };

                // Right and Wrong speed buttons

                $scope.buttonQuestionWrong = function (teamId, questionNumber) {
                    var teamScore = $scope.game.teams[teamId].rounds[$scope.game.currentRoundNumber].questions[questionNumber];
                    teamScore.questionScore = parseFloat(teamScore.questionScore) - parseFloat(teamScore.maxPoints);
                    $scope.updateTeamRankings(teamId);
                };

                $scope.buttonQuestionCorrect = function (teamId, questionNumber) {
                    var teamScore = $scope.game.teams[teamId].rounds[$scope.game.currentRoundNumber].questions[questionNumber];
                    teamScore.questionScore = parseFloat(teamScore.questionScore) + parseFloat(teamScore.maxPoints);
                    $scope.updateTeamRankings(teamId);
                };

                // Wager Buttons

                $scope.buttonIncreaseTeamWager = function (teamId, questionNumber) {
                    var teamScore = $scope.game.teams[teamId].rounds[$scope.game.currentRoundNumber].questions[questionNumber];
                    teamScore.teamWager = parseFloat(teamScore.teamWager) + 10;
                    $scope.calculateWagerScore(teamId, questionNumber);
                };

                $scope.buttonDecreaseTeamWager = function (teamId, questionNumber) {
                    var teamScore = $scope.game.teams[teamId].rounds[$scope.game.currentRoundNumber].questions[questionNumber];
                    teamScore.teamWager = parseFloat(teamScore.teamWager) - 10;
                    $scope.calculateWagerScore(teamId, questionNumber);
                };

                $scope.calculateWagerScore = function (teamId, questionNumber) {
                    var teamQScore = $scope.game.teams[teamId].rounds[$scope.game.currentRoundNumber].questions[questionNumber];
                    var newWager = parseFloat(teamQScore.teamWager);
                    teamQScore.questionScore = 0;
                    $scope.updateTeamRankings(teamId);

                    if (newWager < 0) {
                        $scope.alertProxy.error("Teams cannot wager less than 0 points.");
                        teamQScore.teamWager = 0;
                    } else if (newWager > $scope.game.teams[teamId].gameScore) {
                        teamQScore.teamWager = (parseFloat($scope.game.teams[teamId].gameScore) <= 0) ? 0 : $scope.game.teams[teamId].gameScore;
                        $scope.alertProxy.error("Teams cannot wager more points than they have.");
                    } else {
                        console.log("Wager accepted. Team Game Score: " + $scope.game.teams[teamId].gameScore + " - Team Wager: " + newWager);
                        teamQScore.teamWager = newWager;
                    }
                    var correct = (angular.isDefined(teamQScore.wagerChecked) && teamQScore.wagerChecked);
                    teamQScore.questionScore = (correct) ? teamQScore.teamWager : (teamQScore.teamWager * -1);
                    $scope.updateTeamRankings(teamId);
                };

                $scope.getQuestionType = function (questionNumber) {
                    var question = $scope.game.rounds[$scope.game.currentRoundNumber].questions[questionNumber];
                    if (question.wager === '1') {
                        return 'wager';
                    } else {
                        return 'default';
                    }
                };

                //Start region
                $scope.uiGames = {};
                $scope.uiGames.gridColumn = [];
                $scope.uiGames.options = {
                    paginationPageSizes: [10, 15, 25, 50, 100],
                    paginationPageSize: 15,
                    enablePaginationControls: true,
                    enableHorizontalScrollbar: true,
                    enableVerticalScrollbar: true,
                    rowHeight: 100,
                    headerHeight:110,
                    columnDefs: $scope.uiGames.gridColumn,
                    data:[]
                }

                createAndDisplayUiGridOptions(false);

                function createAndDisplayUiGridOptions(isColumnDefChanged) {
                    var currentGame = {};
                    $scope.uiGames.options.columnDefs = [];
                    $scope.uiGames.options.data = [];
                    $scope.uiGames.options.data.length = 0;
                    $scope.uiGames.gridColumn = [];
                    var newGridDatasource = [];
                    currentGame = $scope.game;
                    var myHeaderCellTemplate = '<div role="columnheader"  ng-class="{ \'sortable\': sortable }" ui-grid-one-bind-aria-labelledby-grid="col.uid + \'-header-text \' + col.uid + \'-sortdir-text\'"' +
                                          'aria-sort="{{col.sort.direction == asc ? \'ascending\' : ( col.sort.direction == desc ? \'descending\' : (!col.sort.direction ? \'none\' : \'other\'))}}">' +
                                          '<div role="button" tabindex="0" class="ui-grid-cell-contents ui-grid-header-cell-primary-focus" col-index="renderIndex" title="TOOLTIP"><span class="ui-grid-header-cell-label" ui-grid-one-bind-id-grid="col.uid + \'-header-text\'" ng-bind-html="col.displayName"></span>' +
                                          '<span ui-grid-one-bind-id-grid="col.uid + \'-sortdir-text\'" ui-grid-visible="col.sort.direction"  aria-label="{{getSortDirectionAriaLabel()}}">' +
                                          '<i ng-class="{ \'ui-grid-icon-up-dir\': col.sort.direction == asc, \'ui-grid-icon-down-dir\': col.sort.direction == desc, \'ui-grid-icon-blank\': !col.sort.direction }" title="{{isSortPriorityVisible() ? i18n.headerCell.priority + \' \' + ( col.sort.priority + 1 )  : null}}" aria-hidden="true"> </i>' +
                                          '<sub ui-grid-visible="isSortPriorityVisible()" class="ui-grid-sort-priority-number"> {{col.sort.priority + 1}} </sub> </span> </div>' +
                                          '<div role="button"  tabindex="0"  ui-grid-one-bind-id-grid="col.uid + \'-menu-button\'" class="ui-grid-column-menu-button" ng-if="grid.options.enableColumnMenus && !col.isRowHeader  && col.colDef.enableColumnMenu !== false" ng-click="toggleMenu($event)" ng-class="{\'ui-grid-column-menu-button-last-col\': isLastCol}" ui-grid-one-bind-aria-label="i18n.headerCell.aria.columnMenuButtonLabel" aria-haspopup="true"> ' +
                                          '<i class="ui-grid-icon-angle-down" aria-hidden="true">  &nbsp; </i> </div>  <div ui-grid-filter></div></div>';

                    var myHeaderCellQuestionTemplate = '<div role="columnheader"  ng-class="{ \'sortable\': sortable }" ui-grid-one-bind-aria-labelledby-grid="col.uid + \'-header-text \' + col.uid + \'-sortdir-text\'"' +
                                               'aria-sort="{{col.sort.direction == asc ? \'ascending\' : ( col.sort.direction == desc ? \'descending\' : (!col.sort.direction ? \'none\' : \'other\'))}}">' +
                                               '<div role="button" tabindex="0" ng-click="grid.appScope.buttonEditQuestion(col.name)" class="ui-grid-cell-contents ui-grid-header-cell-primary-focus" col-index="renderIndex" title="TOOLTIP"><span class="ui-grid-header-cell-label" ui-grid-one-bind-id-grid="col.uid + \'-header-text\'" ng-bind-html="col.displayName"></span>' +
                                               '<span ui-grid-one-bind-id-grid="col.uid + \'-sortdir-text\'" ui-grid-visible="col.sort.direction"  aria-label="{{getSortDirectionAriaLabel()}}">' +
                                               '<i ng-class="{ \'ui-grid-icon-up-dir\': col.sort.direction == asc, \'ui-grid-icon-down-dir\': col.sort.direction == desc, \'ui-grid-icon-blank\': !col.sort.direction }" title="{{isSortPriorityVisible() ? i18n.headerCell.priority + \' \' + ( col.sort.priority + 1 )  : null}}" aria-hidden="true"> </i>' +
                                               '<sub ui-grid-visible="isSortPriorityVisible()" class="ui-grid-sort-priority-number"> {{col.sort.priority + 1}} </sub> </span> </div>' +
                                               '<div role="button"  tabindex="0"  ui-grid-one-bind-id-grid="col.uid + \'-menu-button\'" class="ui-grid-column-menu-button" ng-if="grid.options.enableColumnMenus && !col.isRowHeader  && col.colDef.enableColumnMenu !== false" ng-click="toggleMenu($event)" ng-class="{\'ui-grid-column-menu-button-last-col\': isLastCol}" ui-grid-one-bind-aria-label="i18n.headerCell.aria.columnMenuButtonLabel" aria-haspopup="true"> ' +
                                               '<i class="ui-grid-icon-angle-down" aria-hidden="true">  &nbsp; </i> </div>  <div ui-grid-filter></div></div>';
                                              // '<div role="button" ng-click="grid.appScope.buttonEditQuestion(col.name)">&nbsp;&nbsp;<i class="fa fa-pencil-square-o" aria-hidden="true"></i>&nbsp;Q{{col.name}}</div>';

                    var displayRoundName = "Round #" + $scope.game.currentRoundNumber;
                    if ($scope.game.rounds[$scope.game.currentRoundNumber] != undefined && $scope.game.rounds[$scope.game.currentRoundNumber].name != undefined) {
                        displayRoundName += " - " + $scope.game.rounds[$scope.game.currentRoundNumber].name + "<br/>Team Name"
                    }
                    $scope.uiGames.gridColumn = [
                        { name: "teamSequence", width: 50, displayName: '', field: 'teamSequence',pinnedLeft: true,  enableSorting: false, enableColumnMenu: false, cellTemplate: '<div class="ui-grid-cell-contents ui-grid-cell-contents-middlealign"><div ng-bind-html="row.entity[col.field]"></div></div>' },
                        { name: "TeamId", field: 'teamId', visible: false },
                        { name: "Name", field: 'teamName', displayName: displayRoundName, pinnedLeft: true, enableCellEdit: false, headerCellTemplate: myHeaderCellTemplate, cellTemplate: '<div class="ui-grid-cell-contents ui-grid-cell-contents-middlealign"><div ng-bind-html="row.entity[col.field]"></div></div>' },
                        { name: 'Game Score Rank', displayName: "Game<br>Score / Rank", enableCellEdit: false, field: "gameScore", type: 'number', sort: { direction: uiGridConstants.DESC }, headerCellTemplate: myHeaderCellTemplate, cellTemplate: '<div class="ui-grid-cell-contents ui-grid-cell-contents-middlealign">{{row.entity.gameScore}}/{{row.entity.gameRank}}</div>' },
                    ]
                    for (var x in currentGame.teams) {
                        var Item = {};
                        Item.teamSequence = currentGame.teams[x].teamSequence;
                        Item.teamId = currentGame.teams[x].teamId;
                        Item.teamName = currentGame.teams[x].name;
                        Item.gameScore = $filter("numberEx")(currentGame.teams[x].gameScore);
                        Item.gameRank = currentGame.teams[x].gameRank;
                        var rounds = currentGame.rounds[$scope.game.currentRoundNumber];
                        if (rounds != undefined) {
                            Item.roundScore = currentGame.teams[x].rounds[$scope.game.currentRoundNumber].roundScore;
                            Item.roundRank = currentGame.teams[x].rounds[$scope.game.currentRoundNumber].roundRank;
                            if (rounds != undefined && rounds != null) {
                                var scoreRankRoundColName = "Round #" + $scope.game.round.number + "<br/> Score / Rank";
                                var matchedResult = $scope.uiGames.gridColumn.filter(function (obj) {
                                    return obj.displayName == scoreRankRoundColName;
                                })[0];
                                if (matchedResult == undefined) {
                                    $scope.uiGames.gridColumn.push({ field: "roundScore", enableCellEdit: false, displayName: scoreRankRoundColName, name: "roundScore", headerCellTemplate: myHeaderCellTemplate, cellTemplate: '<div class="ui-grid-cell-contents ui-grid-cell-contents-middlealign">{{row.entity.roundScore | numberEx}}/{{row.entity.roundRank}}</div>' });
                                }
                            }
                            for (var k in rounds.questions) {
                                var fieldName = "#" + rounds.questions[k].number;
                                var fieldNameWithNumberOnly = rounds.questions[k].number;
                                var matchedResult = $scope.uiGames.gridColumn.filter(function (obj) {
                                    return obj.field == fieldName;
                                })[0];
                                var displayName = "#" + rounds.questions[k].number + " - " + $filter("numberEx")(rounds.questions[k].maxPoints) + " pts";
                                if (currentGame.rounds[$scope.game.currentRoundNumber].questions[k].question) {
                                    displayName += "<br/>" + currentGame.rounds[$scope.game.currentRoundNumber].questions[k].question;
                                }
                                var questionType = getQuestionType(rounds.questions[k].number);
                                if (matchedResult === undefined && matchedResult == null)
                                {
                                    if (questionType == 'wager') {
                                        $scope.uiGames.gridColumn.push({ field: fieldName, enableSorting: false, enableRowHashing:false,enableCellEdit: false, displayName: displayName, name: fieldNameWithNumberOnly, headerCellTemplate: myHeaderCellQuestionTemplate, cellTemplate: '<div class="ui-grid-cell-contents questionCol"><div class="input-group"><span class="input-group-btn" ng-show="grid.appScope.displayQuickScoreButtons"><button ng-click="grid.appScope.buttonDecreaseTeamWager(row.entity.teamId,row.entity[col.field].number)" class="btn btn-sm btn-warning" type="button"><i class="fa fa-minus"></i></button></span> <input type="text" data-string-to-number min="0"  type="number" ng-model="row.entity[col.field].teamWager" ng-change="grid.appScope.calculateWagerScore(row.entity.teamId,row.entity[col.field].number)" > <span class="input-group-btn" ng-show="grid.appScope.displayQuickScoreButtons"><button ng-click="grid.appScope.buttonIncreaseTeamWager(row.entity.teamId,row.entity[col.field].number)" class="btn btn-sm btn-success" type="button"><i class="fa fa-plus"></i></button></span></div><br/><input ng-model="row.entity[col.field].answer" type="text" maxlength="255" placeholder="Answer" class="form-control input-sm"><br/><button type="button" class="btn btn-primary btn-sm btn-block" ng-model="row.entity[col.field].wagerChecked" ng-init="row.entity[col.field].wagerChecked = (row.entity[col.field].teamWager && row.entity[col.field].teamWager > 0 && row.entity[col.field].questionScore === row.entity[col.field].teamWager)" ng-click="grid.appScope.calculateWagerScore(row.entity.teamId, row.entity[col.field].number)" uib-btn-checkbox>{{row.entity[col.field].wagerChecked ? \'Correct Answer\' : \'Incorrect Answer\'}}</button></div>' });
                                    }
                                    else if (questionType == 'default') {
                                        $scope.uiGames.gridColumn.push({ field: fieldName, enableSorting: false, enableRowHashing: false, enableCellEdit: false, displayName: displayName, name: fieldNameWithNumberOnly, headerCellTemplate: myHeaderCellQuestionTemplate, cellTemplate: '<div class="ui-grid-cell-contents questionCol ui-grid-cell-contents-middlealign"><div class="input-group"><span class="input-group-btn" ng-show="grid.appScope.displayQuickScoreButtons"><button ng-click="grid.appScope.buttonQuestionWrong(row.entity.teamId,row.entity[col.field].number)" class="btn btn-sm btn-warning" type="button"><i class="fa fa-minus"></i></button></span><input type="text" data-string-to-number  type="number" ng-model="row.entity[col.field].questionScore" ng-change="grid.appScope.updateTeamRankings(row.entity.teamId)" ><span class="input-group-btn" ng-show="grid.appScope.displayQuickScoreButtons"><button ng-click="grid.appScope.buttonQuestionCorrect(row.entity.teamId,row.entity[col.field].number)" class="btn btn-sm btn-success" type="button"><i class="fa fa-plus"></i></button></span></div></div>' });
                                    }
                                 
                                }
                                if (questionType === 'wager') {
                                    Item[fieldName] = currentGame.teams[x].rounds[$scope.game.currentRoundNumber].questions[k];
                                }
                               else {
                                   Item[fieldName] = currentGame.teams[x].rounds[$scope.game.currentRoundNumber].questions[k];
                               }
                            }
                        }
                        newGridDatasource.push(Item);
                    }
                    if (isColumnDefChanged)
                    {
                        //as ui-gird column is not updated even the data is updated ,so need to cause some delay
                        setTimeout(function () {
                            $scope.uiGames.options.columnDefs = $scope.uiGames.gridColumn;
                            $scope.uiGames.options.data = newGridDatasource;
                            //setUIGamesboardHeight();
                        }, 0);
                    }
                    else
                    {
                        $scope.uiGames.options.columnDefs = $scope.uiGames.gridColumn;
                        $scope.uiGames.options.data = newGridDatasource;
                        //setUIGamesboardHeight();
                    }
                    
                }
                $scope.getTableHeight = function () {
                    var padding = 10;
                    var rowHeight = $scope.uiGames.options.rowHeight; // your row height
                    var headerHeight = ($scope.uiGames.options.headerHeight) ? $scope.uiGames.options.headerHeight : 110; // your header height
                    return {
                        height: ($scope.uiGames.options.data.length * rowHeight + headerHeight + padding)
                    };
                };
                
                function getQuestionType(questionNumber) {
                    var question = $scope.game.rounds[$scope.game.currentRoundNumber].questions[questionNumber];
                    if (question.wager === '1') {
                        return 'wager';
                    } else {
                        return 'default';
                    }
                };
               //End region

            }]
    };

});

app.directive('rcTriviaScoreboardRoundNavigation', function (THIS_DIRECTORY) {
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
            $scope.paginationChange = function () {
                $state.go($state.$current, { gameId: $scope.game.id, roundNumber: $scope.currentRoundNumber });
            };
        }]
    };
});

app.directive('rcTriviaScoreboardReadonly', function (THIS_DIRECTORY) {
    return {
        restrict: 'A',          // Must be a element attribute
        templateUrl: THIS_DIRECTORY + 'views/scoreboard.readonly.html',
        scope: {
            game: '=rcTriviaScoreboardReadonly'
        },
        controller: ['$scope', 'DTOptionsBuilder', '$window', 'TriviaScoreboard', 'ScoreboardResizing', '$filter', 'uiGridConstants',
            function ($scope, DTOptionsBuilder, $window, TriviaScoreboard, ScoreboardResizing, $filter, uiGridConstants) {
                /* Used to restrict alert bars */
                $scope.alertProxy = {};

                $scope.game = TriviaScoreboard.getGame();
                if (!$scope.game) {
                    console.log("Error loading game.");
                    die();
                }
                //$scope.dtScoreboard = {};
                //$scope.dtScoreboard.options = DTOptionsBuilder.newOptions()
                //    .withDOM('t')
                //    .withOption('scrollX', '100%')
                //    .withOption('scrollCollapse', true)
                //    .withOption('deferRender', true)
                //    .withOption('paging', false)
                //    .withFixedColumns({ leftColumns: 1 })
                //    .withOption('responsive', false)
                //    .withOption('bSort', false)
                //    .withOption('ordering', false)
                //    .withOption('drawCallback', function() {
                //        ScoreboardResizing.setHeight();
                //    });

                //// Responsive table height
                //angular.element($window).on('resize', function () {
                //    ScoreboardResizing.setHeight();
                //});

                $scope.uiGames = {};
                $scope.uiGames.gridColumn = [];
                $scope.uiGames.options = {
                    paginationPageSizes: [10, 15, 25, 50, 100],
                    paginationPageSize: 15,
                    rowHeight: 80,
                    headerHeight:90,
                    enablePaginationControls: true,
                    enableHorizontalScrollbar: true,
                    enableVerticalScrollbar: false,
                }
                createAndDisplayUiGridOptions();
                function createAndDisplayUiGridOptions() {
                    var currentGame = {};
                    currentGame = $scope.game;
                    var newGridDatasource = [];
                    var myHeaderCellTemplate = '<div role="columnheader"  ng-class="{ \'sortable\': sortable }" ui-grid-one-bind-aria-labelledby-grid="col.uid + \'-header-text \' + col.uid + \'-sortdir-text\'"' +
                                           'aria-sort="{{col.sort.direction == asc ? \'ascending\' : ( col.sort.direction == desc ? \'descending\' : (!col.sort.direction ? \'none\' : \'other\'))}}">' +
                                           '<div role="button" tabindex="0" class="ui-grid-cell-contents ui-grid-header-cell-primary-focus" col-index="renderIndex" title="TOOLTIP"><span class="ui-grid-header-cell-label" ui-grid-one-bind-id-grid="col.uid + \'-header-text\'" ng-bind-html="col.displayName"></span>' +
                                           '<span ui-grid-one-bind-id-grid="col.uid + \'-sortdir-text\'" ui-grid-visible="col.sort.direction"  aria-label="{{getSortDirectionAriaLabel()}}">' +
                                           '<i ng-class="{ \'ui-grid-icon-up-dir\': col.sort.direction == asc, \'ui-grid-icon-down-dir\': col.sort.direction == desc, \'ui-grid-icon-blank\': !col.sort.direction }" title="{{isSortPriorityVisible() ? i18n.headerCell.priority + \' \' + ( col.sort.priority + 1 )  : null}}" aria-hidden="true"> </i>' +
                                           '<sub ui-grid-visible="isSortPriorityVisible()" class="ui-grid-sort-priority-number"> {{col.sort.priority + 1}} </sub> </span> </div>' +
                                           '<div role="button"  tabindex="0"  ui-grid-one-bind-id-grid="col.uid + \'-menu-button\'" class="ui-grid-column-menu-button" ng-if="grid.options.enableColumnMenus && !col.isRowHeader  && col.colDef.enableColumnMenu !== false" ng-click="toggleMenu($event)" ng-class="{\'ui-grid-column-menu-button-last-col\': isLastCol}" ui-grid-one-bind-aria-label="i18n.headerCell.aria.columnMenuButtonLabel" aria-haspopup="true"> ' +
                                           '<i class="ui-grid-icon-angle-down" aria-hidden="true">  &nbsp; </i> </div>  <div ui-grid-filter></div></div>';

                    var displayRoundName = "Round #" + $scope.game.currentRoundNumber;
                    if ($scope.game.rounds[$scope.game.currentRoundNumber] != undefined && $scope.game.rounds[$scope.game.currentRoundNumber].name != undefined) {
                        displayRoundName += " - " + $scope.game.rounds[$scope.game.currentRoundNumber].name + "<br/>Team Name"
                    }
                    $scope.uiGames.gridColumn = [
                        { name: "teamSequence", width: 50, displayName: '', field: 'teamSequence', pinnedLeft: true, enableSorting: false, enableColumnMenu: false, cellTemplate: '<div class="ui-grid-cell-contents ui-grid-cell-contents-middlealign"><div ng-bind-html="row.entity[col.field]"></div></div>' },
                        { name: "Name", field: 'teamName', displayName: displayRoundName, pinnedLeft: true, headerCellTemplate: myHeaderCellTemplate, cellTemplate: '<div class="ui-grid-cell-contents ui-grid-cell-contents-middlealign"><div ng-bind-html="row.entity[col.field]"></div></div>' },
                        { name: 'Game Score Rank', displayName: "Game<br>Score / Rank", field: "gameScore", type: 'number', sort: { direction: uiGridConstants.DESC }, headerCellTemplate: myHeaderCellTemplate, cellTemplate: '<div class="ui-grid-cell-contents ui-grid-cell-contents-middlealign">{{row.entity.gameScore}}/{{row.entity.gameRank}}</div>' },
                    ]
                    for (var x in currentGame.teams) {
                        var Item = {};
                        Item.teamSequence = currentGame.teams[x].teamSequence;
                        Item.teamId = currentGame.teams[x].teamId;
                        Item.teamName = currentGame.teams[x].name;
                        Item.gameScore = $filter("numberEx")(currentGame.teams[x].gameScore);
                        Item.gameRank = currentGame.teams[x].gameRank;
                        var rounds = currentGame.rounds[$scope.game.currentRoundNumber];
                        if (rounds != undefined) {
                            Item.roundScore = currentGame.teams[x].rounds[$scope.game.currentRoundNumber].roundScore;
                            Item.roundRank = currentGame.teams[x].rounds[$scope.game.currentRoundNumber].roundRank;
                            if (rounds != undefined && rounds != null) {
                                var scoreRankRoundColName = "Round #" + $scope.game.round.number + "<br/> Score / Rank";
                                var matchedResult = $scope.uiGames.gridColumn.filter(function (obj) {
                                    return obj.displayName == scoreRankRoundColName;
                                })[0];
                                if (matchedResult == undefined) {
                                    $scope.uiGames.gridColumn.push({ field: "roundScore", enableCellEdit: false, displayName: scoreRankRoundColName, name: "roundScore", headerCellTemplate: myHeaderCellTemplate, cellTemplate: '<div class="ui-grid-cell-contents ui-grid-cell-contents-middlealign">{{row.entity.roundScore | numberEx}}/{{row.entity.roundRank}}</div>' });
                                }
                            }
                            for (var k in rounds.questions) {
                                var fieldName = "#" + rounds.questions[k].number;
                                var fieldNameWithNumberOnly = rounds.questions[k].number;
                                var matchedResult = $scope.uiGames.gridColumn.filter(function (obj) {
                                    return obj.displayName == fieldName;
                                })[0];
                                var displayName = "#" + rounds.questions[k].number + " - " + $filter("numberEx")(rounds.questions[k].maxPoints) + " pts";
                                if (currentGame.rounds[$scope.game.currentRoundNumber].questions[k].question) {
                                    displayName += "<br/>" + currentGame.rounds[$scope.game.currentRoundNumber].questions[k].question;
                                }
                                var questionType = 'default';
                                if (matchedResult == undefined) {
                                    questionType = getQuestionType(rounds.questions[k].number);
                                    if (questionType === 'wager')
                                    {
                                        $scope.uiGames.gridColumn.push({ field: fieldName, displayName: displayName, name: fieldName, headerCellTemplate: myHeaderCellTemplate, cellTemplate: '<div class="ui-grid-cell-contents questionColReadOnly"><div ng-bind-html="row.entity[col.field]"></div></div>' });
                                    }
                                    else {
                                        $scope.uiGames.gridColumn.push({ field: fieldName, displayName: displayName, name: fieldName, headerCellTemplate: myHeaderCellTemplate, cellTemplate: '<div class="ui-grid-cell-contents ui-grid-cell-contents-middlealign questionColReadOnly"><div ng-bind-html="row.entity[col.field]"></div></div>' });
                                    }
                                    
                                }
                                if (questionType === 'wager') {
                                    Item[fieldName] = "<strong>Answer</strong>: " + currentGame.teams[x].rounds[$scope.game.currentRoundNumber].questions[k].answer;
                                    Item[fieldName] += "<br/><strong>Wager</strong>: " + $filter("numberEx")(currentGame.teams[x].rounds[$scope.game.currentRoundNumber].questions[k].teamWager);
                                    Item[fieldName] += "<br/>" + (currentGame.teams[x].rounds[$scope.game.currentRoundNumber].questions[k].questionScore === currentGame.teams[x].rounds[$scope.game.currentRoundNumber].questions[k].teamWager) ? '<strong> Correct</strong>' : ' <strong>Incorrect</strong>'
                                    Item[fieldName] += "<br/><strong>Score</strong>:" + $filter("numberEx")(currentGame.teams[x].rounds[$scope.game.currentRoundNumber].questions[k].questionScore);
                                   // $scope.uiGames.options.rowHeight = 75;
                                   // $scope.uiGames.options.headerHeight = 75;

                                }
                                else {
                                    Item[fieldName] = $filter("numberEx")(currentGame.teams[x].rounds[$scope.game.currentRoundNumber].questions[k].questionScore);
                                }

                            }

                        }
                        newGridDatasource.push(Item);
                    }
                    $scope.uiGames.options.columnDefs = $scope.uiGames.gridColumn;
                    $scope.uiGames.options.data = newGridDatasource;

                }

                function getQuestionType(questionNumber) {
                    var question = $scope.game.rounds[$scope.game.currentRoundNumber].questions[questionNumber];
                    if (question.wager === '1') {
                        return 'wager';
                    } else {
                        return 'default';
                    }
                };
                $scope.getTableHeight = function () {
                    var padding = 15;
                    var rowHeight = $scope.uiGames.options.rowHeight; // your row height
                    var headerHeight = ($scope.uiGames.options.headerHeight) ? $scope.uiGames.options.headerHeight : 90; // your header height
                    return {
                        height: ($scope.uiGames.options.data.length * rowHeight + headerHeight + padding) + "px"
                    };
                };
                //$scope.getQuestionType = function(questionNumber) {
                //    var question = $scope.game.rounds[$scope.game.currentRoundNumber].questions[questionNumber];
                //    if(question.wager === '1') {
                //        return 'wager';
                //    } else {
                //        return 'default';
                //    }
                //};

            }]
    };
});

app.directive('rcTriviaSelectListTeam', function (THIS_DIRECTORY) {
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
            $scope.selected = (angular.isObject($scope.selected)) ? { value: $scope.selected } : {};

            $scope.dataArray = angular.isArray($scope.dataArray) ? $scope.dataArray : new Array();

            $scope.placeholderText = angular.isString($scope.placeholderText) ? $scope.placeholderText : "Search for Trivia Team";
        },
        controller: ["$scope", function ($scope) {
            // Controller - Create a controller which publishes an API for 
            // communicating across directives.
        }]
    };
});

app.directive('rcTriviaSelectListVenue', function (THIS_DIRECTORY) {
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
            $scope.selected = (angular.isObject($scope.selected)) ? { value: $scope.selected } : {};

            $scope.dataArray = angular.isArray($scope.dataArray) ? $scope.dataArray : new Array();

            $scope.placeholderText = angular.isString($scope.placeholderText) ? $scope.placeholderText : "Search for Trivia Joint";
        },
        controller: ["$scope", function ($scope) {
            // Controller - Create a controller which publishes an API for 
            // communicating across directives.
        }]
    };
});

app.directive('rcTriviaSelectListGame', function (THIS_DIRECTORY) {
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
            $scope.selected = (angular.isObject($scope.selected)) ? { value: $scope.selected } : {};

            $scope.dataArray = angular.isArray($scope.dataArray) ? $scope.dataArray : new Array();

            $scope.placeholderText = angular.isString($scope.placeholderText) ? $scope.placeholderText : "Search for Trivia Game";
        },
        controller: ["$scope", function ($scope) {
            // Controller - Create a controller which publishes an API for 
            // communicating across directives.
        }]
    };
});