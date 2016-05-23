'use strict';

/* @author  Rachel Carbone */

angular.module('app.modal.trivia.addTeamToGame', [])        
    .controller('TriviaAddTeamToGameModalCtrl', ['$scope', '$uibModalInstance', 'AlertConfirmService', 'TriviaScoreboard', 'team', 'game', 'teamsList', 'gamesList',
    function($scope, $uibModalInstance, AlertConfirmService, TriviaScoreboard, team, game, teamsList, gamesList) {  
    
    $scope.team = team;
    $scope.game = game;
    $scope.teamsList = teamsList;
    $scope.gamesList = gamesList;
    
    $scope.addTeam = {};
    if(team) {
        for(var t = 0; t < teamsList.length; t++) {
            if(parseInt(teamsList[t].id) === parseInt(team.id)) {
                $scope.addTeam = teamsList[t];
                t = teamsList.length;
                break;
            }
        }
    }
    
    $scope.toGame = {};
    if(game) {
        for(var g = 0; g < gamesList.length; g++) {
            if(parseInt(gamesList[g].id) === parseInt(game.id)) {
                $scope.toGame = gamesList[g];
                g = gamesList.length;
                break;
            }
        }
    }
    
    /* Used to restrict alert bars */
    $scope.alertProxy = {};
    
    /* Holds the add / edit form on the modal */
    $scope.form = {};    
    
    /* Click event for the Add Team button */
    $scope.buttonAddTeam = function() {
        if(!angular.isDefined($scope.addTeam.value.id) &&
            !angular.isDefined($scope.toGame.value.id)) {
            $scope.form.addTeam.$setDirty();
            $scope.alertProxy.error('Invalid checkin. A team and game are required.');
        } else if(!angular.isDefined($scope.addTeam.value.id)) {
            $scope.form.addTeam.$setDirty();
            $scope.alertProxy.error('Please select a team to sign into the game.');
        } else if(!angular.isDefined($scope.toGame.value.id)) {
            $scope.form.addTeam.$setDirty();
            $scope.alertProxy.error('Please select game to check the team into.');
        } else {
            
            AlertConfirmService.confirm('<p>Would like to check team<br/><strong>' + $scope.addTeam.value.label + '</strong><br/><br/>into the game<br/><strong>' + $scope.toGame.value.label + '</strong>?</p><p>The team will be locked in for the duration of the game after it starts.</p>', 'Checking Into Game')
                .result.then(function(results) { 
                    TriviaScoreboard.addTeamToGame($scope.addTeam.value.id, $scope.toGame.value.id).then(
                            function (result) {
                                $uibModalInstance.close(result);
                            }, function (error) {
                        $scope.alertProxy.error(error);
                    });
                }, function(declined) {

                });
        }
    };
        
    /* Click event for the Cancel button */
    $scope.buttonCancel = function() {
        $uibModalInstance.dismiss(false);
    };
        
}]);