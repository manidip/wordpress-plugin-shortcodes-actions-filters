<?php

/*
    "WordPress Plugin Template" Copyright (C) 2015 Michael Simpson  (email : michael.d.simpson@gmail.com)

    This file is part of WordPress Plugin Template for WordPress.

    WordPress Plugin Template is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    WordPress Plugin Template is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Contact Form to Database Extension.
    If not, see <http://www.gnu.org/licenses/>.
*/

// http://wpengineer.com/2426/wp_list_table-a-step-by-step-guide/

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

require_once('AddActionsAndFilters_AdminPageActions.php');

class AddActionsAndFilters_CodeListTable extends WP_List_Table
{
    /**
     * @var AddActionsAndFilters_DataModel
     */
    var $dataModel;

    var $actions;

    public function __construct(&$dataModel)
    {
        parent::__construct();
        $this->dataModel = $dataModel;
        $this->actions = new AddActionsAndFilters_AdminPageActions();
    }

    /**
     * The method get_columns() is needed to label the columns on the top and bottom of the table.
     * The keys in the array have to be the same as in the data array otherwise the respective
     * columns aren't displayed.
     * @return array
     */
    public function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'id' => 'ID',
            'enabled' => 'Enabled',
            'shortcode' => 'Shortcode',
            'name' => 'Name',
            'description' => 'Description'
        );
        return $columns;
    }

    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'id' => array('id', true),
            'enabled' => array('enabled', true),
            'shortcode' => array('shortcode', true),
            'name' => array('name', true),
            'description' => array('description', true)
        );
        return $sortable_columns;
    }

    public function get_bulk_actions()
    {
        return $this->actions->getBulkActionsKeyToDisplayStringArray();
    }


    public function prepare_items()
    {
        // Columns
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        // Pagination
        $totalItems = $this->dataModel->getNumberDataItems();
        $perPage = $this->dataModel->config->getNumberPerPage();
        $this->dataModel->config->setPage($this->get_pagenum());
        $this->set_pagination_args(array(
            'total_items' => $totalItems,
            'per_page' => $perPage,
            'total_pages' => ceil($totalItems / $perPage)
        ));

        // Data
        $this->items = $this->dataModel->getDataItemList();
    }

    /**
     * Display cell value - default view
     * @param object $item
     * @param string $column_name
     * @return mixed
     */
    public function column_default($item, $column_name)
    {
        $text = $item[$column_name];
        if (!$item['enabled']) {
            $text = $this->grayOutText($text);
        }
        return $text;
    }

    /**
     * @param $item array
     * @return array
     */
    public function createRowActions($item) {
        $url = sprintf('?page=%s&id=%s', $_REQUEST['page'], $item['id']);
        if (isset($_REQUEST['paged']) && $_REQUEST['paged']) {
            $url .= '&paged=' . $_REQUEST['paged'];
        }
        $url .= '&action=%s';
        $tag = '<a href="' . $url . '">%s</a>';

        $rowActions = array();
        if ($item['enabled']) {
            $action = $this->actions->getDeactivateStrings();
            $rowActions[$action->getKey()] = sprintf($tag, $action->getKey(), $action->getDisplay());
        } else {
            $action = $this->actions->getActivateStrings();
            $rowActions[$action->getKey()] = sprintf($tag, $action->getKey(), $action->getDisplay());
        }
        $action = $this->actions->getEditStrings();
        $rowActions[$action->getKey()] = sprintf($tag, $action->getKey(), $action->getDisplay());

        $action = $this->actions->getDeleteStrings();
        $rowActions[$action->getKey()] = sprintf($tag, $action->getKey(), $action->getDisplay());

        return $rowActions;
    }

    /**
     * @param $item array
     * @return string
     */
    public function column_name($item)
    {
        $text = $item['name'];
        if (!$item['enabled']) {
            $text = $this->grayOutText($text);
        }
        $rowActions = $this->createRowActions($item);
        return sprintf('%1$s %2$s', $text, $this->row_actions($rowActions));
    }

    /**
     * @param $item array
     * @return string
     */
    public function column_enabled($item)
    {
        $text = ($item['enabled']) ? '&#x2713;' : '&#x2715;';
        if (!$item['enabled']) {
            $text = $this->grayOutText($text);
        }
        return $text;
    }

    /**
     * @param $item array
     * @return string
     */
    public function column_shortcode($item)
    {
        $text = ($item['shortcode']) ? '[...]' : '';
        if (!$item['enabled']) {
            $text = $this->grayOutText($text);
        }
        return $text;
    }

    /**
     * @param $item array
     * @return string
     */
    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="cb[]" value="%s" />', $item['id']
        );
    }

    /**
     * Make text grayed-out in HTML to indicate it is disabled
     * @param $text string
     * @return string
     */
    public function grayOutText($text)
    {
        return '<span class="item-inactive">' . $text . '</span>';
    }


}