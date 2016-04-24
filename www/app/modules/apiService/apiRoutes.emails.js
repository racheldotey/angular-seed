'use strict';

/* 
 * API Routes for Triggering Emails
 * 
 * API calls related to email notifications.
 */

angular.module('apiRoutes.emails', [])
.factory('ApiRoutesEmails', ['ApiService', '$q', function (API, $q) {
        
    var api = {};
    
    api.sendInviteNewPlayerEmail = function(newPlayer) { 
        if(angular.isUndefined(newPlayer.email)) {
            return API.reject('Invalid email please check your parameters and try again.');
        }
        return API.post('/send-email/invite-player', newPlayer, 'Could not send player invite.');
    };
    
    return api;
}]);