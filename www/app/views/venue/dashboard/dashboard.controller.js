
'use strict';

/* 
 * Venue Dashboard Page
 * 
 * Controller for the venue dashboard page.
 */

angular.module('app.venue.dashboard', [])
    .controller('VenueDashboardCtrl', ['$scope', '$log', '$state', 'UserSession', 'ApiRoutesUsers', '$filter',
function ($scope, $log, $state, UserSession, ApiRoutesUsers, $filter) {

    $scope.editGeneralMode = false;
    $scope.showPhoneValidation = false;
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
        //console.log(JSON.stringify(data));
        $scope.user.venueName = data.venue.name;
        $scope.user.address = data.venue.address;
        $scope.user.addressb = data.venue.address_b;
        $scope.user.city = data.venue.city;
        $scope.user.state = data.venue.state;
        $scope.user.zip = data.venue.zip;
        $scope.user.phone = data.venue.phone;
        $scope.user.phone_extension = data.venue.phone_extension;

        $scope.user.website = data.venue.website;
        $scope.user.facebook = data.venue.facebook_url;
        $scope.user.hours = data.venue.hours;
        $scope.user.triviaDay = data.venue.trivia_day;
        $scope.user.triviaTime = data.venue.trivia_time;

        $scope.user.triviaTimeDate = $scope.parseTime(data.venue.trivia_time);

        $scope.user.referralCode = data.venue.referral;
        $scope.user.venueLogo = data.venue.logo;
        $scope.venueLogo.imageDataUrl = data.venue.logo;

        $scope.editingUser = angular.copy($scope.user);


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
        else if ($scope.editingUser.password != "" && $scope.editingUser.password !== $scope.editingUser.passwordB) {
            $scope.form.general.$setDirty();
            $scope.generalFormAlerts.error('Passwords do not match.');
        }
        else {

            if (angular.isString($scope.venueLogo.imageDataUrl) &&
          ($scope.venueLogo.imageDataUrl.indexOf('data:image') > -1)) {
                $scope.editingUser.logoUrl = $scope.venueLogo.imageDataUrl;
            }
            $scope.editingUser.triviaTime = $filter('date')($scope.editingUser.triviaTimeDate, 'h:mm a');

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
                       $scope.user.phone_extension = data.venue.phone_extension;
                       $scope.user.website = data.venue.website;
                       $scope.user.facebook = data.venue.facebook_url;
                       $scope.user.triviaDay = data.venue.trivia_day;
                       $scope.user.triviaTime = data.venue.trivia_time;
                       $scope.user.triviaTimeDate = $scope.parseTime(data.venue.trivia_time);
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

    $scope.parseTime = function (timeString) {
        if (timeString == '' || timeString == undefined) return null;

        var time = timeString.match(/(\d+)(:(\d\d))?\s*(p?)/i);
        if (time == null) return null;

        var hours = parseInt(time[1], 10);
        if (hours == 12 && !time[4]) {
            hours = 0;
        }
        else {
            hours += (hours < 12 && time[4]) ? 12 : 0;
        }
        var d = new Date();
        d.setHours(hours);
        d.setMinutes(parseInt(time[3], 10) || 0);
        d.setSeconds(0, 0);
        return d;
    };

    var passwordValidator = /^(?=.*\d)(?=.*[A-Za-z])[A-Za-z0-9_!@#$%^&*+=-]{8,100}$/;
    $scope.onChangeValidatePassword = function () {
        if ($scope.editingUser.password) {
            $scope.showPasswordRules = (!passwordValidator.test($scope.editingUser.password));
            $scope.onChangeValidateConfirmPassword();
        }
        else {
            $scope.showPasswordRules = false;
        }
    };

    $scope.onChangeValidateConfirmPassword = function () {
        if ($scope.editingUser.password) {
            $scope.showPasswordMissmatch = ($scope.editingUser.password !== $scope.editingUser.passwordB);
        }
        else {
            $scope.showPasswordMissmatch = false;
        }
    };

    $scope.handlePhoneChangeEvent = function ($phone) {
        $scope.showPhoneValidation = false;
        if ($phone === undefined || $phone.length < 10) {
            $scope.showPhoneValidation = true;
        }
    }

}]);
