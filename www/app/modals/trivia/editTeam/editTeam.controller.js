'use strict';

/* @author  Rachel Carbone */

angular.module('app.modal.trivia.editTeam', [])        
    .controller('TriviaEditTeamModalCtrl', ['ApiRoutesGames', 'AlertConfirmService', '$scope', '$uibModalInstance', 'DataTableHelper', 'DTColumnBuilder', 
        'editing', 'venuesList', 'addUserId', 'currentVenueId', 'currentGameId', 'currentMode',
    function(ApiRoutesGames, AlertConfirmService, $scope, $uibModalInstance, DataTableHelper, DTColumnBuilder, 
        editing, venuesList, addUserId, currentVenueId, currentGameId, currentMode) {
    
    $scope.automaticallyAddUserId = addUserId || false;
    
    /* Used to restrict alert bars */
    $scope.alertProxy = {};
    
    /* Holds the add / edit form on the modal */
    $scope.form = {};
    
    /* List of Venues */
    $scope.venuesList = venuesList;
    
    $scope.currentGameId = currentGameId;
    
    /* Save for resetting purposes */
    $scope.saved = (angular.isDefined(editing.id)) ? angular.copy(editing) : { };
    $scope.saved.venue = {};
    if(currentVenueId) {
        for(var v = 0; v < venuesList.length; v++) {
            if(venuesList[v].id == currentVenueId) {
                $scope.saved.venue = venuesList[v];
                break;
            }
        }
    }
    
    /* Item to display and edit */
    $scope.editing = angular.copy($scope.saved);
    
    /* Modal Mode */    
    $scope.setMode = function(type) {
        $scope.viewMode = false;
        $scope.newMode = false;
        $scope.editMode = false;
        $scope.logMode = false;
        
        switch(type) {
            case 'new':
                $scope.newMode = true;
                break;
            case 'edit':
                $scope.editMode = true;
                break;
            case 'log':
                $scope.logMode = true;
                break;
            case 'view':
            default:
                $scope.viewMode = true;
                break;
        }
    };
    
    $scope.getMode = function() {
        if($scope.viewMode) {
            return 'view';
        } else if($scope.logMode) {
            return 'log';
        }  else if($scope.editMode) {
            return 'edit';
        } else {
            return 'new';
        }
    };
    
    /* Save for resetting purposes */
    if(angular.isDefined(editing.id) && editing.id) {
        if(currentMode && (currentMode === 'view' || currentMode === 'edit' || currentMode === 'log')) {
            $scope.setMode(currentMode);
        } else {
            $scope.setMode('view');
        }
        
        $scope.dtTeamCheckins = DataTableHelper.getDTStructure($scope, 'adminTeamCheckinsList', editing.id);
        $scope.dtTeamCheckins.options.withOption('order', [1, 'desc'])
        .withDOM('<"row"<"col-sm-12 col-md-12"fr><"col-sm-12 add-space"t><"col-sm-6"l><"col-sm-6 text-right"i><"col-sm-12 text-center"p>>');
        $scope.dtTeamCheckins.columns = [
            DTColumnBuilder.newColumn('game').withTitle('Game'),
            DTColumnBuilder.newColumn('venue').withTitle('Venue'),
            DTColumnBuilder.newColumn('status').withTitle('Status'),
            DTColumnBuilder.newColumn('createdBy').withTitle('Created By'),
            DTColumnBuilder.newColumn('created').withTitle('Created').renderWith(function (data, type, full, meta) {
                return moment(data, 'YYYY-MM-DD HH:mm:ss').format('M/D/YYYY h:mm a');
            })
        ];
    } else {
        $scope.setMode('new');
        $scope.editing.name = "";
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
        
    /* Click event for the View Checkin Log button */
    $scope.buttonViewCheckinLog = function() {
        $scope.setMode('log');
    };
    
    /* Click event for the Add New Team button */
    $scope.buttonNew = function(currentGameId) {
        var emailError = false;
        
        var players = new Array();
        for(var i = 0; i < $scope.editing.players.length; i++) {
            if($scope.editing.players[i].email === '') {
                // Ignore blank lines
            } else if (angular.isUndefined($scope.editing.players[i].email)) {
                emailError = true;
                break;
            } else {
                players.push({ 'email' : $scope.editing.players[i].email });
            }
        }
            
        if(emailError) {
            $scope.form.modalForm.$setDirty();
            $scope.alertProxy.error('One or more emails entered are invalid. Please confirm the emails for players you would like to invite.');
        } else if(!$scope.form.modalForm.$valid) {
            $scope.form.modalForm.$setDirty();
            $scope.alertProxy.error('Please name your team and select a home venue.');
        } else {
            if($scope.automaticallyAddUserId) {
                players.push({ 'userId' : $scope.automaticallyAddUserId });
                
                
                AlertConfirmService.confirm('Are you really sure that you would like to leave your current team and join this new team?', 'Warning, Leaving Team!')
                .result.then(function () {
                    
                var data ={ 
                    'name' : $scope.editing.name,  
                    'homeVenueId' : $scope.editing.venue.id || $scope.editing.venue.value.id,
                    'players' : players };
                
                if(currentGameId) {
                    data.gameId = currentGameId;
                }
                
                ApiRoutesGames.addTeam(data).then(function(result) {
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
                var data ={ 
                    'name' : $scope.editing.name,  
                    'homeVenueId' : $scope.editing.venue.id || $scope.editing.venue.value.id,
                    'players' : players };
                
                if(currentGameId) {
                    data.gameId = currentGameId;
                }
                
                ApiRoutesGames.addTeam(data).then(function(result) {
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
        
        var emailError = false;
        
        var players = new Array();
        for(var i = 0; i < $scope.editing.players.length; i++) {
            if($scope.editing.players[i].email === '') {
                // Ignore blank lines
            } else if (angular.isUndefined($scope.editing.players[i].email)) {
                emailError = true;
                break;
            } else {
                players.push({ 'email' : $scope.editing.players[i].email });
            }
        }
            
        if(emailError) {
            $scope.form.modalForm.$setDirty();
            $scope.alertProxy.error('One or more emails entered are invalid. Please confirm the emails for players you would like to invite.');
        } else if(!$scope.form.modalForm.$valid) {
            $scope.form.modalForm.$setDirty();
            $scope.alertProxy.error('Please name your team and select a home venue.');
        } else {
            ApiRoutesGames.saveTeam({ 
                'id' : $scope.editing.id,  
                'name' : $scope.editing.name,  
                'homeVenueId' : $scope.editing.venue.id || $scope.editing.venue.value.id,
                'players' : players }).then(function(result) {

                console.log(result);
                $scope.alertProxy.success("Team '" + result.team.name + "'saved");
                $uibModalInstance.close(result);
            }, function(error) {
                console.log(error);
                $scope.alertProxy.error(error);
            });
        }
    };
        
    /* Click event for the Cancel button */
    $scope.buttonCancel = function() {
        if($scope.getMode() === 'edit' || $scope.getMode() === 'log') {
            $scope.setMode('view');
        } else {
            $uibModalInstance.dismiss(false);
        }
    };
    
    $scope.buttonDone = function() {
        $uibModalInstance.dismiss(false);
    };
        
    /* Click event for the Delete Team button */
    $scope.buttonDelete = function() {
        
    };
    
}]);