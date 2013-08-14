<?php

// Load Validations
APP::GetInstance()->load('Validations', 'FieldRender');

/**
 * Form field.
 *
 * @license Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @author Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @version 1.0
 * @package ar.com.eduardocuomo.class.form
 * @copyright Eduardo Daniel Cuomo
 */
class FormField extends TagHtml {

    /**
     * Form field container class.
     *
     * @var string
     */
    const CONT_CLASS = 'form-field';

    /**
     * Form actions container class.
     *
     * @var string
     */
    const CONT_CLASS_ACTION = 'form-action';

    /**
     * Field name.
     *
     * @var string
     */
    public $name;

    /**
     * Function to generate HTML field.
     *
     * Runnable::run = function($name, array $attrs, array $extra) { return 'FIELD HTML';}
     *
     * @var string|Runnable
     */
    public $html;

    /**
     * Field label.
     *
     * @var string
     */
    public $label;

    /**
     * Field attributes.
     *
     * @var array
     */
    public $attrs;

    /**
     * Field validations.
     *
     * @var array
     */
    public $validations;

    /**
     * Field value.
     *
     * @var string
     */
    public $value;

    /**
     * Default field value.
     *
     * @var string
     */
    public $default_value;

    /**
     * Extra parameters.
     *
     * @var array
     */
    public $extra = array();

    /**
     * Container class.
     *
     * @var string
     */
    public $cont_class = self::CONT_CLASS;

    /**
     * Auto set and emtpy value?
     *
     * @var boolean
     */
    protected $_auto_value = true;

    /**
     * Error messages.
     *
     * @var array
     */
    protected $_errors = false;

    /**
     * Default validations values.
     *
     * @var array
     */
    protected $_default_validations = array(
        // TRUE to allow empty
        'empty'                       => true,
        // TRUE to allow only numbers
        'integer'                     => false,
        // TRUE to allow decimal number
        'decimal'                     => false,
        // Decimal char
        'decimal-char'                => ValidationDecimalChar,
        // Minimun String/Integer lenght
        'min'                         => 0,
        // Maximum String/Integer lenght
        'max'                         => null,
        // TRUE to allow only valid e-Mail
        'email'                       => false,
        // TRUE to allow only valid URL
        'url'                         => false,
        // TRUE to allow only valid IP
        'ip'                          => false,
        // TRUE to allow only a valid Date
        'date'                        => false,
        // Date format to validate (y = Year, m = Month, d = Day)
        'date-format'                 => ValidationDateFormat,
        // Date description
        'date-desc'                   => ValidationDateFormatDescription,
        // Check RexExp
        'regexp'                      => null,
        // RegExp description
        'regexp-desc'                 => null
    );

    /**
     * Create Form Field.
     *
     * @param string $name Field name.
     * @param string $label Field label.
     * @param array $attrs Field attributes.
     * @param array $validations Validations.
     * @param boolean $auto_value Set value from Request values.
     */
    function __construct($name = null, $label = null, array $attrs = null, array $validations = null, $auto_value = true) {
        parent::__construct();
        $this->name          = $name;
        $this->label         = $label;
        $this->attrs         = $attrs;
        $this->_auto_value   = !!$auto_value;
        $this->validations   = (!is_null($validations) && is_array($validations)) ? array_merge($this->_default_validations, array_change_key_case($validations, CASE_LOWER)) : null;
        $this->default_value = (is_array($attrs) && isset($attrs['value'])) ? $attrs['value'] : null;
        if ($this->_auto_value) {
            $this->setData($_REQUEST);
        } else {
            $this->value = $this->default_value;
        }
    }

    /**
     * Set attributes.
     *
     * @param array $attrs Attributes.
     * @return FormField
     */
    public function setAttrs(array $attrs) {
        $this->attrs = is_array($this->attrs) ? array_merge($this->attrs, $attrs) : $attrs;
        return $this;
    }

    /**
     * Set an attribute.
     *
     * @param string $name Attribute name.
     * @param string $value Attribute value.
     * @return FormField
     */
    public function setAttr($name, $value) {
        if (!is_array($this->attrs)) $this->attrs = array();
        $this->attrs[$name] = $value;
        return $this;
    }

