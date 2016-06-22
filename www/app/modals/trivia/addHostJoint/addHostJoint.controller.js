'use strict';

/* @author  Rachel Carbone */

angular.module('app.modal.trivia.addHostJoint', [])
    .controller('TriviaAddHostJointModalCtrl', ['$scope',  '$uibModalInstance','$filter', 'AlertConfirmService', 'AuthService',
    function ($scope, $uibModalInstance,$filter, AlertConfirmService, AuthService) {
    /* Used to restrict alert bars */
    $scope.alertProxy = {};
    $scope.showPhoneValidation = false;
    /* Holds the add / edit form on the modal */
    $scope.form = {};

    $scope.saved = {
        'venue': '',
        'nameFirst': '',
        'nameLast': '',
        'address': '',
        'email': '',
        'address': '',
        'addressb': '',
        'city': '',
        'state': '',
        'zip': '',
        'phone': '',
        'phone_extension': '',
        'referrer': '',
        'acceptTerms': true,
        'triviaDay': '',
        'triviaTime': ''
    };

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
    $scope.getMode = function () {
        if ($scope.newMode) {
            return 'new';
        } else if ($scope.editMode) {
            return 'edit';
        } else {
            return 'view';
        }
    };
    $scope.setMode('new');

    /* Click event for the Add / New button */
    $scope.buttonNew = function () {
        var returnMsg = "";
        if (!$scope.form.modalForm.$valid) {
            $scope.form.modalForm.$setDirty();
            $scope.alertProxy.error('Please fill in all fields for trivia joint.');
        }
        else
        {
            $scope.saved.triviaTime = $filter('date')($scope.saved.triviaTimeDate, 'h:mm a');
            AuthService.hostVenueSignup($scope.saved).then(function (result) {
                var strToSend = {
                    'venueId': result.user.venueId,
                    'msg': 'Joint added successfully.'
                }
                $uibModalInstance.close(strToSend);
            }, function (error) {
                $scope.alertProxy.error(error);
            });
           
        }
    };
    $scope.buttonCancel = function() {
        $uibModalInstance.dismiss(false);
    };
    $scope.handlePhoneChangeEvent = function ($phone) {
        $scope.showPhoneValidation = false;
        if ($phone === undefined || $phone.length < 10) {
            $scope.showPhoneValidation = true;
        }
    }
}]);
