<?php
/**
 * Simple Web Application.
 *
 * @license Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @author Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @version 1.0
 * @package ar.com.eduardocuomo
 * @copyright Eduardo Daniel Cuomo
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// XDIE
require('xdie.inc.php');


// Constants
require('constants.inc.php');


if (!ApplicationEnvIsDevelopment) {
    // Production environment
    // Remove error reporting
    // error_reporting(0);
}


// Configurations
require('config.inc.php');

/**
 * Get Application Configuration.
 *
 * @param string $config Use AppConfig_* constants.
 * @param multitype:object $default Default value.
 * @return multitype:object
 */
function CONFIG($config, $default = null) {
    return isset($GLOBALS[AppConfig][$config]) ? $GLOBALS[AppConfig][$config] : $default;
}

/**
 * Check if an empty String.
 *
 * @param object $var Variable to check.
 * @return boolean TRUE if variable is empty string.
 */
function emptyCheck($var) {
    return !isset($var) || is_null($var) || (is_array($var) ? (count($var) == 0) : ($var === ''));
}

/**
 * Check if a not empty String.
 *
 * @param object $var Variable to check.
 * @return boolean TRUE if variable is not empty string.
 */
function notEmptyCheck($var) {
    return !emptyCheck($var);
}


// Utils
require('utils.inc.php');


// Load class
require(ClassPath . 'runnable.class.php');
require(ClassPath . 'cnx.class.php');
require(ClassPath . 'params.class.php');
require(ClassPath . 'session.class.php');
require(ClassPath . 'login.class.php');
require(ClassPath . 'model.class.php');


/**
 * Application exception.
 *
 * @license Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @author Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @version 1.0
 * @package ar.com.eduardocuomo.exception
 * @copyright Eduardo Daniel Cuomo
 */
class AppException extends Exception {
    function __construct($message = null, $code = 500, $previous = null) {
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 20);
        $call  = $trace[0]['file'] . ":" . $trace[0]['line'];
        parent::__construct("$message\nException at: $call", $code, $previous);
    }
}

function AppExceptionHandler($code, $description, $file, $line, $context) {
    if (ApplicationEnvIsDevelopment || !in_array($code, array(8 /* Undefined variable */))) {
        ?><div style="position:absolute; top: 5%; left: 5%; width: 90%; display: block; background: #FFFFFF; border: 3px solid #FF0000; z-index: 99999; overflow: auto;">
            <h1>Error!</h1>
            <p><b>Code:</b> <?php echo $code; ?></p>
            <p><b>Description:</b> <?php echo $description; ?></p>
            <p><b>File:</b> <?php echo "$file : $line"; ?></p>
            <h2>Context</h2>
            <pre style="background: #DDDDDD; font-family: monospace;">
                <?php print_r($context); ?>
            </pre>
            <h2>Backtrace</h2>
            <pre style="background: #DDDDDD; font-family: monospace;">
                <?php debug_print_backtrace(0, 20) ?>
            </pre>
        </div><?
    }
}

//set error handler
set_error_handler("AppExceptionHandler", E_ALL);


/**
 * Application variables.
 *
 * @license Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @author Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @version 1.0
 * @package ar.com.eduardocuomo.app
 * @copyright Eduardo Daniel Cuomo
 */
class AppVars {

    /**
     * Variables.
     *
     * @var array
     */
    protected static $_vars = array();

    /**
     * Null value.
     *
     * @var string
     */
    const __NULL = '!-\t<¡\´{¨*]\`@\"$#<º¿\\""" -\'¬\r~|\n';

    /**
     * Get value.
     *
     * @param string $name Variable name.
     * @return object
     */
    public function get($name) {
        return isset(self::$_vars[$name]) ? self::$_vars[$name] : null;
    }

    /**
     * Has value.
     *
     * @param string $name Variable name.
     * @return boolean TRUE if has value.
     */
    public function has($name) {
        return isset(self::$_vars[$name]);
    }

    /**
     * Set value.
     *
     * @param string $name Variable name.
     * @param object $value Value.
     */
    public function set($name, $value) {
        self::$_vars[$name] = $value;
    }

    /**
     * Get value.
     *
     * @param string $name Variable name.
     * @return object
     */
    public function __get($name) {
        return $this->get($name);
    }

