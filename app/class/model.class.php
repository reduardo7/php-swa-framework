<?php

/**
 * DB Model.
 *
 * @license Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @author Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @version 2.0
 * @package ar.com.eduardocuomo.class.model
 * @copyright Eduardo Daniel Cuomo
 */
abstract class ModelBase extends APP_Base {

    /*** START: MUST BE REDECLARED BY EXTENDING ***/

    /**
     * Model Class.
     *
     * @var string
     */
    protected $_clazz = __CLASS__;

    /**
     * Model title.
     * Redeclare with Model Title.
     *
     * @var string
     */
    const TITLE = '--- NO TITLE ---';

    /**
     * Table name.
     * Redeclare with Model Table Name.
     *
     * @var string
     */
    const TABLE = '--- NO TABLE ---';

    /**
     * Table columns.
     * Redeclare with columns.
     *
     *
     * Syntax:
     * aray(
     *     'db_column_name_1' => [PROPERTIE],
     *     'db_column_name_2' => array([PROPERTIE 1], [PROPERTIE 2] => [PROPERTIE 3]),
     *     ...
     * )
     *
     * PROPERTIES:
     *     string:
     *         id: [BIGINT(20)] ID Number.
     *             ID field.
     *         email: [VARCHAR(255)] Type e-Mail.
     *             e-Mail string.
     *         boolean: [TINYINT(1)] Type boolean.
     *             Boolean value.
     *         url: [VARCHAR(255)] Type URL.
     *             URL string.
     *         string: [VARCHAR(255)] String.
     *             Short string.
     *         text: [TEXT] Text.
     *             Long text.
     *         integer: [NUMERIC] Integer.
     *             Integer number.
     *         decimal: [DECIMAL] Decimal.
     *             Decimal number.
     *         date: [DATE|DATETIME] Date (yyyy-mm-dd).
     *             Date.
     *         ip: [VARCHAR] String.
     *             IP string.
     *         readonly:
     *             Read only field.
     *     array:
     *         enum => array('Text with value = 0', 1 => 'Text 1', 'val2' => 'Text 2')
     *             Select with options.
     *         string => 255
     *             [VARCHAR(255)] Input text.
     *         render => true|false
     *             Render field in auto-generated form?
     *         model => [Model Name]
     *             Model name.
     *         model-alias => [Class variable name]
     *             Variable name where put an instance of related model.
     *         title => {string}
     *             Column title (for list) and column label (for forms).
     *         label => {string}
     *             Column label (for forms), and not show as column in list.
     *         empty => true|false // Boolean
     *             Can be empty? Default: false.
     *
     *
     * Example:
     * array(
     *     'id'        => 'id',
     *     'nombre'    => array('string' => 255, 'title' => 'Nombre'),
     *     'email'     => array('email', 'label' => 'e-Mail', 'empty' => true),
     *     'id_imagen' => array('model' => 'Imagenes', 'model-alias' => 'imagen', 'render' => false),
     *     'url'       => array('url', 'title' => 'URL'),
     *     'activo'    => 'boolean',
     *     'tipo'      => array('title' => 'Tipo', 'enum' => array(
     *         1 => 'Super-Admin',
     *         2 => 'Admin',
     *         3 => 'User'
     *     ))
     * )
     *
     * @var array
     */
    protected $_columns = array();

    /**
     * ID field name.
     * Redeclare if necessary with primary field.
     *
     * @var string|array
     */
    protected $_id_field = 'id';

    /**
     * Join tables.
     *
     * Example:
     * array(
     *     'Table1' => array(
     *         'on' => 'TableName.id = TableNameX.id',
     *         'mode' => 'inner', // Optional
     *         'select' => array(
     *             'field1' => 'alias1',
     *             'field2' => 'alias2'
     *         )
     *     ),
     *     'Table2' => array...
     * )
     *
     * @var array
     */
    protected $_joins = array();

    /*** END: MUST BE REDECLARED BY EXTENDING ***/

    /**
     * Record data.
     *
     * @var array
     */
    protected $_record = array();

    /**
     * DB connection.
     *
     * @var Cnx
     */
    protected $_cnx;

    /**
     * Table name.
     *
     * @var string
     */
    protected $_table = '--- NO TABLE ---';

    /**
     * Table visible columns.
     * This array is fill with $this->_columns
     *
     * @var array
     */
    protected static $_columns_visibles = array();

    /**
     * Used to prevent infinite loop on save record data.
     *
     * @var string
     */
    private $_last_data_checksum = null;

    /**
     * Print Query String.
     *
     * @var boolean
     */
    private $_debug_query_string = false;

