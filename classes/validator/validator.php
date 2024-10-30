<?php

class CF7_Blacklist_Validator {
    
	public function __construct() {
		add_filter( 'wpcf7_validate', array( $this, 'cf7_blacklist_validate_item'), 10, 2 );
        add_filter( 'wpcf7_skip_mail', array( $this, 'cf7_blacklist_skip_mail'), 10, 2 );
        add_filter( 'wpcf7_additional_mail', array( $this, 'cf7_blacklist_skip_mail_2'), 10, 2 );
	}
    
    function cf7_blacklist_validate_item( $result, $form_fields ){
        
        if( !$form_fields || !is_array($form_fields) || count($form_fields) < 1 ){
            return $result;
        }

        $wpcf7_instance = WPCF7_Submission::get_instance();
        $contact_form = $wpcf7_instance->get_contact_form();
        $contact_form_id = $contact_form->id();
        $form_settings = $this->cf7_blacklist_get_form_settings( $contact_form_id );
        
        if( !$form_settings || !is_array($form_settings) || count($form_settings) < 1 || 
            !isset( $form_settings['enable'] ) || $form_settings['enable'] != 'YES' ){
            return $result;
        }
        
        //check if block or skip mail
        if( isset( $form_settings['block_or_skip'] ) && $form_settings['block_or_skip'] == 'SKIP' ){
            return $result;
        }
        
        $blacklist_validation_message = isset($form_settings['blacklist_validation_message']) ? $form_settings['blacklist_validation_message'] : '';
        $white_list_validation_message = isset($form_settings['white_list_validation_message']) ? $form_settings['white_list_validation_message'] : '';
        $email_list_validation_message = isset($form_settings['email_list_validation_message']) ? $form_settings['email_list_validation_message'] : '';
        
        if( trim( $blacklist_validation_message ) == "" ){
            $blacklist_validation_message = 'The value for this field is not valid';
        }
        if( trim( $white_list_validation_message ) == "" ){
            $white_list_validation_message = 'The value for this field is not valid';
        }
        if( trim( $email_list_validation_message ) == "" ){
            $email_list_validation_message = 'The value for this field is not valid';
        }

        foreach( $form_fields as $field ){
            if( $field->name == "" || isset( $result->invalid_fields[$field->name] ) ){
                continue;
            }
            //validate field value against blacklist
            if( !isset( $form_settings[$field->name] ) || !is_array( $form_settings[$field->name] ) || count( $form_settings[$field->name] ) < 1 ){
                continue;
            }
            $field_list_setting = $form_settings[$field->name];
            if( !isset( $field_list_setting['list_type'] ) || $field_list_setting['list_type'] == '' || 
                !isset( $field_list_setting['list_id'] ) || $field_list_setting['list_id'] < 1 || 
                !isset( $field_list_setting['list_comparision'] ) || $field_list_setting['list_comparision'] == '' ){
                continue;
            }
            $list_type = $field_list_setting['list_type'];
            $list_id = $field_list_setting['list_id'];
            $list_comparison = $field_list_setting['list_comparision'];
            $field_value = $wpcf7_instance->get_posted_data( $field->name );
            $checked_results = $this->cf7_check_field_value_match( $list_id, $list_comparison, $field_value );
            
            if( $list_type == 'BLACK_LIST' ){
                if( $checked_results ){
                    $result->invalidate( $field, $blacklist_validation_message );
                }
            }else if( $list_type == 'WHITE_LIST' ){
                if( !$checked_results ){
                    $result->invalidate( $field, $white_list_validation_message );
                }
            }else if( $list_type == 'EMAIL_LIST' ){
                if( $list_comparison == 'ALLOW' ){
                    if( !$checked_results ){
                        $result->invalidate( $field, $email_list_validation_message );
                    }
                }else if( $list_comparison == 'BLOCK' ){
                    if( $checked_results ){
                        $result->invalidate( $field, $email_list_validation_message );
                    }
                }
            }
        }
        
        return $result;
    }
    
    function cf7_blacklist_get_form_settings( $form_id ){
        $form_settings = get_post_meta( $form_id, CF7_Blacklist::$_form_list_data_option_name, true );
        
        return $form_settings;
    }
    
