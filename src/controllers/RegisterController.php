<?php

namespace controllers;

use models\User;

class RegisterController extends Controller
{
    public function render($parameters) {
        if (isset($_POST["username"]) && isset($_POST["password"]) && isset($_POST["email"])) {
            $user_data = [
                "username" => htmlspecialchars($_POST["username"]),
                "password" => htmlspecialchars($_POST["password"]),
                "email" => htmlspecialchars($_POST["email"])
            ];

            $user_model = new User();

            $user_model->getUserByUsername($user_data["username"]);
            if (!empty($user))
                $this->redirect('/register?error');

            $passwordHash = password_hash($user_data["password"], PASSWORD_DEFAULT);
            $user_data["password"] = $passwordHash;

            $user_model->addUser($user_data);

            $this->redirect('/login');
        }

        $this->view->pageTitle = "Register";
        $this->view->isRegistrationError = isset($_GET["error"]);
    }

}