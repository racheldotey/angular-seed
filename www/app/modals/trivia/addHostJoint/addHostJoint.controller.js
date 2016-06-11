'use strict';

/* @author  Rachel Carbone */

angular.module('app.modal.trivia.addHostJoint', [])
    .controller('TriviaAddHostJointModalCtrl', ['$scope',  '$uibModalInstance','$filter', 'AlertConfirmService', 'AuthService',
    function ($scope, $uibModalInstance,$filter, AlertConfirmService, AuthService) {
    /* Used to restrict alert bars */
    $scope.alertProxy = {};
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
        if (!$scope.form.modalForm.$valid) {
            $scope.form.modalForm.$setDirty();
            $scope.alertProxy.error('Please fill in all fields for trivia joint.');
        }
        else
        {
            $scope.saved.triviaTime = $filter('date')($scope.saved.triviaTimeDate, 'h:mm a');
            $uibModalInstance.close($scope.saved);
        }
    };
    $scope.buttonCancel = function() {
        $uibModalInstance.dismiss(false);
    };
}]);