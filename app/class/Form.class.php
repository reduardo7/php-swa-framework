<?php

// Load FormField
APP::GetInstance()->load('FormField');

/**
 * Form.
 *
 * @license Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @author Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @version 1.0
 * @package ar.com.eduardocuomo.class.form
 * @copyright Eduardo Daniel Cuomo
 */
class Form extends APP_Base {

    /**
     * JavaScript VOID(0)
     *
     * @var string
     */
    const VOID = 'javascript:void(0)';

    const FORM_ID = '_fid';

    /**
     * Form name and ID.
     *
     * @var string
     */
    public $name;

    /**
     * Form attributes.
     *
     * @var array
     */
    protected $_attrs;

    /**
     * Default form attributes.
     *
     * @var array
     */
    protected $_form_defaults = array(
        'method' => 'POST'
    );

    /**
     * Form fields.
     *
     * @var FormField[]
     */
    protected $_fields = array();

    /**
     * Form errors.
     * Fill on call "validate()" method.
     *
     * @see Form::validate()
     * @var string array
     */
    protected $_errors = false;

    /**
     * Form ID.
     * Default form ID is 1.
     *
     * @var integer
     */
    protected $_form_id;

    /**
     * Form Submit Button ID.
     *
     * @var integer
     */
    protected static $_form_submit_id = 0;

    /**
     * Create new form.
     *
     * Default action: /CURRENT_CONTEXT/CURRENT_VIEW. Form action.
     *
     * @param array $attrs Optional. Form attributes.
     *  Use 'fid' attribute to set unique internal Form ID.
     */
    function __construct(array $attrs = array()) {
        parent::__construct();

        if (isset($attrs['fid'])) {
            $this->_form_id = $attrs['fid'];
            unset($attrs['fid']);
        } else {
            $trace          = debug_backtrace();
            $called         = $trace[0]['file'] . ':' . $trace[0]['line'];
            $this->_form_id = 'form_' . md5($called);
        }

        if (!isset($attrs['id'])) {
            $attrs['id'] = isset($attrs['name']) ? $attrs['name'] : $this->_form_id;
        }

        if (!isset($attrs['name'])) {
            $attrs['name'] = $attrs['id'];
        }

        $this->_form_defaults['action'] = $this->APP->createLink(array('view' => $this->APP->params->view, 'context' => $this->APP->params->context), false);
        $this->_attrs                   = array_merge($this->_form_defaults, $attrs);

        $this
        ->addInput(self::FORM_ID, false, 'hidden', array('value' => $this->_form_id))
        ->setName($attrs['name']);
    }

    /**
     * Set form data.
     *
     * @param array $data Request / Get / Post values.
     */
    public function setData(array $data) {
        foreach ($this->_fields as $field) {
            $field->setData($data);
        }
    }

    /**
     * Set method as GET.
     */
    public function setMethodGET() {
        $this->_attrs['method'] = 'GET';
        if (isset($this->_attrs['enctype']) && (strtolower($this->_attrs['enctype']) == 'multipart/form-data')) {
            unset($this->_attrs['enctype']);
        }
    }

    /**
     * Set method as POST.
     */
    public function setMethodPOST() {
        $this->_attrs['method'] = 'POST';
    }

    /**
     * Set method as File.
     */
    public function setMethodFILE() {
        $this->_attrs['method']  = 'POST';
        $this->_attrs['enctype'] = 'multipart/form-data';
    }

    /**
     * Set form name and ID.
     *
     * @param string $name Form name and ID.
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * Returns TRUE if form has data.
     *
     * @return boolean TRUE if form has data.
     */
    public function hasData() {
        return APP::GetInstance()->params->hasParams()
            && isset($_REQUEST[self::FORM_ID])
            && ($_REQUEST[self::FORM_ID] == $this->_form_id)
        ;
    }

    /**
     * Returns array with form fields.
     *
     * @return array Array of form fields.
     */
    public function getFields() {
        return $this->_fields;
    }

    /**
     * Return array of field values.
     *
     * @param array $filter_values Only get values in this list.
     * @return array Array of values.
     */
    public function getValues(array $filter_values = null) {
        $values = array();
        foreach ($this->_fields as $field) {
            if (
                (is_null($filter_values) || in_array($field->name, $filter_values))
                && $field->isActive()
            ) {
                $values[$field->name] = $field->value;
            }
        }
        return $values;
    }

