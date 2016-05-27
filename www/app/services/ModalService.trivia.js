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
    'app.modal.trivia.joinTeam',
    'app.modal.trivia.viewGameScoreboard'
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
     * Open Edit Trivia Game Modal
     * 
     * @return uibModalInstance
     */
    api.openViewGameScoreboard = function(gameId, roundNumber) {
        return api.openModal({
            templateUrl: templatePath + 'viewGameScoreboard/viewGameScoreboard.html',
            controller: 'TriviaViewGameScoreboardModalCtrl',
            resolve: {
                viewGame: function() {
                    return { 'id' : gameId, 'roundNumber' : roundNumber };
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
     * Open Join Trivia Team Modal
     * 
     * @return uibModalInstance
     */
    api.openJoinTeam = function(userId, currentTeam) {
        return api.openModal({
            templateUrl: templatePath + 'joinTeam/joinTeam.html',
            controller: 'TriviaJoinTeamModalCtrl',
            resolve: {
                ApiRoutesSimpleLists: 'ApiRoutesSimpleLists',
                userId: function() {
                    return userId;
                },
                currentTeam: function() {
                    return currentTeam || false;
                },
                teamsList: function(ApiRoutesSimpleLists) {
                    return ApiRoutesSimpleLists.simpleAllTeamsList();
                }
            }
        });
    };
    
    /*
     * Open Edit Trivia Team Modal
     * 
     * @return uibModalInstance
     */
    api.openEditTeam = function(team, addUserId, currentVenueId, currentGameId, currentMode) {
        return api.openModal({
            templateUrl: templatePath + 'editTeam/editTeam.html',
            controller: 'TriviaEditTeamModalCtrl',
            resolve: {
                $q: '$q',
                ApiRoutesGames: 'ApiRoutesGames',
                ApiRoutesSimpleLists: 'ApiRoutesSimpleLists',
                editing: function($q, ApiRoutesGames) {
                    return $q(function (resolve, reject) {
                             if (!team) {
                                return resolve({});
                            } else if (angular.isObject(team)) {
                                return resolve(team);
                            } else {
                                ApiRoutesGames.getTeam(team).then(function (result) {
                                    console.log(result);
                                    return resolve(result.team);
                                }, function (error) {
                                    console.log(error);
                                    return reject(error);
                                });
                            }
                    });
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
                },
                currentMode: function() {
                    return currentMode || false;
                }
                
            }
        });
    };
    
    /*
     * Open Edit Trivia Venue / Joint Modal
     * 
     * @return uibModalInstance
     */
    api.openEditVenue = function(venue) {
        return api.openModal({
            templateUrl: templatePath + 'editVenue/editVenue.html',
            controller: 'TriviaEditVenueModalCtrl',
            resolve: {
                editing: function() {
                    return (angular.isObject(venue)) ? venue : { };
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
    
    return api;
}]);