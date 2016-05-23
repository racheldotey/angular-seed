'use strict';

/* @author  Rachel Carbone */

angular.module('app.modal.trivia.editVenue', [])
    .controller('TriviaEditVenueModalCtrl', ['$scope', '$uibModalInstance', 'AlertConfirmService', 'editing', 'ApiRoutesGames', '$filter',
    function ($scope, $uibModalInstance, AlertConfirmService, editing, ApiRoutesGames, $filter) {
        /* Used to restrict alert bars */
        $scope.alertProxy = {};

        /* Holds the add / edit form on the modal */
        $scope.form = {};
        $scope.showPhoneValidation = false;

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


        /* Modal Mode */
        $scope.setMode = function (type) {
            $scope.viewMode = false;
            $scope.newMode = false;
            $scope.editMode = false;

            switch (type) {
                case 'new':
                    $scope.newMode = true;
                    $scope.editMode = true;
                    break;
                case 'edit':
                    $scope.editMode = true;
                    break;
                case 'view':
                default:
                    $scope.viewMode = true;
                    break;
            }
        };

        /* Save for resetting purposes */
        $scope.saved = (angular.isDefined(editing.id)) ? angular.copy(editing) : {
            'venueName': '',
            'phone': '',
            'phoneExtension': '',
            'address': '',
            'addressb': '',
            'city': '',
            'state': '',
            'zip': '',
            'website': '',
            'facebook': '',
            'triviaDay': '',
            'triviaTime': '',
            'referralCode': ''
        };

        if (angular.isDefined(editing.id)) {
            $scope.setMode('view');
            $scope.saved.triviaTimeDate = $scope.parseTime(editing.triviaTime);
        } else {
            $scope.setMode('new');
            // nearest quarter hour
            var currentDateTime = new Date();
            var minutes = currentDateTime.getMinutes();
            var hours = currentDateTime.getHours();
            var m = (parseInt((minutes + 7.5) / 15) * 15) % 60;
            var h = minutes > 52 ? (hours === 23 ? 0 : ++hours) : hours;
            currentDateTime.setHours(h);
            currentDateTime.setMinutes(m);

            $scope.saved.triviaTimeDate = currentDateTime;

            var days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            var day = days[currentDateTime.getDay()];
            $scope.saved.triviaDay = day;
        }

     

        $scope.getMode = function () {
            if ($scope.newMode) {
                return 'new';
            } else if ($scope.editMode) {
                return 'edit';
            } else {
                return 'view';
            }
        };

     
        // Hold venue logo
        $scope.venueLogo = {};
        $scope.savedImageDataUrl = $scope.saved.logo;

        $scope.saved.disabled = (angular.isUndefined(editing.disabled) || editing.disabled === null || !editing.disabled) ? 'false' : 'true';

        /* Item to display and edit */
        $scope.editing = angular.copy($scope.saved);

        /* Click event for the Add / New button */
        $scope.buttonNew = function () {
            if (!$scope.form.modalForm.$valid) {
                $scope.form.modalForm.$setDirty();
                $scope.alertProxy.error('Please fill in all fields for the trivia joint.');
            }
            else {
                if ($scope.venueLogo.file && $scope.venueLogo.imageDataUrl.indexOf('data:image') > -1) {
                    $scope.editing.logo = $scope.venueLogo.imageDataUrl;
                } else if (angular.isString($scope.savedImageDataUrl) &&
                        ($scope.savedImageDataUrl.indexOf('data:image') > -1)) {
                    $scope.editing.logo = $scope.savedImageDataUrl;
                }
                $scope.editing.triviaTime = $filter('date')($scope.editing.triviaTimeDate, 'h:mm a');
                $scope.editing.venueName = $scope.editing.venue;
                ApiRoutesGames.addVenue($scope.editing).then(
                    function (result) {
                        $uibModalInstance.close(result.msg);
                    }, function (error) {
                        $scope.alertProxy.error(error);
                    });
            }
        };

        /* Click event for the Save button */
        $scope.buttonSave = function () {
            if ($scope.venueLogo.file && $scope.venueLogo.imageDataUrl.indexOf('data:image') > -1) {
                $scope.editing.logo = $scope.venueLogo.imageDataUrl;
            } else if (angular.isString($scope.savedImageDataUrl) &&
                    ($scope.savedImageDataUrl.indexOf('data:image') > -1)) {
                $scope.editing.logo = $scope.savedImageDataUrl;
            }
            $scope.editing.triviaTime = $filter('date')($scope.editing.triviaTimeDate, 'h:mm a');
            $scope.editing.venueName = $scope.editing.venue;
            ApiRoutesGames.saveVenue($scope.editing).then(
                    function (result) {
                        $uibModalInstance.close(result.msg);
                    }, function (error) {
                        $scope.alertProxy.error(error);
                    });
        };

        /* Click event for the Cancel button */
        $scope.buttonCancel = function () {
            var mode = $scope.getMode();

            switch (mode) {
                case 'edit':
                    $scope.setMode('view');
                    break;
                case 'new':
                case 'view':
                default:
                    $uibModalInstance.dismiss(false);
                    break;
            };
        };

        /* Click event for the Edit button*/
        $scope.buttonEdit = function () {
            $scope.setMode('edit');
        };

        /* Click event for the Cancel button */
        $scope.buttonCancel = function () {
            $uibModalInstance.dismiss(false);
        };

        $scope.handlePhoneChangeEvent = function ($phone) {
            $scope.showPhoneValidation = false;
            if ($phone === undefined || $phone.length < 10) {
                $scope.showPhoneValidation = true;
            }
        };

        $scope.buttonChangeDisabled = function () {
            // Changing the disable flage to a new value
            if ($scope.saved.disabled !== $scope.editing.disabled) {
                if ($scope.editing.disabled === 'true') {
                    AlertConfirmService.confirm('Are you sure you want to disable this joint? Games will no longer be hosted at the joint. (Note - Change takes effect only after saving the joint.)')
                        .result.then(function () { }, function (error) {
                            $scope.editing.disabled = 'false';
                        });
                } else {
                    AlertConfirmService.confirm('Are you sure you want to enable this joint? Games will now be able to be held at this joint. (Note - Change takes effect only after saving the joint.)')
                        .result.then(function () { }, function (error) {
                            $scope.editing.disabled = 'true';
                        });
                }
            } else {
                var userState = ($scope.editing.disabled === 'true') ? "The joint is already disabled and will remain disabled after save. Games cannot be hosted at this joint." :
                        "The joint is already enabled and will remain enabled after save. Games can be hosted at this joint.";
                var alertTitle = ($scope.editing.disabled === 'true') ? "Joint is Disabled." : "Joint is Enabled.";
                AlertConfirmService.alert(userState, alertTitle);
            }
        };
    }]);