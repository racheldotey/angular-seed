'use strict';

app = angular.module('rcTrivia.directives', []);

app.constant('THIS_DIRECTORY', (function () {
    var scripts = document.getElementsByTagName("script");
    var scriptPath = scripts[scripts.length - 1].src;
    return scriptPath.substring(0, scriptPath.lastIndexOf('/') + 1);
})());

app.directive('rcTriviaScoreboard', function(THIS_DIRECTORY) {
        
    return {
        restrict: 'A',          // Must be a attributeon a html tag
        templateUrl: THIS_DIRECTORY + 'views/scoreboard.html',
        scope: {
            game: '=rcTriviaScoreboard'
        },
        controller: ['$scope', 'TriviaHost', '$state', function($scope, TriviaHost, $state) {
                
            $scope.buttonViewRound = function(roundNumber) {
                TriviaHost.loadRound(roundNumber).then(function (result) {
                        $scope.game = result;
                        console.log($scope.game);
                    }, function (error) {
                        console.log(error);
                    });
            };
            
            
        }],
        link: function(scope, element, attrs) {
            
        }
    };
    
});

app.directive('rcTriviaScoreboardTeams', function(THIS_DIRECTORY) {
    return {
        restrict: 'A',          // Must be a element attribute
        templateUrl: THIS_DIRECTORY + 'views/scoreboard.teams.html'
    };
});

app.directive('rcTriviaScoreboardRound', function(THIS_DIRECTORY) {
    return {
        restrict: 'A',          // Must be a element attribute
        templateUrl: THIS_DIRECTORY + 'views/scoreboard.round.html'
    };
});

app.directive('rcTriviaScoreboardRoundNavigation', function(THIS_DIRECTORY) {
    return {
        restrict: 'A',          // Must be a element attribute
        templateUrl: THIS_DIRECTORY + 'views/scoreboard.roundNavigation.html'
    };
});