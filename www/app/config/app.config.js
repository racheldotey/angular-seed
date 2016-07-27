'use strict';

/*
 * App Configuration
 * 
 * General configuration settings for the app.
 */

var app = angular.module('app.config', [
    'ui.router'
  //'flow',
  //'ngNotificationsBar'
]);
app.config(['$locationProvider', '$urlMatcherFactoryProvider', 'notificationsConfigProvider', 
    function ($locationProvider, $urlMatcherFactoryProvider, notificationsConfigProvider) {
        
        /*
         * Routing Setup
         */
        
        // Allows for use of regular URL path segments (i.e. /article/21), 
        // instead of their hashbang equivalents (/#/article/21).
        // https://docs.angularjs.org/guide/$location#html5-mode
        $locationProvider.html5Mode(true);
        
        // Defines whether URL matching should be case sensitive (the default behavior), or not.
        // http://angular-ui.github.io/ui-router/site/#/api/ui.router.util.$urlMatcherFactory
        $urlMatcherFactoryProvider.caseInsensitive(true);
        
        // Defines whether URLs should match trailing slashes, or not (the default behavior).
        // http://angular-ui.github.io/ui-router/site/#/api/ui.router.util.$urlMatcherFactory
        $urlMatcherFactoryProvider.strictMode(false);
        
        /* Notifications Bar Config
         * Set global settings for the ng-notifications bar.
         * https://github.com/alexbeletsky/ng-notifications-bar
         * http://beletsky.net/ng-notifications-bar/ */
        // auto hide
        notificationsConfigProvider.setAutoHide(true);

        // delay before hide
        notificationsConfigProvider.setHideDelay(3000);

        // support HTML
        notificationsConfigProvider.setAcceptHTML(false);

        // Set an animation for hiding the notification
        notificationsConfigProvider.setAutoHideAnimation('fadeOutNotifications');

        // delay between animation and removing the nofitication
        notificationsConfigProvider.setAutoHideAnimationDelay(1200);
    
    }]);