    /**
     * Set form field data.
     *
     * @param array $data Request / Get / Post values.
     * @return FormField
     */
    public function setData(array $data) {
        if (isset($data[$this->name])) {
            $this->value = $data[$this->name];
        }
        return $this;
    }

    /**
     * Empty setted and selected value.
     *
     * @param boolean $force_empty Force empty value.
     * @return FormField
     */
    public function emptyValue($force_empty = false) {
        if ($this->_auto_value || $force_empty) {
            $this->value = null;
            if (!is_null($this->attrs) && isset($this->attrs['value'])) {
                unset($this->attrs['value']);
            }
            if (isset($this->extra['selected'])) {
                unset($this->extra['selected']);
            }
        }
        return $this;
    }

    /**
     * Set function to generate HTML.
     *
     * Runnable::run = function($name, array $attrs, array $extra) { return 'FIELD HTML';}
     *
     * @param string|Runnable $html Function to generate HTML.
     * @return FormField
     */
    public function setHtml($html) {
        $this->html = $html;
        return $this;
    }

    /**
     * Add extra.
     *
     * @param string $key Extra key.
     * @param string $value Extra value.
     * @return FormField
     */
    public function addExtra($key, $value) {
        $this->extra[$key] = $value;
        return $this;
    }

    /**
     * Build field, label and conteiner.
     *
     * @param string $field_cont_class Optional. Extra form field container class.
     * @param boolean $render Optional.
     *     TRUE to render field.
     *     FALSE to return field HTML.
     * @param boolean $render_errors Optional. Render errors after field?
     *     Only works if has container.
     * @return string
     */
    public function build($field_cont_class = null, $render = true, $render_errors = true) {
        $x = '';
        if ($this->hasContainer()) {
            $fcc = $this->cont_class;
            if (!is_null($field_cont_class)) $fcc .= " $field_cont_class";
            $x .= "<div class=\"$fcc\">";
            if ($this->hasLabel()) {
                if (Validations::containsHtmlTag($this->label)) {
                    // Contains HTML tags
                    $label = $this->label;
                } else {
                    // Not contanins HTML tags
                    $label = htmlspecialchars($this->label);
                }
                if ($this->checkType('file')) $label .= ' (max: ' . Utils::MaxUploadSize() . 'Mb)';
                if ((!$this->validations['empty'] && $this->isActive())) $label .= ' (*)';
                $x .= "<label for=\"{$this->name}\">{$label}</label>";
            }
            $x .= $this->toHtml();
            // Errors
            if ($render_errors) {
                $x .= $this->_buildErrors();
            }
            $x .= '</div>';
        } else {
            $x .= $this->toHtml();
            // Errors
            if ($render_errors) {
                $x .= $this->_buildErrors();
            }
        }

        if ($render) {
            echo $x;
        }

        return $x;
    }

    /**
     * Render HTML field.
     *
     * @param string $value Set field value.
     */
    public function render($value = null) {
        if (!is_null($value)) {
            $old = $this->value;
            $this->value = $value;
        }
        echo $this->toHtml();
        if (!is_null($value)) {
            $this->value = $old;
        }
    }

    /**
     * Check field type.
     * Returns TRUE if this field type is $type.
     *
     * @param string $type Check if this field type is $type.
     * @return boolean
     */
    public function checkType($type) {
        return isset($this->attrs['type']) && (strtolower($this->attrs['type']) == strtolower($type));
    }

    /**
     * Get field HTML.
     *
     * @return string Field HTML.
     */
    public function toHtml() {
        if ($this->html instanceof Runnable) {
            return $this->html->run($this);
        } else {
            return $this->html;
        }
    }

    /**
     * Returns TRUE if has container DIV.
     *
     * @return boolean TRUE if has container DIV.
     */
    public function hasContainer() {
        return !($this->label === false);
    }

    /**
     * Returns TRUE if has label.
     *
     * @return boolean TRUE if has label.
     */
    public function hasLabel() {
        return $this->hasContainer() && notEmptyCheck($this->label);
    }

