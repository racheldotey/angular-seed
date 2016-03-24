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
        
        if($game) {                        
            $game->rounds = DBConn::selectAll("SELECT r.id, r.order AS roundNumber, r.name FROM " . DBConn::prefix() . "game_rounds AS r "
                    . "WHERE r.game_id = :game_id ORDER BY r.order;", array(':game_id' => $gameId));
            
            $game->round = self::selectGameRound($gameId, $roundNumber);
        }
        
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
    
    static function getRoundCount($gameId) {
        return DBConn::selectColumn("SELECT COUNT(id) AS count FROM " . DBConn::prefix() . "game_rounds "
                . "WHERE game_id=:game_id LIMIT 1;", array(':game_id' => $gameId));
    }
    
    static function getQuestionCount($roundId) {
        return DBConn::selectColumn("SELECT COUNT(id) AS count FROM " . DBConn::prefix() . "game_round_questions "
                . "WHERE round_id=:round_id LIMIT 1;", array(':round_id' => $roundId));
    }
    
    static function insertRound($validRound) {
        return DBConn::insert("INSERT INTO " . DBConn::prefix() . "game_rounds(name, order, game_id, default_question_points, created_user_id, last_updated_by) "
                . "VALUES (:name, :order, :game_id, :default_question_points, :created_user_id, :last_updated_by);", $validRound);
    }
    
    static function insertQuestion($validQuestion) {
        return DBConn::insert("INSERT INTO " . DBConn::prefix() . "game_round_questions(question, order, game_id, round_id, max_points, created_user_id, last_updated_by) "
                . "VALUES (:question, :order, :game_id, :round_id, :max_points, :created_user_id, :last_updated_by);", $validQuestion);
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
    
    
    static function saveRoundScores($scores) {
        $saveRoundScore = DBConn::preparedQuery("INSERT INTO " . DBConn::prefix() . "game_score_rounds (game_id,round_id,team_id,score,round_rank,created_user_id) "
                . "VALUES (:game_id,:round_id,:team_id,:score,:round_rank,:created_user_id) "
                . "ON DUPLICATE KEY UPDATE score = :dup_score, round_rank = :dup_round_rank, last_updated_by = :last_updated_by;");
        $result = array();
        foreach($scores as $score) {
            $result[] = $saveRoundScore->execute($score);
        }
        return $result;
    }
    
    static function saveOverallScores($scores) {
        $saveTeamScore = DBConn::preparedQuery("INSERT INTO " . DBConn::prefix() . "game_score_teams (game_id,team_id,score,game_rank,created_user_id) "
                . "VALUES (:game_id,:team_id,:score,:game_rank,:created_user_id) "
                . "ON DUPLICATE KEY UPDATE score = :dup_score, game_rank = :dup_game_rank, "
                . "last_updated_by = :last_updated_by, game_winner = :game_winner;");
        $result = array();
        foreach($scores as $score) {
            $result[] = $saveTeamScore->execute($score);
        }
        return $result;
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
    
    
    static function saveScoreboard($gameId, $rounds, $currentUser) {
        $saveRoundScore = DBConn::preparedQuery("INSERT INTO " . DBConn::prefix() . "game_score_rounds (game_id,round_id,team_id,score,round_rank,created_user_id) "
                . "VALUES (:game_id,:round_id,:team_id,:score,:round_rank,:created_user_id) "
                . "ON DUPLICATE KEY UPDATE score = :dup_score, round_rank = :dup_round_rank, last_updated_by = :last_updated_by;");
        
        $saveTeamScore = DBConn::preparedQuery("INSERT INTO " . DBConn::prefix() . "game_score_teams (game_id,team_id,score,game_rank,created_user_id) "
                . "VALUES (:game_id,:team_id,:score,:game_rank,:created_user_id) "
                . "ON DUPLICATE KEY UPDATE score = :dup_score, game_rank = :dup_game_rank, "
                . "last_updated_by = :last_updated_by, game_winner = :game_winner;");
        
        $saveQuestioneScore = DBConn::preparedQuery("INSERT INTO " . DBConn::prefix() . "game_score_questions(game_id, round_id, question_id, team_id, score, created_user_id) "
                . "VALUES (:game_id,:round_id,:question_id,:team_id,:score,:created_user_id) "
                . "ON DUPLICATE KEY UPDATE score = :dup_score, last_updated_by = :last_updated_by;");
        
        $result = array();
        foreach($rounds as $round) {
            
            foreach($round['teams'] as $team) {
                
                // Save overall game score for this team
                $result[] = $saveTeamScore->execute(array(
                    ':game_id' => $gameId, 
                    ':team_id' => $team['teamId'], 
                    ':score' => $team['gameScore'], 
                    ':dup_score' => $team['gameScore'], 
                    ':game_rank' => $team['gameRank'], 
                    ':dup_game_rank' => $team['gameRank'], 
                    ':game_winner' => (isset($team['winner']) && 
                        ($team['winner'] === 1 || 
                        $team['winner'] === '1' || 
                        $team['winner'] === true || 
                        $team['winner'] === 'true')) ? 1 : 0, 
                    ':created_user_id' => $currentUser,
                    ':last_updated_by' => $currentUser
                ));
                
                // Save Round Score for this team
                $result[] = $saveRoundScore->execute(array(
                    ':game_id' => $gameId, 
                    ':team_id' => $team['teamId'], 
                    ':round_id' => $round['roundId'],
                    ':score' => $team['roundScore'], 
                    ':dup_score' => $team['roundScore'], 
                    ':round_rank' => $team['roundRank'], 
                    ':dup_round_rank' => $team['roundRank'], 
                    ':created_user_id' => $currentUser,
                    ':last_updated_by' => $currentUser
                ));
                
                foreach($team['scores'] as $question) {
                    // Save question scores for this team
                    $result[] = $saveQuestioneScore->execute(array(
                        ':game_id' => $gameId,
                        ':team_id' => $team['teamId'],
                        ':round_id' => $round['roundId'],
                        ':question_id' => $question['questionId'],
                        ':score' => $question['questionScore'],
                        ':dup_score' => $team['roundRank'],
                        ':created_user_id' => $currentUser,
                        ':last_updated_by' => $currentUser
                    ));
                }
            }
        }
        return $result;
    }
}
