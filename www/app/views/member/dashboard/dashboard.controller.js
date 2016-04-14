'use strict';

/* 
 * Member Dashboard Page
 * 
 * Controller for the member dashboard page.
 */

angular.module('app.member.dashboard', [])
    .controller('MemberDashboardCtrl', ['$scope', '$state', '$compile', '$filter', 'TriviaModalService', 'DataTableHelper', 'DTOptionsBuilder', 'DTColumnBuilder',
        function($scope, $state, $compile, $filter, TriviaModalService, DataTableHelper, DTOptionsBuilder, DTColumnBuilder) {


        // DataTable Setup
        $scope.dtScoreboard = {};
        $scope.dtScoreboard.options = DTOptionsBuilder.newOptions();
        // DataTable Setup
        $scope.dtGames = DataTableHelper.getDTStructure($scope, 'publicGamesList');
        $scope.dtGames.columns = [
            DTColumnBuilder.newColumn(null).withTitle('Scoreboard').withClass('responsive-control text-center noclick').renderWith(function(data, type, full, meta) {
                return '<a><small>Scoreboard</small> <i class="fa"></i></a>';
            }).notSortable(),
            DTColumnBuilder.newColumn(null).withTitle('Game Name').renderWith(function (data, type, full, meta) {
                return '<a data-ui-sref="app.member.game({gameId : ' + data.id + ', pageId : 1 })">' + data.name + '</a>';
            }),
            DTColumnBuilder.newColumn('venue').withTitle('Joint'),
            DTColumnBuilder.newColumn('host').withTitle('Host'),
            DTColumnBuilder.newColumn('scheduled').withTitle('Scheduled').renderWith(function (data, type, full, meta) {
                return moment(data, 'YYYY-MM-DD HH:mm:ss').format('M/D/YYYY h:mm a');
            }),
            DTColumnBuilder.newColumn('scoreboard').withTitle('Scoreboard').notSortable().withClass('none')
        ];
        $scope.dtGames.options.withOption('responsive', {
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
                        body += '<tr><td>' + value.gameRank + '</td><td>' + score + '</td><td>' + value.teamName + '</td></tr>\n';
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
        
        $scope.buttonInviteFriend = function() {
            var modalInstance = TriviaModalService.openInviteFriend(false);
            modalInstance.result.then(function(result) {
                $scope.alertProxy.success('Invite sent.');
            });
        };
        
        $scope.buttonCreateTeam = function() {
            var modalInstance = TriviaModalService.openEditGame(false);
            modalInstance.result.then(function(result) {
                $state.go('app.host.game', {'gameId' : result.id, 'roundNumber': 1});
            });
        };

    }]);