'use strict';

/* 
 * Venue Signup Page
 * 
 * Controller for the login page.
 */

angular.module('app.auth.signupVenue', [])
        .controller('AuthSignupVenueCtrl', ['$scope', '$state', '$log', '$window', 'AuthService', 'AlertConfirmService',
        function ($scope, $state, $log, $window, AuthService, AlertConfirmService) {
        
        $scope.$state = $state;
        $scope.form = {};
        $scope.facebookAlerts = {};
        $scope.signupAlerts = {};
        $scope.venueAlerts = {};
        
        $scope.showPasswordRules = false;
        $scope.showPasswordMissmatch = false;
        
        $scope.newUser = {
            'userGroup' : 'venue.owner',
            'nameFirst' : '',
            'nameLast' : '',
            'email' : '',
            'password' : '',
            'passwordB' : '',
            'referer' : '',
            'acceptTerms' : false,
            
            'venueName' : '',
            'phone' : '',
            'address' : '',
            'addressb' : '',
            'city' : '',
            'state' : '',
            'zip' : '',
            'website' : '',
            'facebook' : '',
            'referralCode' : ''
        };

        $scope.signup = function() {
            if(!$scope.form.venue.$valid) {
                $scope.form.venue.$setDirty();
                $scope.venueAlerts.error('Please fill in all fields for your venue.');
            } else if(!$scope.form.user.$valid) {
                $scope.form.user.$setDirty();
                $scope.signupAlerts.error('Please agree to our terms of service and fill in all Name, Email, and Password fields.');
            } else if($scope.newUser.password !== $scope.newUser.passwordB) {
                $scope.form.user.$setDirty();
                $scope.signupAlerts.error('Passwords do not match.');
            } else {
                AuthService.venueSignup($scope.newUser).then(function (results) {
                    $log.debug(results);
                    
                    $scope.signupAlerts.success("Signup successful!  Please wait for confirmation page", 'signup');

                    $timeout(function () {
                        $scope.clearAlerts();
                        $window.top.location.href = 'http://www.triviajoint.com/registration-thank-you-page/';
                    }, 3000);
                }, function (error) {
                    $scope.signupAlerts.error(error);
                });
            }
        };

        $scope.facebookSignup = function() {
            if(!$scope.form.venue.$valid) {
                $scope.form.venue.$setDirty();
                $scope.venueAlerts.error('Please fill in all fields for your venue.');
            } else if(!$scope.newUser.acceptTerms) {
                AlertConfirmService.confirm('Do you agree to our <a href="http://www.triviajoint.com/terms-and-conditions/" target="_blank">Terms of Service</a>?', 'Terms of Service Agreement').result.then(function (resp) {
                    $scope.newUser.acceptTerms = true;
                    AuthService.venueFacebookSignup().then(function (resp) {
                        $log.debug(resp);
                        $scope.newUser = resp;

                        $scope.facebookAlerts.success("Facebook signup Successful!  Please wait for confirmation page", 'terms');

                        $timeout(function () {
                            $scope.clearAlerts();
                            $window.top.location.href = 'http://www.triviajoint.com/registration-thank-you-page/';
                        }, 3000);
                    }, function (err) {
                        $scope.facebookAlerts.error(err);
                    });
                }, function (err) {
                    $scope.facebookAlerts.error('Please accept the Terms of Service to signup.');
                });
            } else {
                AuthService.venueFacebookSignup().then(function (resp) {
                    $log.debug(resp);
                    $scope.newUser = resp;

                    $scope.facebookAlerts.success("Facebook signup Successful!  Please wait for confirmation page", 'terms');

                    $timeout(function () {
                        $scope.clearAlerts();
                        $window.top.location.href = 'http://www.triviajoint.com/registration-thank-you-page/';
                    }, 3000);
                }, function (err) {
                    $scope.facebookAlerts.error(err);
                });
            }
        };
        
        var passwordValidator = /^(?=.*\d)(?=.*[A-Za-z])[A-Za-z0-9_!@#$%^&*+=-]{8,100}$/;
        $scope.onChangeValidatePassword = function() {
            $scope.showPasswordRules = (!passwordValidator.test($scope.newUser.password));
            $scope.onChangeValidateConfirmPassword();
        };
        
        $scope.onChangeValidateConfirmPassword = function() {
            $scope.showPasswordMissmatch = ($scope.newUser.password !== $scope.newUser.passwordB);
        };
        
    }]);