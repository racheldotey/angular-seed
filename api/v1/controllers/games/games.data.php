<?php namespace API;
 require_once dirname(dirname(dirname(__FILE__))) . '/services/api.dbconn.php';

class GameData {
  
    static function selectGame($gameId, $roundNumber = 1) {
        $game = DBConn::selectOne("SELECT g.id, g.name, g.scheduled, g.venue_id AS venueId, g.host_user_id AS hostId, "
                . "game_started AS started, game_ended AS ended, max_points maxPoints, v.name AS venue, "
                . "CONCAT(u.name_first, ' ', u.name_last) AS updatedBy, "
                . "CONCAT(h.name_first, ' ', h.name_last) AS hostName "
                . "FROM " . DBConn::prefix() . "games AS g "
                . "LEFT JOIN " . DBConn::prefix() . "users AS u ON u.id = g.last_updated_by "
                . "LEFT JOIN " . DBConn::prefix() . "users AS h ON h.id = g.last_updated_by "
                . "LEFT JOIN " . DBConn::prefix() . "venues AS v ON v.id = g.venue_id "
                . "WHERE g.id = :game_id LIMIT 1;", array(':game_id' => $gameId));
        
        if(!$game) {  
            return $game;
        }
        
        // Teams and their score
        $qTeams = DBConn::executeQuery("SELECT t.id AS teamId, t.name, g.score AS gameScore, game_rank AS gameRank, game_winner AS gameWinner "
                . "FROM " . DBConn::prefix() . "game_score_teams AS g "
                . "JOIN " . DBConn::prefix() . "teams AS t ON g.team_id = t.id "
                . "WHERE g.game_id = :game_id;", array(':game_id' => $gameId));
        
        $qTeamRounds = DBConn::preparedQuery("SELECT r.id AS roundId, r.order AS number, r.max_points AS maxPoints, "
                . "r.default_question_points AS defaultQuestionPoints, "
                . "s.score AS roundScore, s.round_rank AS roundRank "
                . "FROM " . DBConn::prefix() . "game_rounds AS r "
                . "LEFT JOIN " . DBConn::prefix() . "game_score_rounds AS s ON r.id = s.round_id "
                . "WHERE r.game_id = :game_id AND s.team_id = :team_id ORDER BY r.order;");

        $qTeamQuestions = DBConn::preparedQuery("SELECT q.id AS questionId, q.order AS number, "
                . "q.max_points AS maxPoints, s.score AS questionScore "
                . "FROM " . DBConn::prefix() . "game_round_questions AS q "
                . "LEFT JOIN " . DBConn::prefix() . "game_score_questions AS s ON q.id = s.question_id "
                . "WHERE q.round_id = :round_id AND s.team_id = :team_id ORDER BY q.order;");
        
        //// FORMAT SCOREBOARD
        $teams = Array();
        while($team = $qTeams->fetch(\PDO::FETCH_OBJ)) {
            //// TEAM ROUND SCORES
            $teamRounds = Array();
            $qTeamRounds->execute(array(':game_id' => $gameId, ':team_id' => $team->teamId));
            while($round = $qTeamRounds->fetch(\PDO::FETCH_OBJ)) {
                //// TEAM QUESTION SCORES
                $teamQuestions = Array();
                $qTeamQuestions->execute(array(':round_id' => $round->roundId, ':team_id' => $team->teamId));
                while($question = $qTeamQuestions->fetch(\PDO::FETCH_OBJ)) {
                    $teamQuestions[$question->number] = $question;
                }
                $round->questions = $teamQuestions;
                $teamRounds[$round->number] = $round;
            }
            $team->rounds = $teamRounds;
            $teams[$team->teamId] = $team;
        }
        //// OVERALL TEAM SCORE
        $game->teams = $teams;

        // All Game Rounds and Questions
        $qGameRounds = DBConn::preparedQuery("SELECT r.id AS roundId, r.order AS number, r.name FROM " . DBConn::prefix() . "game_rounds AS r "
                . "WHERE r.game_id = :game_id ORDER BY r.order;");

        $qRoundQuestions = DBConn::preparedQuery("SELECT q.id AS questionId, q.order AS number, "
                . "q.max_points AS maxPoints "
                . "FROM " . DBConn::prefix() . "game_round_questions AS q "
                . "WHERE q.round_id = :round_id ORDER BY q.order;");
        
        $gameRounds = Array();
        $qGameRounds->execute(array(':game_id' => $gameId));
        while($round = $qGameRounds->fetch(\PDO::FETCH_OBJ)) {
            //// GAME QUESTIONS
            $roundQuestions = Array();
            $qRoundQuestions->execute(array(':round_id' => $round->roundId));
            while ($question = $qRoundQuestions->fetch(\PDO::FETCH_OBJ)) {
                $roundQuestions[$question->number] = $question;
            }
            $round->questions = $roundQuestions;
            $gameRounds[$round->number] = $round;
        }
        $game->rounds = $gameRounds;
        
        return $game;
    }
  
