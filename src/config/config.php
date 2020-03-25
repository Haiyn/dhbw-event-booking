<?php
// THE CREDENTIALS IN THIS FILE ARE PUBLIC AND FOR DEMO USE ONLY
// CHANGE THEM TO A DIFFERENT ONE IF YOU WANT TO USE IT FOR PRODUCTION

// Error logging
error_reporting(E_ALL);
ini_set("display_errors", 1);

// URL
define('URL', 'localhost:8080/');

// Database
define('DB_TYPE', 'postgresql');
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'event-booking');
define('DB_USER', 'dev');
define('DB_PASS', 'dev');

// Password Salting
define('AUTH_SALT', '0~y802M]fWH>J]=C7>OlniyMU]>yxCt#-j(r@K37D)B{18yh9 x#@+6Y[@U4Tc,{');