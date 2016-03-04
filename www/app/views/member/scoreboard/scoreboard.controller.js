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
            $scope.dtScoreboard = {};
            $scope.dtScoreboard.options = DTOptionsBuilder.newOptions()
                .withDOM('lfrtip')
                .withOption('scrollY', '300px')
                .withOption('scrollX', '100%')
                .withOption('scrollCollapse', true)
                .withOption('paging', false)
                .withFixedColumns({ leftColumns: 1 })
                .withOption('responsive', false);
    }]);