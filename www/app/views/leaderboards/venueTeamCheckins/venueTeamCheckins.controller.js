'use strict';

/* 
 * Per Joint Team Checkins Leaderboard
 */

angular.module('app.leaderboards.venueTeamCheckins', ['ui.grid', 'ui.grid.autoResize'])
    .controller('VenueTeamCheckinsLeaderboardCtrl', ['$window', '$state', '$stateParams', '$rootScope', '$scope', '$q', 'uiGridConstants', 'ApiRoutesLeaderboards', 'ApiRoutesSimpleLists',
        function($window, $state, $stateParams, $rootScope, $scope, $q, uiGridConstants, ApiRoutesLeaderboards, ApiRoutesSimpleLists) {
        
            /* Used to restrict alert bars */
            $scope.alertProxy = {};
            
            $scope.title = $rootScope.title;
            $scope.selected = { venue : {} };
            $scope.showLimit = $stateParams.count;
            
            $scope.grid = {};
            $scope.grid.enableSorting = true;           // Column sort order
            $scope.grid.enableColumnResizing = true;
            $scope.grid.fastWatch = true;               // Improves performance of updates by watching array length
                            
            $scope.grid.data = [];
            $scope.grid.columnDefs = [
                { field: 'img', displayName:'', cellClass: 'leaderboard-img-cell', enableSorting: false, cellTemplate: '<img ng-src="{{COL_FIELD}}" class="leaderboard-img" />' },
                { field: 'label', displayName:'Team Name' },
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
            
            ($scope.refreshGrid = function(limit, venueId) {
                return $q(function(resolve, reject) {
                    var venue = (angular.isDefined(venueId) && parseInt(venueId)) ? venueId : $stateParams.venueId;
                    var count = (angular.isDefined(limit) && parseInt(limit)) ? limit : $stateParams.count;

                    ApiRoutesLeaderboards.getVenuePlayerCheckinsLeaderboard(venue, count).then(function (result) {
                        $scope.grid.data = result.leaderboard;
                        $scope.setLeaderboardHeight();
                        if ($stateParams.count !== count || $stateParams.venueId !== venue) {
                            $state.go($state.current.name, {count: count, venueId: venue}, {notify: false});
                        }
                        resolve(true);
                    }, function (error) {
                        console.log(error);
                        $scope.alertProxy.error(error);
                        reject(error);
                    });
                });
            })($scope.showLimit);
            
            // Venue Button
            ApiRoutesSimpleLists.simpleVenuesList().then(
                function(results) {
                    console.log(results);
                    $scope.venueList = results;                    
                    for(var i = 0; i < $scope.venueList.length; i++) {
                        if($scope.venueList[i].id === $stateParams.venueId) {
                            $scope.selected.venue = $scope.venueList[i];
                        }
                    }
                }, function (error) {
                    console.log(error);
                    $scope.alertProxy.error(error);
                    $scope.venueList = [];
                });
                
            $scope.$watch("selected.venue", function(newValue, oldValue) {
                if(angular.isDefined(newValue) && angular.isDefined(newValue.id) && $stateParams.venueId != newValue.id) {
                    $scope.refreshGrid(true, newValue.id).then(function(response) {
                        
                    }, function(error) {
                        newValue = oldValue;
                    });
                }
            });
    }]);