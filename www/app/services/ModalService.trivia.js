'use strict';

/* 
 * Service to Load UI Bootstrap Modals
 * Related to Trivia Game Functions
 * 
 * Includes modal controllers and provides and api to launch the modal.
 * https://angular-ui.github.io/bootstrap/#/modal
 */  

angular.module('TriviaModalService', [
    'app.modal.trivia.editGame',
    'app.modal.trivia.editQuestion',
    'app.modal.trivia.editRound',
    'app.modal.trivia.editTeam',
    'app.modal.trivia.invitePlayer'
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
     * Open Edit Trivia Game Modal
     * 
     * @return uibModalInstance
     */
    api.openEditGame = function(gameId) {
        return api.openModal({
            templateUrl: templatePath + 'editGame/editGame.html',
            controller: 'TriviaEditGameModalCtrl',
            resolve: {
                ApiRoutesGames: 'ApiRoutesGames',
                ApiRoutesSimpleLists: 'ApiRoutesSimpleLists',
                editing: function(ApiRoutesGames) {
                    return { 'gameId' : gameId };
                },
                venueList: function(ApiRoutesSimpleLists) {
                    return ApiRoutesSimpleLists.simpleVenuesList();
                }
            }
        });
    };
    
    /*
     * Open Add Trivia Team Modal
     * 
     * @return uibModalInstance
     */
    api.openAddTeam = function(gameId) {
        return api.openModal({
            templateUrl: templatePath + 'editTeam/editTeam.html',
            controller: 'TriviaEditTeamModalCtrl',
            resolve: {
                ApiRoutesGames: 'ApiRoutesGames',
                ApiRoutesSimpleLists: 'ApiRoutesSimpleLists',
                editing: function(ApiRoutesGames) {
                    return { 'gameId' : gameId };
                },
                gameList: function(ApiRoutesSimpleLists) {
                    return ApiRoutesSimpleLists.simpleGamesList();
                }
            }
        });
    };
    
    /*
     * Open Add Trivia Player Modal
     * 
     * @return uibModalInstance
     */
    api.openAddPlayer = function(gameId) {
        return api.openModal({
            templateUrl: templatePath + 'editTeam/editTeam.html',
            controller: 'TriviaEditTeamModalCtrl',
            resolve: {
                ApiRoutesGames: 'ApiRoutesGames',
                ApiRoutesSimpleLists: 'ApiRoutesSimpleLists',
                editing: function(ApiRoutesGames) {
                    return { 'gameId' : gameId };
                },
                gameList: function(ApiRoutesSimpleLists) {
                    return ApiRoutesSimpleLists.simpleGamesList();
                }
            }
        });
    };
    
    /*
     * Open Edit Trivia Game Round Modal
     * 
     * @return uibModalInstance
     */
    api.openEditRound = function(gameId, roundId) {
        return api.openModal({
            templateUrl: templatePath + 'editRound/editRound.html',
            controller: 'TriviaEditRoundModalCtrl',
            resolve: {
                ApiRoutesGames: 'ApiRoutesGames',
                ApiRoutesSimpleLists: 'ApiRoutesSimpleLists',
                editing: function(ApiRoutesGames) {
                    return { 'gameId' : gameId };
                },
                gameList: function(ApiRoutesSimpleLists) {
                    return ApiRoutesSimpleLists.simpleGamesList();
                }
            }
        });
    };
    
    /*
     * Open Edit Trivia Game Round Question Modal
     * 
     * @return uibModalInstance
     */
    api.openEditQuestion = function(question) {
        return api.openModal({
            templateUrl: templatePath + 'editQuestion/editQuestion.html',
            controller: 'TriviaEditQuestionModalCtrl',
            resolve: {
                editing: function() {
                    return (angular.isDefined(question)) ? question : {};
                }
            }
        });
    };
    
    /*
     * Open Invite Player to Trivia Joint Modal
     * 
     * @return uibModalInstance
     */
    api.openInviteFriend = function(currentUser) {
        return api.openModal({
            templateUrl: templatePath + 'invitePlayer/invitePlayer.html',
            controller: 'TriviaInvitePlayerModalCtrl',
            resolve: {
                editing: function() {
                    return (angular.isDefined(currentUser)) ? currentUser : {};
                }
            }
        });
    };
    
    return api;
}]);