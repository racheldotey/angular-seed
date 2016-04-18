'use strict';

/* @author  Rachel Carbone */

angular.module('app.modal.trivia.editTeam', [])        
    .controller('TriviaEditTeamModalCtrl', ['ApiRoutesGames', '$scope', '$uibModalInstance', 'editing',
    function(ApiRoutesGames, $scope, $uibModalInstance, editing) {        
    /* Used to restrict alert bars */
    $scope.alertProxy = {};
    
    /* Holds the add / edit form on the modal */
    $scope.form = {};
    
    /* Save for resetting purposes */
    $scope.saved = (angular.isDefined(editing.id)) ? angular.copy(editing) : { };
    
    /* Item to display and edit */
    $scope.editing = angular.copy($scope.saved);
    
    /* Modal Mode */    
    $scope.setMode = function(type) {
        $scope.viewMode = false;
        $scope.newMode = false;
        $scope.editMode = false;
        
        switch(type) {
            case 'new':
                $scope.newMode = true;
                $scope.editMode = true;
                break;
            case 'edit':
                $scope.editMode = true;
                break;
            case 'view':
            default:
                $scope.viewMode = true;
                break;
        }
    };
    
    $scope.getMode = function() {
        if($scope.newMode) {
            return 'new';
        } else if($scope.editMode) {
            return 'edit';
        } else {
            return 'view';
        }
    };
    
    /* Save for resetting purposes */
    if(angular.isDefined(editing.id) && editing.id) {
        $scope.setMode('view');
    } else {
        $scope.setMode('new');
        $scope.editing.name = "A " + moment().format('dddd') + " Team in " + moment().format('MMMM');
        $scope.editing.players = [ { email : 'rachellcarbone@gmail.com' } ];
    }
    
    /* Click event for the Add Email Input */
    $scope.buttonNewEmailField = function() {
        $scope.editing.players.push({ email : 'rachellcarbone@gmail.com' });
    };
    
        
    /* Click event for the Edit button */
    $scope.buttonEdit = function() {
        $scope.setMode('edit');
    };
    
    /* Click event for the Add New Team button */
    $scope.buttonNew = function() {
        ApiRoutesGames.addTeam($scope.editing).then(function(result) {
            console.log(result);
        }, function(error) {
            console.log(error);
        });
    };
        
    /* Click event for the Save Team button */
    $scope.buttonSave = function() {
        
    };
        
    /* Click event for the Cancel button */
    $scope.buttonCancel = function() {
        if($scope.getMode() === 'new') {
            $uibModalInstance.dismiss(false);
        } else {
            $scope.setMode('view');
        }
    };
        
    /* Click event for the Delete Team button */
    $scope.buttonDelete = function() {
        
    };
    
}]);