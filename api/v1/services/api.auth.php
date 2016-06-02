<?php namespace API;
require_once dirname(dirname(__FILE__)) . '/config/config.php';
require_once dirname(dirname(__FILE__)) . '/controllers/auth/auth.data.php';
require_once dirname(__FILE__) . '/api.dbconn.php';
    
/* 
 * HTTP Code Exlpinations:
 * https://en.wikipedia.org/wiki/List_of_HTTP_status_codes
 */

use \Respect\Validation\Validator as v;

class APIAuth {    
    
    const APISESSIONNAME = 'API_AUTHENTICATED_USER_ID';
    
    static function getUserId() {        
        return (isset($_SESSION[self::APISESSIONNAME])) ? $_SESSION[self::APISESSIONNAME] : '0';
    }
    
    static function isAuthorized($app, $role = 'public') {
        $post = $app->request->post();
        
        /* If this is a public route */
        if(strtolower($role) === 'public' || strtolower($role) === 'guest') {
            /* Try to get the user id, but allow access no matter what */
            if(v::key('apiKey', v::stringType())->validate($post) &&
                v::key('apiToken', v::stringType())->validate($post)) {
                $session = self::selectUserSession($post['apiKey']);
                $_SESSION[self::APISESSIONNAME] = ($session) ? $session->userId : '0';
                //self::updateSessionTimeout($session->sessionId, $session->expires);
            }
            
            return true; // Public access
        }
        
        /* Check parameters for non public routes. */
        if(!v::key('apiKey', v::stringType())->validate($post) || 
           !v::key('apiToken', v::stringType())->validate($post)) {
            /* 
             * 400 Bad Request
             * 
             * The server cannot or will not process the request due to an apparent 
             * client error (e.g., malformed request syntax, invalid request 
             * message framing, or deceptive request routing).
             */
            $app->halt(400, json_encode(array(
                'data' => array('msg' => 'Invalid API Request Access Key and Token Pair - Check your parameters and try again.'), 
                'meta' => array('error' => true, 'status' => 400)
                )));
            return false;
        }
        
        /* Does user have a session */
        $session = self::selectUserSession($post['apiKey']);
        if($session && password_verify($post['apiToken'], $session->apiToken)) {
            
            /* Hold onto the UserId for use in the API Call. */
            $_SESSION[self::APISESSIONNAME] = $session->userId;
            
            /* Does user have an expired session */
            $now = new \DateTime();
            $expires = (is_null($session->expires)) ? $now : new \DateTime($session->expires);
            // If the expiration is NULL it never expires
            if(false && !is_null($session->expires) && $now < $expires) {
                /* 419 Authentication Timeout (not in RFC 2616)
                 * 
                 * Not a part of the HTTP standard, 419 Authentication Timeout 
                 * denotes that previously valid authentication has expired. It is 
                 * used as an alternative to 401 Unauthorized in order to 
                 * differentiate from otherwise authenticated clients being denied 
                 * access to specific server resources.
                 */
                $app->halt(419, json_encode(array(
                    'other' => array('post' => $post, 'route' => $role),
                    'data' => array('msg' => 'Session timed out. Please login again.'), 
                    'meta' => array('error' => true, 'status' => 419)
                    )));
                return false;
            }
            
            
            $userRoles = self::selectUserRoles($session->userId);
            if($userRoles && in_array($role, $userRoles)) {
                /* SUCCESS - User is logged in and does have access to this request. */
                //self::updateSessionTimeout($session->sessionId, $session->expires);
                return true; // Ideal Endpoint
            } else {
                /*
                 * 403 Forbidden
                 * 
                 * The request was a valid request, but the server is refusing 
                 * to respond to it. 403 error semantically means 
                 * "unauthorized", i.e. the user does not have the necessary 
                 * permissions for the resource.
                 */
                $app->halt(403, json_encode(array(
                    'other' => array('post' => $post, 'route' => $role, 'user' => $userRoles),
                    'data' => array('msg' => 'Unauthorized API Access. Session does not have access to this content.'),
                    'meta' => array('error' => true, 'status' => 403)
                )));
                return false;
            }
            
        }
        
        /* 401 Unauthorized
         * 
         * Similar to 403 Forbidden, but specifically for use when 
         * authentication is required and has failed or has not yet been 
         * provided. The response must include a WWW-Authenticate header field 
         * containing a challenge applicable to the requested resource. See 
         * Basic access authentication and Digest access authentication.
         * 401 semantically means "unauthenticated", i.e. the user does 
         * not have the necessary credentials.
         * 
         * NOTE: We are using POST variables `apiKey` and `apiToken` instead
         *       of the WWW-Authenticate header currently. 
         */
        $app->halt(401, json_encode(array(
            'data' => array('msg' => 'Unauthorized API Access.'), 
            'meta' => array('error' => true, 'status' => 401)
            )));
        return false;
    }
    
    private static function selectUserSession($identifier) {
        return DBConn::selectOne("SELECT t.id AS sessionId, user_id AS userId, "
                . "token AS apiToken, identifier AS apiKey, t.expires "
                . "FROM " . DBConn::prefix() . "tokens_auth AS t "
                . "JOIN " . DBConn::prefix() . "users AS u ON u.id = t.user_id "
                . "WHERE identifier = :identifier AND u.disabled IS NULL "
                . "ORDER BY t.expires DESC LIMIT 1;", array(':identifier' => $identifier));
    }
    
    private static function selectUserRoles($userId) {
        return DBConn::selectAll("SELECT DISTINCT(r.slug) "
                . "FROM " . DBConn::prefix() . "auth_lookup_user_group AS ug "
                . "JOIN " . DBConn::prefix() . "auth_lookup_group_role AS gr ON ug.auth_group_id = gr.auth_group_id "
                . "JOIN " . DBConn::prefix() . "auth_roles AS r ON r.id = gr.auth_role_id "
                . "WHERE ug.user_id = :id;", array(':id' => $userId), \PDO::FETCH_COLUMN);
    }
    
    private static function updateSessionTimeout($sessionId, $expiresString) {
        if (is_null($expiresString)) {
            return true;
        }
        
        $timeoutInHours = intval(APIConfig::get('AUTH_COOKIE_TIMEOUT_HOURS'));
        if(!$timeoutInHours) {
            $timeoutInHours = 24;
        }
        $expires = new \DateTime($expiresString);
        
        $newExpires = new \DateTime();
        $newExpires->modify("+{$timeoutInHours} hour");
        
        $interval = $expires->diff($newExpires);
        
        // Only update once every 5 minutes to limit overhead
        if(intval($interval->format('%i')) >= 5) {
            return DBConn::selectOne("UPDATE " . DBConn::prefix() . "tokens_auth SET "
                . "expires = :expires WHERE id = :id LIMIT 1;", 
                    array(':id' => $sessionId, ':expires' => $newExpires->format('Y-m-d H:i:s')));
        }
        return true;
    }
    
}