'use strict';

/*
 * State Declarations: Member / Authenticated
 * 
 * Set up the states for logged in user routes, such as the 
 * user profile page and other authenticated states.
 * Ueses ui-roter's $stateProvider.
 * 
 * Set each state's title (used in the config for the html <title>).
 * 
 * Set auth access for each state.
 */

var app = angular.module('app.router.member', [
    'rcAuth.constants',
    'app.member'
]);
app.config(['$stateProvider', 'USER_ROLES', function ($stateProvider, USER_ROLES) {

        /*  Abstract Member (Authenticated) Route */
        $stateProvider.state('app.member', {
            url: '',
            abstract: true,
            data: {authorizedRoles: USER_ROLES.user},
            views: {
                'header@app.member': {
                    templateUrl: 'app/views/member/memberHeader/memberHeader.html',
                    controller: 'MemberHeaderCtrl'
                },
                'layout@': {
                    templateUrl: 'app/views/member/memberLayout/memberLayout.html',
                    controller: 'MemberLayoutCtrl'
                },
                'footer@app.member': {
                    templateUrl: 'app/views/member/memberFooter/memberFooter.html',
                    controller: 'MemberFooterCtrl'
                }
            }
        });

        $stateProvider.state('app.member.dashboard', {
            bodyClass: 'member dashboard',
            title: 'Member Dashboard',
            url: '/dashboard',
            views: {
                'content@app.member': {
                    templateUrl: 'app/views/member/dashboard/dashboard.html',
                    controller: 'MemberDashboardCtrl'
                }
            }
        });

        $stateProvider.state('app.member.profile', {
            bodyClass: 'member profile',
            title: 'User Profile',
            url: '/profile',
            views: {
                'content@app.member': {
                    templateUrl: 'app/views/member/profile/profile.html',
                    controller: 'MemberProfileCtrl'
                }
            }
        });

        $stateProvider.state('app.member.settings', {
            bodyClass: 'member settings',
            title: 'User Settings',
            url: '/settings',
            views: {
                'content@app.member': {
                    templateUrl: 'app/views/member/settings/settings.html',
                    controller: 'MemberSettingsCtrl'
                }
            }
        });
        
        $stateProvider.state('app.member.game', {
            bodyClass: 'member scoreboard',
            title: 'Game Scoreboard',
            url: '/game-scoreboard/:gameId/:roundNumber',
            views: {
                'content@app.member': {
                    templateUrl: 'app/views/member/scoreboard/scoreboard.html',
                    controller: 'MemberScoreboardDashboardCtrl'
                }
            },
            resolve: {
                $q: '$q',
                $rootScope: '$rootScope', 
                $state: '$state',
                TriviaGame: 'TriviaGame',
                currentGame: function(initUser, TriviaGame, $stateParams, $rootScope, $state, $q) {
                    $stateParams.roundNumber = (parseInt($stateParams.roundNumber)) ? $stateParams.roundNumber : 1;
                    return $q(function (resolve, reject) {
                        TriviaGame.loadGame($stateParams.gameId, $stateParams.roundNumber).then(function (result) {
                            resolve(result);
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
        
        $stateProvider.state('app.member.gameRedirect', {
            url: '/game-scoreboard/:gameId',
            resolve: {
                $state: '$state',
                currentGame: function($stateParams, $state) {
                    $state.go('app.member.game', {gameId: $stateParams.gameId, roundNumber: 1 });
                }
            }
        });
        
    }]);