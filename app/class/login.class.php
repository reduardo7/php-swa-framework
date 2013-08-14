<?php

/**
 * Login Session.
 *
 * @author Eduardo Cuomo <eduardo.cuomo.ar@gmail.com>.
 */
class Login extends Session {

    const USUARIO_TIPO_SUPER_ADMIN = 1;
    const USUARIO_TIPO_ADMIN       = 2;
    const USUARIO_TIPO_USER        = 3;

    /**
     * Session namespace.
     *
     * @var string
     */
    const __SESSION_NAMESPACE = '__LOGIN';

    /**
     * Login session status.
     *
     * @var string
     */
    const __SESSION_VAR_STATUS = '__STATUS';

    function __construct() {
        parent::__construct(self::__SESSION_NAMESPACE);
    }

    /**
     * Start user session.
     *
     * @param array|Model $data Login data.
     */
    public function login($data = array()) {
        if ($data instanceof Model) $data = $data->record;
        $this->set(self::__SESSION_VAR_STATUS, true);
        $this->set($data);
    }

    /**
     * Close login session.
     *
     * @param boolean $destroy_session Optional. Destroy all session?
     */
    public function logout($destroy_session = false) {
        $this->set(self::__SESSION_VAR_STATUS, false);
        $this->destroy($destroy_session);
    }

    /**
     * Return TRUE if session is started.
     *
     * @return boolean TRUE if login session is active.
     */
    public function getStatus() {
        return $this->hasKey(self::__SESSION_VAR_STATUS) && $this->get(self::__SESSION_VAR_STATUS);
    }

    /**
     * Return TRUE if session is started.
     *
     * @return boolean TRUE if login session is active.
     */
    public function isLogin() {
        return $this->getStatus();
    }

    /*** EXTEND ***/

    /**
     * Returns TRUE if is Super Admin.
     *
     * @return boolean
     */
    public function isSuperAdmin() {
        return $this->get('tipo') == self::USUARIO_TIPO_SUPER_ADMIN;
    }

    /**
     * Returns TRUE if is Admin.
     *
     * @return boolean
     */
    public function isAdmin() {
        return $this->get('tipo') == self::USUARIO_TIPO_ADMIN;
    }

    /**
     * Returns TRUE if is user.
     *
     * @return boolean
     */
    public function isUser() {
        return $this->get('tipo') == self::USUARIO_TIPO_USER;
    }

    /**
     * Returns TRUE if is a valid user.
     *
     * @return boolean
     */
    public function isValidUser() {
        return in_array($this->get('tipo'), array(self::USUARIO_TIPO_USER, self::USUARIO_TIPO_ADMIN, self::USUARIO_TIPO_SUPER_ADMIN));
    }

    /**
     * Returns TRUE if is a valid view page.
     *
     * @return boolean
     */
    public function isValidView() {
        $params = new Params();
        foreach (CONFIG(AppConfigLoginNoValidatePages) as $ncp) {
            list($context, $view) = explode('/', $ncp);
            if (in_array($view, array($params->view, '*')) && in_array($context, array($params->context, '*'))) {
                // Valid
                return true;
            }
        }
        return false;
    }

    /**
     * Returns TRUE if session started or is a valid view page.
     *
     * @return boolean
     */
    public function isValid() {
        return ($this->getStatus() && $this->isValidUser()) || $this->isValidView();
    }
}