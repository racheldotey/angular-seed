<?php namespace API;
 require_once dirname(dirname(dirname(__FILE__))) . '/services/api.dbconn.php';

class GameData {
  
    static function selectGame($gameId, $roundNumber = 1) {
        $game = DBConn::selectOne("SELECT g.id, g.name, g.scheduled, g.venue_id AS venueId, g.host_user_id AS hostId, "
                . "game_started AS started, game_ended AS ended, max_points maxPoints, "
                . "CONCAT(u.name_first, ' ', u.name_last) AS updatedBy "
                . "FROM " . DBConn::prefix() . "games AS g "
                . "JOIN " . DBConn::prefix() . "users AS u ON u.id = g.last_updated_by WHERE g.id = :game_id LIMIT 1;", array(':game_id' => $gameId));

        if($game) {
            // Host
            $game->host = DBConn::selectOne("SELECT id, name_first AS nameFirst, name_last AS nameLast, email, "
                    . "CONCAT(name_first, ' ', name_last) AS displayName "
                    . "FROM " . DBConn::prefix() . "users WHERE id = :hotst_user_id LIMIT 1;", array(':hotst_user_id' => $game->hostId));
            unset($game->hostId);
                        
            // Venue
            $game->venue = DBConn::selectOne("SELECT id, name, address, address_b AS addressB, city, state, zip, phone, website "
                    . "FROM " . DBConn::prefix() . "venues WHERE id = :venue_id LIMIT 1;", array(':venue_id' => $game->venueId));
            unset($game->venueId);
            
            // Teams and their score
            $game->teams = DBConn::selectAll("SELECT t.id, t.name FROM " . DBConn::prefix() . "teams AS t "
                    . "JOIN " . DBConn::prefix() . "game_score_teams AS g ON g.team_id = t.id WHERE g.game_id = :game_id;", array(':game_id' => $gameId));
                        
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
                
                $qRoundTeams = DBConn::executeQuery("SELECT s.team_id AS teamId, t.name AS team, IFNULL(r.score, 0) AS roundScore 
FROM " . DBConn::prefix() . "game_score_teams AS s
LEFT JOIN " . DBConn::prefix() . "game_score_rounds AS r ON s.team_id = r.team_id AND r.game_round_id = :game_round_id
JOIN " . DBConn::prefix() . "teams AS t ON t.id = s.team_id
ORDER BY s.team_id;", array(':game_round_id' => $round->roundId));

                $qTeamScores = DBConn::preparedQuery("SELECT q.id AS questionId, IFNULL(s.score, 0) AS questionScore 
FROM " . DBConn::prefix() . "game_round_questions AS q 
LEFT JOIN " . DBConn::prefix() . "game_score_questions AS s ON s.question_id = q.id AND s.team_id = :team_id
WHERE q.round_id = :round_id
ORDER BY q.order;");

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

    static function getScoreboard($gameId) {
        $game = DBConn::selectOne("SELECT g.id, g.name, g.scheduled, g.venue_id AS venueId, g.host_user_id AS hostId, "
                . "game_started AS started, game_ended AS ended, max_points maxPoints, "
                . "CONCAT(u.name_first, ' ', u.name_last) AS updatedBy "
                . "FROM " . DBConn::prefix() . "games AS g "
                . "JOIN " . DBConn::prefix() . "users AS u ON u.id = g.last_updated_by WHERE g.id = :game_id LIMIT 1;", array(':game_id' => $id));

        
        if($game) {
            // Host
            $game->host = DBConn::selectOne("SELECT id, name_first AS nameFirst, name_last AS nameLast, email, "
                    . "CONCAT(name_first, ' ', name_last) AS displayName "
                    . "FROM " . DBConn::prefix() . "users WHERE id = :hotst_user_id LIMIT 1;", array(':hotst_user_id' => $game->hostId));
            unset($game->hostId);
                        
            // Venue
            $game->venue = DBConn::selectOne("SELECT id, name, address, address_b AS addressB, city, state, zip, phone, website "
                    . "FROM " . DBConn::prefix() . "venues WHERE id = :venue_id LIMIT 1;", array(':venue_id' => $game->venueId));
            unset($game->venueId);
            
            
            // Scoreboard
            $game->scoreboard = DBConn::selectAll("SELECT s.team_id AS teamId, s.score, s.game_winner AS winner, s.last_updated AS updated, t.name AS team, "
                    . "CONCAT(u.name_first, ' ', u.name_last) AS updatedBy "
                    . "FROM " . DBConn::prefix() . "game_score_teams AS s JOIN " . DBConn::prefix() . "teams AS t ON t.id = s.team_id "
                    . "JOIN " . DBConn::prefix() . "users AS u ON u.id = s.last_updated_by WHERE s.game_id = 1 ORDER BY s.score;", array(':game_id' => $id));
            
            // Teams and their score
            $gTeams = DBConn::executeQuery("SELECT t.id, t.name FROM " . DBConn::prefix() . "teams AS t "
                    . "JOIN " . DBConn::prefix() . "game_score_teams AS g ON g.team_id = t.id WHERE g.game_id = :game_id;", array(':game_id' => $id));

            $qMembers = DBConn::preparedQuery("SELECT m.joined, u.id, u.name_first AS nameFirst, u.name_last AS nameLast, "
                    . "CONCAT(u.name_first, ' ', u.name_last) AS displayName "
                    . "FROM " . DBConn::prefix() . "team_members AS m "
                    . "LEFT JOIN " . DBConn::prefix() . "users AS u ON u.id = m.user_id "
                    . "WHERE m.team_id = :team_id;");
            
            $teams = Array();
            while($team = $gTeams->fetch(\PDO::FETCH_OBJ)) {
                // Members
                $qMembers->execute(array(':team_id' => $team->id));
                $team->members = $qMembers->fetchAll(\PDO::FETCH_OBJ);
                
                array_push($teams, $team);
            }
            $game->teams = $teams;
            
            // Game Rounds
            $qGameRounds = DBConn::executeQuery("SELECT r.id, r.name, r.max_points AS maxPoints, r.last_updated AS updated, "
                    . "CONCAT(u.name_first, ' ', u.name_last) AS updatedBy "
                    . "FROM " . DBConn::prefix() . "game_rounds AS r JOIN " . DBConn::prefix() . "users AS u ON r.last_updated_by = u.id "
                    . "WHERE r.game_id = :game_id ORDER BY r.id;", array(':game_id' => $id));
            
            $qRoundScores = DBConn::preparedQuery("SELECT s.score, s.last_updated AS updated, t.id AS teamId, t.name AS team, "
                    . "CONCAT(u.name_first, ' ', u.name_last) AS updatedBy "
                    . "FROM " . DBConn::prefix() . "game_score_rounds AS s JOIN " . DBConn::prefix() . "teams AS t ON t.id = s.team_id "
                    . "JOIN " . DBConn::prefix() . "users AS u ON u.id = s.last_updated_by WHERE s.game_round_id = :game_round_id ORDER BY s.score;");
            
            $qRoundQuestions = DBConn::preparedQuery("SELECT s.score, s.last_updated AS updated, t.id AS teamId, t.name AS team, "
                    . "CONCAT(u.name_first, ' ', u.name_last) AS updatedBy "
                    . "FROM " . DBConn::prefix() . "game_score_rounds AS s JOIN " . DBConn::prefix() . "teams AS t ON t.id = s.team_id "
                    . "JOIN " . DBConn::prefix() . "users AS u ON u.id = s.last_updated_by WHERE s.game_round_id = :game_round_id ORDER BY s.score;");
            
            
            $qRoundQuestionScores = DBConn::preparedQuery("SELECT s.score, s.last_updated AS updated, t.id AS teamId, t.name AS team, "
                    . "CONCAT(u.name_first, ' ', u.name_last) AS updatedBy "
                    . "FROM " . DBConn::prefix() . "game_score_rounds AS s JOIN " . DBConn::prefix() . "teams AS t ON t.id = s.team_id "
                    . "JOIN " . DBConn::prefix() . "users AS u ON u.id = s.last_updated_by WHERE s.game_round_id = :game_round_id ORDER BY s.score;");
            
            $rounds = Array();
            while($round = $qGameRounds->fetch(\PDO::FETCH_OBJ)) {
                // Team Round Scores
                $qRoundScores->execute(array(':game_round_id' => $round->id));
                $round->scores = $qRoundScores->fetchAll(\PDO::FETCH_OBJ);
                
                array_push($rounds, $round);
            }
            $game->rounds = $rounds;
            
            return $game;
        }
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
    
    static function insertRound($validRound) {
        return DBConn::insert("INSERT INTO " . DBConn::prefix() . "game_rounds(`name`, `order`, `game_id`, `default_question_points`, `created_user_id`, `last_updated_by`) "
                . "VALUES (:name, :order, :game_id, :default_question_points, :created_user_id, :last_updated_by);", $validRound);
    }
    
    static function insertQuestion($validQuestion) {
        return DBConn::insert("INSERT INTO " . DBConn::prefix() . "game_round_questions(`question`, `order`, `game_id`, `round_id`, `max_points`, `created_user_id`, `last_updated_by`) "
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
    
    static function deleteGame($id) {
        $fields = DBConn::delete("DELETE FROM " . DBConn::prefix() . "lookup_game_field WHERE game_id = :id;", array('id' => $id));
        $groups = DBConn::delete("DELETE FROM " . DBConn::prefix() . "lookup_group_game WHERE game_id = :id;", array('id' => $id));
        
        return DBConn::delete("DELETE FROM " . DBConn::prefix() . "games WHERE id = :id LIMIT 1;", array('id' => $id));
    }
}
