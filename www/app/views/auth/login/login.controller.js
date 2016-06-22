'use strict';

/* 
 * Login Page
 * 
 * Controller for the login page.
 */

angular.module('app.auth.login', [])
    .controller('AuthLoginCtrl', ['$rootScope', '$scope', '$state', '$timeout', 'AuthService', 'ModalService',
    function ($rootScope, $scope, $state, $timeout, AuthService,ModalService) {
        
        
    /* Used to restrict alert bars */
    $scope.alertProxy = {};

        if(angular.isDefined($rootScope.authRedirectErrorData) && 
                angular.isDefined($rootScope.authRedirectErrorData.msg)) {
                $timeout(function() {
                    $scope.alertProxy.error($rootScope.authRedirectErrorData.msg);
                }, 500);
        }
        
    $scope.results = [];

    $scope.$state = $state;
    $scope.form = {};

    $scope.credentials = {
        'email' : 'rachellcarbone@gmail.com',
        'password' : 'password1',
        'remember' : false
    };

    $scope.credentials = {
        'email' : '',
        'password' : '',
        'remember' : false
    };

    $scope.buttonLogin = function() {
        $scope.$broadcast('show-errors-check-validity');

        if($scope.form.login.$valid) {
            AuthService.login($scope.credentials).then(function(results) {
            }, function(error) {
                $scope.alertProxy.error(error);
            });
        } else {
            $scope.form.login.$setDirty();
            $scope.alertProxy.error('Please fill in both fields.');
        }
    };

    $scope.buttonFacebookLogin = function() {
        AuthService.facebookLogin($scope.credentials.remember).then(function (results) {
        }, function (error) {
            $scope.alertProxy.error(error);
        });
    };
    // Forgot Password Modal
    $scope.buttonOpenForgotPasswordModal = function (credentials) {
        var emailAddress = '';
        if (typeof (credentials) !== undefined && typeof (credentials.email) !== undefined) {
            emailAddress = credentials.email;
        }
        var modalInstance = ModalService.openForgotPassword(emailAddress);;
        modalInstance.result.then(function (result) {
            $scope.alertProxy.success(result.msg);
        });
    };
}]);