    static function selectGameRound($gameId, $roundNumber) {
            
            // Game Rounds
            $round = DBConn::selectOne("SELECT r.id AS roundId, r.order AS roundNumber, r.name, r.max_points AS maxPoints, r.default_question_points AS defaultQuestionPoints "
                    . "FROM " . DBConn::prefix() . "game_rounds AS r WHERE r.game_id = :game_id AND r.order = :order;", 
                    array(':game_id' => $gameId, ':order' => $roundNumber));
            
            if($round) {

                $round->questions = DBConn::selectAll("SELECT q.id AS questionId, q.order AS questionNumber, q.question, q.max_points AS maxPoints "
                        . "FROM " . DBConn::prefix() . "game_round_questions AS q  WHERE q.round_id = :round_id ORDER BY q.order;", 
                        array(':round_id' => $round->roundId));
                
                $qRoundTeams = DBConn::executeQuery("SELECT t.name AS team, s.team_id AS teamId, "
                        . "IFNULL(s.score, 0) AS gameScore, s.game_rank AS gameRank, s.game_winner AS gameWinner, "
                        . "IFNULL(r.score, 0) AS roundScore, IFNULL(r.round_rank, 0) AS roundRank "
                        . "FROM " . DBConn::prefix() . "game_score_teams AS s "
                        . "LEFT JOIN " . DBConn::prefix() . "teams AS t ON t.id = s.team_id "
                        . "LEFT JOIN " . DBConn::prefix() . "game_score_rounds AS r ON s.team_id = r.team_id AND r.round_id = :round_id "
                        . "WHERE s.game_id = :game_id "
                        . "ORDER BY s.team_id;", array(':game_id' => $gameId, ':round_id' => $round->roundId));

                $qTeamScores = DBConn::preparedQuery("SELECT q.id AS questionId, IFNULL(s.score, 0) AS questionScore "
                        . "FROM " . DBConn::prefix() . "game_round_questions AS q "
                        . "LEFT JOIN " . DBConn::prefix() . "game_score_questions AS s ON s.question_id = q.id AND s.team_id = :team_id "
                        . "WHERE q.round_id = :round_id "
                        . "ORDER BY q.order;");

                $teams = Array();
                while($team = $qRoundTeams->fetch(\PDO::FETCH_OBJ)) {
                    // Team Round Scores
                    $qTeamScores->execute(array(':round_id' => $round->roundId, ':team_id' => $team->teamId));
                    $team->scores = $qTeamScores->fetchAll(\PDO::FETCH_OBJ);

                    array_push($teams, $team);
                }
                $round->teams = $teams;
                
            }
            
            return $round;
    }
  
