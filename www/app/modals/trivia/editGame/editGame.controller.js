'use strict';

/* @author  Rachel Carbone */

angular.module('app.modal.trivia.editGame', [])        
    .controller('TriviaEditGameModalCtrl', ['$scope', '$uibModalInstance', '$filter', 'AlertConfirmService', 'editing', 'venueList', 'TriviaGame', 'ApiRoutesGames',
    function($scope, $uibModalInstance, $filter, AlertConfirmService, editing, venueList, TriviaGame, ApiRoutesGames) {
    
    $scope.venueList = venueList;
    
    /* Used to restrict alert bars */
    $scope.alertProxy = {};
    
    /* Holds the add / edit form on the modal */
    $scope.form = {};
    
    $scope.generateGameName = function() {
        
    
    //Brickhouse Pizza - January 13, 2016 @ 7:00pm
    };
    
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
        $scope.saved = angular.copy(editing);
    } else {
        $scope.setMode('new');
        $scope.saved = {
            'scheduled' : moment(),
            'maxPoints' : false
        };
        
        // If its more than 5 min past the hour, set it to the next half hour
        // Set time to the next half hour
        var remainder = (30 - $scope.saved.scheduled.minute()) % 30;
        remainder = (remainder < -5) ? remainder + 30 : remainder;
        $scope.saved.scheduled = moment($scope.saved.scheduled).add(remainder, "minutes");
    }
    
    
    /* Item to display and edit */
    $scope.editing = angular.copy($scope.saved);
    
    /* Click event for the Add / New button */
    $scope.buttonNew = function() {
        ApiRoutesGames.addGameRoundQuestion({ 
            'gameId' : $scope.game.id, 
            'roundId' : $scope.game.round.roundId, 
            'question' : $scope.editing.question }).then(
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
    
    
    // Time Picker
    $scope.scheduler = {
        date : {
            opened: true,
            options: {
                formatYear: 'yy',
                maxDate: moment().add(1, 'year'),
                minDate: moment(),
                startingDay: 1
            }
        },
        time : {
            hstep: 1,
            mstep: 30,
            changed: function () {
                console.log('Time changed to: ' + $scope.editing.scheduled);
            }
        }
    };    
    
    
    
    
    
    
    
}]);