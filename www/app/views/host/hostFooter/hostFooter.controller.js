'use strict';

/* 
 * Layout Footer: Admin
 * 
 * Used to control the member layout footer.
 */

angular.module('app.host.footer', [])
        .controller('HostFooterCtrl', ['$scope', 'siteSystemVariables', 
        function ($scope, siteSystemVariables) {
            
        //* Get site configuration variables
        $scope.siteOptions = siteSystemVariables;
        
        //* Year for copyright display
        $scope.currentYear = moment().year();
        
    }]);