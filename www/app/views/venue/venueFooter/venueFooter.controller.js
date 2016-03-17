'use strict';

/* 
 * Layout Footer: Venue
 * 
 * Used to control the member layout footer.
 */

angular.module('app.venue.footer', [])
        .controller('VenueFooterCtrl', ['$scope', 'siteSystemVariables', 
        function ($scope, siteSystemVariables) {
            
        //* Get site configuration variables
        $scope.siteOptions = siteSystemVariables;
        
        //* Year for copyright display
        $scope.currentYear = moment().year();
        
    }]);