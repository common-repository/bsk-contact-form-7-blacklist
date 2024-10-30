<?php

class CF7_Blacklist_Dashboard_Help {
	
	private static $_cf7_plugin_support_center = 'https://www.bannersky.com/contact-us/';
	   
	public function __construct() {
	}
	
	function show_help(){
        ?>
        <div class="wrap">
            <h2>Contact Form 7 Blacklist - Help</h2>
            <div>
                <?php
                    $action_url = admin_url( 'admin.php?page='.CF7_Blacklist_Dashboard::$_cf7_blacklist_page );
        
                    $current_list_view = ( !empty($_REQUEST['listview']) ? $_REQUEST['listview'] : 'blacklist');
				
                    $views = array();

                    //blacklist link
                    $blacklist_url = add_query_arg('listview','blacklist', $action_url);
                    $class = $current_list_view == 'blacklist' ? ' class="current"' :'';
                    $views['blacklist'] = '<a href="'.$blacklist_url.'" '.$class.'>Blacklist</a>';

                    //white list link
                    $whitelist_url = add_query_arg('listview','whitelist', $action_url);
                    $class = $current_list_view== 'whitelist' ? ' class="current"' :'';
                    $views['whitelist'] = '<a href="'.$whitelist_url.'" '.$class.'>White List</a>';

                    //Email list link
                    $emaillist_url = add_query_arg('listview','emaillist', $action_url);
                    $class = $current_list_view == 'emaillist' ? ' class="current"' :'';
                    $views['emaillist'] = '<a href="'.$emaillist_url.'" '.$class.'>Email List</a>';

                    //Help
                    $help_url = add_query_arg('listview','help', $action_url);
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
            <div style="width: 70%; float: left;">
                <div class="wrap" id="cf7_blacklist_help_wrap_ID">
                    <h2 class="nav-tab-wrapper">
                        <a class="nav-tab" href="javascript:void(0);" id="cf7_blacklist_help_tab-plugin-documentation">Plugin Documentation</a>
                        <a class="nav-tab" href="javascript:void(0);" id="cf7_blacklist_help_tab-pugin-support">Plugin Support Centre</a>
                    </h2>
                    <div id="cf7_blacklist_help_tab_content_wrap_ID">
                        <section><?php $this->show_plugin_documentaiton(); ?></section>
                        <section><?php $this->show_plugin_support(); ?></section>
                    </div>
                </div>
            </div>
            <div style="width: 28%; float: left;">
                <div class="wrap" id="cf7_blacklist_help_other_product_wrap_ID">
                    <h2>&nbsp;</h2>
                    <div>
                        <?php $this->show_other_plugin_of_gravity_forms_black_list(); ?>
                    </div>
                </div>
            </div>
            <div style="clear: both;"></div>
        </div>
        <?php
	}
    
    function show_plugin_documentaiton(){
    ?>
    <h4>Settings</h4>
    <ul>
        <li>
            <a href="https://www.bannersky.com/document/contact-form-7-blacklist-documentation/add-blacklist-white-list-email-list/" title="Create List" target="_blank">Create List</a>
        </li>
        <li>
            <a href="https://www.bannersky.com/document/contact-form-7-blacklist-documentation/add-item-to-list/" title="Add item to list" target="_blank">Add item to list</a>
        </li>
        <li>
            <a href="https://www.bannersky.com/document/contact-form-7-blacklist-documentation/apply-list-to-form-field/" title="Apply list to form field" target="_blank">Apply list to form field</a>
        </li>
        <li>
            <a href="https://www.bannersky.com/document/contact-form-7-blacklist-documentation/block-form-submitting-or-disable-notifications/" title="Block submitting or disable notification" target="_blank">Block submitting or disable notification</a>
        </li>
        <li>
            <a href="https://www.bannersky.com/document/contact-form-7-blacklist-documentation/custom-validation-message/" title="Custom validation message" target="_blank">Custom validation message</a>
        </li>
    </ul>
    <h4>Examples</h4>
    <ul>
        <li>
            <a href="https://www.bannersky.com/document/contact-form-7-blacklist-documentation/contact-form-7-spam-filter/" title="Contact Form 7 Spam Filter" target="_blank">Contact Form 7 Spam Filter</a>
        </li>
        <li>
            <a href="https://www.bannersky.com/document/contact-form-7-blacklist-documentation/contact-form-7-registration-limit/" title="Contact Form 7 Registration Limit" target="_blank">Contact Form 7 Registration Limit</a>
        </li>
        <li>
            <a href="https://www.bannersky.com/document/contact-form-7-blacklist-documentation/email-white-list/" title="Contact Form 7 Email White list" target="_blank">Contact Form 7 Email White list</a>
        </li>
    </ul>
    <?php
    }
    
    function show_plugin_support(){
    ?>
            <ul>
                <li><a href="<?php echo self::$_cf7_plugin_support_center; ?>" target="_blank">Visit the Support Centre</a> if you have a question on using this plugin</li>
            </ul>
    <?php
    }
    
    function show_other_plugin_of_gravity_forms_black_list(){
    ?>
    <div class="bsk-prdoucts-single">
        <h3>BSK PDF Manager</h3>
        <p>The plugin support you manage your PDF files in WordPress. It’s convenient to make use of. You just need replica the shortcodes into the page/post the place where you wish to have PDF files to exhibit.</p>
        <ul style="list-style: square; list-style-position: inside;">
            <li>Upload PDF files via categories, bulk uplad by FTP</li>
            <li>Display PDF order by title, date, in list, columns, dropdown. With pagination, date weekday filter and search bar</li>
            <li>Featured image and description supported for PDF document</li>
        </ul>
        <p class="bsk-prdoucts-single-center">
            <a class="button button-primary bsk-prdoucts-single-link-button" href="https://www.bannersky.com/bsk-pdf-manager/" target="_blank">More Info</a>
        </p>
    </div>
    <p>&nbsp;</p>
    <div class="bsk-prdoucts-single">
        <h3>BSK GravityForms Blacklist</h3>
        <p>Built to help block submissions from users using spam data or competitors info to create new entry to your site. This plugin allows you to validate a field's value against the keywords and email addresses.</p>
        <ul style="list-style: square; list-style-position: inside;">
            <li>Blacklist to block form submitting, White list to allow form submitting, Email list use to allow or block form submitting if field value match any of items( keywords ).</li>
            <li>Support add items( keywords ) by import CSV file, also can export items ( keywords ) to CSV file.</li>
        </ul>
        <p class="bsk-prdoucts-single-center">
            <a class="button button-primary bsk-prdoucts-single-link-button" href="https://www.bannersky.com/bsk-gravityforms-blacklist/" target="_blank">More Info</a>
        </p>
        </div>
        <?php
	}
	
}