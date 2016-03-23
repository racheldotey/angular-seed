'use strict';

/* 
 * Admin Teams Page
 * 
 * Controller for the admin teams page where system teams can be viewed and mofified.
 */

angular.module('app.admin.teams', [])
    .controller('AdminTeamsCtrl', ['$scope', '$compile', '$filter', 'DTOptionsBuilder', 'DTColumnBuilder', 'DataTableHelper', 'TriviaModalService', 
        function($scope, $compile, $filter, DTOptionsBuilder, DTColumnBuilder, DataTableHelper, TriviaModalService) {

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
        $scope.dtUserGroups = {};
        $scope.dtUserGroups.options = DTOptionsBuilder.newOptions();

        $scope.dtTeams = DataTableHelper.getDTStructure($scope, 'adminTeamsList');
        /*
        $scope.dtTeams.options.withOption('responsive', {
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
                        if(value.title == 'User Groups') {
                            data = value.data;
                        }
                    });

                    var header = '<table datatable="" dt-options="dtUserGroups.options" class="table table-hover sub-table">\n\
                        <thead><tr>\n\
                        <td>ID</td>\n\
                        <td>Group</td>\n\
                        <td>Description</td>\n\
                        </tr></thead><tbody>';

                    var body = '';
                    $.each(data, function(index, value) {
                        body += '<tr><td>' + value.id + '</td><td>' + value.group + '</td><td>' + value.desc + '</td></tr>\n';
                    });

                    // Create angular table element
                    body = (body) ? body : '<tr><td colspan="3"><p>This user has not been assigned to any groups.</p></td></tr>';

                    var table = angular.element(header + body + '</tbody></table>');

                    // compile the table to keep the directives (ngClick)
                    $compile(table.contents())($scope);

                    return table;
                }
            }
        });
        */
       
        $scope.dtTeams.columns = [
            /*DTColumnBuilder.newColumn(null).withTitle('Members').withClass('responsive-control text-right noclick').renderWith(function(data, type, full, meta) {
                return '<a><small>(' + data.members.length +')</small> <i class="fa"></i></a>';
            }).notSortable(),*/
            DTColumnBuilder.newColumn('id').withTitle('ID'),
            DTColumnBuilder.newColumn('team').withTitle('Team'),
            DTColumnBuilder.newColumn('createdBy').withTitle('Created By'),
            DTColumnBuilder.newColumn('created').withTitle('Created').renderWith(function (data, type, full, meta) {
                return moment(data, 'YYYY-MM-DD HH:mm:ss').format('M/D/YYYY h:mm a');
            }),
            DTColumnBuilder.newColumn(null).withTitle('').renderWith(function(data, type, full, meta) {
                return '<button ng-click="buttonOpenEditTeamModal(\'' + data.id + '\')" type="button" class="btn btn-default btn-xs pull-right">View</button>';
            }).notSortable(),
            //DTColumnBuilder.newColumn('members').withTitle('Team Members').withClass('none').notSortable()
        ];
        
        $scope.buttonOpenNewGameModal = function() {
            var modalInstance = TriviaModalService.openEditGame(false);
            modalInstance.result.then(function(result) { });
        };
        
        $scope.buttonOpenNewTeamModal = function() {
            var modalInstance = TriviaModalService.openEditTeam(false);
            modalInstance.result.then(function(result) { 
                $scope.dtVenues.reloadData();
            });
        };
        
        $scope.buttonOpenNewVenueModal = function() {
            var modalInstance = TriviaModalService.openEditVenue(false);
            modalInstance.result.then(function(result) { });
        };
        
    }]);