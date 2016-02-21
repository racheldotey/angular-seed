'use strict';

/* 
 * Service to Load UI Bootstrap Modals
 * Related to Trivia Game Functions
 * 
 * Includes modal controllers and provides and api to launch the modal.
 * https://angular-ui.github.io/bootstrap/#/modal
 */

angular.module('TriviaModalService', [
    'app.modal.trivia.editQuestion',
    'app.modal.trivia.editRound',
    'app.modal.trivia.editTeam'
])
.factory('TriviaModalService', ['$uibModal', function($uibModal) {
        
    var templatePath = 'app/modals/trivia/';
    
    var api = {};
    
    var defaultOptions = {
        size: 'md',
        backdrop: 'static'
    };
    
    api.openModal = function(apiOptions, passedOptions, passedResolve) {
        /* Get value of resolve */
        var apiResolve = apiOptions.resolve || {};
        var defaultResolve = defaultOptions.resolve || {};
        passedResolve = passedResolve || {};
        var combineResolve = angular.extend({}, defaultResolve, apiResolve, passedResolve);
        
        /* Combine options */
        var config = angular.extend({}, defaultOptions, apiOptions, passedOptions);
        
        /* Set resolve to the combine resolve */
        config.resolve = combineResolve;
        
        /* Return the uibModalInstance */
        return $uibModal.open(config);
    };
    
    /*
     * Open Add Trivia Team Modal
     * 
     * @return uibModalInstance
     */
    api.openAddTeam = function(variable) {
        return api.openModal({
            templateUrl: templatePath + 'editTeam/editTeam.html',
            controller: 'TriviaEditTeamModalCtrl',
            resolve: {
                ApiRoutesGames: 'ApiRoutesGames',
                editing: function(ApiRoutesGames) {
                    if(angular.isDefined(variable)) {
                        return (angular.isObject(variable)) ? variable : 
                                ApiRoutesGames.getRound(variable);
                    } else {
                        return {};
                    }
                }
            }
        });
    };
    
    /*
     * Open Add Trivia Player Modal
     * 
     * @return uibModalInstance
     */
    api.openAddPlayer = function(variable) {
        return api.openModal({
            templateUrl: templatePath + 'editTeam/editTeam.html',
            controller: 'TriviaEditTeamModalCtrl',
            resolve: {
                ApiRoutesGames: 'ApiRoutesGames',
                editing: function(ApiRoutesGames) {
                    if(angular.isDefined(variable)) {
                        return (angular.isObject(variable)) ? variable : 
                                ApiRoutesGames.getRound(variable);
                    } else {
                        return {};
                    }
                }
            }
        });
    };
    
    /*
     * Open Edit Trivia Game Round Modal
     * 
     * @return uibModalInstance
     */
    api.openEditRound = function(variable) {
        return api.openModal({
            templateUrl: templatePath + 'editRound/editRound.html',
            controller: 'TriviaEditRoundModalCtrl',
            resolve: {
                ApiRoutesGames: 'ApiRoutesGames',
                editing: function(ApiRoutesGames) {
                    if(angular.isDefined(variable)) {
                        return (angular.isObject(variable)) ? variable : 
                                ApiRoutesGames.getRound(variable);
                    } else {
                        return {};
                    }
                }
            }
        });
    };
    
    /*
     * Open Edit Trivia Game Round Question Modal
     * 
     * @return uibModalInstance
     */
    api.openEditQuestion = function(variable) {
        return api.openModal({
            templateUrl: templatePath + 'editRound/editRound.html',
            controller: 'TriviaEditRoundModalCtrl',
            resolve: {
                ApiRoutesGames: 'ApiRoutesGames',
                editing: function(ApiRoutesGames) {
                    if(angular.isDefined(variable)) {
                        return (angular.isObject(variable)) ? variable : 
                                ApiRoutesGames.getRound(variable);
                    } else {
                        return {};
                    }
                }
            }
        });
    };
    
    return api;
}]);