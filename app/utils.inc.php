<?php

/**
 * Utils.
 *
 * @license Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @author Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @version 1.0
 * @package ar.com.eduardocuomo.utils
 * @copyright Eduardo Daniel Cuomo
 */
class Utils {

    /**
     * Format money number.
     *
     * @param Number $value Value.
     * @param string $symbol Money symbol.
     * @return string
     */
    public static function MoneyFormat($value, $symbol = '$') {
        return $symbol . number_format($value, 2, ',', '.');
    }

    /**
     * Send an e-Mail to web-page.
     *
     * @param Title $title e-Mail title.
     * @param string $from from e-Mail.
     * @param string $from_name from name.
     * @param string $html body as HTML.
     * @param null|string $text body as Text. If NULL, remove tags from HTML.
     * @return null|string Error message, or NULL if success.
     */
    public static function SendMail($title, $from, $from_name, $html, $text = null) {
        return self::SendMailTo($title, $from, $from_name, array(CONFIG(AppConfigMailCopyTo) => CONFIG(AppConfigMailCopyToName)), $html, $text);
    }

    /**
     * Send an e-Mail to web-page.
     *
     * @param Title $title e-Mail title.
     * @param string $from from e-Mail.
     * @param string $from_name from name.
     * @param array $address Array of e-Mails.
     *  array(
     *    [E-MAIL] => [NAME],
     *    ...
     *  )
     * @param string $html body as HTML.
     * @param null|string $text body as Text. If NULL, remove tags from HTML.
     * @return null|string Error message, or NULL if success.
     */
    public static function SendMailTo($title, $from, $from_name, array $address, $html, $text = null) {
        // Includes
        require_once(PluginPath . 'PHPMailer' . DIRECTORY_SEPARATOR . 'class.phpmailer.php');
        require_once(PluginPath . 'PHPMailer' . DIRECTORY_SEPARATOR . 'class.smtp.php');

        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPAuth = CONFIG(AppConfigMailSmtpAuth);
        $mail->Host     = CONFIG(AppConfigMailHost);
        $mail->Port     = CONFIG(AppConfigMailPort);
        $mail->Username = CONFIG(AppConfigMailUser);
        $mail->Password = CONFIG(AppConfigMailPass);

        $mail->From     = CONFIG(AppConfigMailUser);
        $mail->FromName = $from_name;
        $mail->Subject  = $title;
        $mail->AltBody  = is_null($text) ? strip_tags($html) : $text;

        $mail->AddReplyTo($from);
        $mail->MsgHTML($html);
        foreach ($address as $m => $n) {
            $mail->AddAddress($m, $n);
        }
        $mail->IsHTML(true);

        if (@$mail->Send()) {
            return null;
        } else {
            return $mail->ErrorInfo;
        }
    }

    /**
     * Clean path.
     *
     * @param string $path Path to clean.
     * @return string Cleaned path.
     */
    public static function CleanPath($path) {
        $p2 = trim(str_replace('/./', '/', '/' . $path), '/');
        $p2 = '/' . trim(str_replace('//', '/', $p2), '/');
        return ($p2 == $path) ? $p2 : self::CleanPath($p2);
    }

