'use strict';

/* @author  Rachel Carbone */

angular.module('app.modal.trivia.editQuestion', [])        
    .controller('TriviaEditQuestionModalCtrl', ['$scope', '$uibModalInstance', '$filter', 'AlertConfirmService', 'editing', 'TriviaGame', 'ApiRoutesGames',
    function($scope, $uibModalInstance, $filter, AlertConfirmService, editing, TriviaGame, ApiRoutesGames) {
    
    $scope.game = TriviaGame.getGame();
    
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
    
    if(angular.isDefined(editing.id)) {
        $scope.setMode('view');
    } else {
        $scope.setMode('new');
    }
    
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
    $scope.saved = (angular.isDefined(editing.id)) ? angular.copy(editing) : {};
    
    /* Item to display and edit */
    $scope.editing = angular.copy($scope.saved);
    
    if($scope.getMode('new') === 'new') {
        $scope.editing.roundNumber = ($scope.game.round.questions.length + 1);
        $scope.editing.question = "Question #" + $scope.editing.roundNumber;
        $scope.editing.maxPoints = (angular.isDefined($scope.game.round.defaultQuestionPoints)) ? 
            parseInt($scope.game.round.defaultQuestionPoints) : 2;
    }
    
    /* Click event for the Add / New button */
    $scope.buttonNew = function() {
        var newQuestion = {
            'gameId': $scope.game.id,
            'roundId': $scope.game.round.roundId,
            'maxPoints': $scope.editing.maxPoints,
            'roundNumber' : $scope.editing.roundNumber,
            'question': $scope.editing.question
        };
        ApiRoutesGames.addGameRoundQuestion(newQuestion).then(
            function (result) {
                $uibModalInstance.close(result.game);
            }, function (error) {
                $scope.alertProxy.error(error);
            });
    };
    
    /* Click event for the Save button */
    $scope.buttonSave = function() {
    };
    
    /* Click event for the Delete button */
    $scope.buttonDelete = function() {
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