<?php

APP::GetInstance()
	->load('Paginator')
	->resourceInc('datatable');

/**
 * DataTable.
 *
 * @license Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @author Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @version 1.0
 * @package ar.com.eduardocuomo.class.datatable
 * @copyright Eduardo Daniel Cuomo
 */
class DataTable extends APP_Base {

	/**
	 * Prefix for automatic DataTable ID.
	 *
	 * @var string
	 */
	const TABLE_ID_PREFIX = '_datatable_';

	/**
	 * Limit records.
	 *
	 * @var integer
	 */
	const TABLE_RECORD_LIMIT = 100;

	const __JS_FUNCTION_ID = '[<!-- # -->]';

	/**
	 * Table number.
	 *
	 * @var integer
	 */
	protected static $_TABLE_NUMBER = 0;

	/**
	 * Table ID.
	 *
	 * @var integer
	 */
	protected $_id;

	/**
	 * Main APP Instance.
	 *
	 * @var APP
	 */
	protected $_app;

	/**
	 * DataTable definition.
	 *
	 * @var array
	 */
	protected $_definition = array(
		'bProcessing' => true,
		'bServerSide' => true
	);

	/**
	 * Columns.
	 *
	 *  Format:
	 *    array(
	 *      'columnName1' => 'Column Title 1',
	 *      'columnName2' => array(
	 *        'title' => 'Column Title', // Required if not used "model".
	 *        'model' => $model_instance, // ModelBase instance.
	 *        'visible' => true,
	 *        'searchable' => true,
	 *        'sortable' => true,
	 *        'type' => 'string'|'numeric'|'date'|'html'
	 *        'width' => '20%',
	 *        'column' => 'column_name',
	 *        'default' => 'Default value on NULL',
	 *        'class' => 'css_column_class',
	 *        'format' = 'return value;', // Content for function: function(value,row,data)
	 *      ),
	 *      ...
	 *    )
	 *
	 * @see DataTable::$COLUMNS_DEFAULTS
	 * @see DataTable::__constructor
	 *
	 * @var array
	 */
	protected $_columns;

	/**
	 * Paginator.
	 *
	 * @var Paginator
	 */
	protected $_paginator;

	/**
	 * Query.
	 *
	 * @see Cnx::CreateQuery
	 * @var array
	 */
	protected $_query;

	/**
	 * Search string.
	 *
	 * @var string
	 */
	protected $_search;

	/**
	 * Rows per page.
	 *
	 * @var integer
	 */
	protected $_rows_per_page = Paginator::DEFAULT_ROWS_PER_PAGE;

	/**
	 * Table columns.
	 *
	 * @param array $columns Table columns.
	 *  Format:
	 *    array(
	 *      'columnName' => 'Column Label',
	 *      'column2'    => array(
	 *        'title' => 'Column label', // Required!
	 *        ... // DataTable::$_columns
	 *      ),
	 *      ...
	 *    )
	 * @param array $query Query without limit. Use as Cnx::CreateQuery.
	 *
	 * @see DataTable::$_columns
	 * @see Cnx::CreateQuery
	 * @see Paginator
	 */
	function __construct(array $columns, array $query) {
		parent::__construct();
		self::$_TABLE_NUMBER++;

		$this->_id		= self::$_TABLE_NUMBER;
		$this->_app		= APP::GetInstance();
		$this->_columns	= array_change_key_case($columns, CASE_LOWER);
		$this->_query	= $query;
		$this->_search	= isset($_GET['sSearch']) ? $_GET['sSearch'] : '';

		// Initialize

		// Set current view as source
		$this->setSource($this->_app->params->toString());

		// Column properies translate
		$aoColumns = array();
		$kCol = 0;
		foreach ($this->_columns as $column_name => $props) {
			if (is_array($props) && count($props) > 0) {
				$p = array();
				// Model
				if (array_key_exists('model', $props)) {
					if ($props['model'] instanceof ModelBase) {
						if (!array_key_exists('title', $props)) {
							// Get title from Model
							$this->_columns[$column_name]['title'] = $props['title'] = $props['model']->getColumnLabel($column_name);
						}
					}
				}
				foreach ($props as $k => $v) {
					switch ($k) {
						case 'searchable':	$k = 'bSearchable';		break;
						case 'sortable':	$k = 'bSortable';		break;
						case 'visible':		$k = 'bVisible';		break;
						case 'title':		$k = 'sTitle';			break;
						case 'type':		$k = 'sType';			break;
						case 'width':		$k = 'sWidth';			break;
						case 'column':		$k = 'sName';			break;
						case 'default':		$k = 'sDefaultContent';	break;
						case 'class':		$k = 'sClass';			break;
						/**
						 * fnRender is DEPRECATED.
						 *
						 * Will be removed in the next version of DataTables.
						 * Please use mRender / mData rather than fnRender.
						 *
						 * @deprecated
						 * @link http://datatables.net/usage/columns
						 */
						case 'format':
							$k = 'fnRender';
							$v = self::__JS_FUNCTION_ID .
							'function(d){var r=d.aData;return (function(value,row,data){' .
							$v . '}).call(this,r[' . $kCol . '],r,d);}' . self::__JS_FUNCTION_ID;
							break;
					}
					$p[$k] = $v;
				}
				$aoColumns[] = $p;
			} else {
				// No data
				$aoColumns[] = null;
			}
			// Next column number
			$kCol++;
		}
		$this->_definition['aoColumns'] = $aoColumns;
	}

