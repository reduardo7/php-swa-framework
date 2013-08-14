<?php

/**
 * Connection function.
 * Used for Cnx querys.
 *
 * @author Eduardo Cuomo <eduardo.cuomo.ar@gmail.com>.
 *
 * @see Cnx
 */
class CnxQ {
    protected $_func;
    protected $_params;
    protected $_is_function;

    /**
     * Connection function.
     *
     * @uses
     *  WHERE d = new CnxQ('NOW')
     *  WHERE x = new CnxQ('TRIM', 'param1')
     *  WHERE x = new CnxQ('TRIM', '`column`')
     *  WHERE y = new CnxQ('CONCAT', array('`column1`', 'str1', '`column2`'))
     *  WHERE z = new CnxQ("CONCAT(TRIM(`column1`), '...')", null, false)
     *  WHERE x = new CnxQ('DATE', CnxQ::NOW())
     *
     * @param string $func Function name.
     * @param string|array|boolean $params Function parameters.
     *  Enclose the string with `` to use as column name.
     * @param boolean
     *  TRUE to create a function.
     *  FALSE to use $func as Query String.
     */
    function __construct($func, $params = null, $is_function = true) {
        $this->_is_function = $is_function;
        $this->_func        = $is_function ? strtoupper($func) : $func;
        if (is_null($params)) {
            $this->_params =  null;
        } else {
            if (is_array($params)) {
                $this->_params = $params;
            } else {
                $this->_params = array($params);
            }
        }
    }

    /**
     * Get Query String as String.
     *
     * @return string
     */
    public function toString() {
        if ($this->_is_function) {
            $p = '';
            if (!is_null($this->_params)) {
                if (!is_array($this->_params)) {
                    $p = $this->_params;
                } elseif (count($this->_params) > 0) {
                    foreach ($this->_params as $prm) {
                        if (notEmptyCheck($p)) $p .= ',';
                        $p .= (strpos($prm, '`') === 0) ? $prm : Cnx::GetInstance()->escape($prm);
                    }
                }
            }
            return "{$this->_func}({$p})";
        } else {
            return $this->_func;
        }
    }

    public function __toString() {
        return $this->toString();
    }

    /**
     * Create Connection Function.
     *
     * @uses
     *  WHERE d = CnxQ::NOW()
     *  WHERE d = CnxQ::DATE(CnxQ::NOW())
     *  WHERE n = CnxQ::TRIM('name')
     *  WHERE n = CnxQ::CONCAT('`column1`', 'str1', '`column2`')
     *
     * @return CnxQ
     */
    public static function __callStatic($name, $arguments) {
        return new self($name, $arguments, true);
    }

    /**
     * Create Query function.
     *
     * @uses
     *  WHERE d = CnxQ::Create('NOW')
     *  WHERE n = CnxQ::Create('TRIM', 'param1')
     *  WHERE n = CnxQ::Create('CONCAT', array('`column1`', 'str1', '`column2`'))
     *
     * @param string $func Function name.
     * @param string|array $params Function parameters.
     *  Enclose the string with `` to use as column name.
     * @return CnxQ
     */
    public static function Create($fn, $prms = null) {
        return new self($fn, $prms, true);
    }

    /**
     * Create Query String.
     *
     * @uses
     *  WHERE z = CnxQ::Query("CONCAT(TRIM(`column1`), '...')")
     *
     * @param string $quuery Query string.
     * @return CnxQ
     */
    public static function Query($query) {
        return new self($query, null, false);
    }
}

/**
 * DB connection.
 *
 * @author Eduardo Cuomo <eduardo.cuomo.ar@gmail.com>.
 */
class Cnx {

    /**
     * Text to debug Query string.
     */
    const DEBUG_TEXT = '***DEBUG***';

    /**
     * Array Value to show Query string.
     */
    const DEBUG_KEY = 'DEBUG';

    /**
     * MySQL error: Duplicate Entry.
     *
     * @var integer
     */
    const MYSQL_ERROR_CODE_DUPLICATE_ENTRY = 1062;

