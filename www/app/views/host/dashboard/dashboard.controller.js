'use strict';

/* 
 * Host Dashboard Page
 * 
 * Controller for the host dashboard page.
 */

angular.module('app.host.dashboard', [])
    .controller('HostDashboardCtrl', ['$scope', '$state', 'TriviaModalService', function($scope, $state, TriviaModalService) {

        $scope.buttonNewGame = function() {
            var modalInstance = TriviaModalService.openEditGame(false);
            modalInstance.result.then(function(result) {
                $state.go('app.host.game', {'gameId' : result.id, 'roundNumber': 1});
            });
        };

    }]);