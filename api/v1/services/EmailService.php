<?php namespace API;

/* @author  Rachel L Carbone <hello@rachellcarbone.com> */

class EmailService {
    
    /*
     * System Logger Instance
     */
    private $SystemVariables;
    
    /*
     * System Logger Instance
     */
    private $ApiLogging;
    
    /*
     * Email Log File Name String
     */
    private $logFileName = 'mailer_log';

    /**
     * System Variables Handler to manage the use of variables stored in the database
     * to be used throught the API.
     * 
     * $EmailService = new EmailService( new \API\SystemVariables(), new \API\ApiLogging() );
     *
     * @param  \API\SystemVariables $SystemVariables  Database System Variables Helper Method
     * @param  \API\ApiLogging      $ApiLogging  System Logging Helper Method
     */
    public function __construct(\API\SystemVariables $SystemVariables, \API\ApiLogging $ApiLogging) {
        $this->SystemVariables = $SystemVariables;

        $this->ApiLogging = $ApiLogging;
    }

    public function getPhpMailer() {        
        // Setup mailer for sending message
        $mail = new \PHPMailer;
        $mail->isSMTP();
        $mail->isHTML(true);
        
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
        
        $success = true;
        foreach ($mailerSettings as $name => $value) {
            $var = $this->SystemVariables->get($name);
            if ($var && $var->disabled != 1) {
                $mail->{$value} = $var->value;
            } else {
                $this->ApiLogging->write("ERROR RETRIEVING SMTP SETTING <{$name}>", LOG_WARNING, $this->logFileName);
                $success = false;
            }
        }
        
        return ($success) ? $mail : false;
    }
    
    /**
     * Send an email using the PhpMailer library.
     * 
     * $EmailService->sendEmail( array(
     *      'recipientName' => '',      optional
     *      'recipientEmail' => '',
     *      'fromName' => '',           optional
     *      'fromEmail' => '',          optional
     *      'replyName' => '',          optional
     *      'replyEmail' => '',         optional
     *      'subject' => '',
     *      'htmlBody' => '',
     *      'plainBody' => ''           optional
     * ) );
     *
     * @param  array    $email  Array of email settings.
     */
    public function sendEmail($email) {        
        // Setup mailer for sending message
        $mail = $this->getPhpMailer();
        
        // Add Recipient - include name if it was sent
        if(isset($email['recipientName'])) {
            $mail->addAddress($email['recipientEmail'], $email['recipientName']);
        } else {
            $mail->addAddress($email['recipientEmail']);
        }

        // If a from email is set
        if(isset($email['fromEmail']) && isset($email['fromName'])) {
            $mail->setFrom($email['fromEmail'], $email['fromName']);
        }
        
        // If a reply to email is set
        if(isset($email['replyEmail']) && isset($email['replyName'])) {
            $mail->addReplyTo($email['replyEmail'], $email['replyName']);
        }
        
        // Add email content  
        $mail->Subject = $email['subject'];
        $mail->Body = $email['htmlBody'];

        // Add optional AltBody / Plain text body
        if(isset($email['plainBody'])) {
            $mail->AltBody = $email['plainBody'];
        }
        
        if ($mail->send()) {
            $this->ApiLogging->write("EMAIL FAILURE\nError <{$mail->ErrorInfo}>/n Params:/n" . json_encode($email), LOG_ERR, $this->logFileName);
            return true;
        } else {
            // log the error
            $this->ApiLogging->write("EMAIL FAILURE\nError <{$mail->ErrorInfo}>/n Params:/n" . json_encode($email), LOG_ERR, $this->logFileName);

            return false;
        }
    }
}