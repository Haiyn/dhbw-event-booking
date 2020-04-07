<?php

namespace components\email;

use components\core\Utility;
use components\InternalComponent;
use Exception;
use models\User;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class EmailService extends InternalComponent
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

    /**
     * Sends an email with http content to the specified email address
     * Uses ini settings to send either via php mail() or PHPMailer Framework
     * Redirects to internal error page if it fails (indicates that SMTP is not working)
     * @param $to * email recipient
     * @param $subject * subject of the email
     * @param $message * body of the email
     */
    public function sendEmail($to, $subject, $message)
    {
        // Wrap all the data in an array
        $sender = Utility::getIniFile()['EMAIL_FROM'];
        $header[] = "From: " . $sender;
        $header[] = "ReplyTo: " . $sender;
        $header[] = 'MIME-Version: 1.0';
        $header[] = 'Content-type: text/html; charset=iso-8859-1';

        $mail_data[] = [
            "to" => $to,
            "subject" => $subject,
            "message" => $this->wrapMessage($to, $message),
            "header" => implode("\r\n", $header)
        ];

        // Get ini setting for which mail method to use
        if (filter_var(Utility::getIniFile()['PHPMAILER_ENABLED'], FILTER_VALIDATE_BOOLEAN)) {
            $this->sendPhpmailerMail($mail_data);
        }
        else {
            $this->sendNativeMail($mail_data);
        }

    }

    private function sendNativeMail($mail_data)
    {
        // Call PHPs mail function with the wrapped message and header array imploded into single string
        if (!mail($mail_data['to'], $mail_data['subject'], $mail_data['message'], $mail_data['header'])) {
            // Email send failed, send error details to internal error page and display it
            $this->setError("EmailService failed while sending a mail.");
        }
    }

    private function sendPhpmailerMail($mail_data)
    {
        // Get the email settings from the ini
        $ini = Utility::getIniFile(true)['Email'];

        $mail = new PHPMailer(true);
        try {
            // Set SMPT settings if ini setting true
            if($ini['EMAIL_IS_SMTP']) {
                $mail->isSMTP();
                $mail->Host = $ini['EMAIL_SMTP_HOST'];

                // Set SMTP Auth settings if ini setting true
                if ($ini['EMAIL_IS_AUTH']) {
                    $mail->SMTPAuth = true;
                    $mail->Username = $ini['EMAIL_USERNAME'];
                    $mail->Password = $ini['EMAIL_PASSWORD'];
                }

                // Set encryption settings if ini setting true
                if ($ini['EMAIL_IS_ENCRYPTED']) {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;         // Enable TLS encryption
                    $mail->Port = 465;
                }
            }

            // Set sender and recipient
            $mail->setFrom($ini['EMAIL_FROM_ADDRESS'], $ini['EMAIL_FROM_NAME']);
            $mail->addAddress($mail_data['to']);
            $mail->addReplyTo($ini['EMAIL_FROM_ADDRESS'], $ini['EMAIL_FROM_NAME']);

            // Set content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $mail_data['subject'];
            $mail->Body    = $mail_data['message'];

            $mail->send();
        } catch (Exception $exception) {
            // Email send failed
            // More info in $mail->ErrorInfo for debugging
            $this->setError("EmailService failed while sending a PHPMailer mail: " . $mail->ErrorInfo);
        }
    }

    /**
     * Wraps the passed message with the spcecified header and footer
     * @param $to * email recipient
     * @param $message * email body
     * @return string * wrapped email body
     */
    private function wrapMessage($to, $message)
    {
        // Get the username or first name (if available) via the email
        $user = User::getInstance();
        $foundUser = $user->getUserByEmail($to);
        if (empty($foundUser->first_name)) {
            $name = $foundUser->username;
        } else {
            $name = $foundUser->first_name;
        }

        // This would be better in a template phtml file
        // but since the email contents are very basic, this is not really necessary
        $header = "<p>Dear {$name},</p><br/><br/><p>";
        $footer = "</p><br/><br/><p>Your DHBW Event Booking Team</p>
            <p style='font-size: 0.6rem'>This E-Mail was automatically generated. Please don't reply to it!</p>";

        return $header . $message . $footer;
    }
}
