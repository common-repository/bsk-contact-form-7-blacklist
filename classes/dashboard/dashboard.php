<?php

class CF7_Blacklist_Dashboard {
    
    public static $_cf7_blacklist_settings_option = '_cf7_blacklist_settings_option_';
	public static $_cf7_blacklist_page = 'bsk-cf7-blacklist';
	
	private $_cf7_blacklist_dashboard_error_message = '';
		
	public $_cf7_blacklist_OBJ_blacklist_list = NULL;
    public $_cf7_blacklist_OBJ_updater = NULL;
	public $_cf7_blacklist_OBJ_update_helper = NULL;
    public $_cf7_blacklist_OBJ_form_panel = NULL;
    
    private static $_pro_tips_for_lists = array( 
                                                                'White List',
                                                                'Email List',
                                                                'Import from CSV',
                                                                'Export to CSV'
                                                               );

	public function __construct() {
		global $wpdb;
		
		require_once( 'dashboard-list.php' );
		require_once( 'dashboard-lists.php' );
		require_once( 'dashboard-items.php' );
        require_once( 'dashboard-form-panel.php' );
        
		$this->_cf7_blacklist_OBJ_blacklist_list = new CF7_Blacklist_Dashboard_List();		
		$this->_cf7_blacklist_OBJ_form_panel = new CF7_Blacklist_Dashboard_Form_Panel();
            
        /*
          * Actions & Filters
          */
		add_action( 'admin_menu', array( $this, 'cf7_blacklist_dashboard_menu' ), 999 );
		add_action( 'cf7_blacklist_delete_saved_form_list', array( $this, 'cf7_blacklist_delete_saved_form_list_fun' ) );
		
		add_action( 'admin_notices', array($this, 'cf7_blacklist_admin_notice_fun') );        
        add_filter( 'set-screen-option', array( $this, 'cf7_blacklist_set_option' ), 10, 3);
	}
	
	function cf7_blacklist_admin_notice_fun(){
		if( !$this->_cf7_blacklist_dashboard_error_message ){
			return;
		}
		?>
		<div class="notice notice-error">
			<p><?php echo $this->_cf7_blacklist_dashboard_error_message; ?></p>
		</div>
		<?php
		$this->_cf7_blacklist_dashboard_error_message = '';
	}
	
	function cf7_blacklist_dashboard_menu() {
		
		$authorized_level = 'level_10';
		
		//read plugin settings
		$plugin_settings = get_option( self::$_cf7_blacklist_settings_option, '' );
		

        $cf7_blacklist_menu_hook = add_submenu_page( 'wpcf7',
                                                                          'Blacklist', 
                                                                          'Blacklist',
                                                                          $authorized_level, 
                                                                          self::$_cf7_blacklist_page,
                                                                          array($this, 'cf7_blacklist_lists_table') );
        if( $cf7_blacklist_menu_hook ){
            add_action( "load-$cf7_blacklist_menu_hook", array( $this, 'cf7_blacklist_screen_option_fun' ) );
        }
	}
	