    /**
     * Field is setted?
     *
     * @return boolean TRUE if field is setted.
     */
    public function isSetted() {
        return !is_null($this->value) && ($this->value != '');
    }

    /**
     * Returns errors.
     *
     * @return array
     */
    public function getErrors() {
        if ($this->_errors === false)
            $this->validate();
        return $this->_errors;
    }

    /**
     * Field has errors?
     *
     * @return boolean TRUE if field has errors.
     */
    public function hasErrors() {
        return count($this->getErrors()) > 0;
    }

    /**
     * Field is valid?
     *
     * @return boolean TRUE if field is valid.
     */
    public function isValid() {
        return !$this->hasErrors();
    }

    /**
     * Field is empty?
     *
     * @return boolean TRUE if field is empty.
     */
    public function isEmpty() {
        return Validations::isEmpty($this->value);
    }

    /**
     * For checkbox fields. Returns TRUE if field is checked.
     *
     * @return boolean TRUE if checked.
     */
    public function isChecked() {
        return intval($this->value) > 0;
    }

    /**
     * Compare date.
     *
     * @param string $date (Default: Now) Date to compare. Format: "YYYY-mm-dd".
     * @return boolean|integer
     *  FALSE = $this is invalid (invalid value).
     *   -1   = $this < $date
     *    0   = $this == $date
     *    1   = $this > $date
     */
    public function dateCompare($date = null) {
        if ($this->isValid()) {
            $ff = strtotime($this->value);
            $d = strtotime(is_null($date) ? date('Y-m-d') : $date);
            return ($d == $ff) ? 0 : (($ff < $d) ? -1 : 1);
        } else {
            // Invalid value
            return false;
        }
    }

    /**
     * Field value.
     *
     * @return string
     */
    public function toString() {
        return $this->value;
    }

    /**
     * Field value.
     *
     * @return string
     */
    public function __toString() {
        return $this->toString();
    }

    /**
     * Returns TRUE if field is active (not readonly or disabled).
     *
     * @return boolean
     */
    public function isActive() {
        if (is_array($this->attrs)) {
            return !(in_array('disabled', array_keys($this->attrs)) && $this->attrs['disabled']); // && !in_array('readonly', $this->attrs);
        } else {
            return true;
        }
    }

    /**
     * Add an error.
     * Error message already contains a start message with field label.
     *
     * @param string $message Message.
     * @return FormField
     */
    public function addError($message) {
        $this->_addError($message);
        return $this;
    }

