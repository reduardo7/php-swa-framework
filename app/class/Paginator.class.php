<?php

/**
 * Paginator.
 *
 * @license Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @author Eduardo Daniel Cuomo <eduardo.cuomo.ar@gmail.com>
 * @version 1.0
 * @package ar.com.eduardocuomo.class.paginator
 * @copyright Eduardo Daniel Cuomo
 */
class Paginator {

    /**
     * Default rows per page.
     *
     * @var integer
     */
    const DEFAULT_ROWS_PER_PAGE = 10;

    /**
     * Page number param.
     *
     * @var integer
     */
    const PARAM_PAGE = 'pag';

    /**
     * Sort order.
     *
     * @var string
     */
    const PARAM_ORDER = 'order';

    /**
     * Sort order: Asc.
     *
     * @var string
     */
    const PARAM_ORDER_ASC = 'ASC';

    /**
     * Sort order: Desc.
     *
     * @var string
     */
    const PARAM_ORDER_DESC = 'DESC';

    /**
     * Sort by.
     *
     * @var string
     */
    const PARAM_SORT = 'sort';

    /**
     * Paginator ID.
     *
     * @var string
     */
    const PARAM_PAGINATOR_ID = '_pid';

    /**
     * Start row.
     *
     * @var integer
     */
    protected $start;

    /**
     * Rows in page.
     *
     * @var integer
     */
    protected $_rows_per_page;

    /**
     * Total pages.
     *
     * @var integer
     */
    protected $_total_pages = null;

    /**
     * Total rows.
     *
     * @var integer
     */
    protected $_total_rows = null;

    /**
     * Query.
     *
     * @var string
     */
    protected $_query;

    /**
     * Connection.
     *
     * @var Cnx
     */
    protected $_cnx;

    /**
     * Current page rows.
     *
     * @var array
     */
    protected $_rows;

    /**
     * Current record number.
     *
     * @var integer
     */
    protected $_current_record = -1;

    /**
     * Parameters.
     *
     * @var Params
     */
    protected $_params;

    /**
     * Using order?
     *
     * @var boolean
     */
    protected $_using_order;

    /**
     * Model Class for record set.
     *
     * @var string
     */
    protected $_model = 'Model';

    /**
     * Fixed current page number.
     * If it is setted, page parameter is not used.
     *
     * @var integer
     */
    protected $_current_page_number = null;

    protected $_sort_col = null;

    /**
     * Paginator ID.
     *
     * @var integer
     */
    protected static $_ID = 0;

    /**
     * Create new model paginator.
     *
     * @param array $query Query without limit. Use as Cnx::CreateQuery.
     * @param boolean $use_order Use order column?
     *     If TRUE, the Query $query should not have "ORDER BY".
     * @param integer $rows_per_page Rows per page.
     * @param string $model Model name to use. If not setted, use common Model class.
     *
     * @see Cnx::CreateQuery
     */
    public function __construct(array $query, $use_order = false, $rows_per_page = self::DEFAULT_ROWS_PER_PAGE, $model = null) {
        self::$_ID++;
        $this->_cnx         = new Cnx();
        $this->_query       = array_change_key_case($query, CASE_UPPER);
        $this->_using_order = $use_order;
        $this->_params      = new Params();
        $this->Model($model);

        $this->setRowsPerPage($rows_per_page);
    }

    /**
     * Total rows count.
     *
     * @return integer
     */
    public function count() {
        if (is_null($this->_total_rows)) {
            // Total
            $cqq = $this->_query;
            $cqq['SELECT'] = '1 AS _countRows';

            // Check if Debug
            if (in_array(Cnx::DEBUG_KEY, $cqq)) {
                // Remove Debug
                foreach ($cqq as $k => $v) {
                    if ($v == Cnx::DEBUG_KEY) {
                        unset($cqq[$k]);
                    }
                }
            }

            // Count
            $cq = Cnx::CreateQuery($cqq);
            $this->_cnx->query("SELECT COUNT(_qCountPaginator._countRows) AS c FROM ({$cq}) AS _qCountPaginator");
            $this->_total_rows = $this->_cnx->result('c');
        }
        return $this->_total_rows;
    }

