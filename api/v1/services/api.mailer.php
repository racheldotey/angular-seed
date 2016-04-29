<?php namespace API;
require_once dirname(dirname(__FILE__)) . '/config/config.php';
require_once dirname(__FILE__) . '/api.dbconn.php';

class ApiMailer {
    
    /*
     * System Logger Instance
     */
    static $logger;
    
    /*
     * Log the last PDO error
     */
    private static function logMailError($level, $error) {
        // If the logger hasnt been instantiated
        if(!self::$logger) {
            // Create a new instance of the system Logging class
            self::$logger = new Logging('mailer_log');
        }
        // Write the error arry to the log file
        syslog($level, $error);
        self::$logger->write($error);
        return;
    }
    
    public static function sendWebsiteSignupInvite($token, $playerEmail, $playerName = '') {
        $websiteTitle = APIConfig::get('websiteTitle');
        $websiteUrl = APIConfig::get('websiteUrl');
        $inviteLink = "{$websiteUrl}signup/{$token}/";
        return self::sendEmailFromTemplate('SIGNUP_INVITE_PLAYER', $playerEmail, $playerName, [$websiteTitle, $inviteLink], [$websiteTitle]);
    }
    
    public static function sendTeamInviteNewUser($token, $teamName, $playerEmail) {
        $websiteTitle = APIConfig::get('websiteTitle');
        $websiteUrl = APIConfig::get('websiteUrl');
        $inviteLink = "{$websiteUrl}signup/{$token}/";
        return self::sendEmailFromTemplate('SIGNUP_TEAM_INVITE', $playerEmail, '', [$websiteTitle, $inviteLink, $teamName], [$websiteTitle]);
    }
    
    public static function sendTeamInviteRegisteredUser($token, $teamName, $playerEmail, $playerName = '') {
        $websiteTitle = APIConfig::get('websiteTitle');
        $websiteUrl = APIConfig::get('websiteUrl');
        $inviteLink = "{$websiteUrl}login/";
        return self::sendEmailFromTemplate('TEAM_INVITE_USER', $playerEmail, $playerName, [$websiteTitle, $inviteLink, $teamName], [$websiteTitle]);
    }
    
    private static function sendEmailFromTemplate($templateId, $recipientEmail, $recipientName = '', $bodyParams = [], $subjectParams = []) {
        if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            return array('error' => true, 'msg' => "The following email address is invalid: '{$recipientEmail}'.");
        }
        
        // Setup mailer for sending message
        $mail = self::getPhpMailer();
        $mail->isHTML(true);
        
        // Add Recipient - include name if it was sent
        if(!$recipientName || $recipientName === '') {
            $mail->addAddress($recipientEmail);
        } else {
            $mail->addAddress($recipientEmail, $recipientName);
        }

        // Retrieve template
        $emailTemplate = self::selectEmailTemplate($templateId);
        if (!$emailTemplate) {
            self::logMailError(LOG_ERR, "ERROR RETRIEVING EMAIL TEMPLATE, templateId <{$templateId}>");
            return array('error' => true, 'msg' => "Error generating email <{$templateId}>");
        }

        // If a from email is set
        if($emailTemplate->fromEmail) {
            $mail->setFrom($emailTemplate->fromEmail, $emailTemplate->fromName);
        }
        
        // If a reply to email is set
        if($emailTemplate->replyEmail) {
            $mail->setFrom($emailTemplate->replyEmail, $emailTemplate->replyName);
        }
        
        // Substitute parameters into template
        // Template substitution is for parms named !@{NUMBER}@!, i.e., !@0@!, !@1@!, etc
        // Subject Substituion
        $subject = $emailTemplate->subject;
        $index = 0;
        foreach ($subjectParams AS $sParam) {
            $subject = str_replace('!@' . $index . '@!', $sParam, $subject);
            $index++;
        }
        $mail->Subject = $subject;
        // Body Substitution
        $bodyHtml = $emailTemplate->bodyHtml;
        $bodyPlain = $emailTemplate->bodyPlain;
        $index = 0;
        foreach ($bodyParams AS $bParam) {
            $bodyHtml = str_replace('!@' . $index . '@!', $bParam, $bodyHtml);
            $bodyPlain = str_replace('!@' . $index . '@!', $bParam, $bodyPlain);
            $index++;
        }
        
