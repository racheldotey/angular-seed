'use strict';

/* 
 * Member Dashboard Page
 * 
 * Controller for the member dashboard page.
 */

angular.module('app.member.dashboard', [])
    .controller('MemberDashboardCtrl', ['$scope', '$state', 'TriviaModalService', 'DataTableHelper', 'DTColumnBuilder',
        function($scope, $state, TriviaModalService, DataTableHelper, DTColumnBuilder) {


        // DataTable Setup
        $scope.dtGames = DataTableHelper.getDTStructure($scope, 'publicGamesList');
        $scope.dtGames.columns = [
            DTColumnBuilder.newColumn(null).withTitle('Game Name').renderWith(function (data, type, full, meta) {
                return '<a data-ui-sref="app.member.game({gameId : ' + data.id + ', pageId : 1 })">' + data.name + '</a>';
            }),
            DTColumnBuilder.newColumn('venue').withTitle('Joint'),
            DTColumnBuilder.newColumn('host').withTitle('Host'),
            DTColumnBuilder.newColumn('scheduled').withTitle('Scheduled').renderWith(function (data, type, full, meta) {
                return moment(data, 'YYYY-MM-DD HH:mm:ss').format('M/D/YYYY h:mm a');
            })
        ];
        
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