    /**
     * Character to put at END of field to
     * make as unique Array key in Array Query.
     *
     * @var string
     * @see Cnx::Where
     */
    const UNIQUE_COLUMN_CHAR = '#';

    /**
     * MySQL error message.
     *
     * @var string
     */
    protected $_error;

    /**
     * MySQL error code.
     *
     * @var integer
     */
    protected $_error_code;

    /**
     * Query executed?
     * TRUE: Query executed.
     * FALSE: Error or not executed.
     *
     * @var boolean
     */
    protected $_query_status = false;

    /**
     * MySQL user.
     *
     * @var string
     */
    protected $_cnx_user;

    /**
     * MySQL password.
     *
     * @var string
     */
    protected $_cnx_pass;

    /**
     * MySQL server.
     *
     * @var string
     */
    protected $_cnx_server;

    /**
     * MySQL DB.
     *
     * @var string
     */
    protected $_cnx_db;

    /**
     * MySQL connection.
     *
     * @var resource
     */
    protected $_cnx;

    /**
     * Show MySQL query in case of error.
     *
     * @var boolean
     */
    protected $_debug = false;

    /**
     * Print Query, no execute Query.
     *
     * @var boolean
     */
    protected $_get_query = false;

    /**
     * Query result.
     *
     * @var resource
     */
    protected $_result;

    /**
     * Instance.
     *
     * @var Cnx
     */
    protected static $_instance = null;

    function __construct($db = null, $user = null, $pass = null, $server = null){
        $this->_cnx_db      = is_null($db)      ? CONFIG(AppConfigCnxDb)        : $db;
        $this->_cnx_user    = is_null($user)    ? CONFIG(AppConfigCnxUser)      : $user;
        $this->_cnx_pass    = is_null($pass)    ? CONFIG(AppConfigCnxPass)      : $pass;
        $this->_cnx_server  = is_null($server)  ? CONFIG(AppConfigCnxServer)    : $server;
        $this->_debug       = ApplicationEnv == ApplicationEnvDevelopment;
        $this->connect();
    }

    /**
     * Get Instance.
     *
     * @return Cnx
     */
    public static function GetInstance() {
        if (is_null(self::$_instance)) self::$_instance = new self();
        return self::$_instance;
    }

    /**
     * Get current date and time.
     *
     * @param string $type Return format. Use "date" to get date,
     *  and "time" to get time. If string not contains "date" or "time",
     *  returns NULL.
     *  Example:
     *    'Date and Time'|'Date Time'|'DATE & TIME': Returns current Date and Time.
     *    'Date': Returns current Date.
     *    'TIME': Returns current Time.
     * @return string|null
     */
    public static function Now($type = 'date|time') {
        $d = !(stripos($type, 'date') === false);
        $t = !(stripos($type, 'time') === false);
        if ($d && $t) {
            // Current Date & Time
            return date('Y-m-d H:i:s');
        } elseif ($d) {
            // Current Date
            return date('Y-m-d');
        } elseif ($t) {
            // Current Time
            return date('H:i:s');
        } else {
            // Invalid Type, NULL
            return null;
        }
    }

    /**
     * Connect to MySQL.
     */
    public function connect() {
        if ($this->_cnx = @mysql_connect($this->_cnx_server, $this->_cnx_user, $this->_cnx_pass)) {
            if (@mysql_select_db($this->_cnx_db, $this->_cnx)) {
                // Set all results as UTF-8
                @mysql_query("SET NAMES 'utf8'");
            } else {
                throw new Exception("ERROR! Can not select DB [{$this->_cnx_db}]");
            }
        } else {
            throw new Exception("ERROR! Can not connect to MySQL [{$this->_cnx_server}]");
        }
    }

