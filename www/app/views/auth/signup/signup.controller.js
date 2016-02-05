'use strict';

/* 
 * Login Page
 * 
 * Controller for the login page.
 */

angular.module('app.auth.signup', [])
        .controller('AuthSignupCtrl', ['$rootScope', '$scope', '$state', '$log', 'AuthService', 'ApiRoutesAuth', '$window', '$timeout',
        function ($rootScope, $scope, $state, $log, AuthService, ApiRoutesAuth, $window, $timeout) {
               
        
        $scope.$state = $state;
        $scope.form = {};

        $scope.newUser = {
            'nameFirst' : '',
            'nameLast' : '',
            'email' : '',
            'password' : '',
            'passwordB' : '',
            'referrer' : '',
            'acceptTerms' : false,
            'triviaLove' : ''
        };
        
        $scope.signupAlerts = [];
        $scope.termsAlerts = [];
        
        $scope.showError = function(msg, type) {
            $log.error(msg);
            switch(type) {
                case 'terms':
                    $scope.termsAlerts.push({type: 'danger', msg: msg});
                    break;
                case 'signup':
                default:
                    $scope.signupAlerts.push({type: 'danger', msg: msg});
                    break;
            }
        };
        
        $scope.showSuccess = function(msg, type) {
            $log.info(msg);
            switch(type) {
                case 'terms':
                    $scope.termsAlerts.push({type: 'success', msg: msg});
                    break;
                case 'signup':
                default:
                    $scope.signupAlerts.push({type: 'success', msg: msg});
                    break;
            }
        };
        
        $scope.addAlert = function(array, msg) {
          array.push({msg: msg});
        };

        $scope.closeAlert = function(array, index) {
          array.splice(index, 1);
        };
        
        $scope.clearAlerts = function() {
            $scope.signupAlerts = [];
            $scope.termsAlerts = [];
        };

        $scope.signup = function() {
            $scope.clearAlerts();
            $scope.$broadcast('show-errors-check-validity');
            var stop = false;

            if(!$scope.form.terms.$valid) {
                $scope.form.terms.$setDirty();
                $scope.showError('Please fill in all form fields.', 'terms');
                stop = true;
            }
            if(!$scope.form.signup.$valid) {
                $scope.form.signup.$setDirty();
                $scope.showError('Please fill in all form fields.', 'signup');
                stop = true;
            }
            if(!stop) {
                AuthService.signup($scope.newUser).then(function(results) {
                    $rootScope.newUser = results;
                    $scope.showSuccess("Signup successful!", 'signup');
                    
                    $timeout(function() {
                        $scope.clearAlerts();
                        $window.location.href = 'http://www.triviajoint.com/registration-thank-you-page/';
                    }, 3000);
                    
                }, function(error) {
                    $log.debug(error);
                    $scope.showError(error, 'signup');
                });
            }
        };

        $scope.facebookSignup = function() {
            $scope.clearAlerts();
            $scope.$broadcast('show-errors-check-validity');

            if(!$scope.form.terms.$valid) {
                $scope.form.terms.$setDirty();
                $scope.showError('Please fill in all form fields.', 'terms');
            }
            else if(!$scope.form.signup.$valid) {
                AuthService.facebookSignup($scope.newUser).then(function (resp) {
                    $rootScope.newUser = resp;
                    $scope.showSuccess("Facebook signup Successful!", 'terms');

                    $timeout(function() {
                        $scope.clearAlerts();
                        $window.location.href = 'http://www.triviajoint.com/registration-thank-you-page/';
                    }, 3000);

                }, function (err) {
                    $scope.showError(err, 'terms');
                });
            }
            
            
        };
        
    }]);