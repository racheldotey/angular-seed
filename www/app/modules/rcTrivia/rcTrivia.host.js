'use strict';

/* 
 * Trivia Game Hosting Service
 */

angular.module('rcTrivia.host', [])
    .factory('TriviaHost', ['$log', 'TriviaGame',  
        function($log, TriviaGame) {
            
        var self = this;
        var api = {};
        
        api.getGame = TriviaGame.getGame;
        api.loadGame = TriviaGame.loadGame;
        api.loadRound = TriviaGame.loadRound;
        
        return api;
    }]);