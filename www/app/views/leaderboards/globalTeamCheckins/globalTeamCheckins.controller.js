'use strict';

/* 
 * Global Team Checkin Leaderboard
 */

angular.module('app.leaderboards.globalTeamCheckins', ['ui.grid', 'ui.grid.autoResize'])
    .controller('GlobalTeamCheckinsLeaderboardCtrl', ['$window', '$state', '$stateParams', '$rootScope', '$scope', '$q', 'uiGridConstants', 'ApiRoutesLeaderboards', 'LeaderboardResizing',
        function($window, $state, $stateParams, $rootScope, $scope, $q, uiGridConstants, ApiRoutesLeaderboards, LeaderboardResizing) {
        
            /* Used to restrict alert bars */
            $scope.alertProxy = {};
            
            $scope.title = $rootScope.title;
            $scope.showLimit = $stateParams.limit;
            
            $scope.grid = {};
            $scope.grid.enableSorting = true;           // Column sort order
            $scope.grid.enableColumnResizing = true;
            $scope.grid.fastWatch = true;               // Improves performance of updates by watching array length
                        
            $scope.grid.data = [];
            $scope.grid.columnDefs = [
                { field: 'teamName', displayName:'Team Name' },
                { field: 'homeJoint', displayName:'Team Home Joint' },
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
            
            ($scope.refreshGrid = function(limit) {
                return $q(function(resolve, reject) {
                    var count = (angular.isDefined(limit) && parseInt(limit)) ? limit : $stateParams.limit;

                    ApiRoutesLeaderboards.getGlobalTeamCheckinsLeaderboard(count, $stateParams.startDate, $stateParams.endDate).then(function (result) {
                        $scope.grid.data = result.leaderboard;
                        $scope.setLeaderboardHeight();
                        if ($stateParams.limit !== count) {
                            $state.go($state.current.name, {limit: count, startDate:$stateParams.startDate, endDate:$stateParams.endDate}, {notify: false});
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
    }]);