    /**
     * Set value.
     *
     * @param string $name Variable name.
     * @param object $value Value.
     */
    public function __set($name, $value) {
        return $this->set($name, $value);
    }

    /**
     * Set or get value.
     *
     * If $set_value is setted, then set value.
     *
     * @param string $name Variable name.
     * @param object $set_value Value to set.
     * @return object
     */
    public function __invoke($name, $set_value = self::__NULL) {
        if ($set_value !== self::__NULL) {
            $this->set($name, $set_value);
        }
        return $this->get($name);
    }

    public function __isset($name) {
        return $this->has($name);
    }
}

/**
 * Base APP. Used to extend all other classes and define
 * basic structure, variables and methods for all.
 *
 * @license Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @author Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @version 1.0
 * @package ar.com.eduardocuomo.app
 * @copyright Eduardo Daniel Cuomo
 */
abstract class APP_Base {
    /**
     * Main APP instance.
     *
     * @var APP
     */
    protected $APP;

    /**
     * @param APP $instance Main APP instance.
     */
    function __construct(APP $instance = null) {
        $this->APP = is_null($instance) ? APP::GetInstance() : $instance;
    }
}


/**
 * Main App.
 *
 * @license Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @author Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @version 1.0
 * @package ar.com.eduardocuomo.app
 * @copyright Eduardo Daniel Cuomo
 */
class APP extends APP_Base {
    /**
     * Default Layout.
     *
     * @var string
     */
    const LAYOUT_DEFAULT = 'main';

    /**
     * Current URL.
     *
     * @var string
     */
    public $URL;

    /**
     * Meta-Tags Keywords.
     *
     * @var array
     */
    public $keywords = array();

    /**
     * Breadcrumb.
     * Format: array(
     *     'title' => 'url'
     * )
     *
     * @var array
     */
    public $breadcrumb = array();

    /**
     * Page Title.
     *
     * @var string
     */
    public $page_title;

    /**
     * Styles.
     *
     * @var array
     */
    public $styles = array();

    /**
     * JavaScript files.
     *
     * @var array
     */
    public $javascripts = array();

    /**
     * Page content as HTML.
     *
     * @var string
     */
    public $page_content = null;

    /**
     * Parameters.
     *
     * @var Params
     */
    public $params;

    /**
     * DB Connection.
     *
     * @var Cnx
     */
    public $cnx;

    /**
     * Session.
     *
     * @var Session
     */
    public $session;

    /**
     * Session Login.
     *
     * @var Login
     */
    public $login;

    /**
     * Application variables.
     *
     * @var AppVars
     */
    public $vars;

    /**
     * True if renrering.
     *
     * @var boolean
     */
    protected $_rendering = false;

    /**
     * Layout to use.
     *
     * @var string
     */
    protected $_layout;

    /**
     * Syles on render.
     *
     * @var array
     */
    protected $_styles_render = array();

    /**
     * JavaScript files on render.
     *
     * @var array
     */
    protected $_javascripts_render = array();

    protected static $_render_file = array();
    protected static $_resource_inc = array();
    protected static $_resource_files = array();

    function __construct() {
        parent::__construct($this);
        // Init vars
        $this->URL        = substr($_SERVER['PHP_SELF'], 1);
        $this->params     = new Params();
        $this->page_title = CONFIG(AppConfigWebTitle);
        $this->vars       = new AppVars();

        // Load class
        $this->_loadClass('Cnx');
        $this->_loadClass('Session');
        $this->_loadClass('Login');

        // Set default Layout
        // If a Ajax Reuest, remove default Layout
        $this->setLayout($this->params->isAjax() ? false : self::LAYOUT_DEFAULT);
    }

    /**
     * Application Environment is Development?
     *
     * @return boolean TRUE if Application Environment is Development.
     */
    public function environmentIsDevelopment() {
        return ApplicationEnvIsDevelopment;
    }

    /**
     * Set Layout to use.
     *
     * @param string $layout_name Layout name. FALSE for no Layout.
     * @return APP
     *
     * @see APP::LAYOUT_DEFAULT
     */
    public function setLayout($layout_name) {
        $this->_layout = $layout_name;
        return $this;
    }

