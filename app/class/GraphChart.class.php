<?php

/**
 * Graph Chart Generator.
 *
 * @author Eduardo Cuomo <eduardo.cuomo.ar@gmail.com>.
 *
 * @example
 * $graph = new GraphChart('Graph Title');
 * $graph->add('Name 1', array(
 *        'Label 1' => 48,
 *        'Label 2' => 125,
 *        'Label 3' => 159,
 *        'Label 4' => 147,
 *        'Label 5' => 154,
 *        'Label 6' => 114,
 *        'Label 7' => 163,
 *        'Label 8' => 122,
 *        'Label 9' => 96
 *    ))
 *    ->add('Name 2', array(
 *        'Label 1' => 8,
 *        'Label 2' => 27,
 *        'Label 3' => 0,
 *        'Label 4' => 79,
 *        'Label 5' => 47,
 *        'Label 6' => 59,
 *        'Label 7' => 80,
 *        'Label 8' => 30,
 *        'Label 9' => 70
 *    ))
 *    ->add('Name 3', array(
 *        'Label 1' => 28,
 *        'Label 2' => 56,
 *        'Label 3' => 98,
 *        'Label 4' => 112,
 *        'Label 5' => 87,
 *        'Label 6' => 26,
 *        'Label 7' => 38,
 *        'Label 8' => 110,
 *        'Label 9' => 20
 *    ))
 *    ->add('Name 4', array(
 *        'Label 1' => 38,
 *        'Label 2' => 43,
 *        'Label 3' => 69,
 *        'Label 4' => 54,
 *        'Label 5' => 16,
 *        'Label 6' => 16,
 *        'Label 7' => 202,
 *        'Label 8' => 20,
 *        'Label 9' => 73
 *    ));
 */
class GraphChart extends APP_Base {

    protected $_title;
    protected $_image_src = null;
    protected $_height = null;

    /**
     * Values.
     *
     * Example:
     *  array(
     *      'Name' => array(
     *          'X Label 1' => VALUE,
     *          'X Label 2' => 213,
     *          'X Label 3' => 112,
     *          ...
     *      ),
     *      ...
     *  )
     *
     * @var array
     */
    protected $_values = array();

    /**
     * Constructor.
     *
     * @param string $title Graph title.
     */
    function __construct($title = '') {
        $this->_title = $title;
    }

    /**
     * Set graph title.
     *
     * @param string $title Graph title.
     * @return GraphChart
     */
    public function setTitle($title) {
        $this->_title = $title;
        return $this;
    }

    /**
     * Set title image.
     *
     * @param string $src Image SRC.
     * @return GraphChart
     */
    public function setImage($src) {
        $this->_image_src = $src;
        return $this;
    }

    public function setHeight($height) {
        $this->_height = $height;
    }

    /**
     * Add values.
     *
     * @param string $name Values name.
     * @param array $values Values.
     *  Example:
     *  array(
     *      'X Label 1' => VALUE,
     *      'X Label 2' => 213,
     *      'X Label 3' => 112,
     *      ...
     *  )
     * @return GraphChart
     */
    public function add($name, array $values) {
        $this->_values[$name] = $values;
        return $this;
    }

    /**
     * Render table.
     */
    public function render() {
        $labels = array();
        $title  = $this->_title;
        $style  = '';

        if ($this->_image_src) {
            $title = "<img src=\"{$this->_image_src}\" alt=\"\" /> $title";
        }

        if ($this->_height) {
            $style = "height:{$this->_height};";
        }

        foreach ($this->_values as $values) {
            foreach ($values as $label => $value) {
                if (!in_array($label, $labels)) {
                    $labels[] = $label;
                }
            }
        }

        echo '<div class="box"><div class="header">' .
            "<h2>{$title}</h2>" .
            "<div class=\"content\" style=\"{$style}\">" .
            '<table class="chart styled borders" events="{click:funcion(){console.log(\'test\');}}"><thead><tr><th></th>';
        foreach ($labels as $label) {
            echo "<th>{$label}</th>";
        }
        echo '</tr></thead><tbody>';
        foreach ($this->_values as $name => $values) {
            echo "<tr><th>{$name}</th>";
            foreach ($values as $label => $value) {
                echo '<td>' . (in_array($label, $labels) ? $value : 0) . '</td>';
            }
            echo '</tr>';
        }
        echo '</tbody></table></div></div></div>';
    }
}