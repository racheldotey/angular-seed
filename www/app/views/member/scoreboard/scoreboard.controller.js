'use strict';

/* 
 * Host Scoreboard Page
 * 
 * Controller the game for the host.
 */

angular.module('app.member.scoreboard', [])
    .controller('MemberScoreboardDashboardCtrl', ['$scope', 'currentGame', 'DTOptionsBuilder', 'DTColumnDefBuilder', 
        function($scope, currentGame, DTOptionsBuilder, DTColumnDefBuilder) {
            $scope.game = currentGame;
    }]);