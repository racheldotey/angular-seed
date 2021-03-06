'use strict';

/* 
 * Page: Error Not Authorized
 * 
 * Used to display a not authorized message to users.
 */

angular.module('app.error.notAuthorized', [])
        .controller('ErrorNotAuthorizedCtrl', ['$scope', '$state', '$timeout',
    function($scope, $state, $timeout) {
        $scope.timer = 5;
        
        $timeout(function () {
            $scope.goToDashboard();
        }, 6000);
        
        ($scope.tick = function() {
            $timeout(function () {
                $scope.timer--;
                if($scope.timer > 0) {
                    $scope.tick();
                }
            }, 1000);
        })();
        
        $scope.goToDashboard = function() {
            $state.go('app.member.dashboard');
        };
        
        $scope.isActiveState = function(state) {
            return (state === $state.current.name);
        };
    }]);