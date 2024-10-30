<?php

class CF7_Blacklist_Dashboard_Form_Panel {
    
	public function __construct() {
		
        add_action( 'wpcf7_editor_panels', array( $this, 'cf7_blacklist_add_panel' ) );
        add_action( 'wpcf7_after_save', array( $this, 'cf7_blacklist_save_form_setting' ) );
        add_action( 'wpcf7_after_create', array( $this, 'cf7_blacklist_duplicate_form_setting' ) );
	}
	
	function cf7_blacklist_add_panel( $panels ){
        
        $panels['cf7-blacklist-panel'] = array(
                                                                'title'     => __( 'Blacklist Settings', 'cf7-blacklist' ),
                                                                'callback'  => array( $this, 'cf7_blacklist_display_mapping_form' ),
                                                            );
		return $panels;
    }
    
    function cf7_blacklist_display_mapping_form( $cf7_post ){
        wp_nonce_field( 'cf7_blacklist_mapping_form_nonce', 'cf7_blacklist_mapping_form_nonce' );

		$form_fields = $this->cf7_blacklist_get_form_fields( $cf7_post->id() );
        $form_settings = $this->cf7_blacklist_get_form_settings( $cf7_post->id() );
        $blacklists = $this->cf7_blacklist_get_lists( 'BLACK_LIST' );
        $white_lists = $this->cf7_blacklist_get_lists( 'WHITE_LIST' );
        $email_lists = $this->cf7_blacklist_get_lists( 'EMAIL_LIST' );
        
        $enable = $form_settings && isset( $form_settings['enable'] ) ? $form_settings['enable'] : 'NO';
        $mapping_container_display = $enable == 'YES' ? 'block' : 'none';
        $enable_checked = $enable == 'YES' ? 'checked="true"' : '';
        ?>
        <div class="cf7-blacklist-panel">
            <?php $this->bsk_pdf_manager_show_pro_tip_box( array( 'White List', 'Email List', 'Skip Mail(s)', 'Validation Message' ) ); ?>
            <h2>Blacklist Mapping</h2>
            <p>Choose list type and apply list to your form fields.</p>
            <p>
                <label>
                    <input type="checkbox" name="cf7_blacklist_enable_setting_for_form" class="cf7-blacklist-enable-setting-chk" value="YES" <?php echo $enable_checked; ?> /> Enable mapping for this form
                </label>
            </p>
            <div class="cf7-blacklist-form-fields-mapping-container" style="display: <?php echo $mapping_container_display ?>;">
                <table class="widefat striped">
                    <thead>
                        <th style="width: 30%;">Field Name</th>
                        <th style="width: 25%;">List Type</th>
                        <th style="width: 30%;">List</th>
                        <th style="width: 15%;">Comparison</th>
                    </thead>
                    <tbody>
                        <?php
                        if( $form_fields && is_array( $form_fields ) && count( $form_fields ) > 0 ){
                            foreach( $form_fields as $field ){
                                if( $field->name == "" ){
                                    continue;
                                }
                                $field_settings = $form_settings && isset( $form_settings[$field->name] ) ? $form_settings[$field->name] : false;

                                $none_checked = '';
                                $black_checked = $field_settings && isset( $field_settings['list_type'] ) && $field_settings['list_type'] == 'BLACK_LIST' ? 
                                                          'checked' : '';
                                $white_checked = $field_settings && isset( $field_settings['list_type'] ) && $field_settings['list_type'] == 'WHITE_LIST' ? 
                                                          'checked' : '';
                                $email_checked = $field_settings && isset( $field_settings['list_type'] ) && $field_settings['list_type'] == 'EMAIL_LIST' ? 
                                                          'checked' : '';

                                if( $black_checked == '' && $white_checked == '' && $email_checked == '' ){
                                    $none_checked = 'checked';
                                }

                                $black_display = $black_checked == 'checked' ? 'inline-block' : 'none';
                                $white_display = $white_checked == 'checked' ? 'inline-block' : 'none';
                                $email_display = $email_checked == 'checked' ? 'inline-block' : 'none';

                                $comparision_display = $email_checked == 'checked' ? 'none' : 'inline-block';
                                $email_action_display = $email_checked == 'checked' ? 'inline-block' : 'none';
                                if( $none_checked == 'checked' ){
                                    $comparision_display = 'none';
                                    $email_action_display = 'none';
                                }
                        ?>
                        <tr>
                            <td><?php echo $field->name; ?></td>
                            <td>
                                <label>
                                    <input type="radio" name="cf7_blacklist_list_type_of_<?php echo $field->name; ?>" value="" <?php echo $none_checked; ?> class="cf7-blacklist-list-type-raido" /> None
                                </label><br />
                                <label>
                                    <input type="radio" name="cf7_blacklist_list_type_of_<?php echo $field->name; ?>" value="BLACK_LIST" <?php echo $black_checked; ?> class="cf7-blacklist-list-type-raido" /> Blacklist
                                </label><br />
                                <label>
                                    <input type="radio" name="cf7_blacklist_list_type_of_<?php echo $field->name; ?>" value="WHITE_LIST" <?php echo $white_checked; ?> class="cf7-blacklist-list-type-raido" disabled /> White List
                                </label><br />
                                <label>
                                    <input type="radio" name="cf7_blacklist_list_type_of_<?php echo $field->name; ?>" value="EMAIL_LIST" <?php echo $email_checked; ?> class="cf7-blacklist-list-type-raido" disabled /> Email List
                                </label>
                            </td>
                            <td>
                                <?php
                                $options_str = '';
                                $none_selected = ' selected';
                                if( $blacklists && is_array($blacklists) && count($blacklists) > 0 ){
                                    foreach( $blacklists as $list_id => $list_name ){
                                        $selected_str = '';
                                        if( $field_settings['list_id'] == $list_id ){
                                            $selected_str = ' selected';
                                            $none_selected = '';
                                        }
                                        $options_str .= '<option value="'.$list_id.'"'.$selected_str.'>'.$list_name.'</option>';
                                    }
                                }
                                ?>
                                <select name="cf7_blacklist_blacklist_of_<?php echo $field->name; ?>" class="cf7-blacklist-list-select-blacklist" style="display: <?php echo $black_display ?>; width: 100%;">
                                    <option value=""<?php echo $none_selected; ?>>Select...</option>
                                    <?php echo $options_str; ?>
                                </select>
                            </td>
                            <?php
                            $comparision = $field_settings['list_comparision'];
                            $same_c_i_selected = $comparision == 'SAME_CASE_INSENSITIVE' ? ' selected' : '';
                            $contain_c_i_selected = $comparision == 'CONTAINS_CASE_INSENSITIVE' ? ' selected' : '';
                            $same_c_s_selected = $comparision == 'SAME_CASE_SENSITIVE' ? ' selected' : '';
                            $contain_s_i_selected = $comparision == 'CONTAINS_CASE_SENSITIVE' ? ' selected' : '';
                            
                            $none_selected = ' selected';
                            if( $same_c_i_selected || $contain_c_i_selected || $same_c_s_selected || $contain_s_i_selected ){
                                $none_selected = '';
                            }
                            ?>
                            <td>
                                <select name="cf7_blacklist_comparison_<?php echo $field->name; ?>" class="cf7-blacklist-comparision" style="display: <?php echo $comparision_display; ?>; width: 100%;">
                                    <option value=""<?php echo $none_selected; ?>>Select...</option>
                                    <optgroup label="Case-insensitive">
                                        <option value="SAME_CASE_INSENSITIVE"<?php echo $same_c_i_selected; ?>>Same</option>
                                        <option value="CONTAINS_CASE_INSENSITIVE"<?php echo $contain_c_i_selected; ?>>Contains</option>
                                    </optgroup>
                                    <optgroup label="Case-sensitive">
                                        <option value="SAME_CASE_SENSITIVE"<?php echo $same_c_s_selected; ?>>Same</option>
                                        <option value="CONTAINS_CASE_SENSITIVE"<?php echo $contain_s_i_selected; ?>>Contains</option>
                                    </optgroup>
                                </select>
                            </td>
                        </tr>
                        <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
                <h2 style="margin-top: 40px;">Blacklist Settings</h2>
                <h4>Block or Skip Mail(s)</h4>
                <?php
                $block_checked = '';
                $skip_checked = '';
                $skip_mails_choice_display = 'none';
                $skip_mails_choice_checked_Mail = '';
                $skip_mails_choice_checked_Mail_2 = '';
                if( $form_settings && is_array($form_settings) && isset( $form_settings['block_or_skip'] ) ){
                    $block_checked = $form_settings['block_or_skip'] == 'BLOCK' ? ' checked' : '';
                    $skip_checked = $form_settings['block_or_skip'] == 'SKIP' ? ' checked' : '';
                }
                if( $block_checked == "" && $skip_checked == "" ){
                    $block_checked = ' checked';
                }
                if( $skip_checked != '' ){
                    $skip_mails_choice_display = 'block';
                }
                if( $form_settings && is_array($form_settings) && isset( $form_settings['skip_mails_Mail'] ) && 
                    $form_settings['skip_mails_Mail'] == 'YES' ){
                    $skip_mails_choice_checked_Mail = ' checked';
                }
                if( $form_settings && is_array($form_settings) && isset( $form_settings['skip_mails_Mail_2'] ) && 
                    $form_settings['skip_mails_Mail_2'] == 'YES' ){
                    $skip_mails_choice_checked_Mail_2 = ' checked';
                }
                /*
                  * According to Contat Form 7, it cannot only skip Mail while not skip Mail_2 or additional mails
                  */
                $skip_mails_choice_checked_Mail_2_wrap_display = "inline-block";
                if( $skip_mails_choice_checked_Mail ){
                    $skip_mails_choice_checked_Mail_2 = ' checked';
                    $skip_mails_choice_checked_Mail_2_wrap_display = "none";
                }
                ?>
                <p>
                    <label style="display: inline-block; width: 35%;">
                        <input type="radio" name="cf7_blacklist_block_or_skip" value="BLOCK" class="cf7-blacklist-block-or-skip-raido"<?php echo $block_checked; ?>/> Block form submitting
                    </label>
                    <label style="display: inline-block; width: 35%;">
                        <input type="radio" name="cf7_blacklist_block_or_skip" value="SKIP" class="cf7-blacklist-block-or-skip-raido"<?php echo $skip_checked; ?>  disabled /> Skip Mail(s)
                    </label>
                </p>
                <h4>Validation Message</h4>
                <?php
                $blacklist_validation_message = '';
                $white_list_validation_message = '';
                $email_list_validation_message = '';
                if( $form_settings && is_array($form_settings) && isset( $form_settings['blacklist_validation_message'] ) ){
                    $blacklist_validation_message = $form_settings['blacklist_validation_message'];
                }
                if( $form_settings && is_array($form_settings) && isset( $form_settings['white_list_validation_message'] ) ){
                    $white_list_validation_message = $form_settings['white_list_validation_message'];
                }
                if( $form_settings && is_array($form_settings) && isset( $form_settings['email_list_validation_message'] ) ){
                    $email_list_validation_message = $form_settings['email_list_validation_message'];
                }
                $blacklist_validation_message = $blacklist_validation_message ? $blacklist_validation_message : 'The value for this field is not valid';
                $white_list_validation_message = $white_list_validation_message ? $white_list_validation_message : 'The value for this field is not valid';
                $email_list_validation_message = $email_list_validation_message ? $email_list_validation_message : 'The value for this field is not valid';
                ?>
                <p>
                    <label style="display: inline-block; width: 25%;">Blacklist validation message: </label>
                    <input style="width: 70%;" name="cf7_blacklist_validation_msg_blacklist" value="<?php echo $blacklist_validation_message; ?>" disabled />
                </p>
                <p>
                    <label style="display: inline-block; width: 25%;">White list validation message: </label>
                    <input style="width: 70%;" name="cf7_blacklist_validation_msg_white_list" value="<?php echo $white_list_validation_message; ?>" disabled />
                </p>
                <p>
                    <label style="display: inline-block; width: 25%;">Email list validation message: </label>
                    <input style="width: 70%;" name="cf7_blacklist_validation_msg_email_list" value="<?php echo $email_list_validation_message; ?>" disabled />
                </p>
            </div>
        </div>
        <?php
    }
    
    function cf7_blacklist_get_lists( $list_type ) {
        global $wpdb;
        
        $sql = 'SELECT * FROM `'.$wpdb->prefix.CF7_Blacklist::$_list_tbl_name.'` WHERE `list_type` = %s';
        $sql = $wpdb->prepare( $sql, $list_type );
        $results = $wpdb->get_results( $sql );
        if( !$results || !is_array($results) || count($results) < 1 ){
            return false;
        }
		$return_array = array();
        foreach( $results as $list_obj ){
            $return_array[$list_obj->id] = $list_obj->list_name;
        }
        
		return $return_array;
	}
    
    function cf7_blacklist_get_form_fields( $post_id ) {
		$contact_form = WPCF7_ContactForm::get_instance( $post_id );
		$manager = WPCF7_FormTagsManager::get_instance();

		$form_fields = $manager->scan( $contact_form->prop( 'form' ) );

		return $form_fields;
	}
    
    function cf7_blacklist_get_form_settings( $post_id ){
        $form_settings = get_post_meta( $post_id, CF7_Blacklist::$_form_list_data_option_name, true );
        
        return $form_settings;
    }
    
    function cf7_blacklist_save_form_setting( $contact_form ){
        if ( ! isset( $_POST ) || empty( $_POST ) ) {
			return;
        }
        
        if ( ! wp_verify_nonce( $_POST['cf7_blacklist_mapping_form_nonce'], 'cf7_blacklist_mapping_form_nonce' ) ) {
            return;
        }
        
        $form_id = $contact_form->id();
        $form_fields = $this->cf7_blacklist_get_form_fields( $form_id );
        if( !$form_fields || !is_array( $form_fields ) || count( $form_fields ) < 1 ){
            return;
        }
        //organise form setting
        $form_settings = array();
        $form_settings['enable'] = isset($_POST['cf7_blacklist_enable_setting_for_form']) ? $_POST['cf7_blacklist_enable_setting_for_form'] : '';
        foreach( $form_fields as $field ){
            if( $field->name == "" ){
                continue;
            }
            $field_settings = array();
            $field_settings['list_type'] = $_POST['cf7_blacklist_list_type_of_'.$field->name];
            $field_settings['list_id'] = 0;
            $field_settings['list_comparision'] = '';
            
            if( $field_settings['list_type'] == 'BLACK_LIST' ){
                $field_settings['list_id'] = $_POST['cf7_blacklist_blacklist_of_'.$field->name];
                $field_settings['list_comparision'] = $_POST['cf7_blacklist_comparison_'.$field->name];
            }else if( $field_settings['list_type'] == 'WHITE_LIST' ){
                $field_settings['list_id'] = $_POST['cf7_blacklist_white_list_of_'.$field->name];
                $field_settings['list_comparision'] = $_POST['cf7_blacklist_comparison_'.$field->name];
            }else if( $field_settings['list_type'] == 'EMAIL_LIST' ){
                $field_settings['list_id'] = $_POST['cf7_blacklist_email_list_of_'.$field->name];
                $field_settings['list_comparision'] = $_POST['cf7_blacklist_email_action_'.$field->name];
            }
            
            $form_settings[$field->name] = $field_settings;
        }
        $form_settings['block_or_skip'] = $_POST['cf7_blacklist_block_or_skip'];
        $form_settings['skip_mails_Mail'] = isset($_POST['cf7_blacklist_skip_mail']) ? $_POST['cf7_blacklist_skip_mail'] : '';
        $form_settings['skip_mails_Mail_2'] = isset($_POST['cf7_blacklist_skip_mail_2']) ? $_POST['cf7_blacklist_skip_mail_2'] : '';
        /*
          * According to Contat Form 7, it cannot only skip Mail while not skip Mail_2 or additional mails
          */
        if( $form_settings['skip_mails_Mail'] == 'YES' ){
            $form_settings['skip_mails_Mail_2'] = 'YES';
        }
        
        $form_settings['blacklist_validation_message'] = trim( $_POST['cf7_blacklist_validation_msg_blacklist'] );
        $form_settings['white_list_validation_message'] = trim( $_POST['cf7_blacklist_validation_msg_white_list'] );
        $form_settings['email_list_validation_message'] = trim( $_POST['cf7_blacklist_validation_msg_email_list'] );
        
        //save postmeta
        update_post_meta( $form_id, CF7_Blacklist::$_form_list_data_option_name, $form_settings );
    }
    
    function cf7_blacklist_duplicate_form_setting($contact_form ) {
		$contact_form_id = $contact_form->id();

		if ( ! empty( $_REQUEST['post'] ) && ! empty( $_REQUEST['_wpnonce'] ) ) {
			$old_form_id = intval( $_REQUEST['post'] );
            
            $old_form_settings = get_post_meta( $old_form_id, CF7_Blacklist::$_form_list_data_option_name, true );
			update_post_meta( $contact_form_id, CF7_Blacklist::$_form_list_data_option_name, $old_form_settings );
		}
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
