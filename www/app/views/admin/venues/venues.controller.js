'use strict';

/* 
 * Admin Venues Page
 * 
 * Controller for the admin venues page where system venues can be viewed and mofified.
 */

angular.module('app.admin.venues', [])
        .controller('AdminVenuesCtrl', ['$scope', '$compile', '$filter', 'DTOptionsBuilder', 'DTColumnBuilder', 'DataTableHelper', 'TriviaModalService',
            function ($scope, $compile, $filter, DTOptionsBuilder, DTColumnBuilder, DataTableHelper, TriviaModalService) {

                /* Modal triggers */
                // Edit User Modal
                $scope.buttonOpenEditVenueModal = function (id) {
                    var found = $filter('filter')($scope.dtVenues.instance.DataTable.data(), {id: id}, true);
                    if (angular.isDefined(found[0])) {
                        var modalInstance = TriviaModalService.openEditVenue(found[0]);
                        modalInstance.result.then(function (selectedItem) {
                            $scope.dtVenues.reloadData();
                    }
                };

                // DataTable Setup
                $scope.dtUserGroups = {};
                $scope.dtUserGroups.options = DTOptionsBuilder.newOptions();

                $scope.dtVenues = DataTableHelper.getDTStructure($scope, 'adminVenuesList');
                /*
                 $scope.dtVenues.options.withOption('order', [1, 'desc']).withOption('responsive', {
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

                $scope.dtVenues.columns = [/*
                 DTColumnBuilder.newColumn(null).withTitle('Games').withClass('responsive-control text-right noclick').renderWith(function(data, type, full, meta) {
                 return '<a><small>(' + data.games.length +')</small> <i class="fa"></i></a>';
                 }).notSortable(),*/
                    DTColumnBuilder.newColumn('id').withTitle('ID'),
                    DTColumnBuilder.newColumn('logo').withTitle('Logo').renderWith(function (data, type, full, meta) {
                        return '<img ng-src="' + data + '" class="img-responsive" style="max-height: 25px; max-width: 25px;" />';
                    }),
                    DTColumnBuilder.newColumn('venue').withTitle('Joint Name'),
                    DTColumnBuilder.newColumn('city').withTitle('City'),
                    DTColumnBuilder.newColumn('state').withTitle('State'),
                    DTColumnBuilder.newColumn('website').withTitle('Website').renderWith(function (data, type, full, meta) {
                        return (data.length <= 0) ? '' : '<a href="' + data + '" target="_blank">Website</a>';
                    }),
                    DTColumnBuilder.newColumn('facebook').withTitle('Facebook').renderWith(function (data, type, full, meta) {
                        return (data.length <= 0) ? '' : '<a href="' + data + '" target="_blank">Facebook</a>';
                    }),
                    DTColumnBuilder.newColumn('trivia_day').withTitle('Day'),
                    DTColumnBuilder.newColumn('trivia_time').withTitle('Hours'),
                    DTColumnBuilder.newColumn('referral').withTitle('Referral Code').renderWith(function (data, type, full, meta) {
                        return '<code>' + data + '</code>';
                    }),
                    DTColumnBuilder.newColumn('createdBy').withTitle('Created By'),
                    DTColumnBuilder.newColumn('created').withTitle('Created').renderWith(function (data, type, full, meta) {
                        return moment(data, 'YYYY-MM-DD HH:mm:ss').format('M/D/YYYY h:mm a');
                    }),
                    DTColumnBuilder.newColumn(null).withTitle('').renderWith(function (data, type, full, meta) {
                        return '<button ng-click="buttonOpenEditVenueModal(\'' + data.id + '\')" type="button" class="btn btn-default btn-xs pull-right">View</button>';
                    }).notSortable(),
                            //DTColumnBuilder.newColumn('games').withTitle('Upcoming Games').withClass('none').notSortable()
                ];

                $scope.buttonOpenNewGameModal = function () {
                    var modalInstance = TriviaModalService.openEditGame(false);
                    modalInstance.result.then(function (result) { });
                };

                $scope.buttonOpenNewTeamModal = function () {
                    var modalInstance = TriviaModalService.openEditTeam(false);
                    modalInstance.result.then(function (result) { });
                };

                $scope.buttonOpenNewVenueModal = function () {
                    var modalInstance = TriviaModalService.openEditVenue(false);
                    modalInstance.result.then(function (result) {
                        $scope.dtVenues.reloadData();
                    });
                };

            }]);