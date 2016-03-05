'use strict';

/* 
 * Host Pages Module
 * 
 * Include controllers and other modules required on authenticated host pages.
 */

angular.module('app.host', [
    'app.host.layout',
    'app.host.header',
    'app.host.footer',
    'app.host.dashboard',
    'app.host.scoreboard'
]);