    /**
     * Force field validation.
     */
    public function validate() {
        $this->_errors = array();
        if (
                !is_null($this->validations)
                && is_array($this->validations)
                && $this->isActive()
        ) {
            // Validate as number
            $validate_as_integer = array_key_exists('integer', $this->validations) && $this->validations['integer'];
            $validate_as_decimal = array_key_exists('decimal', $this->validations) && $this->validations['decimal'];
            $validate_as_number  = $validate_as_decimal || $validate_as_integer;
            if ($validate_as_number) {
                $validate_as_number_valid = true;
                if ($validate_as_integer) {
                    if (!Validations::isInteger($this->value)) {
                        $validate_as_number_valid = false;
                        $this->_addError('debe ser un número');
                    }
                }
                if ($validate_as_decimal) {
                    if (!Validations::isDecimal($this->value, $this->validations['decimal-char'])) {
                        $validate_as_number_valid = false;
                        $this->_addError("debe ser un número decimal (usar para decimales el caracter '{$this->validations['decimal-char']}')");
                    }
                }
            }
            // Validate
            foreach ($this->validations as $validation_name => $val) {
                switch ($validation_name) {
                    case 'empty':
                        if (!$val && Validations::isEmpty($this->value))
                            $this->_addError('no puede ser vacío');
                        break;
                    case 'min':
                        if ($validate_as_number) {
                            // As number
                            if ($validate_as_number_valid && !is_null($val) && ($this->value < $val))
                                $this->_addError("debe ser como mínimo {$val}");
                        } else {
                            // As string
                            if (is_int($val) && !Validations::minLength($this->value, $val))
                                $this->_addError("debe tener como mínimo {$val} caracteres");
                        }
                        break;
                    case 'max':
                        if ($validate_as_number) {
                            // As number
                            if ($validate_as_number_valid && !is_null($val) && ($this->value > $val))
                                $this->_addError("debe ser como máximo {$val}");
                        } else {
                            // As string
                            if (is_int($val) && !Validations::maxLength($this->value, $val))
                                $this->_addError("debe tener como máximo {$val} caracteres");
                        }
                        break;
                    case 'email':
                        if ($val && !Validations::isEmail($this->value))
                            $this->_addError('debe ser un e-Mail');
                        break;
                    case 'ip':
                        if ($val && !Validations::isIP($this->value))
                            $this->_addError('debe ser una IP');
                        break;
                    case 'url':
                        if ($val && !Validations::isURL($this->value))
                            $this->_addError('debe ser una URL');
                        break;
                    case 'date':
                        if ($val && !Validations::isDate($this->value, $this->validations['date-format']))
                            $this->_addError("debe ser una fecha con el formato '{$this->validations['date-desc']}'");
                        break;
                    case 'regexp':
                        if (!is_null($val) && !Validations::isRegExp($this->value, $val))
                            if (is_null($this->validations['regexp-desc'])) {
                            $this->_addError('no tiene el formato esperado');
                        } else {
                            $this->_addError('debe ser: ' . $this->validations['regexp-desc']);
                        }
                        break;
                }
            }
        }
    }

    /**
     * Create HTML field.
     *
     * @param string|boolean $label Field label.
     *     FALSE to remove field container.
     *     NULL to remove field label.
     * @param string|Runnable $html Field HTML content.
     *     Runnable::run = function($name, array $attrs, array $extra) { return 'FIELD HTML';}
     * @param string $name Field name.
     * @param array $attrs Optional. Attributes.
     * @param array $extra Optional. Extras.
     * @param boolean $render Optional. TRUE to render value.
     * @param boolean $auto_value. Auto set value?
     * @return string Field HTML.
     */
    public static function Html($name, $html = null, $label = false, array $attrs = null, array $extra = null, $render = true, $auto_value = true) {
        if (is_array($name)) return self::_callAsArray(__METHOD__, $name, array(
            'name'       => null,
            'html'       => null,
            'label'      => null,
            'attrs'      => null,
            'extra'      => null,
            'render'     => true,
            'auto_value' => true
        ));
        if (is_null($name)) throw new AppException("Invalid call! {$name} can not be NULL!");
        $field = new self($name, $label, $attrs);
        $field->setHtml($html);
        if (!is_null($extra) && is_array($extra)) {
            foreach ($extra as $key => $value) {
                $field->addExtra($key, $value);
            }
        }
        // Render
        if ($render) $field->build();
        // Return
        return $field;
    }

