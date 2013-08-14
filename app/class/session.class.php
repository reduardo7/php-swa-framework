<?php

/**
 * Session Manager.
 *
 * @author Eduardo Cuomo <eduardo.cuomo.ar@gmail.com>.
 */
class Session {

    /**
     * Default session namespace.
     *
     * @var string
     */
    const __SESSION_NAMESPACE_DEFAULT = '__APP';

    /**
     * Namespace.
     *
     * @var string
     */
    protected $_namespace;

    /**
     * Create new session manager.
     *
     * @param string $namespace Namespace.
     *     Default: APP namespace.
     */
    function __construct($namespace = self::__SESSION_NAMESPACE_DEFAULT) {
        $this->_namespace = $namespace;
        $this->start();
    }

    /**
     * Start session.
     */
    public function start() {
        if (session_id() == '')
            session_start();
        if (!isset($_SESSION[$this->_namespace])) {
            $_SESSION[$this->_namespace] = array();
        }
    }

    /**
     * Destroy session.
     *
     * @param boolean $destroy_session Optional. Destroy all session?
     */
    public function destroy($destroy_session = false) {
        if ($destroy_session) {
            $this->destroyAll();
        } else {
            foreach ($_SESSION[$this->_namespace] as $k => $v) {
                $_SESSION[$this->_namespace][$k] = null;
                unset($_SESSION[$this->_namespace][$k]);
            }
            unset($_SESSION[$this->_namespace]);
            $_SESSION[$this->_namespace] = array();
        }
    }

    /**
     * Destroy all session.
     */
    public function destroyAll() {
        session_unset();
        session_destroy();
    }

    /**
     * Write session.
     */
    public function write() {
        session_commit();
        session_write_close();
        session_start();
    }

    /**
     * Set session values.
     *
     * @param string|array $key_data
     *     String: Key.
     *     Array: Array of keys and values.
     * @param object $value Optional Value if $key_data is string.
     */
    public function set($key_data, $value = null) {
        if (is_array($key_data)) {
            foreach ($key_data as $key => $value) {
                $this->set($key, $value);
            }
        } else {
            $key = $key_data;
            if (!isset($_SESSION[$this->_namespace]) || !is_array($_SESSION[$this->_namespace])) {
                $_SESSION[$this->_namespace] = array();
            }
            $_SESSION[$this->_namespace][$key] = $value;
        }
    }

    /**
     * Get session value.
     *
     * @param string $key Key.
     * @param object $default Default: NULL. Default value if value not exists.
     * @return object Session value.
     */
    public function get($key, $default = null) {
        return (isset($_SESSION[$this->_namespace]) && isset($_SESSION[$this->_namespace][$key])) ? $_SESSION[$this->_namespace][$key] : $default;
    }

    /**
     * Get session ID.
     *
     * @return string
     */
    public function getSessionId() {
        return session_id();
    }

    /**
     * Get session name.
     *
     * @return string
     */
    public function getSessionName() {
        return session_name();
    }

    /**
     * Get all session data.
     *
     * @return array
     */
    public function getData() {
        return $_SESSION[$this->_namespace];
    }

    /**
     * Key exists?
     *
     * @param string $key Key.
     * @return boolean TRUE if exists.
     */
    public function hasKey($key) {
        return isset($_SESSION[$this->_namespace]) && isset($_SESSION[$this->_namespace][$key]);
    }

    /**
     * Unset session value.
     *
     * @param string $key Session key to unset.
     */
    public function unsetValue($key) {
        $_SESSION[$this->_namespace][$key] = null;
        unset($_SESSION[$this->_namespace][$key]);
    }

    public function __isset($key) {
        return $this->hasKey($key);
    }

    public function __unset($key) {
        $this->unsetValue($key);
    }

    public function __set($key, $value) {
        $this->set($key, $value);
    }

    public function __get($key) {
        return $this->get($key);
    }
}