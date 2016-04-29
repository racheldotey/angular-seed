'use strict';

/* @author  Rachel Carbone */

angular.module('app.modal.trivia.editTeam', [])        
    .controller('TriviaEditTeamModalCtrl', ['ApiRoutesGames', 'AlertConfirmService', '$scope', '$uibModalInstance', 'editing', 'venuesList', 'addUserId',
    function(ApiRoutesGames, AlertConfirmService, $scope, $uibModalInstance, editing, venuesList, addUserId) {
    
    $scope.automaticallyAddUserId = addUserId || false;
    
    /* Used to restrict alert bars */
    $scope.alertProxy = {};
    
    /* Holds the add / edit form on the modal */
    $scope.form = {};
    
    /* List of Venues */
    $scope.venuesList = venuesList;
    
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
        $scope.editing.players = [ { email : '' } ];
    }
    
    /* Click event for the Add Email Input */
    $scope.buttonNewEmailField = function() {
        $scope.editing.players.push({ email : '' });
    };
    
        
    /* Click event for the Edit button */
    $scope.buttonEdit = function() {
        $scope.setMode('edit');
    };
    
    /* Click event for the Add New Team button */
    $scope.buttonNew = function() {
        if(!$scope.form.modalForm.$valid) {
            $scope.form.modalForm.$setDirty();
            $scope.alertProxy.error('Please select a home venue and name for your team.');
        } else {
            var players = new Array();
            for(var i = 0; i < $scope.editing.players.length; i++) {
                if($scope.editing.players[i].email.length > 0) {
                    players.push({ 'email' : $scope.editing.players[i].email });
                }
            }
            if($scope.automaticallyAddUserId) {
                players.push({ 'userId' : $scope.automaticallyAddUserId });
                
                
                AlertConfirmService.confirm('Are you really sure that you would like to leave your current team and join this new team?', 'Warning, Leaving Team!')
                .result.then(function () {
                    ApiRoutesGames.addTeam({ 
                        'name' : $scope.editing.name,  
                        'venueId' : $scope.editing.venue.id,  
                        'players' : players }).then(function(result) {

                        console.log(result);
                        $scope.alertProxy.success("Team '" + result.team.name + "'added");
                        $uibModalInstance.close(result);
                    }, function(error) {
                        console.log(error);
                        $scope.alertProxy.error(error);
                    });
                }, function (declined) {
                    $scope.alertProxy.info('No changes were saved. Close the window to remain in the same Team.');
                });
            } else {
                ApiRoutesGames.addTeam({ 
                    'name' : $scope.editing.name,  
                    'venueId' : $scope.editing.venue.id,  
                    'players' : players }).then(function(result) {

                    console.log(result);
                    $scope.alertProxy.success("Team '" + result.team.name + "'added");
                    $uibModalInstance.close(result);
                }, function(error) {
                    console.log(error);
                    $scope.alertProxy.error(error);
                });
            }
        }
    };
        
    /* Click event for the Save Team button */
    $scope.buttonSave = function() {
        
    };
        
    /* Click event for the Cancel button */
    $scope.buttonCancel = function() {
        if($scope.getMode() === 'edit') {
            $scope.setMode('view');
        } else {
            $uibModalInstance.dismiss(false);
        }
    };
        
    /* Click event for the Delete Team button */
    $scope.buttonDelete = function() {
        
    };
    
}]);