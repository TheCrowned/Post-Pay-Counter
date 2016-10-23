<?php

/*  Copyright 2015  Matthew Van Andel  (email : matt@mattvanandel.com)
*/


/*************************** LOAD THE BASE CLASS *******************************
 * The WP_List_Table class isn't automatically available to plugins, so we need
 * to check if it's available and load it if necessary. In this tutorial, we are
 * going to use the WP_List_Table class directly from WordPress core.
 */
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


/************************** CREATE A PACKAGE CLASS *****************************
 *******************************************************************************
 * Create a new list table package that extends the core WP_List_Table class.
 * WP_List_Table contains most of the framework for generating the table, but we
 * need to define and override some methods so that our data can be displayed
 * exactly the way we need it to be.
 *
 * To display this example on a page, you will first need to instantiate the class,
 * then call $yourInstance->prepare_items() to handle any data manipulation, then
 * finally call $yourInstance->display() to render the table to the page.
 */
class Post_Pay_Counter_Authors_List_Table extends WP_List_Table {

	var $data; //hold formatted stats
	var $raw_data; //holds raw stats
	var $columns; //holds formatted stats columns
	var $perm; //holds PPC_permissions instance
	var $current_author_id; //holds current author id in table tr

    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct( $stats_data ) {
        global $status, $page;

        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'author',     //singular name of the listed records
            'plural'    => 'authors',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );

		$this->perm = new PPC_permissions();
		
