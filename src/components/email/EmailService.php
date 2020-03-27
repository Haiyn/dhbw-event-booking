<?php

namespace components\email;

use components\core\Utility;
use models\User;

class EmailService
{
    private static $instance;

    public function __construct()
    {
        self::$instance = $this;
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /*
     * Sends an email with http content to the specified email address
     * Redirects to internal error page if it fails (indicates that SMTP is not working)
     */
    public function sendEmail($to, $subject, $message) {
        // Set the headers needed for a html email
        $header[] = "From: " . Utility::getIniFile()['EMAIL_FROM'];
        $header[] = 'MIME-Version: 1.0';
        $header[] = 'Content-type: text/html; charset=iso-8859-1';

        // Call PHPs mail function with the wrapped message and header array imploded into single string
        // TODO: Configure SMTP Server on localhost:25 for mails to actually be sent
        if (!mail($to, $subject, $this->wrapMessage($to, $message), implode("\r\n", $header)))
        {
            // Email send failed
            header("Location: /internal-error");
            return;
        }
    }

    /*
     * Wraps the passed message with the spcecified header and footer
     */
    private function wrapMessage($to, $message) {
        // Get the username or first name (if available) via the email
        $user = User::newInstance();
        $foundUser = $user->getUserByEmail($to);
        $name = "User"; // default value if no user is found (should never happen)
        if(empty($foundUser->first_name))
        {
            $name = $foundUser->username;
        }
        else
        {
            $name = $foundUser->first_name;
        }

        // This would be better in a template phtml file
        // but since the email contents are very basic, this is not really necessary
        $header = "<p>Dear {$name},</p><br/><br/><p>";
        $footer = "</p><br/><br/><p>Your DHBW Event Booking Team</p>
            <p style='font-size: 0.6rem'>his E-Mail was automatically generated. Please don't reply to it!</p>";

        return $header . $message . $footer;
    }
}