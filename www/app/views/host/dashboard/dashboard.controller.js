'use strict';

/* 
 * Host Dashboard Page
 * 
 * Controller for the host dashboard page.
 */

angular.module('app.host.dashboard', [])
    .controller('HostDashboardCtrl', ['$scope', '$state', '$compile', '$filter', 'ModalService', 'TriviaModalService', 'DataTableHelper', 'DTOptionsBuilder', 'DTColumnBuilder', 'UserSession', 'HostData', 'AlertConfirmService', 'ApiRoutesGames',
        function($scope, $state, $compile, $filter, ModalService, TriviaModalService, DataTableHelper, DTOptionsBuilder, DTColumnBuilder, UserSession, HostData, AlertConfirmService, ApiRoutesGames) {

        /* Used to restrict alert bars */
        $scope.alertProxy = {};

        $scope.hostActiveGames = HostData.activeGames || [];

        // DataTable Setup
        $scope.dtScoreboard = {};
        $scope.dtScoreboard.options = DTOptionsBuilder.newOptions();
        // DataTable Setup
        $scope.dtGames = DataTableHelper.getDTStructure($scope, 'publicHostGamesList', UserSession.id());
        $scope.dtGames.columns = [
            DTColumnBuilder.newColumn(null).withTitle('Scoreboard').withClass('responsive-control text-center noclick').renderWith(function(data, type, full, meta) {
                return '<a><small>Scoreboard</small> <i class="fa"></i></a>';
            }).notSortable(),
            DTColumnBuilder.newColumn(null).withTitle('Game Name').renderWith(function (data, type, full, meta) {
                return '<a data-ui-sref="app.member.game({gameId : ' + data.id + ', pageId : 1 })">' + data.name + '</a>';
            }),
            DTColumnBuilder.newColumn('venue').withTitle('Joint'),
            DTColumnBuilder.newColumn('host').withTitle('Host'),
            DTColumnBuilder.newColumn(null).withTitle('Status').renderWith(function (data, type, full, meta) {
                var scheduled = (type !== 'display') ? moment(data.scheduled, 'YYYY-MM-DD HH:mm:ss').format('YYMDHms') : 
                        moment(data.scheduled, 'YYYY-MM-DD HH:mm:ss').format('h:mm a on M/D/YYYY ');
                var started = (type !== 'display') ? moment(data.started, 'YYYY-MM-DD HH:mm:ss').format('YYMDHms') : 
                        moment(data.started, 'YYYY-MM-DD HH:mm:ss').format('h:mm a on M/D/YYYY ');
                var ended = (type !== 'display') ? moment(data.ended, 'YYYY-MM-DD HH:mm:ss').format('YYMDHms') : 
                        moment(data.ended, 'YYYY-MM-DD HH:mm:ss').format('h:mm a on M/D/YYYY ');
                    
                if(data.ended) {
                    return (type !== 'display') ? ended : 
                            '<span title="Scheduled: ' + scheduled + ' Started: ' + started +  ' Ended: ' + ended +  '">Ended at  ' + ended + '</span>';
                } else if(data.started) {
                    return (type !== 'display') ? started : 
                            '<span title="Scheduled: ' + scheduled + ' Started: ' + started +  '">In progress, started at  ' + started + '</span>';
                } else {
                    return (type !== 'display') ? scheduled : 
                            '<span title="Scheduled: ' + scheduled + '">Scheduled for  ' + scheduled + '</span>';
                }
            }),
            DTColumnBuilder.newColumn('scoreboard').withTitle('Scoreboard').notSortable().withClass('none')
        ];
        $scope.dtGames.options.withOption('order', [4, 'desc']).withOption('responsive', {
            details: {
                type: 'column',
                renderer: function(api, rowIdx, columns) {
                    var data = {};
                    angular.forEach(columns, function (value, key) {
                        if(value.title === 'Scoreboard') {
                            data = value.data;
                        }
                    });
                        
                    var header = '<table datatable="" dt-options="dtScoreboard.options" class="table sub-table table-striped">\n\
                        <thead><tr>\n\
                        <th>Rank</th>\n\
                        <th>Score</th>\n\
                        <th>Team</th>\n\
                        </tr></thead><tbody>';

                    var body = '';
                    $.each(data, function(index, value) {
                        //var winner = (value.gameWinner === '1') ? '' : '';
                        var score = $filter('numberEx')(value.gameScore);
                        var name = (!value.teamName) ? "Team ID #" + value.teamId: value.teamName;
                        body += '<tr><td>' + value.gameRank + '</td><td>' + score + '</td><td>' + name + '</td></tr>\n';
                    });

                    // Create angular table element
                    body = (body) ? body : '<tr><td colspan="3"><p>There are no teams participating in this game.</p></td></tr>';

                    var table = angular.element(header + body + '</tbody></table>');

                    // compile the table to keep the directives (ngClick)
                    $compile(table.contents())($scope);

                    return table;
                }
            }
        });
        
        $scope.buttonNewGame = function() {
            if($scope.hostActiveGames.length >= 1) {
                AlertConfirmService.alert("Game hosts may not have more than one game running at a time.", "Unable to start game.");
            } else {
                var modalInstance = TriviaModalService.openEditGame(false);
                modalInstance.result.then(function (result) {
                    $state.go('app.host.game', {'gameId': result.id, 'roundNumber': 1});
                });
            }
        };
        
        $scope.buttonInviteSiteSignup = function() {
            var modalInstance = ModalService.openInviteSiteSignup();
            modalInstance.result.then(function(result) {
                $scope.alertProxy.success(result);
            });
        };
        
        $scope.buttonSignupPlayer = function() {
            var modalInstance = ModalService.openSignup(false);
            modalInstance.result.then(function(result) {
                $scope.alertProxy.success(result);
            });
        };
        
        $scope.buttonCreateTeam = function() {
            var g = angular.isDefined($scope.hostActiveGames[0]) ? $scope.hostActiveGames[0] : {};
            var modalInstance = TriviaModalService.openEditTeam(false, false, g.venueId, g.id);
            modalInstance.result.then(function (result) {
                for(var i = 0; i < result.invites.length; i++) {
                    $scope.alertProxy.success(result.invites[i].msg);
                }
            }, function () {});
        };
        
        $scope.buttonCheckinTeam = function(gameId) {
            var game = (angular.isDefined($scope.dtGames.rows[gameId])) ? $scope.dtGames.rows[gameId] : {};
            var modalInstance = TriviaModalService.openAddTeam(game);
            modalInstance.result.then(function (result) {
                $scope.alertProxy.success("Team successfully checked in to current game.");
            }, function () {});
        };
        
        $scope.buttonEndGame = function(gameId) {
        AlertConfirmService.confirm('Are you sure you want to end this game? It cannot be started again once it has been closed.', 'Confirm End Game.')
            .result.then(function () {
                AlertConfirmService.confirm('Are you sure you positive you would like to close this game? It will finalize team scores.', 'Warning! Closing Game.')
                    .result.then(function () {
                        ApiRoutesGames.endGame(gameId).then(function (result) {
                            $scope.alertProxy.success(result.msg);
                            $scope.hostActiveGames = [];
                            $scope.dtGames.reloadData();
                        }, function (error) {
                            reject(error);
                        });
                    }, function (declined) {});
            }, function (declined) {});
            
        };

    }]);