    /**
     * Create input field.
     *
     * @param string|boolean $label Field label.
     *     FALSE to remove field container.
     *     NULL to remove field label.
     * @param string $name Field name.
     * @param string $type Optional. Input type.
     *     If string is 'hidden', then $label = false.
     *     If not setted:
     *         If $name == 'password'
     *             then  type = 'password'
     *             else type = 'text'.
     * @param array $attrs Optional. Attributes.
     * @param array $validations Optional. Validations.
     *     If not setted, calculate validatios.
     * @param boolean $render Optional. TRUE to render value.
     * @param boolean $auto_value. Auto set value?
     * @return string Field HTML.
     */
    public static function Input($name, $label = false, $type = null, array $attrs = null, array $validations = null, $render = true, $auto_value = true) {
        if (is_array($name)) return self::_callAsArray(__METHOD__, $name, array(
            'name'        => null,
            'label'       => null,
            'type'        => null,
            'attrs'       => null,
            'validations' => null,
            'render'      => true,
            'auto_value'  => true
        ));
        if (is_null($name)) throw new AppException("Invalid call! $name can not be NULL!");
        $type = strtolower($type);
        $lname = strtolower($name);
        $field_name_username = array('usr', 'user', 'username', 'usuario');
        $field_name_password = array('password', 'pass', 'clave', 'pwd', 'contrasena', 'contrasenia');
        $field_name_email = array('mail', 'email', 'e_mail');

        if (is_null($attrs)) {
            $attrs = array();
        } else {
            $attrs = array_change_key_case($attrs, CASE_LOWER);
        }

        if (is_null($type) || emptyCheck($type)) {
            if (in_array($lname, $field_name_password)) {
                $type = 'password';
            } else {
                $type = 'text';
            }
        }

        $hidden = ($type == 'hidden') || (array_key_exists('type', $attrs) && ($attrs['type'] == 'hidden'));

        if ($hidden) {
            // Attributes
            $type = 'hidden';
            // Remove label
            $label = false;
            // Remove validations
            $validations = null;
        }

        $attrs['type'] = $type;

        if (!is_null($validations) && is_array($validations)) {
            // Set custom validations
            if (!isset($attrs['min']) && isset($validations['min'])) {
                $attrs['min'] = $validations['min'];
            }
            if (!isset($attrs['max']) && isset($validations['max'])) {
                $attrs[($type == 'number') ? 'max' : 'maxlength'] = $validations['max'];
            }
        } elseif (in_array($lname, $field_name_email)) {
            // e-Mail
            $validations = array('email' => true);
        } elseif (in_array($lname, array_merge($field_name_username, $field_name_password))) {
            // Username or Password
            $validations = array('empty' => false);
        }

        $field = new self($name, $label, $attrs, ($hidden ? null : $validations), ($auto_value && !$hidden));
        $field->setHtml(new FormFieldRunnableInput());

        // Render
        if ($render) $field->build();
        // Return
        return $field;
    }

    /**
     * Add hidden input.
     *
     * @param string $name Field name.
     * @param string $value Value.
     * @param boolean $render Optional. TRUE to render value.
     * @return Form
     */
    public static function Hidden($name, $value, $render = true) {
        return self::Input($name, false, 'hidden', array('value' => $value), null, $render);
    }

    /**
     * Create TextArea field.
     *
     * @param string $name Field name.
     * @param string|boolean $label Field label.
     *     FALSE to remove field container.
     *     NULL to remove field label.
     * @param array $attrs Optional. Attributes.
     * @param array $validations Optional. Validations.
     * @param boolean $render Optional. TRUE to render value.
     * @param boolean $auto_value. Auto set value?
     * @return string Field HTML.
     */
    public static function TextArea($name, $label = false, array $attrs = null, array $validations = null, $render = true, $auto_value = true) {
        if (is_array($name)) return self::_callAsArray(__METHOD__, $name, array(
            'name'        => null,
            'label'       => null,
            'attrs'       => null,
            'validations' => null,
            'render'      => true,
            'auto_value'  => true
        ));
        if (is_null($name)) throw new AppException("Invalid call! $name can not be NULL!");
        if (is_null($attrs)) $attrs = array();
        $field = new self($name, $label, $attrs, $validations);
        $field->setHtml(new FormFieldRunnableTextArea());

        // Render
        if ($render) $field->build();
        // Return
        return $field;
    }

