<?php
/*
Plugin Name: BSK Contact Form 7 Blacklist
Description: Check Contact Form 7 fields value against your items and block submission according to setting.
Version: 1.0.1
Author: BannerSky.com
Author URI: http://www.bannersky.com/
------------------------------------------------------------------------

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, 
or any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin Folder Path.
if ( ! defined( 'CF7_BLACKLIST_DIR' ) ) {
    define( 'CF7_BLACKLIST_DIR', plugin_dir_path( __FILE__ ) );
}
// Plugin Folder URL.
if ( ! defined( 'CF7_BLACKLIST_URL' ) ) {
    define( 'CF7_BLACKLIST_URL', plugin_dir_url( __FILE__ ) );
}
/**
 * Plugin main class.
 */
class CF7_Blacklist {
    
    private static $instance;
    public static $_plugin_version = '1.0.1';
    private $_db_version = '1.0';
	private $_saved_db_version_option = '_cf7_blacklist_db_ver_';
    
    public static $_list_tbl_name = 'cf7_blacklist_list';
	public static $_items_tbl_name = 'cf7_blacklist_items';
	public static $_form_list_data_option_name = '_cf7_blacklist_form_list_data_';
    
    public static $_url_to_upgrade = 'https://www.bannersky.com/document/contact-form-7-blacklist-documentation/upgrade-free-to-pro-version/';
    
    public $_CLASS_OBJ_dashboard;
    public $_CLASS_OBJ_validator;

    public $ajax_loader;
    
    public static function instance() {
        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof CF7_Blacklist ) ) {
            global $wpdb;
            
			self::$instance = new CF7_Blacklist;
            
            /*
              * Initialize variables 
            */
            self::$instance->ajax_loader = '<img src="'.CF7_BLACKLIST_URL.'images/ajax-loader.gif" />';
            
            /*
              * plugin hook
            */
            register_activation_hook( __FILE__, array( self::$instance, 'cf7_blacklist_plugin_activate' ) );
            register_deactivation_hook( __FILE__, array( self::$instance, 'cf7_blacklist_deactivate' ) );
            register_uninstall_hook( __FILE__, 'CF7_Blacklist::cf7_blacklist_uninstall' );
            
            /*
              * classes
              */
            require_once CF7_BLACKLIST_DIR . 'classes/dashboard/dashboard.php';
            require_once CF7_BLACKLIST_DIR . 'classes/validator/validator.php';
            
            self::$instance->_CLASS_OBJ_dashboard = new CF7_Blacklist_Dashboard();
            self::$instance->_CLASS_OBJ_validator = new CF7_Blacklist_Validator();
            /*
              * Actions
              */
            add_action( 'admin_enqueue_scripts', array(self::$instance, 'cf7_blacklist_enqueue_scripts') );
            add_action( 'wp_enqueue_scripts', array(self::$instance, 'cf7_blacklist_enqueue_scripts') );
            
            add_action( 'init', array(self::$instance, 'cf7_blacklist_post_action') );
		}
        
		return self::$instance;
	}
	/**
	 * Activation handler.
	 */
	public function cf7_blacklist_plugin_activate( $network_wide ) {
		//create or update table
        self::$instance->cf7_blacklist_create_table();
	}

	public function cf7_blacklist_deactivate() {
	}
    
    public function cf7_blacklist_uninstall(){
        
        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $has_active_pro_verison = false;
        $plugins = get_plugins();
        foreach( $plugins as $plugin_key => $data ){
            if( 'bsk-contact-form-7-blacklist-pro/bsk-contact-form-7-blacklist-pro.php' == $plugin_key && 
                is_plugin_active( $plugin_key ) ){
                $has_active_pro_verison = true;
                break;
            }
        }
        if( $has_active_pro_verison == true ){
            return;
        }
        
        self::$instance->cf7_blaclist_remove_tables_n_options();
    }
    
    public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__,  'Cheatin&#8217;', '1.0' );
	}
    
    public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__,  'Cheatin&#8217;', '1.0' );
	}
    
    public function cf7_blacklist_enqueue_scripts(){
        
        if( is_admin() ){
            
            wp_enqueue_script( 'cf7-blacklist-admin', 
                                          CF7_BLACKLIST_URL . 'js/cf7-blacklist-admin.js', 
                                          array('jquery'), 
                                          filemtime( CF7_BLACKLIST_DIR.'js/cf7-blacklist-admin.js' ) 
                                        );			
            wp_enqueue_style(  'cf7-blacklist-admin', 
                                          CF7_BLACKLIST_URL . 'css/cf7-blacklist-admin.css', 
                                          array(), 
                                          filemtime( CF7_BLACKLIST_DIR.'css/cf7-blacklist-admin.css' ) 
                                        );	
		}else{
            //do nothing
		}
    }
    
    function cf7_blacklist_post_action(){
		if( isset( $_POST['cf7_blacklist_action'] ) && strlen($_POST['cf7_blacklist_action']) >0 ) {
			do_action( 'cf7_blacklist_action_' . $_POST['cf7_blacklist_action'], $_POST );
		}
		if( isset( $_GET['cf7-blacklist-action'] ) && strlen($_GET['cf7-blacklist-action']) >0 ) {
			do_action( 'cf7_blacklist_action_' . $_GET['cf7-blacklist-action'], $_GET );
		}
	}
    
    function cf7_blacklist_create_table(){
		global $wpdb;
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$charset_collate = $wpdb->get_charset_collate();
		
		$list_table = $wpdb->prefix.self::$_list_tbl_name;
		$sql = "CREATE TABLE $list_table (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `list_name` varchar(512) NOT NULL,
		  `list_type` varchar(512) NOT NULL,
		  `date` datetime DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) $charset_collate;";
		dbDelta( $sql );
		
		$items_table = $wpdb->prefix.self::$_items_tbl_name;
		$sql = "CREATE TABLE $items_table (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `list_id` int(11) NOT NULL,
		  `value` varchar(512) NOT NULL,
		  PRIMARY KEY (`id`)
		) $charset_collate;";
		dbDelta($sql);
		
		update_option( self::$instance->_saved_db_version_option, self::$instance->_db_version );
	}
    
    function cf7_blaclist_remove_tables_n_options(){
		global $wpdb;
		
        $table_list = $wpdb->prefix.self::$_list_tbl_name;
		$table_items = $wpdb->prefix.self::$_items_tbl_name;
		
		$wpdb->query("DROP TABLE IF EXISTS $table_list");
		$wpdb->query("DROP TABLE IF EXISTS $table_items");
		
		$sql = 'DELETE FROM `'.$wpdb->options.'` WHERE `option_name` LIKE "_cf7_blacklist_%"';
		$wpdb->query( $sql );
        
        $sql = 'DELETE FROM `'.$wpdb->postmeta.'` WHERE `meta_key` LIKE "'.self::$_form_list_data_option_name.'"';
        $wpdb->query( $sql );
	}
    
}

CF7_Blacklist::instance();
