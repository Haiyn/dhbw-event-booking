<?php

namespace controllers;

use models\User;

class LoginController extends Controller
{
    public function render($parameters)
    {
        session_start();

        if (isset($_POST["emailOrId"]) && isset($_POST["password"]))
        {
           $username = "";
           $password = "";

            $this -> loginUser($username, $password);
        }

        $this->view->pageTitle = "Login";
        $this->view->isSuccess = isset($_GET["success"]);
        $this->view->isError = isset($_GET["error"]);
    }



   private function loginUser(){
       if(isset($_POST['login'])){
           $user = new User();

           $username = trim($_POST["emailOrId"]);
           $email = trim($_POST["emailOrId"]);
           $password = trim($_POST["password"]);

           // Checking username or email from database
           $validUser = $user->getUserByUsername($username) || $user->getUserByEmail($email);
           if (!empty($validUser))
           {
//               $this ->redirect('event-overview');
               echo "Success";
           }
           else{
               echo "Invalid username or e-mail.";
           }

           $validUser = $user->validatePassword($password);
           $hash = 'test1234';
           if(password_verify($password, $hash)){
               echo "Success";
           }
           else {
               echo "Invalid password.";
           }
       }
   }
}