    /**
     * Render form.
     *
     * @param string $field_cont_class Optional. Extra form field container class.
     * @param boolean $render_errors Optional. Render errors when form has data?
     * @return Form
     */
    public function render($field_cont_class = null, $render_errors = true) {
        if (!isset($this->_attrs['name'])) $this->_attrs['name'] = $this->name;
        if (!isset($this->_attrs['id'])) $this->_attrs['id'] = $this->name;
        if (isset($this->_attrs['class'])) {
            $this->_attrs['class'] .= ' form';
        } else {
            $this->_attrs['class'] = 'form';
        }
        // Open form
        $form = '<form ';
        foreach ($this->_attrs as $key => $value) {
            $value = strtr($value, array(
                '"'  => '\'',
                "\n" => ' ',
                "\t" => ' ',
                "\r" => ''
            ));
            $form .= " $key=\"$value\"";
        }
        $form .= '>';
        echo $form;

        // Render fields
        foreach ($this->_fields as $field) {
            $field->build($field_cont_class, true, $render_errors && $this->hasData());
        }

        // Close form
        echo '</form>';
        return $this;
    }

    /**
     * Validate form.
     *
     * @return Form
     */
    public function validate() {
        $this->_errors = array();
        foreach ($this->_fields as $field)
            if ($field->hasErrors())
            $this->_errors[$field->name] = $field->getErrors();
        return $this;
    }

    /**
     * Empty form fields.
     *
     * @param array $fields Fields names to empty value.
     *     NULL to empty all form fields.
     * @param boolean $force_empty Force empty value.
     * @return Form
     */
    public function emptyFields(array $fields = null, $force_empty = false) {
        foreach ($this->_fields as $field) {
            if (is_null($fields) || in_array($field->name, $fields)) {
                $field->emptyValue($force_empty);
            }
        }
        return $this;
    }

    /**
     * Get form field.
     *
     * @param string $field_name Field name.
     * @return FormField|null
     */
    public function getField($field_name) {
        return $this->hasField($field_name) ? $this->_fields[$field_name] : null;
    }

    public function __get($key) {
        return $this->getField($key);
    }

    public function __set($key, $value) {
        $this->getField($key)->value = $value;
    }

    public function __isset($key) {
        return !is_null($this->getField($key));
    }

    /**
     * Form field ID.
     *
     * @return FormField
     */
    public function getFieldFormId() {
        return $this->getField(self::FORM_ID);
    }

    /**
     * Returns TRUE if field name exists in this form.
     *
     * @param string $field_name Field name to check.
     * @return boolean
     */
    public function hasField($field_name) {
        return isset($this->_fields[$field_name]);
    }

    /**
     * Remove field.
     *
     * @param string $field_name Field name to check.
     * @return Form
     */
    public function removeField($field_name) {
        if (isset($this->_fields[$field_name])) {
            $this->_fields[$field_name] = null;
            unset($this->_fields[$field_name]);
        }
        return $this;
    }

    /**
     * Get form file field.
     * Alias of Form::getField to use 'FormFieldFile'.
     *
     * @param string $field_name File field name.
     * @return FormFieldFile|null
     * @see Form::getField()
     */
    public function getFieldFile($field_name) {
        return $this->getField($field_name);
    }

    /**
     * Get form field.
     *
     * @param string $field_name
     * @return FormField
     * @see Form::getField
     */
    public function __invoke($field_name) {
        return $this->getField($field_name);
    }

    /**
     * Get form errors.
     *
     * @return array Array of strings with error messages.
     */
    public function getErrors() {
        if ($this->_errors === false)
            $this->validate();
        return $this->_errors;
    }

    /**
     * Form has errors?
     *
     * @return boolean TRUE if form has errors.
     */
    public function hasErrors() {
        return count($this->getErrors()) > 0;
    }

    /**
     * Form is valid?
     *
     * @return boolean TRUE if form is valid.
     */
    public function isValid() {
        return $this->hasData() && !$this->hasErrors();
    }

    /**
     * Create HTML field.
     *
     * @param string $name Field name.
     * @param string|Runnable $html Field HTML content.
     *  Runnable::run = function($name, array $attrs, array $extra) {
     *      return 'FIELD HTML';
     *  }
     * @param string|boolean $label Field label.
     *     FALSE to remove field container.
     *     NULL to remove field label.
     * @param array $attrs Optional. Attributes.
     * @param array $extra Optional. Extras.
     * @param boolean $auto_value. Auto set value?
     * @return string Field HTML.
     */
    public function addHtml($name, $html = null, $label = null, array $attrs = null, array $extra = null, $auto_value = true) {
        if (is_array($name)) {
            $name['render'] = false;
            $n = $name['name'];
        } else {
            $n = $name;
        }
        if (emptyCheck($n)) throw new Exception('"name" must be setted.');
        $this->_fields[$n] = FormField::Html($name, $html, $label, $attrs, $extra, false, $auto_value);
        return $this;
    }

