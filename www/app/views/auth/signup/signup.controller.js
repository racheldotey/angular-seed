'use strict';

/* 
 * Login Page
 * 
 * Controller for the login page.
 */

angular.module('app.auth.signup', [])
        .controller('AuthSignupCtrl', ['$rootScope', '$scope', '$state', '$log', 'AuthService', 'ApiRoutesAuth',
        function ($rootScope, $scope, $state, $log, AuthService, ApiRoutesAuth) {
        
        $scope.$state = $state;
        $scope.form = {};

        $scope.newUser = {
            'nameFirst' : 'Jane',
            'nameLast' : 'Trivia',
            'email' : 'jane@trivia.org',
            'password' : 'gameOn2332',
            'passwordB' : 'gameOn2332',
            'referrer' : 'The internet!'
        };
        
        $scope.additionalInfo = {
            'triviaLove' : 'warm'
        };
        
        $scope.showError = function(error) {
            $log.error(error);
            
        };
        
        $scope.showSuccess = function(msg) {
            $log.info(msg);
        };

        $scope.signup = function() {
            $scope.$broadcast('show-errors-check-validity');

            if($scope.form.signup.$valid) {
                AuthService.signup($scope.newUser).then(function(results) {
                    $rootScope.newUser = results;
                    $scope.showSuccess(results);
                    $state.go('app.auth.signup.stepTwo');
                }, function(error) {
                    $log.debug(error);
                });
            } else {
                $scope.form.signup.$setDirty();
               $scope.showError('Please fill in all required form fields.');
            }
        };

        $scope.facebookSignup = function() {
            AuthService.facebookSignup().then(function (resp) {
                $rootScope.newUser = resp;
                $scope.showSuccess(resp);
                $state.go('app.auth.signup.stepTwo');
            }, function (err) {
                $scope.showError(err);
            });
        };
        
        $scope.becomeMember = function() {
            var data = $scope.additionalInfo;
            data.userId = $rootScope.newUser.id;
            ApiRoutesAuth.postAdditionalInfo(data).then(function (resp) {
                $scope.showSuccess(resp);
                $state.go('app.auth.signup.success');
            }, function (err) {
                $scope.showError(err);
            });
        };
        
    }]);