'use strict';

/* 
 * Modal Signup Form
 * 
 * Controller for the modal version of the signup form.
 */

angular.module('app.modal.signup', [])
        .controller('SignupModalCtrl', ['$scope', '$uibModalInstance', '$state', '$log', '$window', '$timeout', 'AuthService', 'AlertConfirmService',
        function ($scope, $uibModalInstance, $state, $log, $window, $timeout, AuthService, AlertConfirmService) {
        
        $scope.$state = $state;
        $scope.form = {};
        $scope.facebookAlerts = {};
        $scope.signupAlerts = {};
        
        $scope.showPasswordRules = false;
        $scope.showPasswordMissmatch = false;

        $scope.newUser = {
            'userGroup' : 'player',
            'nameFirst' : '',
            'nameLast' : '',
            'email' : '',
            'password' : '',
            'passwordB' : '',
            'referrer' : '',
            'acceptTerms' : false
        };
        $scope.newUserB = {
            'userGroup' : 'player',
            'nameFirst' : 'Ra',
            'nameLast' : 'Carbone',
            'email' : 'r@gmail.com',
            'password' : 'password1',
            'passwordB' : 'password1',
            'referrer' : 'Google',
            'acceptTerms' : true
        };

        $scope.signup = function() {
            if(!$scope.form.signup.$valid) {
                $scope.form.signup.$setDirty();
                $scope.signupAlerts.error('Please fill in the Email, and Password fields.');
            } else if($scope.newUser.password !== $scope.newUser.passwordB) {
                $scope.form.signup.$setDirty();
                $scope.signupAlerts.error('Passwords do not match.');
            } else if(!$scope.newUser.acceptTerms) {
                AlertConfirmService.confirm('Do you agree to our <a href="http://www.triviajoint.com/terms-and-conditions/" target="_blank">Terms of Service</a>?', 'Terms of Service Agreement').result.then(function (resp) {
                    $scope.newUser.acceptTerms = true;
                    AuthService.signup($scope.newUser, true).then(function (results) {
                        $log.debug(results);
                        $uibModalInstance.close("Signup successful!");
                    }, function (error) {
                        $scope.signupAlerts.error(error);
                    });
                }, function (err) {
                    $scope.facebookAlerts.error('Please accept the Terms of Service to signup.');
                });
            } else {
                AuthService.signup($scope.newUser, true).then(function (results) {
                    $log.debug(results);
                    $uibModalInstance.close("Signup successful!  Please wait for confirmation page.");
                }, function (error) {
                    $scope.signupAlerts.error(error);
                });
            }
        };

        $scope.facebookSignup = function() {
            if(!$scope.newUser.acceptTerms) {
                AlertConfirmService.confirm('Do you agree to our <a href="http://www.triviajoint.com/terms-and-conditions/" target="_blank">Terms of Service</a>?', 'Terms of Service Agreement').result.then(function (resp) {
                    $scope.newUser.acceptTerms = true;
                    AuthService.facebookSignup().then(function (resp) {
                        $log.debug(resp);
                        $scope.newUser = resp;
                        
                        $uibModalInstance.close("Facebook signup Successful!");
                    }, function (err) {
                        $scope.facebookAlerts.error(err);
                    });
                }, function (err) {
                    $scope.facebookAlerts.error('Please accept the Terms of Service to signup.');
                });
            } else {
                AuthService.facebookSignup().then(function (resp) {
                    $log.debug(resp);
                    $scope.newUser = resp;

                    $uibModalInstance.close("Facebook signup Successful!");
                }, function (err) {
                    $scope.facebookAlerts.error(err);
                });
            }
        };
        
        var passwordValidator = /^(?=.*\d)(?=.*[A-Za-z])[A-Za-z0-9_!@#$%^&*+=-]{8,55}$/;
        $scope.onChangeValidatePassword = function() {
            $scope.showPasswordRules = (!passwordValidator.test($scope.newUser.password));
            $scope.onChangeValidateConfirmPassword();
        };
        
        $scope.onChangeValidateConfirmPassword = function() {
            $scope.showPasswordMissmatch = ($scope.newUser.password !== $scope.newUser.passwordB);
        };

        /* Click event for the Cancel button */
        $scope.buttonCancel = function() {
            $uibModalInstance.dismiss(false);
        };
    }]);