    /**
     * Add input field to form.
     *
     * @param string $name Field name.
     * @param string|boolean $label Field label.
     *     FALSE to remove field container.
     *     NULL to remove field label.
     * @param string $type Optional. Input type.
     *     If string is 'hidden', then $label = false.
     *     If not setted:
     *         If $name == 'password'
     *             then  type = 'password'
     *             else type = 'text'.
     *     If string is 'file', the method form is setted to 'file'.
     * @param array $attrs Optional. Attributes.
     * @param array $validations Optional. Validations.
     *     If not setted, calculate validatios.
     * @param boolean $auto_value. Auto set value?
     * return Form
     */
    public function addInput($name, $label = false, $type = null, array $attrs = null, array $validations = null, $auto_value = true) {
        if (is_array($name)) $name['render'] = false;
        if ((is_array($name) && (isset($name['type'])) && (strtolower($name['type']) == 'file')) || (strtolower($type) == 'file')) {
            // Input file
            $this->addInputFile($name, $label, $attrs, $validations);
        } else {
            if (is_array($name)) {
                $name['render'] = false;
                $n = $name['name'];
            } else {
                $n = $name;
            }
            if (emptyCheck($n)) throw new Exception('"name" must be setted.');
            $this->_fields[$n] = FormField::Input($name, $label, $type, $attrs, $validations, false, $auto_value);
        }
        return $this;
    }
    /**
     * Add input file field to form.
     *
     * @param string $name Field name.
     * @param string|boolean $label Field label.
     *     FALSE to remove field container.
     *     NULL to remove field label.
     * @param string $type Optional. Input type.
     *     If string is 'hidden', then $label = false.
     *     If not setted:
     *         If $name == 'password'
     *             then  type = 'password'
     *             else type = 'text'.
     *     If string is 'file', the method form is setted to 'file'.
     * @param array $attrs Optional. Attributes.
     * @param array $validations Optional. Validations.
     *     If not setted, calculate validatios.
     * return Form
     */
    public function addInputFile($name, $label = false, array $attrs = null, array $validations = null) {
        $this->setMethodFILE();
        $n = null;
        if (is_array($name)) {
            $name['render'] = false;
            $n = $name['name'];
        } else {
            $n = $name;
        }
        if (emptyCheck($n)) throw new Exception('"name" must be setted.');
        $this->_fields[$n] = FormFieldFile::InputFile($name, $label, $attrs, $validations, false);
        return $this;
    }

    /**
     * Add hidden input.
     *
     * @param string $name Field name.
     * @param string $value Value.
     * @param boolean $render Optional. TRUE to render value.
     * @return Form
     */
    public function addHidden($name, $value) {
        $this->_fields[$name] = FormField::Hidden($name, $value, false);
        return $this;
    }

    /**
     * Add TextArea field to form.
     *
     * @param string $name Field name.
     * @param string|boolean $label Field label.
     *     FALSE to remove field container.
     *     NULL to remove field label.
     * @param array $attrs Optional. Attributes.
     * @param array $validations Optional. Validations.
     * @param boolean $auto_value. Auto set value?
     * return Form
     */
    public function addTextarea($name, $label = false, array $attrs = null, array $validations = null, $auto_value = true) {
        if (is_array($name)) {
            $name['render'] = false;
            $n = $name['name'];
        } else {
            $n = $name;
        }
        if (emptyCheck($n)) throw new Exception('"name" must be setted.');
        $this->_fields[$n] = FormField::TextArea($name, $label, $attrs, $validations, false, $auto_value);
        return $this;
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
     * @param boolean $auto_value. Auto set value?
     * @return string Field HTML.
     */
    public function addSelect($name, $label = false, array $options = array(), $selected = null, array $attrs = null, array $validations = null, $auto_value = true) {
        if (is_array($name)) {
            $name['render'] = false;
            $n = $name['name'];
        } else {
            $n = $name;
        }
        if (emptyCheck($n)) throw new Exception('"name" must be setted.');
        $this->_fields[$n] = FormField::Select($name, $label, $options, $selected, $attrs, $validations, false, $auto_value);
        return $this;
    }

    /**
     * Add Submit button field to form.
     *
     * @param string $value Optional. Field value/text.
     * @param string $name Optional. Field name.
     * @param array $attrs Optional. Attributes.
     *     If not setted, calculate validatios.
     * return Form
     */
    public function addSubmitButton($value = null, $name = null, array $attrs = null) {
        if (emptyCheck($name)) $name = '_submit_form_' . self::$_form_submit_id++;
        if (emptyCheck($value)) {
            if (is_null($attrs)) {
                $attrs = array('value' => false);
            } else {
                $attrs['value'] = false;
            }
        } elseif (is_null($attrs)) {
            $attrs = array('value' => $value);
        } else {
            $attrs['value'] = $value;
        }
        $fld                  = FormField::Input($name, null, 'submit', $attrs, null, false, false);
        $fld->cont_class      = FormField::CONT_CLASS_ACTION;
        $this->_fields[$name] = $fld;
        return $this;
    }
}