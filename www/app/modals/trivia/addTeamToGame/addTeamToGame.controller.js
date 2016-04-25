'use strict';

/* @author  Rachel Carbone */

angular.module('app.modal.trivia.addTeamToGame', [])        
    .controller('TriviaAddTeamToGameModalCtrl', ['$scope', '$uibModalInstance', 'TriviaScoreboard', 'game', 'teamsList',
    function($scope, $uibModalInstance, TriviaScoreboard, game, teamsList) {  
    
    $scope.game = game;
    $scope.teamsList = teamsList;
    $scope.addTeam = '';
        
    /* Used to restrict alert bars */
    $scope.alertProxy = {};
    
    /* Holds the add / edit form on the modal */
    $scope.form = {};    
    
    /* Click event for the Add Team button */
    $scope.buttonAddTeam = function() {
        if(!$scope.form.addTeam.$valid) {
            $scope.form.addTeam.$setDirty();
            $scope.alertProxy.error('Please select a team to sign into your game.');
        } else {
            TriviaScoreboard.addTeamToGame($scope.addTeam.id, $scope.game.id).then(
                function (result) {
                    $uibModalInstance.close(result);
                }, function (error) {
                    $scope.alertProxy.error(error);
                });
        }
    };
        
    /* Click event for the Cancel button */
    $scope.buttonCancel = function() {
        $uibModalInstance.dismiss(false);
    };
        
}]);