'use strict';

/* 
 * API Routes for Auth
 * 
 * API calls related to authentication.
 */

angular.module('apiRoutes.auth', [])
.factory('ApiRoutesAuth', ['ApiService', function (API) {

    var api = {};

    // Verify Cookie Credentials and Retrieve Authenticated User Data

    api.getAuthenticatedUser = function (credentials) {
        if (!credentials.apiKey || !credentials.apiToken) {
            return API.reject('Invalid credentials please verify your information and try again.');
        }

        var user = API.post('auth/authenticate/', credentials, 'Error, User Not Authenticated.');

        return ;
    };

    // Standard Login

    api.postLogin = function (credentials) {
        if (!credentials.password || !credentials.email) {
            return API.reject('Invalid credentials please verify your information and try again.');
        }
        credentials.remember = credentials.remember || false;

        return API.post('auth/login/', credentials, 'System unable to login.');
    };

    // Facebook Login

    api.postFacebookLogin = function (user) {
        if (!user.accessToken ||
                !user.facebookId ||
                !user.nameFirst ||
                !user.nameLast ||
                !user.email ||
                !user.link ||
                !user.locale ||
                !user.timezone ||
                !user.ageRange) {
            return API.reject('Invalid credentials please verify your information and try again.');
        }
        user.remember = user.remember || false;

        return API.post('auth/login/facebook/', user, 'System unable to login.');
    };

    // Standard Signup

    api.postSignup = function (newUser) {
        if (angular.isUndefined(newUser.password) ||
            angular.isUndefined(newUser.email) ||
            angular.isUndefined(newUser.nameFirst)) {
            return API.reject('Invalid user please verify your information and try again.');
        }
        return API.post('auth/signup/', newUser, 'System unable to register new user.');
    };

    // Facebook Signup

    api.postFacebookSignup = function (newUser) {
        if (!newUser.accessToken ||
                !newUser.facebookId ||
                !newUser.nameFirst ||
                !newUser.nameLast ||
                !newUser.email ||
                !newUser.link ||
                !newUser.locale ||
                !newUser.timezone ||
                !newUser.ageRange) {
            return API.reject('Invalid user please verify your information and try again.');
        }
        return API.post('auth/signup/facebook/', newUser, 'System unable to register new facebook user.');
    };

    // Logout Based on Token Stored in Cookie

    api.postLogout = function (logout) {
        var data = {};
        data.logout = logout;

        return API.post('auth/logout/', data, 'System unable to logout.');
    };

    // Confirm New User Email

    api.postConfirmNewUserEmail = function (data) {
        if (!data.linkToken || !data.linkPassword) {
            return API.reject('Invalid confirm password token please verify your information and try again.');
        }

        return API.post('auth/signup/confirm-email/', data, 'System unable to confirm new user email address.');
    };

    // Forgot password

    api.postForgotpassword = function (credentials) {
        if (!credentials.email) {
            return API.reject('Invalid credentials please verify your information and try again.');
        }

        return API.post('/auth/forgotpassword/', credentials, 'System unable to request for forgotpassword.');
    };

    api.getforgotemailaddress = function (credentials) {
        if (!credentials.usertoken) {
            return API.reject('Invalid credentials please verify your information and try again.');
        }

        return API.post('/auth/getforgotpasswordemail/', credentials, 'System unable to request for forgotpassword.');
    };

    api.postResetpassword = function (credentials) {
        if (!credentials.password || !credentials.email) {
            return API.reject('Invalid credentials please verify your information and try again.');
        }

        return API.post('/auth/resetpassword/', credentials, 'System unable to request for resetpassword.');
    };

    api.postForgotPasswordEmail = function (email) {
        var data = { 'email': email };

        return API.post('auth/password-reset/', data, 'Error sending password reset email.');
    };

    api.postValidatePasswordResetToken = function (token) {
        if (!token) {
            return API.reject('Invalid password reset token.');
        }

        var data = { 'token': token };

        return API.post('auth/validate-token/', data, 'Error validating token.');
    };

    api.updatePassword = function (user) {
        if (!user.id || !user.email || !user.token || !user.password || user.password <= 0) {
            return API.reject('Invalid credentials please verify your information and try again.');
        }

        return API.post('auth/change-password/', user, 'Error changing password.');
    };

    return api;
}]);