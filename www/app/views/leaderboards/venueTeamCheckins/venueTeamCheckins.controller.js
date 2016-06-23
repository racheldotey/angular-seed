'use strict';

/* 
 * Per Joint Team Checkins Leaderboard
 */

angular.module('app.leaderboards.venueTeamCheckins', ['ui.grid', 'ui.grid.autoResize'])
    .controller('VenueTeamCheckinsLeaderboardCtrl', ['$window', '$state', '$stateParams', '$rootScope', '$scope', '$q', 'uiGridConstants', 'ApiRoutesLeaderboards', 'LeaderboardResizing',
        function($window, $state, $stateParams, $rootScope, $scope, $q, uiGridConstants, ApiRoutesLeaderboards, LeaderboardResizing) {
        
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
                { field: 'teamName', displayName:'Team Name' },
                { field: 'mobileCheckins', displayName:'Mobile Checkins', type: 'number', sort: { direction: uiGridConstants.DESC, priority: 1 }, maxWidth: 175 },
                { field: 'liveCheckins', displayName:'Live Team Checkins', type: 'number', maxWidth: 175 }
            ];
            
            $scope.gridHeight = 50;       
            $scope.setLeaderboardHeight = function() {
                $scope.gridHeight = ($scope.grid.data.length > 0) ? LeaderboardResizing.getUIGridHeight() : 50;
            };
            
            // Responsive leaderboard height on window resize
            angular.element($window).on('resize', function () {
                $scope.setLeaderboardHeight();
            });
            
            ($scope.refreshGrid = function(limit, venueId) {
                return $q(function(resolve, reject) {
                    var venue = (angular.isDefined(venueId) && parseInt(venueId)) ? venueId : $stateParams.venueId;
                    var count = (angular.isDefined(limit) && parseInt(limit)) ? limit : $stateParams.count;

                    ApiRoutesLeaderboards.getVenueTeamCheckinsLeaderboard(venue, count).then(function (result) {
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