'use strict';

/* @author  Rachel Carbone */

angular.module('app.modal.trivia.editHost', [])
    .controller('TriviaEditHostModalCtrl', ['$scope',  '$uibModalInstance', 'AlertConfirmService', 'editing', 'ApiRoutesGames', '$filter',
    function ($scope,  $uibModalInstance, AlertConfirmService, editing, ApiRoutesGames, $filter) {
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

        $scope.saved = {
            'id': '',
            'trv_users_id': '',
            'nameFirst': '',
            'nameLast': '',
            'email': '',
            'referrer': '',
            'phone': '',
            'phone_extension': '',
            'host_address': '',
            'host_addressb': '',
            'host_city': '',
            'host_state': '',
            'host_zip': '',
            'host_website': '',
            'host_facebook': '',
            "created": '',
            "createdBy": '',
            "updatedBy": '',
            "disabled": '',
            "triviaDay": '',
            "triviaTime": '',
        };
        /* Save for resetting purposes */
        if (angular.isDefined(editing.id))
        {
            $scope.saved = {
                'hostId': editing.id,
                'userId': editing.trv_users_id,
                'nameFirst': editing.nameFirst,
                'nameLast': editing.nameLast,
                'email': editing.email,
                'referrer': editing.referrer,
                'phone': editing.phone,
                'phone_extension': editing.phoneExtension,
                'host_address': editing.address,
                'host_addressb': editing.addressb,
                'host_city': editing.city,
                'host_state': editing.state,
                'host_zip': editing.zip,
                'host_website': editing.website,
                'host_facebook': editing.facebook,
                "created": editing.created,
                "createdBy": editing.createdBy,
                "updatedBy": editing.updatedBy,
                "disabled": editing.disabled,
            };
        }
       
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
            ApiRoutesGames.saveHost($scope.editing).then(
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

        $scope.buttonChangeDisabled = function() {
            // Changing the disable flage to a new value
            if($scope.saved.disabled !== $scope.editing.disabled) {
                if($scope.editing.disabled === 'true') {
                    AlertConfirmService.confirm('Are you sure you want to disable this joint? Games will no longer be hosted at the joint. (Note - Change takes effect only after saving the joint.)')
                        .result.then(function () { }, function (error) {
                            $scope.editing.disabled = 'false';
                        });
                } else {
                    AlertConfirmService.confirm('Are you sure you want to enable this joint? Games will now be able to be held at this joint. (Note - Change takes effect only after saving the joint.)')
                        .result.then(function () {  }, function (error) {
                            $scope.editing.disabled = 'true';
                        });
                }
            } else {
                var userState = ($scope.editing.disabled === 'true') ? "The joint is already disabled and will remain disabled after save. Games cannot be hosted at this joint." :
                        "The joint is already enabled and will remain enabled after save. Games can be hosted at this joint.";
                var alertTitle = ($scope.editing.disabled === 'true') ? "Host is Disabled." : "Host is Enabled.";
                AlertConfirmService.alert(userState, alertTitle);
            }
        };
    }]);