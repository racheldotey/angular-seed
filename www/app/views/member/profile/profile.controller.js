'use strict';

/* 
 * Member Profile Page
 * 
 * Controller for the member profile page used to view and edit a users profile.
 */

angular.module('app.member.profile', [])
    .controller('MemberProfileCtrl', ['$scope', '$log', 'UserSession', 'ApiRoutesUsers', 'USER_ROLES', '$filter', 'DTOptionsBuilder', 'DTColumnBuilder', 'DataTableHelper', 'ApiRoutesSimpleLists', 'TriviaModalService',
    function ($scope, $log, UserSession, ApiRoutesUsers, USER_ROLES, $filter, DTOptionsBuilder, DTColumnBuilder, DataTableHelper, ApiRoutesSimpleLists, TriviaModalService) {

        $scope.editGeneralMode = false;
        $scope.editPasswordMode = false;
        $scope.showPasswordRules = false;
        $scope.showPasswordMissmatch = false;
        $scope.editHostInformationMode = false;
        $scope.showPhoneValidation = false;
        $scope.isNewJointAddedInList=false;

        // for dropdownselected data
        $scope.VenuesDropDown = [];
        // for public venue list
        $scope.publicVenuesList = [];
        //for Joint Locations
        $scope.hostVenueList = [];
        $scope.dtInstance = {};
        $scope.dtOptions = DTOptionsBuilder
            .newOptions()
            .withBootstrap()
            .withDOM('<"row"<"col-sm-12 col-md-12"fr><"col-sm-12 add-space"t><"col-sm-6"l><"col-sm-6 text-right"i><"col-sm-12 text-center"p>>')
            .withOption('order', [0, 'desc'])
            .withPaginationType('full_numbers')
        /* Form Alert Proxy */
        $scope.generalFormAlerts = {};
        $scope.passwordFormAlerts = {};
        $scope.editHostInformationAlerts = {};
        $scope.generalPasswordupdateAlerts = {};
        $scope.alertHostVenue={};
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
            ApiRoutesSimpleLists.simplePublicVenuesList().then(function (result) {
                $scope.publicVenuesList = result;
            });
            ApiRoutesUsers.getHostUserInfo($scope.user).then(function (data) {
                if (!angular.isDefined(data.host)) {
                    //if user is given with host role only but no data exist in host table
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
                    $scope.userHostInfo.venueIds = [];
                }
                else {
                    //if host data is found that host information is shown ,and General tab is hide
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
                    $scope.userHostInfo.venueIds = data.host.venues.map(function (a) { return a.venue_id; });

                    if (data.host.venues != null && data.host.venues != undefined && (data.host.venues).length > 0)
                        $scope.hostVenueList = data.host.venues;

                    //console.log("data=" + JSON.stringify($scope.hostVenueList));
                    //console.log("\n editing=" + JSON.stringify($scope.userHostInfo));
                }
            }, function (error) {
                $scope.isUserRoleHost = false;
                $log.error('ERROR getHostUserInfo User: ', error);
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

        /* Click event for the Delete Venue button */
        $scope.buttonDeleteHostVenue = function (venueId) {
            var hostData = {
                'hostId':  $scope.userHostInfo.hostId,
                'venueId': venueId
            }
            ApiRoutesUsers.deleteHostVenue(hostData).then(function (result) {
                $scope.hostVenueList = $scope.hostVenueList.filter(function (obj) {
                    return obj.venue_id != venueId;
                });
                $scope.userHostInfo.venueIds = $scope.userHostInfo.venueIds.filter(function (obj) {
                    return obj != venueId;
                });
                $scope.alertHostVenue.success("Venue has been removed,Please Click on Save Button to save it.");

            }, function (error) {
                $scope.alertHostVenue.error(error);
                $log.info(error);
            });
            
        }
        /* Click event for adding Selected Joint to host */
        $scope.buttonAddHostVenue = function () {
            var assignTohostVenueListArray = {
                'venue_id': '',
                'name': '',
                'triviaDay': '',
                'triviaTime': '',
            }
            if (!angular.isDefined($scope.VenuesDropDown.venue.value)) {
                $scope.alertHostVenue.error("Please select atlest one joints from the Existing Joints ");
                return;
            }
            var venueId = $scope.VenuesDropDown.venue.value.id;
            if (($scope.hostVenueList).length > 0) {
                var isContains = $scope.hostVenueList.filter(function (obj) {
                    return obj.venue_id == venueId;
                })[0];
                if (isContains != undefined && isContains.venue_id != null && isContains.venue_id != undefined) {
                    $scope.alertHostVenue.error("Selected joint is already exists in your list.Please select new joint");
                }
                else {
                    assignTohostVenueListArray.venue_id = $scope.VenuesDropDown.venue.value.id;
                    assignTohostVenueListArray.name = $scope.VenuesDropDown.venue.value.name;
                    assignTohostVenueListArray.triviaDay = $scope.VenuesDropDown.venue.value.triviaDay;
                    assignTohostVenueListArray.triviaTime = $scope.VenuesDropDown.venue.value.triviaTime;
                    $scope.hostVenueList.push(assignTohostVenueListArray);
                    $scope.userHostInfo.venueIds.push(venueId);
                    $scope.isNewJointAddedInList = true;
                    $scope.alertHostVenue.success("Joint is added in your selection (Note - Change takes effect only after click on Save button for saving the joint)");
                }
            }
            else {
                assignTohostVenueListArray.venue_id = $scope.VenuesDropDown.venue.value.id;
                assignTohostVenueListArray.name = $scope.VenuesDropDown.venue.value.name;
                assignTohostVenueListArray.triviaDay = $scope.VenuesDropDown.venue.value.triviaDay;
                assignTohostVenueListArray.triviaTime = $scope.VenuesDropDown.venue.value.triviaTime;
                $scope.hostVenueList.push(assignTohostVenueListArray);
                $scope.userHostInfo.venueIds.push(venueId);
                $scope.isNewJointAddedInList = true;
                $scope.alertHostVenue.success("Joint is added in your selection (Note - Change takes effect only after click on Save button for saving the joint)");
            }
        }

        $scope.buttonOpenNewVenueModal = function () {
            var modalInstance = TriviaModalService.openAddHostJoint();
            modalInstance.result.then(function (data) {
                $scope.alertHostVenue.success(data.msg);
                ApiRoutesSimpleLists.simplePublicVenuesList().then(function (result) {
                    $scope.publicVenuesList = result;
                    if (data.venueId) {
                        for (var t = 0; t < $scope.publicVenuesList.length; t++) {
                            if (parseInt($scope.publicVenuesList[t].id) === parseInt(data.venueId)) {
                                $scope.VenuesDropDown.venue.value = $scope.publicVenuesList[t];
                                break;
                            }
                        }
                    }
                });
            });
        };
        //region for editing grid
        $scope.selectedHostVenue = {};
        $scope.defaultDayTime = {
            'triviaDay': '',
            'triviaTimeDate': ''
        };
        $scope.getDisplayTemplate = function (hostVenue) {
            if (hostVenue.venue_id === $scope.selectedHostVenue.venue_id) return 'edittemplate';
            else return 'displaytemplate';
        };



        $scope.editHostVenue = function (hostVenue) {
            var currentDateTime = new Date();
            var minutes = currentDateTime.getMinutes();
            var hours = currentDateTime.getHours();
            var m = (parseInt((minutes + 7.5) / 15) * 15) % 60;
            var h = minutes > 52 ? (hours === 23 ? 0 : ++hours) : hours;
            currentDateTime.setHours(h);
            currentDateTime.setMinutes(m);
            if (hostVenue.triviaTime == undefined || hostVenue.triviaTime == null) {
                $scope.defaultDayTime.triviaTimeDate = currentDateTime;
            }
            else {
                $scope.defaultDayTime.triviaTimeDate = $scope.parseTime(hostVenue.triviaTime);
            }
            var days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            var day = days[currentDateTime.getDay()];
            if (hostVenue.triviaDay == undefined || hostVenue.triviaDay == null) {
                $scope.defaultDayTime.triviaDay = day;
            }
            else {
                $scope.defaultDayTime.triviaDay = hostVenue.triviaDay;
            }

            $scope.selectedHostVenue = angular.copy(hostVenue);
        };
        $scope.resetDisplayTemplate = function () {
            $scope.selectedHostVenue = {};
        };
        $scope.updateHostVenue = function (idx) {
            if ($scope.defaultDayTime.triviaDay == undefined || $scope.defaultDayTime.triviaTimeDate == undefined) {
                $scope.alertHostVenue.error("Trivia Day and Time both are required.");
                return;
            }
            else {
                var hostData = {
                    'hostId': $scope.userHostInfo.hostId,
                    'venueId': $scope.selectedHostVenue.venue_id,
                    'triviaDay': $scope.defaultDayTime.triviaDay,
                    'triviaTime': $filter('date')($scope.defaultDayTime.triviaTimeDate, 'h:mm a'),
                }
                ApiRoutesUsers.updateHostVenue(hostData).then(function (result) {
                    $scope.selectedHostVenue.triviaTime = $filter('date')($scope.defaultDayTime.triviaTimeDate, 'h:mm a');
                    $scope.selectedHostVenue.triviaDay = $scope.defaultDayTime.triviaDay;
                    $scope.hostVenueList[idx] = angular.copy($scope.selectedHostVenue);
                    $scope.resetDisplayTemplate();
                    $scope.alertHostVenue.success("Venue information modified successfully.");
                }, function (error) {
                    $scope.alertHostVenue.error = error;
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

    }]);