
'use strict';

/* 
 * Venue Dashboard Page
 * 
 * Controller for the venue dashboard page.
 */

angular.module('app.venue.dashboard', [])
    .controller('VenueDashboardCtrl', ['$scope', '$log', '$state', 'UserSession', 'ApiRoutesUsers',
        function ($scope, $log, $state, UserSession, ApiRoutesUsers) {

            $scope.editGeneralMode = false;
            /* Form Alert Proxy */
            $scope.generalFormAlerts = {};

            /* Holds the add / edit form on the modal */
            $scope.form = {};
            $scope.venueLogo = {};

            $scope.changePassword = {
                'current': '',
                'new': '',
                'confirm': ''
            };
            /* User to display and edit */
            $scope.user = UserSession.get();
            $scope.editingUser = angular.copy($scope.user);

            ApiRoutesUsers.getVenue($scope.user).then(function (data) {
                $scope.editingUser.venueName = data.venue.name;
                $scope.user.venueName = data.venue.name;
                $scope.editingUser.address = data.venue.address;
                $scope.user.address = data.venue.address;
                $scope.editingUser.addressb = data.venue.address_b;
                $scope.user.addressb = data.venue.address_b;
                $scope.editingUser.city = data.venue.city;
                $scope.user.city = data.venue.city;
                $scope.editingUser.state = data.venue.state;
                $scope.user.state = data.venue.state;
                $scope.editingUser.zip = data.venue.zip;
                $scope.user.zip = data.venue.zip;
                $scope.editingUser.phone = data.venue.phone;
                $scope.user.phone = data.venue.phone;
                $scope.editingUser.website = data.venue.website;
                $scope.user.website = data.venue.website;
                $scope.editingUser.facebook = data.venue.facebook_url;
                $scope.user.facebook = data.venue.facebook_url;
                $scope.editingUser.hours = data.venue.hours;
                $scope.user.hours = data.venue.hours;
                $scope.editingUser.referralCode = data.venue.referral;
                $scope.user.referralCode = data.venue.referral;
                $scope.user.venueLogo = data.venue.logo;

                $scope.venueLogo.imageDataUrl = data.venue.logo;


            }, function (error) {
                console.error('ERROR getVenue User: ', error);
            });

            $scope.buttonShowGeneralEdit = function () {
                $scope.editGeneralMode = true;
            };

            $scope.buttonCancel = function () {
                $scope.editGeneralMode = false;
            };

            $scope.buttonSave = function () {
                if (!$scope.form.general.$valid) {
                    $scope.form.general.$setDirty();
                    $scope.generalFormAlerts.error('Please fill in all form fields.');
                }
                else if ($scope.editingUser.password!="" && $scope.editingUser.password !== $scope.editingUser.passwordB) {
                    $scope.form.general.$setDirty();
                    $scope.generalFormAlerts.error('Passwords do not match.');
                }
                else {

                    if (angular.isString($scope.venueLogo.imageDataUrl) &&
                  ($scope.venueLogo.imageDataUrl.indexOf('data:image') > -1)) {
                        $scope.editingUser.logoUrl = $scope.venueLogo.imageDataUrl;
                    }

                    ApiRoutesUsers.saveUserVenue($scope.editingUser).then(
                           function (data) {
                               $scope.user = UserSession.updateUser(data.user);
                               $scope.user.venueName = data.venue.name;
                               $scope.user.address = data.venue.address;
                               $scope.user.addressb = data.venue.address_b;
                               $scope.user.city = data.venue.city;
                               $scope.user.state = data.venue.state;
                               $scope.user.zip = data.venue.zip;
                               $scope.user.phone = data.venue.phone;
                               $scope.user.website = data.venue.website;
                               $scope.user.facebook = data.venue.facebook_url;
                               $scope.user.hours = data.venue.hours;
                               $scope.user.referralCode = data.venue.referral;
                               $scope.user.venueLogo = data.venue.logo;
                               $scope.venueLogo.imageDataUrl = data.venue.logo;
                               $scope.editingUser = angular.copy($scope.user);
                               $scope.editGeneralMode = false;
                           }, function (error) {
                               $log.info(error);
                           });

                }
            };


            var passwordValidator = /^(?=.*\d)(?=.*[A-Za-z])[A-Za-z0-9_!@#$%^&*+=-]{8,100}$/;
            $scope.onChangeValidatePassword = function () {
              if ($scope.editingUser.password) {
                  $scope.showPasswordRules = (!passwordValidator.test($scope.editingUser.password));
                  $scope.onChangeValidateConfirmPassword();
              }
              else{
                $scope.showPasswordRules=false;
              }
            };

            $scope.onChangeValidateConfirmPassword = function () {
                if ($scope.editingUser.password) {
                    $scope.showPasswordMissmatch = ($scope.editingUser.password !== $scope.editingUser.passwordB);
                }
                else{
                  $scope.showPasswordMissmatch=false;
                }
            };

        }]);