    static function selectRound($roundId) {
            
            // Game Rounds
            $round = DBConn::selectOne("SELECT r.id AS roundId, r.game_id AS gameId, r.order AS roundNumber, r.name, r.max_points AS maxPoints, r.default_question_points AS defaultQuestionPoints "
                    . "FROM " . DBConn::prefix() . "game_rounds AS r WHERE r.id = :id;", 
                    array(':id' => $roundId));
            
            if($round) {

                $round->questions = DBConn::selectAll("SELECT q.id AS questionId, q.order AS questionNumber, q.question, q.max_points AS maxPoints "
                        . "FROM " . DBConn::prefix() . "game_round_questions AS q  WHERE q.round_id = :round_id ORDER BY q.order;", 
                        array(':round_id' => $round->roundId));
                
                $qRoundTeams = DBConn::executeQuery("SELECT t.name AS team, s.team_id AS teamId, "
                        . "IFNULL(s.score, 0) AS gameScore, s.game_rank AS gameRank, s.game_winner AS gameWinner, "
                        . "IFNULL(r.score, 0) AS roundScore, IFNULL(r.round_rank, 0) AS roundRank "
                        . "FROM " . DBConn::prefix() . "game_score_teams AS s "
                        . "LEFT JOIN " . DBConn::prefix() . "teams AS t ON t.id = s.team_id "
                        . "LEFT JOIN " . DBConn::prefix() . "game_score_rounds AS r ON s.team_id = r.team_id AND r.round_id = :round_id "
                        . "WHERE s.game_id = :game_id "
                        . "ORDER BY s.team_id;", array(':game_id' => $round->gameId, ':round_id' => $round->roundId));

                $qTeamScores = DBConn::preparedQuery("SELECT q.id AS questionId, IFNULL(s.score, 0) AS questionScore "
                        . "FROM " . DBConn::prefix() . "game_round_questions AS q "
                        . "LEFT JOIN " . DBConn::prefix() . "game_score_questions AS s ON s.question_id = q.id AND s.team_id = :team_id "
                        . "WHERE q.round_id = :round_id "
                        . "ORDER BY q.order;");

                $teams = Array();
                while($team = $qRoundTeams->fetch(\PDO::FETCH_OBJ)) {
                    // Team Round Scores
                    $qTeamScores->execute(array(':round_id' => $round->roundId, ':team_id' => $team->teamId));
                    $team->scores = $qTeamScores->fetchAll(\PDO::FETCH_OBJ);

                    array_push($teams, $team);
                }
                $round->teams = $teams;
                
            }
            
            return $round;
    }
    
    static function getRoundCount($gameId) {
        return DBConn::selectColumn("SELECT COUNT(id) AS count FROM " . DBConn::prefix() . "game_rounds "
                . "WHERE game_id=:game_id LIMIT 1;", array(':game_id' => $gameId));
    }
    
    static function getQuestionCount($roundId) {
        return DBConn::selectColumn("SELECT COUNT(id) AS count FROM " . DBConn::prefix() . "game_round_questions "
                . "WHERE round_id=:round_id LIMIT 1;", array(':round_id' => $roundId));
    }
    
    static function insertStartingRounds($count, $gameId, $defaultPoints, $currentUser) {
        $qAddRound = DBConn::preparedQuery("INSERT INTO " . DBConn::prefix() . "game_rounds(name, `order`, game_id, default_question_points, created_user_id, last_updated_by) "
                . "VALUES (:name, :order, :game_id, :default_question_points, :created_user_id, :last_updated_by);");
        
        $results = array();
        for($i = 1; $i <= $count; $i++) {
            $results[] = $qAddRound->execute(array(
                ":name" => "Round #" . $i,
                ":order" => $i,
                ":game_id" => $gameId,
                ":default_question_points" => $defaultPoints,
                ":created_user_id" => $currentUser,
                ":last_updated_by" => $currentUser
            ));
        }
        return $results;
    }
    
    static function insertRound($validRound) {
        $results = DBConn::insert("INSERT INTO " . DBConn::prefix() . "game_rounds(name, `order`, game_id, default_question_points, created_user_id, last_updated_by) "
                . "VALUES (:name, :order, :game_id, :default_question_points, :created_user_id, :last_updated_by);", $validRound);
        
        if($results) {
            self::calculateGameScores($validRound[':game_id'], $validRound[':created_user_id']);
        }
        
        return $results;
    }
    
    static function insertQuestion($validQuestion) {
        $results = DBConn::insert("INSERT INTO " . DBConn::prefix() . "game_round_questions(question, `order`, game_id, round_id, max_points, created_user_id, last_updated_by) "
                . "VALUES (:question, :order, :game_id, :round_id, :max_points, :created_user_id, :last_updated_by);", $validQuestion);
        
        if($results) {
            self::calculateGameScores($validQuestion[':game_id'], $validQuestion[':created_user_id']);
        }
        
        return $results;
    }
    
