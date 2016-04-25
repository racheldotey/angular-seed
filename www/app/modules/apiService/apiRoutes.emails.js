'use strict';

/* 
 * API Routes for Triggering Emails
 * 
 * API calls related to email notifications.
 */

angular.module('apiRoutes.emails', [])
.factory('ApiRoutesEmails', ['ApiService', '$q', function (API, $q) {
        
    var api = {};
    
    api.validateInviteToken = function(token) { 
        if(angular.isUndefined(token)) {
            return API.reject('Invalid token please check your parameters and try again.');
        }
        return API.post('/send-email/validate-token/' + token, 'Could validate invite token.');
    };
    
    api.sendInviteNewPlayerEmail = function(newPlayer) { 
        if(angular.isUndefined(newPlayer.email)) {
            return API.reject('Invalid email please check your parameters and try again.');
        }
        return API.post('/send-email/invite-player', newPlayer, 'Could not send player invite.');
    };
    
    api.sendTeamInviteEmail = function(newPlayer) { 
        if(angular.isUndefined(newPlayer.email)) {
            return API.reject('Invalid email please check your parameters and try again.');
        }
        return API.post('/send-email/team-invite', newPlayer, 'Could not send team invite.');
    };
    
    return api;
}]);