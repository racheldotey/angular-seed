'use strict';

/* 
 * Public Landing Page
 * 
 * The main not authenticated, public, landing page (index/home/default).
 */

angular.module('app.public.landing', [])
    .controller('PublicLandingCtrl',  ['$scope', '$compile', '$filter', 'DataTableHelper', 'DTOptionsBuilder', 'DTColumnBuilder',
        function($scope, $compile, $filter, DataTableHelper, DTOptionsBuilder, DTColumnBuilder) {


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
            DTColumnBuilder.newColumn(null).withTitle('Status').renderWith(function (data, type, full, meta) {
                var scheduled = moment(data.scheduled, 'YYYY-MM-DD HH:mm:ss').format('h:mm a on M/D/YYYY ');
                var started = moment(data.started, 'YYYY-MM-DD HH:mm:ss').format('h:mm a on M/D/YYYY ');
                var ended = moment(data.ended, 'YYYY-MM-DD HH:mm:ss').format('h:mm a on M/D/YYYY ');
                    
                if(data.ended) {
                    return '<span title="Scheduled: ' + scheduled + ' Started: ' + started +  ' Ended: ' + ended +  '">Ended at  ' + ended + '</span>';
                } else if(data.started) {
                    return '<span title="Scheduled: ' + scheduled + ' Started: ' + started +  '">In progress, started at  ' + started + '</span>';
                } else {
                    return '<span title="Scheduled: ' + scheduled + '">Scheduled for  ' + scheduled + '</span>';
                }
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

    }]);