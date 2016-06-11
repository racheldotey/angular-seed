'use strict';

/* 
 * Admin Venues Page
 * 
 * Controller for the admin venues page where system venues can be viewed and mofified.
 */

angular.module('app.admin.hosts', [])
        .controller('AdminHostsCtrl', ['$scope', '$compile', '$filter', 'DTOptionsBuilder', 'DTColumnBuilder', 'DataTableHelper', 'ModalService', 'ApiRoutesUsers', 'TriviaModalService',
            function ($scope, $compile, $filter, DTOptionsBuilder, DTColumnBuilder, DataTableHelper, ModalService, ApiRoutesUsers, TriviaModalService) {

                $scope.alertProxy = {};

                /* Modal triggers */
                // Edit User Modal
                $scope.buttonOpenEditHostModal = function (id) {
                    var found = $filter('filter')($scope.dtHosts.instance.DataTable.data(), { id: id }, true);
                    if (angular.isDefined(found[0])) {
                        var modalInstance = TriviaModalService.openEditHost(found[0]);
                        modalInstance.result.then(function (result) {
                            $scope.alertProxy.success(result);
                            $scope.dtHosts.reloadData();
                        });
                    }
                };

                // DataTable Setup
                $scope.dtUserGroups = {};
                $scope.dtUserGroups.options = DTOptionsBuilder.newOptions();

                $scope.dtHosts = DataTableHelper.getDTStructure($scope, 'adminHostList');
                $scope.dtHosts.options.withOption('order', [0, 'desc']);

                $scope.dtHosts.columns = [
                    DTColumnBuilder.newColumn('id').withTitle('ID'),
                    DTColumnBuilder.newColumn(null).withTitle('').withClass('text-center').renderWith(function (data, type, full, meta) {
                        return (data.disabled === null) ?
                                '<span title="This Trivia Joint is enabled." class="label label-success" style="font-size: 12px; padding: 5px 8px;"><i class="fa fa-lg fa-check-circle-o"></i></span>' :
                                '<span title="This Trivia Joint has been disabled and cannot host games." class="label label-danger" style="font-size: 12px; padding: 5px 8px;"><i class="fa fa-lg fa-exclamation-circle"></i></span>';
                    }).notSortable(),
                    DTColumnBuilder.newColumn('nameFirst').withTitle('First Name'),
                    DTColumnBuilder.newColumn('nameLast').withTitle('Last Name'),
                   // DTColumnBuilder.newColumn('email').withTitle('Email Address'),
                    DTColumnBuilder.newColumn(null).withTitle('Email Address').renderWith(function (data, type, full, meta) {
                        return '<a ng-click="buttonOpenEditHostModal(\'' + data.id + '\')">' + data.email + '</a>';
                    }),
                    DTColumnBuilder.newColumn('city').withTitle('City'),
                    DTColumnBuilder.newColumn('state').withTitle('State'),
                    DTColumnBuilder.newColumn('phone').withTitle('Phone'),
                    DTColumnBuilder.newColumn('website').withTitle('Website').renderWith(function (data, type, full, meta) {
                        return (data.length <= 0) ? '' : '<a href="' + data + '" target="_blank">' + data + '</a>';
                    }),
                    DTColumnBuilder.newColumn('facebook').withTitle('Facebook').renderWith(function (data, type, full, meta) {
                        return (data.length <= 0) ? '' : '<a href="' + data + '" target="_blank">' + data + '</a>';
                    }),
                    //DTColumnBuilder.newColumn('triviaDay').withTitle('Day'),
                    //DTColumnBuilder.newColumn('triviaTime').withTitle('Hours'),
                    //DTColumnBuilder.newColumn(null).withTitle('Created By').renderWith(function (data, type, full, meta) {
                    //    return (data && data.createdBy !== null) ?'<a href="mailto:' + data.createdByEmail + '">' + data.createdBy + '</a>' : '';
                    //}),
                    DTColumnBuilder.newColumn(null).withTitle('Created By').renderWith(function (data, type, full, meta) {
                        return '<a ng-click="buttonOpenEditUserModal(\'' + data.trv_users_id + '\')">' + data.createdBy + '</a>';
                    }),
                    DTColumnBuilder.newColumn('created').withTitle('Created').renderWith(function (data, type, full, meta) {
                        return moment(data, 'YYYY-MM-DD HH:mm:ss').format('M/D/YYYY h:mm a');
                    }),
                    DTColumnBuilder.newColumn(null).withTitle('').renderWith(function (data, type, full, meta) {
                        return '<button ng-click="buttonOpenEditHostModal(\'' + data.id + '\')" type="button" class="btn btn-default btn-xs pull-right">View</button>';
                    }).notSortable()

                ];

                $scope.buttonOpenNewHostModal = function () {
                    var modalInstance = TriviaModalService.openEditHost(false);
                    modalInstance.result.then(function (result) {
                        $scope.alertProxy.success(result);
                        $scope.dtHosts.reloadData();
                    });
                };
                $scope.buttonOpenEditUserModal = function (id) {

                    if (angular.isDefined(id)) {
                        var modalInstance = ModalService.openEditUser(id);
                        modalInstance.result.then(function (selectedItem) {
                            $scope.dtHosts.reloadData();
                        }, function () { });
                    };
                }

            }]);


