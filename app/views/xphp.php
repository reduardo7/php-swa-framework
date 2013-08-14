<?php
/**
 * XPHP Files.
 *
 * @license Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @author Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @version 1.0
 * @package ar.com.eduardocuomo
 * @copyright Eduardo Daniel Cuomo
 */

// Conversion
$XPHP_CONVERT = array(
    '{{=' => '<?php echo ', // Open tag
    '{{' => '<?php ', // Open tag
    '}}' => '; ?>' // Close tag
);

// Disable layout
$APP->setLayout(false);

$path = $APP->params->getParametersString();
$content = file_get_contents(BasePath . DIRECTORY_SEPARATOR . $path);
$php = strtr($content, $XPHP_CONVERT);

// Headers
switch (strtolower(substr($path, strlen($path) - 3))) {
    case 'css':
        header('Content-type: text/css');
        break;
    case 'js':
        header('Content-type: application/javascript');
        break;
    default:
        header('Content-type: text/plain');
        break;
}

// Render
eval("?>$php");