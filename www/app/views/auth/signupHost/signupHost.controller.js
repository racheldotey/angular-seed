'use strict';

/*
 * Host Signup Page
 * 
 * Controller for the login page.
 */

angular.module('app.auth.signupHost', [])
        .controller('AuthSignupHostCtrl', ['$scope', '$state', '$log','$filter', '$window', '$timeout', 'AuthService', 'AlertConfirmService', 'DTOptionsBuilder', 'DTColumnBuilder', 'DataTableHelper', 'ApiRoutesSimpleLists', 'ApiRoutesUsers', 'TriviaModalService',
        function ($scope, $state, $log, $filter, $window, $timeout, AuthService, AlertConfirmService, DTOptionsBuilder, DTColumnBuilder, DataTableHelper, ApiRoutesSimpleLists, ApiRoutesUsers, TriviaModalService) {

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
                'venueId': '',
            }

         

            // for dropdownselected data
            $scope.VenuesDropDown = [];
            // for public venue list
            $scope.publicVenuesList = [];


            ApiRoutesSimpleLists.simplePublicVenuesList().then(function (result) {
                $scope.publicVenuesList = result;
            });


            $scope.dtVenues = DataTableHelper.getDTStructure($scope, 'VenuesListPublicHost');
            $scope.dtVenues.options.withOption('order', [0, 'desc']);
            $scope.dtVenues.columns = [
                DTColumnBuilder.newColumn('venue').withTitle('Joint Name').withClass('responsive-control noclick'),
                DTColumnBuilder.newColumn('triviaDay').withTitle('Day'),
                DTColumnBuilder.newColumn('triviaTime').withTitle('Hours'),
            ];

            $scope.signup = function () {
                //if (angular.isString($scope.hostLogo.imageDataUrl) &&
                //        ($scope.hostLogo.imageDataUrl.indexOf('data:image') > -1)) {
                //    $scope.newUser.logo = $scope.hostLogo.imageDataUrl;
                //}
               
                if (!$scope.form.host.$valid) {
                    $scope.form.host.$setDirty();
                    if ($scope.form.host.website.$error.url)
                    {
                        $scope.signupAlerts.error('Invalid website url provided. Check your parameters and try again.');
                    }
                    else if ($scope.form.host.facebook.$error.pattern) {
                        $scope.signupAlerts.error('Invalid facebook url provided. Check your parameters and try again..');
                    }
                    else
                    {
                        $scope.signupAlerts.error('Please fill in all fields for your host.');
                    }
                } else if ($scope.newUser.password !== $scope.newUser.passwordB) {
                    $scope.form.host.$setDirty();
                    $scope.signupAlerts.error('Passwords do not match.');
                }// region to check if host Joint is Selected or Not
                else if (!angular.isDefined($scope.VenuesDropDown.venue.value)) {
                    $scope.signupAlerts.error('Please Select one of the Existing Joint.');
                }// end region
                else if (!$scope.newUser.host_accepted_terms) {
                    AlertConfirmService.confirm('Do you agree to our <a href="http://www.triviajoint.com/joint-terms-and-conditions/" target="_blank">Terms of Service</a>?', 'Terms of Service Agreement').result.then(function (resp) {
                        $scope.newUser.host_accepted_terms = true;
                        var selectedVenueId = $scope.VenuesDropDown.venue.value.id || $scope.VenuesDropDown.venue.id;
                        $scope.newUser.venueId = selectedVenueId;
                        AuthService.hostSignup($scope.newUser).then(function (results) {
                            $log.debug(results);
                            $scope.signupAlerts.success("Signup successful!  Please wait for confirmation page", 'signup');
                            $timeout(function () {
                                $window.top.location.href = 'http://www.triviajoint.com/joint-registration-congratulations/';
                            }, 1000);
                        }, function (error) {
                            console.log(JSON.stringify(error));
                            $scope.signupAlerts.error(error);
                        });
                    }, function (err) {
                        $scope.facebookAlerts.error('Please accept the Terms of Service to signup.');
                    });
                } else {
                    var selectedVenueId = $scope.VenuesDropDown.venue.value.id || $scope.VenuesDropDown.venue.id;
                    $scope.newUser.venueId = selectedVenueId;
                    AuthService.hostSignup($scope.newUser).then(function (results) {
                        $log.debug(results);
                        $scope.signupAlerts.success("Signup successful!  Please wait for confirmation page", 'signup');
                        $timeout(function () {
                            $window.top.location.href = 'http://www.triviajoint.com/joint-registration-congratulations/';
                        }, 1000);
                    }, function (error) {
                        $scope.signupAlerts.error(error);
                    });
                }
            };

            $scope.facebookSignup = function () {
                //if (angular.isString($scope.hostLogo.imageDataUrl)
                //        ($scope.hostLogo.imageDataUrl.indexOf('data:image') > -1)) {
                //    $scope.newUser.logo = $scope.hostLogo.imageDataUrl;
                //}

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
                }// region to check if host Joint is Selected or Not 
                else if (!angular.isDefined($scope.VenuesDropDown.venue.value)) {
                    $scope.signupAlerts.error('Please Select one of the Existing Joint.');
                }// end region
                else if (!$scope.newUser.host_accepted_terms) {
                    AlertConfirmService.confirm('Do you agree to our <a href="http://www.triviajoint.com/joint-terms-and-conditions/" target="_blank">Terms of Service</a>?', 'Terms of Service Agreement').result.then(function (resp) {
                        $scope.newUser.host_accepted_terms = true;

                        var selectedVenueId = $scope.VenuesDropDown.venue.value.id || $scope.VenuesDropDown.venue.id;
                        $scope.newUser.venueId = selectedVenueId;
                       
                        AuthService.hostFacebookSignup().then(function (resp) {
                            $log.debug(resp);
                            $scope.newUser = resp;

                            $scope.facebookAlerts.success("Facebook signup Successful!  Please wait for confirmation page", 'terms');

                            $timeout(function () {
                                $window.top.location.href = 'http://www.triviajoint.com/host-registration-congratulations/';
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
                            $window.top.location.href = 'http://www.triviajoint.com/host-registration-congratulations/';
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
                modalInstance.result.then(function (result)
                {
                    $scope.ModelVenu = angular.copy(result);
                });
            };

            $scope.buttonAddNewVenue = function ()
            {
                if(!angular.isDefined($scope.ModelVenu.venue))
                {
                    $scope.signupJoinSelectionAlerts.error("Please add new joint information,by clicking on Add New Joint. Then click on Add Joint to add joint in Trivia. ");
                    return;
                }
                AuthService.hostVenueSignup($scope.ModelVenu).then(function (results) {
                    $scope.dtVenues.reloadData();
                    ApiRoutesSimpleLists.simplePublicVenuesList().then(function (result) {
                        $scope.publicVenuesList = result;
                    });
                    // setting the selected as newly added
                    //for (var v = 0; v < $scope.publicvenueslist.length; v++) {
                    //    if ($scope.publicvenueslist[v].id == 20) {
                    //        $scope.venuesdropdown.venue.value = $scope.publicvenueslist[v];
                    //        break;
                    //    }
                    //}
                    // clearing the data once joint added to venue succesfully..
                    $scope.ModelVenu = {};
                }, function (error) {
                    $scope.signupAlerts.error(error);
                });
                //reload data once venue added to database
              
            }
           
            $scope.handlePhoneChangeEvent = function ($phone) {
                $scope.showPhoneValidation = false;
                if ($phone === undefined || $phone.length < 10) {
                    $scope.showPhoneValidation = true;
                }
            }
        }]);