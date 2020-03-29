<?php

namespace controllers;

use components\core\Utility;
use models\User;

class LoginController extends Controller
{
    public function render($parameters)
    {
        session_start();
        if (isset($_POST["emailOrId"]) && isset($_POST["password"])) {
            // Sanitize the data by removing any harmful code and markup
            $user_data = [
                "emailOrId" => filter_var(htmlspecialchars($_POST["emailOrId"]), FILTER_SANITIZE_STRING) ||
                    filter_var(htmlspecialchars($_POST["emailOrId"]), FILTER_SANITIZE_EMAIL),
                "password" => htmlspecialchars($_POST["password"])

            ];
            // Trim every value to assert that no whitespaces are submitted
            foreach ($user_data as $key => &$value) {
                $user_data[$key] = trim($value);
            }

            $this->loginUser($user_data);

            $this->view->pageTitle = "Login";
            /* $this->view->isSuccess = isset($_GET["success"]);
             $this->view->isError = isset($_GET["error"]);*/
        }
    }

    private function loginUser($user_data)
    {
        if (isset($_POST['login'])) {

            $user = User::newInstance();
            $password_hash = md5(Utility::getIniFile()['AUTH_SALT'] . $user_data['password']);

            $validUsername = $user->getUserByUsername($user_data['emailOrId']);
            if(empty($validUsername)){
               $this->setError("Invalid username");
            }else{
                header("Location: register");
            }

            $validEmail = $user->getUserByEmail($user_data['emailOrId']);
            if(empty($validEmail)){
                $this->setError("Invalid email");
            }else{
                header("Location: register");
            }

            $validPassword = $user->getPassword($user_data['password']);
            if(!password_verify((string)$validPassword, $password_hash)){
                $this->setError("Invalid Password");
            }else{
                header("Location: register");
            }



        }
    }
}



//          /*  $query = pg_query($validUser);
//            if (pg_num_rows($query) > 0)
//            {
//                $this->setSuccess("Good");
//                if(password_verify($user_data['password'], $password_hash )){
//                    $this->redirect('register');
//                }else{
//                    $this -> setError("Invalid Password");
//                }
//            }
//            else{
//                $this -> setError("Invalid Username od Email");
//            }
//
//
//        }
//    }*/


/* private function loginUser($user_data)
 {
     if (isset($_POST['login'])) {
         $user = new User();
         $password_hash = md5(Utility::getIniFile()['AUTH_SALT'] . $user_data['password']);

         // Checking username or email from database
         $validUser = $user->getUserByUsername($user_data['emailOrId']) || $user->getUserByEmail($user_data['emailOrId']);
         if (!empty($validUser)) {
             if (password_verify($password_hash, $user_data['password'])) {
                 $this->setSuccess("finally logged in");
                 $this->redirect('register');
             } else {
                 $this->setError("Invalid password");
             }

             // $validUser->validatePassword($user_data['password']);
         } else {
             echo "Invalid username or e-mail.";

         }
     }
 }*/


/* private function validatePassword($user_data)
 {
     $password_hash = md5(Utility::getIniFile()['AUTH_SALT'] . $user_data['password']);

     if (password_verify($password_hash, $user_data['password'])) {
     echo "Success";
 } else {
     echo "Invalid password.";
 }
 }*/