    /**
     * Create new record.
     *
     * @param array|integer $data_id Record data or record ID.
     */
    function __construct($data_id = null) {
        parent::__construct();
        $this->_table = $this->getTable();

        // Start connection
        $this->_cnx = new Cnx();

        if ($data_id instanceof self) {
            // Is model
            $this->setData($data_id->getData());
        } elseif (is_array($data_id)) {
            // Set record data
            $this->setData($data_id);
        } elseif (is_numeric($data_id)) {
            // Load record by ID
            $this->findById($data_id);
        }
    }

    /**
     * Execute extra operations on data load.
     * Redeclare if necessary with initial funcion to execute
     * and re-call it if necessary (parent::onBeforeLoad()).
     */
    public function onBeforeLoad() {
        $columns = $this->getColumns(true);
        foreach ($columns as $col => $attrs) {
            if (is_array($attrs)) {
                if (array_key_exists('model', $attrs)) {
                    // Is model column
                    if (array_key_exists('model-alias', $attrs)) {
                        // Load model alias
                        $this->{$attrs['model-alias']} = $this->_loadModel($attrs['model'], $col);
                    }
                }
            }
        }
    }

    /**
     * To string.
     * Redeclare with toString method, to render model as string.
     *
     * @return string String representation.
     */
    public function toString() {
        return $this->_table . '{' . $this->id . '}';
    }

    /**
     * Executed before save data.
     * Redeclare if necessary with on save actions.
     */
    public function onBeforeSave() { }

    /**
     * Set form fields.
     * Redeclare if necessary with form fields to use
     * and re-call it if necessary (parent::SetFormFields($frm)).
     *
     * @param Form $frm Form to set fields.
     */
    public function setFormFields(Form $frm) {
        foreach ($this->getColumns() as $col => $props) {
            if ($props != 'id') {
                $label = $this->getColumnLabel($col);

                if (!is_array($props)) {
                    // String -> Array
                    $props = array($props);
                }

                if (!isset($props['render']) || $props['render']) {
                    $empty = isset($props['empty']) && $props['empty'];
                    $readonly = in_array('readonly', $props, true) ? array('readonly' => true, 'disabled' => true) : array();

                    if (self::_isArrayKeyInt('boolean', $props)) {
                        // Boolean
                        $frm->addSelect($col, $label, array(
                            0 => 'No',
                            1 => 'Si'
                        ), $readonly);
                    } elseif (self::_isArrayKeyInt('text', $props)) {
                        // TextArea
                        $frm->addTextarea($col, $label, $readonly, array(
                            'empty' => $empty
                        ));
                    } elseif (self::_isArrayKeyInt('email', $props)) {
                        $frm->addInput($col, $label, 'email',
                            array_merge(
                                $readonly,
                                array(
                                    'placeholder' => 'user@server.com',
                                    'maxlength' => 255
                                )
                            ),
                            array(
                                'empty' => $empty,
                                'max' => 255,
                                'email' => true
                            ));
                    } elseif (self::_isArrayKeyInt('url', $props)) {
                        $frm->addInput($col, $label, 'url',
                            array_merge(
                                $readonly,
                                array(
                                    'placeholder' => 'http://',
                                    'maxlength' => 255
                                )
                            ),
                            array(
                                'empty' => $empty,
                                'max' => 255,
                                'url' => true
                            ));
                    } elseif (self::_isArrayKeyInt('integer', $props)) {
                        $frm->addInput($col, $label, 'number',
                            array_merge(
                                $readonly,
                                array(
                                    'placeholder' => '0',
                                    'maxlength' => 255,
                                    'step' => 1
                                )
                            ),
                            array(
                                'empty' => $empty,
                                'integer' => true
                            ));
                    } elseif (self::_isArrayKeyInt('decimal', $props)) {
                        $frm->addInput($col, $label, 'number',
                            array_merge(
                                $readonly,
                                array(
                                    'placeholder' => '0.0',
                                    'maxlength' => 255
                                )
                            ),
                            array(
                                'empty' => $empty,
                                'decimal' => true
                            ));
                    } elseif (self::_isArrayKeyInt('date', $props)) {
                        $frm->addInput($col, $label, 'date',
                            array_merge(
                                $readonly,
                                array(
                                    'readonly' => true
                                )
                            ),
                            array(
                                'date-format' => 'y-m-d',
                                'date' => true,
                                'empty' => $empty
                            ))
                            ->addHtml(
                                $col . '_datepicker_js',
                                "<script>$(function(){\$('#{$col}').datepicker({dateFormat:'yy-mm-dd'});});</script>"
                            );
                    } elseif (self::_isArrayKeyInt('ip', $props)) {
                        $frm->addInput($col, $label, 'text',
                            array_merge(
                                $readonly,
                                array(
                                    'placeholder' => '0.0.0.0',
                                    'maxlength' => 15
                                )
                            ),
                            array(
                                'empty' => $empty,
                                'ip' => true
                            ));
                    } elseif (isset($props['enum'])) {
                        $frm->addSelect(array(
                            'name'        => $col,
                            'label'       => $label,
                            'validations' => array('empty' => $empty),
                            'options'     => $props['enum'],
                            'attrs'       => $readonly
                        ));
                    } elseif (isset($props['model'])) {
                        $v = $props['model'];
                        $this->APP->model($v);
                        $model = 'Model' . $v;
                        $modelInstance = Utils::NewInstanceOf($model);

                        $options = array('' => '...'); // First option
                        foreach ($modelInstance->findAll() as $reg) {
                            $options[$reg->id] = $reg->toString();
                        }

                        $frm->addSelect(
                            $col,
                            emptyCheck($label) ? $modelInstance->getTitle() : $label,
                            $options,
                            null,
                            $readonly,
                            array('empty' => $empty)
                        );
                    } elseif (isset($props['string'])) {
                        // Text input
                        self::_formFieldSimpleInput($frm, $label, $col, $empty, $readonly, $props['string']);
                    } else {
                        // Text input
                        self::_formFieldSimpleInput($frm, $label, $col, $empty, $readonly);
                    }
                }
            }
        }
    }

