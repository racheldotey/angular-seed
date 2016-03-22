'use strict';

/* @author  Rachel Carbone */

angular.module('app.modal.trivia.invitePlayer', [])        
    .controller('TriviaInvitePlayerModalCtrl', ['$scope', '$uibModalInstance', '$filter', 'AlertConfirmService', 'editing',
    function($scope, $uibModalInstance, editing) {        
    /* Used to restrict alert bars */
    $scope.alertProxy = {};
    
    /* Holds the add / edit form on the modal */
    $scope.form = {};
    
    /* Item to display and edit */
    $scope.invite = {
        'email' : ''
    };
    
    /* Click event for the Add Team button */
    $scope.buttonInvite = function() {
    };
        
    /* Click event for the Cancel button */
    $scope.buttonCancel = function() {
        $uibModalInstance.dismiss(false);
    };
    
}]);