'use strict';

/* 
 * Global Player Leaderboard
 * 
 * Controller for the game scorboard, host view.
 */

angular.module('app.leaderboards.globalPlayers', ['ui.grid'])
    .controller('GlobalPlayerLeaderboardCtrl', ['$scope', 
        function($scope) {
            $scope.myData = [
                { 'imgSrc': '', 'label': 'Player Name', 'mobileScore' : '22', 'liveScore' : '24' },
                { 'imgSrc': '', 'label': 'Player Name 2', 'mobileScore' : '21', 'liveScore' : '22' },
                { 'imgSrc': '', 'label': 'Player Name 3', 'mobileScore' : '27', 'liveScore' : '29' }
            ];
    }]);