<?php
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}



require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
require_once ABSPATH . 'wp-admin/includes/screen.php';
require_once ABSPATH . 'wp-admin/includes/template.php';

class My_Post_List_Table extends WP_List_Table
{
    public function prepare_items()
    {
        global $wpdb;
        
        // Set up the query to fetch data from your custom table
        $table_name = $wpdb->prefix . 'crop_epaper';
        $query = "SELECT paper_id, paper_title, MIN(paper_file) as paper_file, DATE(created_at) as date FROM $table_name GROUP BY paper_title, DATE(created_at)";
        $results = $wpdb->get_results($query, ARRAY_A);

        $data = array();
        foreach ($results as $result) {
            $data[] = array(
                'paper_id' => $result['paper_id'],
                'paper_title' => $result['paper_title'],
                'paper_file' => '<img src="' . $result['paper_file'] . '" style="max-width: 100px;" />',
                'date'=>$result['date'],
                'delete' => "Delete"
            );
        }        
        // Sort the data based on the request
        $orderby = isset($_REQUEST['orderby']) ? $_REQUEST['orderby'] : 'paper_id';
        $order = isset($_REQUEST['order']) ? $_REQUEST['order'] : 'asc';
        usort($data, function ($a, $b) use ($orderby, $order) {
            $result = strnatcasecmp($a[$orderby], $b[$orderby]);
            return $order === 'asc' ? $result : -$result;
        });

        // Set the pagination parameters
        $per_page = 10;
        $current_page = $this->get_pagenum();
        $total_items = count($data);

        // Slice the data based on the pagination parameters
        $data = array_slice($data, (($current_page - 1) * $per_page), $per_page);

        // Set the items and the column headers for the table
        $this->items = $data;
        $columns = $this->get_columns();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, array(), $sortable);
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ));
    }

    public function get_sortable_columns()
    {
        return array(
            'paper_id' => array('paper_id', true),
            'paper_title' => array('paper_title', false),
            'date' => array('date', false),
        );
    }

    public function get_columns()
    {
        $columns = array(
            'paper_id' => "ID",
            'paper_title' => "Paper Title",
            'paper_file' => "News Paper",
            'date' => "Date",
            'delete' => "Delete"
        );

        return $columns;
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'paper_id':
            case 'paper_title':
            case 'paper_file':
            case 'date':
                return $item[$column_name];
            case 'delete':
                return '<button class="btn btn-danger"><a href="?page=' . $_GET["page"] . '&action=ep-del&paper_id=' . $item['paper_id'] . '">Delete</a></button>';
            default:
                return '';
        }
    }
   
 
}

// Instantiate the table and display it
$table = new My_Post_List_Table();
$table->prepare_items();
$table->display();