    /**
     * Array of models instances.
     *
     * @var array Array of models instances.
     */
    private static $_instance = array();

    /**
     * Get an instance of current Model.
     *
     * @return ModelBase Current model instance.
     */
    final public static function GetInstance() {
        $clazz = get_called_class();
        if (!isset(self::$_instance[$clazz])) {
            self::$_instance[$clazz] = Utils::NewInstanceOf($clazz);
        }
        return self::$_instance[$clazz];
    }

    /**
     * Get record ID.
     *
     * @return integer|array|null NULL if is not a record.
     */
    final public function getId() {
        if ($this->isRecord()) {
            if (is_array($this->_id_field)) {
                // Multi-Key
                $ids = array();
                foreach ($this->_id_field as $field) {
                    $ids[$field] = $this->_record[$field];
                }
                return $ids;
            } else {
                // Single-Key
                return $this->_record[$this->_id_field];
            }
        } else {
            return null;
        }
    }

    /**
     * Get query on run "query" method?
     *
     * @param boolean $enable Enable?
     * @return ModelBase This instence.
     *
     * @see Cnx::getQueryString
     */
    final public function debugQueryString($enable = true) {
        $this->_cnx->getQueryString($enable);
        $this->_debug_query_string = $enable;
        return $this;
    }

    /**
     * Returns TRUE if field value is empty.
     *
     * @param string $field_name Field name.
     * @return boolean TRUE if field is empty.
     */
    final public function isEmpty($field_name) {
        if ($this->isDate($field_name)) {
            return $this->get($field_name) == '0000-00-00';
        } elseif ($this->isBoolean($field_name)) {
            return true;
        } elseif ($this->isBoolean($field_name)) {
            return true;
        } else {
            return emptyCheck($this->get($field_name));
        }
    }

    /*** FINAL METHODS ***/

    /**
     * Get class name.
     *
     * @return string Class name.
     */
    final public function getClass() {
        return get_class($this);
    }

    /**
     * Get table name.
     *
     * @return string Table name.
     */
    final public function getTable() {
        return $this->_getStaticValue('TABLE');
    }

    /**
     * Get table title.
     *
     * @return string Table title.
     */
    final public function getTitle() {
        return $this->_getStaticValue('TITLE');
    }

    /**
     * Use: $model('field_name');
     *
     * @param string $field_name Field name.
     * @return null|string|integer
     */
    final function __invoke($field_name) {
        return $this->get($field_name);
    }

    final function __toString() {
        return $this->toString();
    }

    /**
     * Returns record data.
     *
     * @return array
     * @see ModelBase::toArray()
     */
    final public function getData() {
        return $this->_record;
    }

    /**
     * Returns record data.
     *
     * @return array
     * @see ModelBase::getData()
     */
    final public function toArray() {
        return $this->getData();
    }

    /**
     * Returns TRUE if is a record in DB with ID.
     * Returns TRUE if data contains a valid ID.
     *
     * @return boolean
     */
    final public function isRecord() {
        if (is_array($this->_id_field)) {
            // Multi-Key
            $has_id = true;
            foreach ($this->_id_field as $field) {
                if (!isset($this->_record[$field]) || emptyCheck($this->_record[$field])) {
                    $has_id = false;
                    break;
                }
            }
        } else {
            // Single-Key
            $has_id = (isset($this->_record[$this->_id_field]) && notEmptyCheck($this->_record[$this->_id_field]));
        }

        if ($has_id) {
            // The ID is setted
            if ($this->_table == self::TABLE) {
                // Is a record
                return true;
            } else {
                // Is a Model
                if (is_array($this->_id_field)) {
                    // Multi-Key
                    $where = array();
                    foreach ($this->_id_field as $field) {
                        $where[$field] = $this->_record[$field];
                    }
                    return $this->_cnx->count($this->_table, $where, $this->_id_field[0]) > 0;
                } else {
                    // Single-Key
                    return $this->_cnx->count($this->_table, $this->_id_field, $this->_record[$this->_id_field], $this->_id_field) > 0;
                }
            }
        } else {
            // Not is a record, no have a ID
            return false;
        }
    }

