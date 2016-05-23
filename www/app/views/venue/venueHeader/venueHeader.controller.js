'use strict';

/* 
 * Layout Header: Venue
 * 
 * Used to control the venue layout header and top navigtion.
 */

angular.module('app.venue.header', [])
        .controller('VenueHeaderCtrl', ['$scope', 'UserSession', 'AuthService', 'siteSystemVariables', 
        function ($scope, UserSession, AuthService, siteSystemVariables) {
        
        //* Site configuration variables pre loaded by the resolve
        $scope.siteOptions = siteSystemVariables;
        
        //* User display name for logged in indicator
        $scope.userDisplayName = UserSession.displayName();

        //* Logout function in the auth service
        $scope.logout = AuthService.logout;
        
        //* ui.bootstrap navbar
        $scope.navbarCollapsed = true;
        
        //* ui.bootstrap logged in user menu drop down
        $scope.userNavDropdownIsOpen = false;
        
        //* ui.bootstrap authentication menu drop down
        $scope.authNavDropdownIsOpen = false;
        
        $(".navbar-nav li.trigger-collapse a").click(function (event) {
            $scope.navbarCollapsed = true;
        });
    }]);