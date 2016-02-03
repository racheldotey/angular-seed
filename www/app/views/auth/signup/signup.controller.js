'use strict';

/* 
 * Login Page
 * 
 * Controller for the login page.
 */

angular.module('app.auth.signup', [])
        .controller('AuthSignupCtrl', ['$rootScope', '$scope', '$state', '$log', 'AuthService', 'ApiRoutesAuth',
        function ($rootScope, $scope, $state, $log, AuthService, ApiRoutesAuth) {
        
        
        if(($state.is('app.auth.signup') || $state.is('app.auth.signup.stepTwo') || $state.is('app.auth.signup.success')) 
                && typeof($rootScope.newUser) === 'undefined') {
            $rootScope.newUser = {id:1};
            //$state.go('app.auth.signup');
        }
        
        
        $scope.$state = $state;
        $scope.form = {};

        $scope.newUser = {
            'nameFirst' : '',
            'nameLast' : '',
            'email' : '',
            'password' : '',
            'passwordB' : '',
            'referrer' : ''
        };
        
        $scope.additionalInfo = {
            'triviaLove' : ''
        };
        
        $scope.signupAlerts = [];
        $scope.facebookAlerts = [];
        $scope.additionalAlerts = [];
        
        $scope.showError = function(msg, type) {
            $log.error(msg);
            switch(type) {
                case 'facebook':
                    $scope.facebookAlerts.push({type: 'danger', msg: msg});
                    break;
                case 'additional':
                    $scope.additionalAlerts.push({type: 'danger', msg: msg});
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
                case 'facebook':
                    $scope.facebookAlerts.push({type: 'success', msg: msg});
                    break;
                case 'additional':
                    $scope.additionalAlerts.push({type: 'success', msg: msg});
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
            $scope.facebookAlerts = [];
            $scope.additionalAlerts = [];
        };

        $scope.signup = function() {
            $scope.$broadcast('show-errors-check-validity');

            if($scope.form.signup.$valid) {
                AuthService.signup($scope.newUser).then(function(results) {
                    $rootScope.newUser = results;
                    $scope.showSuccess("Signup successful!", 'signup');
                    $state.go('app.auth.signup.stepTwo');
                    $scope.clearAlerts();
                }, function(error) {
                    $log.debug(error);
                    $scope.showError(error, 'signup');
                });
            } else {
                $scope.form.signup.$setDirty();
               $scope.showError('Please fill in all required form fields.', 'signup');
            }
        };

        $scope.facebookSignup = function() {
            AuthService.facebookSignup().then(function (resp) {
                $rootScope.newUser = resp;
                $scope.showSuccess("Facebook signup Successful!", 'facebook');
                $state.go('app.auth.signup.stepTwo');
                $scope.clearAlerts();
            }, function (err) {
                $scope.showError(err, 'facebook');
            });
        };
        
        $scope.becomeMember = function() {
            
            if($scope.form.signupTwo.$valid) {
                var data = $scope.additionalInfo;
                data.userId = $rootScope.newUser.id;
                ApiRoutesAuth.postAdditionalInfo(data).then(function (resp) {
                    $scope.showSuccess("Save successful.", 'additional');
                    $state.go('app.auth.signup.success');
                    $scope.clearAlerts();
                }, function (err) {
                    $scope.showError(err, 'additional');
                });
            
            } else {
                $scope.form.signupTwo.$setDirty();
               $scope.showError('Please answer the question and accept the terms of services below.', 'additional');
            }
        };
        
    }]);