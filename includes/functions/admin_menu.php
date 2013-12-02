<?php
//Build the admin menu
if (!function_exists('add_event_espresso_menus')) {
    function add_event_espresso_menus() {
        global $org_options, $espresso_premium;
		$espresso_manager = '';
		$default_role = 'administrator';

		//If the permissions manager is installed, then load the $espresso_manager global
		if (function_exists('espresso_permissions_config_mnu') && $espresso_premium == true) {
			global $espresso_manager;
			//echo "<pre>".print_r($espresso_manager,true)."</pre>";
		} else {
			$espresso_manager = array('espresso_manager_events' => '', 'espresso_manager_categories' => '', 'espresso_manager_form_groups' => '', 'espresso_manager_form_builder' => '', 'espresso_manager_groupons' => '', 'espresso_manager_discounts' => '', 'espresso_manager_event_emails' => '', 'espresso_manager_personnel_manager' => '', 'espresso_manager_general' => '', 'espresso_manager_calendar' => '', 'espresso_manager_members' => '', 'espresso_manager_payment_gateways' => '', 'espresso_manager_social' => '', 'espresso_manager_addons' => '', 'espresso_manager_support' => '', 'espresso_manager_venue_manager' => '', 'espresso_manager_event_pricing' => '', 'espresso_manager_ticketing' => '', 'espresso_manager_seating' => '', 'espresso_system_status' => '');
		}

        //Main menu tab
        add_menu_page(__('Event Espresso','event_espresso'), '<span style=" font-size:12px">'.__('Event Espresso','event_espresso').'</span>', apply_filters('espresso_management_capability', $default_role, isset($espresso_manager['espresso_manager_general']) && !empty($espresso_manager['espresso_manager_general']) ? $espresso_manager['espresso_manager_general'] : $default_role), 'event_espresso', 'organization_config_mnu', EVENT_ESPRESSO_PLUGINFULLURL . 'images/events_icon_16.png');

        //General Settings
        add_submenu_page('event_espresso', __('Event Espresso - General Settings', 'event_espresso'), __('General Settings', 'event_espresso'), apply_filters('espresso_management_capability', $default_role, isset($espresso_manager['espresso_manager_general']) && !empty($espresso_manager['espresso_manager_general']) ? $espresso_manager['espresso_manager_general'] : $default_role), 'event_espresso', 'organization_config_mnu');

        //Event Setup
        add_submenu_page('event_espresso', __('Event Espresso - Event Overview', 'event_espresso'), __('Event Overview', 'event_espresso'), apply_filters('espresso_management_capability', $default_role, isset($espresso_manager['espresso_manager_events']) && !empty($espresso_manager['espresso_manager_events']) ? $espresso_manager['espresso_manager_events'] : $default_role), 'events', 'event_espresso_manage_events');
		
		//Seating chart management
		if ( defined('ESPRESSO_SEATING_CHART') ){
			add_submenu_page('event_espresso', __('Event Espresso - Seating Chart','event_espresso'), __('Seating Chart','event_espresso'), apply_filters('espresso_management_capability', $default_role, isset($espresso_manager['espresso_manager_seating']) && !empty($espresso_manager['espresso_manager_seating']) ? $espresso_manager['espresso_manager_seating'] : $default_role), 'seating_chart', 'event_espresso_manage_seating_chart');
		}
		
        //Venues
        if (isset($org_options['use_venue_manager']) && $org_options['use_venue_manager'] == 'Y' && $espresso_premium == true) {
            add_submenu_page('event_espresso', __('Event Espresso - Venue Manager', 'event_espresso'), __('Venue Manager', 'event_espresso'), apply_filters('espresso_management_capability', $default_role, isset($espresso_manager['espresso_manager_venue_manager']) && !empty($espresso_manager['espresso_manager_venue_manager']) ? $espresso_manager['espresso_manager_venue_manager'] : $default_role), 'event_venues', 'event_espresso_venue_config_mnu');
            //add_submenu_page('event_espresso', __('Event Espresso - Locales/Regions Manager','event_espresso'), __('Locale Manager','event_espresso'), $default_role, 'event_locales', 'event_espresso_locale_config_mnu');
        }
        //Personnel
        if (isset($org_options['use_personnel_manager']) && $org_options['use_personnel_manager'] == 'Y' && $espresso_premium == true) {
            add_submenu_page('event_espresso', __('Event Espresso - Staff Manager', 'event_espresso'), __('Staff Manager', 'event_espresso'), apply_filters('espresso_management_capability', $default_role, isset($espresso_manager['espresso_manager_personnel_manager']) && !empty($espresso_manager['espresso_manager_personnel_manager']) ? $espresso_manager['espresso_manager_personnel_manager'] : $default_role), 'event_staff', 'event_espresso_staff_config_mnu');
        }

        //Form Questions
        add_submenu_page('event_espresso', __('Event Espresso - Questions', 'event_espresso'), __('Questions', 'event_espresso'), apply_filters('espresso_management_capability', $default_role, isset($espresso_manager['espresso_manager_form_builder']) && !empty($espresso_manager['espresso_manager_form_builder']) ? $espresso_manager['espresso_manager_form_builder'] : $default_role), 'form_builder', 'event_espresso_questions_config_mnu');

        //Questions Groups
        add_submenu_page('event_espresso', __('Event Espresso - Question Groups', 'event_espresso'), __('Question Groups', 'event_espresso'), apply_filters('espresso_management_capability', $default_role, isset($espresso_manager['espresso_manager_form_groups']) && !empty($espresso_manager['espresso_manager_form_groups']) ? $espresso_manager['espresso_manager_form_groups'] : $default_role), 'form_groups', 'event_espresso_question_groups_config_mnu');

        //EventCategories
        add_submenu_page('event_espresso', __('Event Espresso - Manage Event Categories', 'event_espresso'), __('Categories', 'event_espresso'), apply_filters('espresso_management_capability', $default_role, isset($espresso_manager['espresso_manager_categories']) && !empty($espresso_manager['espresso_manager_categories']) ? $espresso_manager['espresso_manager_categories'] : $default_role), 'event_categories', 'event_espresso_categories_config_mnu');

		do_action( 'action_hook_espresso_add_new_ee_submenu', $espresso_manager );

        //Discounts
        if (function_exists('event_espresso_discount_config_mnu') && $espresso_premium == true) {
            add_submenu_page('event_espresso', __('Event Espresso - Promotional Codes', 'event_espresso'), __('Promotional Codes', 'event_espresso'), apply_filters('espresso_management_capability', $default_role, isset($espresso_manager['espresso_manager_discounts']) && !empty($espresso_manager['espresso_manager_discounts']) ? $espresso_manager['espresso_manager_discounts'] : $default_role), 'discounts', 'event_espresso_discount_config_mnu');
        }

        //Groupons
        if (function_exists('event_espresso_groupon_config_mnu') && $espresso_premium == true) {
            add_submenu_page('event_espresso', __('Groupons', 'event_espresso'), __('Groupon Codes', 'event_espresso'), apply_filters('espresso_management_capability', $default_role, isset($espresso_manager['espresso_manager_groupons']) && !empty($espresso_manager['espresso_manager_groupons']) ? $espresso_manager['espresso_manager_groupons'] : $default_role), 'groupons', 'event_espresso_groupon_config_mnu');
        }

        //Email Manager
        if (function_exists('event_espresso_email_config_mnu') && $espresso_premium == true) {
            add_submenu_page('event_espresso', __('Event Espresso - Email Manager', 'event_espresso'), __('Email Manager', 'event_espresso'), apply_filters('espresso_management_capability', $default_role, isset($espresso_manager['espresso_manager_event_emails']) && !empty($espresso_manager['espresso_manager_event_emails']) ? $espresso_manager['espresso_manager_event_emails'] : $default_role), 'event_emails', 'event_espresso_email_config_mnu');
        }
		
		//Event styles & templates
		if (function_exists('event_espresso_manage_templates')) {
        	add_submenu_page('event_espresso', __('Event Espresso - Template Settings', 'event_espresso'), __('Template Settings', 'event_espresso'), $default_role, 'template_confg', 'event_espresso_manage_templates');
		}

        //Calendar Settings 
        if (function_exists('espresso_calendar_config_mnu')) {
            add_submenu_page('event_espresso', __('Event Espresso - Calendar Settings', 'event_espresso'), __('Calendar Settings', 'event_espresso'), apply_filters('espresso_management_capability', $default_role, isset($espresso_manager['espresso_manager_calendar']) && !empty($espresso_manager['espresso_manager_calendar']) ? $espresso_manager['espresso_manager_calendar'] : $default_role), 'espresso_calendar', 'espresso_calendar_config_mnu');
        }

        //Payment Settings
        if (function_exists('event_espresso_gateways_options')) {
            add_submenu_page('event_espresso', __('Event Espresso - Payment Settings', 'event_espresso'), __('Payment Settings', 'event_espresso'), apply_filters('espresso_management_capability', $default_role, isset($espresso_manager['espresso_manager_payment_gateways']) && !empty($espresso_manager['espresso_manager_payment_gateways']) ? $espresso_manager['espresso_manager_payment_gateways'] : $default_role), 'payment_gateways', 'event_espresso_gateways_options');
        }

        //Member Settings
        if (function_exists('event_espresso_member_config_mnu') && $espresso_premium == true) {
            add_submenu_page('event_espresso', __('Event Espresso - Member Settings', 'event_espresso'), __('Member Settings', 'event_espresso'), apply_filters('espresso_management_capability', $default_role, isset($espresso_manager['espresso_manager_members']) && !empty($espresso_manager['espresso_manager_members']) ? $espresso_manager['espresso_manager_members'] : $default_role), 'members', 'event_espresso_member_config_mnu');
        }

        //MailChimp Integration Settings
        if (function_exists('event_espresso_mailchimp_settings') && $espresso_premium == true) {
            add_submenu_page('event_espresso', __('Event Espresso - MailChimp Integration', 'event_espresso'), __('MailChimp Integration', 'event_espresso'), $default_role, 'espresso-mailchimp', 'event_espresso_mailchimp_settings');
        }
		
		//Infusionsoft Integration Settings
        if (function_exists('event_espresso_infusionsoft_settings') && $espresso_premium == true) {
            add_submenu_page('event_espresso', __('Event Espresso - Infusionsoft Settings', 'event_espresso'), __('Infusionsoft Settings', 'event_espresso'), $default_role, 'espresso-infusionsoft', 'event_espresso_infusionsoft_settings');
        }
		
		//Eventbrite Integration Settings
        if (function_exists('espresso_eventbrite_settings') && $espresso_premium == true) {
            add_submenu_page('event_espresso', __('Event Espresso - Eventbrite Settings', 'event_espresso'), __('Eventbrite Settings', 'event_espresso'), $default_role, 'espresso-eventbrite', 'espresso_eventbrite_settings');
        }
		
		//Ticketing Settings
        if (function_exists('espresso_ticket_config_mnu') && $espresso_premium == true) {
            add_submenu_page('event_espresso', __('Event Espresso - Ticket Customization', 'event_espresso'), __('Ticket Templates', 'event_espresso'), apply_filters('espresso_management_capability', $default_role, isset($espresso_manager['espresso_manager_ticketing']) && !empty($espresso_manager['espresso_manager_ticketing']) ? $espresso_manager['espresso_manager_ticketing'] : $default_role), 'event_tickets', 'espresso_ticket_config_mnu');
        }

        //Facebook Event Integration Settings
        if (function_exists('espresso_fb_settings') && $espresso_premium == true) {
            add_submenu_page('event_espresso', __('Event Espresso - Facebook Settings', 'event_espresso'), __('Facebook Settings', 'event_espresso'), $default_role, 'espresso_facebook', 'espresso_fb_settings');
        }

		//Reports
		if (function_exists('espresso_reports') && $espresso_premium == true) {
			add_submenu_page('event_espresso', __('Event Espresso - Reports','event_espresso'), __('Reports','event_espresso'), $default_role, 'espresso_reports', 'espresso_reports');
		}
		
		//Attendee Import
		if (function_exists('espresso_attendee_import') && $espresso_premium == true) {
			add_submenu_page('event_espresso', __('Event Espresso - Attendee Import Tool','event_espresso'), __('Attendee Import','event_espresso'), $default_role, 'espresso_attendee_import', 'espresso_attendee_import');
		}
		
		//Voucher Import
		if (function_exists('espresso_voucher_import') && $espresso_premium == true) {
			add_submenu_page('event_espresso', __('Event Espresso - Voucher Import Tool','event_espresso'), __('Voucher Import','event_espresso'), $default_role, 'espresso_voucher_import', 'espresso_voucher_import');
		}


        //Social Media Settings
        if (function_exists('espresso_social_config_mnu') && $espresso_premium == true) {
            add_submenu_page('event_espresso', __('Event Espresso - Social Media Settings', 'event_espresso'), __('Social Media', 'event_espresso'), apply_filters('espresso_management_capability', $default_role, isset($espresso_manager['espresso_manager_social']) && !empty($espresso_manager['espresso_manager_social']) ? $espresso_manager['espresso_manager_social'] : $default_role), 'espresso_social', 'espresso_social_config_mnu');
        }

        //Addons
       // add_submenu_page('event_espresso', __('Event Espresso - Addons', 'event_espresso'), __('Addons', 'event_espresso'), $default_role, 'admin_addons', 'event_espresso_addons_mnu');
		
		//Test Drive Pro
		if ($espresso_premium != true)
			add_submenu_page('event_espresso', __('Event Espresso - Test Drive Pro', 'event_espresso'), __('Test Drive Pro', 'event_espresso'), $default_role, 'test_drive', 'event_espresso_test_drive');

		//System Status
		add_submenu_page('event_espresso', __('Event Espresso - System Status', 'event_espresso'), __('System Status', 'event_espresso'), apply_filters('espresso_management_capability', $default_role, isset($espresso_manager['espresso_system_status']) && !empty($espresso_manager['espresso_system_status']) ? $espresso_manager['espresso_system_status'] : $default_role), 'espresso-system-status', 'espresso_system_status');
		
		//Help/Support
        add_submenu_page('event_espresso', __('Event Espresso - Help/Support', 'event_espresso'), __('<span style="color: red;">Help/Support</span>', 'event_espresso'), apply_filters('espresso_management_capability', $default_role, isset($espresso_manager['espresso_manager_support']) && !empty($espresso_manager['espresso_manager_support']) ? $espresso_manager['espresso_manager_support'] : $default_role), 'support', 'event_espresso_support');

		//Permissions settings
		add_submenu_page('events', __('Event Espresso - Permissions Settings', 'event_espresso'), '<span class="ee_menu_group"  onclick="return false;">' . __('Permissions', 'event_espresso') . '</span>', $default_role, 'espresso_permissions', 'espresso_permissions_config_mnu');
		
		if (function_exists('espresso_manager_version') && $espresso_premium == true) {
			add_submenu_page('event_espresso', __('Event Espresso - Event Manager Permissions', 'event_espresso'), __('User Permissions', 'event_espresso'), $default_role, 'espresso_permissions', 'espresso_permissions_config_mnu');
			add_submenu_page('event_espresso', __('Event Espresso - Event Manager Roles', 'event_espresso'), __('User Roles', 'event_espresso'), $default_role, 'roles', 'espresso_permissions_roles_mnu');
			if ($org_options['use_venue_manager'] == 'Y' && function_exists('espresso_permissions_user_groups')) {
				if (espresso_member_data('role') == "administrator") {
					add_submenu_page('event_espresso', __('Event Espresso - Locales/Regions', 'event_espresso'), __('Locales/Regions', 'event_espresso'), apply_filters('espresso_management_capability', $default_role, isset($espresso_manager['espresso_manager_venue_manager']) && !empty($espresso_manager['espresso_manager_venue_manager']) ? $espresso_manager['espresso_manager_venue_manager'] : $default_role), 'event_locales', 'event_espresso_locale_config_mnu');
				}
				add_submenu_page('event_espresso', __('Event Espresso - Regional Managers', 'event_espresso'), __('Regional Managers', 'event_espresso'), $default_role, 'event_groups', 'espresso_permissions_user_groups');
			}
		}
		
    }

}
