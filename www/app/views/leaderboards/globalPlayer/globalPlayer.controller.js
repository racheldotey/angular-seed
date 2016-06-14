'use strict';

/* 
 * Global Player Leaderboard
 * 
 * Controller for the game scorboard, host view.
 */

angular.module('app.leaderboards.globalPlayers', ['ui.grid', 'ui.grid.autoResize'])
    .controller('GlobalPlayerLeaderboardCtrl', ['$window', '$state', '$stateParams', '$scope', 'uiGridConstants', 'ApiRoutesLeaderboards',
        function($window, $state, $stateParams, $scope, uiGridConstants, ApiRoutesLeaderboards) {
            $scope.showLimit = $stateParams.count;
            
            $scope.grid = {};
            $scope.grid.enableSorting = true;           // Column sort order
            $scope.grid.enableColumnResizing = true;
            $scope.grid.fastWatch = true;               // Improves performance of updates by watching array length
            
 $scope.someProp = 'abc',
 $scope.showMe = function(){
                   alert($scope.someProp);
                };
                
            $scope.grid.data = [];
            $scope.grid.columnDefs = [
                { field: 'img', displayName:'', cellClass: 'leaderboard-img-cell', enableSorting: false, cellTemplate: '<img ng-src="{{COL_FIELD}}" class="leaderboard-img" />' },
                { field: 'label', displayName:'Player' },
                { field: 'mobileScore', displayName:'Mobile Score', type: 'number', sort: { direction: uiGridConstants.DESC, priority: 1 } },
                { field: 'liveScore', displayName:'Live Score', type: 'number' }
            ];
            
            $scope.setLeaderboardHeight = function() {
                // Height of the visible window area (screen size)
                var visibleWindowHeight = $(window).height();
                // Add a little padding
                var padding = 20;
                // Do the maths
                var newHeight = visibleWindowHeight - padding;
                // Change the inner scrollable tables height
                angular.element(document.getElementsByClassName('grid')[0]).css('height', newHeight + 'px');
            };
            
            // Responsive leaderboard height on window resize
            angular.element($window).on('resize', function () {
                $scope.setLeaderboardHeight();
            });
            
            ($scope.refreshGrid = function(limit) {
                ApiRoutesLeaderboards.getGlobalPlayersLeaderboard(limit).then(function(result) {
                    $scope.grid.data = result.leaderboard;
                    $scope.setLeaderboardHeight();
                    if($stateParams.count != limit) {
                        $state.go($state.current.name, {count: limit}, {notify: false});
                    }
                }, function(error) {
                    console.log(error);
                });
            })($scope.showLimit);
    }]);