    /**
     * Clean before output.
     *
     * @return Current output before clean.
     */
    public function outputClean() {
        $o = ob_get_contents();
        ob_clean();
        ob_flush();
        return $o;
    }

    /**
     * Print current output and restart output capture.
     *
     * @return APP
     */
    public function outputFlush() {
        echo $this->outputClean();
        return $this;
    }

    /**
     * Add CSS file.
     *
     * @param string $css_file
     * @param boolean $top If TRUE, add CSS file in first line.
     * @param string $path FALSE to ignore.
     * @return APP
     */
    public function style($css_file, $top = false, $path = null) {
        return $this->_addResource($css_file, $top, $path, 'styles', ResourceCss);
    }

    /**
     * Add JS file.
     *
     * @param string $js_file
     * @param boolean $top If TRUE, add JS file in first line.
     * @param string $path FALSE to ignore.
     * @return APP
     */
    public function js($js_file, $top = false, $path = null) {
        return $this->_addResource($js_file, $top, $path, 'javascripts', ResourceJs);
    }

    /**
     * Add Meta-Tag Keywords.
     *
     * @param string $keyword Keyword.
     * @param string $keyword Keyword.
     * @param string $keyword Keyword.
     * @param ...
     * @return APP
     */
    public function addKeyWords($keyword) {
        $params = func_get_args();
        $this->keywords = array_merge($this->keywords, $params);
        return $this;
    }

    /**
     * Breadcrumb.
     *
     * @param string $title Breadcrumb title.
     * @param string $url Optional. Page URL.
     * @return APP
     *
     * @see APP::renderBreadcrumb()
     */
    public function addBreadcrumb($title, $url = 'javascript:void(0)') {
        $this->breadcrumb[$title] = $url;
        return $this;
    }

    /**
     * Render Breadcrumb.
     *
     * @param string $item_format Item format.
     *  Use '[url]' where render item URL, and '[title]' where render title.
     *  Example: '<li><a href="[url]">[title]</a></li>'
     *
     * @see APP::addBreadcrumb()
     */
    public function renderBreadcrumb($item_format) {
        if (count($this->breadcrumb) > 0) {
            foreach ($this->breadcrumb as $title => $url) {
                echo strtr($item_format, array(
                    '[url]'   => TagHtml::EscapeTag($url),
                    '[title]' => htmlentities($title)
                ));
            }
        }
    }

    /**
     * Set current page title.
     *
     * @param string $title
     * @return APP
     */
    public function setPageTitle($title) {
        $this->page_title = CONFIG(AppConfigWebTitle) . ' :: ' . $title;
        return $this;
    }

    /**
     * Render PHP Info.
     *
     * @param boolean $use_style Print PHP Info CSS style?
     * @param int $what PHP Info Output from "phpinfo($what)" manual.
     * @see http://php.net/manual/en/function.phpinfo.php
     */
    public function phpInfo($use_style = true, $what = INFO_ALL) {
        phpinfo($what);
        $cont = $this->outputClean();
        // Start
        define('PHPINFO_START', '<div class="center">');
        $cont = '<div class="phpinfo">' . substr($cont, strpos($cont, PHPINFO_START) + strlen(PHPINFO_START));
        // End
        $cont = substr($cont, 0, strrpos($cont, '</body>'));
        // Add spaces
        $cont = str_replace(',', ', ', $cont);
        // Style
        if ($use_style) {
            echo '<style type="text/css">'
            . '.phpinfo td, .phpinfo th, .phpinfo h1, .phpinfo h2 {font-family: sans-serif;}'
            . '.phpinfo pre {margin: 0px; font-family: monospace;}'
            . '.phpinfo a:link {color: #000099; text-decoration: none; background-color: #ffffff;}'
            . '.phpinfo a:hover {text-decoration: underline;}'
            . '.phpinfo table {border-collapse: collapse;}'
            . '.phpinfo .center {text-align: center;}'
            . '.phpinfo .center table { margin-left: auto; margin-right: auto; text-align: left;}'
            . '.phpinfo .center th { text-align: center !important; }'
            . '.phpinfo td, .phpinfo th { border: 1px solid #000000; font-size: 75%; vertical-align: baseline;}'
            . '.phpinfo h1 {font-size: 150%;}'
            . '.phpinfo h2 {font-size: 125%;}'
            . '.phpinfo .p {text-align: left;}'
            . '.phpinfo .e {background-color: #ccccff; font-weight: bold; color: #000000;}'
            . '.phpinfo .h {background-color: #9999cc; font-weight: bold; color: #000000;}'
            . '.phpinfo .v {background-color: #cccccc; color: #000000;}'
            . '.phpinfo .vr {background-color: #cccccc; text-align: right; color: #000000;}'
            . '.phpinfo img {float: right; border: 0px;}'
            . '.phpinfo hr {width: 600px; background-color: #cccccc; border: 0px; height: 1px; color: #000000;}'
            . '</style>';
        }
        // Render
        echo $cont;
    }

