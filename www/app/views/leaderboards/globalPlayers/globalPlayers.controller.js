'use strict';

/* 
 * Global Player Score Leaderboard
 */

angular.module('app.leaderboards.globalPlayers', ['ui.grid', 'ui.grid.autoResize'])
    .controller('GlobalPlayersLeaderboardCtrl', ['$window', '$state', '$stateParams', '$rootScope', '$scope', '$q', 'uiGridConstants', 'ApiRoutesLeaderboards', 'LeaderboardResizing',
        function($window, $state, $stateParams, $rootScope, $scope, $q, uiGridConstants, ApiRoutesLeaderboards, LeaderboardResizing) {
        
            /* Used to restrict alert bars */
            $scope.alertProxy = {};
            
            $scope.title = $rootScope.title;
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
                { field: 'homeJoint', displayName:'Team Home Joint' },
                { field: 'mobileScore', displayName:'Mobile Score', type: 'number', sort: { direction: uiGridConstants.DESC, priority: 1 }, maxWidth: 175 },
                { field: 'liveScore', displayName:'Live Team Score', type: 'number', cellFilter: 'numberEx', maxWidth: 175 }
            ];
            
            $scope.setLeaderboardHeight = function() {
                if($scope.grid.data && $scope.grid.data.length) {
                    LeaderboardResizing.setUIGridHeight();
                } else {
                    angular.element(document.getElementsByClassName('grid')[0]).css('height', '50px');
                }
            };
            
            // Responsive leaderboard height on window resize
            angular.element($window).on('resize', function () {
                $scope.setLeaderboardHeight();
            });
            
            ($scope.refreshGrid = function(limit) {
                return $q(function(resolve, reject) {
                    var count = (angular.isDefined(limit) && parseInt(limit)) ? limit : $stateParams.count;

                    ApiRoutesLeaderboards.getGlobalPlayersLeaderboard(count).then(function (result) {
                        $scope.grid.data = result.leaderboard;
                        $scope.setLeaderboardHeight();
                        if ($stateParams.count !== count) {
                            $state.go($state.current.name, {count: count}, {notify: false});
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