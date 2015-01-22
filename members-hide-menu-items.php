<?php
/*
Plugin Name: Members Hide Menu Items
Plugin URI: http://plugins.findingsimple.com
Description: Hide post/page menu items based the content permission feature in Justin Tadlock's [Members plugin] (https://github.com/justintadlock/members) 
Version: 1.0
Author: Finding Simple
Author URI: http://findingsimple.com
License: GPL2
*/
/*
Copyright 2015  Finding Simple  (email : plugins@findingsimple.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! class_exists( 'Members_Hide_Menu_Items' ) ) :

/**
 * So that themes and other plugins can customise the text domain, the Members_Hide_Menu_items
 * should not be initialized until after the plugins_loaded and after_setup_theme hooks.
 * However, it also needs to run early on the init hook.
 *
 * @package Members Hide Menu Items
 * @since 1.0
 */
function initialize_mhmi(){

	// Check if the Members plugin is active and the content permission feature is active
	if( function_exists( 'members_get_setting' ) ) {

		if ( members_get_setting( 'content_permissions' ) ) {
			Members_Hide_Menu_items::init();
		}

	}

}
add_action( 'init', 'initialize_mhmi', -1 );

/**
 * Plugin Main Class.
 *
 * @package Members Hide Menu Items
 * @since 1.0
 */
class Members_Hide_Menu_items {

	static $text_domain;
	
	/**
	 * Initialise
	 */
	public static function init() {

		self::$text_domain = apply_filters( 'mhmi_text_domain', 'mhmi' );

		if ( ! is_admin() ) {
			add_filter( 'wp_get_nav_menu_items', array( __CLASS__, 'exclude_menu_items' ) );
		}

	}

	/**
	 * Exclude menu items via wp_get_nav_menu_items filter
	 * this fixes plugin's incompatibility with theme's that use their own custom Walker
	 */
	public static function exclude_menu_items( $items ) {

		$hide_children_of = array();

		$current_user = wp_get_current_user();

		// Iterate over the items to search and destroy
		foreach ( $items as $key => $item ) {

			$visible = true;

			// hide any item that is the child of a hidden item
			if( in_array( $item->menu_item_parent, $hide_children_of ) ){
				$visible = false;
				$hide_children_of[] = $item->ID; // for nested menus
			}

			// check if member role view content
			if ( $visible && ( $item->type == 'post_type' ) ) {
				$visible = members_can_user_view_post( $current_user->ID, $item->object_id );
			}

			// unset non-visible item
			if ( ! $visible ) {
				$hide_children_of[] = $item->ID; // store ID of item 
				unset( $items[$key] ) ;
			}

		}

		return $items;

	}

};

endif;