    /**
     * Render page using Layout.
     *
     * @param string $content Content to render.
     * @param string $layout Optional. Layout to use.
     */
    public function render($content, $layout = null) {
        $this->page_content = $content;
        $this->_rendering = true;
        $layout = (is_null($layout) ? $this->_layout : $layout);
        // Render
        if (!($layout === false)) {
            $file = LayoutPath . (is_null($layout) ? $this->_layout : $layout) . '.php';
            $this->_render($file);
        } else {
            // No Layout
            echo $this->page_content;
        }
    }

    /**
     * Render JSON.
     *
     * @param string $var Value to render as JSON.
     */
    public function renderJson($var) {
        $this->outputClean();
        // Render JSON
        echo json_encode($var);
        exit();
    }

    /**
     * Render view.
     *
     * @param string $view_name View name.
     */
    public function renderView($view_name) {
        $this->renderFile(ViewsPath . $view_name . '.php');
    }

    /**
     * Render file.
     *
     * @param string $file File path to render.
     */
    public function renderFile($file) {
        // Start buffer
        ob_start();
        // Render
        $this->_render($file);
        $content = $this->outputClean();
        // Render page
        $this->render($content);
        // Include
        $cont = ob_get_contents();
        $this->outputClean();

        // Extract JavaScript
        $this->_extractTag($cont, 'script', 'type="text/javascript" language="javascript"');
        //         $regex_js = '/<script(?:(?!src)(?!<\/script>).)*<\/script>/s';
        //         preg_match_all($regex_js, $cont, $scripts);
        //         $scripts = $scripts[0];
        //         if (count($scripts) > 0) {
        //             foreach ($scripts as $k => $script) {
        //                 $scripts[$k] = trim(preg_replace('/<\/script>$/s', '', preg_replace('/^<script[^>]*>/s', '', $script)));
        //             }
        //             $jss = implode("\n", $scripts);
        //             $cont = preg_replace($regex_js, '', $cont);
        //             $cont = str_replace($this->_createMark('script'), "<script type=\"text/javascript\" language=\"javascript\">{$jss}</script>", $cont);
        //         }

        // Extract CSS style
        $this->_extractTag($cont, 'style', 'type="text/css"');
        //         $regex_css = '/<style(?:(?!src)(?!<\/style>).)*<\/style>/s';
        //         preg_match_all($regex_js, $cont, $styles);
        //         $styles = $styles[0];
        //         if (count($styles) > 0) {
        //             foreach ($styles as $k => $style) {
        //                 $styles[$k] = trim(preg_replace('/<\/style>$/s', '', preg_replace('/^<style[^>]*>/s', '', $style)));
        //             }
        //             $csss = implode("\n", $styles);
        //             $cont = preg_replace($regex_css, '', $cont);
        //             $cont = str_replace($this->_createMark('css'), "<style type=\"text/style\">{$csss}</style>", $cont);
        //         }

        echo $cont;
    }

    /**
     * Render CSS files imports.
     *
     * @return APP
     */
    public function renderStyles() {
        if (sizeof($this->_styles_render) > 0) {
            foreach ($this->_styles_render as $style) {
                echo "<link rel=\"stylesheet\" href=\"$style\" type=\"text/css\" media=\"all\" />";
            }
        }
        if (sizeof($this->styles) > 0) {
            foreach ($this->styles as $style) {
                echo "<link rel=\"stylesheet\" href=\"$style\" type=\"text/css\" media=\"all\" />";
            }
        }
        echo $this->_createMark('style');
        return $this;
    }

