'use strict';

/* @author  Rachel Carbone */

angular.module('app.modal.trivia.viewGameScoreboard', [])        
    .controller('TriviaViewGameScoreboardModalCtrl', ['$scope', '$uibModalInstance', '$filter', 'viewGame', 'DataTableHelper', 'DTColumnBuilder',
    function($scope, $uibModalInstance, $filter, viewGame, DataTableHelper, DTColumnBuilder) { 
    
    /* Used to restrict alert bars */
    $scope.alertProxy = {};
        
    $scope.dtScoreboard = DataTableHelper.getDTStructure($scope, 'publicGameScoreboardList', viewGame.id, false, viewGame.roundNumber);
    $scope.dtScoreboard.options.withOption('order', [1, 'desc'])
    .withDOM('<"row"<"col-sm-12 col-md-12"fr><"col-sm-12 add-space"t><"col-sm-6"l><"col-sm-6 text-right"i><"col-sm-12 text-center"p>>');
    $scope.dtScoreboard.columns = [
        DTColumnBuilder.newColumn('team').withTitle('Team'),
        DTColumnBuilder.newColumn('gameScore').withTitle('Game Score').renderWith(function (data, type, full, meta) {
            return (type === 'display') ? $filter('numberEx')(data) : parseFloat(data);
        }),
        DTColumnBuilder.newColumn('gameRank').withTitle('Game Rank').renderWith(function (data, type, full, meta) {
            return (type === 'display') ? data : parseInt(data);
        }),
        DTColumnBuilder.newColumn('roundScore').withTitle('Round Score').renderWith(function (data, type, full, meta) {
            return (type === 'display') ? $filter('numberEx')(data) : parseFloat(data);
        }),
        DTColumnBuilder.newColumn('roundRank').withTitle('Round Rank ').renderWith(function (data, type, full, meta) {
            return (type === 'display') ? data : parseInt(data);
        })
    ];
        
    /* Click event for the Cancel button */
    $scope.buttonCancel = function() {
        $uibModalInstance.dismiss(false);
    };
        
}]);