    /**
     * Returns TRUE if this object contains record data.
     *
     * @return boolean
     */
    final public function hasData() {
        return count($this->_record) > 0;
    }

    /**
     * Returns TRUE if field is setted.
     *
     * @param string $field_name Field name.
     * @param boolean $empty_valid Valid empty valie?
     * @return boolean TRUE if field is setted.
     */
    final public function isSetted($field_name, $empty_valid = true) {
        return isset($this->_record[$field_name]) && ($empty_valid || notEmptyCheck($this->_record[$field_name]));
    }

    final public function __isset($field_name) {
        return $this->isSetted($field_name);
    }

    /**
     * Get record field value from model type.
     *
     * @param string $field_name Field name.
     * @return null|string|integer|boolean
     */
    final public function get($field_name) {
        $columns = $this->getColumns();
        if (isset($columns[$field_name])) {
            $attrs = $columns[$field_name];
            if (is_array($attrs)) {
                if (isset($attrs['enum'])) {
                    return $this->getAsEnum($field_name);
                } else {
                    foreach ($attrs as $key => $value) {
                        if (is_numeric($key)) {
                            switch ($value) {
                                case 'boolean': return $this->getAsBoolean($field_name);
                                case 'date': return $this->getAsDate($field_name);
                            }
                        }
                    }
                }
            }
        }
        return $this->getValue($field_name);
    }

    final function __get($field_name) {
        return $this->get($field_name);
    }

    /**
     * Returns TRUE if field is Boolean.
     *
     * @param string $field_name Filed name.
     * @return boolean
     */
    final public function isBoolean($field_name) {
        return $this->_checkType($field_name, 'boolean');
    }

    /**
     * Returns TRUE if is Date.
     *
     * @param string $field_name Field name.
     * @return boolean
     */
    final public function isDate($field_name) {
        return $this->_checkType($field_name, 'date');
    }

    /**
     * Returns TRUE if is Integer.
     *
     * @param string $field_name Field name.
     * @return boolean
     */
    final public function isInteger($field_name) {
        return $this->_checkType($field_name, 'integer');
    }

    /**
     * Returns TRUE if is String.
     *
     * @param string $field_name Field name.
     * @return boolean
     */
    final public function isString($field_name) {
        return $this->_checkType($field_name, 'string');
    }

    /**
     * Returns TRUE if is Text.
     *
     * @param string $field_name Field name.
     * @return boolean
     */
    final public function isText($field_name) {
        return $this->_checkType($field_name, 'text');
    }

