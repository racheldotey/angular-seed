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
                    $scope.signupAlerts.push({type: 'danger', msg: msg});
                    break;
                case 'additional':
                    $scope.facebookAlerts.push({type: 'danger', msg: msg});
                    break;
                case 'signup':
                default:
                    $scope.additionalAlerts.push({type: 'danger', msg: msg});
                    break;
            }
        };
        
        $scope.showSuccess = function(msg, type) {
            $log.info(msg);
            switch(type) {
                case 'facebook':
                    $scope.signupAlerts.push({type: 'success', msg: msg});
                    break;
                case 'additional':
                    $scope.facebookAlerts.push({type: 'success', msg: msg});
                    break;
                case 'signup':
                default:
                    $scope.additionalAlerts.push({type: 'success', msg: msg});
                    break;
            }
        };
        
        $scope.addAlert = function(array, msg) {
          array.push({msg: msg});
        };

        $scope.closeAlert = function(index) {
          $scope.alerts.splice(index, 1);
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
                $scope.showSuccess("Facebook signup Successful!");
                $state.go('app.auth.signup.stepTwo', 'facebook');
                    $scope.clearAlerts();
            }, function (err) {
                $scope.showError(err, 'facebook');
            });
        };
        
        $scope.becomeMember = function() {
            var data = $scope.additionalInfo;
            data.userId = $rootScope.newUser.id;
            ApiRoutesAuth.postAdditionalInfo(data).then(function (resp) {
                $scope.showSuccess("Save successful.", 'additional');
                $state.go('app.auth.signup.success');
                    $scope.clearAlerts();
            }, function (err) {
                $scope.showError(err, 'additional');
            });
        };
        
    }]);