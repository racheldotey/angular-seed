<?php namespace API;

/* @author  Rachel L Carbone <hello@rachellcarbone.com> */

use \Respect\Validation\Validator as v;

class ApiAuthMiddleware {
    
    const APISESSIONNAME = 'API_AUTHENTICATED_USER_ID';

    private $slimContainer;

    private $db;

    private $ApiLogging;

    private $requiredRole;

    private $user;

    /**
     * Set the required role.
     *
     * @param  \Interop\Container\ContainerInterface $slimContainer Slim Container
     * @param  String                                $role Required Role to Access Route
     */
    public function __construct(\Interop\Container\ContainerInterface $slimContainer, $role) {
        // Hold onto the Slim Container
        $this->slimContainer = $slimContainer;
        // Get the Database Controller
        $this->db = $slimContainer->get('DBConn');
        // Get the Database Controller
        $this->ApiLogging = $slimContainer->get('ApiLogging');

        // Select array of roles from the database
        // ex array('guest', 'member', 'admin');
        $acceptedRoles = $this->selectAllRoleSlugs();

        // Ensure the required role is in the database
        if(in_array(strtolower($role), $acceptedRoles)) {
            $this->requiredRole = strtolower($role);
        } else {
            // This is an unknow role, all access will be denied 
            // except by the super admin (see _invoke() below).
            $this->requiredRole = false;
        }
    }

    /**
     * Validate accepted role for this route.
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next) {

        $authorized = $this->isAuthorized($request, $response);

        return ($authorized === true) ? $next($request, $response) : $authorized;
    }
    
    static function getUserId() {        
        return (isset($_SESSION[self::APISESSIONNAME])) ? $_SESSION[self::APISESSIONNAME] : '0';
    }

    private function getCredentials($request) {
        $post = $request->getParsedBody();
        $get = $request->getQueryParams();

        if(v::key('apiKey', v::stringType())->validate($post) ||
            v::key('apiToken', v::stringType())->validate($post)) {
            return array(
                'apiKey' => (v::key('apiKey', v::stringType())->validate($post)) ? $post['apiKey'] : false,
                'apiToken' => (v::key('apiToken', v::stringType())->validate($post)) ? $post['apiToken'] : false
            );
        } else {
            return array(
                'apiKey' => (v::key('apiKey', v::stringType())->validate($get)) ? $get['apiKey'] : false,
                'apiToken' => (v::key('apiToken', v::stringType())->validate($get)) ? $get['apiToken'] : false
            );
        }
    }

    private function isAuthorized($request, $response) {
        return true; // Public access

        $credentials = $this->getCredentials($request);
        
        /* If this is a public route */
        if(strtolower($this->requiredRole) === 'public' || 
            strtolower($this->requiredRole) === 'guest') {

            // Try to get the user id, but allow access no matter what
            if($credentials['apiKey'] && $credentials['apiToken']) {
                
                // Try to login user
                $session = $this->selectUserSession($credentials['apiKey']);
                $_SESSION[self::APISESSIONNAME] = ($session) ? $session->userId : '0';
            }
            
            return true; // Public access
        }
        
        /* Check parameters for non public routes. */
        if(!$credentials['apiKey'] || !$credentials['apiToken']) {
               
            // 400 Bad Request
            return $this->slimContainer->view->render($response, 400, 'Invalid API Request Access Key and Token Pair - Check your parameters and try again.');
        }
        
        /* Does user have a session */
        $session = $this->selectUserSession($credentials['apiKey']);
        if($session && password_verify($credentials['apiToken'], $session->apiToken)) {
            
            /* Hold onto the UserId for use in the API Call. */
            $_SESSION[self::APISESSIONNAME] = $session->userId;
            
            /* Does user have an expired session */
            $now = new \DateTime();
            $expires = (is_null($session->expires)) ? $now : new \DateTime($session->expires);

            // If the expiration is NULL it never expires
            if(!is_null($session->expires) && $now > $expires) {
                // 401 Unauthorized
                return $this->slimContainer->view->render($response, 401, 'Session timed out. Please login again.');
            }
            
            $userRoles = $this->selectUserRoles($session->userId);
            if(!$userRoles || !in_array($this->requiredRole, $userRoles)) {
                // 403 Forbidden
                return $this->slimContainer->view->render($response, 403, 'Unauthorized API Access. Session does not have access to this content.');
            } else {
                /* SUCCESS - User is logged in and does have access to this request. */
                return true; // Ideal Endpoint
            }
        }
        
        // 401 Unauthorized
        return $this->slimContainer->view->render($response, 401, 'Unauthorized API Access.');
    }
    
    /* Database Methods */
    
    private function selectAllRoleSlugs() {
        return  $this->db->selectColumn("SELECT slug FROM " .  $this->db->prefix() . "auth_roles WHERE disabled = 0;");
    }
    
    private function selectUserSession($identifier) {
        return $this->db->selectOne("SELECT t.id AS sessionId, user_id AS userId, "
                . "token AS apiToken, identifier AS apiKey, t.expires "
                . "FROM " . $this->db->prefix() . "tokens_auth AS t "
                . "JOIN " . $this->db->prefix() . "users AS u ON u.id = t.user_id "
                . "WHERE identifier = :identifier AND u.disabled IS NULL "
                . "ORDER BY t.expires DESC LIMIT 1;", array(':identifier' => $identifier));
    }
    
    private function selectUserRoles($userId) {
        return $this->db->selectColumn("SELECT DISTINCT(r.slug) "
                . "FROM " . $this->db->prefix() . "auth_lookup_user_group AS ug "
                . "JOIN " . $this->db->prefix() . "auth_lookup_group_role AS gr ON ug.auth_group_id = gr.auth_group_id "
                . "JOIN " . $this->db->prefix() . "auth_roles AS r ON r.id = gr.auth_role_id "
                . "WHERE ug.user_id = :id;", array(':id' => $userId));
    }
}