		$this->data = $stats_data['formatted_stats']['stats'];
		$this->columns = $stats_data['formatted_stats']['cols'];
		$this->raw_data = $stats_data['raw_stats'];
    }


    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title()
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as
     * possible.
     *
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     *
     * For more detailed insight into how columns are handled, take a look at
     * WP_List_Table::single_row_columns()
     *
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name) {
		global $current_user;

		if( $column_name == 'author_id' AND isset( $item[$column_name] ) )
			$this->current_author_id = $item[$column_name];
		
		if( isset( $item[$column_name] ) ) {
			$field_value = $item[$column_name];

			//Cases in which other stuff needs to be added to the output
			switch( $column_name ) {
				case 'author_name':
					if( ( $this->perm->can_see_others_detailed_stats() OR $this->current_author_id == $current_user->ID ) )
						$field_value = '<a href="'.PPC_general_functions::get_the_author_link( $this->current_author_id ).'" title="'.__( 'Go to detailed view' , 'post-pay-counter').'">'.$field_value.'</a>';
					break;

				case 'author_total_payment':
					//Avoid tooltip non-isset notice
					if( isset( $this->raw_data[$this->current_author_id]['total']['ppc_misc']['tooltip_normal_payment'] ) )
						$tooltip = $this->raw_data[$this->current_author_id]['total']['ppc_misc']['tooltip_normal_payment'];
					else
						$tooltip = '';

						$field_value = '<abbr title="'.$tooltip.'" class="ppc_payment_column">'.PPC_general_functions::format_payment( $field_value ).'</abbr>';
					break;

				case 'author_words':
				case 'author_visits':
				case 'author_images':
				case 'author_comments':
					$label_field_name = substr($column_name, 7, strlen($column_name));
					if($this->raw_data[$this->current_author_id]['total']['ppc_count']['normal_count'][$label_field_name]['real'] != $this->raw_data[$this->current_author_id]['total']['ppc_count']['normal_count'][$label_field_name]['to_count'] )
						$field_value = '<abbr title="Total is '.$this->raw_data[$this->current_author_id]['total']['ppc_count']['normal_count'][$label_field_name]['real'].'&#13;'.__( 'Displayed is what you\'ll be paid for.', 'post-pay-counter' ).'" class="ppc_count_column">'.$field_value.'</abbr>';
					break;
			}

			$field_value = apply_filters( 'ppc_general_stats_html_each_field_value', $field_value, $column_name, $this->raw_data[$this->current_author_id] );

		} else {
			//Retrocompatibility for PRO HTML columns added directly to table
			ob_start();
			do_action( 'ppc_general_stats_html_after_each_default', $this->current_author_id, $this->data, $this->raw_data );
			$added_items = ob_get_clean();
			$added_items = array_filter( explode( '</td>', $added_items ) );

			if( $column_name == 'author_pay_field' )
				$field_value = substr( $added_items[0], 4 );
			else if( $column_name == 'author_payment_history' )
				$field_value = substr( $added_items[1], 35 );
			else		
				$field_value = apply_filters( 'ppc_general_stats_each_field_empty_value', 'N.A.', $column_name ).'</td>';
		}

		return $field_value;
    }
    

    /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     *
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['ID']                //The value of the checkbox should be the record's id
        );
    }


    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     *
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     *
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns(){
        $columns = array(
            //'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'title'     => 'Title'
        );
	
		$columns = $this->columns;

		//Retrocompatibility for PRO HTML columns added directly to table
		ob_start();
		do_action( 'ppc_general_stats_html_cols_after_default' );
		$added_cols = ob_get_clean();
		$added_cols = array_filter( explode( '</th>', $added_cols ) );

		if( ! empty( $added_cols ) ) {
			$columns['author_pay_field'] = substr( $added_cols[0], 16 );
			$columns['author_payment_history'] = substr( $added_cols[1], 16 );
		}
		
        return $columns;
    }


    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle),
     * you will need to register it here. This should return an array where the
     * key is the column that needs to be sortable, and the value is db column to
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     *
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     *
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {

		//return array();
		
        $sortable_columns = array(
            'author_id'     => array('author_id',false),     //true means it's already sorted
            'author_name'    => array('author_name',false),
            'author_total_payment'  => array('author_total_payment',false)
        );
        return apply_filters( 'ppc_stats_author_sortable_columns', $sortable_columns );
    }


    /** ************************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     *
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     *
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     *
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {
		return array();
        $actions = array(
            'delete'    => 'Delete'
        );
        return $actions;
    }


    /** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     *
     * @see $this->prepare_items()
     **************************************************************************/
    function process_bulk_action() {

        //Detect when a bulk action is being triggered...
        if( 'delete'===$this->current_action() ) {
            wp_die('Items deleted (or they would be if we had items to delete)!');
        }

    }


    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     *
     * @global WPDB $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items() {
        global $wpdb; //This is used only if making any database queries

        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 5000;


        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();


        /**
         * REQUIRED. Finally, we build an array to be used by the class for column
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         * 
         * $this->get_column_info(); retrieves user-hidden columns through Screen Options.
         */
        $this->_column_headers = $this->get_column_info();


        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        //$this->process_bulk_action();


        /**
         * Instead of querying a database, we're going to fetch the example data
         * property we created for use in this plugin. This makes this example
         * package slightly different than one you might build on your own. In
         * this example, we'll be using array manipulation to sort and paginate
         * our data. In a real-world implementation, you will probably want to
         * use sort and pagination data to build a custom query instead, as you'll
         * be able to use your precisely-queried data immediately.
         */
        $data = $this->data;
        

        /**
         * This checks for sorting input and sorts the data in our array accordingly.
         *
         * In a real-world situation involving a database, you would probably want
         * to handle sorting by passing the 'orderby' and 'order' values directly
         * to a custom query. The returned data will be pre-sorted, and this array
         * sorting technique would be unnecessary.
         */
		if( isset( $_REQUEST['orderby'] ) AND isset( $_REQUEST['order'] ) ) {
			function usort_reorder($a, $b) {
				$result = strnatcasecmp( $a[$_REQUEST['orderby']], $b[$_REQUEST['orderby']] ); //Determine sort order
				return ( $_REQUEST['order'] === 'asc' ) ? $result : -$result; //Send final sort direction to usort
			}
			usort($data, 'usort_reorder');
		}


        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently
         * looking at. We'll need this later, so you should always include it in
         * your own package classes.
         */
        //$current_page = $this->get_pagenum();

        /**
         * REQUIRED for pagination. Let's check how many items are in our data array.
         * In real-world use, this would be the total number of items in your database,
         * without filtering. We'll need this later, so you should always include it
         * in your own package classes.
         */
        //$total_items = count($data);


        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to
         */
        //$data = array_slice($data,(($current_page-1)*$per_page),$per_page);



        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where
         * it can be used by the rest of the class.
         */
        $this->items = $data;


        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        /*$this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );*/
    }
}