    /**
     * Render JS files imports.
     *
     * @return APP
     */
    public function renderJs() {
        if (sizeof($this->_javascripts_render) > 0) {
            foreach ($this->_javascripts_render as $js) {
                echo "<script type=\"text/javascript\" language=\"javascript\" src=\"$js\"></script>";
            }
        }
        if (sizeof($this->javascripts) > 0) {
            foreach ($this->javascripts as $js) {
                echo "<script type=\"text/javascript\" language=\"javascript\" src=\"$js\"></script>";
            }
        }
        echo $this->_createMark('script');
        return $this;
    }

    /**
     * Return page content.
     */
    public function renderContent() {
        echo $this->page_content;
    }

    /**
     * Get Meta-Tag Keywords.
     *
     * @return string Keywords.
     */
    public function getKeywords() {
        return implode(',', $this->keywords);
    }

    /**
     * Redirect.
     *
     * @param array|string $url URL to redirect.
     */
    public function redirect($url) {
        if (is_array($url)) {
            $url = $this->createLink($url, false);
        }
        header("Location: $url");
        exit();
    }

    /**
     * Get image path.
     *
     * @param string $src Image.
     * @param boolean $return Optional. Default: true. TRUE to render value. FALSE to no render value.
     * @return string Resource path.
     */
    public function resourceImg($src, $render = true) {
        return $this->resource(ResourceImg . '/' . $src, $render);
    }

    /**
     * Get uploaded file path.
     *
     * @param string $src File name in "upload" path.
     * @param boolean $return Optional. Default: true. TRUE to render value. FALSE to no render value.
     * @return string Resource path.
     */
    public function uploadedFile($src, $render = true) {
        return $this->resource(UploadPath . '/' . $src, $render);
    }

    /**
     * Get resource path.
     *
     * @param string $src Resource.
     * @param boolean $return Optional. Default: true. TRUE to render value. FALSE to no render value.
     * @return string Resource path.
     */
    public function resource($src, $render = true) {
        $s = Utils::cleanPath($src);
        if ($render) echo $s;
        return $s;
    }

    /**
     * Show 404 error page.
     *
     * @param boolean $output_clean Default: TRUE. Clean output?
     */
    public function errorPage404($output_clean = true) {
        $this->outputClean();
        header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found", true, 404);
        if (!$output_clean) {
            $this->renderView(ViewError404);
        }
        die();
    }

    /**
     * Show 500 error page.
     *
     * @param string $description Description.
     * @param boolean $output_clean Clean output before rebder?
     */
    public function errorPage500($description = null, $output_clean = true) {
        if ($output_clean) $this->outputClean();
        header("{$_SERVER['SERVER_PROTOCOL']} 500 Internal Server Error", true, 500);
        $this->vars->error_description = $description;
        $this->renderView(ViewError500);
        die();
    }

    /**
     * Create link to view.
     *
     * @param string $params Parameters or view name.
     *     Parameters: array('view' => 'viesName', 'context' => 'contextName', 'param1' => 'value1')
     * @param boolean $return Optional. Default: true. TRUE to render value. FALSE to no render value.
     * @return string Link to view.
     */
    public function createLink($params = array(), $render = true) {
        $link = Params::CreateLink($params);
        if ($render) echo $link;
        return $link;
    }

    /**
     * Include class form "class" path.
     * File name must be a Class name.
     *
     * @param string $clazz1 Class to load.
     * @param string $clazz2 Class to load.
     * @param string $clazz3 Class to load.
     * @param string $clazz4 ...
     * @return APP
     */
    public function load($clazz1) {
        foreach (func_get_args() as $clazz) {
            if (!class_exists($clazz)) {
                $this->_require(ClassPath . $clazz . '.class.php', null, true);
            }
        }

        return $this;
    }

