<?php

namespace components\authorization;

use components\core\Utility;
use models\Session;

class AuthorizationService
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
     * This function generates a new session on the server and database
     * Depending on the passed parameter, it can be used to generate a logged in session (user_id not null)
     * or a generic, not logged in session (user_id is null)
     * Used by login and logout
     * @param string|null $user_id * optional
     */
    public function setSession($user_id = null)
    {
        // Start a new session and get the user data
        session_start();
        $session_data = $this->generateCurrentSessionData($user_id);

        // Set the server session
        if (!empty($user_id)) {
            $_SESSION['USER_ID'] = $session_data['user_id'];
            $_SESSION['LOGIN_TIME'] = $session_data['login_time'];
        }
        $_SESSION['IP_ADDRESS'] = $session_data['ip_address'];
        $_SESSION['USER_AGENT'] = $session_data['user_agent'];

        // Save the session to the database
        $session = Session::getInstance();
        if (!$session->saveSession($session_data))
        {
            // Insert/Update was unsuccessful
            header("Location: /internal-error");
            exit(1);
        }
    }

    /**
     * Unset the server session and delete the session from the database
     * Creates a new, not logged in session upon unset and redirects to the login page
     * Used by logout and failed session check
     */
    public function unsetSession()
    {
        // Delete the session from the database
        $session = Session::getInstance();
        if (!$session->deleteSessionById(session_id()))
        {
            // Something went wrong while deleting the existing session
            header("Location: /internal-error");
            exit(1);
        }

        // Delete the session from the server
        session_unset();
        session_destroy();

        // Create a new "not logged in" session, it is needed for error message displaying
        $this->setSession();

        // Redirect to the login page
        header("Location: /login");
        exit(0);
    }

    /**
     * Checks the current session for whether the user is logged in or not
     * Also prevents session hijacking via the browser cookie session_id by comparing it to the saved session
     * in the database
     * (Unsets and) redirects to login page if session is invalid
     */
    public function checkSession()
    {
        // If user_id is not set, the user is not logged in
        if (empty($_SESSION['USER_ID']))
        {
            header("Location: /login");
            exit(0);
        }
        // Check if the session is expired
        if (!empty($_SESSION['LOGIN_TIME']) &&
            time() - Utility::getIniFile()['LOGIN_TIMEOUT'] > $_SESSION['LOGIN_TIME'])
        {
            $this->unsetSession();
        }
        // Beyond this point, the user to that session ID is logged in

        // Compare database session with the current user's data to verify they're actually the logged in user
        $session = Session::getInstance();
        $savedSession = $session->getSessionBySessionId(session_id());
        $currentSession = self::generateCurrentSessionData();

        // Check if session exists in database
        if (empty($savedSession))
        {
            $this->unsetSession();
        }

        // Check if current user session matches the database session for that ID
        if ($savedSession->ip_address != $currentSession['ip_address'] ||
            $savedSession->user_agent != $currentSession['user_agent'])
        {
            $this->unsetSession();
        }
        // Beyond this point, the user is logged in and actually the session owner of the session ID
        // It is now safe to show the web page to this user
    }

    /**
     * Resume an existing session from the cookie regardless of login status
     * Used for pages that require a session but not a logged in session (-> checkSession)
     */
    public function resumeSession() {
        session_start();

        // Check if a logged in user called /login or /register
        if(strpos($_SERVER['REQUEST_URI'], "login") || strpos($_SERVER['REQUEST_URI'], "register")) {
            if($_SESSION['USER_ID']) {
                // Logged in users cannot access these pages, redirect
                header("Location: /event-overview");
                exit;
            }
        }
    }

    /**
     * Generates an array that contains the current user's connectin information
     * Can be called as logged in session  array (user_id not null)
     * or not logged in session array (user_id null)
     * @param string|null $user_id * optional
     * @return array * generated session data
     */
    private static function generateCurrentSessionData($user_id = null)
    {
        return $session_data = [
            "session_id" => session_id(),
            "user_id" => $user_id,
            // only set the login time if the user is logged in (user_id not null)
            "login_time" => empty($user_id) ? null : $_SERVER['REQUEST_TIME'],
            // check for HTTP_X_FORWARDED_FOR to save the correct IP
            "ip_address" => isset($_SERVER['HTTP_X_FORWARDED_FOR'])
                ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'],
            "user_agent" => $_SERVER['HTTP_USER_AGENT']
        ];
    }
}