	/**
	 * Set rows per page / display length.
	 *
	 * @param integer $rows_per_page Rows per page / display length.
	 * @return DataTable
	 */
	public function setRowsPerPage($rows_per_page) {
		$this->_rows_per_page = $rows_per_page;
		$this->_definition['iDisplayLength'] = $rows_per_page;
		return $this;
	}

	/**
	 * Set rows per page / display length.
	 *
	 * @param integer $rows_per_page Rows per page / display length.
	 * @return DataTable
	 */
	public function setDisplayLength($rows_per_page) {
		return $this->setRowsPerPage($rows_per_page);
	}

	public function setSearch($search_string) {
		$this->_search = $search_string;
		$this->_definition['oSearch'] = $search_string;
		return $this;
	}

	/**
	 * Get DataTable display start.
	 *
	 * @return integer
	 */
	public function getDisplayStart() {
		return $this->hasDisplayStart() ? intval($_GET['iDisplayStart']) : 0;
	}

	/**
	 * Has DataTable display start?
	 *
	 * @return boolean
	 */
	public function hasDisplayStart() {
		return isset($_GET['iDisplayStart']);
	}

	/**
	 * Get DataTable display length.
	 *
	 * @return integer
	 *
	 * @see DataTable::hasDisplayLength()
	 */
	public function getDisplayLength() {
		return $this->hasDisplayLength() ? intval($_GET['iDisplayLength']) : self::TABLE_RECORD_LIMIT;
	}

	/**
	 * Has DataTable display length?
	 *
	 * @return boolean
	 */
	public function hasDisplayLength() {
		return isset($_GET['iDisplayLength']) && ($_GET['iDisplayLength'] != '-1');
	}

	/**
	 * Get DataTable search string.
	 *
	 * @return string
	 */
	public function getSearch() {
		return $this->_search;
	}

	/**
	 * Has DataTable search string?
	 *
	 * @return boolean
	 */
	public function hasSearch() {
		return isset($_GET['sSearch']) && (trim($_GET['sSearch']) != '');
	}

	/**
	 * Set Ajax source.
	 *
	 * @param string $url
	 */
	public function setSource($url) {
		$this->_definition['sAjaxSource'] = $url;
		return $this;
	}

	/**
	 * Use pagination?
	 *
	 * @param boolean $enable TRUE to enable pagination.
	 * @return DataTable
	 */
	public function usePagination($enable) {
		$this->_definition['bPaginate'] = !!$enable;
		return $this;
	}

	/**
	 * Use search filter?
	 *
	 * @param boolean $enable TRUE to enable filter.
	 * @return DataTable
	 */
	public function useFilter($enable) {
		$this->_definition['bFilter'] = !!$enable;
		return $this;
	}

	/**
	 * Use page length selector?
	 *
	 * @param boolean $enable TRUE to enable lenght selector.
	 * @return DataTable
	 */
	public function usePageLenghtSelector($enable) {
		$this->_definition['bLengthChange'] = !!$enable;
		return $this;
	}

	/**
	 * Use Auto-Width?
	 *
	 * @param boolean $enable TRUE to enable Auto-Width.
	 * @return DataTable
	 */
	public function autoWidth($enable) {
		$this->_definition['bAutoWidth'] = !!$enable;
		return $this;
	}

	/**
	 * Show DataTable info?
	 *
	 * @param boolean $enable TRUE to show DataTable info.
	 * @return DataTable
	 */
	public function showInfo($show) {
		$this->_definition['bInfo'] = !!$show;
		return $this;
	}

	/**
	 * DataTable HTML table ID.
	 *
	 * @return string
	 */
	public function getTableId() {
		return self::TABLE_ID_PREFIX . $this->_id;
	}

