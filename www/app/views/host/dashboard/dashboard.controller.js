'use strict';

/* 
 * Host Dashboard Page
 * 
 * Controller for the host dashboard page.
 */

angular.module('app.host.dashboard', [])
    .controller('HostDashboardCtrl', ['$scope', 'TriviaModalService', function($scope, TriviaModalService) {

        $scope.buttonNewGame = function() {
            var modalInstance = TriviaModalService.openEditGame(false);
            modalInstance.result.then(function(result) {
                console.log(result);
            });
        };

    }]);