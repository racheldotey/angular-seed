'use strict';

/* 
 * Host Dashboard Page
 * 
 * Controller for the host dashboard page.
 */

angular.module('app.host.dashboard', [])
    .controller('HostDashboardCtrl', ['$scope', '$state', 'TriviaModalService', 'DataTableHelper', 'DTColumnBuilder', 'UserSession',
        function($scope, $state, TriviaModalService, DataTableHelper, DTColumnBuilder, UserSession) {


        // DataTable Setup
        $scope.dtGames = DataTableHelper.getDTStructure($scope, 'publicHostGamesList', UserSession.id());
        $scope.dtGames.columns = [
            DTColumnBuilder.newColumn(null).withTitle('Game Name').renderWith(function (data, type, full, meta) {
                return '<a data-ui-sref="app.host.game({gameId : ' + data.id + ', pageId : 1 })">' + data.name + '</a>';
            }),
            DTColumnBuilder.newColumn('venue').withTitle('Joint'),
            DTColumnBuilder.newColumn('host').withTitle('Host'),
            DTColumnBuilder.newColumn('scheduled').withTitle('Scheduled').renderWith(function (data, type, full, meta) {
                return moment(data, 'YYYY-MM-DD HH:mm:ss').format('M/D/YYYY h:mm a');
            })
        ];
        
        $scope.buttonNewGame = function() {
            var modalInstance = TriviaModalService.openEditGame(false);
            modalInstance.result.then(function(result) {
                $state.go('app.host.game', {'gameId' : result.id, 'roundNumber': 1});
            });
        };

    }]);