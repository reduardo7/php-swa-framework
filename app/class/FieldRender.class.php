<?php

/**
 * Form field render.
 *
 * @license Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @author Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @version 1.0
 * @package ar.com.eduardocuomo.class.form
 * @copyright Eduardo Daniel Cuomo
 */
abstract class FieldRender extends Runnable {
    /**
     * Run.
     *
     * @param FormField $field
     * @return string
     */
    public function run($field = null) {
        return $this->render($field);
    }

    /**
     * Render.
     *
     * @param FormField $field
     * @return string
     */
    public abstract function render($field);
}

/**
 * Input form field render.
 *
 * @license Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @author Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @version 1.0
 * @package ar.com.eduardocuomo.class.form
 * @copyright Eduardo Daniel Cuomo
 */
class FormFieldRunnableInput extends FieldRender {
    public function render($field) {
        $name = $field->name;
        $attrs = $field->attrs;

        // Start tag
        $html = "<input name=\"$name\" id=\"$name\"";

        // Value
        if (!is_null($field->value) && ($field->value != '')) {
            $attrs['value'] = htmlspecialchars($field->value);
        } elseif (isset($attrs['value'])) {
            if ($attrs['value'] === false) {
                unset($attrs['value']);
            } else {
                $attrs['value'] = htmlspecialchars($attrs['value']);
            }
        } else {
            $attrs['value'] = '';
        }

        // Other attributes
        $html .= FormField::__attributes($attrs);

        // Close tag
        $html .= ' />';

        return $html;
    }
}

/**
 * TextArea form field render.
 *
 * @license Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @author Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @version 1.0
 * @package ar.com.eduardocuomo.class.form
 * @copyright Eduardo Daniel Cuomo
 */
class FormFieldRunnableTextArea extends FieldRender {
    public function render($field) {
        $name = $field->name;
        $attrs = $field->attrs;

        $text_value = '';

        // Start tag
        $html = "<textarea name=\"$name\" id=\"$name\"";

        // Value
        if (!is_null($field->value) && ($field->value != '')) {
            $text_value = htmlspecialchars($field->value);
            if (isset($attrs['value'])) {
                unset($attrs['value']);
            }
        } elseif (isset($attrs['value'])) {
            $text_value = htmlspecialchars($attrs['value']);
            unset($attrs['value']);
        }

        // Other attributes
        $html .= FormField::__attributes($attrs);

        // Close tag
        $html .= ">$text_value</textarea>";

        return $html;
    }
}

/**
 * Select form field render.
 *
 * @license Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @author Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @version 1.0
 * @package ar.com.eduardocuomo.class.form
 * @copyright Eduardo Daniel Cuomo
 */
class FormFieldRunnableSelect extends FieldRender {
    public function render($field) {
        $id = $name     = $field->name;
        $attrs          = $field->attrs;
        $extra          = $field->extra;
        $emptyOption    = '';

        if (!is_null($field->value) && ($field->value != '')) {
            $selected = $field->value;
        } else {
            $selected = $extra['selected'];
        }

        if (isset($attrs['options']) && is_array($attrs['options'])) {
            foreach ($attrs['options'] as $k => $v) {
                $emptyOption = FormField::__option($k, $v, $selected) ;
            }
            unset($attrs['options']);
        }

        // Multi-Select
        if (isset($attrs['multiple']) && $attrs['multiple']) {
            $attrs['multiple'] = 'true';
            $name .= '[]';
            if (!isset($attrs['size'])) $attrs['size'] = 5;
        }

        $html = "<select name=\"$name\" id=\"$id\"" . FormField::__attributes($attrs) . '>';
        $html .= $emptyOption;
        $html .= FormField::__options($extra['options'], $selected);
        $html .= '</select>';
        return $html;
    }
}