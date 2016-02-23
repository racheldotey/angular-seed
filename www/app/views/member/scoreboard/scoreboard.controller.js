'use strict';

/* 
 * Host Scoreboard Page
 * 
 * Controller the game for the host.
 */

angular.module('app.member.scoreboard', [])
    .controller('MemberScoreboardDashboardCtrl', ['$scope', 'currentGame', 
        function($scope, currentGame) {
            $scope.game = currentGame;
    }]);