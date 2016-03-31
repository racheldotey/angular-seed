'use strict';

/* @author  Rachel Carbone */

angular.module('app.modal.trivia.editQuestion', [])        
    .controller('TriviaEditQuestionModalCtrl', ['$scope', '$uibModalInstance', '$filter', 'AlertConfirmService', 'editing', 'TriviaScoreboard', 'ApiRoutesGames',
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
    
    if(angular.isDefined(editing.questionId)) {
        $scope.setMode('view');
        /* Save for resetting purposes */
        $scope.saved = angular.copy(editing);
        $scope.saved.maxPoints = parseFloat(editing.maxPoints);
    } else {
        $scope.setMode('new');
        $scope.saved = {};
    }
    
    /* Item to display and edit */
    $scope.editing = angular.copy($scope.saved);
    
    if($scope.getMode('new') === 'new') {
        $scope.editing.questionNumber = ($scope.game.round.questions.length + 1);
        $scope.editing.question = "";
        $scope.editing.maxPoints = (angular.isDefined($scope.game.round.defaultQuestionPoints) && $scope.game.round.defaultQuestionPoints > 0) ? 
            parseFloat($scope.game.round.defaultQuestionPoints) : 5;
    }
    
    /* Click event for the Add / New button */
    $scope.buttonNew = function() {
        var newQuestion = {
            'gameId': $scope.game.id,
            'roundId': $scope.game.round.roundId,
            'maxPoints': $scope.editing.maxPoints,
            'questionNumber' : $scope.editing.questionNumber,
            'question': $scope.editing.question,
            'wager': $scope.editing.wager
        };
        TriviaScoreboard.newQuestion(newQuestion).then(
            function (result) {
                $uibModalInstance.close(result);
            }, function (error) {
                $scope.alertProxy.error(error);
            });
    };
    
    /* Click event for the Save button */
    $scope.buttonSave = function() {
        var question = {
            'gameId': $scope.game.id,
            'roundId': $scope.game.round.roundId,
            'questionId': $scope.editing.questionId,
            'maxPoints': $scope.editing.maxPoints,
            'question': $scope.editing.question,
            'wager': $scope.editing.wager
        };
        TriviaScoreboard.editQuestion(question).then(
            function (result) {
                $uibModalInstance.close(result);
            }, function (error) {
                $scope.alertProxy.error(error);
            });
    };
    
    /* Click event for the Delete button */
    $scope.buttonDelete = function() {
        var question = {
            'gameId': $scope.game.id,
            'roundId': $scope.game.round.roundId,
            'questionId': $scope.editing.questionId
        };
        TriviaScoreboard.deleteQuestion(question).then(
            function (result) {
                $uibModalInstance.close(result);
            }, function (error) {
                $scope.alertProxy.error(error);
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