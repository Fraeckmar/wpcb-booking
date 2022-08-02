<?php 

class WPCB_Calendar_Post
{
    function __construct()
    {
        add_action('admin_init', array($this, 'calendar_custom_post_type'));
    }
    
    function calendar_custom_post_type()
    {
        $labels_menu = array(
			'name'					=> _x('Calendars', 'Calendars', 'wpcb_booking'),
			'singular_name'			=> _x('Calendar', 'Calendar', 'wpcb_booking'),
			'menu_name' 			=> esc_html__('Calendar', 'wpcb_booking'),
			'all_items' 			=> esc_html__('All Calendars', 'wpcb_booking'),
			'view_item' 			=> esc_html__('View Calendar', 'wpcb_booking'),
			'add_new_item' 			=> esc_html__('Add New Calendar', 'wpcb_booking'),
			'add_new' 				=> esc_html__('Add Calendar', 'wpcb_booking'),
			'edit_item' 			=> esc_html__('Edit Calendar', 'wpcb_booking'),
			'update_item' 			=> esc_html__('Update Calendar', 'wpcb_booking'),
			'search_items' 			=> esc_html__('Search Calendar', 'wpcb_booking'),
			'not_found' 			=> esc_html__('Calendar Not found', 'wpcb_booking'),
			'not_found_in_trash' 	=> esc_html__('Calendar Not found in Trash', 'wpcb_booking')
		);

		$calendar_supports 			= array( 'title', 'author', 'thumbnail', 'revisions' );
		$args_tag         			= array(
			'label' 				=> esc_html__('Calendar', 'wpcb_booking'),
			'description' 			=> esc_html__('Calendar', 'wpcb_booking'),
			'labels' 				=> $labels_menu,
			'supports' 				=> $calendar_supports,
			'taxonomies' 			=> array( 'wpcb_booking', 'post_tag' ),
			'menu_icon' 			=> 'dashicons-calendar',
			'hierarchical' 			=> true,
			'public' 				=> false,
			'show_ui' 				=> true,
			'show_in_menu' 			=> true,
			'show_in_nav_menus' 	=> true,
			'show_in_admin_bar' 	=> true,
			'menu_position' 		=> 5,
			'can_export' 			=> true,
			'has_archive' 			=> false,
			'exclude_from_search' 	=> true,
			'publicly_queryable' 	=> false,
			'capability_type' 		=> 'post'
		);

		register_post_type('wpcb_calendar', $args_tag);
    }
}
new WPCB_Calendar_Post;