    /**
     * Add Query Where.
     *
     * @param array|string $where
     * @return Paginator
     */
    public function addWhere($where) {
        $this->_total_rows       = null;
        $this->_query['WHERE'][] = $where;
        return $this;
    }

    /**
     * Set current page number.
     * Page parameter is not used.
     *
     * @param integer $page_number Current page number.
     * @return Paginator
     */
    public function setPageNumber($page_number) {
        $this->_current_page_number = $page_number;
        return $this;
    }

    /**
     * Set rows per page.
     *
     * @param integer $rows_per_page Rows per page.
     * @return Paginator
     */
    public function setRowsPerPage($rows_per_page) {
        $this->_rows_per_page = $rows_per_page;
        return $this;
    }

    /**
     * Set/Get model name.
     *
     * @param string $model_name Set Model Class.
     * @return string Current Model Class.
     */
    public function Model($model_clazz = null) {
        if (notEmptyCheck($model_clazz)) {
            $this->_model = $model_clazz;
        }
        return $this->_model;
    }

    /**
     * Get order.
     *
     * @param boolean $return_default Return default order if not setted.
     * @return string|null Order [ASC] or [DESC].
     */
    public function getOrder($return_default = true) {
        $h = $this->paramsForThis() && isset($_GET[self::PARAM_ORDER]) && in_array($_GET[self::PARAM_ORDER], array(self::PARAM_ORDER_ASC, self::PARAM_ORDER_DESC));
        if ($return_default || $h) {
            return $h ? $_GET[self::PARAM_ORDER] : self::PARAM_ORDER_ASC;
        } else {
            return null;
        }
    }

    /**
     * URL parameters are for this paginator?
     * (for multi-paginator in same page).
     *
     * @return boolean TRUE if URL parameters are for this paginator.
     */
    public function paramsForThis() {
        return isset($_GET[self::PARAM_PAGINATOR_ID]) && ($_GET[self::PARAM_PAGINATOR_ID] == self::$_ID);
    }

    /**
     * Get sort column.
     *
     * @return string Sort column.
     */
    public function getSortColumn() {
        if (is_null($this->_sort_col)) {
            $this->_sort_col = ($this->paramsForThis() && isset($_GET[self::PARAM_SORT])) ? $_GET[self::PARAM_SORT] : null;
        }
        return $this->_sort_col;
    }

    /**
     * Set sort column name.
     *
     * @param string $sort_col Column name.
     * @return Paginator
     */
    public function setSortColumn($sort_col) {
        $this->_sort_col = $sort_col;
        return $this;
    }

    /**
     * Get current page number.
     *
     * @return integer Page number.
     */
    public function getPageNumber() {
        if (is_null($this->_current_page_number)) {
            $this->_current_page_number = ($this->paramsForThis() && isset($_GET[self::PARAM_PAGE]) && is_numeric($_GET[self::PARAM_PAGE])) ? intval($_GET[self::PARAM_PAGE]) : 1;
            $this->getTotalPages();
            $this->_current_record = -1;
        }
        return $this->_current_page_number;
    }

    /**
     * Get page rows.
     *
     * @return array Model rows.
     */
    public function getPageRows() {
        $this->_getRows();
        return $this->_rows;
    }

    /**
     * Create order by head Link.
     * Returns '#' if not setted as 'Using Order'.
     *
     * @param string $column_name Column name for Order By.
     * @return string Link.
     */
    public function headLink($column_name) {
        if ($this->_using_order) {
            // Get curren order
            $order = $this->getOrder();
            $this->_addCommonParams();
            // Order by
            $this->_params->setParam(self::PARAM_SORT, $column_name);
            // Order
            $this->_params->setParam(self::PARAM_ORDER, ($order == self::PARAM_ORDER_ASC) ? self::PARAM_ORDER_DESC : self::PARAM_ORDER_ASC);
            // Generate link
            $lnk = $this->_params->toString();
            // Restore current order
            $this->_params->setParam(self::PARAM_ORDER, $order);
            // Return link
            return $lnk;
        } else {
            // Paginator not defined
            return '#';
        }
    }