    /**
     * Create Select field.
     *
     * @param string $name Field name.
     * @param string|boolean $label Field label.
     *     FALSE to remove field container.
     *     NULL to remove field label.
     * $param array $options Options for Select.
     *     Uses:
     *         array(
     *             'text1'
     *             'vvv' => 'text2'
     *         )
     *         <select>
     *             <option value="0">text1</select>
     *             <option value="vvv">text2</select>
     *         </select>
     *
     *
     *         array(
     *             'og1' => array
     *                 'text1'
     *                 'vvv' => 'text2'
     *             )
     *         )
     *         <select>
     *             <optgroup label="og1">
     *                 <option value="0">text1</select>
     *                 <option value="vvv">text2</select>
     *             </optgroup>
     *         </select>
     *
     *
     *         array(
     *             // Data
     *             array(
     *                 array('id' => 1, 'val' => 'Name 1'),
     *                 array('id' => 2, 'val' => 'Name 2')
     *             ),
     *             'id', // Value
     *             'val' // Text
     *         )
     *         <select>
     *             <option value="1">Name 1</select>
     *             <option value="2">Name 2</select>
     *         </select>
     *
     *
     *         array(
     *             // Data
     *             array({Model}, {Model}),
     *             'id', // Field to use as Value
     *             'name' // Field to use as Text. If not setted, call "toString" method
     *         )
     *         <select>
     *             <option value="1">Name 1</select>
     *             <option value="2">Name 2</select>
     *         </select>
     * @$selected array|string $selected Selected option.
     * @param array $attrs Optional. Attributes.
     *  For multi-select, use:
     *    multiple = true
     * @param array $validations Optional. Validations.
     * @param boolean $render Optional. TRUE to render value.
     * @param boolean $auto_value. Auto set value?
     * @return string Field HTML.
     */
    public static function Select($name, $label = false, array $options = array(), $selected = null, array $attrs = null, array $validations = null, $render = true, $auto_value = true) {
        if (is_array($name)) return self::_callAsArray(__METHOD__, $name, array(
            'name'        => null,
            'label'       => null,
            'options'     => array(),
            'selected'    => null,
            'attrs'       => null,
            'validations' => null,
            'render'      => true,
            'auto_value'  => true
        ));
        if (is_null($name)) throw new AppException("Invalid call! $name can not be NULL!");
        if (is_null($attrs)) $attrs = array();
        $field = new self($name, $label, $attrs, $validations, $auto_value);
        $field->setHtml(new FormFieldRunnableSelect());
        $field->addExtra('selected', $selected);
        $field->addExtra('options', $options);

        // Render
        if ($render) $field->build();
        // Return
        return $field;
    }

    /**
     * Render select option.
     *
     * @param string $value Option value.
     * @param string $text Option text.
     * @param boolean $selected TRUE to set as selected.
     * @return string HTML select option.
     */
    public static function __option($value, $text, $selected = false) {
        $text = htmlspecialchars($text);
        $s = $selected ? ' selected="selected"' : '';
        return "<option value=\"{$value}\"{$s}>{$text}</option>";
    }

    /**
     * Render select options.
     *
     * @param array $options Options.
     * @param array|string $selected Default value to set as selected.
     * @return string HTML select option.
     */
    public static function __options(array $options, $selected = null) {
        $html = '';
        if (is_array($options)
                && (count($options) > 1)
                && isset($options[0]) && is_array($options[0])
                && isset($options[1]) && is_string($options[1])) {
            $ops = array();
            $o3 = count($options) > 2;
            foreach ($options[0] as $op) {
                if (is_array($op)) {
                    // Array
                    $ops[$op[$options[1]]] = $o3 ? $op[$options[2]] : $op[$options[1]];
                } else {
                    // Object
                    $ops[$op->{$options[1]}] = $o3 ? $op->{$options[2]} : strval($op);
                }
            }
            $html = self::__options($ops, $selected);
        } else {
            foreach ($options as $value => $text) {
                if (is_array($text)) {
                    $value = self::EscapeTag($value);
                    $html .= "<optgroup label=\"$value\">";
                    foreach ($text as $v => $t) {
                        $html .= FormField::__option($v, $t, is_array($selected) ? in_array($v, $selected) : ($v == $selected));
                    }
                    $html .= '</optgroup>';
                } else {
                    $html .= FormField::__option($value, $text, is_array($selected) ? in_array($value, $selected) : ($value == $selected));
                }
            }
        }
        return $html;
    }

    protected function _addError($message) {
        if ($this->hasLabel()) {
            $this->_errors[] = "El campo '{$this->label}' $message";
        } else {
            $this->_errors[] = "El campo $message";
        }
    }

    protected function _buildErrors() {
        $x = '';
        if ($this->hasErrors()) {
            $x .= '<ul class="error">';
            foreach ($this->_errors as $error_message) {
                $x .= '<li>' . htmlspecialchars($error_message) . '</li>';
            }
            $x .= '</ul>';
        }
        return $x;
    }

