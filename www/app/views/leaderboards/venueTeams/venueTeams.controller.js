'use strict';

/* 
 * Per Joint Team Score Leaderboard
 */

angular.module('app.leaderboards.venueTeams', ['ui.grid', 'ui.grid.autoResize'])
    .controller('VenueTeamsLeaderboardCtrl', ['$window', '$state', '$stateParams', '$rootScope', '$scope', '$q', 'uiGridConstants', 'ApiRoutesLeaderboards', 'LeaderboardResizing',
        function($window, $state, $stateParams, $rootScope, $scope, $q, uiGridConstants, ApiRoutesLeaderboards, LeaderboardResizing) {
        
            /* Used to restrict alert bars */
            $scope.alertProxy = {};
            
            $scope.title = $rootScope.title;
            $scope.selected = { venue : {} };
            $scope.showLimit = $stateParams.limit;
            
            $scope.grid = {};
            $scope.grid.enableSorting = true;           // Column sort order
            $scope.grid.enableColumnResizing = true;
            $scope.grid.fastWatch = true;               // Improves performance of updates by watching array length
                      
            $scope.grid.data = [];
            $scope.grid.columnDefs = [
                { field: 'teamName', displayName:'Team Name', cellClass: 'text-verticle-center' },
                { field: 'mobileScore', displayName:'Mobile Score', cellClass: 'text-verticle-center text-center', type: 'number', sort: { direction: uiGridConstants.DESC, priority: 1 }, maxWidth: 175 },
                { field: 'liveScore', displayName:'Live Team Score', cellClass: 'text-verticle-center text-center', type: 'number', cellFilter: 'numberEx', maxWidth: 175 }
            ];
            
            $scope.gridHeight = 50;       
            $scope.setLeaderboardHeight = function() {
                $scope.gridHeight = ($scope.grid.data && $scope.grid.data.length > 0) ? LeaderboardResizing.getUIGridHeight() : 50;
            };
            
            // Responsive leaderboard height on window resize
            angular.element($window).on('resize', function () {
                $scope.setLeaderboardHeight();
            });
            
            ($scope.refreshGrid = function(limit, venueId) {
                return $q(function(resolve, reject) {
                    var venue = (angular.isDefined(venueId) && parseInt(venueId)) ? venueId : $stateParams.venueId;
                    var count = (angular.isDefined(limit) && parseInt(limit)) ? limit : $stateParams.limit;

                    ApiRoutesLeaderboards.getVenueTeamsLeaderboard(venue, count, $stateParams.startDate, $stateParams.endDate).then(function (result) {
                        $scope.grid.data = (angular.isDefined(result.leaderboard) && angular.isArray(result.leaderboard)) ? result.leaderboard : $scope.grid.data;
                        $scope.setLeaderboardHeight();
                        if ($stateParams.limit !== count || $stateParams.venueId !== venue) {
                            $state.go($state.current.name, {limit: count, venueId: venue, startDate:$stateParams.startDate, endDate:$stateParams.endDate}, {notify: false});
                        }
                        resolve(true);
                    }, function (error) {
                        if(angular.isString(error)) {
                            $scope.errorMessage = error;
                        }
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