    static function insertTeamIntoGame($validTeam) {
        $results = DBConn::insert("INSERT INTO " . DBConn::prefix() . "game_score_teams(`game_id`, `team_id`, `created_user_id`, `last_updated_by`) "
                . "VALUES (:game_id, :team_id, :created_user_id, :last_updated_by);", $validTeam);
        
        if($results) {
            self::calculateGameScores($validTeam[':game_id'], $validTeam[':created_user_id']);
        }
        
        return $results;
    }
    
    /* CRUD for Games */
  
    static function insertGame($validGame) {
        return DBConn::insert("INSERT INTO " . DBConn::prefix() . "games(name, venue_id, host_user_id, scheduled, created_user_id, last_updated_by) "
                . "VALUES (:name, :venue_id, :host_user_id, :scheduled, :created_user_id, :last_updated_by);", $validGame);
    }
    
    static function updateGame($validGame) {
        return DBConn::update("UPDATE " . DBConn::prefix() . "games SET name=:name, venue_id=:venue_id, "
                . "host_user_id=:host_user_id, scheduled=:scheduled, last_updated_by=:last_updated_by "
                . "WHERE id = :id;", $validGame);
    }
    
    static function updateStartGame($game) {
        return DBConn::update("UPDATE " . DBConn::prefix() . "games SET game_started=NOW(), "
                . "last_updated_by=:last_updated_by WHERE id=:id;", $game);
        /*
        return DBConn::update("UPDATE " . DBConn::prefix() . "games SET game_started=NOW(), "
                . "last_updated_by=:last_updated_by WHERE id=:id AND game_started IS NULL;", $game);
         */
    }
    
    static function selectStarted($gameId) {
        return DBConn::selectColumn("SELECT game_started FROM " . DBConn::prefix() . "games "
                . "WHERE id=:id LIMIT 1;", array(':id' => $gameId));
    }
    
    static function updateEndGame($game) {
        return DBConn::update("UPDATE " . DBConn::prefix() . "games SET game_ended=NOW(), last_updated_by=:last_updated_by "
                . "WHERE id=:id AND game_started IS NOT NULL;", $game);
        /*
        return DBConn::update("UPDATE " . DBConn::prefix() . "games SET game_ended=NOW(), last_updated_by=:last_updated_by "
                . "WHERE id=:id AND game_started IS NOT NULL AND game_ended IS NULL;", $game);
         */
    }
    
    static function selectEnded($gameId) {
        return DBConn::selectColumn("SELECT game_ended FROM " . DBConn::prefix() . "games "
                . "WHERE id=:id LIMIT 1;", array(':id' => $gameId));
    }
    
    
    
    static function saveQuestionScores($scores) {
        $saveQuestioneScore = DBConn::preparedQuery("INSERT INTO " . DBConn::prefix() . "game_score_questions(game_id, round_id, question_id, team_id, score, created_user_id) "
                . "VALUES (:game_id,:round_id,:question_id,:team_id,:score,:created_user_id) "
                . "ON DUPLICATE KEY UPDATE score = :dup_score, last_updated_by = :last_updated_by;");
        $result = array();
        foreach($scores as $score) {
            $result[] = $saveQuestioneScore->execute($score);
        }
        return $result;
    }
    
