'use strict';

/* @author  Rachel Carbone */

angular.module('app.modal.trivia.editRound', [])        
    .controller('TriviaEditRoundModalCtrl', ['$scope', '$uibModalInstance', '$filter', 'AlertConfirmService', 'editing', 'TriviaScoreboard', 'ApiRoutesGames',
    function($scope, $uibModalInstance, $filter, AlertConfirmService, editing, TriviaScoreboard, ApiRoutesGames) {        
    
    $scope.game = TriviaScoreboard.getGame();
    
    /* Used to restrict alert bars */
    $scope.alertProxy = {};
    
    /* Holds the add / edit form on the modal */
    $scope.form = {};
    
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
    
    if(angular.isDefined(editing.roundId)) {
        $scope.setMode('view');
        /* Save for resetting purposes */
        $scope.saved = angular.copy(editing);
        $scope.saved.maxPoints = parseFloat(editing.maxPoints);
        $scope.saved.defaultQuestionPoints = parseFloat(editing.defaultQuestionPoints);
    } else {
        $scope.setMode('new');
        $scope.saved = { defaultQuestionPoints : 10 };
    }
    
    /* Item to display and edit */
    $scope.editing = angular.copy($scope.saved);
    
    /* Click event for the Add / New button */
    $scope.buttonNew = function() {
        TriviaScoreboard.newRound({ 
            'gameId' : $scope.game.id, 
            'name' : $scope.editing.name,
            'defaultQuestionPoints' : $scope.editing.defaultQuestionPoints
        }).then(
            function (result) {
                $uibModalInstance.close(result);
            }, function (error) {
                $scope.alertProxy.error(error);
            });
    };
    
    /* Click event for the Save button */
    $scope.buttonSave = function() {
    };
    
    /* Click event for the Delete button */
    $scope.buttonDelete = function() {
        
        AlertConfirmService.confirm('Are you sure you want to disable this round?.')
            .result.then(function () {
                ApiRoutesGames.deleteRound($scope.editing.id).then(
                    function (result) {
                        $uibModalInstance.close(result);
                    }, function (error) {
                        $scope.alertProxy.error(error);
                    });
            });
    };
        
    /* Click event for the Cancel button */
    $scope.buttonCancel = function() {
        var mode = $scope.getMode();
        
        switch(mode) {
            case 'edit':
                $scope.setMode('view');
                break;
            case 'new':
            case 'view':
            default:
                $uibModalInstance.dismiss(false);
                break;
        };
    };
    
    /* Click event for the Edit button*/
    $scope.buttonEdit = function() {
        $scope.setMode('edit');
    };
    
}]);