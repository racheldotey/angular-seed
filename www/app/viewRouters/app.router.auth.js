'use strict';

/*
 * State Declarations: Authorization Pages
 * 
 * Set up the states for auth routes, such as the 
 * login page, forgot password and other auth states.
 * Uses ui-roter's $stateProvider.
 * 
 * Set each state's title (used in the config for the html <title>).
 * 
 * Set auth access for each state.
 */

var app = angular.module('app.router.auth', [
    'rcAuth.constants',
    'app.views'
]);
app.config(['$stateProvider', 'USER_ROLES', 
    function ($stateProvider, USER_ROLES) {

        /*  Abstract Auth Route */
        $stateProvider.state('app.auth', {
            url: '',
            abstract: true,
            data: {authorizedRoles: USER_ROLES.guest},
            views: {
                'header@app': {
                    templateUrl: 'app/views/_elements/authHeader/authHeader.html',
                    controller: 'AuthHeaderCtrl'
                }
            }
        });

        /* Login / Authentication Related States */

        $stateProvider.state('app.auth.signup', {
            bodyClass: 'auth signup',
            title: 'Sign Up',
            url: '/signup',
            views: {
                'content@app': {
                    templateUrl: 'app/views/auth/signup/signup.html',
                    controller: 'AuthSignupCtrl'
                }
            }
        });

        $stateProvider.state('app.auth.signup.invite', {
            bodyClass: 'auth signup',
            title: 'You have been invited!',
            url: '/:linkToken',
            views: {
                'content@app': {
                    templateUrl: 'app/views/auth/playerInvite/playerInvite.html',
                    controller: 'AuthPlayerInviteCtrl'
                },
                'signupform@app.signup.invite': {
                    templateUrl: 'app/views/auth/signup/signupForm.html'
                }
            },
            resolve: {
                $q: '$q',
                ApiRoutesEmails: 'ApiRoutesEmails',
                $stateParams: '$stateParams',
                InvitationData: function($q, $stateParams, ApiRoutesEmails) {
                    return $q(function(resolve, reject) {  
                        ApiRoutesEmails.validateInviteToken($stateParams.token).then(function (result) {
                            resolve(result.invite);
                            console.log(result);
                        }, function(error) {
                            console.log(error);
                            resolve(error);
                        });
                    });
                }
            }
        });

        $stateProvider.state('app.auth.signup.confirmEmail', {
            title: 'Confirming Email..',
            url: '/confirm-email/:linkToken/:linkPassword',
            views: {
                'content@app': {}
            },
            resolve: {
                $q: '$q',
                ApiRoutesEmails: 'ApiRoutesAuth',
                $stateParams: '$stateParams',
                $state: '$state',
                TempLinkTokenData: function($q, $stateParams, $state, ApiRoutesAuth) {
                    return $q(function(resolve, reject) {  
                        var data = {
                            linkToken : $stateParams.linkToken,
                            linkPassword : $stateParams.linkPassword
                        };
                        ApiRoutesAuth.postConfirmNewUserEmail(data).then(function (result) {
                            resolve(result.invite);
                            console.log(result);
                            $state.go('app.auth.signup.confirmSuccess', data);
                        }, function(error) {
                            console.log(error);
                            resolve(error);
                            $state.go('app.auth.signup.confirmFail', data);
                        });
                    });
                }
            }
        });
        
        $stateProvider.state('app.auth.signup.confirmSuccess', {
            title: 'Success! Your Email Is Confirmed',
            url: '/confirm-email/success',
            views: {
                'content@app': {
                    templateUrl: 'app/views/auth/confirmEmail/confirmEmailSuccess.html',
                    controller: 'ConfirmEmailCtrl'
                }
            }
        });
        
        $stateProvider.state('app.auth.signup.confirmFail', {
            title: 'Your Email Could Not Be Confirmed',
            url: '/confirm-email/failed',
            views: {
                'content@app': {
                    templateUrl: 'app/views/auth/confirmEmail/confirmEmailFail.html',
                    controller: 'ConfirmEmailCtrl'
                }
            }
        });
        
        $stateProvider.state('app.auth.login', {
            bodyClass: 'auth login',
            title: 'Login',
            url: '/login',
            views: {
                'content@app': {
                    templateUrl: 'app/views/auth/login/login.html',
                    controller: 'AuthLoginCtrl'
                }
            }/*,
            resolve: {
                $q: '$q',
                $rootScope: '$rootScope',
                AUTH_EVENTS: 'AUTH_EVENTS',
                alreadyLoggedIn: function(initUser, $rootScope, AUTH_EVENTS, $q, AuthService) {
                    return $q(function(resolve, reject) {  
                        if(AuthService.getUser()) {
                            $rootScope.$broadcast(AUTH_EVENTS.loginSuccess);
                            resolve(true);
                        } else {
                            resolve(false);
                        }
                    });
                }
            }*/
        });
        
        $stateProvider.state('app.auth.login.locked', {
            title: 'Account Locked',
            url: '/account-locked'
        });
        
        $stateProvider.state('app.auth.login.forgotPassword', {
            title: 'Forgot Password',
            url: '/forgot-password'
        });
        
        $stateProvider.state('app.auth.login.forgotPassword.resetEmailSent', {
            title: 'Reset Instructions Have Been Sent',
            url: '/reset-instructions-sent'
        });
        
        $stateProvider.state('app.auth.login.forgotPassword.changePassword', {
            title: 'Change Your Password',
            url: '/change-password'
        });

        // This should catch incoming requests, 
        // Trigger the logout method and then redirect
        // to public.
        $stateProvider.state('app.auth.logout', {
            bodyClass: 'auth logout',
            url: '/logout'
        });

        $stateProvider.state('app.auth.resetPassword', {
            title: 'Reset Password',
            url: '/reset_password/:usertoken',
            views: {
                'content@app': {
                    templateUrl: 'app/views/auth/resetPassword/resetPassword.html',
                    controller: 'ResetPasswordCtrl'
                },
                'signupform@app.resetPassword': {
                    templateUrl: 'app/views/auth/resetPassword/resetPasswordForm.html'
                }
            },
            resolve: {
                $q: '$q',
                $rootScope: '$rootScope',
                $state: '$state'
            }
        });

    }]);