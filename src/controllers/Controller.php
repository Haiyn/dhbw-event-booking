<?php

namespace controllers;

use stdClass;

abstract class Controller
{
    public $viewName;
    protected $view;

    public function __construct()
    {
        $this->view = new stdClass();
    }

    abstract public function render($params);

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
     * Opens the phtml file in /views/[viewname]/[viewname].phtml
     */
    final public function showView()
    {
        extract((array)$this->view);
        require dirname(__DIR__) . "/views/{$this->viewName}/{$this->viewName}.phtml";
    }

    /*
     * These message setters set a new session variable according to their view name and message level and store the
     * passed message in it. It then redirects to their current view with the message level as a parameter
     *
     * e.g. in register, the _setError method creates $_SESSION['REGISTER_ERROR'] and reroutes to /register?error
     */
    protected function setError($errorMessage, $params = "")
    {
        $_SESSION[str_replace("-", "_", strtoupper($this->viewName)) . "_ERROR"] = $errorMessage;
        $this->redirect("/{$this->viewName}?error{$params}");
    }

    protected function setWarning($warningMessage, $params = "")
    {
        $_SESSION[str_replace("-", "_", strtoupper($this->viewName)) . "_WARNING"] = $warningMessage;
        $this->redirect("/{$this->viewName}?warning{$params}");
    }

    protected function setSuccess($successMessage, $params = "")
    {
        $_SESSION[str_replace("-", "_", strtoupper($this->viewName)) . "_SUCCESS"] = $successMessage;
        $this->redirect("/{$this->viewName}?success{$params}");
    }
}
