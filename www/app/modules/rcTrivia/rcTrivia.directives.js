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
        controller: ['$scope', '$filter', '$state', function($scope, $filter, $state) {
            $scope.viewRound = function(roundId) {
                console.log("View Round " + roundId);
                var found = $filter('filter')($scope.game.rounds, {'id':roundId}, true);
                if(angular.isDefined(found[0])) {
                    $scope.game.round = found[0];
                }
                console.log(found);
            };
        }],
        link: function(scope, element, attrs) {
            
        }
    };
    
});

app.directive('rcTriviaScoreboardTeams', function(THIS_DIRECTORY) {
    return {
        restrict: 'E',          // Must be a html element
        transclude: true,       // The element is replaced with the template
        templateUrl: THIS_DIRECTORY + 'views/scoreboard.teams.html'
    };
});

app.directive('rcTriviaScoreboardRounds', function(THIS_DIRECTORY) {
    return {
        restrict: 'E',          // Must be a html element
        transclude: true,       // The element is replaced with the template
        templateUrl: THIS_DIRECTORY + 'views/scoreboard.rounds.html'
    };
});

app.directive('rcTriviaScoreboardQuestion', function(THIS_DIRECTORY) {
    return {
        restrict: 'E',          // Must be a html element
        transclude: true,       // The element is replaced with the template
        templateUrl: THIS_DIRECTORY + 'views/scoreboard.round.question.html'
    };
});