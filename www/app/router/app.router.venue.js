'use strict';

/*
 * State Declarations: Venue / Authenticated
 * 
 * Set up the states for logged in user routes, such as the 
 * user profile page and other authenticated states.
 * Ueses ui-roter's $stateProvider.
 * 
 * Set each state's title (used in the config for the html <title>).
 * 
 * Set auth access for each state.
 */

var app = angular.module('app.router.venue', [
    'rcAuth.constants',
    'app.venue'
]);
app.config(['$stateProvider', '$urlRouterProvider', 'USER_ROLES', function ($stateProvider, $urlRouterProvider, USER_ROLES) {

        /*  Abstract Member (Authenticated) Route */
        $stateProvider.state('app.venue', {
            url: '/venue',
            abstract: true,
            data: {authorizedRoles: USER_ROLES.user},
            views: {
                'header@app.venue': {
                    templateUrl: 'app/views/venue/venueHeader/venueHeader.html',
                    controller: 'VenueHeaderCtrl'
                },
                'layout@': {
                    templateUrl: 'app/views/venue/venueLayout/venueLayout.html',
                    controller: 'VenueLayoutCtrl'
                },
                'footer@app.venue': {
                    templateUrl: 'app/views/venue/venueFooter/venueFooter.html',
                    controller: 'VenueFooterCtrl'
                }
            }
        });

        $stateProvider.state('app.venue.dashboard', {
            bodyClass: 'venue dashboard',
            title: 'Venue Dashboard',
            url: '/dashboard',
            views: {
                'content@app.venue': {
                    templateUrl: 'app/views/venue/dashboard/dashboard.html',
                    controller: 'VenueDashboardCtrl'
                }
            }
        });
        
        // Redirect /venue to the dashboard
        $urlRouterProvider.when('/venue', '/venue/dashboard');
        $urlRouterProvider.when('/venue/', '/venue/dashboard');

        $stateProvider.state('app.venue.profile', {
            bodyClass: 'venue profile',
            title: 'User Profile',
            url: '/profile',
            views: {
                'content@app.venue': {
                    templateUrl: 'app/views/member/profile/profile.html',
                    controller: 'MemberProfileCtrl'
                }
            }
        });
        
        
    }]);