    /**
     * Escape text for MySQL query.
     *
     * @param string $t String to escape.
     * @param boolean $quotes Add quotes to result text?
     * @return string
     */
    public function escape($t, $quotes = false) {
        if ($t === true) {
            // Boolean TRUE
            return 1;
        } elseif ($t === false) {
            // Boolean FALSE
            return 0;
        } elseif (is_null($t)) {
            // NULL
            return 'NULL';
        } elseif (is_numeric($t)) {
            // Is a number
            return $t;
        } elseif ($t == '*') {
            // *
            return '*';
        } else {
            // Escape String
            $t = mysql_real_escape_string($t, $this->_cnx);
            return $quotes ? "'$t'" : $t;
        }
    }

    /**
     * Enalbe debug?
     *
     * @param boolean $enable Enable?
     * @return Cnx This instence.
     */
    public function debug($enable) {
        $this->_debug = !!$enable;
        return $this;
    }

    /**
     * Get query on run "query" method?
     *
     * @param boolean $enable Enable?
     * @return Cnx This instence.
     *
     * @see Cnx::query
     */
    public function getQueryString($enable = true) {
        $this->_get_query = $enable;
        return $this;
    }

    /**
     * Close MySQL connection.
     */
    public function close() {
        @mysql_close($this->_cnx);
    }

    /**
     * Execute MySQL Query.
     *
     * @param string|array $q Query to execute or Cnx::CreateQuery array.
     * @return Cnx
     *
     * @see Cnx::getQueryString
     * @see Cnx::CreateQuery
     */
    public function query($q) {
        if (is_array($q)) {
            return $this->query(self::CreateQuery($q));
        } else {
            if (!(strpos($q, self::DEBUG_TEXT) === false)) {
                // Debug Query
                $q = str_replace(self::DEBUG_TEXT, '', $q);
                $q = str_ireplace('SELECT', "\n<b>SELECT</b>", $q);               // SELECT
                $q = preg_replace('/\s+AS\s+/i', "<i>\$0</i>", $q);               // AS
                $q = preg_replace('/\s+ON\s+/i', "<i>\$0</i>", $q);               // ON
                $q = str_ireplace('FROM', "\n<b>FROM</b>", $q);                   // FROM
                $q = preg_replace('/([A-Z]+\s)?JOIN/i', "\n\t<b>\$0</b>", $q);    // JOIM
                $q = str_ireplace('WHERE', "\n<b>WHERE</b>", $q);                 // WHERE
                $q = preg_replace('/\s+(AND|OR)\s+/i', "\n\t<b>\$0</b>", $q);     // AMD / OR
                $q = preg_replace('/GROUP\s+BY/i', "\n<b>\$0</b>", $q);           // GROUP BY
                $q = preg_replace('/ORDER\s+BY/i', "\n<b>\$0</b>", $q);           // ORDER BY
                $q = str_ireplace('LIMIT', "\n<b>LIMIT</b>", $q);                 // LIMIT
                $q = str_ireplace('OFFSET', "<b>OFFSET</b>", $q);               // OFFSET
                echo "<br /><hr /><br /><div style=\"display:block;\"><h1>Query Debug</h1><br /><pre style=\"backgroud-color:gray;font-family:monospace;\">{$q}</pre></div>";
                die();
            } elseif ($this->_get_query) {
                return $q;
            } else {
                if ($this->_result = @mysql_query($q)) {
                    $this->_query_status = true;
                } else {
                    $this->_doError($q);
                }
            }
        }
        return $this;
    }

    public function __invoke($q) {
        return $this->query($q);
    }

    /**
     * Insert values.
     *
     * @param string $table Table name.
     * @param array $arr_values Values.
     * @param boolean $escape_text Optional. Default: true. Escape all values.
     * @return boolean TRUE if query executed.
     * @see Cnx::getStatus()
     */
    public function insert($table, array $arr_values, $escape_text = true) {
        $fields = '';
        $values = '';
        foreach($arr_values as $field => $value) {
            if (notEmptyCheck($fields)) {
                $fields .= ',';
                $values .= ',';
            }
            $fields .= self::__columnEscape($field);
            $values .= $escape_text ? $this->escape($value, true) : $value;
        }

        //Consulta
        $this->query("INSERT INTO `{$table}` ({$fields}) VALUES ({$values})");
        return $this->getStatus();
    }

