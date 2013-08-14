<?php

/**
 * Parameters.
 *
 * @author Eduardo Cuomo <eduardo.cuomo.ar@gmail.com>.
 */
class Params {

    /**
     * Base URL parameter name.
     *
     * @var string
     */
    const PARAM = '_p';

    /**
     * Parameter for Ajax request.
     *
     * @var string
     */
    const PARAM_IS_AJAX = 'ajax';

    /**
     * Invalid parameters to exclude.
     * Warning! Not use as parameter!
     * Note: This parameters are present in 000webhost.
     *
     * @var array
     */
    public static $INVALID_PARAMS = array('siteowner', 'PHPSESSID');

    /**
     * View.
     *
     * @var string
     */
    public $view = null;

    /**
     * View.
     *
     * @var string
     */
    public $context = null;

    /**
     * Parameters.
     *
     * @var array
     */
    protected $_params = array();

    /**
     * Parameters string.
     *
     * @var string
     */
    protected $_p = null;

    /**
     * Complete parameters string.
     *
     * @var string
     */
    protected $_pa;

    /**
     * Instance.
     *
     * @var Params
     */
    protected static $_instance = null;

    /**
     * All Request params.
     *
     * @var array
     */
    protected static $_REQUEST_PARAMS_FULL = null;

    /**
     * Request params.
     *
     * @var array
     */
    protected static $_REQUEST_PARAMS = null;

    /**
     * URL special parameters string.
     *
     * @var string
     */
    protected static $_REQUEST_PARAM_STR = null;

    /**
     * Current URL view.
     *
     * @var string
     */
    protected static $_REQUEST_VIEW = null;

    /**
     * Current URL context.
     *
     * @var string
     */
    protected static $_REQUEST_CONTEXT = null;

    function __construct() {
        if (!self::$_REQUEST_PARAMS_FULL) {
            if ($_REQUEST) {
                // Remove invalid parameters
                foreach (self::$INVALID_PARAMS as $invalid) {
                    if (isset($_REQUEST[$invalid])) {
                        unset($_REQUEST[$invalid]);
                    }
                }

                if (isset($_REQUEST[self::PARAM])) {
                    $this->_pa  = $_REQUEST[self::PARAM];
                    $p          = explode('/', $_REQUEST[self::PARAM]);
                    $this->view = array_shift($p);

                    if (count($p) % 2 == 1) {
                        // Has context
                        $this->context = $this->view;
                        $this->view    = array_shift($p);
                    }

                    if ($p) {
                        $this->_p = implode('/', $p);
                        $x = array();
                        $a = null;
                        foreach ($p as $v) {
                            if (is_null($a)) {
                                $a = trim($v);
                            } else {
                                // This Object
                                $x[$a] = $v;
                                // GET
                                if (!isset($_GET[$a]))     $_GET[$a]     = $v;
                                // REQUEST
                                if (!isset($_REQUEST[$a])) $_REQUEST[$a] = $v;
                                // Next
                                $a = null;
                            }
                        }

                        // Add other parameters to array
                        $this->_params = array_merge($x, $_REQUEST);
                    } else {
                        $this->_p = '';
                        $this->_params = $_REQUEST;
                    }
                } else {
                    // No GET['p']
                    $this->_params = $_REQUEST;
                }
            }

            if (emptyCheck($this->view)) {
                $this->view = 'index';
            }

            if (emptyCheck($this->context)) {
                $this->context = '/';
                $path = ViewsPath . $this->view;
                $file = $path . '.php';

                if (!file_exists($file) && !is_file($file)) {
                    if (is_dir($path)) {
                        $this->context = $this->view;
                        $this->view    = 'index';
                    }
                }
            }

            // Save for cache
            self::$_REQUEST_PARAMS_FULL = $this->_params;
            self::$_REQUEST_PARAMS      = $this->_p;
            self::$_REQUEST_PARAM_STR   = $this->_pa;
            self::$_REQUEST_VIEW        = $this->view;
            self::$_REQUEST_CONTEXT     = $this->context;
        } else {
            // Load from cache
            $this->_params = self::$_REQUEST_PARAMS_FULL;
            $this->_p      = self::$_REQUEST_PARAMS;
            $this->_pa     = self::$_REQUEST_PARAM_STR;
            $this->view    = self::$_REQUEST_VIEW;
            $this->context = self::$_REQUEST_CONTEXT;
        }
    }

