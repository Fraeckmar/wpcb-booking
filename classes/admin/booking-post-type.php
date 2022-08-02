<?php 

class WPCB_Booking_Post
{
    static function init() 
    {
        add_action('admin_init', array(__CLASS__, 'wpcb_booking_custom_post_type'), 9);
    }
    
    static function wpcb_booking_custom_post_type()
    {
        $labels_menu = array(
			'name'					=> _x('Booking', 'Booking', 'wpcb_booking'),
			'singular_name'			=> _x('Booking', 'Booking', 'wpcb_booking'),
			'menu_name' 			=> esc_html__('Booking', 'wpcb_booking'),
			'all_items' 			=> esc_html__('All Bookings', 'wpcb_booking'),
			'view_item' 			=> esc_html__('View Booking', 'wpcb_booking'),
			'add_new_item' 			=> esc_html__('Add New Booking', 'wpcb_booking'),
			'add_new' 				=> esc_html__('Add Booking', 'wpcb_booking'),
			'edit_item' 			=> esc_html__('Edit Booking', 'wpcb_booking'),
			'update_item' 			=> esc_html__('Update Booking', 'wpcb_booking'),
			'search_items' 			=> esc_html__('Search Booking', 'wpcb_booking'),
			'not_found' 			=> esc_html__('Booking Not found', 'wpcb_booking'),
			'not_found_in_trash' 	=> esc_html__('Booking Not found in Trash', 'wpcb_booking')
		);

		$booking_supports 			= array( 'title', 'author', 'thumbnail', 'revisions' );
		$args_tag         			= array(
			'label' 				=> esc_html__('Booking', 'wpcb_booking'),
			'description' 			=> esc_html__('Booking', 'wpcb_booking'),
			'labels' 				=> $labels_menu,
			'supports' 				=> $booking_supports,
			'taxonomies' 			=> array( 'wpcb_booking', 'post_tag' ),
			'menu_icon' 			=> 'dashicons-book-alt',
			'hierarchical' 			=> true,
			'public' 				=> true,
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

		register_post_type('wpcb_booking', $args_tag);
    }
}
WPCB_Booking_Post::init();