    /**
     * Update values.
     *
     * @param string $table Table name.
     * @param string|array $condition Conditions to update (WHERE).
     * @param array $arr_values Values.
     * @param boolean $escape_text Optional. Default: true. Escape all values.
     * @return boolean TRUE if query executed.
     * @see Cnx::getStatus()
     */
    public function update($table, $condition, array $arr_values, $escape_text = true) {
        $updates = '';
        foreach($arr_values as $field => $value) {
            if (notEmptyCheck($updates)) $updates .= ',';
            if ($escape_text) $value = $this->escape($value, true);
            $updates .= self::__columnEscape($field) . ' = ' . $value;
        }
        if (is_array($condition)) $condition = self::Where($condition);
        $this->query("UPDATE `{$table}` SET {$updates} WHERE {$condition}");
        return $this->getStatus();
    }

    /**
     * Replace values.
     *
     * @param string $table Table name.
     * @param array $arr_values Values.
     * @param boolean $escape_text Optional. Default: true. Escape all values.
     * @return boolean TRUE if query executed.
     * @see Cnx::getStatus()
     */
    public function replace($table, array $arr_values, $escape_text = true) {
        $fields = '';
        $values = '';
        foreach($arr_values as $field => $value) {
            if (notEmptyCheck($fields)) {
                $fields .= ',';
                $values .= ',';
            }
            $fields .= self::__columnEscape($field);
            $values .= $escape_text ? $this->escape($value, true) : $value;
        }

        //Consulta
        $this->query("REPLACE INTO `{$table}` ({$fields}) VALUES ({$values})");
        return $this->getStatus();
    }

    /**
     * Delete row/s.
     *
     * @param string $table Table from delete.
     * @param array|string $field_where Field for WHERE.
     * @param string $value Optional. Value for WHERE.
     */
    public function delete($table, $field_where, $value = null, $escape_text = true) {
        if (is_array($field_where)) {
            $where = self::Where($field_where);
        } else {
            if ($escape_text) $value = $this->escape($value, true);
            $field_where = self::__columnEscape($field_where);
            $where       = "{$field_where} = '{$value}'";
        }
        $this->query("DELETE FROM `{$table}` WHERE {$where}");
    }

    /**
     * Returns last inserted ID.
     *
     * @return integer Last inserted ID.
     */
    function lastInsertedID() {
        return mysql_insert_id($this->_cnx);
    }

    /**
     * Generate search string.
     *
     * @param array|string $field Field or array of fields.
     * @param string $value Value to search.
     * @return string Search string.
     */
    public static function SearchString($field, $value) {
        if (is_array($field)) {
            $q = '';
            foreach ($field as $fld) {
                if (notEmptyCheck($q)) $q .= ' OR ';
                $q .= '(' . self::SearchString($fld, $value) . ')';
            }
            return "($q)";
        } else {
            $q = '';
            $rr = preg_replace("(\/|\ |\.|\,|:|;|\<|\>|\[|\]|\{|\}|'|\"|\(|\)|\n|\r|\t|\"|\*|\\|\$|\^|\?)", ' ', $value);
            $rr = str_replace("  ", " ", $rr);
            $ab = array_filter(array_unique(explode(' ', $value)), 'notEmptyCheck');
            if (count($ab) > 0) {
                foreach ($ab as $t) {
                    if (notEmptyCheck($q)) $q .= ' AND ';
                    //                     $q .= 'LOWER(' . self::__columnEscape($field) . ') REGEXP LOWER(\'' . self::_searchStringStr($t) . '\')';
                    $q .= 'LOWER(' . self::__columnEscape($field) . ') LIKE "%' . strtolower(self::GetInstance()->escape($t)) . '%"';
                }
            }
            return $q;
        }
    }

    /**
     * Fiend exists in table.
     *
     * @param string $field Field to check if exists.
     * @param string $table Table where check if field exists.
     * @return boolean TRUE if field exists in table.
     */
    public function fieldExist($field, $table) {
        $q = "SHOW COLUMNS FROM `$table` WHERE FIELD = '$field'";
        $q = $this->query($q);
        return ($this->count($q) == 0) ? false : true;
    }

