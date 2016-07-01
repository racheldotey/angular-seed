'use strict';

/*
 * Host Signup Page
 * 
 * Controller for the login page.
 */

angular.module('app.auth.signupHost', [])
        .controller('AuthSignupHostCtrl', ['$scope', '$state', '$log', '$filter', '$window', '$timeout', 'AuthService', 'AlertConfirmService', 'DTOptionsBuilder', 'DTColumnBuilder', 'DataTableHelper', 'ApiRoutesSimpleLists', 'TriviaModalService',
        function ($scope, $state, $log, $filter, $window, $timeout, AuthService, AlertConfirmService, DTOptionsBuilder, DTColumnBuilder, DataTableHelper, ApiRoutesSimpleLists, TriviaModalService) {

            $scope.$state = $state;
            $scope.form = {};
            $scope.facebookAlerts = {};
            $scope.signupAlerts = {};
            $scope.alertProxy = {};
            $scope.signupJoinSelectionAlerts = {};

            $scope.showPasswordRules = false;
            $scope.showPasswordMissmatch = false;
            $scope.showPhoneValidation = false;

            $scope.hostLogo = {};
            $scope.ModelVenu = {};
            $scope.newUser = {
                'userGroup': 'host.owner',
                'nameFirst': '',
                'nameLast': '',
                'email': '',
                'password': '',
                'passwordB': '',
                'referrer': '',
                'host_accepted_terms': false,
                'phone': '',
                'phone_extension': '',
                'host_address': '',
                'host_addressb': '',
                'host_city': '',
                'host_state': '',
                'host_zip': '',
                'host_website': '',
                'host_facebook': '',
                //'venueId': '',
                'venueIds': [],
            }

            // for dropdownselected data
            $scope.VenuesDropDown = [];
            // for public venue list
            $scope.publicVenuesList = [];
            ApiRoutesSimpleLists.simplePublicVenuesList().then(function (result) {
                $scope.publicVenuesList = result;
            });

            //for host venue selection table
            $scope.NewVenueHostList = [];
            $scope.dtInstance = {};
            $scope.dtOptions = DTOptionsBuilder
                .newOptions()
                .withBootstrap()
                .withDOM('<"row"<"col-sm-12 col-md-12"fr><"col-sm-12 col-md-12 add-space"t><"col-sm-6 col-md-4"l><"col-sm-6 col-md-4"i><"col-sm-12 col-md-4"p>>')
                .withOption('order', [0, 'desc'])
                .withPaginationType('full_numbers')


            $scope.signup = function () {
                $scope.newUser.venueIds = $scope.NewVenueHostList.map(function (a) { return a.id; });
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
                else if (!$scope.newUser.host_accepted_terms) {
                    AlertConfirmService.confirm('Do you agree to our <a href="http://www.triviajoint.com/host-terms-and-conditions.html" target="_blank">Terms of Service</a>?', 'Terms of Service Agreement').result.then(function (resp) {
                        $scope.newUser.host_accepted_terms = true;
                        AuthService.hostSignup($scope.newUser).then(function (results) {
                            $log.debug(results);
                            $scope.signupAlerts.success("Signup successful!  Please wait for confirmation page", 'signup');
                            $timeout(function () {
                                $window.top.location.href = results.redirectPage;
                            }, 1000);
                        }, function (error) {
                            $scope.signupAlerts.error(error);
                        });
                    }, function (err) {
                        $scope.facebookAlerts.error('Please accept the Terms of Service to signup.');
                    });
                } else {
                    AuthService.hostSignup($scope.newUser).then(function (results) {
                        $log.debug(results);
                        $scope.signupAlerts.success("Signup successful!  Please wait for confirmation page", 'signup');
                        $timeout(function () {
                            $window.top.location.href = results.redirectPage;
                        }, 1000);
                    }, function (error) {
                        $scope.signupAlerts.error(error);
                    });
                }
            };

            $scope.facebookSignup = function () {
                $scope.newUser.venueIds = $scope.NewVenueHostList.map(function (a) { return a.id; });
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
                }
                else if ($scope.newUser.venueIds == null || $scope.newUser.venueIds == undefined || ($scope.newUser.venueIds).length <= 0) {
                    $scope.signupAlerts.error('Please Select one of the Existing Joint,and click on Add Joint to add it as your joint.');
                } else if (!$scope.newUser.host_accepted_terms) {
                    AlertConfirmService.confirm('Do you agree to our <a href="http://www.triviajoint.com/host-terms-and-conditions.html" target="_blank">Terms of Service</a>?', 'Terms of Service Agreement').result.then(function (resp) {
                        $scope.newUser.host_accepted_terms = true;
                        AuthService.hostFacebookSignup().then(function (resp) {
                            $log.debug(resp);
                            $scope.newUser = resp;
                            $scope.facebookAlerts.success("Facebook signup Successful!  Please wait for confirmation page", 'terms');
                            $timeout(function () {
                                $window.top.location.href = resp.redirectPage;
                            }, 1000);
                        }, function (err) {
                            $scope.facebookAlerts.error(err);
                        });
                    }, function (err) {
                        $scope.facebookAlerts.error('Please accept the Terms of Service to signup.');
                    });
                } else {
                    AuthService.hostFacebookSignup().then(function (resp) {
                        $log.debug(resp);
                        $scope.newUser = resp;
                        $scope.facebookAlerts.success("Facebook signup Successful!  Please wait for confirmation page", 'terms');
                        $timeout(function () {
                            $window.top.location.href = resp.redirectPage;
                        }, 1000);
                    }, function (err) {
                        $scope.facebookAlerts.error(err);
                    });
                }
            };

            var passwordValidator = /^(?=.*\d)(?=.*[A-Za-z])[A-Za-z0-9_!@#$%^&*+=-]{8,55}$/;
            $scope.onChangeValidatePassword = function () {
                $scope.showPasswordRules = (!passwordValidator.test($scope.newUser.password));
                $scope.onChangeValidateConfirmPassword();
            };

            $scope.onChangeValidateConfirmPassword = function () {
                if ($scope.newUser.passwordB) {
                    $scope.showPasswordMissmatch = ($scope.newUser.password !== $scope.newUser.passwordB);
                }
            };

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

            //$scope.buttonAddNewVenue = function () {
            //    if (!angular.isDefined($scope.VenuesDropDown.venue.value)) {
            //        $scope.signupJoinSelectionAlerts.error("Please select at least one joint from the Existing Joint or add a new joint");
            //        return;
            //    }
            //    else {
            //        var selectedVenueId = $scope.VenuesDropDown.venue.value.id;
            //        if (($scope.NewVenueHostList).length) {
            //            var isContains = $scope.NewVenueHostList.filter(function (obj) {
            //                return obj.id == selectedVenueId;
            //            })[0];
            //            if (isContains != undefined && isContains.id != null && isContains.id != undefined) {
            //                $scope.signupJoinSelectionAlerts.error("Selected Joint is already added in your selection.Please select new joint");
            //            }
            //            else {
            //                $scope.NewVenueHostList.push($scope.VenuesDropDown.venue.value);
            //            }
            //        } else {
            //            $scope.NewVenueHostList.push($scope.VenuesDropDown.venue.value);
            //        }

            //    }

            //}
            $scope.buttonDeleteHostVenue = function (venueId) {
                $scope.NewVenueHostList = $scope.NewVenueHostList.filter(function (obj) {
                    return obj.id != venueId;
                });
            }
            $scope.handlePhoneChangeEvent = function ($phone) {
                $scope.showPhoneValidation = false;
                if ($phone === undefined || $phone.length < 10) {
                    $scope.showPhoneValidation = true;
                }
            }

            $scope.$watch("VenuesDropDown.venue.value", function (newValue, oldValue) {
                if (angular.isDefined(newValue) && angular.isDefined(newValue.id)) {
                    var selectedVenueId = $scope.VenuesDropDown.venue.value.id;
                    if (($scope.NewVenueHostList).length) {
                        var isContains = $scope.NewVenueHostList.filter(function (obj) {
                            return obj.id == selectedVenueId;
                        })[0];
                        if (isContains != undefined && isContains.id != null && isContains.id != undefined) {
                            $scope.signupJoinSelectionAlerts.error("Selected Joint is already added in your selection.Please select new joint");
                        }
                        else {
                            $scope.NewVenueHostList.push($scope.VenuesDropDown.venue.value);
                        }
                    } else {
                        $scope.NewVenueHostList.push($scope.VenuesDropDown.venue.value);
                    }
                }
            });
        }]);
