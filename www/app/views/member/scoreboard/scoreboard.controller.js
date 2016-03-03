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
            $scope.dtScoreboard.dtOptions = DTOptionsBuilder.newOptions()
                .withDOM('t')
                .withOption('scrollY', '300px')
                .withOption('scrollX', '100%')
                .withOption('scrollCollapse', true)
                .withOption('paging', false)
                .withFixedColumns({ leftColumns: 1 });
    
            $scope.dtScoreboard.dtColumnDefs = [
                DTColumnDefBuilder.newColumnDef(0),
                DTColumnDefBuilder.newColumnDef(1),
                DTColumnDefBuilder.newColumnDef(2),
                DTColumnDefBuilder.newColumnDef(3),
                DTColumnDefBuilder.newColumnDef(4),
                DTColumnDefBuilder.newColumnDef(5),
                DTColumnDefBuilder.newColumnDef(6),
                DTColumnDefBuilder.newColumnDef(7),
                DTColumnDefBuilder.newColumnDef(8)
            ];
    }]);