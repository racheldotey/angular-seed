'use strict';

/* 
 * API Routes for Users
 * 
 * API calls related to user data.
 */

angular.module('apiRoutes.users', [])
.factory('ApiRoutesUsers', ['ApiService', function (API) {

    var api = {};

    api.getUser = function (id) {
        if (angular.isUndefined(id)) {
            return API.reject('Invalid user. Please check your parameters and try again.');
        }
        return API.get('user/get/' + id, 'Could not get user.');
    };

    api.addUser = function (user) {
        if (angular.isUndefined(user.nameFirst) || angular.isUndefined(user.nameLast) ||
                angular.isUndefined(user.email) || angular.isUndefined(user.password) || angular.isUndefined(user.phone)) {
            return API.reject('Invalid user please verify your information and try again.');
        }

        return API.post('user/insert/', user, 'System unable to create new user.');
    };

    api.saveUser = function (user) {
        if (angular.isUndefined(user.nameFirst) || angular.isUndefined(user.nameLast) ||
                angular.isUndefined(user.email) || angular.isUndefined(user.id) || angular.isUndefined(user.phone)) {
            return API.reject('Invalid user. Please check your parameters and try again.');
        }

        return API.post('user/update/' + user.id, user, 'System unable to save user.');
    };


    api.saveUserVenue = function (user) {
        if (angular.isUndefined(user.venueId) || angular.isUndefined(user.nameFirst) || angular.isUndefined(user.nameLast) ||
                angular.isUndefined(user.email) || angular.isUndefined(user.id)
                || angular.isUndefined(user.venue) || angular.isUndefined(user.address)
                || angular.isUndefined(user.city) || angular.isUndefined(user.state)
                || angular.isUndefined(user.zip) || angular.isUndefined(user.phone)) {
            return API.reject('Invalid venue. Please check your parameters and try again.');
        }
        return API.post('venuesdata/update/' + user.id, user, 'System unable to save user.');
    };


    api.getVenue = function (user) {
        if (angular.isUndefined(user.id)) {
            return API.reject('Invalid user. Please check your parameters and try again.');
        }
        return API.get('venue/getbyuser/' + user.id, user, 'System unable to get venue.');
    };

    api.changePassword = function (password) {
        if (angular.isUndefined(password.userId) ||
                angular.isUndefined(password.current) || angular.isUndefined(password.new)) {
            return API.reject('Invalid password. Please check your parameters and try again.');
        }

        return API.post('user/update/password/', password, 'System unable to save user.');
    };

    api.deleteUser = function (id) {
        if (angular.isUndefined(id)) {
            return API.reject('Invalid user. Please check your parameters and try again.');
        }
        return API.delete('user/delete/' + id, 'System unable to delete user.');
    };

    api.disableUser = function (id) {
        if (angular.isUndefined(id)) {
            return API.reject('Invalid user. Please check your parameters and try again.');
        }
        return API.post('user/disable/' + id, 'System unable to disable user.');
    };

    api.enableUser = function (id) {
        if (angular.isUndefined(id)) {
            return API.reject('Invalid user. Please check your parameters and try again.');
        }
        return API.post('user/enable/' + id, 'System unable to enable user.');
    };

    api.unassignUserFromGroup = function (pair) {
        if (angular.isUndefined(pair.userId) || angular.isUndefined(pair.groupId)) {
            return API.reject('Invalid user / group pair please check your parameters and try again.');
        }
        return API.post('user/unassign-group', pair, 'System unable to unassign user to group.');
    };

    api.assignUserToGroup = function (pair) {
        if (angular.isUndefined(pair.userId) || angular.isUndefined(pair.groupId)) {
            return API.reject('Invalid user / group pair please check your parameters and try again.');
        }
        return API.post('user/assign-group', pair, 'System unable to assign user from group.');
    };

    //region for get;set; of host information
    api.saveHostUserInfo = function (host) {
        if (!angular.isNumber(parseInt(host.hostId)) || !angular.isNumber(parseInt(host.userId)) ||
                !angular.isString(host.nameFirst) ||
                !angular.isString(host.nameLast) ||
                !angular.isString(host.phone) ||
                !angular.isString(host.phone_extension) ||
                !angular.isString(host.host_address) ||
                !angular.isString(host.host_addressb) ||
                !angular.isString(host.host_city) ||
                !angular.isString(host.host_state) ||
                !angular.isNumber(parseInt(host.host_zip))) {
            return API.reject('Invalid venue. Please check your parameters and try again.');
        }
        return API.post('/hostsdata/update/' + host.userId, host, 'System unable to save user.');
    };
    api.getHostUserInfo = function (user) {
        if (angular.isUndefined(user.id)) {
            return API.reject('Invalid user. Please check your parameters and try again.');
        }
        return API.get('/host/getHostByUser/' + user.id, user, 'System unable to get venue.');
    };
    api.deleteHostVenue = function (host) {
        if (angular.isUndefined(host.hostId)) {
            return API.reject('Invalid host. Please check your parameters and try again.');
        }
        return API.post('/host/delete/venue/' + host.hostId, host, 'System unable to get venue.');
    };
    api.updateHostVenue = function (host) {
        console.log(JSON.stringify(host));
        if (angular.isUndefined(host.hostId)) {
            return API.reject('Invalid host. Please check your parameters and try again.');
        }
        return API.post('/host/update/trivia/' + host.hostId, host, 'System unable to get venue.');
    };
    //end region

    return api;
}]);