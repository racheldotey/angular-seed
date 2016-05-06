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

        if (angular.isDefined(editing.id)) {
            $scope.setMode('view');
        } else {
            $scope.setMode('new');
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
        

        // Hold venue logo
        $scope.venueLogo = {};
        $scope.savedImageDataUrl = $scope.saved.logo;

        $scope.saved.triviaTimeDate = new Date();

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
        }
    }]);