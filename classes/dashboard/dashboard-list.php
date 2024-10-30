<?php

class CF7_Blacklist_Dashboard_List {
	
	public function __construct() {
		global $wpdb;
		
		require_once( 'dashboard-items.php' );
		
		add_action( 'cf7_blacklist_action_save_list', array($this, 'cf7_blacklist_save_list_fun') );
		add_action( 'cf7_blacklist_action_save_item', array($this, 'cf7_blacklist_save_item_fun') );
		add_action( 'cf7_blacklist_action_delete_item', array($this, 'cf7_blacklist_delete_item_fun') );
		add_action( 'cf7_blacklist_action_delete_list_by_id', array($this, 'cf7_blacklist_delete_list_by_id_fun') );
	}
	
	function cf7_blacklist_list_edit( $list_id = -1, $list_view, $page_name ){
		global $wpdb;
		
		$list_type = 'BLACK_LIST';
		$list_title = 'Blacklist';
        $disabled_anchor_class = '';
        $pro_tips_for_list_edit = array( 'Import from CSV', 'Export to CSV' );
		if( $list_view == 'whitelist' ){
			$list_type = 'WHITE_LIST';
			$list_title = 'White List';
            $pro_tips_for_list_edit = array( 'White List', 'Import from CSV', 'Export to CSV' );
            $disabled_anchor_class = ' cf7-blacklist-anchor-disabled';
		}else if( $list_view == 'emaillist' ){
			$list_type = 'EMAIL_LIST';
			$list_title = 'Email List';
            $pro_tips_for_list_edit = array( 'Email List', 'Import from CSV', 'Export to CSV' );
            $disabled_anchor_class = ' cf7-blacklist-anchor-disabled';
		}
		
		$list_name = '';
		if ($list_id > 0){
			$sql = 'SELECT * FROM '.$wpdb->prefix.CF7_Blacklist::$_list_tbl_name.' WHERE id = %d AND `list_type` = %s';
			$sql = $wpdb->prepare( $sql, $list_id, $list_type );
			$list_obj_array = $wpdb->get_results( $sql );
			if (count($list_obj_array) > 0){
				$list_name = $list_obj_array[0]->list_name;
				$list_date = date( 'Y-m-d', strtotime($list_obj_array[0]->date) );
			}
		}
		
		$page_url = add_query_arg( 
                                                array( 
                                                    'view' => 'edit', 
                                                    'id' => $list_id, 
                                                    'listview' => $list_view
                                                ), 
                                                admin_url( 'admin.php?page='.$page_name )
                                               );
		?>
        <div class="wrap">
        	<div id="icon-edit" class="icon32"><br/></div>
            <h2>Contact Form 7 Blacklist - <?php echo $list_title; ?></h2>
            <?php $this->bsk_pdf_manager_show_pro_tip_box( $pro_tips_for_list_edit ); ?>
            <div>
            <?php
				$views = array();
				
				$base_url = admin_url( 'admin.php?page='.$page_name );
				
				//blacklist link
				$blacklist_url = add_query_arg('listview','blacklist', $base_url);
				$class = $list_view == 'blacklist' ? ' class="current"' :'';
				$views['blacklist'] = '<a href="'.$blacklist_url.'" '.$class.'>Blacklist</a>';
				
				//white list link
				$whitelist_url = add_query_arg('listview','whitelist', $base_url);
				$class = $list_view== 'whitelist' ? ' class="current"' :'';
				$views['whitelist'] = '<a href="'.$whitelist_url.'" '.$class.'>White List</a>';
				
				//Email list link
				$emaillist_url = add_query_arg('listview','emaillist', $base_url);
				$class = $list_view == 'emaillist' ? ' class="current"' :'';
				$views['emaillist'] = '<a href="'.$emaillist_url.'" '.$class.'>Email List</a>';
        
                //Help
                $help_url = add_query_arg('listview','help');
                $class = $current_list_view == 'help' ? ' class="current"' :'';
                $views['help'] = '<a href="'.$help_url.'" '.$class.'>Help</a>';
				
				echo "<ul class='subsubsub'>\n";
				foreach ( $views as $class => $view ) {
					$views[ $class ] = "\t<li class='$class'>$view";
				}
				echo implode( " |</li>\n", $views ) . "</li>\n";
				echo "</ul>";
			?>
            	<div style="clear:both;"></div>
            </div>
            <div class="cf7-blacklist-edit-list-container">
                <form id="cf7_blacklist_list_edit_form_id" method="post" action="<?php echo $page_url; ?>">
                <input type="hidden" name="page" value="<?php echo $page_name; ?>" />
				<?php if( isset($_GET['list_save']) && $_GET['list_save'] == 'succ' ){ ?>
                <div class="notice notice-success is-dismissible inline">
                    <p><?php echo ucfirst(strtolower($list_title)); ?> saved successfully</p>
                </div>
                <?php } ?>
                <?php if( $list_id < 1 ){ ?>
                <h3>Add New <?php echo $list_title; ?></h3>
                <?php }else{ ?>
                <h3>Edit <?php echo $list_title; ?></h3>
                <?php } ?>
                <p>
                    <label class="cf7-blacklist-admin-label">List Name: </label>
                    <input type="text" class="cf7-blacklist-add-list-input" name="cf7_blacklist_list_name" id="cf7_blacklist_list_name_ID" value="<?php echo $list_name; ?>" maxlength="512" />
                    <a class="cf7-blacklist-action-anchor<?php echo $disabled_anchor_class; ?>" id="cf7_blacklist_blacklist_list_save_ID" style="margin-left:20px;">Save</a>
                </p>
                <p>
                    <input type="hidden" name="cf7_blacklist_list_id" value="<?php echo $list_id; ?>" />
                    <input type="hidden" name="cf7_blacklist_list_type" id="cf7_blacklist_list_type_ID" value="<?php echo $list_type; ?>" />
                    <input type="hidden" name="cf7_blacklist_action" value="save_list" />
                    <?php wp_nonce_field( plugin_basename( __FILE__ ), 'cf7_blacklist_list_save_oper_nonce' ); ?>
                </p>
                </form>
            </div>
            <?php if( $list_id > 0 ){ ?>
            <p style="margin-top: 20px;">&nbsp;</p>
			<a id="cf7_blacklist_edit_items_conteianer_anchor">&nbsp;</a>
            <div class="cf7-blacklist-edit-item-container">
            	<?php if( isset($_GET['item_action']) && trim($_GET['item_action']) != "" ){ ?>
                <script type="text/javascript">
					jQuery(document).ready( function($) {
						$('html, body').animate({
						  scrollTop: $("#cf7_blacklist_edit_items_conteianer_anchor").offset().top
						}, 1000);
					});
				</script>
                <?php
					$notice_message = 'Successfully!';
					$notice_class 	 = 'notice-success';
					switch( $_GET['item_action'] ){
						case 'save_succ':
							$notice_message = 'Item saved successfully';
						break;
						case 'del_succ':
							$notice_message = 'Item deleted';
						break;
						case 'upload_csv_failed':
							$notice_message = 'Upload CSV file failed';
							$notice_class 	 = 'notice-error';
						break;
						case 'open_csv_failed':
							$notice_message = 'The CSV file cannot be open';
							$notice_class 	 = 'notice-error';
						break;
						case 'empty_csv':
							$notice_message = 'The CSV file is empty';
							$notice_class 	 = 'notice-error';
						break;
						case 'invalid_csv_type':
							$notice_message = 'The CSV file type is not right';
							$notice_class 	 = 'notice-error';
						break;
						case 'inserted_count':
							if( $_GET['inserted_count'] < 1 ){
								$notice_message = 'No item has been imported, please check you CSV file.';
								$notice_class 	 = 'notice-error';
								if( $list_type == 'EMAIL_LIST' ){
									$notice_message .= ' Only valid email address accepted.';
								}
							}else{
								$notice_message = $_GET['inserted_count'].' itmes has been imported successfully';
							}
						break;
					}
				?>
                <div class="notice <?php echo $notice_class; ?> is-dismissible inline">
                    <p><?php echo $notice_message; ?></p>
                </div>
                <?php } ?>
                <h3>Items:</h3>
                <form id="cf7_blacklist_items_form_id" method="post" action="<?php echo $page_url; ?>" enctype="multipart/form-data">
                <input type="hidden" name="page" value="<?php echo $page_name; ?>" />
                <?php if( $list_view == 'emaillist' ){ ?>
                <p style="margin-top:20px;">
                	<input type="checkbox" name="cf7_blacklist_add_email_domain_name_checkbox" id="cf7_blacklist_add_email_domain_name_checkbox_ID" value="YES" /><label for="cf7_blacklist_add_email_domain_name_checkbox_ID"> Add email domain name</label>
                </p>
                <p style="margin-top:20px;display:none;" id="cf7_blacklist_add_email_domain_name_input_container_ID" >
                    <label class="cf7-blacklist-admin-label">Add email domain name:</label> 
                    <input type="text" class="bsk-gfbl-add-item-input" name="cf7_blacklist_email_domain_name" id="cf7_blacklist_email_domain_name_ID" maxlength="512"/>
                    <a class="cf7-blacklist-action-anchor" id="cf7_blacklist_add_email_domain_name_save_anchor_ID" style="margin-left:20px;">Save</a>
                    <br />
                    <label class="cf7-blacklist-admin-label">&nbsp;</label>
                    <span style="display:inline-block;font-style: italic;">eg: *@gmail.com</span>
                </p>
                <?php } // end of if( $list_view == 'emaillist' ) ?>
                <p style="margin-top:20px;" id="cf7_blacklist_add_email_list_item_input_container_ID">
                    <label class="cf7-blacklist-admin-label">Add item by input:</label> 
                    <input type="text" class="bsk-gfbl-add-item-input" name="cf7_blacklist_add_item_by_input_name" id="cf7_blacklist_add_item_by_input_name_ID" maxlength="512" style="width: 80%;"/>
                    <a class="cf7-blacklist-action-anchor" id="cf7_blacklist_add_item_by_input_save_anchor_ID" style="margin-left:20px;">Save</a>
                </p>
                <p>
                    <label class="cf7-blacklist-admin-label">Add item by CSV:</label> 
                    <input type="file" name="cf7_blacklist_add_item_by_csv" id="cf7_blacklist_add_item_by_csv_ID" />
                    <a class="cf7-blacklist-action-anchor cf7-blacklist-anchor-disabled" id="cf7_blacklist_add_item_by_csv_save_anchor_ID" style="margin-left:20px;">Upload</a>
                    <input type="hidden" id="cf7_blacklist_add_item_by_csv_selected_file_ID" value="" />
                </p>
                <p>
                	<label class="cf7-blacklist-admin-label">&nbsp;</label>
                    <?php
					$template_url = CF7_BLACKLIST_URL.'assets/cf7-blacklist-tmpl.csv.zip';
					?>
                    <span style="font-style:italic;">In CSV file, the first column of every line will be take as a item, download <a href="<?php echo $template_url; ?>">template </a>here.</span>
                </p>
                <p style="margin-top: 20px;">&nbsp;</p>
                <?php
                $init_args['list_type']			= $list_type;
                $init_args['list_id'] 			= $list_id;

                $_cf7_blacklist_OBJ_items = new CF7_Blacklist_Dashboard_Items( $init_args );
                
                //Fetch, prepare, sort, and filter our data...
                $_cf7_blacklist_OBJ_items->prepare_items();
                $_cf7_blacklist_OBJ_items->search_box( 'search', 'cf7-blacklist-items-serch' );
				$_cf7_blacklist_OBJ_items->views();
				$_cf7_blacklist_OBJ_items->display();
				
				$this->show_export_as_csv_form( $list_id );
                ?>
                <input type="hidden" name="cf7_blacklist_list_id" value="<?php echo $list_id; ?>" />
                <input type="hidden" name="cf7_blacklist_action" id="cf7_blacklist_action_ID" value="" />
                <input type="hidden" name="cf7_blacklist_item_id" id="cf7_blacklist_item_id_ID" value="0" />
                <input type="hidden" name="cf7_blacklist_items_list_type" id="cf7_blacklist_items_list_type_ID" value="<?php echo $list_type; ?>" />
                <?php wp_nonce_field( plugin_basename( __FILE__ ), 'cf7_blacklist_item_save_oper_nonce' ); ?>
                </form>
            </div>
            <?php } ?>
        </div>
        <?php
	}
	
