'use strict';

/* 
 * Host Scoreboard Page
 * 
 * Controller for the game scorboard, host view.
 */

angular.module('app.host.scoreboard', [])
    .controller('HostScoreboardDashboardCtrl', ['$scope', 'currentGame',
        function($scope, currentGame) {
            $scope.game = currentGame;
    }]);