    /**
     * Get parameter.
     *
     * @param string $index Parameter name/index.
     * @return multitype:integer, NULL, string
     */
    public function __get($index) {
        return $this->get($index);
    }

    /**
     * Return all parameters.
     *
     * @param boolean Get base params (global parameter)?
     * @return array
     */
    public function getAll($base_params = false) {
        if ($base_params) {
            return $this->_params;
        } else {
            $p = $this->_params;
            unset($p[self::PARAM]);
            return $p;
        }
    }

    /**
     * Returns TRUE if has params.
     *
     * @return boolean TRUE if has params.
     */
    public function hasParams() {
        return count($this->getAll()) > 0;
    }

    /**
     * Returns TRUE if Ajax parameter is setted.
     *
     * @return boolean
     */
    public function isAjax() {
        return $this->has(self::PARAM_IS_AJAX);
    }

    /**
     * Get parameters string.
     *
     * @return string
     */
    public function getParametersString($original = false) {
        return $original ? $this->_pa : $this->_p;
    }

    /**
     * Get parameter.
     *
     * @param integer|string $index Parameter name/index.
     * @param integer|string|null $default Default value.
     * @return multitype:integer, NULL, string
     */
    public function get($index, $default = null) {
        if (isset($this->_params[$index])) {
            return $this->_params[$index];
        } else {
            return $default;
        }
    }

    /**
     * Returns TRUE if parameters is setted.
     *
     * @param string $key Parameter name.
     * @return boolean
     */
    public function has($key) {
        return isset($this->_params[$key]);
    }

    /**
     * Set parameter.
     *
     * @param string $name
     * @param integer|string $value
     */
    public function setParam($name, $value) {
        $this->_params[$name] = strval($value);
    }

    /**
     * Returns page view ([CONTEXT]/[VIEW]).
     *
     * @return string
     */
    public function getPage() {
        return "{$this->context}/{$this->view}";
    }

    /**
     * Create link to view.
     *
     * @param string $params Parameters or view name.
     *     Parameters: array('view' => 'viesName', 'context' => 'contextName', 'param1' => 'value1')
     * @return string Link to view.
     */
    public static function CreateLink($params = array()) {
        $prms = '';
        if (is_array($params)) {
            $it      = self::GetInstance();
            $pr      = array_change_key_case($params, CASE_LOWER);
            $view    = array_key_exists('view', $pr)    ? $pr['view']    : $it->view;
            $context = array_key_exists('context', $pr) ? $pr['context'] : $it->context;
            foreach ($params as $k => $v) {
                if (!in_array(strtolower($k), array('view', 'context'))) {
                    if (is_bool($v)) $v = $v ? 1 : 0;
                    $prms .= emptyCheck($prms) ? '?' : '&';
                    $prms .= urlencode($k) . '=' . urlencode($v);
                }
            }
        } else {
            $view = $params;
            $context = null;
        }
        $context = trim(trim($context), '/');
        $view = trim(trim($view), '/');
        if (emptyCheck($context) || (strtolower($context) == 'index')) {
            $context = '/';
        } else {
            $context = "/$context/";
        }
        $link = $context . $view . $prms;
        return $link;
    }

    /**
     * Get Instance.
     *
     * @return Params
     */
    public static function GetInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Convert all parameters to string.
     *
     * @return string
     */
    public function toString() {
        return self::CreateLink(array_merge(array(
            'view' => $this->view,
            'context' => $this->context
        ), $this->getAll()));
    }

    public function __toString() {
        return $this->toString();
    }

    public function __isset($key) {
        return $this->has($key);
    }

    public function __invoke($index) {
        return $this->get($index);
    }
}