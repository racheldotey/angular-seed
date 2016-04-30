'use strict';

/* 
 * Member Dashboard Page
 * 
 * Controller for the member dashboard page.
 */

angular.module('app.member.dashboard', [])
    .controller('MemberDashboardCtrl', ['$scope', '$state', '$compile', '$filter', 'TriviaModalService', 'AlertConfirmService', 'DataTableHelper', 'DTOptionsBuilder', 'DTColumnBuilder', 'AuthService', 'ApiRoutesEmails',
        function($scope, $state, $compile, $filter, TriviaModalService, AlertConfirmService, DataTableHelper, DTOptionsBuilder, DTColumnBuilder, AuthService, ApiRoutesEmails) {
        
        $scope.currentPlayer = AuthService.getUser();
        
        $scope.playerGreeting = '';
        
        ($scope.updateGreeting = function() {
            $scope.playerGreeting = 'Welcome back ' + $scope.currentPlayer.displayName + '!';
            
            for(var i = 0; i < $scope.currentPlayer.teams.length; i++) {
                $scope.playerGreeting = $scope.playerGreeting + ' You have been a member of team "' + 
                        $scope.currentPlayer.teams[i].name + '" since ' + 
                        moment($scope.currentPlayer.teams[i].joined, 'YYYY-MM-DD HH:mm:ss').format('MMMM') + ".";
            }
            
        })();
        
        /* Used to restrict alert bars */
        $scope.alertProxy = {};

        // DataTable Setup
        $scope.dtScoreboard = {};
        $scope.dtScoreboard.options = DTOptionsBuilder.newOptions();
        // DataTable Setup
        $scope.dtGames = DataTableHelper.getDTStructure($scope, 'publicGamesList');
        $scope.dtGames.columns = [
            DTColumnBuilder.newColumn(null).withTitle('Scoreboard').withClass('responsive-control text-center noclick').renderWith(function(data, type, full, meta) {
                return '<a><small>Scoreboard</small> <i class="fa"></i></a>';
            }).notSortable(),
            DTColumnBuilder.newColumn(null).withTitle('Game Name').renderWith(function (data, type, full, meta) {
                return '<a data-ui-sref="app.member.game({gameId : ' + data.id + ', pageId : 1 })">' + data.name + '</a>';
            }),
            DTColumnBuilder.newColumn('venue').withTitle('Joint'),
            DTColumnBuilder.newColumn('host').withTitle('Host'),
            DTColumnBuilder.newColumn(null).withTitle('Status').renderWith(function (data, type, full, meta) {
                var scheduled = moment(data.scheduled, 'YYYY-MM-DD HH:mm:ss').format('h:mm a on M/D/YYYY ');
                var started = moment(data.started, 'YYYY-MM-DD HH:mm:ss').format('h:mm a on M/D/YYYY ');
                var ended = moment(data.ended, 'YYYY-MM-DD HH:mm:ss').format('h:mm a on M/D/YYYY ');
                    
                if(data.ended) {
                    return '<span title="Scheduled: ' + scheduled + ' Started: ' + started +  ' Ended: ' + ended +  '">Ended at  ' + ended + '</span>';
                } else if(data.started) {
                    return '<span title="Scheduled: ' + scheduled + ' Started: ' + started +  '">In progress, started at  ' + started + '</span>';
                } else {
                    return '<span title="Scheduled: ' + scheduled + '">Scheduled for  ' + scheduled + '</span>';
                }
            }),
            DTColumnBuilder.newColumn('scoreboard').withTitle('Scoreboard').notSortable().withClass('none')
        ];
        $scope.dtGames.options.withOption('responsive', {
            details: {
                type: 'column',
                renderer: function(api, rowIdx, columns) {
                    var data = {};
                    angular.forEach(columns, function (value, key) {
                        if(value.title === 'Scoreboard') {
                            data = value.data;
                        }
                    });
                        
                    var header = '<table datatable="" dt-options="dtScoreboard.options" class="table sub-table table-striped">\n\
                        <thead><tr>\n\
                        <th>Rank</th>\n\
                        <th>Score</th>\n\
                        <th>Team</th>\n\
                        </tr></thead><tbody>';

                    var body = '';
                    $.each(data, function(index, value) {
                        //var winner = (value.gameWinner === '1') ? '' : '';
                        var score = $filter('numberEx')(value.gameScore);
                        var name = (!value.teamName) ? "Team ID #" + value.teamId : value.teamName;
                        body += '<tr><td>' + value.gameRank + '</td><td>' + score + '</td><td>' + name + '</td></tr>\n';
                    });

                    // Create angular table element
                    body = (body) ? body : '<tr><td colspan="3"><p>There are no teams participating in this game.</p></td></tr>';

                    var table = angular.element(header + body + '</tbody></table>');

                    // compile the table to keep the directives (ngClick)
                    $compile(table.contents())($scope);

                    return table;
                }
            }
        });
        
        /* Button Click Events */
        
        $scope.buttonInviteSiteSignup = function() {
            var modalInstance = TriviaModalService.openInviteSiteSignup();
            modalInstance.result.then(function(result) {
                $scope.alertProxy.success(result);
            });
        };
        
        $scope.buttonInviteToTeam = function() {
            var modalInstance = TriviaModalService.openInviteToTeam();
            modalInstance.result.then(function(result) {
                $scope.alertProxy.success(result);
            });
        };
        
        $scope.buttonCheckinTeam = function() {
            if($scope.currentPlayer.teams.length) {
                var modalInstance = TriviaModalService.openAddTeam(false, $scope.currentPlayer.teams[0]);
                modalInstance.result.then(function (result) {
                    $scope.alertProxy.success("Team successfully checked in to current game.");
                }, function () {});
            } else {
                AlertConfirmService.alert("You must be a member of a team to checkin your team.", "Cannot Checkin Team");
            }
        };
        
        $scope.buttonCreateTeam = function() {
            if($scope.currentPlayer.teams.length > 0) {
                AlertConfirmService.confirm('Warning, if you create a new team you will be removed from your current team, "' + $scope.currentPlayer.teams[0].name + '". Would you like to contine?', 'Warning, Leaving Team!')
                .result.then(function () {
                    var modalInstance = TriviaModalService.openEditTeam(false, $scope.currentPlayer.id);
                    modalInstance.result.then(function (result) {
                        
                        AuthService.reloadUser().then(function (result) {
                            $scope.currentPlayer = result;
                            $scope.updateGreeting();
                        }, function () {
                            console.log("Couldnt reload user");
                        });
                    
                    }, function () {});
                }, function (declined) {});
            } else {
                var modalInstance = TriviaModalService.openEditTeam(false, $scope.currentPlayer.id);
                modalInstance.result.then(function (result) {
                        
                    AuthService.reloadUser().then(function (result) {
                        $scope.currentPlayer = result;
                        $scope.updateGreeting();
                    }, function () {
                        console.log("Couldnt reload user");
                    });
                        
                }, function () {});
            }
        };
        
        /* Team Invite Managment */
        
        $scope.buttonAcceptInvitation = function(token, teamName, teamId) {
            if($scope.currentPlayer.teams.length > 0) {
                AlertConfirmService.confirm('Warning, if you accept this invite team you will be removed from your current team, "' + $scope.currentPlayer.teams[0].name + '" and added to the new team. Would you like to contine?', 'Warning, Leaving Team!')
                .result.then(function () {
                    AlertConfirmService.confirm('Wait, are you absolutely sure that you want to leave "' + $scope.currentPlayer.teams[0].name + '" and join "' + teamName + '"?', 'Warning, Leaving Team!')
                    .result.then(function () {

                        ApiRoutesEmails.acceptTeamInvite(token, $scope.currentPlayer.id, teamId).then(function (result) {

                            AuthService.reloadUser().then(function (result) {
                                $scope.currentPlayer = result;
                                $scope.updateGreeting();
                            }, function () {
                                console.log("Couldnt reload user");
                            });

                        }, function () {});

                    }, function (declined) {});
                }, function (declined) {});
            } else {
                ApiRoutesEmails.acceptTeamInvite(token, $scope.currentPlayer.id, teamId).then(function (result) {

                    AuthService.reloadUser().then(function (result) {
                        $scope.currentPlayer = result;
                        $scope.updateGreeting();
                    }, function () {
                        console.log("Couldnt reload user");
                    });

                }, function () {});
            }
        };
        
        $scope.buttonDeclineInvitation = function(token, teamName, teamId) {
            ApiRoutesEmails.declineTeamInvite(token, $scope.currentPlayer.id, teamId).then(function (result) {
                        
                AuthService.reloadUser().then(function (result) {
                    $scope.currentPlayer = result;
                    $scope.updateGreeting();
                }, function () {
                    console.log("Couldnt reload user");
                });
                        
            }, function () {});
        };

    }]);