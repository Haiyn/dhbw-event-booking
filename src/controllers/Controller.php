<?php

namespace controllers;

use stdClass;

abstract class Controller
{
    public $viewName;
    protected $_view;

    function __construct() {
        $this->_view = new stdClass();
    }

    abstract public function render($params);

    /*
     * Redirects to the given url. Makes use of the router.
     */
    protected final function _redirect($url)
    {
        header("Location: $url");
        header("Connection: close");
        exit;
    }

    /*
     * Opens the phtml file in /views/[viewname]/[viewname].phtml
     */
    public final function showView()
    {
        extract((array)$this->_view);
        require dirname(__DIR__)."/views/{$this->viewName}/{$this->viewName}.phtml";
    }

    /*
     * These message setters set a new session variable according to their view name and message level and store the
     * passed message in it. It then redirects to their current view with the message level as a parameter
     *
     * e.g. in register, the _setError method creates $_SESSION['REGISTER_ERROR'] and reroutes to /register?error
     */
    protected function _setError($errorMessage)
    {
        $_SESSION[strtoupper($this->viewName) . "_ERROR"] = $errorMessage;
        $this->_redirect("/{$this->viewName}?error");
    }

    protected function _setWarning($warningMessage) {
        $_SESSION[strtoupper($this->viewName) . "_WARNING"] = $warningMessage;
        $this->_redirect("/{$this->viewName}?warning");
    }

    protected function _setSuccess($successMessage) {
        $_SESSION[strtoupper($this->viewName) . "_SUCCESS"] = $successMessage;
        $this->_redirect("/{$this->viewName}?success");
    }


}