    /**
     * Count rows.
     * If no use parameters, returns number of last executed query rows.
     *
     * @param string $q_table Query result or table.
     * @param string|array $wfield If String, used as Where field; if Array, used as Where query.
     * @param string $wvalue Where value (used ontly if $wfield is a String.
     * @param string $count_field Default: '*'. Field to count.
     * @return integer Number of rows.
     */
    public function count($q_table = null, $wfield = null, $wvalue = null, $count_field = null) {
        if (emptyCheck($wfield) && emptyCheck($wvalue)) {
            $q = is_null($q_table) ? $this->_result : $q_table;
            if (@mysql_num_rows($q) < 1) {
                return 0;
            } else {
                return @mysql_num_rows($q);
            }
        } else {
            if (is_array($wfield)) {
                $where = self::Where($wfield);
            } else {
                $wvalue = $this->escape($wvalue, true);
                $where = "`{$wfield}` = {$wvalue}";
            }
            $count_field = is_null($count_field) ? '*' : self::__columnEscape($count_field);
            $q = $this->query("SELECT IF( COUNT({$count_field}) < 1, 0, COUNT({$count_field})) AS c FROM `{$q_table}` WHERE {$where}");
            return $this->result('c');
        }
    }

    /**
     * Returns last executed query result.
     *
     * @param string $q Default: NULL. Field in first row.
     *     If STRING returns field in first row.
     *     If NULL returns all rows.
     *     If TRUE returns first row as Array.
     * @return array
     */
    public function result($q = null) {
        if (is_null($q)) {
            if ($this->count() > 0) {
                $rows = array();
                while ($r = mysql_fetch_array($this->_result))
                    $rows[] = $r;
                return $rows;
            } else {
                // No results
                return array();
            }
        } else {
            if ($first_result = mysql_fetch_array($this->_result)) {
                if ($q === true) {
                    // Return row
                    return $first_result;
                } else {
                    // Return value
                    return $first_result[$q];
                }
            } else {
                // No results
                return null;
            }
        }
    }

    /**
     * MySQL error mesage.
     *
     * @return string
     */
    public function getErrorMessage() {
        return $this->_error;
    }

    /**
     * MySQL error code.
     *
     * @return integer
     */
    public function getErrorCode() {
        return $this->_error_code;
    }

    /**
     * Query executed?
     * TRUE: Query executed.
     * FALSE: Error or not executed.
     *
     * @return boolean
     */
    public function getStatus() {
        return $this->_query_status;
    }

    /**
     * Create DB Backup.
     *
     * @param string $file File where save.
     * @param string $db DB to save. If not setted, use current DB.
     */
    public function backUp($file, $db = null){
        $db = emptyCheck($db) ? $this->_cnx_db : $db;
        $pass = $this->_cnx_pass;
        $user =  $this->_cnx_user;
        exec("mysqldump --opt --user=$user --password=$pass $db > $file");
    }

    /**
     * Generate Order for query.
     *
     * @param array|string $order Order.
     *     Example:
     *         'field1 ASC, field2 DESC'
     *         array('field0', 'field1' => 'ASC', 'field2' => 'DESC')
     * @return string
     */
    public static function Order($order) {
        $ord = '';
        if (!is_null($order)) {
            if (is_array($order)) {
                $ord = 'ORDER BY ';
                foreach ($order as $field => $sort) {
                    if ($ord != 'ORDER BY ') {
                        $ord .= ',';
                    }
                    if (is_numeric($field)) {
                        $field = $sort;
                        $sort = 'ASC';
                    } else {
                        $sort = strtoupper($sort);
                        if (($sort != 'ASC') && ($sort != 'DESC')) {
                            $sort = 'ASC';
                        }
                    }
                    $field = self::__columnEscape($field);
                    $ord .= "$field $sort";
                }
            } else {
                $ord = "ORDER BY $order";
            }
        }
        return $ord;
    }

