'use strict';

/* 
 * Service to Load UI Bootstrap Modals
 * Related to Trivia Game Functions
 * 
 * Includes modal controllers and provides and api to launch the modal.
 * https://angular-ui.github.io/bootstrap/#/modal
 */  

angular.module('TriviaModalService', [
    'app.modal.trivia.addTeamToGame',
    'app.modal.trivia.editGame',
    'app.modal.trivia.editQuestion',
    'app.modal.trivia.editRound',
    'app.modal.trivia.editTeam',
    'app.modal.trivia.editVenue',
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
                    return (angular.isObject(gameId)) ? gameId : { 'gameId' : gameId };
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
    api.openAddTeam = function(game, team) {
        return api.openModal({
            templateUrl: templatePath + 'addTeamToGame/addTeamToGame.html',
            controller: 'TriviaAddTeamToGameModalCtrl',
            resolve: {
                ApiRoutesSimpleLists: 'ApiRoutesSimpleLists',
                game: function() {
                    return game;
                },
                team: function() {
                    return team;
                },
                teamsList: function(ApiRoutesSimpleLists) {
                    return ApiRoutesSimpleLists.simpleTeamsList();
                },
                gamesList: function(ApiRoutesSimpleLists) {
                    return ApiRoutesSimpleLists.simpleActiveGamesList();
                }
            }
        });
    };
    
    /*
     * Open Edit Trivia Team Modal
     * 
     * @return uibModalInstance
     */
    api.openEditTeam = function(team, addUserId, currentVenueId, currentGameId) {
        return api.openModal({
            templateUrl: templatePath + 'editTeam/editTeam.html',
            controller: 'TriviaEditTeamModalCtrl',
            resolve: {
                ApiRoutesGames: 'ApiRoutesGames',
                ApiRoutesSimpleLists: 'ApiRoutesSimpleLists',
                editing: function(ApiRoutesGames) {
                    return (angular.isObject(team)) ? team : { };
                },
                addUserId: function() {
                    return addUserId || false;
                },
                currentVenueId: function() {
                    return currentVenueId || false;
                },
                currentGameId: function() {
                    return currentGameId || false;
                },
                venuesList: function(ApiRoutesSimpleLists) {
                    return ApiRoutesSimpleLists.simpleVenuesList();
                }
            }
        });
    };
    
    /*
     * Open Edit Trivia Venue / Joint Modal
     * 
     * @return uibModalInstance
     */
    api.openEditVenue = function(venueId) {
        return api.openModal({
            templateUrl: templatePath + 'editVenue/editVenue.html',
            controller: 'TriviaEditVenueModalCtrl',
            resolve: {
                ApiRoutesGames: 'ApiRoutesGames',
                ApiRoutesSimpleLists: 'ApiRoutesSimpleLists',
                editing: function(ApiRoutesGames) {
                    return { 'venueId' : venueId };
                }
            }
        });
    };
    
    /*
     * Open Edit Trivia Game Round Modal
     * 
     * @return uibModalInstance
     */
    api.openEditRound = function(round) {
        return api.openModal({
            templateUrl: templatePath + 'editRound/editRound.html',
            controller: 'TriviaEditRoundModalCtrl',
            resolve: {
                editing: function() {
                    return (angular.isDefined(round)) ? round : {};
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
    api.openInviteSiteSignup = function() {
        return api.openModal({
            templateUrl: templatePath + 'invitePlayer/invitePlayer.html',
            controller: 'TriviaInvitePlayerModalCtrl',
            resolve: {
                InvitingPlayer: function() {
                    return false;
                }
            }
        });
    };
    
    /*
     * Open Invite Player to Team to Trivia Joint Modal
     * 
     * @return uibModalInstance
     */
    api.openInviteToTeam = function() {
        return api.openModal({
            templateUrl: templatePath + 'invitePlayer/invitePlayer.html',
            controller: 'TriviaInvitePlayerModalCtrl',
            resolve: {
                AuthService: 'AuthService',
                InvitingPlayer: function(AuthService) {
                    return AuthService.getUser();
                }
            }
        });
    };
    
    return api;
}]);