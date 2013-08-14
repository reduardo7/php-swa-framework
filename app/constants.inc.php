<?php

/**
 * Application Environment.
 * Type: Development.
 *
 * @var string
 */
define('ApplicationEnvDevelopment', 'development');

/**
 * Application Environment.
 * Type: Production.
 *
 * @var string
 */
define('ApplicationEnvProduction', 'production');

/**
 * Application Environment.
 * Apache VAR name.
 *
 * @var string
 */
define('ApplicationEnvVar', 'APPLICATION_ENV');

/**
 * Current Application Environment.
 *
 * @var string
 */
define('ApplicationEnv', isset($_SERVER[ApplicationEnvVar]) ? strtolower($_SERVER[ApplicationEnvVar]) : ApplicationEnvProduction);

/**
 * HTTP host name / URL.
 *
 * @var string
 */
define('HttpHost', $_SERVER['HTTP_HOST']);

/**
 * TRUE if the Application Environment is Development.
 *
 * @var boolean
 */
define('ApplicationEnvIsDevelopment', ApplicationEnv == ApplicationEnvDevelopment);

/**
 * Base path.
 * To use "root" path, use empty string
 *
 * @var string
 */
define('BasePath', '.' . DIRECTORY_SEPARATOR);

/**
 * Application path.
 *
 * @var string
 */
define('ApplicationPath', BasePath . 'app' . DIRECTORY_SEPARATOR);

/**
 * Plugin path.
 *
 * @var string
 */
define('PluginPath', ApplicationPath . 'plugins' . DIRECTORY_SEPARATOR);

/**
 * Class path.
 *
 * @var string
 */
define('ClassPath', ApplicationPath . 'class' . DIRECTORY_SEPARATOR);

/**
 * Model path.
 *
 * @var string
 */
define('ModelPath', ApplicationPath . 'models' . DIRECTORY_SEPARATOR);

/**
 * Includes path.
 *
 * @var string
 */
define('IncludesPath', ApplicationPath . 'includes' . DIRECTORY_SEPARATOR);

/**
 * Log file path.
 *
 * @var string
 */
define('LogPath', '/tmp/xphp.log');

/**
 * Layout path.
 *
 * @var string
 */
define('LayoutPath', ApplicationPath . 'layout' . DIRECTORY_SEPARATOR);

/**
 * Views path.
 *
 * @var string
 */
define('ViewsPath', ApplicationPath . 'views' . DIRECTORY_SEPARATOR);

/**
 * 404 view error page name.
 *
 * @var string
 */
define('ViewError404', 'error404');

/**
 * 500 view error page name.
 *
 * @var string
 */
define('ViewError500', 'error500');

/**
 * Default Layout to use.
 *
 * @var string
 */
define('LayoutDefault', 'main');

/**
 * Resources includes.
 *
 * @var string
 */
define('ResourceInc', ApplicationPath . 'resources' . DIRECTORY_SEPARATOR);

/**
 * Resource CSS.
 *
 * @var string
 */
define('ResourceCss', 'css');

/**
 * Resource JS.
 *
 * @var string
 */
define('ResourceJs', 'js');

/**
 * Resource Image.
 *
 * @var string
 */
define('ResourceImg', 'img');

/**
 * Execute PHP in files.
 * File name example:
 *     styles.xphp.css
 *     scripts.xphp.js
 *
 * @var string
 */
define('XPHP_FILE', 'xphp');

/**
 * Init file.
 * Use to include in all files in a context.
 * Usage:
 *     Add extra functions to pages.
 *     Check session status.
 *
 * @var string
 */
define('InitContextFileName', '_0init.inc.php');

/**
 * Validation: Decimal Char.
 *
 * @var char
 */
define('ValidationDecimalChar', '.');

/**
 * Validation: Date format.
 *
 * @var string
 */
define('ValidationDateFormat', 'y-m-d');

/**
 * Validation: Date format description.
 *
 * @var string
 */
define('ValidationDateFormatDescription', 'YYYY-mm-dd');

/**
 * HTML Meta-Tag: Description.
 *
 * @var string
 */
define('HtmlMetatagDescription', 'Author: Eduardo D. Cuomo, Illustrator: Eduardo D. Cuomo, Category: Mobile, Description: ');


/** ************** */
/** *** Upload *** */
/** ************** */

/**
 * Upload directory name.
 *
 * @var string
 */
define('UploadDirName', 'files');

/**
 * Upload path.
 *
 * @var string
 */
define('UploadPath', BasePath . UploadDirName);


/** ********************** */
/** *** config.inc.php *** */
/** ********************** */

/**
 * $GLOBAL application configuration namespace.
 *
 * @var string
 */
define('AppConfig', 'AppConfig');

/**
 * Login configuration.
 * Array with pages where not check login status (isValidPage returns TRUE).
 *
 * @uses
 * array(
 *     '[CONTEXT]/[VIEW]',
 *     ...
 * )
 * Use "*" as ALL selector (for CONTEXT or VIEW).
 *
 * @example
 * array(
 *     '*\/login',
 *     'admin/login'
 * )
 *
 * @var array
 *
 * @see Login::isValidPage()
 */
define('AppConfigLoginNoValidatePages', 'AppConfigLoginNoValidatePages');

/**
 * Application Configuration: e-Mail.
 * User name.
 *
 * @var string
 */
define('AppConfigMailUser', 'MAIL_USER');

/**
 * Application Configuration: e-Mail.
 * Password.
 *
 * @var string
 */
define('AppConfigMailPass', 'MAIL_PASS');

/**
 * Application Configuration: e-Mail.
 * e-Mail from.
 *
 * @var string
 */
define('AppConfigMailCopyTo', 'MAIL_FROM');

/**
 * Application Configuration: e-Mail.
 * e-Mail from name.
 *
 * @var string
 */
define('AppConfigMailCopyToName', 'MAIL_FROM_NAME');

/**
 * Application Configuration: e-Mail.
 * Server port.
 *
 * @var integer
 */
define('AppConfigMailPort', 'MAIL_PORT');

/**
 * Application Configuration: e-Mail.
 * Server host.
 *
 * @var string
 */
define('AppConfigMailHost', 'MAIL_HOST');

/**
 * Application Configuration: e-Mail.
 * Server requires SMTP auth?
 *
 * @var boolean
 */
define('AppConfigMailSmtpAuth', 'MAIL_SMTP_AUTH');

/**
 * Application Configuration: DB Connection.
 * DB name.
 *
 * @var string
 */
define('AppConfigCnxDb', 'CNX_DB');

/**
 * Application Configuration: DB Connection.
 * User.
 *
 * @var string
 */
define('AppConfigCnxUser', 'CNX_USER');

/**
 * Application Configuration: DB Connection.
 * Password.
 *
 * @var string
 */
define('AppConfigCnxPass', 'CNX_PASS');

/**
 * Application Configuration: DB Connection.
 * Serven URL.
 *
 * @var string
 */
define('AppConfigCnxServer', 'CNX_SERVER');

/**
 * Application Configuration: Web.
 * Title.
 *
 * @var string
 */
define('AppConfigWebTitle', 'WEB_TITLE');