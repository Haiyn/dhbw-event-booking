<?php

namespace components\authorization;

use components\database\Database;

class Session{

    private static $database;

    public function __construct()
    {
        self::$database = Database::newInstance(null);
    }

    static function sessionStart($SSID, $user_id, $login_time, $ip_address, $user_agent){

        session_id($SSID . '_SESSION');
        session_start();

        if (self::checkIfSessionExpired()){

            if (!self::checkIfSessionExists()){
                $_SESSION = array();
                $_SESSION['ip_address'] = isset($_SERVER['HTTP_X_FORWARDED_FOR'])
                ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
            }
        }


    }

    /*
     * Check to see if session expired
     * */
    static protected function checkIfSessionExpired(){
        if (isset($_SESSION['EXPIRED']) && $_SESSION['EXPIRED'] < time())
            return false;
    }


}
