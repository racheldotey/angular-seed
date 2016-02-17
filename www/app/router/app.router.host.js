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
app.config(['$stateProvider', 'USER_ROLES', function ($stateProvider, USER_ROLES) {

        /*  Abstract Member (Authenticated) Route */
        $stateProvider.state('app.host', {
            url: '/host',
            abstract: true,
            data: {authorizedRoles: USER_ROLES.user},
            views: {
                'header@app.host': {
                    templateUrl: 'app/views/member/memberHeader/memberHeader.html',
                    controller: 'MemberHeaderCtrl'
                },
                'layout@': {
                    templateUrl: 'app/views/member/memberLayout/memberLayout.html',
                    controller: 'MemberLayoutCtrl'
                },
                'footer@app.host': {
                    templateUrl: 'app/views/member/memberFooter/memberFooter.html',
                    controller: 'MemberFooterCtrl'
                }
            }
        });

        $stateProvider.state('app.host.dashboard', {
            bodyClass: 'host dashboard',
            title: 'Host Dashboard',
            url: '/dashboard',
            views: {
                'content@app.host': {
                    templateUrl: 'app/views/member/dashboard/dashboard.html',
                    controller: 'MemberDashboardCtrl'
                }
            }
        });
        
        $stateProvider.state('app.host.game', {
            bodyClass: 'host scoreboard',
            title: 'Game Host Scoreboard',
            url: '/game-scoreboard/:gameId',
            views: {
                'content@app.host': {
                    templateUrl: 'app/views/host/scoreboard/scoreboard.html',
                    controller: 'HostScoreboardDashboardCtrl'
                }
            },
            resolve: {
                API: 'ApiRoutesGames',
                currentGame: function(initUser, API, $stateParams) {
                    return API.getGame($stateParams.gameId);
                }
            }
        });
        
        
    }]);