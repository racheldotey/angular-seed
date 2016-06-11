'use strict';

/* 
 * Member Profile Page
 * 
 * Controller for the member profile page used to view and edit a users profile.
 */

angular.module('app.member.profile', [])
    .controller('MemberProfileCtrl', ['$scope', '$log', 'UserSession', 'ApiRoutesUsers', 'USER_ROLES',
    function ($scope, $log, UserSession, ApiRoutesUsers, USER_ROLES) {

        $scope.editGeneralMode = false;
        $scope.editPasswordMode = false;
        $scope.showPasswordRules = false;
        $scope.showPasswordMissmatch = false;
        $scope.editHostInformationMode = false;
        $scope.showPhoneValidation = false;
        /* Form Alert Proxy */
        $scope.generalFormAlerts = {};
        $scope.passwordFormAlerts = {};
        $scope.editHostInformationAlerts = {};
        $scope.generalPasswordupdateAlerts = {};
        /* Holds the add / edit form on the modal */
        $scope.form = {};
        $scope.changePassword = {
            'current': '',
            'new': '',
            'confirm': ''
        };

        /* User to display and edit */
        $scope.user = UserSession.get();
        $scope.editingUser = angular.copy($scope.user);

        $scope.userHostInfo = [];
        $scope.isUserRoleHost = false;

        $scope.buttonShowGeneralEdit = function () {
            $scope.editGeneralMode = true;
            $scope.editHostInformationMode = false;
        };
        $scope.buttonShowGeneralEditHost = function () {
            $scope.editGeneralMode = false;
            $scope.editHostInformationMode = true;
        };
        //bind host information in form


        if ($scope.user.roles.indexOf(USER_ROLES.host) > -1) {
            $scope.isUserRoleHost = true;
            ApiRoutesUsers.getHostUserInfo($scope.user).then(function (data) {
                if (!angular.isDefined(data.host)) {
                    //as use is assigned role of host only, but no entry for this user on host table found
                    //so,showing only Genral probile,
                    // $scope.isUserRoleHost = false;
                    $scope.userHostInfo.hostId = '';
                    $scope.userHostInfo.userId = $scope.user.id;
                    $scope.userHostInfo.nameFirst = $scope.user.nameFirst;
                    $scope.userHostInfo.nameLast = $scope.user.nameLast;
                    $scope.userHostInfo.email = $scope.user.email;
                    $scope.userHostInfo.phone = $scope.user.phone;
                    $scope.userHostInfo.phone_extension = '';
                    $scope.userHostInfo.host_address = '';
                    $scope.userHostInfo.host_addressb ='';
                    $scope.userHostInfo.host_city = '';
                    $scope.userHostInfo.host_state ='';
                    $scope.userHostInfo.host_zip ='';
                    $scope.userHostInfo.host_website = '';
                    $scope.userHostInfo.host_facebook = '';
                }
                else {
                    //if host data is found that host information is shown ,and General tab is hide
                   // $scope.isUserRoleHost = true;
                    $scope.userHostInfo.hostId = data.host.id;
                    $scope.userHostInfo.userId = data.host.trv_users_id;
                    $scope.userHostInfo.nameFirst = data.host.nameFirst;
                    $scope.userHostInfo.nameLast = data.host.nameLast;
                    $scope.userHostInfo.email = data.host.email;
                    $scope.userHostInfo.phone = data.host.phone;
                    $scope.userHostInfo.phone_extension = data.host.phoneExtension;
                    $scope.userHostInfo.host_address = data.host.address;
                    $scope.userHostInfo.host_addressb = data.host.addressb;
                    $scope.userHostInfo.host_city = data.host.city;
                    $scope.userHostInfo.host_state = data.host.state;
                    $scope.userHostInfo.host_zip = data.host.zip;
                    $scope.userHostInfo.host_website = data.host.website;
                    $scope.userHostInfo.host_facebook = data.host.facebook;
                }
            }, function (error) {
                $scope.isUserRoleHost = false;
                console.error('ERROR getHostUserInfo User: ', error);
            });
        }

        /* Click event for the Save button */
        $scope.buttonSave = function () {
            if (!$scope.form.general.$valid) {
                $scope.form.general.$setDirty();
                $scope.generalFormAlerts.error('Please fill in all form fields.');
            } else {
                ApiRoutesUsers.saveUser($scope.editingUser).then(
                    function (result) {
                        $scope.user = UserSession.updateUser(result.user);

                        $scope.editGeneralMode = false;
                        $scope.generalFormAlerts.success('Profile saved.');
                    }, function (error) {
                        $log.info(error);
                        $scope.passwordFormAlerts.error('Invalid user. Check your parameters and try again.');
                    });
            }
        };

        $scope.buttonShowChangePassword = function () {
            $scope.editPasswordMode = true;
            $scope.editHostInformationMode = false;
        };

        /* Click event for the Add / New button */
        $scope.buttonChangePassword = function () {
            if (!$scope.form.password.$valid) {
                $scope.form.password.$setDirty();
                $scope.passwordFormAlerts.error('Please fill in all form fields.');
            } else if ($scope.changePassword.new !== $scope.changePassword.confirm) {
                $scope.form.password.$setDirty();
                $scope.passwordFormAlerts.error('Passwords must match.');
            }  else if($scope.showPasswordRules) {
                $scope.form.password.$setDirty();
                $scope.passwordFormAlerts.error('Your new password must be at least 8 characters long and contain at least one letter and one number.');
            } else {
                $scope.changePassword.userId = $scope.user.id;
                ApiRoutesUsers.changePassword($scope.changePassword).then(
                    function (result) {
                        $scope.generalPasswordupdateAlerts.success(result.msg);
                        $scope.editPasswordMode = false;
                        $scope.generalFormAlerts.success('Password saved.');
                        $scope.changePassword = {
                            'current' : '',
                            'new' : '',
                            'confirm' : ''
                        };
                    }, function (error) {
                        $log.info(error);
                        $scope.changePassword.current = '';
                        $scope.passwordFormAlerts.error('Invalid current password. Could not update user password.');
                    });
            }
        };

        var passwordValidator = /^(?=.*\d)(?=.*[A-Za-z])[A-Za-z0-9_!@#$%^&*+=-]{8,55}$/;
        $scope.onChangeValidatePassword = function () {
            $scope.showPasswordRules = (!passwordValidator.test($scope.changePassword.new));
            $scope.onChangeValidateConfirmPassword();
        };

        $scope.onChangeValidateConfirmPassword = function () {
            $scope.showPasswordMissmatch = ($scope.changePassword.new !== $scope.changePassword.confirm);
        };
        $scope.buttonShowHostInformation = function () {
            $scope.editHostInformationMode = true;
        };
        /* Click event for saving host information*/
        $scope.buttonSaveHostInformation = function () {
            if (!$scope.form.host.$valid) {
                $scope.form.host.$setDirty();
                if ($scope.form.host.website.$error.url) {
                    $scope.editHostInformationAlerts.error('Invalid website url provided. Check your parameters and try again.');
                }
                else if ($scope.form.host.facebook.$error.pattern) {
                    $scope.editHostInformationAlerts.error('Invalid facebook url provided. Check your parameters and try again..');
                }
                else {
                    $scope.editHostInformationAlerts.error('Please fill in all fields for your host.');
                }
                //$scope.editHostInformationAlerts.error('Please fill in all form fields.');
            } else {
                ApiRoutesUsers.saveHostUserInfo($scope.userHostInfo).then(
                    function (result) {
                        $scope.user = UserSession.updateUser(result.user);
                        $scope.editHostInformationMode = false;
                        $scope.editHostInformationAlerts.success(result.msg);
                        $scope.editGeneralMode = false;
                    }, function (error) {
                        $log.info(error);
                    });
            }
        }
        $scope.buttonCancel = function () {
            $scope.editGeneralMode = false;
            $scope.editPasswordMode = false;
            $scope.showPasswordRules = false;
            $scope.showPasswordMissmatch = false;
            $scope.editHostInformationMode = false;
            $scope.showPhoneValidation = false;
            $scope.changePassword = {
                'current': '',
                'new': '',
                'confirm': ''
            };
            $scope.editingUser = angular.copy($scope.user);
        };
        $scope.handlePhoneChangeEvent = function ($phone) {
            $scope.showPhoneValidation = false;
            if ($phone === undefined || $phone.length < 10) {
                $scope.showPhoneValidation = true;
            }
        }

    }]);