	/**
	 * Get DataTable definition.
	 *
	 * @param boolean $return_as_js Return as valid JavaScript?
	 * @return array|string
	 */
	public function getDataTableDefinition($return_as_js = false) {
		if ($return_as_js) {
			$a = '"' . self::__JS_FUNCTION_ID;
			$b = self::__JS_FUNCTION_ID . '"';
			$def = json_encode($this->_definition);
			while (!(strpos($def, self::__JS_FUNCTION_ID) === false)) {
				// Convert functions strings to functions
				$ia = strpos($def, $a) + strlen($a);
				$o = json_decode('{"fn":"' . substr($def, $ia, strpos($def, $b) - $ia) . '"}');
				$def = substr($def, 0, strpos($def, $a)) . $o->fn . substr($def, strpos($def, $b) + strlen($b));
				// Memory free
				unset($o);
			}
			return $def;
		} else {
			return $this->_definition;
		}
	}

	/**
	 * Returns TRUE if an Ajax call.
	 *
	 * @return boolean
	 */
	public function isAjax() {
		return isset($_GET['sEcho']);
	}

	/**
	 * Get JSON DataTable Data.
	 *
	 * @return array
	 */
	public function getJsonData() {
		$this->_getRows();

		$records = array();
		while ($this->_paginator->hasNextRecord()) {
			$row = $this->_paginator->getNextRecord();
			$r = array();
			foreach (array_keys($this->_columns) as $col) {
				$r[] = $row->get($col);
			}
			$records[] = $r;
		}

		$iTotal = $this->_paginator->getTotalRows();

		return array(
			'sEcho'					=> intval($_GET['sEcho']),
			'iTotalRecords'			=> $iTotal,
			'iTotalDisplayRecords'	=> $iTotal,
			'aaData'				=> $records
		);
	}

	/**
	 * Render HTML table.
	 *
	 * @param boolean $render_javascript_tag Render JavaScript tag
	 *  with DataTable initialization.
	 * @param boolean $return Default: FALSE. Return output?
	 * @return string|null
	 */
	public function renderTable($render_javascript_tag = false, $return = false) {
		// Start
		$html = "<table id=\"{$this->getTableId()}\">";
		// Header
		$html .= '<thead>';
		foreach ($this->_columns as $column_name => $v) {
			$html .= HTML::Tag(
				'th', is_array($v) ? (array_key_exists('title', $v) ? $v['title'] : "[{$column_name}]") : $v, array(), true);
		}
		$html .= '</thead>';
		// Body
		$html .= '<tbody></tbody>';
		// End
		$html .= '</table>';
		// JavaScript
		$html .= $this->renderJavaScript(true, true);
		if ($return) {
			return $html;
		} else {
			echo $html;
		}
	}

	/**
	 * Render JSON for Ajax.
	 *
	 * @param boolean $clean_output Clean current output buffer?
	 */
	public function renderJson($clean_output = true) {
		// Clean current output
		if ($clean_output) {
			$this->_app->outputClean();
		}
		// Build JSON
		echo json_encode($this->getJsonData());
		// End
		die();
	}

	/**
	 * Render DataTable initialization JavaScript.
	 *
	 * @param boolean $render_tag Render HTML "script" tag?
	 * @param boolean $return Default: FALSE. Retrurn result and no print?
	 * @return string|null
	 */
	public function renderJavaScript($render_tag = false, $return = false) {
		$html = '';
		if ($render_tag) {
			$html = '<script type="text/javascript">';
		}
		$html .= "$('#{$this->getTableId()}').dataTable({$this->getDataTableDefinition(true)});";
		if ($render_tag) {
			$html .= '</script>';
		}
		if ($return) {
			return $html;
		} else {
			echo $html;
		}
	}

	protected function _getRows() {
		$cols = array_keys($this->_columns);

		// Paginator
		$this->_paginator = new Paginator($this->_query, true, $this->getDisplayLength());

		// Paging
		if ($this->hasDisplayStart() && $this->hasDisplayLength()) {
			$this->_paginator
			->setRowsPerPage($this->getDisplayLength())
			->setPageNumber(floor($this->getDisplayStart() / $this->getDisplayLength()) + 1);
		}

		// Order
		if (isset($_GET['iSortCol_0'])) {
			$order = array();
			for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
				if ($_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == 'true') {
					$order[$cols[intval($_GET['iSortCol_' . $i])]] = $_GET['sSortDir_' . $i];
				}
			}
			$this->_paginator->setSortColumn($order);
		}

		// Search
		if ($this->hasSearch()) {
			$this->_paginator->addWhere(Cnx::SearchString($cols, $this->getSearch()));
		}

		// Individual column filtering
		for ($i = 0; $i < count($cols); $i++) {
			if (($_GET['bSearchable_' . $i] == 'true') && ($_GET['sSearch_' . $i] != '')) {
				$this->_paginator->addWhere(Cnx::SearchString($cols[$i], $_GET['sSearch_' . $i]));
			}
		}
	}
}