    /**
     * Include a file into view to render.
     * The include file must be in the views path.
     *
     * @param string $inc_file File name.
     *     File name format: _[$inc_file].inc.php
     *     File name example:
     *         'table' => _table.inc.php
     *         'row'   => _row.inc.php
     * @param array $vars Optional. Include variables to set.
     * @param string $inc_path Optional. Include path where search.
     * @return APP
     */
    public function includeView($inc_file, array $vars = null, $inc_path = null) {
        $path = ViewsPath;
        if (is_null($inc_path)) {
            $path .= $inc_path;
        } else {
            $path .= trim($inc_path, DIRECTORY_SEPARATOR);
        }

        $this->_require($path . DIRECTORY_SEPARATOR . "_{$inc_file}.inc.php", $vars);
        return $this;
    }

    /**
     * Load model or models.
     *
     * @param string $model_name1 Model name to load.
     * @param string $model_name2 Model name to load.
     * @param string $model_name3 Model name to load.
     * @param string $model_name4 ...
     * @return APP
     */
    public function model($model_name1) {
        foreach (func_get_args() as $model_name) {
            $clazz = 'Model' . $model_name;
            if (!class_exists($clazz)) {
                $this->_require(ModelPath . $clazz . '.inc.php', null, true);
            }
        }
        return $this;
    }

    /**
     * Model name validate.
     * Returns TRUE if model exists.
     *
     * @param string $model_name Model name.
     * @return boolean
     */
    public function modelValidate($model_name) {
        return preg_match('/^[a-zA-Z0-9\_\-\.]+$/', $model_name) && file_exists(ModelPath . $model_name . '.inc.php');
    }

    /**
     * Resource include.
     *
     * @param string $resource_inc_name1 Resource include to load.
     * @param string $resource_inc_name2 Resource include to load.
     * @param string $resource_inc_name3 Resource include to load.
     * @param string $resource_inc_name4 ...
     * @return APP
     */
    public function resourceInc($resource_inc_name1) {
        foreach (func_get_args() as $resource_inc_name) {
            // Resource include name
            $resource_inc_name = strtolower(trim($resource_inc_name));
            $path = ResourceInc . $resource_inc_name . '.inc.php';
            if (array_search($path, self::$_resource_inc) === false) {
                self::$_resource_inc[] = $path;
                $this->_require($path);
            }
        }

        return $this;
    }

    /**
     * Print log.
     *
     * @param object $var Variable to print.
     * @param string $title Log title.
     */
    public function log($var, $title = null) {
        self::LogWrite($var, $title);
    }

    /**
     * Print log.
     *
     * @param object $var Variable to print.
     * @param string $title Log title.
     */
    public static function LogWrite($var, $title = null) {
        if (ApplicationEnvIsDevelopment) {
            $str = date('Y-m-d h:i:s | ');
            if (!is_null($title)) $str .= $title . ': ';
            $str .= var_export($var, true) . "\n\n";
            file_put_contents(LogPath, $str, FILE_APPEND);
        }
    }

    /**
     * Current APP instance.
     *
     * @return APP
     */
    public static function GetInstance() {
        return $GLOBALS['APP'];
    }

    /**
     * Call instance methods from static call.
     *
     * The first letter of method must be uppercase.
     *
     * APP::MethodName($p1, $p2) == $APP->methodName($p1, $p2);
     *
     * @use APP::MethodName($parameter1, $parameter2);
     * @return Method result.
     */
    public static function __callStatic($method, $arguments) {
        return call_user_func_array(array(self::GetInstance(), lcfirst($method)), $arguments);
    }

    /**
     * Render file.
     *
     * @param string $file File to render.
     */
    protected function _render($file) {
        if (file_exists($file)) {
            $kfile = Utils::CleanPath($file);

            if (!array_key_exists($kfile, self::$_render_file)) {
                self::$_render_file[$kfile] = 0;
            }

            // Prevent recursive loop
            if (@++self::$_render_file[$kfile] > 3) {
                $times = self::$_render_file[$kfile];
                $fl = __FILE__ . ':' . __LINE__;
                throw new AppException("Loop on include '$file'. Times: $times");
            }

            // Alias

            // Context init file
            $file_path = substr($file, 0, strrpos($file, '/') + 1);
            $init_file = $file_path . InitContextFileName;
            $kinit_file = Utils::CleanPath($init_file);
            // Prevent re-include
            if (!isset(self::$_render_file[$kinit_file]) && file_exists($init_file)) {
                self::$_render_file[$kinit_file] = 1;
                $this->_require($init_file, null, true);
            }

            // Include
            $this->_require($file);
        } else {
            if (false && $this->environmentIsDevelopment()) {
                // Development, show error
                $file_name = substr($file, strrpos($file, '/') + 1);
                throw new AppException("File '$file_name' not found.\nPath: $file");
            } else {
                $this->errorPage404();
            }
        }
    }

