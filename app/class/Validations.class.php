<?php

/**
 *
 * @author ecuomo
 *
 */
class Validations {
    /**
     * Validate if value is empty.
     *
     * @param string $value Value to check.
     * @return boolean TRUE if value is empty.
     */
    public static function isEmpty($value) {
        return emptyCheck($value);
    }

    /**
     * Validate if value is Integer.
     *
     * @param string $value Value to check.
     * @return boolean TRUE if value is Integer.
     */
    public static function isInteger($value) {
        return self::isRegExp($value, '/^[0-9]+$/');
    }

    /**
     * Validate if value is Decimal.
     *
     * @param string $value Value to check.
     * @param string $decimal_char Optional Decimal char.
     * @return boolean TRUE if value is Decimal.
     */
    public static function isDecimal($value, $decimal_char = ValidationDecimalChar) {
        return self::isRegExp($value, "/$[0-9]+\\{$decimal_char}?[0-9]*^/");
    }

    /**
     * Validate value min length.
     *
     * @param string $value Value to check.
     * @param int $min_length Min length.
     * @return boolean TRUE if value min length is bigger or equals than $min_length.
     */
    public static function minLength($value, $min_length) {
        return strlen(trim($value)) >= $min_length;
    }

    /**
     * Validate value max length.
     *
     * @param string $value Value to check.
     * @param int $max_length Max length.
     * @return boolean TRUE if value max length is lower or equals than $max_length.
     */
    public static function maxLength($value, $max_length) {
        return strlen(trim($value)) <= $max_length;
    }

    /**
     * Validate if value is a valid e-Mail.
     *
     * @param string $value Value to check.
     * @return boolean TRUE if value is a valid e-Mail.
     */
    public static function isEmail($value) {
        return !!filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Validate if value is a valid IP.
     *
     * @param string $value Value to check.
     * @return boolean TRUE if value is a valid IP.
     */
    public static function isIP($value) {
        return !!filter_var($value, FILTER_VALIDATE_IP);
    }

    /**
     * Validate if value is a valid URL.
     *
     * @param string $value Value to check.
     * @return boolean TRUE if value is a valid URL.
     */
    public static function isURL($value) {
        return !!filter_var($value, FILTER_VALIDATE_URL);
    }

    /**
     * Validate if value is a Date.
     *
     * @param string $value Value to check.
     * @param string $date_format Optional. Date format.
     *      y = Year
     *      m = Month
     *      d = Day
     * @return boolean TRUE if value is a valid Date.
     */
    public static function isDate($value, $date_format = null) {
        if (emptyCheck($date_format)) $date_format = ValidationDateFormat;
        $date_format    = strtolower($date_format);
        $format         = preg_replace('/[^ymd]/', '', $date_format);
        $sep            = preg_replace('/[a-z]/', '', $date_format);

        if (
                (strlen($format) != 3)
                || (strlen($sep) != 2)
                || (strpos($format, 'd') === false)
                || (strpos($format, 'm') === false)
                || (strpos($format, 'y') === false)
                || (substr($sep, 0, 1) != substr($sep, 1))
        ) {
            throw new Exception("Invalid date format [{$date_format}]");
        }

        $date_parts = explode(substr($sep, 0, 1), $value);

        if (count($date_parts) != 3) return false;

        $day    = $date_parts[strpos($format, 'd')];
        $month  = $date_parts[strpos($format, 'm')];
        $year   = $date_parts[strpos($format, 'y')];

        if (empty($day) || empty($month) || empty($year)) {
            return false;
        } else {
            return checkdate($month, $day, $year);
        }
    }

    /**
     * Check if value has time format (HH:MM).
     *
     * @param string $time Time. Format: HH:MM.
     * @return boolean TRUE if is a time.
     */
    public static function isTime($time) {
        return self::isRegExp($time, '/^[0-2][0-9]:[0-5][0-9]$/');
    }

    /**
     * Validate if value pass RegExp.
     *
     * @param string $value Value to check.
     * @param string $regexp RegExp.
     * @return boolean TRUE if value pass RegExp.
     */
    public static function isRegExp($value, $regexp) {
        return preg_match($regexp, $value) === 1;
    }

    /**
     * Validate if value contains HTML tag.
     *
     * @param string $value Value to check.
     * @return boolean TRUE if value contains HTML tag.
     */
    public static function containsHtmlTag($value) {
        return self::isRegExp($value, '/<\/?\w+\s+[^>]*>/');
    }
}