    function cf7_check_field_value_match( $list_id, $list_comparison, $field_value ){
		global $wpdb;
		
		if( $field_value == "" ){
			return false;
		}
		//get list data
		$list_data_sql = 'SELECT `value` FROM `'.$wpdb->prefix.CF7_Blacklist::$_items_tbl_name.'` WHERE `list_id` = %d';
		$list_data_sql = $wpdb->prepare( $list_data_sql, $list_id );
		$items_array = $wpdb->get_results( $list_data_sql );
		if( !$items_array || !is_array($items_array) || count($items_array) < 1 ){
			return false;
		}

		$checked_results = false;
		switch ($list_comparison) {
			case 'SAME_CASE_INSENSITIVE':
				$field_value_uppercase = strtoupper( $field_value );
				foreach( $items_array as $item_obj ){
					$item_uppercase = strtoupper( $item_obj->value );
					if( $item_uppercase == $field_value_uppercase ){
						$checked_results = true;
						break;
					}
				}
			break;
			case 'CONTAINS_CASE_INSENSITIVE':
				$field_value_uppercase = strtoupper( $field_value );
				foreach( $items_array as $item_obj ){
					$item_uppercase = strtoupper( $item_obj->value );
					if( strpos($field_value_uppercase, $item_uppercase) !== false ){
						$checked_results = true;
						break;
					}
				}
			break;
			case 'SAME_CASE_SENSITIVE':
				foreach( $items_array as $item_obj ){
					if( $field_value == $item_obj->value ){
						$checked_results = true;
						break;
					}
				}
			break;
			case 'CONTAINS_CASE_SENSITIVE':
				foreach( $items_array as $item_obj ){
					if( strpos($field_value, $item_obj->value) !== false ){
						$checked_results = true;
						break;
					}
				}
			break;
			//for email
			case 'ALLOW':
			case 'BLOCK':
				$field_value_domain_start = strpos( $field_value, '@' );
				if( $field_value_domain_start === false ){
					$checked_results = false;
					break;
				}
				$filed_value_domain = substr( $field_value, $field_value_domain_start + 1, -1 );
				$field_value_uppercase = strtoupper( $field_value );
				foreach( $items_array as $item_obj ){
					//check if email domain
					if( strpos( $item_obj->value, '*@' ) !== false ){
						$email_domain = substr( $item_obj->value, 2, -1 );
						
						if( strtoupper($filed_value_domain) == strtoupper($email_domain) ){
							$checked_results = true;
							break;
						}
					}else{
						$item_uppercase = strtoupper( $item_obj->value );
						if( $item_uppercase == $field_value_uppercase ){
							$checked_results = true;
							break;
						}
					}
				}
			break;
		}

        return $checked_results;
	}
    
    function cf7_blacklist_skip_mail( $skip, $contact_form ){
        $contact_form_id = $contact_form->id();
        $form_settings = $this->cf7_blacklist_get_form_settings( $contact_form_id );
        
        if( !$form_settings || !is_array($form_settings) || 
            !isset( $form_settings['enable'] ) || $form_settings['enable'] != 'YES' ){
            return $skip;
        }

        if( !isset( $form_settings['block_or_skip'] ) || $form_settings['block_or_skip'] != 'SKIP' ){
            return $skip;
        }
        
        /*
          This filter is only for skip main mail.
          In Contact Form 7, if main mail skipped then Mail 2 would also be skipped
          */
        if( $form_settings && is_array($form_settings) && isset( $form_settings['skip_mails_Mail'] ) && 
            $form_settings['skip_mails_Mail'] != 'YES' ){
            return $skip;
        }
        $wpcf7_instance = WPCF7_Submission::get_instance();
        //$contact_form = WPCF7_ContactForm::get_instance($form_id);
		$manager = WPCF7_FormTagsManager::get_instance();
        $form_fields = $manager->scan( $contact_form->prop( 'form' ) );
        if( !$form_fields || !is_array($form_fields) || count($form_fields) < 1 ){
            return $skip;
        }
        foreach( $form_fields as $field ){
            if( $field->name == "" ){
                continue;
            }
            //validate field value against blacklist
            if( !isset( $form_settings[$field->name] ) || !is_array( $form_settings[$field->name] ) || count( $form_settings[$field->name] ) < 1 ){
                continue;
            }
            $field_list_setting = $form_settings[$field->name];
            if( !isset( $field_list_setting['list_type'] ) || $field_list_setting['list_type'] == '' || 
                !isset( $field_list_setting['list_id'] ) || $field_list_setting['list_id'] < 1 || 
                !isset( $field_list_setting['list_comparision'] ) || $field_list_setting['list_comparision'] == '' ){
                continue;
            }
            $list_type = $field_list_setting['list_type'];
            $list_id = $field_list_setting['list_id'];
            $list_comparison = $field_list_setting['list_comparision'];
            $field_value = $wpcf7_instance->get_posted_data( $field->name );
            $checked_results = $this->cf7_check_field_value_match( $list_id, $list_comparison, $field_value );
            
            if( $list_type == 'BLACK_LIST' ){
                if( $checked_results ){
                    return true;
                }
            }else if( $list_type == 'WHITE_LIST' ){
                if( !$checked_results ){
                    return true;
                }
            }else if( $list_type == 'EMAIL_LIST' ){
                if( $list_comparison == 'ALLOW' ){
                    if( !$checked_results ){
                        return true;
                    }
                }else if( $list_comparison == 'BLOCK' ){
                    if( $checked_results ){
                        return true;
                    }
                }
            }
        }

        return $skip;
    }
    
