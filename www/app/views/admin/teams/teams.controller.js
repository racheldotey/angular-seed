'use strict';

/* 
 * Admin Teams Page
 * 
 * Controller for the admin teams page where system teams can be viewed and mofified.
 */

angular.module('app.admin.teams', [])
    .controller('AdminTeamsCtrl', ['$scope', '$compile', '$filter', 'DTOptionsBuilder', 'DTColumnBuilder', 'DataTableHelper', 'TriviaModalService', 
        function($scope, $compile, $filter, DTOptionsBuilder, DTColumnBuilder, DataTableHelper, TriviaModalService) {

        $scope.alertProxy = {};

        /* Modal triggers */
        // Edit User Modal
        $scope.buttonOpenEditTeamModal = function (id) {
            var found = $filter('filter')($scope.dtTeams.instance.DataTable.data(), {id: id}, true);
            if(angular.isDefined(found[0])) {
                var modalInstance = TriviaModalService.openEditTeam(found[0]);
                modalInstance.result.then(function (selectedItem) {
                    $scope.dtTeams.reloadData();
                }, function () {});
            }
        };

        // DataTable Setup
        $scope.dtTeamPlayers = {};
        $scope.dtTeamPlayers.options = DTOptionsBuilder.newOptions();

        $scope.dtTeams = DataTableHelper.getDTStructure($scope, 'adminTeamsList');
        $scope.dtTeams.options.withOption('order', [1, 'desc']).withOption('responsive', {
            details: {
                type: 'column',
                renderer: function(api, rowIdx, columns) {
                    // Get the group id
                    var id = 0;
                    var data = new Array();
                    angular.forEach(columns, function (value, key) {
                        if(value.title == 'ID') {
                            id = value.data;
                        }
                        if(value.title == 'Players') {
                            data = value.data;
                        }
                    });

                    var header = '<table datatable="" dt-options="dtTeamPlayers.options" class="table table-hover sub-table">\n\
                        <thead><tr>\n\
                        <td>ID</td>\n\
                        <td>Team Member</td>\n\
                        <td>Joined Date</td>\n\
                        </tr></thead><tbody>';

                    var body = '';
                    $.each(data, function(index, value) {
                        var joined = moment(value.joined, 'YYYY-MM-DD HH:mm:ss').format('M/D/YYYY h:mm a');
                        body += '<tr><td>' + value.id + '</td><td>' + value.name + '</td><td>' + joined + '</td></tr>\n';
                    });

                    // Create angular table element
                    body = (body) ? body : '<tr><td colspan="3"><p>This team does not have any players.</p></td></tr>';

                    var table = angular.element(header + body + '</tbody></table>');

                    // compile the table to keep the directives (ngClick)
                    $compile(table.contents())($scope);

                    return table;
                }
            }
        });
       
        $scope.dtTeams.columns = [
            DTColumnBuilder.newColumn(null).withTitle('Team Players').withClass('responsive-control text-right noclick').renderWith(function(data, type, full, meta) {
                return '<a><small>(' + data.players.length +')</small> <i class="fa"></i></a>';
            }).notSortable(),
            DTColumnBuilder.newColumn('id').withTitle('ID'),
            DTColumnBuilder.newColumn('name').withTitle('Team'),
            DTColumnBuilder.newColumn('homeVenue').withTitle('Home Joint'),
            DTColumnBuilder.newColumn(null).withTitle('Last Game').renderWith(function (data, type, full, meta) {
                return (!data.lastGameName) ? 'None' : '<a data-ui-sref="app.member.game({gameId :' + data.lastGameId + ', roundNumber : 1 })">' + data.lastGameName + '</a>';
            }),
            DTColumnBuilder.newColumn('createdBy').withTitle('Created By'),
            DTColumnBuilder.newColumn('created').withTitle('Created').renderWith(function (data, type, full, meta) {
                return moment(data, 'YYYY-MM-DD HH:mm:ss').format('M/D/YYYY h:mm a');
            }),
            DTColumnBuilder.newColumn(null).withTitle('').renderWith(function(data, type, full, meta) {
                return '<button ng-click="buttonOpenEditTeamModal(\'' + data.id + '\')" type="button" class="btn btn-default btn-xs pull-right">View</button>';
            }).notSortable(),
            DTColumnBuilder.newColumn('players').withTitle('Players').withClass('none').notSortable()
        ];
        
        $scope.buttonOpenNewGameModal = function() {
            var modalInstance = TriviaModalService.openEditGame(false);
            modalInstance.result.then(function(result) { });
        };
        
        $scope.buttonOpenNewTeamModal = function() {
            var modalInstance = TriviaModalService.openEditTeam();
            modalInstance.result.then(function(result) { 
                $scope.dtVenues.reloadData();
            });
        };
        
        $scope.buttonOpenNewVenueModal = function() {
            var modalInstance = TriviaModalService.openEditVenue(false);
            modalInstance.result.then(function(result) { });
        };
        
    }]);