    protected static function _callAsArray($method, array $params, array $vars) {
        $php = '';
        foreach (array_keys($vars) as $var) {
            if (notEmptyCheck($php)) {
                $php .= ',';
            }
            if (isset($params[$var])) {
                $php .= "\$params['$var']";
            } else {
                $php .= "\$vars['$var']";
            }
        }
        eval("\$return = {$method}({$php});");
        return $return;
    }
}

/**
 * File form field.
 *
 * @license Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @author Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @version 1.0
 * @package ar.com.eduardocuomo.class.form
 * @copyright Eduardo Daniel Cuomo
 */
class FormFieldFile extends FormField {

    /**
     * File.
     *
     * Example = array(
     *     'name'     => 'file.jpg',
     *     'type'     => 'image/jpeg',
     *     'tmp_name' => '/tmp/phpRVcqVC',
     *     'error'    => 0,
     *     'size'     => 71336
     * )
     *
     * @var array
     */
    protected $_file = array();

    /**
     * Default validations values.
     *
     * @var array
     */
    protected $_default_validations = array(
        // TRUE to allow empty
        'empty' => true
    );

    /**
     * Uploaded file name.
     *
     * @var string
     */
    protected $_file_name;

    /**
     * Uploaded file path.
     *
     * @var string
     */
    protected $_file_path;

    function __construct($name = null, $label = null, array $attrs = null, array $validations = null) {
        if (is_null($attrs)) {
            $attrs = array();
        }
        $attrs['type'] = 'file';
        parent::__construct($name, $label, $attrs, $validations);
        $this->setData($_FILES);
    }

    /**
     * Get uploaded file name.
     *
     * @return string|null File name.
     */
    public function getUploadedFileName() {
        return $this->_getUploadedFileProp('name');
    }

    /**
     * Get uploaded file type.
     *
     * @return string|null File type.
     */
    public function getUploadedFileType() {
        return $this->_getUploadedFileProp('type');
    }

    /**
     * Get uploaded temporal file path.
     *
     * @return string|null Temporal file path.
     */
    public function getUploadedFileTmpName() {
        return $this->_getUploadedFileProp('tmp_name');
    }

    /**
     * Get uploaded file error code.
     *
     * @return integer|null Upload error. 0 on no error.
     */
    public function getUploadedFileError() {
        return $this->_getUploadedFileProp('error');
    }

    /**
     * Get uploaded file size in bytes.
     *
     * @return integer|null File size.
     */
    public function getUploadedFileSize() {
        return $this->_getUploadedFileProp('size');
    }

    /**
     * Set form field data.
     *
     * @param array $data Request / Get / Post values.
     * @return FormFieldFile
     */
    public function setData(array $data) {
        if (isset($data[$this->name])) {
            // File attributes
            $this->_file = $data[$this->name];
            // File name
            $this->value = isset($this->_file['name']) ? $this->_file['name'] : null;
        }
        return $this;
    }

    /**
     * Empty setted and selected value.
     *
     * @param boolean $force_empty Force empty value.
     * @return FormFieldFile
     * @see FormField::emptyValue()
     */
    public function emptyValue($force_empty = false) {
        parent::emptyValue($force_empty);
        $this->_file = array();
        return $this;
    }

    /**
     * Field is setted?
     *
     * @return boolean TRUE if field is setted.
     */
    public function isSetted() {
        return !is_null($this->value) && ($this->value != '') && is_array($this->_file)
        && isset($this->_file['name']) && !is_null($this->_file['name']) && ($this->_file['name'] != '');
    }

    /**
     * Force field validation.
     */
    public function validate() {
        $this->_errors = array();
        if ($this->isSetted() && ($this->getUploadedFileError() > 0)) {
            $this->_addError('ha fallado en el proceso de subida');
        } elseif ($this->isSetted() && $this->getUploadedFileExtension() == null) {
            $this->_addError('no puede ser guardado debido a que el archivo seleccionado no tiene extensión');
        } else {
            if (!is_null($this->validations) && is_array($this->validations)) {
                foreach ($this->validations as $validation_name => $val) {
                    switch (strtolower($validation_name)) {
                        case 'empty':
                            if (!$val && Validations::isEmpty($this->value))
                                $this->_addError('no puede ser vacío');
                            break;
                    }
                }
            }
        }
    }

