'use strict';

/* 
 * Host Scoreboard Page
 * 
 * Controller the game for the host.
 */

angular.module('app.host.scoreboard', [])
    .controller('HostScoreboardDashboardCtrl', ['$scope', 'currentGame', 
        function($scope, currentGame) {
            $scope.game = currentGame;
    }]);