	function show_export_as_csv_form( $list_id ) {
		if( $list_id < 1 ){
			return;
		}
		global $wpdb;
		
		$sql = 'SELECT COUNT(*) FROM `'.$wpdb->prefix.CF7_Blacklist::$_items_tbl_name.'` AS i WHERE i.`list_id` = %d';
		$sql = $wpdb->prepare( $sql, $list_id );
		if( $wpdb->get_var( $sql ) < 1 ){
			return;
		}
	?>
    <div class="cf7-blacklist-admin-export-items-as-csv-div" style="margin-top:40px;">
        <h3>Items Export</h3>
        <p>
        	Click the export button below to download all items as a CSV file.
            <a class="cf7-blacklist-action-anchor cf7-blacklist-anchor-disabled" id="cf7_blacklist_export_items_as_CSV_anchor_ID">Export</a>
        </p>
        <?php wp_nonce_field( plugin_basename( __FILE__ ), 'cf7_blacklist_items_export_nonce', true ); ?>
    </div>
	<?php
	}
	
	function cf7_blacklist_save_list_fun( $data ){
		global $wpdb;
        
        $list_tbl_name = $wpdb->prefix.CF7_Blacklist::$_list_tbl_name;
            
		//check nonce field
		if ( !wp_verify_nonce( $data['cf7_blacklist_list_save_oper_nonce'], plugin_basename( __FILE__ ) ) ){
			die( 'Security check!' );
			return;
		}

		if ( !isset($data['cf7_blacklist_list_id']) ){
			return;
		}
		$id = $data['cf7_blacklist_list_id'];
		$name = trim($data['cf7_blacklist_list_name']);
		$list_type = $data['cf7_blacklist_list_type'];
		$date = date( 'Y-m-d 00:00:00', current_time('timestamp') );
		$page_name = $data['page'];

		$name = wp_unslash($name); 
		if ( $id > 0 ){
			$wpdb->update( $list_tbl_name, array( 'list_name' => $name, 'date' => $date, 'list_type' => 'BLACK_LIST' ), array( 'id' => $id ) );
		}else if($id == -1){
			//insert
			$wpdb->insert( $list_tbl_name, array( 'list_name' => $name, 'date' => $date, 'list_type' => 'BLACK_LIST' ) );
			$id = $wpdb->insert_id;
		}
		
		add_action( 'admin_notices', array( $this, 'cf7_blacklist_save_list_successfully_fun') );
		
		$list_view = 'blacklist';
		
		$redirect_to = add_query_arg( array('listview' => $list_view, 'view' => 'edit', 'id' => $id, 'list_save' => 'succ'), admin_url( 'admin.php?page='.$page_name ) );
		wp_redirect( $redirect_to );
		exit;
	}
	