    /**
     * Save uploaded file.
     *
     * @param string|boolean|null $file_name Is setted, new file name.
     *     String: File name.
     *     NULL: Uploaded file name.
     *     FALSE: MD5 file name.
     * @param boolean $replace Replace file if exists?
     *     TRUE: Replace file if exists.
     *     FALSE: Rename file if exists.
     * @param string $path Default: UploadPath. File path where save uploaded file.
     * @return string Uploaded file path.
     */
    public function saveFile($file_name = null, $replace = false, $path = null) {
        if (is_null($path)) $path = UploadPath;

        // Create directory if not exists
        if (!is_dir($path)) mkdir($path);

        if ($file_name === false) {
            $file_name = uniqid(md5($this->getUploadedFileName()) . '_', true);
        }

        if (is_null($file_name)) {
            // Use name of uploaded file
            $file_name = $this->getUploadedFileName();
        } else {
            // Add uploaded file extension
            $file_name .= '.' . $this->getUploadedFileExtension();
        }

        $this->_file_name = $fn = $file_name = Utils::EscapeFileName($file_name);
        $file_path = $path . DIRECTORY_SEPARATOR . $file_name;
        if (file_exists($file_path)) {
            if ($replace) {
                // Delete old file
                unlink($file_path);
            } else {
                // Rename file
                $i = 0;
                while (file_exists($file_path)) {
                    $i++;
                    $file_name = "{$fn}_{$i}." . $this->getUploadedFileExtension();
                    $file_path = $path . DIRECTORY_SEPARATOR . $file_name;
                }
            }
        }
        move_uploaded_file($this->getUploadedFileTmpName(), $file_path);
        $this->_file_name = $file_name;
        $this->_file_path = $file_path;
        return $this->_file_path;
    }

    /**
     * Get file extension and name.
     *
     * @param string $file_name Returns file name.
     * @return string|null File extension. NULL if has not extension.
     */
    public function getUploadedFileExtension(&$file_name = null) {
        $fn = $this->getUploadedFileName();
        if (strrpos($fn, '.') === false) {
            $file_name = $fn;
            return null;
        } else {
            $file_name = substr($fn, 0, strrpos($fn, '.'));
            return substr($fn, strrpos($fn, '.') + 1);
        }
    }

    /**
     * Saved file name.
     *
     * @return string
     */
    public function getSavedFileName() {
        return $this->_file_name;
    }

    /**
     * Saved file path.
     *
     * @return string
     */
    public function getSavedFilePath() {
        return $this->_file_path;
    }

    /**
     * Create input field.
     *
     * @param string $name Field name.
     * @param string|boolean $label Field label.
     *     FALSE to remove field container.
     *     NULL to remove field label.
     * @param array $attrs Optional. Attributes.
     * @param array $validations Optional. Validations.
     *     If not setted, calculate validatios.
     * @param boolean $render Optional. TRUE to render value.
     * @param boolean $auto_value. Auto set value?
     * @return string Field HTML.
     */
    public static function InputFile($name, $label = false, array $attrs = null, array $validations = null, $render = true) {
        if (is_array($name)) return self::_callAsArray(__METHOD__, $name, array(
            'name'        => null,
            'label'       => null,
            'attrs'       => null,
            'validations' => null,
            'render'      => true
        ));
        if (is_null($name)) throw new AppException("Invalid call! $name can not be NULL!");
        $lname = strtolower($name);

        $field = new self($name, $label, $attrs, $validations);
        $field->setHtml(new FormFieldRunnableInput());

        // Render
        if ($render) $field->build();

        // Return
        return $field;
    }

    protected function _getUploadedFileProp($prop) {
        return isset($this->_file[$prop]) ? $this->_file[$prop] : null;
    }

}