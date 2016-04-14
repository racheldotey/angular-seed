'use strict';

/* 
 * Host Dashboard Page
 * 
 * Controller for the host dashboard page.
 */

angular.module('app.host.dashboard', [])
    .controller('HostDashboardCtrl', ['$scope', '$state', 'TriviaModalService', 'DataTableHelper', 'DTColumnBuilder', 'UserSession', 'HostData', 'AlertConfirmService',
        function($scope, $state, TriviaModalService, DataTableHelper, DTColumnBuilder, UserSession, HostData, AlertConfirmService) {

        $scope.hostActiveGames = HostData.activeGames || [];

        // DataTable Setup
        $scope.dtGames = DataTableHelper.getDTStructure($scope, 'publicHostGamesList', UserSession.id());
        $scope.dtGames.columns = [
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
            })
        ];
        
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