    /**
     * Create order by head Link with title.
     *
     * @param string $column_name Column name for Order By.
     * @param string $text Column text.
     */
    public function headTitle($column_name, $text) {
        echo '<a href="' . $this->headLink($column_name) . '">';
        $o = $this->getOrder(false);
        $s = $this->getSortColumn();
        if (($column_name == $s) && ($o == self::PARAM_ORDER_ASC)) {
            echo '<i class="icon-chevron-down"></i> ';
        } elseif (($column_name == $s) && ($o == self::PARAM_ORDER_DESC)) {
            echo '<i class="icon-chevron-up"></i> ';
        }
        HTML::ToHtml($text);
        echo '</a>';
    }

    /**
     * Returns and render URL parameter for page number.
     *
     * @param integer $page_number Page number.
     * @param boolean $render Render?
     * @return string URL parameter for next page.
     */
    public function urlToPage($page_number, $render = false) {
        if (is_null($page_number) || ($page_number < 1)) $page_number = 1;
        if ($page_number > $this->getLastPage()) $page_number = $this->getLastPage();
        $this->_addCommonParams();
        $this->_params->setParam(self::PARAM_PAGE, $page_number);
        $p = $this->_params->toString();
        if ($render) echo $p;
        return $p;
    }

    /**
     * Returns and render URL parameter for next page.
     *
     * @param boolean $render Render?
     * @return string URL parameter for next page.
     */
    public function urlNext($render = false) {
        return $this->urlToPage(($this->hasNextPage() ? $this->getNextPage() : 1), $render);
    }

    /**
     * Returns and render URL parameter for next page.
     *
     * @param boolean $render Render?
     * @return string URL parameter for next page.
     */
    public function urlPrev($render = false) {
        return $this->urlToPage(($this->hasPrevPage() ? $this->getPrevPage() : $this->getLastPage()), $render);
    }

    /**
     * Returns next record, or FALSE if not has record.
     *
     * @return Model|boolean
     */
    public function getNextRecord() {
        if ($this->hasNextRecord()) {
            $this->_current_record++;
            return $this->getCurrentRecord();
        } else {
            return false;
        }
    }

    /**
     * Returns current record.
     *
     * @param string $model_clazz Model class name.
     * @return ModelBase
     */
    public function getCurrentRecord($model_clazz = null) {
        $this->_getRows();
        if (emptyCheck($model_clazz)) $model_clazz = $this->_model;
        return new $model_clazz($this->_rows[$this->_current_record]);
    }

    /**
     * Returns TRUE if has next record.
     *
     * @return boolean TRUE if has next record.
     */
    public function hasNextRecord() {
        $this->_getRows();
        return ($this->_current_record + 1) < count($this->_rows);
    }

    /**
     * Render paginator.
     *
     * @param string $cont_class Container class.
     * @param string $link_class Link class.
     * @param string $selected_class Selected page class.
     */
    public function render($cont_class = null, $link_class = null, $selected_class = null) {
        $this->count();
        $this->getPageNumber();
        $this->getTotalPages();
        if ($this->_total_rows > $this->_rows_per_page) {
            if (!is_null($cont_class)) {
                $cont_class = " class=\"{$cont_class}\"";
            }
            if (!is_null($link_class)) {
                $link_class = " class=\"{$link_class}\"";
            }
            if (!is_null($selected_class)) {
                $selected_class = " class=\"{$selected_class}\"";
            }

            echo "<div class=\"pagination\"{$cont_class}>";

            $page = $this->_current_page_number - 1;
            if ($page > 0) {
                $lnk = $this->urlPrev();
                echo "<a href=\"{$lnk}\"{$link_class}>&lt; Anterior</a>";
            }
            $puntosX = true;
            for ($p = 1; $p <= $this->_total_pages; $p++) {
                $ver = false;
                $puntos = false;
                $LimSup = $this->_total_pages - 3;
                if (($p <= 3) || ($p > $LimSup)) { //Primeros y ultimos 3
                    $ver = true;
                } else {
                    $puntos = true;
                    $LimMInf = $current_page - 3;
                    $LimMSup = $current_page + 3;
                    if (($LimMInf < $p) && ($p < $LimMSup)) {
                        $ver = true;
                        if ($LimSup >= $LimMInf) $puntos = false;
                    } else {
                        $puntos = true;
                    }
                }

                if ($ver) { // Escribe Num. de current_page.
                    $puntosX = true;
                    if ($this->_current_page_number == $p) {
                        echo "<span{$selected_class}>{$p}</span>";
                    } else {
                        $lnk = $this->urlToPage($p);
                        echo "<a href=\"{$lnk}\"{$link_class}>{$p}</a>";
                    }
                } else { // Write
                    if($puntos && $puntosX){
                        echo '...';
                        $puntosX = false;
                    }
                }
            }
            if (($this->_current_page_number + 1) <= $this->_total_pages) {
                $lnk = $this->urlNext();
                echo "<a href=\"{$lnk}\"{$link_class}>Siguiente &gt;</a>";
            }
            echo '</div>';
        }
    }

