'use strict';

/*
 * State Declarations: Game Leaderboards
 * 
 * Set up the states for logged in user routes, such as the 
 * user profile page and other authenticated states.
 * Uses ui-roter's $stateProvider.
 * 
 * Set each state's title (used in the config for the html <title>).
 * 
 * Set auth access for each state.
 */

var app = angular.module('app.router.leaderboards', [
    'rcAuth.constants',
    'app.leaderboards'
]);
app.config(['$stateProvider', '$urlRouterProvider', 'USER_ROLES', function ($stateProvider, $urlRouterProvider, USER_ROLES) {

        /*  Abstract Member (Authenticated) Route */
        $stateProvider.state('app.iframeLeaderboard', {
            url: '/iframe/leaderboard',
            abstract: true,
            data: {authorizedRoles: USER_ROLES.guest},
            views: {
                'layout@': {
                    templateUrl: 'app/views/member/memberiFrameLayout/memberiFrameLayout.html'
                }
            }
        });
        
        /*  Abstract Member (Authenticated) Route */
        $stateProvider.state('app.leaderboard', {
            url: '/leaderboard',
            abstract: true,
            data: {authorizedRoles: USER_ROLES.guest},
            views: {
                'header@app.leaderboard': {
                    templateUrl: 'app/views/member/memberHeader/memberHeader.html',
                    controller: 'MemberHeaderCtrl'
                },
                'layout@': {
                    templateUrl: 'app/views/member/memberLayout/memberLayout.html',
                    controller: 'MemberLayoutCtrl'
                },
                'footer@app.leaderboard': {
                    templateUrl: 'app/views/member/memberFooter/memberFooter.html',
                    controller: 'MemberFooterCtrl'
                }
            }
        });
        
        // Global Player Checkins Leaderboard 
        $stateProvider.state('app.leaderboard.globalPlayerCheckins', {
            bodyClass: 'leaderboard player checkins',
            title: 'Global Player Checkins Leaderboard',
            url: '/global/players/checkins?limit&startDate&endDate',
            views: {
                'content@app.leaderboard': {
                    templateUrl: 'app/views/leaderboards/globalPlayerCheckins/globalPlayerCheckins.html',
                    controller: 'GlobalPlayerCheckinsLeaderboardCtrl'
                }
            }
        });
        
        // Global Player Checkins Leaderboard - iFrame
        $stateProvider.state('app.iframeLeaderboard.globalPlayerCheckins', {
            bodyClass: 'leaderboard player checkins iframe-compatible',
            title: 'Global Player Checkins Leaderboard',
            url: '/global/players/checkins?limit&startDate&endDate',
            views: {
                'content@app.iframeLeaderboard': {
                    templateUrl: 'app/views/leaderboards/globalPlayerCheckins/globalPlayerCheckins.html',
                    controller: 'GlobalPlayerCheckinsLeaderboardCtrl'
                }
            }
        });
        
        // Global Player Score Leaderboard
        $stateProvider.state('app.leaderboard.globalPlayers', {
            bodyClass: 'leaderboard players',
            title: 'Global Player Score Leaderboard',
            url: '/global/players/score?limit&startDate&endDate',
            views: {
                'content@app.leaderboard': {
                    templateUrl: 'app/views/leaderboards/globalPlayers/globalPlayers.html',
                    controller: 'GlobalPlayersLeaderboardCtrl'
                }
            }
        });
        
        // Global Player Score Leaderboard - iFrame
        $stateProvider.state('app.iframeLeaderboard.globalPlayers', {
            bodyClass: 'leaderboard players iframe-compatible',
            title: 'Global Player Score Leaderboard',
            url: '/global/players/score?limit&startDate&endDate',
            views: {
                'content@app.iframeLeaderboard': {
                    templateUrl: 'app/views/leaderboards/globalPlayers/globalPlayers.html',
                    controller: 'GlobalPlayersLeaderboardCtrl'
                }
            }
        });
        
        // Global Team Checkin Leaderboard
        $stateProvider.state('app.leaderboard.globalTeamCheckins', {
            bodyClass: 'leaderboard team checkins',
            title: 'Global Team Checkin Leaderboard',
            url: '/global/teams/checkins?limit&startDate&endDate',
            views: {
                'content@app.leaderboard': {
                    templateUrl: 'app/views/leaderboards/globalTeamCheckins/globalTeamCheckins.html',
                    controller: 'GlobalTeamCheckinsLeaderboardCtrl'
                }
            }
        });
        
        // Global Team Checkin Leaderboard - iFrame
        $stateProvider.state('app.iframeLeaderboard.globalTeamCheckins', {
            bodyClass: 'leaderboard team checkins iframe-compatible',
            title: 'Global Team Checkin Leaderboard',
            url: '/global/teams/checkins?limit&startDate&endDate',
            views: {
                'content@app.iframeLeaderboard': {
                    templateUrl: 'app/views/leaderboards/globalTeamCheckins/globalTeamCheckins.html',
                    controller: 'GlobalTeamCheckinsLeaderboardCtrl'
                }
            }
        });
        
        // Global Team Score Leaderboard
        $stateProvider.state('app.leaderboard.globalTeams', {
            bodyClass: 'leaderboard teams',
            title: 'Global Team Score Leaderboard',
            url: '/global/teams/score?limit&startDate&endDate',
            views: {
                'content@app.leaderboard': {
                    templateUrl: 'app/views/leaderboards/globalTeams/globalTeams.html',
                    controller: 'GlobalTeamsLeaderboardCtrl'
                }
            }
        });
        
        // Global Team Score Leaderboard - iFrame
        $stateProvider.state('app.iframeLeaderboard.globalTeams', {
            bodyClass: 'leaderboard teams iframe-compatible',
            title: 'Global Team Score Leaderboard',
            url: '/global/teams/score?limit&startDate&endDate',
            views: {
                'content@app.iframeLeaderboard': {
                    templateUrl: 'app/views/leaderboards/globalTeams/globalTeams.html',
                    controller: 'GlobalTeamsLeaderboardCtrl'
                }
            }
        });
        
        // Per Joint Player Checkins Leaderboard
        $stateProvider.state('app.leaderboard.venuePlayerCheckins', {
            bodyClass: 'leaderboard players',
            title: 'Per Joint Player Checkins Leaderboard',
            url: '/joint/players/checkins/:venueId?limit&startDate&endDate',
            views: {
                'content@app.leaderboard': {
                    templateUrl: 'app/views/leaderboards/venuePlayerCheckins/venuePlayerCheckins.html',
                    controller: 'VenuePlayerCheckinsLeaderboardCtrl'
                }
            }
        });
        
        // Per Joint Player Checkins Leaderboard - iFrame
        $stateProvider.state('app.iframeLeaderboard.venuePlayerCheckins', {
            bodyClass: 'leaderboard players iframe-compatible',
            title: 'Per Joint Player Checkins Leaderboard',
            url: '/joint/players/checkins/:venueId?limit&startDate&endDate',
            views: {
                'content@app.iframeLeaderboard': {
                    templateUrl: 'app/views/leaderboards/venuePlayerCheckins/venuePlayerCheckins.html',
                    controller: 'VenuePlayerCheckinsLeaderboardCtrl'
                }
            }
        });
        
        // Per Joint Player Score Leaderboard
        $stateProvider.state('app.leaderboard.venuePlayers', {
            bodyClass: 'leaderboard venue players',
            title: 'Per Joint Player Score Leaderboard',
            url: '/joint/players/score/:venueId?limit&startDate&endDate',
            views: {
                'content@app.leaderboard': {
                    templateUrl: 'app/views/leaderboards/venuePlayers/venuePlayers.html',
                    controller: 'VenuePlayersLeaderboardCtrl'
                }
            }
        });
        
        // Per Joint Player Score Leaderboard - iFrame
        $stateProvider.state('app.iframeLeaderboard.venuePlayers', {
            bodyClass: 'leaderboard venue players iframe-compatible',
            title: 'Per Joint Player Score Leaderboard',
            url: '/joint/players/score/:venueId?limit&startDate&endDate',
            views: {
                'content@app.iframeLeaderboard': {
                    templateUrl: 'app/views/leaderboards/venuePlayers/venuePlayers.html',
                    controller: 'VenuePlayersLeaderboardCtrl'
                }
            }
        });
        
        // Per Joint Team Checkins Leaderboard
        $stateProvider.state('app.leaderboard.venueTeamCheckins', {
            bodyClass: 'leaderboard players',
            title: 'Per Joint Team Checkins Leaderboard',
            url: '/joint/teams/checkins/:venueId?limit&startDate&endDate',
            views: {
                'content@app.leaderboard': {
                    templateUrl: 'app/views/leaderboards/venueTeamCheckins/venueTeamCheckins.html',
                    controller: 'VenueTeamCheckinsLeaderboardCtrl'
                }
            }
        });
        
        // Per Joint Team Checkins Leaderboard - iFrame
        $stateProvider.state('app.iframeLeaderboard.venueTeamCheckins', {
            bodyClass: 'leaderboard players iframe-compatible',
            title: 'Per Joint Team Checkins Leaderboard',
            url: '/joint/teams/checkins/:venueId?limit&startDate&endDate',
            views: {
                'content@app.iframeLeaderboard': {
                    templateUrl: 'app/views/leaderboards/venueTeamCheckins/venueTeamCheckins.html',
                    controller: 'VenueTeamCheckinsLeaderboardCtrl'
                }
            }
        });
        
        // Per Joint Team Score Leaderboard
        $stateProvider.state('app.leaderboard.venueTeams', {
            bodyClass: 'leaderboard venue teams',
            title: 'Per Joint Team Score Leaderboard',
            url: '/joint/teams/score/:venueId?limit&startDate&endDate',
            views: {
                'content@app.leaderboard': {
                    templateUrl: 'app/views/leaderboards/venueTeams/venueTeams.html',
                    controller: 'VenueTeamsLeaderboardCtrl'
                }
            }
        });
        
        // Per Joint Team Score Leaderboard - iFrame
        $stateProvider.state('app.iframeLeaderboard.venueTeams', {
            bodyClass: 'leaderboard venue teams iframe-compatible',
            title: 'Per Joint Team Score Leaderboard',
            url: '/joint/teams/score/:venueId?limit&startDate&endDate',
            views: {
                'content@app.iframeLeaderboard': {
                    templateUrl: 'app/views/leaderboards/venueTeams/venueTeams.html',
                    controller: 'VenueTeamsLeaderboardCtrl'
                }
            }
        });
        
    }]);