	function cf7_blacklist_lists_table(){
		
		$current_view = 'list';
		if(isset($_GET['view']) && $_GET['view']){
			$current_view = trim($_GET['view']);
		}
		if(isset($_POST['view']) && $_POST['view']){
			$current_view = trim($_POST['view']);
		}
		$current_list_view = ( !empty($_REQUEST['listview']) ? $_REQUEST['listview'] : 'blacklist');
		$current_list_view_str = 'Blacklist';
		if( $current_list_view == 'whitelist' ){
			$current_list_view_str = 'White List';
		}else if( $current_list_view == 'emaillist' ){
			$current_list_view_str = 'Email List';
		}
		
		$current_base_page = admin_url( 'admin.php?page='.self::$_cf7_blacklist_page.'&listview='.$current_list_view );
		
		if( $current_list_view == 'help' ){
            require_once( 'dashboard-help.php' );
            
			$cf7_blacklist_help = new CF7_Blacklist_Dashboard_Help();
            $cf7_blacklist_help->show_help();
			
			return;
		}
		
		if ($current_view == 'list'){
			
            $_cf7_blacklist_OBJ_blacklist_lists = new CF7_Blacklist_Dashboard_Lists();
			
			//Fetch, prepare, sort, and filter our data...
			$_cf7_blacklist_OBJ_blacklist_lists->prepare_items();
			
			$add_new_page_url = add_query_arg( 'view', 'addnew', $current_base_page );
			echo '<div class="wrap">
					<div id="icon-edit" class="icon32"><br/></div>
					<h2>Contact Form 7 Blacklist - '.$current_list_view_str.'<a href="'.$add_new_page_url.'" class="add-new-h2">Add New</a></h2>';
            
            $this->bsk_pdf_manager_show_pro_tip_box( self::$_pro_tips_for_lists );
            
			echo '<form id="cf7_blacklist_lists_form_id" method="post" action="'.$current_base_page.'">';
						$_cf7_blacklist_OBJ_blacklist_lists->views();
						$_cf7_blacklist_OBJ_blacklist_lists->display();
			echo '  
						<input type="hidden" name="cf7_blacklist_list_id" id="cf7_blacklist_list_id_to_be_processed_ID" value="0" />
                		<input type="hidden" name="cf7_blacklist_action" id="cf7_blacklist_action_ID" value="" />';
						wp_nonce_field( 'cf7_blacklist_list_oper_nonce', 'cf7_blacklist_list_oper_nonce' );
			echo '
					</form>
				  </div>';
		}else if ( $current_view == 'addnew' || $current_view == 'edit'){
			$list_id = -1;
			if(isset($_GET['id']) && $_GET['id']){
				$list_id = trim($_GET['id']);
				$list_id = intval($list_id);
			}
			$this->_cf7_blacklist_OBJ_blacklist_list->cf7_blacklist_list_edit( $list_id, $current_list_view, self::$_cf7_blacklist_page );
		}
	}
	
	function cf7_blacklist_delete_saved_form_list_fun(){
		
		//check nonce field
		if ( !wp_verify_nonce( $_POST['cf7_blacklist_saved_form_lists_oper_nonce'], 'cf7_blacklist_saved_form_lists_oper_nonce' ) ){
			wp_die( 'Security check!' );
			return;
		}

		$form_id = $_POST["cf7_blacklist_form_list_form_id"];
		$form_id = (int)$form_id;
		if( $form_id < 1 ){
			wp_die( 'Invalid form ID' );
			return;
		}
		
		delete_option( $this->_cf7_blacklist_form_list_data_option_name.$form_id );
	}
	
    function cf7_blacklist_screen_option_fun(){
        $option = 'per_page';
        
        $list_edit_view = isset($_GET['view']) ? $_GET['view'] : '';
        $label = 'Lists per page';
        $option_name = 'cf7_blacklist_lists_per_page';
        if( $list_edit_view == 'edit' ){
            $label = 'Items per list';
            $option_name = 'cf7_blacklist_items_per_page';
        }
            
        $args = array(
            'label' => $label,
            'default' => 20,
            'option' => $option_name
        );

        add_screen_option( $option, $args );
    }

    function cf7_blacklist_set_option($status, $option, $value) {

        if ( 'cf7_blacklist_lists_per_page' == $option || 
             'cf7_blacklist_items_per_page' == $option ){
            
            return $value;
        } 
        
        return $status;
    }
    
    function bsk_pdf_manager_show_pro_tip_box( $tips_array ){
        $tips = implode( ', ', $tips_array );
		$str = 
        '<div class="bsk-pro-tips-box">
			<b>Pro Tip: </b><span class="bsk-pro-tips-box-tip">'.$tips.' only supported in Pro version</span>
			<a href="'.CF7_Blacklist::$_url_to_upgrade.'" target="_blank">Upgrade to Pro</a>
		</div>';
		
		echo $str;
	}
    
    
}