    /**
     * Generate Limit for query.
     *
     * @param integer $limit Limit.
     *     Example:
     *         10
     *         array(10, 5)
     *         '10,5'
     * @param integer $offset Offset.
     * @return string
     */
    public static function Limit($limit, $offset = null) {
        $lmt = '';
        if (!is_null($limit)) {
            $lmt = "LIMIT $limit";
            if (!is_null($offset)) {
                $lmt .= " OFFSET $offset";
            }
        }
        return $lmt;
    }

    /**
     * Generate Where for query.
     *
     * @param array|string $where Where.
     *     array(
     *         'custom1 = custom2', // Extra
     *         'field1' => '111', // =
     *         'field2 <' => 222, // <
     *         'field3 LIKE' => 'asd', // LIKE
     *         'field4' => array(1, 2, 3, 4, 'asd'), // IN
     *         'field5 NOT' => array(1, 2, 3, 4, 'asd'), // NOT IN
     *         'OR' => array(
     *             'fieldX' => '1000', // Repeat `fieldX` == "fieldX = 1000"
     *             'fieldX#2' => '11', // Repeat `fieldX` == "fieldX = 0"
     *             'fieldX#55' => '13' // Repeat `fieldX` == "fieldX = 13"
     *         ),
     *         'AND' => array( ... )
     *     )
     * @param string $cond Condition (AND | OR).
     * @throws Exception Invalid field.
     * @return string
     */
    public static function Where($where = '1 = 1', $cond = 'AND') {
        if (is_array($where)) {
            if (count($where) == 0) return '1 = 1';
            $w = '';
            foreach ($where as $field => $value) {
                if (notEmptyCheck($w)) {
                    $w .= " $cond ";
                }
                if (is_integer($field)) {
                    $w .= self::Where($value);
                } else {
                    $f = strtoupper($field);
                    if ((($f == 'OR') || ($f == 'AND')) && is_array($value)) {
                        $w .= '(' . self::Where($value, $f) . ')';
                    } else {
                        $p = explode(' ', $field);
                        if (count($p) == 1) {
                            $fld = $field;
                            $cnd = is_array($value) ? '' : '=';
                        } elseif (count($p) == 2) {
                            list($fld, $cnd) = $p;
                            $cnd = strtoupper($cnd);
                        } else {
                            // Error!
                            throw new AppException("Invalid field! [$field]");
                        }
                        if (is_array($value)) {
                            $val = '';
                            foreach ($value as $v) {
                                if (notEmptyCheck($val)) {
                                    $val .= ',';
                                }
                                $val .= self::GetInstance()->escape($v, true);
                            }
                            $val = "IN ($val)";
                        } elseif ($value instanceof CnxQ) {
                            $val = $value->toString();
                        } else {
                            $val = self::GetInstance()->escape($value, true);
                        }
                        $fld = self::__columnEscape(preg_replace('/' . self::UNIQUE_COLUMN_CHAR . '[0-9]*$/', '', $fld));
                        $w .= "$fld $cnd $val";
                    }
                }
            }
            return $w;
        } else {
            return $where;
        }
    }

