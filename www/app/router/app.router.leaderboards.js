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
            data: {authorizedRoles: USER_ROLES.host},
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
            data: {authorizedRoles: USER_ROLES.host},
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
        
        $stateProvider.state('app.leaderboard.globalPlayerCheckins', {
            bodyClass: 'leaderboard player checkins',
            title: 'Global Player Checkins Leaderboard',
            url: '/global/players/checkins/:count',
            views: {
                'content@app.leaderboard': {
                    templateUrl: 'app/views/leaderboards/globalPlayerCheckins/globalPlayerCheckins.html',
                    controller: 'GlobalPlayerCheckinsLeaderboardCtrl'
                }
            }
        });
        
        $stateProvider.state('app.iframeLeaderboard.globalPlayerCheckins', {
            bodyClass: 'leaderboard player checkins',
            title: 'Global Player Checkins Leaderboard',
            url: '/global/players/checkins/:count',
            views: {
                'content@app.iframeLeaderboard': {
                    templateUrl: 'app/views/leaderboards/globalPlayerCheckins/globalPlayerCheckins.html',
                    controller: 'GlobalPlayerCheckinsLeaderboardCtrl'
                }
            }
        });
        
        $stateProvider.state('app.leaderboard.globalPlayers', {
            bodyClass: 'leaderboard players',
            title: 'Global Player Score Leaderboard',
            url: '/global/players/score/:count',
            views: {
                'content@app.leaderboard': {
                    templateUrl: 'app/views/leaderboards/globalPlayers/globalPlayers.html',
                    controller: 'GlobalPlayersLeaderboardCtrl'
                }
            }
        });
        
        $stateProvider.state('app.iframeLeaderboard.globalPlayers', {
            bodyClass: 'leaderboard players',
            title: 'Global Player Score Leaderboard',
            url: '/global/players/score/:count',
            views: {
                'content@app.iframeLeaderboard': {
                    templateUrl: 'app/views/leaderboards/globalPlayers/globalPlayers.html',
                    controller: 'GlobalPlayersLeaderboardCtrl'
                }
            }
        });
        
        $stateProvider.state('app.leaderboard.globalTeamCheckins', {
            bodyClass: 'leaderboard team checkins',
            title: 'Global Team Checkin Leaderboard',
            url: '/global/teams/checkins/:count',
            views: {
                'content@app.leaderboard': {
                    templateUrl: 'app/views/leaderboards/globalTeamCheckins/globalTeamCheckins.html',
                    controller: 'GlobalTeamCheckinsLeaderboardCtrl'
                }
            }
        });
        
        $stateProvider.state('app.iframeLeaderboard.globalTeamCheckins', {
            bodyClass: 'leaderboard team checkins',
            title: 'Global Team Checkin Leaderboard',
            url: '/global/teams/checkins/:count',
            views: {
                'content@app.iframeLeaderboard': {
                    templateUrl: 'app/views/leaderboards/globalTeamCheckins/globalTeamCheckins.html',
                    controller: 'GlobalTeamCheckinsLeaderboardCtrl'
                }
            }
        });
        
        $stateProvider.state('app.leaderboard.globalTeams', {
            bodyClass: 'leaderboard teams',
            title: 'Global Team Score Leaderboard',
            url: '/global/teams/score/:count',
            views: {
                'content@app.leaderboard': {
                    templateUrl: 'app/views/leaderboards/globalTeams/globalTeams.html',
                    controller: 'GlobalTeamsLeaderboardCtrl'
                }
            }
        });
        
        $stateProvider.state('app.iframeLeaderboard.globalTeams', {
            bodyClass: 'leaderboard teams',
            title: 'Global Team Score Leaderboard',
            url: '/global/teams/score/:count',
            views: {
                'content@app.iframeLeaderboard': {
                    templateUrl: 'app/views/leaderboards/globalTeams/globalTeams.html',
                    controller: 'GlobalTeamsLeaderboardCtrl'
                }
            }
        });
        
        $stateProvider.state('app.leaderboard.venuePlayerCheckins', {
            bodyClass: 'leaderboard players',
            title: 'Per Joint Player Checkins Leaderboard',
            url: '/joint/players/checkins/:venueId/:count',
            views: {
                'content@app.leaderboard': {
                    templateUrl: 'app/views/leaderboards/venuePlayerCheckins/venuePlayerCheckins.html',
                    controller: 'VenuePlayerCheckinsLeaderboardCtrl'
                }
            }
        });
        
        $stateProvider.state('app.iframeLeaderboard.venuePlayerCheckins', {
            bodyClass: 'leaderboard players',
            title: 'Per Joint Player Checkins Leaderboard',
            url: '/joint/players/checkins/:venueId/:count',
            views: {
                'content@app.iframeLeaderboard': {
                    templateUrl: 'app/views/leaderboards/venuePlayerCheckins/venuePlayerCheckins.html',
                    controller: 'VenuePlayerCheckinsLeaderboardCtrl'
                }
            }
        });
        
        $stateProvider.state('app.leaderboard.venuePlayers', {
            bodyClass: 'leaderboard venue players',
            title: 'Per Joint Player Score Leaderboard',
            url: '/joint/players/score/:venueId/:count',
            views: {
                'content@app.leaderboard': {
                    templateUrl: 'app/views/leaderboards/venuePlayers/venuePlayers.html',
                    controller: 'VenuePlayersLeaderboardCtrl'
                }
            }
        });
        
        $stateProvider.state('app.iframeLeaderboard.venuePlayers', {
            bodyClass: 'leaderboard venue players',
            title: 'Per Joint Player Score Leaderboard',
            url: '/joint/players/score/:venueId/:count',
            views: {
                'content@app.iframeLeaderboard': {
                    templateUrl: 'app/views/leaderboards/venuePlayers/venuePlayers.html',
                    controller: 'VenuePlayersLeaderboardCtrl'
                }
            }
        });
        
        $stateProvider.state('app.leaderboard.venueTeamCheckins', {
            bodyClass: 'leaderboard players',
            title: 'Per Joint Team Checkins Leaderboard',
            url: '/jont/players/checkins/:venueId/:count',
            views: {
                'content@app.leaderboard': {
                    templateUrl: 'app/views/leaderboards/venueTeamCheckins/venueTeamCheckins.html',
                    controller: 'VenueTeamCheckinsLeaderboardCtrl'
                }
            }
        });
        
        $stateProvider.state('app.iframeLeaderboard.venueTeamCheckins', {
            bodyClass: 'leaderboard players',
            title: 'Per Joint Team Checkins Leaderboard',
            url: '/jont/players/checkins/:venueId/:count',
            views: {
                'content@app.iframeLeaderboard': {
                    templateUrl: 'app/views/leaderboards/venueTeamCheckins/venueTeamCheckins.html',
                    controller: 'VenueTeamCheckinsLeaderboardCtrl'
                }
            }
        });
        
        $stateProvider.state('app.leaderboard.venueTeams', {
            bodyClass: 'leaderboard venue teams',
            title: 'Per Joint Team Score Leaderboard',
            url: '/joint/teams/score/:venueId/:count',
            views: {
                'content@app.leaderboard': {
                    templateUrl: 'app/views/leaderboards/venueTeams/venueTeams.html',
                    controller: 'VenueTeamsLeaderboardCtrl'
                }
            }
        });
        
        $stateProvider.state('app.iframeLeaderboard.venueTeams', {
            bodyClass: 'leaderboard venue teams',
            title: 'Per Joint Team Score Leaderboard',
            url: '/joint/teams/score/:venueId/:count',
            views: {
                'content@app.iframeLeaderboard': {
                    templateUrl: 'app/views/leaderboards/venueTeams/venueTeams.html',
                    controller: 'VenueTeamsLeaderboardCtrl'
                }
            }
        });
        
    }]);