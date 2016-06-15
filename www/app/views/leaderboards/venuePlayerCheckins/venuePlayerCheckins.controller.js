'use strict';

/* 
 * Per Joint Player Checkins Leaderboard
 */

angular.module('app.leaderboards.venuePlayerCheckins', ['ui.grid', 'ui.grid.autoResize'])
    .controller('VenuePlayerCheckinsLeaderboardCtrl', ['$window', '$state', '$stateParams', '$rootScope', '$scope', 'uiGridConstants', 'ApiRoutesLeaderboards', 'ApiRoutesSimpleLists',
        function($window, $state, $stateParams, $rootScope, $scope, uiGridConstants, ApiRoutesLeaderboards, ApiRoutesSimpleLists) {
            
            $scope.title = $rootScope.title;
            $scope.selectedVenue = {};
            $scope.selectedVenue.id = $stateParams.venueId;
            $scope.showLimit = $stateParams.count;
            
            $scope.grid = {};
            $scope.grid.enableSorting = true;           // Column sort order
            $scope.grid.enableColumnResizing = true;
            $scope.grid.fastWatch = true;               // Improves performance of updates by watching array length
                            
            $scope.grid.data = [];
            $scope.grid.columnDefs = [
                { field: 'img', displayName:'', cellClass: 'leaderboard-img-cell', enableSorting: false, cellTemplate: '<img ng-src="{{COL_FIELD}}" class="leaderboard-img" />' },
                { field: 'label', displayName:'Player Name' },
                { field: 'mobileScore', displayName:'Mobile Score', type: 'number', sort: { direction: uiGridConstants.DESC, priority: 1 } },
                { field: 'liveScore', displayName:'Live Team Score', type: 'number' }
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
                ApiRoutesLeaderboards.getVenuePlayerCheckinsLeaderboard($stateParams.venueId, limit).then(function(result) {
                    $scope.grid.data = result.leaderboard;
                    $scope.setLeaderboardHeight();
                    if($stateParams.count != limit) {
                        $state.go($state.current.name, {count: limit, venueId: $stateParams.venueId}, {notify: false});
                    }
                }, function(error) {
                    console.log(error);
                });
            })($scope.showLimit);
            
            // Venue Button
            ApiRoutesSimpleLists.simpleVenuesList().then(
                function(results) {
                    console.log(results);
                    $scope.venueList = results;
                }, function (error) {
                    console.log(error);
                    $scope.venueList = [];
                });
                
            $scope.$watch("selectedVenue.id", function(newValue, oldValue) {
                if(angular.isDefined(newValue) && parseInt(newValue) && $stateParams.venueId != newValue) {
                    ApiRoutesLeaderboards.getVenuePlayerCheckinsLeaderboard(newValue, $stateParams.count).then(function(result) {
                        $scope.grid.data = result.leaderboard;
                        $scope.setLeaderboardHeight();
                        $state.go($state.current.name, {count: $stateParams.count, venueId: newValue}, {notify: false});
                    }, function(error) {
                        console.log(error);
                    });
                }
            });
    }]);