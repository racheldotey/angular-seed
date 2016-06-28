'use strict';

/* 
 * Global Team Score Leaderboard
 */

angular.module('app.leaderboards.globalTeams', ['ui.grid', 'ui.grid.autoResize'])
    .controller('GlobalTeamsLeaderboardCtrl', ['$window', '$state', '$stateParams', '$rootScope', '$scope', '$q', 'uiGridConstants', 'ApiRoutesLeaderboards', 'LeaderboardResizing',
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
                { field: 'teamName', displayName:'Team Name', cellClass: 'text-verticle-center' },
                { field: 'homeJoint', displayName:'Team Home Joint', cellClass: 'text-verticle-center' },
                { field: 'mobileScore', displayName:'Mobile Score', cellClass: 'text-verticle-center text-center', type: 'number', sort: { direction: uiGridConstants.DESC, priority: 1 }, maxWidth: 175 },
                { field: 'liveScore', displayName:'Live Team Score', cellClass: 'text-verticle-center text-center', type: 'number', cellFilter: 'numberEx', maxWidth: 175 }
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

                    ApiRoutesLeaderboards.getGlobalTeamsLeaderboard(count, $stateParams.startDate, $stateParams.endDate).then(function (result) {
                        $scope.grid.data = result.leaderboard;
                        $scope.setLeaderboardHeight();
                        if ($stateParams.limit !== count) {
                            $state.go($state.current.name, {limit: count, startDate:$stateParams.startDate, endDate:$stateParams.endDate}, {notify: true});
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