	function cf7_blacklist_save_item_fun( $data ){
		global $wpdb;

		//check nonce field
		if ( !wp_verify_nonce( $data['cf7_blacklist_item_save_oper_nonce'], plugin_basename( __FILE__ ) ) ){
			die( 'Security check!' );
			return;
		}

		if ( !isset($data['cf7_blacklist_list_id']) ){
			return;
		}
		
		$list_id = $data['cf7_blacklist_list_id'];
		$value = $data['cf7_blacklist_add_item_by_input_name'];
		$page_name = $data['page'];
		$list_type = $data['cf7_blacklist_items_list_type'];
		
		$value = wp_unslash($value);
		
		if( isset($data['cf7_blacklist_add_email_domain_name_checkbox']) && $data['cf7_blacklist_add_email_domain_name_checkbox'] == 'YES' ){
			$value = $data['cf7_blacklist_email_domain_name'];
		}
		
		//insert
		$wpdb->insert( $wpdb->prefix.CF7_Blacklist::$_items_tbl_name, array( 'list_id' => $list_id, 'value' => $value ) );
		
		$list_view = 'blacklist';
		
		$redirect_to = add_query_arg( 
                                                    array('listview' => $list_view, 
                                                             'view' => 'edit', 
                                                             'id' => $list_id, 
                                                             'item_action' => 'save_succ'
                                                           ), 
                                                   admin_url( 'admin.php?page='.$page_name ) );
		wp_redirect( $redirect_to );
		exit;
	}
	
