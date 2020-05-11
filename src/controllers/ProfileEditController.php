<?php


namespace controllers;

use components\core\Utility;
use components\email\EmailService;
use components\validators\ValidatorException;
use components\validators\UserValidator;
use models\User;

class ProfileEditController extends Controller
{
    public function render($params)
    {
        $this->session->checkSession();
        $user = User::getInstance();
        $userId = $user->getUserById($_SESSION['USER_ID']);
        $userMail = $userId->email;

        $this->displayUserInfo();

        //save button is pressed on username edit
        if (isset($_POST["username"])) {
            $new_data = [
                'username' => filter_var(htmlspecialchars($_POST['username']), FILTER_SANITIZE_STRING)
            ];
            foreach ($new_data as $key => &$value) {
                $new_data[$key] = trim($value);
            }

            $this->updateUsername($new_data, $userId);
            $this->setSuccess("Profile successfully updated");
        }


        //Save button pressed on personal info
        if (isset($_POST["first_name"]) && isset($_POST["last_name"]) && isset($_POST["email"])) {

            $new_data = [
                'first_name' => filter_var(htmlspecialchars($_POST['first_name']), FILTER_SANITIZE_STRING),
                'last_name' => filter_var(htmlspecialchars($_POST['last_name']), FILTER_SANITIZE_STRING),
                'email' => filter_var(htmlspecialchars($_POST['email']), FILTER_SANITIZE_EMAIL)
            ];

            foreach ($new_data as $key => &$value) {
                $new_data[$key] = trim($value);
            }

            $this->updatePersonalInfo($new_data, $userId);

            //check if there has been any input in the email field and if yes, send confirmation email
            if ($new_data['email'] !== $userMail){
                $this->confirmEmail($new_data['email']);
            }

            $this->setSuccess("Profile successfully updated");
        }

        $this->view->pageTitle = "Profile";
        $this->view->isSuccess = isset($_GET["success"]);
        $this->view->isError = isset($_GET["error"]);
    }


    /**
     * Displays personal information of currently logged in user inside editing input fields
     */
    private function displayUserInfo()
    {
        $user = User::getInstance();
        $userById = $user->getUserById($_SESSION['USER_ID']);

        $username = $userById->username;
        $firstName = $userById->first_name;
        $lastName = $userById->last_name;
        $email = $userById->email;

        $this->view->username = $username;
        $this->view->firstName = $firstName;
        $this->view->lastName = $lastName;
        $this->view->email = $email;
    }

    /**
     * Updates username
     * @param $new_data *new username
     * @param $old_data *old username
     */
    private function updateUsername($new_data, $old_data)
    {
        $user = User::getInstance();
        $userId = $_SESSION['USER_ID'];

        $userValidator = UserValidator::getInstance();
        try {
            $userValidator->validateUsername($new_data, $old_data);
        } catch (ValidatorException $exception) {
            $this->setError($exception->getMessage());
        }

        $new_data += ["user_id" => $userId];

        if (!$user->updateUsername($new_data)) {
            $this->setError("Something went wrong");
        }
    }

    /**
     * Updates first name, last name or email
     * @param $new_data * new data to be saved to database
     * @param $old_data *existing data
     */
    private function updatePersonalInfo($new_data, $old_data)
    {
        $user = User::getInstance();
        $userId = $_SESSION['USER_ID'];

        $userValidator = UserValidator::getInstance();
        try {
            $userValidator->validateNewData($new_data, $old_data);
        } catch (ValidatorException $exception) {
            $this->setError($exception->getMessage());
        }

        //check if there has been any input to the email field and if that email is already being used
        $existingEmail = $user->getUserByEmail($new_data["email"]);
        if ($new_data['email'] !== $old_data->email && !empty($existingEmail)) {
            $this->setError("This E-Mail address is already being used by another account!");
        }

        $new_data += ["user_id" => $userId];

        if (!$user->updateUserData($new_data)) {
            $this->setError("Something went wrong");
        }
    }


    private function confirmEmail($email)
    {
        if (isset($_POST['email']) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $initFile = Utility::getIniFile();
            $user = User::getInstance();
            $hash = $user->getUserByEmail($email)->verification_hash;

            if (filter_var($initFile['EMAIL_ENABLED'], FILTER_VALIDATE_BOOLEAN)) {
                $emailService = EmailService::getInstance();
                $emailService->sendEmail(
                    $email,
                    "Confirm your new email address",
                    "Follow <a href='" . Utility::getApplicationURL() . "/confirm?hash={$hash}'>this link</a> 
                to confirm your email address.");
            } else {
                $this->setSuccess("Please confirm your email address with this link: <a href='/confirm?hash={$hash}'>Confirm</a>");
            }

            $this->setSuccess("The E-Mail confirmation link has been successfully sent to 
                    <strong>{$email}</strong>");
        }
    }
}

