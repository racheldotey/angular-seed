'use strict';

/* @author  Rachel Carbone */

angular.module('app.modal.trivia.editHost', [])
    .controller('TriviaEditHostModalCtrl', ['$scope', '$uibModalInstance', 'AlertConfirmService', 'editing', 'ApiRoutesGames', '$filter', 'DTOptionsBuilder', 'DTColumnBuilder', 'DataTableHelper', 'ApiRoutesSimpleLists', 'ApiRoutesUsers', 'TriviaModalService', 'AuthService',
    function ($scope, $uibModalInstance, AlertConfirmService, editing, ApiRoutesGames, $filter, DTOptionsBuilder, DTColumnBuilder, DataTableHelper, ApiRoutesSimpleLists, ApiRoutesUsers, TriviaModalService, AuthService) {
        /* Used to restrict alert bars */
        $scope.alertProxy = {};
        $scope.alertHostVenue = {};
        $scope.signupAlerts = {};
        $scope.signupJoinSelectionAlerts = {}

        /* Holds the add / edit form on the modal */
        $scope.form = {};
        $scope.showPhoneValidation = false;
        $scope.isNewJointAddedInList = false;
        $scope.venueLogo = {};
        $scope.newUser = {
            'userGroup': 'host.owner',
            'nameFirst': '',
            'nameLast': '',
            'email': '',
            'password': '',
            'passwordB': '',
            'referrer': '',
            'host_accepted_terms': true,
            'phone': '',
            'phone_extension': '',
            'host_address': '',
            'host_addressb': '',
            'host_city': '',
            'host_state': '',
            'host_zip': '',
            'host_website': '',
            'host_facebook': '',
            'venueIds': [],
        }
        /* Modal Mode */
        $scope.setMode = function (type) {
            $scope.viewMode = false;
            $scope.newMode = false;
            $scope.editMode = false;
            switch (type) {
                case 'new':
                    $scope.newMode = true;
                    //$scope.editMode = true;
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
      
        // for dropdownselected data
        $scope.VenuesDropDown = [];
        $scope.publicVenuesList = [];
        ApiRoutesSimpleLists.simplePublicVenuesList().then(function (result) {
            $scope.publicVenuesList = result;
        });
        //for host venue list table
        $scope.hostVenueList = [];
        if (editing != undefined && editing.venues != undefined && (editing.venues).length > 0) {
            $scope.hostVenueList = editing.venues;
        }
      
        $scope.dtInstance = {};
        $scope.dtOptions = DTOptionsBuilder
            .newOptions()
            .withBootstrap()
            .withDOM('<"row"<"col-sm-12 col-md-12"fr><"col-sm-12 add-space"t><"col-sm-6"l><"col-sm-6 text-right"i><"col-sm-12 text-center"p>>')
            .withOption('order', [0, 'desc'])
            .withPaginationType('full_numbers')

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
            'venueIds': [],
        };
        /* Save for resetting purposes */
        if (angular.isDefined(editing.id)) {
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
                'venueIds': editing.venues.map(function (a) { return a.venue_id; }),
            };
        }
        $scope.saved.disabled = (angular.isUndefined(editing.disabled) || editing.disabled === null || !editing.disabled) ? 'false' : 'true';

        /* Item to display and edit */
        $scope.editing = angular.copy($scope.saved);
        /* Click event for the Save button */
        $scope.buttonSave = function () {
            if (!$scope.form.modalForm.$valid) {
                $scope.form.modalForm.$setDirty();
                if ($scope.form.modalForm.website.$error.url) {
                    $scope.alertProxy.error('Invalid website url provided. Check your parameters and try again.');
                }
                else if ($scope.form.modalForm.facebook.$error.pattern) {
                    $scope.alertProxy.error('Invalid facebook url provided. Check your parameters and try again..');
                }
                else {
                    $scope.alertProxy.error('Please fill in all fields for your host.');
                }
            }
            else {
                ApiRoutesGames.saveHost($scope.editing).then(
                        function (result) {
                            $uibModalInstance.close(result.msg);
                        }, function (error) {
                            $scope.alertProxy.error(error);
                        });
            }
        };

        /* Click event for the Cancel button */
        $scope.buttonCancel = function () {
            if ($scope.isNewJointAddedInList == false) {
                $uibModalInstance.dismiss(false);
            }
            else {
                $uibModalInstance.close("reloadDataOnly");
            }
            $uibModalInstance.dismiss(false);
        };
        /* Click event for the Edit button*/
        $scope.buttonEdit = function () {
            $scope.setMode('edit');
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
                var alertTitle = ($scope.editing.disabled === 'true') ? "Host is Disabled." : "Host is Enabled.";
                AlertConfirmService.alert(userState, alertTitle);
            }
        };

        /* Click event for the Delete Venue button */
        $scope.buttonDeleteHostVenue = function (venueId) {
            var hostData = {
                'hostId': $scope.editing.hostId,
                'venueId': venueId
            }
            ApiRoutesUsers.deleteHostVenue(hostData).then(function (result) {
                $scope.hostVenueList = $scope.hostVenueList.filter(function (obj) {
                    return obj.venue_id != venueId;
                });
                $scope.editing.venueIds = $scope.editing.venueIds.filter(function (obj) {
                    return obj != venueId;
                });
                $scope.alertHostVenue.success("Venue has been removed,Please Click on Save Button to save it.");

            }, function (error) {
                $scope.alertHostVenue.error = error;
            });

        }
        /*click event for editing of trivia time and day values*/
        
        /* Click event for adding Selected Joint to host */
        $scope.buttonAddHostVenue = function () {
            var assignTohostVenueListArray = {
                'venue_id': '',
                'venue': '',
                'venue_triviaDay': '',
                'venue_triviaTime': '',
            }
            if (!angular.isDefined($scope.VenuesDropDown.venue.value)) {
                $scope.signupJoinSelectionAlerts.error("Please select at least one joint from the Existing Joint or add a new joint ");
                return;
            }
            var venueId = $scope.VenuesDropDown.venue.value.id;
            if (($scope.hostVenueList).length > 0) {
                var isContains = $scope.hostVenueList.filter(function (obj) {
                    return obj.venue_id == venueId;
                })[0];
                if (isContains != undefined && isContains.venue_id != null && isContains.venue_id != undefined) {
                    $scope.signupJoinSelectionAlerts.error("Selected joint is already exists in your list.Please select new joint");
                }
                else {
                    assignTohostVenueListArray.venue_id = $scope.VenuesDropDown.venue.value.id;
                    assignTohostVenueListArray.venue = $scope.VenuesDropDown.venue.value.name;
                    assignTohostVenueListArray.venue_triviaDay = $scope.VenuesDropDown.venue.value.triviaDay;
                    assignTohostVenueListArray.venue_triviaTime = $scope.VenuesDropDown.venue.value.triviaTime;
                    $scope.hostVenueList.push(assignTohostVenueListArray);
                    $scope.editing.venueIds.push(venueId);
                    $scope.isNewJointAddedInList = true;
                    $scope.signupJoinSelectionAlerts.success("Joint is added in your selection (Note - Change takes effect only after click on Save button for saving the joint)");
                }
            }
            else {
                assignTohostVenueListArray.venue_id = $scope.VenuesDropDown.venue.value.id;
                assignTohostVenueListArray.venue = $scope.VenuesDropDown.venue.value.name;
                assignTohostVenueListArray.venue_triviaDay = $scope.VenuesDropDown.venue.value.triviaDay;
                assignTohostVenueListArray.venue_triviaTime = $scope.VenuesDropDown.venue.value.triviaTime;
                $scope.hostVenueList.push(assignTohostVenueListArray);
                $scope.editing.venueIds.push(venueId);
                $scope.isNewJointAddedInList = true;
                $scope.signupJoinSelectionAlerts.success("Joint is added in your selection (Note - Change takes effect only after click on Save button for saving the joint)");
            }
        }

        $scope.buttonOpenNewVenueModal = function () {
            var modalInstance = TriviaModalService.openAddHostJoint();
            modalInstance.result.then(function (data) {
                $scope.signupJoinSelectionAlerts.success(data.msg);
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

        $scope.buttonAddNewVenue = function () {
            if (!angular.isDefined($scope.VenuesDropDown.venue.value)) {
                $scope.signupJoinSelectionAlerts.error("Please select at least one joint from the Existing Joint or add a new joint ");
                return;
            }
            else {
                var selectedVenueId = $scope.VenuesDropDown.venue.value.id;
                if (($scope.hostVenueList).length) {
                    var isContains = $scope.hostVenueList.filter(function (obj) {
                        return obj.id == selectedVenueId;
                    })[0];
                    if (isContains != undefined && isContains.id != null && isContains.id != undefined) {
                        $scope.signupJoinSelectionAlerts.error("Selected Joint is already added in your selection.Please select new joint");
                    }
                    else {
                        $scope.hostVenueList.push($scope.VenuesDropDown.venue.value);
                    }
                } else {
                    $scope.hostVenueList.push($scope.VenuesDropDown.venue.value);
                }

            }
        }

        $scope.buttonAddNewHost = function () {
            $scope.newUser.venueIds = $scope.hostVenueList.map(function (a) { return a.id; });
            if (!$scope.form.host.$valid) {
                $scope.form.host.$setDirty();
                if ($scope.form.host.website.$error.url) {
                    $scope.signupAlerts.error('Invalid website url provided. Check your parameters and try again.');
                }
                else if ($scope.form.host.facebook.$error.pattern) {
                    $scope.signupAlerts.error('Invalid facebook url provided. Check your parameters and try again..');
                }
                else {
                    $scope.signupAlerts.error('Please fill in all fields for your host.');
                }
            } else if ($scope.newUser.password !== $scope.newUser.passwordB) {
                $scope.form.host.$setDirty();
                $scope.signupAlerts.error('Passwords do not match.');
            }
            else if ($scope.newUser.venueIds == null || $scope.newUser.venueIds == undefined || ($scope.newUser.venueIds).length <= 0) {
                $scope.signupAlerts.error('Please Select one of the Existing Joint,and click on Add Joint to add it as your joint.');
            }
            else {
                AuthService.hostSignup($scope.newUser, true).then(function (results) {
                    $uibModalInstance.close("Host added successful!");
                }, function (error) {
                    $scope.signupAlerts.error(error);
                });
            }
        }

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
            if (hostVenue.venue_triviaTime == undefined || hostVenue.venue_triviaTime == null) {
                $scope.defaultDayTime.triviaTimeDate = currentDateTime;
            }
            else {
                $scope.defaultDayTime.triviaTimeDate = $scope.parseTime(hostVenue.venue_triviaTime);
            }
            var days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            var day = days[currentDateTime.getDay()];
            if (hostVenue.venue_triviaDay == undefined || hostVenue.venue_triviaDay == null) {
                $scope.defaultDayTime.triviaDay = day;
            }
            else {
                $scope.defaultDayTime.triviaDay = hostVenue.venue_triviaDay;
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
                var hostData= {
                    'hostId': $scope.editing.hostId,
                    'venueId': $scope.selectedHostVenue.venue_id,
                    'triviaDay':  $scope.defaultDayTime.triviaDay,
                    'triviaTime':  $filter('date')($scope.defaultDayTime.triviaTimeDate, 'h:mm a'),
                }
                ApiRoutesUsers.updateHostVenue(hostData).then(function (result) {
                    $scope.selectedHostVenue.venue_triviaTime = $filter('date')($scope.defaultDayTime.triviaTimeDate, 'h:mm a');
                    $scope.selectedHostVenue.venue_triviaDay = $scope.defaultDayTime.triviaDay;
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
