'use strict';

/* @author  Rachel Carbone */

angular.module('app.modal.trivia.editTeam', [])        
    .controller('TriviaEditTeamModalCtrl', ['$scope', '$uibModalInstance', '$filter', 'AlertConfirmService', 'editing',
    function($scope, $uibModalInstance, editing) {        
    /* Used to restrict alert bars */
    $scope.alertProxy = {};
    
    /* Holds the add / edit form on the modal */
    $scope.form = {};
    
    
    /* Save for resetting purposes */
    $scope.saved = (angular.isDefined(editing.id)) ? angular.copy(editing) : {};
    
    /* Item to display and edit */
    $scope.editing = angular.copy($scope.saved);
    
    /* Click event for the Add Team button */
    $scope.buttonAddTeam = function() {
    };
        
    /* Click event for the Cancel button */
    $scope.buttonCancel = function() {
        $uibModalInstance.dismiss(false);
    };
    
}]);