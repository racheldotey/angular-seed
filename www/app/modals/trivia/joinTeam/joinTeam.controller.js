'use strict';

/* @author  Rachel Carbone */

angular.module('app.modal.trivia.joinTeam', [])        
    .controller('TriviaJoinTeamModalCtrl', ['$scope', '$uibModalInstance', 'AlertConfirmService', 'ApiRoutesGames', 'userId', 'currentTeam', 'teamsList',
    function($scope, $uibModalInstance, AlertConfirmService, ApiRoutesGames, userId, currentTeam, teamsList) {  
    
    $scope.userId = userId;
    $scope.currentTeam = currentTeam;
    $scope.teamsList = teamsList;
    
    $scope.joinTeam = {};
    if(currentTeam) {
        for(var t = 0; t < teamsList.length; t++) {
            if(parseInt(teamsList[t].id) === parseInt(currentTeam.id)) {
                $scope.joinTeam = teamsList[t];
                t = teamsList.length;
                break;
            }
        }
    }
    
    /* Used to restrict alert bars */
    $scope.alertProxy = {};
    
    /* Holds the add / edit form on the modal */
    $scope.form = {};    
    
    /* Click event for the Joiun Team button */
    $scope.buttonJoinTeam = function() {
        if(angular.isDefined($scope.joinTeam.value) &&
            ($scope.joinTeam.value.id === $scope.currentTeam.id)) {
            $scope.alertProxy.error('You are already a member of that team.');
        } else if(angular.isUndefined($scope.joinTeam.value.id)) {
            $scope.form.joinTeam.$setDirty();
            $scope.alertProxy.error('Please select a team to join.');
        }  else if (angular.isDefined($scope.joinTeam.value)) {
            
            AlertConfirmService.confirm("Are you positive that you would like to leave your current team - '" + 
                    $scope.currentTeam.name + "' - and join - '" + $scope.joinTeam.value.label + "'?", "Changing Team")
                .result.then(function (result) {

                    ApiRoutesGames.addTeamMemberById($scope.userId, $scope.joinTeam.value.id).then(
                            function (result) {
                                result.msg = "You have successfully joined " + $scope.joinTeam.value.label + ".";
                                $uibModalInstance.close(result);
                            }, function (error) {
                        $scope.alertProxy.error(error);
                    });

                }, function (declined) {
                   $scope.alertProxy.success('No changes were saved.');
                });
        } else {
            ApiRoutesGames.addTeamMemberById($scope.userId, $scope.joinTeam.value.id).then(
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