    static function calculateGameScores($gameId, $currentUser) {
        
        $result = array();
        $game = array(':game_id' => $gameId);
        
        $teams = DBConn::selectColumn("SELECT team_id FROM " . DBConn::prefix() . "game_score_teams "
                . "WHERE game_id=:game_id;", $game);
        
        $rounds = DBConn::selectColumn("SELECT id FROM " . DBConn::prefix() . "game_rounds "
                 . "WHERE game_id=:game_id;", $game);
                
        $saveRoundScore = DBConn::preparedQuery("INSERT INTO " . DBConn::prefix() . "game_score_rounds (game_id,round_id,team_id,created_user_id) "
                . "VALUES (:game_id,:round_id,:team_id,:created_user_id) "
                . "ON DUPLICATE KEY UPDATE score = 0, round_rank = 0, last_updated_by = :last_updated_by;");
        
        $saveTeamScore = DBConn::preparedQuery("INSERT INTO " . DBConn::prefix() . "game_score_teams (game_id,team_id,created_user_id) "
                . "VALUES (:game_id,:team_id,:created_user_id) "
                . "ON DUPLICATE KEY UPDATE score = 0, game_rank = 0, last_updated_by = :last_updated_by;");
        
        for($t = 0; $t < count($teams); $t++) {
            $saved = $saveTeamScore->execute(array_merge(array(), $game, array(
                ':team_id' => $teams[$t],
                ':created_user_id' => $currentUser,
                ':last_updated_by' => $currentUser
            )));
            
            $result[] = "Team #" . $teams[$t] . " saved = " . $saved;
            
            for($r = 0; $r < count($rounds); $r++) {
                $saved = $saveRoundScore->execute(array_merge(array(), $game, array(
                    ':team_id' => $teams[$t], 
                    ':round_id' => $rounds[$r],
                    ':created_user_id' => $currentUser,
                    ':last_updated_by' => $currentUser
                )));
            
                $result[] = "Team #" . $teams[$t] . " Round #" . $rounds[$r] .  " saved = " . $saved;
            }
        }
        
        // Update Max Score for Game Rounds
        $result[] = DBConn::update("UPDATE " . DBConn::prefix() . "game_rounds AS r "
                . "INNER JOIN (SELECT qs.round_id, SUM(qs.max_points) AS maxpoints "
                . "FROM " . DBConn::prefix() . "game_round_questions AS qs GROUP BY qs.round_id) AS q ON r.id = q.round_id "
                . "SET r.max_points = q.maxpoints WHERE r.game_id = :game_id;", $game);
        
        
        // Update Team Totals for each Round
        $result[] = DBConn::update("UPDATE " . DBConn::prefix() . "game_score_rounds AS r "
                . "INNER JOIN (SELECT qs.round_id, qs.team_id, SUM(qs.score) AS total "
                . "FROM " . DBConn::prefix() . "game_score_questions AS qs GROUP BY qs.round_id, qs.team_id) AS q "
                . "ON r.round_id = q.round_id AND r.team_id = q.team_id SET r.score = q.total WHERE r.game_id = :game_id;", $game);
        
        // Update Team Totals for the Game Rounds
        $result[] = DBConn::update("SET @lastscore = 0; SET @ordering = 0; "
                . "UPDATE " . DBConn::prefix() . "game_score_rounds SET round_rank = "
                . "IF(score = @lastscore, @ordering, (@ordering := @ordering + 1)), "
                . "score = (@lastscore := score) WHERE game_id = :game_id ORDER BY score DESC;", $game);
                
        // Update Max Score for Game
        $result[] = DBConn::update("UPDATE " . DBConn::prefix() . "games AS g "
                . "INNER JOIN (SELECT rs.game_id, SUM(rs.max_points) AS maxpoints "
                . "FROM " . DBConn::prefix() . "game_rounds AS rs) AS r ON r.game_id = g.id "
                . "SET g.max_points = r.maxpoints WHERE g.id = :game_id;", $game);
        
        // Update Team Totals for the Game
        $result[] = DBConn::update("UPDATE " . DBConn::prefix() . "game_score_teams AS t "
                . "INNER JOIN (SELECT rs.game_id, rs.team_id, SUM(rs.score) AS total "
                . "FROM " . DBConn::prefix() . "game_score_rounds AS rs GROUP BY rs.team_id) AS r "
                . "ON t.game_id = r.game_id AND t.team_id = r.team_id SET t.score = r.total WHERE t.game_id = :game_id;", $game);
        
        
        // Update Team Totals for the Game
        $result[] = DBConn::update("SET @lastscore = 0; SET @ordering = 0; "
                . "UPDATE " . DBConn::prefix() . "game_score_teams SET game_rank = "
                . "IF(score = @lastscore, @ordering, (@ordering := @ordering + 1)), "
                . "score = (@lastscore := score) WHERE game_id = :game_id ORDER BY score DESC;", $game);
        
        return $result;
    }
}
