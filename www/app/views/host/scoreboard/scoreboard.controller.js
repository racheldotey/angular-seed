'use strict';

/* 
 * Player Scoreboard Page
 * 
 * Controller the game for the player.
 */

angular.module('app.host.scoreboard', [])
    .controller('HostScoreboardDashboardCtrl', ['$scope', 'currentGame', 'TriviaGame', 'AlertConfirmService', 'TriviaModalService', 'DTOptionsBuilder', 'DTColumnDefBuilder',
        function($scope, currentGame, TriviaGame, AlertConfirmService, TriviaModalService, DTOptionsBuilder, DTColumnDefBuilder) {
            $scope.game = currentGame;
    }]);