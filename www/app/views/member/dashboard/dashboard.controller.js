'use strict';

/* 
 * Member Dashboard Page
 * 
 * Controller for the member dashboard page.
 */

angular.module('app.member.dashboard', [])
    .controller('MemberDashboardCtrl', ['$scope', '$state', '$compile', '$filter', 'ModalService', 'TriviaModalService', 'AlertConfirmService', 'DataTableHelper', 'DTOptionsBuilder', 'DTColumnBuilder', 'AuthService', 'ApiRoutesEmails',
        function($scope, $state, $compile, $filter, ModalService, TriviaModalService, AlertConfirmService, DataTableHelper, DTOptionsBuilder, DTColumnBuilder, AuthService, ApiRoutesEmails) {
        
        $scope.currentPlayer = AuthService.getUser();
        
        $scope.playerGreeting = '';
        
        ($scope.updateGreeting = function() {
            $scope.playerGreeting = '';
            
            for(var i = 0; i < $scope.currentPlayer.teams.length; i++) {
                if($scope.currentPlayer.teams[i].gameId &&
                    $scope.currentPlayer.teams[i].gameId > 0) {
                
                    $scope.playerGreeting = $scope.playerGreeting + 'Your team "' + 
                        $scope.currentPlayer.teams[i].name + '" is participating in a Trivia game right now. ' + 
                        'Click to view the game: <a data-ui-sref="app.member.game({gameId : ' + $scope.currentPlayer.teams[i].gameId + ', pageId : 1 })">' + 
                        $scope.currentPlayer.teams[i].game + '</a>. ';
                } else {
                    $scope.playerGreeting = $scope.playerGreeting + 'You have been a member of team "' + 
                            $scope.currentPlayer.teams[i].name + '" since ' + 
                            moment($scope.currentPlayer.teams[i].joined, 'YYYY-MM-DD HH:mm:ss').format('MMMM') + ". ";
                }
                //$scope.playerGreeting = $compile($scope.playerGreeting)($scope);
            }
            
        })();
        
        /* Used to restrict alert bars */
        $scope.alertProxy = {};

        // DataTable Setup
        $scope.dtScoreboard = {};
        $scope.dtScoreboard.options = DTOptionsBuilder.newOptions();
        // DataTable Setup
        var teamId = (angular.isDefined($scope.currentPlayer.teams[0])) ? $scope.currentPlayer.teams[0].id : '0';
        $scope.dtGames = DataTableHelper.getDTStructure($scope, 'publicTeamGamesList', teamId);
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
                var scheduled = (type !== 'display') ? moment(data.scheduled, 'YYYY-MM-DD HH:mm:ss').format('x') : 
                        moment(data.scheduled, 'YYYY-MM-DD HH:mm:ss').tz('America/New_York').format('h:mm a on M/D/YYYY ');
                var started = (type !== 'display') ? moment(data.started, 'YYYY-MM-DD HH:mm:ss').format('x') : 
                        moment(data.started, 'YYYY-MM-DD HH:mm:ss').tz('America/New_York').format('h:mm a on M/D/YYYY ');
                var ended = (type !== 'display') ? moment(data.ended, 'YYYY-MM-DD HH:mm:ss').format('x') : 
                        moment(data.ended, 'YYYY-MM-DD HH:mm:ss').tz('America/New_York').format('h:mm a on M/D/YYYY ');
                    
                if(data.ended) {
                    return (type !== 'display') ? ended : 
                            '<span title="Scheduled: ' + scheduled + ' Started: ' + started +  ' Ended: ' + ended +  '">Ended at  ' + ended + '</span>';
                } else if(data.started) {
                    return (type !== 'display') ? started : 
                            '<span title="Scheduled: ' + scheduled + ' Started: ' + started +  '">In progress, started at  ' + started + '</span>';
                } else {
                    return (type !== 'display') ? scheduled : 
                            '<span title="Scheduled: ' + scheduled + '">Scheduled for  ' + scheduled + '</span>';
                }
            }),
            DTColumnBuilder.newColumn('scoreboard').withTitle('Scoreboard').notSortable().withClass('none')
        ];
        $scope.dtGames.options.withOption('order', [4, 'desc']).withOption('responsive', {
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
            var modalInstance = ModalService.openInviteSiteSignup();
            modalInstance.result.then(function(result) {
                $scope.alertProxy.success(result);
            });
        };
        
        $scope.buttonInviteToTeam = function() {
            var modalInstance = ModalService.openInviteToTeam();
            modalInstance.result.then(function(result) {
                $scope.alertProxy.success(result);
            });
        };
        
        $scope.buttonCheckinTeam = function() {
            if($scope.currentPlayer.teams.length <= 0 || angular.isUndefined($scope.currentPlayer.teams[0])) {
                AlertConfirmService.alert("You must be a member of a team to checkin your team.", "Cannot Checkin Team");
            } else {
                var team = $scope.currentPlayer.teams[0];
                
                // If the team is in a game that has started but has not ended
                if(team.gameId && (team.gameStarted !== 'false' && team.gameEnded === 'false')) {
                    
                    AlertConfirmService.confirm("Your team is already checked into an active game. Would you like to view the game scoreboard?", "Already Checked In")
                        .result.then(function (result) {
                            $state.go('app.member.game', { gameId: team.gameId, roundId: 1 });
                        }, function (declined) {});
                        
                } else if (team.gameId && (team.gameStarted === 'false')) {
                    
                    AlertConfirmService.confirm("Your team is already checked into a game but it has not started yet. Would you like to check into a different game?", "Already Checked In")
                        .result.then(function (result) {
                            var modalInstance = TriviaModalService.openAddTeam(false, team);
                            modalInstance.result.then(function (result) {
                                $scope.alertProxy.success("Team successfully checkedin to game.");
                                $scope.dtGames.reloadData();
                            }, function () {});
                        }, function (declined) {});
                        
                } else {
                    
                    var modalInstance = TriviaModalService.openAddTeam(false, team);
                    modalInstance.result.then(function (result) {
                        $scope.alertProxy.success("Team successfully checkedin to game.");
                        $scope.dtGames.reloadData();
                    }, function () {});
                }
            }
        };
        
        
        
        $scope.buttonCreateTeam = function() {
            if($scope.currentPlayer.teams.length > 0) {
                AlertConfirmService.confirm('Warning, if you create a new team you will be removed from your current team, "' + $scope.currentPlayer.teams[0].name + '". Would you like to contine?', 'Warning, Leaving Team!')
                .result.then(function () {
                    var modalInstance = TriviaModalService.openEditTeam(false, $scope.currentPlayer.id);
                    modalInstance.result.then(function (result) {
                        if(result.team.name) {
                            $scope.alertProxy.success("Team " + result.team.name + " added.");
                        }
                        if(result.invites) {
                            for(var i = 0; i < result.invites.length; i++) {
                                if(result.invites[i].error === false) {
                                    $scope.alertProxy.error(result.invites[i].msg);
                                } else {
                                    $scope.alertProxy.success(result.invites[i].msg);
                                }
                            }
                        }
                        
                        AuthService.reloadUser().then(function (result) {
                            $scope.currentPlayer = result;
                            $scope.updateGreeting();
                        }, function () {
                            console.log("Couldnt reload user");
                        });
                    
                    }, function (error) {
                        $scope.alertProxy.error(error);
                    });
                }, function (declined) {});
            } else {
                var modalInstance = TriviaModalService.openEditTeam(false, $scope.currentPlayer.id);
                modalInstance.result.then(function (result) {
                    $scope.alertProxy.success(result);
                        
                    AuthService.reloadUser().then(function (result) {
                        $scope.currentPlayer = result;
                        $scope.updateGreeting();
                    }, function () {
                        console.log("Couldnt reload user");
                    });
                        
                }, function (error) {
                    $scope.alertProxy.error(error);
                });
            }
        };
        
        $scope.buttonJoinTeam = function() {
            if($scope.currentPlayer.teams.length > 0) {
                AlertConfirmService.confirm('Warning, if you join a new team you will be removed from your current team, "' + $scope.currentPlayer.teams[0].name + '". Would you like to contine?', 'Warning, Leaving Team!')
                .result.then(function () {
                    var modalInstance = TriviaModalService.openJoinTeam($scope.currentPlayer.id, $scope.currentPlayer.teams[0]);
                    modalInstance.result.then(function (result) {
                        if(result.team.name) {
                            $scope.alertProxy.success("You have successfully joined Team #" + result.team.id + " - '" + result.team.name + "'.");
                        }
                        if(result.invites) {
                            for(var i = 0; i < result.invites.length; i++) {
                                if(result.invites[i].error === false) {
                                    $scope.alertProxy.error(result.invites[i].msg);
                                } else {
                                    $scope.alertProxy.success(result.invites[i].msg);
                                }
                            }
                        }
                        
                        AuthService.reloadUser().then(function (result) {
                            $scope.currentPlayer = result;
                            $scope.updateGreeting();
                        }, function () {
                            console.log("Couldnt reload user");
                        });
                    
                    }, function (error) {
                        $scope.alertProxy.error(error);
                    });
                }, function (declined) {});
            } else {
                    var modalInstance = TriviaModalService.openJoinTeam($scope.currentPlayer.id, $scope.currentPlayer.teams[0]);
                modalInstance.result.then(function (result) {
                    $scope.alertProxy.success(result);
                        
                    AuthService.reloadUser().then(function (result) {
                        $scope.currentPlayer = result;
                        $scope.updateGreeting();
                    }, function () {
                        console.log("Couldnt reload user");
                    });
                        
                }, function (error) {
                    $scope.alertProxy.error(error);
                });
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
                            $scope.alertProxy.success(result.msg);

                            AuthService.reloadUser().then(function (result) {
                                $scope.currentPlayer = result;
                                $scope.updateGreeting();
                            }, function () {
                                console.log("Couldnt reload user");
                            });

                        }, function (error) {
                            $scope.alertProxy.error(error.msg);
                        });

                    }, function (declined) {});
                }, function (declined) {});
            } else {
                ApiRoutesEmails.acceptTeamInvite(token, $scope.currentPlayer.id, teamId).then(function (result) {
                    $scope.alertProxy.success(result.msg);

                    AuthService.reloadUser().then(function (result) {
                        $scope.currentPlayer = result;
                        $scope.updateGreeting();
                    }, function () {
                        console.log("Couldnt reload user");
                    });

                }, function (error) {
                    $scope.alertProxy.error(error.msg);
                });
            }
        };
        
        $scope.buttonDeclineInvitation = function(token, teamName, teamId) {
            ApiRoutesEmails.declineTeamInvite(token, $scope.currentPlayer.id, teamId).then(function (result) {
                $scope.alertProxy.success(result.msg);
                        
                AuthService.reloadUser().then(function (result) {
                    $scope.currentPlayer = result;
                    $scope.updateGreeting();
                }, function () {
                    console.log("Couldnt reload user");
                });
                        
            }, function (error) {
                $scope.alertProxy.error(error.msg);
            });
        };

    }]);