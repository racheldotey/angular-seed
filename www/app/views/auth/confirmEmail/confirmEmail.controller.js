'use strict';

/* 
 * Confrirm Email Page
 * 
 * Controller for the signup email confirmation page.
 */

angular.module('app.auth.confirmEmail', [])
        .controller('ConfirmEmailCtrl', ['$scope', '$state', '$timeout',
    function($scope, $state, $timeout) {
        $scope.timer = 5;
        
        $timeout(function () {
            if($state.is('app.auth.signup.confirmFail')) {
                $scope.goToProfile();
            } else {
                $scope.goToDashboard();
            }
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
        
        $scope.goToProfile = function() {
            $state.go('app.member.profile');
        };
    }]);