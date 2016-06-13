'use strict';

/* 
 * Global Player Leaderboard
 * 
 * Controller for the game scorboard, host view.
 */

angular.module('app.leaderboards.globalPlayers', [])
    .controller('GlobalPlayerLeaderboardCtrl', ['$scope', 
        function($scope) {
            $scope.game = {};
    }]);