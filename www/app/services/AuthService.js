'use strict';

/* 
 * Auth Services
 * Sets Nav, Session and Build as global page variables. 
 */

angular.module('AuthService', [
    'rcAuth.constants',
    'rcAuth.cookies',
    'rcAuth.facebook',
    'rcAuth.interceptors',
    'rcAuth.user',
    'rcAuth.visibility',
    'rcAuth.directives'
]).factory('AuthService', ['$rootScope', '$q', '$log', 'UserSession', 'AUTH_EVENTS', 'VisibilityService', 'ApiRoutesAuth', 'FacebookAuthService', 'CookieService',
    function ($rootScope, $q, $log, UserSession, AUTH_EVENTS, VisibilityService, API, FacebookAuthService, CookieService) {

        var factory = {};

        factory.getUser = function () {
            return UserSession.get();
        };

        factory.init = function () {
            /* Returns promise that always resolves true */
            return $q(function (resolve, reject) {
                if (UserSession.get()) {
                    console.log("AuthService Init - User already set - ", UserSession.get());
                    return resolve(true);
                }

                var credentials = CookieService.getAuthCookie();

                if (credentials) {
                    console.log("AuthService Init - credentials - ", credentials);
                    API.getAuthenticatedUser(credentials)
                        .then(function (data) {
                            data.user.apiKey = credentials.apiKey;
                            data.user.apiToken = credentials.apiToken;
                            if (!UserSession.create(data.user)) {
                                $log.error('[authInit] Credentials found but session Couldn\'t be Created', data);
                            }
                            return resolve(UserSession.get());
                        }, function (error) {
                            CookieService.destroyAuthCookie();
                            $log.info('[authInit]', error);
                            /// Why is this silent? 
                            return resolve(false);
                        });
                } else {
                    console.log("AuthService Init - No idea what the hell is going on");
                    /// Why is this silent? 
                    return resolve(false);
                }
            });
        };

        factory.signup = function (newUser) {
            /* 
             * Regular Signup:
             * var newUser = {
             'nameFirst': '',
             'nameLast': '',
             'email': '',
             'password': '',
             'passwordB': '',
             'referrer': ''
             };
             */

            return $q(function (resolve, reject) {
                API.postSignup(newUser)
                        .then(function (data) {

                            if (UserSession.create(data.user)) {
                                //put valid login creds in a cookie
                                CookieService.setAuthCookie(data.user.apiKey, data.user.apiToken, data.sessionLifeHours);

                                resolve(UserSession.get());
                                $rootScope.$broadcast(AUTH_EVENTS.loginSuccess);
                            } else {
                                $log.error(data);
                                reject("Error: Could not sign up user. Please try again later.");
                            }
                        }, function (error) {
                            $log.error(error);
                            reject(error);
                        });
            });
        };

        factory.venueSignup = function (newUser) {
            /* 
             * Regular Signup:
             * var newUser = {
             'nameFirst': '',
             'nameLast': '',
             'email': '',
             'password': '',
             'passwordB': '',
             'referrer': ''
             };
             */

            return $q(function (resolve, reject) {
                API.postVenueSignup(newUser)
                        .then(function (data) {

                            if (UserSession.create(data.user)) {
                                //put valid login creds in a cookie
                                CookieService.setAuthCookie(data.user.apiKey, data.user.apiToken, data.sessionLifeHours);

                                resolve(UserSession.get());
                                $rootScope.$broadcast(AUTH_EVENTS.loginSuccess);
                            } else {
                                $log.error(data);
                                reject("Error: Could not sign up user. Please try again later.");
                            }
                        }, function (error) {
                            $log.error(error);
                            reject(error);
                        });
            });
        };

        factory.login = function (credentials) {
            var savedAuth = CookieService.getAuthCookie();
            if (savedAuth) {
                credentials.logout = savedAuth.apiKey;
            }

            CookieService.destroyAuthCookie();
            UserSession.destroy();

            return $q(function (resolve, reject) {
                API.postLogin(credentials)
                        .then(function (data) {

                            if (UserSession.create(data.user)) {
                                //put valid login creds in a cookie
                                CookieService.setAuthCookie(data.user.apiKey, data.user.apiToken, data.sessionLifeHours);

                                resolve(UserSession.get());
                                $rootScope.$broadcast(AUTH_EVENTS.loginSuccess);
                            } else {
                                reject("Error: Could not log in user. Please try again later.");
                                $rootScope.$broadcast(AUTH_EVENTS.loginFailed);
                                $log.error(data);
                            }
                        }, function (error) {
                            reject(error);
                            $rootScope.$broadcast(AUTH_EVENTS.loginFailed);
                        });
            });
        };

        factory.forgotpassword = function (credentials) {
            return $q(function (resolve, reject) {
                API.postForgotpassword(credentials)
                        .then(function (data) {



                        }, function (error) {
                            reject(error);
                            $rootScope.$broadcast(AUTH_EVENTS.forgotpasswordFailed);
                        });
            });


        };

        factory.forgotemailaddress = function (credentials) {
            return $q(function (resolve, reject) {
                API.getforgotemailaddress(credentials)
                        .then(function (data) {
                            resolve(data);


                        }, function (error) {
                            reject(error);
                            //$rootScope.$broadcast(AUTH_EVENTS.getforgotemailaddressFailed);
                        });
            });


        };

        factory.resetpassword = function (credentials) {
            return $q(function (resolve, reject) {
                API.postResetpassword(credentials)
                        .then(function (data) {
                            resolve(data);


                        }, function (error) {
                            reject(error);
                            //$rootScope.$broadcast(AUTH_EVENTS.getforgotemailaddressFailed);
                        });
            });


        };

        factory.logout = function () {

            var credentials = CookieService.getAuthCookie();
            var logout = (credentials) ? credentials.apiKey : false;

            return $q(function (resolve, reject) {
                if (logout) {
                    API.postLogout(logout)
                            .then(function (data) {
                                console.log(data);
                            }, function (error) {
                                console.log(error);
                            });
                }
                CookieService.destroyAuthCookie();
                UserSession.destroy();

                /* Logout success event */
                $rootScope.$broadcast(AUTH_EVENTS.logoutSuccess);

                return resolve("User was successfully logged out.");
            });
        };

        factory.isAuthenticated = function () {
            return $q(function (resolve, reject) {
                var user = UserSession.get();
                if (user) {
                    return resolve(user);
                }

                var credentials = CookieService.getAuthCookie();
                if (credentials) {
                    API.getAuthenticatedUser(credentials)
                            .then(function (data) {
                                data.user.apiKey = credentials.apiKey;
                                data.user.apiToken = credentials.apiToken;
                                if (UserSession.create(data.user)) {
                                    return resolve(UserSession.get());
                                } else {
                                    $log.error('[isAuthenticated] Session Couldn\'t be Created', data);
                                    return reject('User is not authenticated.');
                                }
                            }, function (error) {
                                return reject(error);
                            });
                } else {
                    return reject('User is not authenticated.');
                }
            });
        };

        factory.isAuthorized = function (authorizedRole) {
            return $q(function (resolve, reject) {

                // Checks a role (presumably for $state access or to see if a user can view
                // a page or menu item etc) to see if it is publicly accessable to everyone
                // including unautorized (not logged in) users.
                if (VisibilityService.isAccessUnauthenticated(authorizedRole)) {
                    // User doesn't have to be logged in
                    resolve(true);
                } else {
                    // Confirm that the user is logged in
                    factory.isAuthenticated().then(function (results) {

                        if (VisibilityService.isVisibleToUser(authorizedRole, UserSession.roles())) {
                            resolve(true);
                        } else {
                            reject(AUTH_EVENTS.notAuthorized);
                        }

                    }, function (results) {
                        // Reject because the user was not logged in
                        reject(AUTH_EVENTS.notAuthenticated);
                    });
                }
            });
        };

        factory.facebookLogin = function (rememberLogin) {
            var remember = (rememberLogin) ? true : false;
            /* Facebook Signup:
             * var newUser = {
             'nameFirst' : data.user.first_name,
             'nameLast' : data.user.last_name,
             'email' : data.user.email,
             'facebookId': data.user.id,
             'link' : data.user.link,
             'locale' : data.user.locale,
             'timezone' : data.user.timezone,
             'ageRange' : angular.toJson(data.user.age_range)
             };
             */
            return $q(function (resolve, reject) {
                /* Is the user logged in with facebook?
                 * Ask them to allow our app. */
                FacebookAuthService.login().then(function (data) {
                    var user = {
                        'accessToken': data.authResponse.accessToken,
                        'facebookId': data.user.id,
                        'nameFirst': data.user.first_name,
                        'nameLast': data.user.last_name,
                        'email': data.user.email,
                        'link': data.user.link,
                        'locale': data.user.locale,
                        'timezone': data.user.timezone,
                        'ageRange': angular.toJson(data.user.age_range),
                        'remember': remember
                    };

                    //* Signup through our normal method
                    API.postVenueFacebookLogin(user).then(function (data) {

                        if (UserSession.create(data.user)) {
                            //put valid login creds in a cookie
                            CookieService.setAuthCookie(data.user.apiKey, data.user.apiToken, data.sessionLifeHours);

                            resolve(UserSession.get());
                            $rootScope.$broadcast(AUTH_EVENTS.loginSuccess);
                        } else {
                            reject(data);
                            $log.error(data);
                        }
                    }, function (error) {
                        $log.error('ERROR Signup User: ', error);
                        reject(error);
                    });

                }, function (error) {
                    $rootScope.$broadcast(AUTH_EVENTS.loginFailed);
                    reject(error);
                });
            });
        };

        factory.facebookSignup = function (additionalPrams) {
            /* Facebook Signup:
             * var newUser = {
             'nameFirst' : data.user.first_name,
             'nameLast' : data.user.last_name,
             'email' : data.user.email,
             'facebookId': data.user.id,
             'link' : data.user.link,
             'locale' : data.user.locale,
             'timezone' : data.user.timezone,
             'ageRange' : angular.toJson(data.user.age_range)
             };
             */
            return $q(function (resolve, reject) {
                /* Is the user logged in with facebook?
                 * Ask them to allow our app. */
                FacebookAuthService.login().then(function (data) {
                    var newUser = {
                        'accessToken': data.authResponse.accessToken,
                        'facebookId': data.user.id,
                        'nameFirst': data.user.first_name,
                        'nameLast': data.user.last_name,
                        'email': data.user.email,
                        'link': data.user.link,
                        'locale': data.user.locale,
                        'timezone': data.user.timezone,
                        'ageRange': angular.toJson(data.user.age_range)
                    };

                    var user = angular.extend({}, additionalPrams, newUser);

                    //* Signup through our normal method
                    API.postFacebookSignup(user).then(function (data) {

                        if (UserSession.create(data.user)) {
                            //put valid login creds in a cookie
                            CookieService.setAuthCookie(data.user.apiKey, data.user.apiToken, data.sessionLifeHours);

                            resolve(UserSession.get());
                            $rootScope.$broadcast(AUTH_EVENTS.loginSuccess);
                        } else {
                            reject(data);
                            $log.error(data);
                        }
                    }, function (error) {
                        $log.error('ERROR Signup User: ', error);
                        reject(error);
                    });

                }, function (error) {
                    $rootScope.$broadcast(AUTH_EVENTS.loginFailed);
                    reject(error);
                });
            });
        };

        factory.venueFacebookSignup = function (additionalPrams) {
            /* Facebook Signup:
             * var newUser = {
             'nameFirst' : data.user.first_name,
             'nameLast' : data.user.last_name,
             'email' : data.user.email,
             'facebookId': data.user.id,
             'link' : data.user.link,
             'locale' : data.user.locale,
             'timezone' : data.user.timezone,
             'ageRange' : angular.toJson(data.user.age_range)
             };
             */
            return $q(function (resolve, reject) {
                /* Is the user logged in with facebook?
                 * Ask them to allow our app. */
                FacebookAuthService.login().then(function (data) {
                    var newUser = {
                        'accessToken': data.authResponse.accessToken,
                        'facebookId': data.user.id,
                        'nameFirst': data.user.first_name,
                        'nameLast': data.user.last_name,
                        'email': data.user.email,
                        'link': data.user.link,
                        'locale': data.user.locale,
                        'timezone': data.user.timezone,
                        'ageRange': angular.toJson(data.user.age_range)
                    };

                    var user = angular.extend({}, additionalPrams, newUser);

                    //* Signup through our normal method
                    API.postFacebookSignup(user).then(function (data) {

                        if (UserSession.create(data.user)) {
                            //put valid login creds in a cookie
                            CookieService.setAuthCookie(data.user.apiKey, data.user.apiToken, data.sessionLifeHours);

                            resolve(UserSession.get());
                            $rootScope.$broadcast(AUTH_EVENTS.loginSuccess);
                        } else {
                            reject(data);
                            $log.error(data);
                        }
                    }, function (error) {
                        $log.error('ERROR Signup User: ', error);
                        reject(error);
                    });

                }, function (error) {
                    $rootScope.$broadcast(AUTH_EVENTS.loginFailed);
                    reject(error);
                });
            });
        };

        return factory;

    }]);