        $mail->Body = $bodyHtml;
        $mail->AltBody = $bodyPlain;
        
        if ($mail->send()) {
            // log the success
            self::logMailError(LOG_INFO, "EMAIL SUCCESS\n Template Id:<{$templateId}> Sender: <{$emailTemplate->replyEmail}, {$emailTemplate->replyName}> Recipient: <{$recipientEmail}, {$recipientName}> Subject: <{$subject}> Body: <{$bodyPlain}>");
            return array('error' => false, 'msg' => "Success! Email Sent to <{$recipientEmail}, {$recipientName}>");
        } else {
            // log the error
            self::logMailError(LOG_ERR, "EMAIL FAILURE\nError <{$mail->ErrorInfo}>/n Template Id:<{$templateId}> Sender: <{$emailTemplate->replyEmail}, {$emailTemplate->replyName}> Recipient: <{$recipientEmail}, {$recipientName}> Subject: <{$subject}> Body: <{$bodyPlain}>");
            return array('error' => true, 'msg' => "Unknown Error: Error sending email to <{$recipientEmail}, {$recipientName}>");
        }
    }

    private static function getPhpMailer() {        
        // Setup mailer for sending message
        $mail = new \PHPMailer;
        $mail->isSMTP();
        
        $mailerSettings = array(
            // Enable SMTP debugging
            // 0 = off (for production use)
            // 1 = client messages
            // 2 = client and server messages
            "SMTP_SMTP_DEBUG" => 'SMTPDebug',
            //Ask for HTML-friendly debug output
            "SMTP_DEBUGOUTPUT" => "Debugoutput",
            // Set the encryption system to use - ssl (deprecated) or tls
            "SMTP_SECURE" => "SMTPSecure",
            // Whether to use SMTP authentication
            "SMTP_AUTH" => "SMTPAuth",
            // Set the hostname of the mail server
            "SMTP_SERVER_HOST" => "Host",
            // Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
            "SMTP_SERVER_PORT" => "Port",
            // Username to use for SMTP authentication - use full email address for gmail
            "SMTP_SERVER_USERNAME" => "Username",
            // Password to use for SMTP authentication
            "SMTP_SERVER_PASSWORD" => "Password"
        );
        
        foreach ($mailerSettings as $name => $value) {
            $var = ConfigData::getVariableByName($name);
            if ($var && $var->disabled != 1) {
                $mail->{$value} = $var->value;
            } else {
                self::logMailError(LOG_WARNING, "ERROR RETRIEVING SMTP SETTING <{$name}>");
            }
        }
        
        return $mail;
    }
    
    private static function selectEmailTemplate($templateId) {
        $template = DBConn::selectOne("SELECT id, identifier, from_email AS fromEmail, from_name AS fromName, "
                . "reply_email AS replyEmail, reply_name AS replyName, subject, body_html AS bodyHtml, body_plain AS bodyPlain "
                . "FROM " . DBConn::prefix() . "email_templates WHERE identifier = :identifier LIMIT 1;", 
                array(':identifier' => $templateId));
        if (!$template) {
            self::logMailError(LOG_ERR, "ERROR RETRIEVING EMAIL TEMPLATE, templateId <{$templateId}>");
            return false;
        }
        return $template;
    }
}

/*
[LOCAL] Team Up! You've been invited to a Trivia Team.
<p>A player at <a href='!@1@!' target='_blank'>!@0@!</a> would like you to join their team!</p><p>Click the link above or paste the following URL into your browser to join team '!@2@!':</p><p>!@1@!</p>
A player at !@0@! would like you to join their team! Paste the following URL into your browser to join team '!@2@!': !@1@!
    
[LOCAL] A friend has invited you to join !@0@!
<p>A player at <a href='!@1@!' target='_blank'>!@0@!</a> would like you to join in on the fun!</p><p>Click the link above or paste the following URL into your browser to signup:</p><p>!@1@!</p>
A player at !@0@! would like you to join in on the fun!\n\rPaste the following URL into your browser to signup:\n\r!@1@!
 */