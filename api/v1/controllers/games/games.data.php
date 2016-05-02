<?php namespace API;
 require_once dirname(dirname(dirname(__FILE__))) . '/services/api.dbconn.php';
 require_once dirname(dirname(dirname(__FILE__))) . '/services/api.auth.php';

class GameData {
  
    static function selectGame($gameId) {
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
        $qTeams = DBConn::executeQuery("SELECT t.id AS teamId, t.name, IFNULL(g.score, 0) AS gameScore, IFNULL(game_rank, 0) AS gameRank, game_winner AS gameWinner "
                . "FROM " . DBConn::prefix() . "game_score_teams AS g "
                . "JOIN " . DBConn::prefix() . "teams AS t ON g.team_id = t.id "
                . "WHERE g.game_id = :game_id;", array(':game_id' => $gameId));
        
        $qTeamRounds = DBConn::preparedQuery("SELECT r.id AS roundId, r.order AS number, "
                . "r.max_points AS maxPoints, r.default_question_points AS defaultQuestionPoints, "
                . "IFNULL(s.score, 0) AS roundScore, IFNULL(s.round_rank, 0) AS roundRank "
                . "FROM " . DBConn::prefix() . "game_rounds AS r LEFT JOIN (SELECT sr.round_id, sr.score, sr.round_rank "
                . "FROM " . DBConn::prefix() . "game_score_rounds AS sr WHERE sr.team_id = :team_id) AS s ON s.round_id = r.id "
                . "WHERE r.game_id = :game_id ORDER BY r.order;");
        
        $qTeamQuestions = DBConn::preparedQuery("SELECT q.id AS questionId, q.order AS number, q.max_points AS maxPoints, "
                . "IFNULL(s.wager, 0) AS teamWager, IFNULL(s.score, 0) AS questionScore, IFNULL(s.answer, '') AS answer "
                . "FROM " . DBConn::prefix() . "game_round_questions AS q "
                . "LEFT JOIN (SELECT sq.question_id, sq.score, sq.answer, sq.wager "
                . "FROM " . DBConn::prefix() . "game_score_questions AS sq "
                . "WHERE sq.team_id = :team_id) AS s ON s.question_id = q.id "
                . "WHERE q.round_id = :round_id ORDER BY q.order;");
        
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
        $qGameRounds = DBConn::preparedQuery("SELECT r.id AS roundId, r.order AS number, r.name, "
                . "r.max_points AS maxPoints, r.default_question_points AS defaultQuestionPoints "
                . "FROM " . DBConn::prefix() . "game_rounds AS r "
                . "WHERE r.game_id = :game_id ORDER BY r.order;");

        $qRoundQuestions = DBConn::preparedQuery("SELECT q.id AS questionId, q.order AS number, question, "
                . "wager, q.max_points AS maxPoints "
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
      
    static function getRoundCount($gameId) {
        return DBConn::selectColumn("SELECT COUNT(id) AS count FROM " . DBConn::prefix() . "game_rounds "
                . "WHERE game_id=:game_id LIMIT 1;", array(':game_id' => $gameId));
    }
    
    static function getQuestionCount($roundId) {
        return DBConn::selectColumn("SELECT COUNT(id) AS count FROM " . DBConn::prefix() . "game_round_questions "
                . "WHERE round_id=:round_id LIMIT 1;", array(':round_id' => $roundId));
    }
    
    /* Team and Game interactions */
    static function selectTeamCurrentGameId($teamId) {
        return DBConn::selectColumn("SELECT current_game_id FROM " . DBConn::prefix() . "teams "
                . "WHERE id = :id AND current_game_id IS NOT NULL LIMIT 1;", array(':id' => $teamId));        
    }
    
    static function insertTeamIntoGame($validTeam) {
        $results = DBConn::insert("INSERT INTO " . DBConn::prefix() . "game_score_teams(`game_id`, `team_id`, `created_user_id`, `last_updated_by`) "
                . "VALUES (:game_id, :team_id, :created_user_id, :last_updated_by);", $validTeam);
        
        if($results) {
            self::calculateGameScores($validTeam[':game_id'], $validTeam[':created_user_id']);
            self::updateTeamCheckedInStatus(array(
                ':id' => $validTeam[':team_id'], 
                ':current_game_id' => $validTeam[':game_id'], 
                ':last_updated_by' => $validTeam[':last_updated_by']));
        }
        
        return $results;
    }
        
    static function updateTeamCheckedInStatus($validTeam) {
        return DBConn::update("UPDATE " . DBConn::prefix() . "teams SET current_game_id=:current_game_id, last_updated_by=:last_updated_by "
                . "WHERE id = :id;", $validTeam);
    }
        
    static function checkoutTeamsFromGame($gameId, $userId) {
        return DBConn::update("UPDATE " . DBConn::prefix() . "teams SET current_game_id=0, last_updated_by=:last_updated_by "
                . "WHERE current_game_id = :current_game_id;", 
                array(':current_game_id' => $gameId, ":last_updated_by" => $userId));
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
    
    static function updateEndGame($gameId, $userId) {
        self::calculateGameScores($gameId);
        self::checkoutTeamsFromGame($gameId, $userId);
        
        DBConn::update("UPDATE " . DBConn::prefix() . "game_score_teams SET game_winner=1, last_updated_by=:last_updated_by "
                . "WHERE game_id=:game_id AND game_rank = 1;", array(':game_id' => $gameId, ":last_updated_by" => $userId));
        
        DBConn::update("UPDATE " . DBConn::prefix() . "teams SET current_game_id=NULL, last_updated_by=:last_updated_by "
                . "WHERE current_game_id=:current_game_id;", array(':current_game_id' => $gameId, ":last_updated_by" => $userId));
        
        return DBConn::update("UPDATE " . DBConn::prefix() . "games SET game_ended=NOW(), last_updated_by=:last_updated_by "
                . "WHERE id=:id;", array(":id" => $gameId, ":last_updated_by" => $userId));
    }
    
    static function selectEnded($gameId) {
        return DBConn::selectColumn("SELECT game_ended FROM " . DBConn::prefix() . "games "
                . "WHERE id=:id LIMIT 1;", array(':id' => $gameId));
    }
    
    /* CRUD for Game Rounds */
    
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
            self::calculateGameScores($validRound[':game_id']);
        }
        
        return $results;
    }
    
    static function updateRound($validRound) {
        $results = DBConn::update("UPDATE " . DBConn::prefix() . "game_rounds SET "
                . "name = :name, default_question_points = :default_question_points, last_updated_by = :last_updated_by "
                . "WHERE id = :id AND game_id = :game_id;", $validRound);
        
        if($results) {
            self::calculateGameScores($validRound[':game_id']);
        }
        
        return $results;
    }

    static function deleteRound($validRound) {
        $results = DBConn::delete("DELETE FROM " . DBConn::prefix() . "game_score_rounds "
                . "WHERE game_id = :game_id AND round_id = :round_id;", $validRound);
        
        $results = DBConn::delete("DELETE FROM " . DBConn::prefix() . "game_rounds "
                . "WHERE id = :id AND game_id = :game_id;", $validRound);
        
        if($results) {
            self::updateRoundOrder($validRound[':game_id']);
            self::calculateGameScores($validRound[':game_id']);
        }
        
        return $results;
    }
    
    private static function updateRoundOrder($gameId) {
        return DBConn::update("SET @rownumber = 0; "
                . "UPDATE " . DBConn::prefix() . "game_rounds SET `order` = (@rownumber:=@rownumber+1) "
                . "WHERE game_id = :game_id ORDER BY `order` ASC;", array(':game_id' => $gameId));
    }
    
    /* CRUD for Game Questions */
    
    static function insertQuestion($validQuestion) {
        $results = DBConn::insert("INSERT INTO " . DBConn::prefix() . "game_round_questions(question, `order`, game_id, round_id, max_points, wager, created_user_id, last_updated_by) "
                . "VALUES (:question, :order, :game_id, :round_id, :max_points, :wager, :created_user_id, :last_updated_by);", $validQuestion);
        
        if($results) {
            self::calculateGameScores($validQuestion[':game_id']);
        }
        
        return $results;
    }
    
    static function updateQuestion($validQuestion) {                    
        $results = DBConn::update("UPDATE " . DBConn::prefix() . "game_round_questions SET "
                . "question = :question, max_points = :max_points, wager = :wager, last_updated_by = :last_updated_by "
                . "WHERE id = :id AND game_id = :game_id AND round_id = :round_id;", $validQuestion);
        
        if($results) {
            self::calculateGameScores($validQuestion[':game_id']);
        }
        
        return $results;
    }
    
    static function deleteQuestion($validQuestion) {
        $results = DBConn::delete("DELETE FROM " . DBConn::prefix() . "game_score_questions "
                . "WHERE question_id = :id AND game_id = :game_id AND round_id = :round_id;", $validQuestion);
        
        $results = DBConn::delete("DELETE FROM " . DBConn::prefix() . "game_round_questions "
                . "WHERE id = :id AND game_id = :game_id AND round_id = :round_id;", $validQuestion);
        
        if($results) {
            self::updateQuestionOrder($validQuestion[':round_id']);
            self::calculateGameScores($validQuestion[':game_id']);
        }
        
        return $results;
    }
    
    private static function updateQuestionOrder($roundId) {
        return DBConn::update("SET @rownumber = 0; UPDATE " . DBConn::prefix() . "game_round_questions SET `order` = (@rownumber:=@rownumber+1) "
                . "WHERE round_id = :round_id ORDER BY `order` ASC;", array(':round_id' => $roundId));
    }
    
    static function saveQuestionScores($scores) {
        $saveQuestioneScore = DBConn::preparedQuery("INSERT INTO " . DBConn::prefix() . "game_score_questions"
                . "(game_id, round_id, question_id, team_id, wager, answer, score, created_user_id) "
                . "VALUES (:game_id,:round_id,:question_id,:team_id,:wager,:answer,:score,:created_user_id) "
                . "ON DUPLICATE KEY UPDATE wager = :dup_wager, answer = :dup_answer, score = :dup_score, "
                . "last_updated_by = :last_updated_by;");
        $result = array();
        foreach($scores as $score) {
            $result[] = $saveQuestioneScore->execute($score);
        }
        return $result;
    }
    
    /* GAME SCORING */
    
    static function calculateGameScores($gameId) {
        $currentUser = APIAuth::getUserId();
        $result = array();
        $game = array(':game_id' => $gameId);
        
        $teams = DBConn::selectColumn("SELECT team_id FROM " . DBConn::prefix() . "game_score_teams WHERE game_id=:game_id;", $game);
        $teams = (is_array($teams)) ? $teams : array($teams);
        
        $rounds = DBConn::selectColumn("SELECT id FROM " . DBConn::prefix() . "game_rounds WHERE game_id=:game_id;", $game);
        $rounds = (is_array($rounds)) ? $rounds : array($rounds);
        
        $saveTeamScore = DBConn::preparedQuery("INSERT INTO " . DBConn::prefix() . "game_score_teams (game_id,team_id,created_user_id) "
                . "VALUES (:game_id,:team_id,:created_user_id) "
                . "ON DUPLICATE KEY UPDATE score = 0, game_rank = 0, last_updated_by = :last_updated_by;");
        
        $saveRoundScore = DBConn::preparedQuery("INSERT INTO " . DBConn::prefix() . "game_score_rounds (game_id,round_id,team_id,created_user_id) "
                . "VALUES (:game_id,:round_id,:team_id,:created_user_id) "
                . "ON DUPLICATE KEY UPDATE score = 0, round_rank = 0, last_updated_by = :last_updated_by;");
        
        for($t = 0; $t < count($teams); $t++) {
            $saved = $saveTeamScore->execute(array(
                ':game_id' => $gameId,
                ':team_id' => $teams[$t],
                ':created_user_id' => $currentUser,
                ':last_updated_by' => $currentUser
            ));
            
            for($r = 0; $r < count($rounds); $r++) {
                $saved = $saveRoundScore->execute(array(
                    ':game_id' => $gameId,
                    ':team_id' => $teams[$t], 
                    ':round_id' => $rounds[$r],
                    ':created_user_id' => $currentUser,
                    ':last_updated_by' => $currentUser
                ));
            
                $result[] = "Team #" . $teams[$t] . " Round #" . $rounds[$r] .  " saved = " . $saved;
                
                $result[] = array(
                    ':game_id' => $gameId,
                    ':team_id' => $teams[$t], 
                    ':round_id' => $rounds[$r],
                    ':created_user_id' => $currentUser,
                    ':last_updated_by' => $currentUser
                );
            }
        }
        
        // Update Max Score for Game Rounds
        $result[] = DBConn::update("UPDATE " . DBConn::prefix() . "game_rounds AS r "
                . "INNER JOIN (SELECT qs.round_id, SUM(qs.max_points) AS maxpoints "
                . "FROM " . DBConn::prefix() . "game_round_questions AS qs GROUP BY qs.round_id) AS q ON r.id = q.round_id "
                . "SET r.max_points = q.maxpoints WHERE r.game_id = :game_id;", $game);
        
        
        // Update Team Totals for each Round
        $saveTeamRoundScore = DBConn::preparedQuery("UPDATE " . DBConn::prefix() . "game_score_rounds AS r "
                . "INNER JOIN (SELECT qs.round_id, qs.team_id, SUM(qs.score) AS total "
                . "FROM " . DBConn::prefix() . "game_score_questions AS qs "
                . "WHERE qs.round_id = :round_id GROUP BY qs.team_id) AS q "
                . "ON r.round_id = q.round_id AND r.team_id = q.team_id "
                . "SET  last_updated_by=:last_updated_by, r.score = q.total;");
                
            for($r = 0; $r < count($rounds); $r++) {
                $result[] = $saveTeamRoundScore->execute(array(
                    ':round_id' => $rounds[$r],
                    ':last_updated_by' => $currentUser
                ));
                $saveTeamRoundScore->closeCursor();
            }
        
        
        // Update Team Totals for the Game Rounds
        $saveTeamRoundOrder = DBConn::preparedQuery("SET @lastscore = NULL; SET @ordering = 0; "
                . "UPDATE " . DBConn::prefix() . "game_score_rounds SET last_updated_by=:last_updated_by, round_rank = "
                . "IF(score = @lastscore, @ordering, (@ordering := @ordering + 1)), "
                . "score = (@lastscore := score) WHERE round_id = :round_id ORDER BY score DESC;");
                
            for($r = 0; $r < count($rounds); $r++) {
                $result[] = $saveTeamRoundOrder->execute(array(
                    ':round_id' => $rounds[$r],
                    ':last_updated_by' => $currentUser
                ));
                $saveTeamRoundOrder->closeCursor();
            }
            
        // Update Max Score for Game
        $result[] = DBConn::update("UPDATE " . DBConn::prefix() . "games AS g "
                . "INNER JOIN (SELECT rs.game_id, SUM(rs.max_points) AS maxpoints "
                . "FROM " . DBConn::prefix() . "game_rounds AS rs) AS r ON r.game_id = g.id "
                . "SET g.max_points = r.maxpoints WHERE g.id = :game_id;", $game);
        
        // Update Team Totals for the Game
        
        /*
        $result[] = DBConn::update("UPDATE " . DBConn::prefix() . "game_score_teams AS t "
                . "INNER JOIN (SELECT rs.game_id, rs.team_id, SUM(rs.score) AS total "
                . "FROM " . DBConn::prefix() . "game_score_rounds AS rs GROUP BY rs.team_id) AS r "
                . "ON t.game_id = r.game_id AND t.team_id = r.team_id SET t.score = r.total WHERE t.game_id = :game_id;", $game);
         */
        $result[] = DBConn::update("UPDATE " . DBConn::prefix() . "game_score_teams AS t "
                . "INNER JOIN (SELECT rs.game_id, rs.team_id, SUM(rs.score) AS total "
                . "FROM " . DBConn::prefix() . "game_score_rounds AS rs "
                . "WHERE rs.game_id = :game_id GROUP BY rs.team_id) AS r "
                . "ON t.game_id = r.game_id AND t.team_id = r.team_id SET t.score = r.total;", $game);

        // Update Team Totals for the Game
        $result[] = DBConn::update("SET @lastscore = NULL; SET @ordering = 0; "
                . "UPDATE " . DBConn::prefix() . "game_score_teams SET game_rank = "
                . "IF(score = @lastscore, @ordering, (@ordering := @ordering + 1)), "
                . "score = (@lastscore := score) WHERE game_id = :game_id ORDER BY score DESC;", $game);
        
        return $result;
    }
    
    static function selectGameHostData($hostId) {
        $hostData = array();
        $hostData['activeGames'] = DBConn::selectAll("SELECT g.id, g.name, g.scheduled, g.venue_id AS venueId, g.host_user_id AS hostId, "
                . "game_started AS started, game_ended AS ended, max_points maxPoints, "
                . "CONCAT(u.name_first, ' ', u.name_last) AS host, v.name AS venue "
                . "FROM " . DBConn::prefix() . "games AS g LEFT JOIN " . DBConn::prefix() . "users AS u ON u.id = g.host_user_id "
                . "LEFT JOIN " . DBConn::prefix() . "venues AS v ON v.id = g.venue_id "
                . "WHERE g.host_user_id = :host_user_id AND g.game_started IS NOT NULL AND g.game_ended IS NULL;", array(':host_user_id' => $hostId));
        
        $hostData['scheduledGames'] = DBConn::selectAll("SELECT g.id, g.name, g.scheduled, g.venue_id AS venueId, g.host_user_id AS hostId, "
                . "game_started AS started, game_ended AS ended, max_points maxPoints, "
                . "CONCAT(u.name_first, ' ', u.name_last) AS host, v.name AS venue "
                . "FROM " . DBConn::prefix() . "games AS g LEFT JOIN " . DBConn::prefix() . "users AS u ON u.id = g.host_user_id "
                . "LEFT JOIN " . DBConn::prefix() . "venues AS v ON v.id = g.venue_id "
                . "WHERE g.host_user_id = :host_user_id AND g.game_started IS NULL;", array(':host_user_id' => $hostId));
        return $hostData;
    }
}