    /**
     * Returns first row number.
     *
     * @return integer Row number.
     */
    public function getFirstRowNumber() {
        return $this->start;
    }

    /**
     * Returns TRUE if has next page.
     *
     * @return boolean TRUE if has next page.
     */
    public function hasNextPage() {
        return $this->getPageNumber() < $this->getTotalPages();
    }

    /**
     * Returns TRUE if has resutl.
     *
     * @return boolean TRUE if has result.
     */
    public function hasResults() {
        $this->count();
        return $this->_total_rows > 0;
    }

    /**
     * Returns next page number.
     *
     * @return integer Page number.
     */
    public function getNextPage() {
        return $this->getPageNumber() + 1;
    }

    /**
     * Returns TRUE if has previus page.
     *
     * @return boolean TRUE if has previus page.
     */
    public function hasPrevPage() {
        return $this->getPageNumber() > 1;
    }

    /**
     * Returns previous page number.
     *
     * @return integer Page number.
     */
    public function getPrevPage() {
        return $this->getPageNumber() - 1;
    }

    /**
     * Returns number of total pages.
     *
     * @return integer Number of pages.
     */
    public function getTotalPages() {
        $this->_total_pages = ceil($this->getTotalRows() / $this->getRowsPerPage());
        if ($this->_total_pages < 1) $this->_total_pages = 1;
        return $this->_total_pages;
    }

    /**
     * Returns number of total rows.
     *
     * @return integer Number of rows.
     */
    public function getTotalRows() {
        $this->count();
        return $this->_total_rows;
    }

    /**
     * Returns number of rows per page.
     *
     * @return integer Rows per page.
     */
    public function getRowsPerPage() {
        return $this->_rows_per_page;
    }

    /**
     * Returns last page number.
     *
     * @return integer Last page number.
     */
    public function getLastPage() {
        return $this->_total_pages;
    }

    protected function _addCommonParams() {
        $this->_params->setParam(self::PARAM_PAGINATOR_ID, self::$_ID);
    }

    protected function _getRows() {
        $this->getPageNumber();
        $this->getTotalPages();
        $rqq = $this->_query;
        // Fix page number
        if ($this->_current_page_number > $this->_total_pages) {
            $this->_current_page_number = $this->_total_pages;
        } elseif ($this->_current_page_number < 1) {
            $this->_current_page_number = 1;
        }

        if (!is_numeric($this->_current_page_number) || ($this->_current_page_number < 1)) {
            $this->start = 0;
            $this->_current_page_number = 1;
        } else {
            $this->start = ($this->_current_page_number - 1) * $this->_rows_per_page;
        }

        // Order
        if ($this->_using_order) {
            $sort_column = $this->getSortColumn();
            if (!is_null($sort_column)) {
                $rqq['ORDER'] = $sort_column;
            }
        }

        // Rows
        $rqq['LIMIT'] = array($this->_rows_per_page, $this->start);
        $rq = Cnx::CreateQuery($rqq);
        $this->_cnx->query($rq);
        $this->_rows = $this->_cnx->result();
    }
}