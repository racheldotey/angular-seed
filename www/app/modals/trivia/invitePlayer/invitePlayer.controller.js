'use strict';

/* @author  Rachel Carbone */

angular.module('app.modal.trivia.invitePlayer', [])        
    .controller('TriviaInvitePlayerModalCtrl', ['$scope', '$uibModalInstance', 'ApiRoutesEmails',
    function($scope, $uibModalInstance, ApiRoutesEmails) {        
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
        ApiRoutesEmails.sendInviteNewPlayerEmail($scope.invite).then(function(response) {
            console.log(response);
        }, function(error) {
            console.log(error);
        });
    };
        
    /* Click event for the Cancel button */
    $scope.buttonCancel = function() {
        $uibModalInstance.dismiss(false);
    };
    
}]);