    /**
     * Return PHP executable file if it is.
     *
     * @param string $file
     * @param string $path
     * @return string
     */
    protected function _xphpFile($file, $path) {
        $fu = preg_match('/^[a-zA-Z0-9]+:\/\/.+/', $file);
        if (
                !$fu // Not a URL
                && preg_match('/^.+\.' . XPHP_FILE . '\.[a-zA-Z0-9]+$/i', $file) // *.xphp.*
        ) {
            if (!($path === false)) {
                $file = "$path/$file";
            }
            return '/' . XPHP_FILE . '/' . trim($file, '/');
        } else {
            if (!(($path === false) || $fu)) {
                $file = "$path/$file";
            }
            return $file;
        }
    }

    /**
     * Load class into local variable.
     *
     * @param string $class Class name.
     */
    protected function _loadClass($class) {
        if (class_exists($class)) {
            $var = strtolower($class);
            eval("\$this->{$var} = new $class();");
        }
    }

    protected function _createMark($x) {
        return '<!-- ' . $x . '-' . md5($x . __CLASS__ . __FUNCTION__) . ' -->';
    }

    protected function _addResource($file, $top, $path, $var, $resource_path) {
        if (is_null($path)) $path = $this->resource($resource_path, false);
        $file = $this->_xphpFile($file, $path);
        if (array_search($file, self::$_resource_files) === false) {
            if ($this->_rendering) {
                if ($top) {
                    $this->{"_{$var}_render"} = array_merge(array($file), $this->{"_{$var}_render"});
                } else {
                    $this->{"_{$var}_render"}[] = $file;
                }
            } else {
                if ($top) {
                    $this->{$var} = array_merge(array($file), $this->{$var});
                } else {
                    $this->{$var}[] = $file;
                }
            }

            self::$_resource_files[] = $file;
        }
        return $this;
    }

    protected function _require($path, array $vars = null, $once = false) {
        if (file_exists($path)) {
            // Set varialbes
            $APP = $this; // Alias
            if (!is_null($vars) && (count($vars) > 0)) {
                foreach ($vars as $var_name => $var_value) {
                    $$var_name = $var_value;
                }
            }
            // Include
            if ($once) {
                require_once($path);
            } else {
                require($path);
            }
        } else {
            throw new Exception("File not found: {$path}");
        }
    }

    /**
     * Extract tag content.
     */
    protected function _extractTag(&$cont, $tag, $props) {
        $regex_tag = "/<{$tag}(?:(?!src)(?!<\/{$tag}>).)*<\/{$tag}>/s";
        preg_match_all($regex_tag, $cont, $tag_datas);
        $tag_datas = $tag_datas[0];
        if (count($tag_datas) > 0) {
            foreach ($tag_datas as $k => $tag_data) {
                $tag_datas[$k] = trim(preg_replace("/<\/{$tag}>$/s", '', preg_replace("/^<{$tag}[^>]*>/s", '', $tag_data)));
            }
            $tag_cont = implode("\n", $tag_datas);
            $cont = preg_replace($regex_tag, '', $cont);
            $cont = str_replace($this->_createMark($tag), "<{$tag} {$props}>{$tag_cont}</{$tag}>", $cont);
        }
    }
}

try {
    /**
     * Main App.
     *
     * @var APP
     */
    $GLOBALS['APP'] = $APP = new APP();
    // Render page
    $APP->renderView($APP->params->context . DIRECTORY_SEPARATOR . $APP->params->view);
} catch (Exception $e) {
    // Exception
    if (ApplicationEnvIsDevelopment) {
        $APP->errorPage500("{$e->getMessage()}\n\n{$e->getTraceAsString()}");
    } else {
        $APP->errorPage500("Error!");
    }
}