    /**
     * Escape file name.
     *
     * @param string $file_name File name.
     * @return string File name.
     */
    public static function EscapeFileName($file_name) {
        return preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $file_name);
    }

    /**
     * Convert an array to object.
     *
     * @param array $array Array to convert.
     * @return stdClass
     */
    public static function ArrayToObject(array $array = array()) {
        return json_decode(json_encode($array));
    }

    /**
     * Returns the maximum file size allowed for upload in MB.
     *
     * @return integer
     */
    public static function MaxUploadSize() {
        $max_upload   = intval(ini_get('upload_max_filesize'));
        $max_post     = intval(ini_get('post_max_size'));
        $memory_limit = intval(ini_get('memory_limit'));
        return min($max_upload, $max_post, $memory_limit);
    }

    /**
     * Create new instance of a PHP Class.
     *
     * @param string $clazz Class name to create.
     * @param object $param1 Optional. Constructor parameter 1.
     * @param object $param2 Optional. Constructor parameter 2.
     * @param object $param3 Optional. Constructor parameter 3.
     * @param object $param4 ...
     * @return stdClass Class name
     */
    public static function NewInstanceOf($clazz) {
        $params = func_get_args();
        $prms = '';
        if (count($params) > 1) {
            $params = array_shift($params);
            foreach ($params as $k => $v) {
                if (notEmptyCheck($prms)) $prms .= ',';
                $prms .= "\$params[{$k}]";
            }
        }
        eval("\$o = new {$clazz}({$prms});");
        return $o;
    }

    /**
     * Create new instance of a PHP Class.
     *
     * @param string $clazz Class name to create.
     * @param string $static Static method.
     * @param object $param1 Optional. Constructor parameter 1.
     * @param object $param2 Optional. Constructor parameter 2.
     * @param object $param3 Optional. Constructor parameter 3.
     * @param object $param4 ...
     * @return object Class method result.
     */
    public static function CallStatic($clazz, $static) {
        $params = func_get_args();
        $prms = '';
        if (count($params) > 1) {
            $params = array_shift($params);
            foreach ($params as $k => $v) {
                if (notEmptyCheck($prms)) $prms .= ',';
                $prms .= "\$params[{$k}]";
            }
        }
        eval("\$o = {$clazz}::{$static}({$prms});");
        return $o;
    }

    /**
     * Create new instance of a PHP Class.
     *
     * @param string $clazz Class name to create.
     * @param string $var Static variable or constant name.
     * @return object Variable value.
     */
    public static function GetStatic($clazz, $var) {
        eval("\$o = {$clazz}::{$var};");
        return $o;
    }

    /**
     * Get client IP.
     *
     * @return string Client IP.
     */
    public static function GetClientIp() {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_VIA'])) {
            $ip = $_SERVER['HTTP_VIA'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = 'unknown';
        }
        return $ip;
    }
}


/**
 * HTML tags.
 *
 * @license Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @author Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @version 1.0
 * @package ar.com.eduardocuomo.utils.html
 * @copyright Eduardo Daniel Cuomo
 */
class TagHtml extends APP_Base {

    /**
     * HTML tag.
     *
     * @param string $type HTML tag type.
     * @param string $string String.
     * @param array $attrs Attributes.
     * @param boolean $return Default: FALSE. Return value and no print?
     * @return string|null
     */
    public static function Tag($type, $string = null, array $attrs = array(), $return = false) {
        $atr = '';
        if (count($attrs) > 0) $atr = ' ' . self::__attributes($attrs);
        if (is_null($string)) {
            $html = "<{$type}{$atr} />";
        } else {
            $html = "<{$type}{$atr}>{$string}</{$type}>";
        }
        if ($return) {
            return $html;
        } else {
            echo $html;
        }
    }

    /**
     * Escape HTML in text to use into HTML Tag.
     * Replace:
     *     "  => '
     *     \n => ' '
     *     \r => ' '
     *     \t => ' '
     *
     * @param string $value Text to escape.
     * @return string Text.
     */
    public static function EscapeTag($value) {
        return strtr($value, array(
                '"' => '\'',
                "\n" => ' ',
                "\t" => ' ',
                "\r" => ''
        ));
    }

    /**
     * Prepare attributes.
     *
     * @param array $attrs Attributes.
     * @param array $defaults Default attributes.
     * @return string HTML attributes.
     */
    public static function __attributes(array $attrs, array $defaults = null) {
        $html = '';
        $has_attrs = isset($attrs) && !is_null($attrs) && (count($attrs) > 0);
        $has_def = !is_null($defaults) && (count($defaults) > 0);
        if ($has_attrs || $has_def) {
            if ($has_attrs) {
                $attrs = array_change_key_case($attrs, CASE_LOWER);
            } else {
                $attrs = array();
            }
            if ($has_def) {
                $defaults = array_change_key_case($defaults, CASE_LOWER);
                $attrs = array_merge($defaults, $attrs);
            }
            foreach ($attrs as $key => $value) {
                if (($key == 'style') && is_array($value)) {
                    // Style
                    $style = '';
                    foreach ($value as $k => $v)
                        $style .= "$k:$v;";
                    $value = $style;
                } elseif (($key == 'class') && is_array($value)) {
                    // Class
                    $class = '';
                    foreach ($value as $v) {
                        if (notEmptyCheck($class)) $class .= ' ';
                        $class .= $v;
                    }
                    $value = $class;
                } else {
                    // Other
                    $value = self::EscapeTag($value);
                }
                $html .= " $key=\"$value\"";
            }
        }
        return $html;
    }
}


