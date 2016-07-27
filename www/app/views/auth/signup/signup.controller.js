'use strict';

/* 
 * Login Page
 * 
 * Controller for the login page.
 */

angular.module('app.auth.signup', [])
        .controller('AuthSignupCtrl', ['$scope', '$state', '$log', '$window', '$timeout', 'AuthService', 'AlertConfirmService', 'notifications',
        function ($scope, $state, $log, $window, $timeout, AuthService, AlertConfirmService, notifications) {
        
        var passwordValidator = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])([a-zA-Z0-9!@#$%^&*_+=-]{8,100})$/;
        
        $scope.$state = $state;
        $scope.form = {};
        
        $scope.showPasswordRules = false;
        $scope.showPasswordMissmatch = false;

        $scope.newUser = {
            'userGroup' : 'player',
            'nameFirst' : '',
            'nameLast' : '',
            'email' : '',
            'phone' : '',
            'password' : '',
            'passwordB' : '',
            'referrer' : '',
            'acceptTerms' : true,
            'acceptNewsletter' : true
        };

        var sendSignupForm = function() {
            AuthService.signup($scope.newUser).then(function (results) {
                $log.debug(results);

                notifications.showSuccess("Signup successful! You will recieve a confirmation email shortly.");

                $timeout(function () {
                    $state.go('');
                }, 1000);
            }, function (error) {
                notifications.showError(error);
            });
        };

        var sendFacebookSignup = function() {
            AuthService.facebookSignup().then(function (resp) {
                $log.debug(resp);
                $scope.newUser = resp;

                notifications.showSuccess("Signup successful! You will recieve a confirmation email shortly.");

                $timeout(function () {
                    $window.top.location.href = 'http://www.angularseed.com/registration-thank-you-page/';
                }, 1000);
            }, function (err) {
                notifications.showError(err);
            });
        };

        var triggerHoneypot = function() {
            if(angular.isDefined($scope.newUser.honeypot)) {
                console.log('Honeypot failed.');
                $scope.newUser = {
                    'userGroup' : 'player',
                    'nameFirst' : '',
                    'nameLast' : '',
                    'email' : '',
                    'phone' : '',
                    'password' : '',
                    'passwordB' : '',
                    'referrer' : '',
                    'acceptTerms' : true,
                    'acceptNewsletter' : true
                };
            }
        };

        $scope.signup = function() {
            notifications.closeAll();
            triggerHoneypot();

            if(!$scope.form.signup.$valid) {
                $scope.form.signup.$setDirty();
                notifications.showError('Please agree to our Terms of Use and fill your Name, Email, and Password.');
            } else if($scope.newUser.password !== $scope.newUser.passwordB) {
                $scope.form.signup.$setDirty();
                notifications.showError('Passwords do not match.');
            } else if(!$scope.newUser.acceptTerms) {
                AlertConfirmService.confirm('Do you agree to our <a data-ui-sref="app.public.terms" target="_blank">Terms of Use</a>?', 'Terms of Use').result.then(function (resp) {
                    $scope.newUser.acceptTerms = true;
                    sendSignupForm();
                }, function (err) {
                    notifications.showError('Please accept the Terms of Use to signup.');
                });
            } else {
                sendSignupForm();
            }
        };

        $scope.facebookSignup = function() {
            notifications.closeAll();
            triggerHoneypot();
            
            if(!$scope.newUser.acceptTerms) {
                AlertConfirmService.confirm('Do you agree to our <a data-ui-sref="app.public.terms" target="_blank">Terms of Use</a>?', 'Terms of Use').result.then(function (resp) {
                    $scope.newUser.acceptTerms = true;
                    sendFacebookSignup();
                }, function (err) {
                    notifications.showError('Please accept the Terms of Use to signup.');
                });
            } else {
                sendFacebookSignup();
            }
        };
        
        $scope.onChangeValidatePassword = function() {
            $scope.showPasswordRules = (!passwordValidator.test($scope.newUser.password));
            $scope.onChangeValidateConfirmPassword();
        };
        
        $scope.onChangeValidateConfirmPassword = function() {
            $scope.showPasswordMissmatch = ($scope.newUser.password !== $scope.newUser.passwordB);
        };
    }]);