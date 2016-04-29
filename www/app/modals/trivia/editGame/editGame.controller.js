'use strict';

/* @author  Rachel Carbone */

angular.module('app.modal.trivia.editGame', [])        
    .controller('TriviaEditGameModalCtrl', ['$scope', '$uibModalInstance', 'AlertConfirmService', 'editing', 'venueList', 'ApiRoutesGames', 'UserSession',
    function($scope, $uibModalInstance, AlertConfirmService, editing, venueList, ApiRoutesGames, UserSession) {
    
    $scope.venueList = venueList;
    
    /* Used to restrict alert bars */
    $scope.gameAlerts = {};
    
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
            'scheduledDate' : new Date(),
            'defaultQuestionPoints' : 10
        };
        
        // If its more than 5 min past the hour, set it to the next half hour
        // Set time to the next half hour
        var now = moment();
        var remainder = (30 - now.minute()) % 30;
        remainder = (remainder < -5) ? remainder + 30 : remainder;
        $scope.saved.scheduledTime = moment(now).add(remainder, "minutes");
    }
    
    $scope.updateGameName = function() {
        // Brickhouse - 2016-03-08 5:00pm
        var date = moment($scope.editing.scheduledDate).format('dddd MMMM D, YYYY');
        var time = moment($scope.editing.scheduledTime).format('h:mm a');
        var venue = (angular.isDefined($scope.editing.venue) && angular.isDefined($scope.editing.venue.name)) ? $scope.editing.venue.name : '';
        $scope.editing.gameName = venue + ' - ' + date + ' ' + time ;
    };
    $scope.$watch("editing.scheduledDate", function(newValue, oldValue) {
        $scope.updateGameName();
    });
    
    
    /* Item to display and edit */
    $scope.editing = angular.copy($scope.saved);
    
    /* Click event for the Add / New button */
    $scope.buttonNew = function() {
        if(!$scope.form.game.$valid) {
            $scope.form.game.$setDirty();
            $scope.gameAlerts.error('Please fill in all fields for your game.');
        } else {
            // Combine the date and time
            var scheduled = moment($scope.editing.scheduledDate);
            scheduled.hour(moment($scope.editing.scheduledTime).hour());
            scheduled.minute(moment($scope.editing.scheduledTime).minute());
            scheduled.second(0);

            ApiRoutesGames.addGame({ 
                'scheduled' : scheduled.format("YYYY-MM-DD HH:mm:ss"), 
                'venueId' : $scope.editing.venue.id , 
                'hostId' : UserSession.id(),
                'name' : $scope.editing.gameName,
                'defaultQuestionPoints' : $scope.editing.defaultQuestionPoints}).then(
            function (result) {
                $uibModalInstance.close(result.game);
            }, function (error) {
                $scope.gameAlerts.error(error);
            });
        }
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
            opened: false,
            options: {
                formatYear: 'yy',
                maxDate: moment().add(6, "months"),
                minDate: new Date(),
                startingDay: 1,
                showWeeks: false
            }
        },
        time : {
            hstep: 1,
            mstep: 30,
            changed: function () {
                $scope.updateGameName();
            }
        }
    };    
    
}]);