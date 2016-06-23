<?php namespace API;

class HotSalsaRequest {
    
    /* Global List of Teams API
     * /location/getTeamNames 
     *  
     * Per Joint List of Teams API
     * Input: locationId (optional)
     * /location/getTeamNames?locationId=11 
     */
    public static $HOT_SALSA_URL_TEAMS_LIST = '/location/getTeamNames';
    
    /* Global List of Joints API
     * /locationList 
     */
    public static $HOT_SALSA_URL_VENUE_LIST = '/locationList';
    
    /* Global Player Score Leaderboard API
     * /trivia/gameNight/cumilativeScore?scoreType=player&count=10&startDate= optional&endDate=optional
     * 
     * Global Team Score Leaderboard API
     * /trivia/gameNight/cumilativeScore?scoreType=team&count=10&startDate= optional&endDate=optional 
     * 
     * Per Joint Player Score Leaderboard APIs
     * /trivia/gameNight/cumilativeScore?scoreType=player&scoreLevel=bar&locationId=11&count=10&startDate=optional&endDate=optional
     * 
     * Per Joint Team Score Leaderboard API
     * /trivia/gameNight/cumilativeScore?scoreType=team&scoreLevel=bar&locationId=11&count=10&startDate= optional&endDate=optional
     */
    public static $HOT_SALSA_URL_MOBILE_SCORE = '/trivia/gameNight/cumilativeScore';
    
    /* Global Player Checkins Leaderboard API
     * /location/getCheckins?scoreType=player&count=10
     * 
     * Global Team Checkins Leaderboard API
     * /location/getCheckins?scoreType=team&count=10
     * 
     * Per Joint Player Checkins Leaderboard API
     * /location/getCheckins?scoreType=player&scoreLevel=bar&locationId=11&count=10
     * 
     * Per Joint Team Checkins Leaderboard API
     * /location/getCheckins?scoreType=team&scoreLevel=bar&locationId=11&count=10
     */
    public static $HOT_SALSA_URL_GAME_CHECKINS = '/location/getCheckins';
    
    public static function makeRequest($apiPath) {
        $log = new Logging('leaderboards');
            
        $url = APIConfig::get('HOT_SALSA_API_URL');
        $version = APIConfig::get('HOT_SALSA_APP_VERSION');
        $code = APIConfig::get('HOT_SALSA_URL_CODE');
        $key = APIConfig::get('HOT_SALSA_AUTH_KEY');
        $os = APIConfig::get('HOT_SALSA_OS');
        $package = APIConfig::get('HOT_SALSA_PACKAGE_CODE');

        if (!$url || !$version || !$code || !$key || !$os || !$package) {
            $log->write("Could not attempt call. The Hot Salsa signup hook is enabled but a system variable is disabled or missing.");
            $log->write(array(
                'apiUrl' => $url,
                'appVersion' => $version,
                'code' => $code,
                'authKey' => $key,
                'os' => $os,
                'packageCode' => $package
            ));
            return false;
        }

        $params = array(
            'appVersion' => $version,
            'code' => $code,
            'authKey' => $key,
            'os' => $os,
            'packageCode' => $package
        );
        
        // create curl resource 
        $ch = curl_init();
        
        // set url 
        curl_setopt($ch, CURLOPT_URL, $url . $apiPath);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

        //return the transfer as a string 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // $output contains the output string 
        $curlOutput = curl_exec($ch);

        if (!$curlOutput) {
            // No Results = Error
            $error = (curl_error($ch)) ? curl_error($ch) : 'ERROR: No results';
            $info = (curl_getinfo($ch)) ? json_encode(curl_getinfo($ch)) : 'ERROR: No Info';
            
            $log->write($error);
            $log->write($info);
            
            curl_close($ch);
            
            return false;
        }
        
        // Results
        $curlResult = json_decode($curlOutput, true);
        
        if (!isset($curlResult['status']) || $curlResult['status'] === 'failed') {
            $error = (isset($curlResult['status'])) ? $curlResult['status'] : 'ERROR: Unknown error occured';
            
            $log->write($error);
            
            curl_close($ch);
            
            return false;
        } 
        curl_close($ch);
        return $curlResult;
    }
}