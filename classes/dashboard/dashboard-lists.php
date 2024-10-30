<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class CF7_Blacklist_Dashboard_Lists extends WP_List_Table {
   
	private $_cf7_blacklist_list_type = '';
    private $_cf7_blacklist_list_id = 0;
    private $_cf7_blacklist_list_view = 0;
    
    function __construct() {
		global $wpdb;
		
		//Set parent defaults
		parent::__construct( array( 
								'singular' => 'bsk-cf7-blacklist-lists',  //singular name of the listed records
								'plural'   => 'bsk-cf7-blacklist-lists', //plural name of the listed records
								'ajax'     => false                          //does this table support ajax?
								) 
						   );
		
		$this->_cf7_blacklist_list_view = ( !empty($_REQUEST['listview']) ? $_REQUEST['listview'] : 'blacklist');
		$this->_cf7_blacklist_list_type = 'BLACK_LIST';
		if( $this->_cf7_blacklist_list_view == 'whitelist' ){
			$this->_cf7_blacklist_list_type = 'WHITE_LIST';
		}else if( $this->_cf7_blacklist_list_view == 'emaillist' ){
			$this->_cf7_blacklist_list_type = 'EMAIL_LIST';
		}
    }

    function column_default( $item, $column_name ) {
        switch( $column_name ) {
			case 'id':
				echo $item['id_link'];
				break;
			case 'list_name':
				echo $item['list_name'];
				break;
			case 'items_count':
				echo $item['items_count'];
				break;	
            case 'date':
                echo $item['date'];
                break;
			case 'action':
				echo $item['action'];
                break;
        }
    }
   
    function column_cb( $item ) {
        return sprintf( 
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            esc_attr( $this->_args['singular'] ),
            esc_attr( $item['id'] )
        );
    }

    function get_columns() {
    
        $columns = array( 
			'cb'        		=> '<input type="checkbox"/>',
			'id'				=> 'ID',
            'list_name'     	=> 'List Name',
			'items_count'     	=> 'Items Count',
            'date' 				=> 'Date',
			'action' 			=> 'Action'
        );
        
        return $columns;
    }
   
	function get_sortable_columns() {
		$c = array(
					'list_name' => 'list_name',
					'date'    	=> 'date'
					);
		
		return $c;
	}
	
     function get_views() {
		$views = array();

		//blacklist link
		$blacklist_url = add_query_arg('listview','blacklist');
		$class = $this->_cf7_blacklist_list_view == 'blacklist' ? ' class="current"' :'';
		$views['blacklist'] = '<a href="'.$blacklist_url.'" '.$class.'>Blacklist</a>';
		
		//white list link
		$whitelist_url = add_query_arg('listview','whitelist');
		$class = $this->_cf7_blacklist_list_view == 'whitelist' ? ' class="current"' :'';
		$views['whitelist'] = '<a href="'.$whitelist_url.'" '.$class.'>White List</a>';
		
		//Email list link
		$emaillist_url = add_query_arg('listview','emaillist');
		$class = $this->_cf7_blacklist_list_view == 'emaillist' ? ' class="current"' :'';
		$views['emaillist'] = '<a href="'.$emaillist_url.'" '.$class.'>Email List</a>';
		
         //Help
        $help_url = add_query_arg('listview','help');
        $class = $current_list_view == 'help' ? ' class="current"' :'';
        $views['help'] = '<a href="'.$help_url.'" '.$class.'>Help</a>';
         
        return $views;
    }
   
    function get_bulk_actions() {
    
        $actions = array( 
            //'delete'=> 'Delete'
        );
        
        return $actions;
    }

    function do_bulk_action() {
    }

    function get_data() {
		global $wpdb;
		
		$search = '';
		$orderby = '';
		$order = '';
        // check to see if we are searching
        if( isset( $_POST['s'] ) ) {
            $search = trim( $_POST['s'] );
        }
		if ( isset( $_REQUEST['orderby'] ) ){
			$orderby = $_REQUEST['orderby'];
		}
		if ( isset( $_REQUEST['order'] ) ){
			$order = $_REQUEST['order'];
		}
		
		$sql = 'SELECT * FROM '.$wpdb->prefix.CF7_Blacklist::$_list_tbl_name.' AS l WHERE l.`list_type` = %s ';
		if( $search ){
			$sql .= ' AND l.list_name LIKE %s';
			$sql = $wpdb->prepare( $sql, $this->_cf7_blacklist_list_type, '%'.$search.'%' );
		}else{
			$sql = $wpdb->prepare( $sql, $this->_cf7_blacklist_list_type );
		}
		$orderCase = ' ORDER BY l.date DESC';
		if ( $orderby ){
			$orderCase = ' ORDER BY l.'.$orderby.' '.$order;
		}
		$lists = $wpdb->get_results($sql.$orderCase);
		if (!$lists || count($lists) < 1){
			return NULL;
		}
		$list_page_url = admin_url( 'admin.php?page='.CF7_Blacklist_Dashboard::$_cf7_blacklist_page );
		
		$lists_data = array();
		foreach ( $lists as $list ) {
            $sql = $wpdb->prepare( 'SELECT COUNT(*) FROM `'.$wpdb->prefix.CF7_Blacklist::$_items_tbl_name.'` WHERE `list_id` = %d', $list->id );
			$items_count = $wpdb->get_var( $sql );
            
            //list edit
			$list_edit_url = add_query_arg( 
                                            array(
                                                    'listview' => $this->_cf7_blacklist_list_view,
												    'view' 	 => 'edit', 
										 		    'id' 		 => $list->id
                                            ),
											$list_page_url );
            $list_eidt_anchor = '<a class="cf7-blacklist-action-anchor cf7-blacklist-action-anchor-first cf7-blacklist-admin-edit-list" href="'.$list_edit_url.'">Manage Items</a>';
            
			//list delete
			$list_delete_url = add_query_arg( array('view' 	=> 'delete', 
										 		                     'id' 	=> $list->id),
											                $list_page_url );
            
			$delete_anchor = '<a class="cf7-blacklist-action-anchor cf7-blacklist-admin-delete-list" '.
							 'rel="'.$list->id.'" count="'.$items_count.'">Delete</a>';
			
			//organise data
			$lists_data[] = array( 
			    'id' 				  => $list->id,
				'id_link' 			=> '<a href="'.$list_edit_url.'">'.$list->id.'</a>',
				'list_name'       => '<a href="'.$list_edit_url.'">'.$list->list_name.'</a>',
				'date'				=> date('Y-m-d', strtotime($list->date)),
				'action'		    => $list_eidt_anchor.$delete_anchor,
				'items_count'	=> $items_count
			);
		}
		
		return $lists_data;
    }

    function prepare_items() {
       
        /**
         * First, lets decide how many records per page to show
         */
        $user = get_current_user_id();
        $screen = get_current_screen();
        $option = $screen->get_option('per_page', 'option');
        $per_page = get_user_meta($user, $option, true);
        if ( empty ( $per_page) || $per_page < 1 ) {
            $per_page = $screen->get_option( 'per_page', 'default' );
        }
        
        $data = array();
		
        add_thickbox();

		$this->do_bulk_action();
       
        $data = $this->get_data();
   
        $current_page = $this->get_pagenum();
        $total_items = 0;
        if( $data && is_array( $data ) && count( $data ) > 0 ){
            $total_items = count( $data );
        }
       
	    if ($total_items > 0){
        	$data = array_slice( $data,( ( $current_page-1 )*$per_page ),$per_page );
		}
        $this->items = $data;

        $this->set_pagination_args( array( 
            'total_items' => $total_items,                  // We have to calculate the total number of items
            'per_page'    => $per_page,                     // We have to determine how many items to show on a page
            'total_pages' => ceil( $total_items/$per_page ) // We have to calculate the total number of pages
        ) );
    }
	

	
	function get_column_info() {
		
		$columns = array( 
							'cb'        		=> '<input type="checkbox"/>',
							'id'				=> 'ID',
							'list_name'     	=> 'List Name',
							'items_count'     	=> 'Items Count',
							'date' 				=> 'Date',
							'action' 			=> 'Action'
						);
		
		$hidden = array();

		$_sortable = apply_filters( "manage_{$this->screen->id}_sortable_columns", $this->get_sortable_columns() );

		$sortable = array();
		foreach ( $_sortable as $id => $data ) {
			if ( empty( $data ) )
				continue;

			$data = (array) $data;
			if ( !isset( $data[1] ) )
				$data[1] = false;

			$sortable[$id] = $data;
		}

		$_column_headers = array( $columns, $hidden, $sortable, array() );

		return $_column_headers;
	}
}