    /**
     * Returns TRUE if is Enum.
     *
     * @param string $field_name Field name.
     * @return boolean
     */
    final public function isEnum($field_name) {
        $columns = $this->getColumns();
        if (isset($columns[$field_name])) {
            $attrs = $columns[$field_name];
            if (is_array($attrs) && isset($attrs['enum'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get record field value.
     *
     * @param string $field_name Field name.
     * @return null|string|integer
     */
    final public function getValue($field_name) {
        return $this->isSetted($field_name) ? $this->_record[$field_name] : null;
    }

    /**
     * Returns field as date. Returns NULL if not a valid date.
     *
     * @param string $field_name Field name.
     * @param string $format Date format for result.
     * @return string|NULL
     */
    final public function getAsDate($field_name, $format = null) {
        $d = $this->getValue($field_name);
        if (preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}\:\d{2}:\d{2})?$/', $d)) {
            // 2013-03-17 16:03:15 | 2013-03-17
            if (is_null($format)) {
                // Return as Date
                return date($d);
            } else {
                // Return as String with format
                return date($format, strtotime($d));
            }
        } else {
            // Invalid date format
            return null;
        }
    }

    /**
     * Returns field as boolean.
     * Returns TRUE if value is:
     *     true'v', 'V', 'y', 'Y',
     *     's', 'S', 1, '1', 't', 'T',
     *     'true', 'TRUE', 'verdadero', 'VERDADERO'
     * Else values are FALSE.
     *
     * @param string $field_name Field name.
     * @return boolean
     */
    final public function getAsBoolean($field_name) {
        $d = $this->getValue($field_name);
        if (in_array(strtolower($d), array(1, '1', 'y', 's', 't', 'true', 'v', 'verdadero', true), true)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return Enum value of field.
     *
     * @param string $field_name Field name.
     * @return string|null Return Enum value or NULL if not exists.
     */
    final public function getAsEnum($field_name) {
        $c = $this->getColumns();
        $a = $c[$field_name];
        $v = $this->getValue($field_name);
        return (notEmptyCheck($v) && isset($a['enum'][$v])) ? $a['enum'][$v] : null;
    }

    /**
     * Set record field value.
     *
     * @param string $field_name Field name.
     * @param integer|string $value Value to set.
     * @return ModelBase
     */
    final public function set($field_name, $value) {
        $this->_record[$field_name] = $value;
        return $this;
    }

    final function __set($field_name, $value) {
        $this->set($field_name, $value);
    }

    /**
     * Save record.
     * If ID is not setted, create new record.
     * If ID is setted, update record with this ID.
     *
     * @param array|Form $data Record data. If not used, save current record data in object.
     * @return ModelBase Saved field data.
     * @see Model::getQueryStatus()
     * @see Model::getQueryErrorCode()
     * @see Model::getQueryErrorMessage()
     */
    final public function save($data = null) {
        if ((class_exists('Form') && ($data instanceof Form)) || (is_array($data) && (count($data) > 0))) $this->setData($data);
        if ($this->hasData()) {
            // Before save
            $this->onBeforeSave();
            // Has data
            if ($this->isRecord()) {
                // Update
                $values = $this->_record;
                if (is_array($this->_id_field)) {
                    // Multi-Key
                    $where = array();
                    $ids = $this->getId();
                    foreach ($this->_id_field as $field) {
                        unset($values[$field]);
                        $where[$field] = $ids[$field];
                    }
                } else {
                    // Single-Key
                    unset($values[$this->_id_field]);
                    $id = $this->_cnx->escape($this->getId());
                    $where = "`{$this->_id_field}` = '{$id}'";
                }
                if ($this->_cnx->update($this->_table, $where, $values)) {
                    // Load data
                    $this->findById($this->id);
                }
            } else {
                // Insert
                if ($this->_cnx->insert($this->_table, $this->_record)) {
                    // Load data
                    $this->findById($this->_cnx->lastInsertedID());
                }
            }
        }
        return $this;
    }

    /**
     * Update all records with custom values.
     *
     * @param array $arr_values Values.
     * @param string|array $condition (Default: All) Conditions to update (WHERE).
     * @return ModelBase
     */
    final public function updateAll(array $arr_values, $condition = '1 = 1') {
        $this->_cnx->update($this->getTable(), $condition, $arr_values);
        return $this;
    }

    /**
     * Update records with current model values.
     *
     * @param string|array $condition Conditions to update (WHERE).
     * @return ModelBase
     */
    final public function updateWhere($condition) {
        $arr_values = $this->getData();
        $this->_cnx->update($this->getTable(), $condition, $arr_values);
        return $this;
    }

    /**
     * Insert new rows.
     *
     * @param array $rows Rows to save.
     * @return ModelBase
     */
    final public function insert(array $rows) {
        if (count($rows) > 0) {
            if (is_array($rows[0])) {
                // Multiple records
                foreach ($rows as $row) {
                    $this->newRecord($row)->save();
                }
            } else {
                // Unique record
                $this->newRecord($rows)->save();
            }
        }
        $this->clearData();
        return $this;
    }

    /**
     * MySQL error mesage.
     *
     * @return string
     */
    final public function getQueryErrorMessage() {
        return $this->_cnx->getErrorMessage();
    }

    /**
     * MySQL error code.
     *
     * @return integer
     */
    final public function getQueryErrorCode() {
        return $this->_cnx->getErrorCode();
    }

    /**
     * Query executed?
     * TRUE: Query executed.
     * FALSE: Error or not executed.
     *
     * @return boolean
     */
    final public function getQueryStatus() {
        return $this->_cnx->getStatus();
    }

    /**
     * Set record data.
     *
     * @param array|Form $data Record data.
     * @return ModelBase
     */
    final public function setData($data) {
        if (class_exists('Form') && ($data instanceof Form)) {
            $this->setData($data->getValues(array_keys($this->getColumns())));
        } else {
            if (is_null($data) || !is_array($data)) {
                $this->_record = array();
            } else {
                // Remove invalid columns
                foreach ($data as $column_name => $v) {
                    if (
                        is_numeric($column_name) ||
                        (($this->getTable() != self::TABLE) && !array_key_exists($column_name, $this->_columns))
                    ) {
                        unset($data[$column_name]);
                    }
                }
                $this->_record = $data;
            }
            if ($this->isRecord() && $this->_checkSavedDataChecksum()) {
                $this->onBeforeLoad();
            }
        }
        return $this;
    }

    /**
     * Clear record data. Not affect DB record, only clear this object.
     *
     * @return ModelBase
     */
    final public function clearData() {
        $this->_record = array();
        return $this;
    }

    /**
     * Create new record. Clear current record data.
     *
     * @param array|Form $data Record data.
     * @return ModelBase
     */
    final public function newRecord($data = null) {
        $this->clearData();
        if (!is_null($data)) {
            $this->setData($data);
        }
        return $this;
    }

    /**
     * Find by record ID.
     *
     * @param integer|arrat $id Record ID or IDs.
     * @return ModelBase
     */
    final public function findById($id) {
        $this->findFirst($this->_id_field, $id);
        return $this;
    }

    /**
     * Reload record.
     *
     * @return ModelBase
     */
    final public function reload() {
        if ($this->hasData()) {
            $this->findById($this->getId());
        }
        return $this;
    }

    /**
     * Find by field.
     *
     * @param string $field Field name.
     * @param string $value Field value.
     * @return ModelBase
     */
    final public function findBy($column_name, $value) {
        $value       = $this->_cnx->escape($value);
        $column_name = Cnx::__columnEscape($column_name);
        $select      = $this->_getJoinSelect();
        $join        = $this->_getJoinFrom();
        $this->_execQuery("SELECT *{$select} FROM `{$this->_table}`{$join} WHERE {$column_name} = '{$value}'");
        $this->setData($this->_cnx->result(true));
        return $this;
    }

    /**
     * Find first record by field.
     *
     * @param string|array $field Field name.
     * @param string|array $value Field value.
     * @return ModelBase
     */
    final public function findFirst($column_name = null, $value = null) {
        $select = $this->_getJoinSelect();
        $join   = $this->_getJoinFrom();
        $q      = "SELECT *{$select} FROM `{$this->_table}`{$join}";
        if (!is_null($column_name)) {
            if (is_array($column_name)) {
                // Multi-Key
                $where = array();
                foreach ($column_name as $ckey => $cval) {
                    if (is_array($value)) {
                        if (array_key_exists($cval, $value)) {
                            $v = $value[$cval];
                        } else {
                            $v = array_key_exists($ckey, $value) ? $value[$ckey] : null;
                        }
                    } else {
                        $v = $value;
                    }
                    $where[$cval] = $v;
                }
                $where = Cnx::Where($where);
            } else {
                // Single-Key
                $value       = $this->_cnx->escape($value);
                $column_name = Cnx::__columnEscape($column_name);
                $where       = "{$column_name} = '{$value}'";
            }
            $q .= " WHERE {$where}";
        }
        $q .= " LIMIT 1";
        $this->_execQuery($q);
        $this->setData($this->_cnx->result(true));
        return $this;
    }

    /**
     * Find record.
     *
     * @param array $where Where.
     *     array(
     *         'field1' => '111', // =
     *         'field2 <' => 222, // <
     *         'field3 LIKE' => 'asd', // LIKE
     *         'field4' => array(1, 2, 3, 4, 'asd'), // IN
     *         'field5 NOT' => array(1, 2, 3, 4, 'asd'), // NOT IN
     *         'OR' => array( ... ),
     *         'AND' => array( ... )
     *     )
     * @param string|array $order Order.
     *     Examples:
     *         array('field1' => 'DESC', 'field2')
     *         array('field1', 'field2')
     *         'field1 DESC'
     *         'field1'
     * @return ModelBase
     */
    final public function find(array $where, $order = null) {
        $ord    = Cnx::Order($order);
        $whr    = Cnx::Where($where);
        $select = $this->_getJoinSelect();
        $join   = $this->_getJoinFrom();
        $this->_execQuery("SELECT *{$select} FROM `{$this->_table}`{$join} WHERE {$whr} {$ord} LIMIT 1");
        $this->setData($this->_cnx->result(true));
        return $this;
    }

    /**
     * Find all rows by field.
     *
     * @param string $field Field name.
     * @param string $value Field value.
     * @param string|array $order Order.
     *     Examples:
     *         array('field1' => 'DESC', 'field2')
     *         array('field1', 'field2')
     *         'field1 DESC'
     *         'field1'
     * @param integer $row_count Limit row count.
     * @param integer $offset Limit offset.
     * @return ModelBase[]
     */
    final public function findAllBy($field, $value, $order = null, $row_count = null, $offset = null) {
        $ord    = Cnx::Order($order);
        $lmt    = Cnx::Limit($row_count, $offset);
        $value  = $this->_cnx->escape($value);
        $select = $this->_getJoinSelect();
        $join   = $this->_getJoinFrom();
        return $this->_cnxResult("SELECT *{$select} FROM `{$this->_table}`{$join} WHERE `{$field}` = '{$value}' {$ord} {$lmt}");
    }

    /**
     * Delete records.
     *
     * @param array|string Optional. $field_where Field name or condition.
     *  If not setted, delete current record.
     * @param string Optional. $value Field value.
     * @return ModelBase This instence.
     */
    final public function delete($field_where = null, $value = null) {
        if (!$field_where && $this->isRecord()) {
            $field_where = $this->getId();
        }
        if ($field_where) {
            if (is_array($field_where)) {
                $where = Cnx::Where($field_where);
            } else {
                $value       = $this->_cnx->escape($value);
                $field_where = Cnx::__columnEscape($field_where);
                $where       = "{$field_where} = '{$value}'";
            }
            $this->_execQuery("DELETE FROM `{$this->_table}` WHERE {$where}");
        }
        return $this;
    }

    /**
     * Find all rows.
     *
     * @param array $where Where.
     *     array(
     *         'field1' => '111', // =
     *         'field2 <' => 222, // <
     *         'field3 LIKE' => 'asd', // LIKE
     *         'field4' => array(1, 2, 3, 4, 'asd'), // IN
     *         'field5 NOT' => array(1, 2, 3, 4, 'asd'), // NOT IN
     *         'OR' => array( ... ),
     *         'AND' => array( ... )
     *     )
     * @param string|array $order Order.
     *     Examples:
     *         array('field1' => 'DESC', 'field2')
     *         array('field1', 'field2')
     *         'field1 DESC'
     *         'field1'
     * @param integer $row_count Limit row count.
     * @param integer $offset Limit offset.
     * @return ModelBase[]
     * @see Cnx::Where
     * @see Cnx::Limit
     * @see Cnx::Order
     */
    final public function findAll(array $where = array(), $order = null, $row_count = null, $offset = null) {
        $ord = Cnx::Order($order);
        $lmt = Cnx::Limit($row_count, $offset);
        $whr = Cnx::Where($where);
        $select = $this->_getJoinSelect();
        $join = $this->_getJoinFrom();
        return $this->_cnxResult("SELECT *{$select} FROM `{$this->_table}`{$join} WHERE {$whr} {$ord} {$lmt}");
    }

    /**
     * Count where.
     *
     * @param string|array $where Where count.
     * @return integer
     * @see Cnx::Where
     */
    final public function countWhere($where) {
        $whr      = Cnx::Where($where);
        $join     = $this->_getJoinFrom();
        $id_field = is_array($this->_id_field) ? $this->_id_field[0] : $this->_id_field;
        $this->_execQuery("SELECT COUNT(`{$this->_table}`.`{$id_field}`) AS _CountQueryResult FROM `{$this->_table}`{$join} WHERE {$whr}");
        $r = $this->_cnx->result();
        return intval($r[0]['_CountQueryResult']);
    }

    /**
     * Search all rows.
     *
     * @param string $field Field name.
     * @param string $value Field value.
     * @param array $where Where.
     *     array(
     *         'field1' => '111', // =
     *         'field2 <' => 222, // <
     *         'field3 LIKE' => 'asd', // LIKE
     *         'field4' => array(1, 2, 3, 4, 'asd'), // IN
     *         'field5 NOT' => array(1, 2, 3, 4, 'asd'), // NOT IN
     *         'OR' => array( ... ),
     *         'AND' => array( ... )
     *     )
     * @param string|array $order Order.
     *     Examples:
     *         array('field1' => 'DESC', 'field2')
     *         array('field1', 'field2')
     *         'field1 DESC'
     *         'field1'
     * @param integer $row_count Limit row count.
     * @param integer $offset Limit offset.
     * @return ModelBase[]
     */
    final public function search($field, $value, array $where = array(), $order = null, $row_count = null, $offset = null) {
        $ord = Cnx::Order($order);
        $lmt = Cnx::Limit($row_count, $offset);
        $whr = $this->_cnx->searchString($field, $value);
        if (!is_null($where)) {
            $whr = "($whr) AND " . Cnx::Where($where);
        }
        $select = $this->_getJoinSelect();
        $join = $this->_getJoinFrom();
        return $this->_cnxResult("SELECT *{$select} FROM `{$this->_table}`{$join} WHERE {$whr} {$ord} {$lmt}");
    }

    /**
     * Set form.
     * Redeclare if necessary with form fields to use.
     *
     * @param Form $frm Form to set fields.
     */
    final public function setForm(Form $frm) {
        $this->setFormFields($frm);
        if (!$frm->hasData() && $this->isRecord()) {
            $frm->setData($this->toArray());
        }
    }

    /**
     * Get model columns.
     *
     * @param boolean $all
     *     TRUE to get all columns.
     *     FALSE to get only visible columns (columns with 'title' attribute).
     * @return array
     */
    final public function getColumns($all = true) {
        if ($all) {
            return $this->_columns;
        } else {
            $clazz = $this->getClass();
            if (!isset(self::$_columns_visibles[$clazz])) {
                self::$_columns_visibles[$clazz] = array();
                foreach ($this->_columns as $k => $v) {
                    if (is_array($v)) {
                        if (array_key_exists('title', $v)) {
                            self::$_columns_visibles[$clazz][$k] = $v;
                        }
                    }
                }
            }
            return self::$_columns_visibles[$clazz];
        }
    }

    /**
     * Returns TRUE if column exists.
     *
     * @param string $column_name Column name.
     * @return boolean TRUE if column exists.
     */
    final public function hasColumn($column_name) {
        return array_key_exists($column_name, $this->getColumns());
    }

    /**
     * Get column label.
     *
     * @param string $column_name Column name.
     * @return string|null Column label.
     */
    final public function getColumnLabel($column_name) {
        if ($this->hasColumn($column_name)) {
            $cols = $this->getColumns();
            $attrs = $cols[$column_name];
            return isset($attrs['label']) ? $attrs['label'] : (isset($attrs['title']) ? $attrs['title'] : null);
        } else {
            return null;
        }
    }

    /**
     * Execute Query String.
     *
     * @param string $q Query String.
     */
    final protected function _execQuery($q) {
        $r = $this->_cnx->query($q);
        if ($this->_debug_query_string) {
            XDIE($r);
        }
    }

    final private function _getStaticValue($st) {
        eval("\$x = {$this->_clazz}::{$st};");
        return $x;
    }

    /**
     * @return ModelBase[]
     */
    final private function _cnxResult($q) {
        $this->_execQuery($q);
        $result = $this->_cnx->result();
        $records = array();
        if (is_array($result)) {
            foreach ($result as $record) {
                eval("\$records[] = new {$this->_clazz}(\$record);");
            }
        }
        return $records;
    }

    /**
     * Load model at field.
     *
     * @param string $model_name Model to load.
     * @param string $field Field where search the row ID.
     * @return ModelBase Model instance.
     */
    final private function _loadModel($model_name, $field = null) {
        $this->APP->model($model_name);
        $clazz = 'Model' . $model_name;
        if (!is_null($field) && notEmptyCheck($field)) {
            eval("\$o = new {$clazz}(\$this->{$field});");
        } else {
            eval("\$o = new {$clazz}();");
        }
        return $o;
    }

    final private static function _formFieldSimpleInput(Form $frm, $label, $name, $empty, array $attrs, $len = 255) {
        $frm->addInput($name, $label, 'text',
                array_merge($attrs, array('maxlength' => $len)),
                array(
                    'empty' => $empty,
                    'max' => $len
                )
        );
    }

    final private static function _isArrayKeyInt($key, array $props) {
        $s = array_search($key, $props);
        return !($s === false) && is_numeric($s);
    }

    final private function _getJoinSelect() {
        $select = '';
        foreach ($this->_joins as $table => $def) {
            foreach ($def['select'] as $column => $alias) {
                $column = Cnx::__columnEscape($column);
                $alias = Cnx::__columnEscape($alias);
                $select .= ", `{$table}`.{$column} AS {$alias}";
            }
        }
        return $select;
    }

    final private function _getJoinFrom() {
        $from = '';
        foreach ($this->_joins as $table => $def) {
            $mode = isset($def['mode']) ? strtoupper($def['mode']) : 'INNER';
            $from .= " {$mode} JOIN `{$table}` ON ({$def['on']})";
        }
        return $from;
    }

    /**
     * Check if internal record data changed.
     *
     * @return boolean TRUE if data changed.
     */
    final private function _checkSavedDataChecksum() {
        if (is_null($this->_last_data_checksum) || ($this->_last_data_checksum != $this->_getDataChecksum())) {
            $this->_last_data_checksum = $this->_getDataChecksum();
            // Data changed
            return true;
        }
        // No changes
        return false;
    }

    /**
     * Get record data checksum.
     */
    final private function _getDataChecksum() {
        $s = serialize($this->_record);
        $s = md5($s) . strlen($s);
        return $s;
    }

    /**
     * Check field type.
     *
     * @param string $field_name
     * @param string $type
     * @return boolean
     */
    final private function _checkType($field_name, $type) {
        $columns = $this->getColumns();
        if (isset($columns[$field_name])) {
            $attrs = $columns[$field_name];
            if (is_array($attrs) && !isset($attrs['enum'])) {
                foreach ($attrs as $key => $value) {
                    if (is_numeric($key) && ($value == $type)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
}

/**
 * Generic DB Model for generic instance.
 *
 * @license Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @author Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @version 1.0
 * @package ar.com.eduardocuomo.class.model
 * @copyright Eduardo Daniel Cuomo
 */
class Model extends ModelBase {

    /**
     * Execute generic query.
     *
     * @param array $query Query,
     * @return Model
     * @see Cnx::CreateQuery
     */
    final public function query(array $query) {
        $this->_execQuery(Cnx::CreateQuery($query));
        $this->setData($this->_cnx->result(true));
        return $this;
    }
}