/**
 * HTML utils for view.
 *
 * @license Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @author Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @version 1.0
 * @package ar.com.eduardocuomo.utils.html
 * @copyright Eduardo Daniel Cuomo
 */
class HTML extends TagHtml {

    /**
     * HTML H*.
     * HTML tag: h1, h2, ...
     *
     * @param integer $number
     * @param string $string String.
     * @param array $attrs Attributes.
     */
    public static function H($number, $string, array $attrs = array()) {
        self::Tag('h' . intval($number), $string, $attrs);
    }

    /**
     * Span.
     * HTML tag: span
     *
     * @param string $string String.
     * @param array $attrs Attributes.
     */
    public static function Span($string, array $attrs = array()) {
        self::Tag('span', $string, $attrs);
    }

    /**
     * Separation.
     * HTML tag: hr
     *
     * @param array $attrs Attributes.
     */
    public static function Hr(array $attrs = array()) {
        self::Tag('hr', null, $attrs);
    }

    /**
     * Line break.
     * HTML tag: br
     *
     * @param array $attrs Attributes.
     */
    public static function Br(array $attrs = array()) {
        self::Tag('br', null, $attrs);
    }

    /**
     * Span.
     * HTML tag: span
     *
     * @param string $string String.
     * @param array $attrs Attributes.
     */
    public static function Div($string, array $attrs = array()) {
        self::Tag('div', $string, $attrs);
    }

    /**
     * PRE.
     * HTML tag: pre
     *
     * @param string $string String.
     * @param array $attrs Attributes.
     */
    public static function Pre($string, array $attrs = array()) {
        self::Tag('pre', $string, $attrs);
    }

    /**
     * Pagraph.
     * HTML tag: p
     *
     * @param string $string String.
     * @param array $attrs Attributes.
     */
    public static function P($string, array $attrs = array()) {
        $ps = explode("\n", $string);
        foreach ($ps as $p) {
            self::Tag('p', trim($p), $attrs);
        }
    }

    /**
     * Strong.
     * HTML tag: strong
     *
     * @param string $string String.
     * @param array $attrs Attributes.
     */
    public static function Strong($string, array $attrs = array()) {
        self::Tag('strong', $string, $attrs);
    }

    /**
     * Escape string and print as HTML.
     *
     * @param string $string
     * @param boolean $return Optional. Default: true. TRUE to render value. FALSE to no render value.
     * @return string
     */
    public static function ToHtml($string, $render = true) {
        $h = nl2br(htmlentities($string));
        if ($render) echo $h;
        return $h;
    }

    /**
     * Print head page title.
     *
     * @param string $title Page title. If NULL, use APP page title.
     */
    public static function PageTitle($title = null) {
        if (is_null($title)) $title = APP::GetInstance()->page_title;
        echo preg_replace('/\<\wr[ \t\n\r]*(\/)?\>/', ' :: ', $title);
    }

    /**
     * Format money number.
     *
     * @param Number $value Value.
     * @param string $symbol Money symbol.
     * @see Utils::MoneyFormat
     */
    public static function MoneyFormat($value, $symbol = '$') {
        echo Utils::MoneyFormat($value, $symbol);
    }

    /**
     * Generate JS to anti-copy text.
     *
     * @param string $str to convert.
     */
    public static function AntiCopy($str) {
        $enc = '<span class="anticopy">';
        for ($i = 0; $i < strlen($str); $i++) {
            $enc .= self::_anti_copy_code() . $str[$i];
        }
        echo $enc . self::_anti_copy_code() . '</span>';
    }

    /**
     * Generate Anti-Copy code.
     *
     * @return string
     */
    protected static function _anti_copy_code() {
        $c = '';
        $chrs = '124357689qazwsxedcrfvtgbbyhnujmikolp963852741';
        for ($i = 0; $i <= rand(20, 50); $i++) {
            $c .= substr($chrs, rand(1, strlen($chrs)) - 1, 1);
        }
        return "<b>$c</b>";
    }
}