jQuery(document).ready( function($) {
	
	$("#cf7_blacklist_list_edit_form_id").keypress(function(e) {
		var key = e.charCode || e.keyCode || 0;     
		if (key == 13) {
			e.preventDefault();
		}
    });
	
	$("#cf7_blacklist_blacklist_list_save_ID").click(function(){
        var list_type = $("#cf7_blacklist_list_type_ID").val();
		var list_name = $("#cf7_blacklist_list_name_ID").val();
		
        if( list_type != 'BLACK_LIST' ){
            return;
        }
		list_name = $.trim(list_name);
		if( list_name == "" ){
			alert( "List name cannot be empty" );
			$("#cf7_blacklist_list_name_ID").focus();
			
			return false;
		}
		
		$("#cf7_blacklist_list_edit_form_id").submit();
	});
	
	
	$("#cf7_blacklist_add_item_by_input_save_anchor_ID").click(function(){
		var item_value = $("#cf7_blacklist_add_item_by_input_name_ID").val();
		var item_list_type = $("#cf7_blacklist_items_list_type_ID").val();
		
        if( item_list_type != 'BLACK_LIST' ){
			return;
		}
        
		item_value = $.trim(item_value);
		if( item_value == "" ){
			alert( "Item value cannot be empty" );
			$("#cf7_blacklist_add_item_by_input_name_ID").focus();
			
			return false;
		}
		
		$("#cf7_blacklist_action_ID").val( "save_item" );
		$("#cf7_blacklist_items_form_id").submit();
	});
	
	$(".cf7-blacklist-item-delete-anchor").click(function(){
		var item_id = $(this).attr('rel');
		
		if( parseInt(item_id) < 1 ){
			alert( "Invalid opearation" );
		}
		
		$("#cf7_blacklist_item_id_ID").val( item_id );
		$("#cf7_blacklist_action_ID").val( "delete_item" );
		
		$("#cf7_blacklist_items_form_id").submit();
	});
	
	$("#cf7_blacklist_add_item_by_csv_ID").change(function (){
       var file_name = $(this).val();
       $("#cf7_blacklist_add_item_by_csv_selected_file_ID").val( file_name );
    });
	
	$(".cf7-blacklist-admin-delete-list").click(function(){
		var list_id = $(this).attr("rel");
		var count = $(this).attr("count");
		
		if( parseInt(list_id) < 1 ){
			alert( "Invalid operation" );
			return false;
		}
		
		if( parseInt(count) > 0 ){
			r = confirm( count + " item(s) inlcuded in this list, are you sure you will remove them?" );
			if( r == false ){
				return false;
			}
		}
		
		$("#cf7_blacklist_list_id_to_be_processed_ID").val( list_id );
		$("#cf7_blacklist_action_ID").val( "delete_list_by_id" );
		$("#cf7_blacklist_lists_form_id").submit();
	});
	
	$("#cf7_blacklist_add_email_domain_name_checkbox_ID").on("click", function(){
		if( $(this).is(":checked") ){
			$("#cf7_blacklist_add_email_domain_name_input_container_ID").css( "display", "block" );
			
			$("#cf7_blacklist_add_email_list_item_input_container_ID").css( "display", "none" );
		}else{
			$("#cf7_blacklist_add_email_domain_name_input_container_ID").css( "display", "none" );
			
			$("#cf7_blacklist_add_email_list_item_input_container_ID").css( "display", "block" );
		}
	});
    

    $("#cf7-blacklist-items-serch-search-input").insertAfter($("#search-submit").parent().find('#search-submit'));
    
    /*
      * Form setting
      */
    $(".cf7-blacklist-list-type-raido").click(function(){
        var list_type = $(this).val();
        
        $(this).parents( "tr" ).find( ".cf7-blacklist-list-select-blacklist" ).css( "display", "none" );
        $(this).parents( "tr" ).find( ".cf7-blacklist-list-select-white-list" ).css( "display", "none" );
        $(this).parents( "tr" ).find( ".cf7-blacklist-list-select-email-list" ).css( "display", "none" );
        $(this).parents( "tr" ).find( ".cf7-blacklist-comparision" ).css( "display", "none" );
        $(this).parents( "tr" ).find( ".cf7-blacklist-email-action" ).css( "display", "none" );
        if( list_type == "" ){
            return;
        }
        if( list_type == 'BLACK_LIST' ){
            $(this).parents( "tr" ).find( ".cf7-blacklist-list-select-blacklist" ).css( "display", "inline-block" );
        }else if( list_type == 'WHITE_LIST' ){
            $(this).parents( "tr" ).find( ".cf7-blacklist-list-select-white-list" ).css( "display", "inline-block" );
        }else if( list_type == 'EMAIL_LIST' ){
            $(this).parents( "tr" ).find( ".cf7-blacklist-list-select-email-list" ).css( "display", "inline-block" );
        }
        
        if( list_type == 'EMAIL_LIST' ){
            $(this).parents( "tr" ).find( ".cf7-blacklist-email-action" ).css( "display", "inline-block" );
        }else{
            $(this).parents( "tr" ).find( ".cf7-blacklist-comparision" ).css( "display", "inline-block" );
        }
        
    });
    
    $(".cf7-blacklist-enable-setting-chk").click(function(){
        if( $(this).is(":checked") ){
            $(this).parents( ".cf7-blacklist-panel" ).find( ".cf7-blacklist-form-fields-mapping-container" ).css("display", "block");
        }else{
            $(this).parents( ".cf7-blacklist-panel" ).find( ".cf7-blacklist-form-fields-mapping-container" ).css("display", "none");
        }
    });
    
    
    /* help tab switch */
	$("#cf7_blacklist_help_wrap_ID .nav-tab-wrapper a").click(function(){
		//alert( $(this).index() );
		$('#cf7_blacklist_help_wrap_ID section').hide();
		$('#cf7_blacklist_help_wrap_ID section').eq($(this).index()).show();
		
		$(".nav-tab").removeClass( "nav-tab-active" );
		$(this).addClass( "nav-tab-active" );
		
		return false;
	});
    $("#cf7_blacklist_help_tab-plugin-documentation").click();
    
});
