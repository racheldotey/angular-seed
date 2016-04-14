'use strict';

/*
 * State Declarations: Host / Authenticated
 * 
 * Set up the states for logged in user routes, such as the 
 * user profile page and other authenticated states.
 * Ueses ui-roter's $stateProvider.
 * 
 * Set each state's title (used in the config for the html <title>).
 * 
 * Set auth access for each state.
 */

var app = angular.module('app.router.host', [
    'rcAuth.constants',
    'app.host'
]);
app.config(['$stateProvider', '$urlRouterProvider', 'USER_ROLES', function ($stateProvider, $urlRouterProvider, USER_ROLES) {

        /*  Abstract Member (Authenticated) Route */
        $stateProvider.state('app.host', {
            url: '/host',
            abstract: true,
            data: {authorizedRoles: USER_ROLES.host},
            views: {
                'header@app.host': {
                    templateUrl: 'app/views/host/hostHeader/hostHeader.html',
                    controller: 'HostHeaderCtrl'
                },
                'layout@': {
                    templateUrl: 'app/views/host/hostLayout/hostLayout.html',
                    controller: 'HostLayoutCtrl'
                },
                'footer@app.host': {
                    templateUrl: 'app/views/host/hostFooter/hostFooter.html',
                    controller: 'HostFooterCtrl'
                }
            }
        });

        $stateProvider.state('app.host.dashboard', {
            bodyClass: 'host dashboard',
            title: 'Host Dashboard',
            url: '/dashboard',
            views: {
                'content@app.host': {
                    templateUrl: 'app/views/host/dashboard/dashboard.html',
                    controller: 'HostDashboardCtrl'
                }
            },
            resolve: {
                $q: '$q',
                ApiRoutesGames: 'ApiRoutesGames',
                HostData: function($q, ApiRoutesGames, initUser) {
                    return $q(function (resolve, reject) {
                        ApiRoutesGames.getGameHost(initUser.id).then(function (result) {
                            console.log('HOST Resolve Success: ' + result);
                            resolve(result);
                        }, function (error) {
                            console.log('HOST Resolve Error: ' + error);
                            resolve({});
                        });
                    });
                }
            }
        });
        
        // Redirect /host to the dashboard
        $urlRouterProvider.when('/host', '/host/dashboard');
        $urlRouterProvider.when('/host/', '/host/dashboard');

        $stateProvider.state('app.host.profile', {
            bodyClass: 'host profile',
            title: 'User Profile',
            url: '/profile',
            views: {
                'content@app.host': {
                    templateUrl: 'app/views/member/profile/profile.html',
                    controller: 'MemberProfileCtrl'
                }
            }
        });
        
        $stateProvider.state('app.host.game', {
            bodyClass: 'host scoreboard',
            title: 'Game Host Scoreboard',
            url: '/game-scoreboard/:gameId/:roundNumber',
            views: {
                'content@app.host': {
                    templateUrl: 'app/views/host/scoreboard/scoreboard.html',
                    controller: 'HostScoreboardDashboardCtrl'
                }
            },
            resolve: {
                $q: '$q',
                $rootScope: '$rootScope', 
                $state: '$state',
                TriviaScoreboard: 'TriviaScoreboard',
                AlertConfirmService: 'AlertConfirmService',
                currentGame: function(AlertConfirmService, TriviaScoreboard, $stateParams, $rootScope, $state, $q) {
                    $stateParams.roundNumber = (parseInt($stateParams.roundNumber)) ? $stateParams.roundNumber : 1;
                    return $q(function (resolve, reject) {
                        TriviaScoreboard.loadGame($stateParams.gameId, $stateParams.roundNumber).then(function (result) {
                            if(!result && $stateParams.roundNumber > 1) {
                                $rootScope.$evalAsync(function () {
                                    $state.go('app.host.game', {gameId: $stateParams.gameId, roundNumber: 1 });
                                });
                            } else if (!result) {
                                AlertConfirmService.alert('A game with this ID could not be found. Confirm your URL and try again.', 'Game could not be loaded.')
                                    .result.then(function () {
                                        $rootScope.$evalAsync(function () {
                                            $state.go('app.host.dashboard');
                                        });
                                    }, function (declined) {
                                        $rootScope.$evalAsync(function () {
                                            $state.go('app.host.dashboard');
                                        });
                                    });
                            } else {
                                resolve(result);
                            }
                        }, function (error) {
                            $rootScope.$evalAsync(function () {
                                $state.go('app.host.dashboard');
                            });
                            console.log(error);
                            reject(false);
                        });
                    }); 
                }
            }
        });
        
        $stateProvider.state('app.host.gameRedirect', {
            url: '/game-scoreboard/:gameId',
            resolve: {
                $state: '$state',
                currentGame: function($stateParams, $state) {
                    $state.go('app.host.game', {gameId: $stateParams.gameId, roundNumber: 1 });
                }
            }
        });
        
        
    }]);