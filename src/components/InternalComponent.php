<?php

namespace components;

/**
 * Abstract Class InternalComponent
 * Parent class for all internal component classes. Offers internal error redirecting.
 * @package components
 */
abstract class InternalComponent
{
    /*
     * Redirects to the given url. Makes use of the router.
     */
    final protected function redirect($url)
    {
        header("Location: $url");
        header("Connection: close");
        exit;
    }

    /*
     * These message setters set a new session variable according to their view name and message level and store the
     * passed message in it. It then redirects to their current view with the message level as a parameter
     *
     * e.g. in register, the _setError method creates $_SESSION['REGISTER_ERROR'] and reroutes to /register?error
     */
    protected function setError($errorMessage)
    {
        $_SESSION["INTERNAL_ERROR"] = $errorMessage;
        $this->redirect("/internal-error");
    }
}
