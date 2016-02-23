'use strict';

/* 
 * Player Scoreboard Page
 * 
 * Controller the game for the player.
 */

angular.module('app.host.scoreboard', [])
    .controller('HostScoreboardDashboardCtrl', ['$scope', 'currentGame', 
        function($scope, currentGame) {
            $scope.game = currentGame;
    }]);