	function cf7_blacklist_delete_item_fun( $data ){
		global $wpdb;

		//check nonce field
		if ( !wp_verify_nonce( $data['cf7_blacklist_item_save_oper_nonce'], plugin_basename( __FILE__ ) ) ){
			die( 'Security check!' );
			return;
		}

		if ( !isset($data['cf7_blacklist_item_id']) ){
			return;
		}
		$list_id = $data['cf7_blacklist_list_id'];
		$id = $data['cf7_blacklist_item_id'] + 0;
		$page_name = $data['page'];
		$list_type = $data['cf7_blacklist_items_list_type'];
		
		$sql = 'DELETE FROM `'.$wpdb->prefix.CF7_Blacklist::$_items_tbl_name.'` WHERE `id` = %d';
		$sql = $wpdb->prepare( $sql, $id );
		
		$wpdb->query( $sql );
		
		$list_view = 'blacklist';
		$redirect_to = add_query_arg( 
                                                    array('listview' => $list_view, 
                                                             'view' => 'edit', 
                                                             'id' => $list_id, 
                                                             'item_action' => 'del_succ'
                                                            ), 
                                                    admin_url( 'admin.php?page='.$page_name ) 
                                                  );
		wp_redirect( $redirect_to );
		exit;
	}
	
	function cf7_blacklist_delete_list_by_id_fun( $data ){
		//check nonce field
		if ( !wp_verify_nonce( $data['cf7_blacklist_list_oper_nonce'], 'cf7_blacklist_list_oper_nonce' ) ){
			die( 'Security check!' );
			return;
		}
		
		$list_id = $data['cf7_blacklist_list_id'];
		if( $list_id < 1 ){
			add_action( 'admin_notices', array($this, 'cf7_blacklist_delete_list_invlaid_id_fun') );
		}
		
		global $wpdb;
		
		//delete items
        $sql = 'DELETE FROM `'.$wpdb->prefix.CF7_Blacklist::$_items_tbl_name.'` WHERE `list_id` = %d';
        $sql = $wpdb->prepare( $sql, $list_id );
        $wpdb->query( $sql );
		
		
		//delete list
		$sql = 'DELETE FROM `'.$wpdb->prefix.CF7_Blacklist::$_list_tbl_name.'` WHERE `id` = %d';
		$sql = $wpdb->prepare( $sql, $list_id );
		$wpdb->query( $sql );
		
		add_action( 'admin_notices', array($this, 'cf7_blacklist_delete_list_successfully_fun') );
	}
	
	function cf7_blacklist_delete_list_invlaid_id_fun(){
		?>
        <div class="notice notice-error is-dismissible">
            <p>Delete list failed: Invalid list id</p>
        </div>
        <?php
	}
	
	function cf7_blacklist_delete_list_successfully_fun(){
		?>
        <div class="notice notice-success is-dismissible">
            <p>The list and all items in it have been deleted</p>
        </div>
        <?php
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