    /**
     * Create MySQL query.
     *
     * @param array $query
     *     array(
     *         'SELECT' => array(
     *             'DISTINCT',
     *             'field1',
     *             'field2' => 'alias1',
     *              '[columns_to_group]' => 'alias2', // "GROUP_CONCAT(columns_to_group SEPARATOR ',')  AS alias2"
     *              '[columns_to_group] | ' => 'alias3' // "GROUP_CONCAT(columns_to_group SEPARATOR ' | ')  AS alias3"
     *         )
     *         'SELECT' => 'field1',
     *
     *         'FROM' => 'table',
     *         'FROM' => array(
     *             'table1',
     *             'table2', // Separated by comma
     *             'table3' => array(
     *                 'INNER',
     *                 ... // Cnx::Where format
     *              ),
     *              'table4' => ... // Cnx::Where format
     *         )
     *
     *         'WHERE' => ... // Cnx::Where format
     *
     *         'ORDER' => ... // Cnx::Order format
     *
     *         'GROUP' => 'field1',
     *         'GROUP' => array('field1', 'field2'),
     *
     *         'LIMIT' => 10 // LIMIT
     *         'LIMIT' => array(10, 5) // LIMIT, OFFSET
     *         'LIMIT' => '10,5' // LIMIT, OFFSET
     *     );
     * @return string MySQL query.
     */
    public static function CreateQuery(array $query) {
        $query = array_change_key_case($query, CASE_UPPER);
        $table = is_array($query['FROM']) ? "`{$query['FROM'][0]}`" : "`{$query['FROM']}`";

        // SELECT
        $q = 'SELECT ';
        if (isset($query['SELECT'])) {
            if (is_array($query['SELECT'])) {
                if (isset($query['SELECT'][0]) && (strtoupper($query['SELECT'][0]) === 'DISTINCT')) {
                    // Delete first element
                    array_shift($query['SELECT'][0]);
                    $q .= 'DISTINCT ';
                }
                $first = true;
                foreach ($query['SELECT'] as $field => $alias) {
                    if ($first) {
                        $first = false;
                    } else {
                        $q .= ', ';
                    }
                    if (is_numeric($field)) {
                        $q .= self::__columnEscape($alias);
                    } else {
                        if (preg_match('/^\[.+\].*$/', $field)) {
                            $parts = explode(']', $field, 2);
                            if (count($parts) > 1) {
                                list($field, $separator) = $parts;
                                if ($separator == '') {
                                    $separator = ',';
                                } else {
                                    $separator = str_replace("'", "\\'", $separator);
                                }
                            } else {
                                list($field) = $parts;
                                $separator = ',';
                            }
                            $field = self::__columnEscape(substr($field, 1));
                            $q .= "GROUP_CONCAT({$field} SEPARATOR '{$separator}')  AS {$alias}";
                        } else {
                            $q .= self::__columnEscape($field) . ' AS ' . $alias;
                        }
                    }
                }
            } else {
                $q .= self::__columnEscape($query['SELECT']);
            }
        } else {
            $q .= "{$table}.*";
        }

        // FROM
        $q .= ' FROM';
        if (!isset($query['FROM'])) {
            throw new AppException('Query [FROM] is required!');
        }
        if (is_array($query['FROM'])) {
            $first = true;
            foreach ($query['FROM'] as $table => $join) {
                $q .= ' ';
                if (is_numeric($table)) {
                    if (!$first) {
                        $q .= ', ';
                    }
                    $q .= self::__columnEscape($join);
                } else {
                    if (is_array($join)) {
                        if (count($join) != 2) {
                            throw new AppException('Query [FROM JOIN] should be an array with 2 elements');
                        }
                        $q .= strtoupper($join[0]) . ' JOIN ' . self::__columnEscape($table) . ' ON (' . self::Where($join[1]) . ')';
                    } else {
                        $q .= 'INNER JOIN ' . self::__columnEscape($table) . ' ON (' . self::Where($join) . ')';
                    }
                }
                $first = false;
            }
        } else {
            $q .= self::__columnEscape($query['FROM']);
        }
        $q .= ' ';

        // WHERE
        if (isset($query['WHERE'])) {
            $q .= 'WHERE ' . self::Where($query['WHERE']) . ' ';
        }

        // GROUP BY
        if (isset($query['GROUP'])) {
            $q .= ' GROUP BY ';
            if (is_array($query['GROUP'])) {
                $first = true;
                foreach ($query['GROUP'] as $order) {
                    if (!$first) {
                        $q .= ', ';
                    }
                    $first = false;
                    $q .= self::__columnEscape($order);
                }
            } else {
                $q .= self::__columnEscape($query['GROUP']);
            }
            $q .= ' ';
        }

        // ORDER BY
        if (isset($query['ORDER'])) {
            $q .= self::Order($query['ORDER']) . ' ';
        }

        // LIMIT
        if (isset($query['LIMIT'])) {
            if (is_array($query['LIMIT'])) {
                if (count($query['LIMIT']) > 0) {
                    if (count($query['LIMIT']) == 1) {
                        $q .= self::Limit($query['LIMIT'][0]);
                    } else {
                        $q .= self::Limit($query['LIMIT'][0], $query['LIMIT'][1]);
                    }
                }
            } else {
                if (strpos($query['LIMIT'], ',') === false) {
                    list($limit, $offset) = explode(',', $query['LIMIT']);
                    $q .= self::Limit(trim($limit), trim($offset));
                } else {
                    $q .= self::Limit($query['LIMIT']);
                }
            }
        }

        // Debug
        if (in_array(self::DEBUG_TEXT, $query) || in_array(self::DEBUG_KEY, $query)) {
            $q = self::DEBUG_TEXT . $q;
        }

        return $q;
    }