    function cf7_blacklist_skip_mail_2( $additional_mail, $contact_form ){
        $contact_form_id = $contact_form->id();
        $form_settings = $this->cf7_blacklist_get_form_settings( $contact_form_id );
        if( !$form_settings || !is_array($form_settings) || 
            !isset( $form_settings['enable'] ) || $form_settings['enable'] != 'YES' ){
            return $additional_mail;
        }

        if( !isset( $form_settings['block_or_skip'] ) || $form_settings['block_or_skip'] != 'SKIP' ){
            return $additional_mail;
        }
        
        /*
          This filter is only for skip main_2
          In Contact Form 7, if mail mail skipped then Mail 2 would also be skipped
          */
        if( !isset( $form_settings['skip_mails_Mail_2'] ) || $form_settings['skip_mails_Mail_2'] != 'YES' ){

            return $additional_mail;
        }
        $wpcf7_instance = WPCF7_Submission::get_instance();
        //$contact_form = WPCF7_ContactForm::get_instance($form_id);
		$manager = WPCF7_FormTagsManager::get_instance();
        $form_fields = $manager->scan( $contact_form->prop( 'form' ) );
        if( !$form_fields || !is_array($form_fields) || count($form_fields) < 1 ){
            return $additional_mail;
        }
        foreach( $form_fields as $field ){
            if( $field->name == "" ){
                continue;
            }
            //validate field value against blacklist
            if( !isset( $form_settings[$field->name] ) || !is_array( $form_settings[$field->name] ) || count( $form_settings[$field->name] ) < 1 ){
                continue;
            }
            $field_list_setting = $form_settings[$field->name];
            if( !isset( $field_list_setting['list_type'] ) || $field_list_setting['list_type'] == '' || 
                !isset( $field_list_setting['list_id'] ) || $field_list_setting['list_id'] < 1 || 
                !isset( $field_list_setting['list_comparision'] ) || $field_list_setting['list_comparision'] == '' ){
                continue;
            }
            
            $list_type = $field_list_setting['list_type'];
            $list_id = $field_list_setting['list_id'];
            $list_comparison = $field_list_setting['list_comparision'];
            $field_value = $wpcf7_instance->get_posted_data( $field->name );
            $checked_results = $this->cf7_check_field_value_match( $list_id, $list_comparison, $field_value );
            
            if( $list_type == 'BLACK_LIST' ){
                if( $checked_results ){
                    unset( $additional_mail['mail_2'] );
                    return $additional_mail;
                }
            }else if( $list_type == 'WHITE_LIST' ){
                if( !$checked_results ){
                    unset( $additional_mail['mail_2'] );
                    return $additional_mail;
                }
            }else if( $list_type == 'EMAIL_LIST' ){
                if( $list_comparison == 'ALLOW' ){
                    if( !$checked_results ){
                        unset( $additional_mail['mail_2'] );
                        return $additional_mail;
                    }
                }else if( $list_comparison == 'BLOCK' ){
                    if( $checked_results ){
                        unset( $additional_mail['mail_2'] );
                        return $additional_mail;
                    }
                }
            }
        }

        return $additional_mail;
    }
}
