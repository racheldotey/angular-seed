'use strict';

/* 
 * Per Joint Player Checkins Leaderboard
 */

angular.module('app.leaderboards.venuePlayerCheckins', ['ui.grid', 'ui.grid.autoResize'])
    .controller('VenuePlayerCheckinsLeaderboardCtrl', ['$window', '$state', '$stateParams', '$rootScope', '$scope', '$q', 'uiGridConstants', 'ApiRoutesLeaderboards',
        function($window, $state, $stateParams, $rootScope, $scope, $q, uiGridConstants, ApiRoutesLeaderboards) {
        
            /* Used to restrict alert bars */
            $scope.alertProxy = {};
            
            $scope.title = $rootScope.title;
            $scope.selected = { venue : {} };
            $scope.showLimit = $stateParams.count;
            
            $scope.grid = {};
            $scope.grid.enableSorting = true;           // Column sort order
            $scope.grid.enableColumnResizing = true;
            $scope.grid.fastWatch = true;               // Improves performance of updates by watching array length
            
            $scope.grid.rowHeight = 100;
            $scope.grid.data = [];
            $scope.grid.columnDefs = [
                { field: 'img', displayName:'', cellClass: 'leaderboard-img-cell text-center', enableSorting: false, cellTemplate: '<img ng-src="{{COL_FIELD}}" class="leaderboard-img" />', maxWidth: 110 },
                { field: 'player', displayName:'Player Name' },
                { field: 'teamName', displayName:'Team Name' },
                { field: 'mobileCheckins', displayName:'Mobile Checkins', type: 'number', sort: { direction: uiGridConstants.DESC, priority: 1 }, maxWidth: 175 },
                { field: 'liveCheckins', displayName:'Live Team Checkins', type: 'number', maxWidth: 175 }
            ];
            
            $scope.setLeaderboardHeight = function() {
                if($scope.grid.data.length) {
                    // Height of the entire page
                    var pageHeight = $(document).height();
                    // Current height of the table
                    var currentGridHeight = angular.element(document.getElementsByClassName('grid')[0]).height();
                    // The height of everything except the grid.
                    var everythingElseHeight = pageHeight - Math.round(currentGridHeight);
                    // Add a little padding
                    var padding = 0;
                    // Height of the visible window area (screen size)
                    var visibleWindowHeight = $(window).height();
                    // Whats left height
                    var whatsLeftHeight = visibleWindowHeight - everythingElseHeight;
                    // Do the maths
                    var newHeight = (whatsLeftHeight > 100) ? whatsLeftHeight : 100;
                    // Change the inner scrollable tables height
                    angular.element(document.getElementsByClassName('grid')[0]).css('height', newHeight + 'px');
                } else {
                    angular.element(document.getElementsByClassName('grid')[0]).css('height', '50px');
                }
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
                        $scope.setLeaderboardHeight();
                        console.log(error);
                        $scope.alertProxy.error(error);
                        reject(error);
                    });
                });
            })($scope.showLimit);
            
            // Venue Button
            ApiRoutesLeaderboards.getListOfJoints().then(
                function(results) {
                    console.log(results);
                    $scope.venueList = results.joints;                   
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