    /**
     * Put `` to column name.
     *
     * @param string|CnxQ $table_column Table column name.
     * @param boolean $validate Validate column name?
     * @return string
     */
    public static function __columnEscape($table_column, $validate = false) {
        if ($table_column instanceof CnxQ) {
            return $table_column->toString();
        } else {
            $table_column = trim($table_column);
            if ($validate && !preg_match('/^[a-zA-Z0-9\`\_\.]+$/', $table_column)) {
                throw new AppException('Invalid column name');
            }
            if (preg_match('/[\`\(\+\-\ \"\')]/', $table_column)) {
                return $table_column;
            } else {
                if (stripos($table_column, ' as ') === false) {
                    $alias = '';
                } else {
                    list($table_column, $alias) = preg_split('/\s[aA][sS]\s/', $table_column, 2);
                    $table_column = trim($table_column);
                    $alias        = ' AS `' . trim(trim(trim($alias), '`')) . '`';
                }
                return '`' . str_replace('.', '`.`', $table_column) . '`' . $alias;
            }
        }
    }

    protected static function _searchStringStr($str) {
        $str = trim(strtolower($str));
        if (strlen($str) > 0) {
            $tmp = '';
            for ($i = 0; $i < strlen($str); $i++) $tmp .= '(' . $str[$i] . '){1,2}';
            $str = $tmp;
        }
        $from = array(
            '/[aàáäAÀÁÄ]/',
            '/[eèéëEÈÉË]/',
            '/[iìíïIÌÍÏ]/',
            '/[oöòóOÖÒÓ]/',
            '/[uüùúUÜÙÚ]/',
            '/[ñÑ]/',
            '/[çÇ]/'
        );
        $to = array(
            ('[aàáäAÀÁÄ]'),
            ('[eèéëEÈÉË]'),
            ('[iìíïIÌÍÏ]'),
            ('[oöòóOÖÒÓ]'),
            ('[uüùúUÜÙÚ]'),
            ('[ñÑ]|ni'),
            ('[çÇ]')
        );
        $str = preg_replace($from, $to, $str);
        return $str;
    }

    protected function _doError($q) {
        $this->_query_status = false;
        $this->_error        = mysql_error();
        $this->_error_code   = mysql_errno();

        switch ($this->_error_code) {
            case self::MYSQL_ERROR_CODE_DUPLICATE_ENTRY:
                // Duplicate entry
                break;
            default:
                // Other error
                if ($this->_debug) {
                    echo '<pre>';
                    echo "\n\nError ({$this->_error_code}):\n\t{$this->_error}\n\nQuery:\n\t{$q}\n\nDebug:\n\n";
                    debug_print_backtrace();
                    echo '</pre>';
                    die();
                } else {
                    ob_start();
                    debug_print_backtrace();
                    $trace = ob_get_contents();
                    ob_end_clean();
                    APP::GetInstance()->errorPage500(
                            'Error! Se ha producido un error al realizar la operacion solicitada.' .
                            '<br />Es posible que <u>NO</u> se hayan guardado los cambios realizados.' .
                            '<br />Antes de contunuar, por favor, <u>de aviso</u> a los programadores sobre este problema.' .
                            "<br /><br /><b>Trace</b><br /><pre>$trace</pre>"
                    );
                }
        }
    }
}