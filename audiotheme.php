<?php
/*
AudioTheme Framework
The engine of AudioTheme 

Version: 1.0.0
Author: AudioTheme
Author URI: http://AudioTheme.com
License: GPLv2

Copyright 2012 AudioTheme

This program is free software; you can redistribute it and/or modify 
it under the terms of the GNU General Public License as published by 
the Free Software Foundation; either version 2 of the License, or (at 
your option) any later version.This program is distributed in the hope 
that it will be useful, but WITHOUT ANY WARRANTY; without even the 
implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Define Constants
 *
 * @since 1.0
 */
define( 'AUDIOTHEME_VERSION', 1.0 );
define( 'AUDIOTHEME_DIR', get_template_directory() . '/audiotheme/' );
define( 'AUDIOTHEME_URI', get_template_directory_uri() . '/audiotheme/' );


/**
 * General Inclusions
 *
 * @since 1.0
 */
require( AUDIOTHEME_DIR . 'includes/general-template.php' );
require( AUDIOTHEME_DIR . 'includes/functions.php' );
require( AUDIOTHEME_DIR . 'includes/formatting.php' );
require( AUDIOTHEME_DIR . 'includes/media.php' );


/**
 * AudioTheme Setup
 *
 * @since 1.0
 */
add_action( 'after_setup_theme', 'audiotheme_setup' );
function audiotheme_setup() {
	/* Include Shortcodes */
	require( AUDIOTHEME_DIR . 'includes/default-filters.php' );
	require( AUDIOTHEME_DIR . 'includes/shortcodes.php' );
	
	/* Include Admin functionality */
	if ( is_admin() ) {
		require( AUDIOTHEME_DIR . 'admin/admin.php' );
	}
	
	/* Include Gigs CPT functionality */
	require( AUDIOTHEME_DIR . 'gigs/gigs.php' );
	
	add_action( 'init', 'audiotheme_init' );
	add_action( 'init', 'audiotheme_register_scripts' );
}


/**
 * AudioTheme Init
 *
 * @since 1.0
 */
function audiotheme_init() {

	register_post_type( 'audiotheme_gallery', array(
		'capability_type'        => 'post',
		'has_archive'            => false,
		'hierarchical'           => false,
		'labels'                 => array(
			'name'               => _x( 'Galleries', 'post type general name', 'audiotheme-i18n' ),
			'singular_name'      => _x( 'Gallery', 'post type singular name', 'audiotheme-i18n' ),
			'add_new'            => _x( 'Add New', 'gallery', 'audiotheme-i18n' ),
			'add_new_item'       => __( 'Add New Gallery', 'audiotheme-i18n' ),
			'edit_item'          => __( 'Edit Gallery', 'audiotheme-i18n' ),
			'new_item'           => __( 'New Gallery', 'audiotheme-i18n' ),
			'view_item'          => __( 'View Gallery', 'audiotheme-i18n' ),
			'search_items'       => __( 'Search Galleries', 'audiotheme-i18n' ),
			'not_found'          => __( 'No galleries found', 'audiotheme-i18n' ),
			'not_found_in_trash' => __( 'No galleries found in Trash', 'audiotheme-i18n' ),
			'all_items'          => __( 'All Galleries', 'audiotheme-i18n' )
		),
		'menu_position'          => 9,
		'public'                 => true,
		'publicly_queryable'     => true,
		'rewrite'                => array( 'slug' => 'galleries', 'with_front' => false ),
		'show_ui'                => true,
		'show_in_menu'           => true,
		'supports'               => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'author' )
	) );
	
	register_post_type( 'audiotheme_record', array(
		'capability_type'        => 'post',
		'has_archive'            => false,
		'hierarchical'           => false,
		'labels'                 => array(
			'name'               => _x( 'Records', 'post type general name', 'audiotheme-i18n' ),
			'singular_name'      => _x( 'Record', 'post type singular name', 'audiotheme-i18n' ),
			'add_new'            => _x( 'Add New', 'record', 'audiotheme-i18n' ),
			'add_new_item'       => __( 'Add New Record', 'audiotheme-i18n' ),
			'edit_item'          => __( 'Edit Record', 'audiotheme-i18n' ),
			'new_item'           => __( 'New Record', 'audiotheme-i18n' ),
			'view_item'          => __( 'View Record', 'audiotheme-i18n' ),
			'search_items'       => __( 'Search Records', 'audiotheme-i18n' ),
			'not_found'          => __( 'No records found', 'audiotheme-i18n' ),
			'not_found_in_trash' => __( 'No records found in Trash', 'audiotheme-i18n' ),
			'all_items'          => __( 'Records', 'audiotheme-i18n' )
		),
		'menu_position'          => 7,
		'public'                 => true,
		'publicly_queryable'     => true,
		'rewrite'                => array( 'slug' => 'records', 'with_front' => false ),
		'show_ui'                => true,
		'show_in_menu'           => true,
		'supports'               => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'author' ),
		'taxonomies'             => array( 'post_tag' )
	) );
	
	register_post_type( 'audiotheme_track', array(
		'capability_type'        => 'post',
		'has_archive'            => false,
		'hierarchical'           => false,
		'labels'                 => array(
			'name'               => _x( 'Tracks', 'post type general name', 'audiotheme-i18n' ),
			'singular_name'      => _x( 'Track', 'post type singular name', 'audiotheme-i18n' ),
			'add_new'            => _x( 'Add New', 'track', 'audiotheme-i18n' ),
			'add_new_item'       => __( 'Add New Track', 'audiotheme-i18n' ),
			'edit_item'          => __( 'Edit Track', 'audiotheme-i18n' ),
			'new_item'           => __( 'New Track', 'audiotheme-i18n' ),
			'view_item'          => __( 'View Track', 'audiotheme-i18n' ),
			'search_items'       => __( 'Search Tracks', 'audiotheme-i18n' ),
			'not_found'          => __( 'No tracks found', 'audiotheme-i18n' ),
			'not_found_in_trash' => __( 'No tracks found in Trash', 'audiotheme-i18n' ),
			'all_items'          => __( 'Tracks', 'audiotheme-i18n' )
		),
		'public'                 => true,
		'publicly_queryable'     => true,
		'rewrite'                => array( 'slug' => 'records', 'with_front' => false ),
		'show_ui'                => true,
		'show_in_menu'           => 'edit.php?post_type=audiotheme_record',
		'supports'               => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'author' )
	) );
	
	register_post_type( 'audiotheme_video', array(
		'capability_type'        => 'post',
		'has_archive'            => false,
		'hierarchical'           => false,
		'labels'                 => array(
			'name'               => _x( 'Videos', 'post type general name', 'audiotheme-i18n' ),
			'singular_name'      => _x( 'Video', 'post type singular name', 'audiotheme-i18n' ),
			'add_new'            => _x( 'Add New', 'video', 'audiotheme-i18n' ),
			'add_new_item'       => __( 'Add New Video', 'audiotheme-i18n' ),
			'edit_item'          => __( 'Edit Video', 'audiotheme-i18n' ),
			'new_item'           => __( 'New Video', 'audiotheme-i18n' ),
			'view_item'          => __( 'View Video', 'audiotheme-i18n' ),
			'search_items'       => __( 'Search Videos', 'audiotheme-i18n' ),
			'not_found'          => __( 'No videos found', 'audiotheme-i18n' ),
			'not_found_in_trash' => __( 'No videos found in Trash', 'audiotheme-i18n' ),
			'all_items'          => __( 'Videos', 'audiotheme-i18n' )
		),
		'menu_position'          => 8,
		'public'                 => true,
		'publicly_queryable'     => true,
		'rewrite'                => array( 'slug' => 'videos', 'with_front' => false ),
		'show_ui'                => true,
		'show_in_menu'           => true,
		'supports'               => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'author' ),
		'taxonomies'             => array( 'post_tag' )
	) );
	
	
	register_taxonomy( 'audiotheme_record_type', 'audiotheme_record', array(
		'args'                           => array( 'orderby' => 'term_order' ),
		'hierarchical'                   => true,
		'labels'                         => array(
			'name'                       => _x( 'Record Types', 'taxonomy general name', 'audiotheme-i18n' ),
			'singular_name'              => _x( 'Record Type', 'taxonomy singular name', 'audiotheme-i18n' ),
			'search_items'               => __( 'Search Record Types', 'audiotheme-i18n' ),
			'popular_items'              => __( 'Popular Record Types', 'audiotheme-i18n' ),
			'all_items'                  => __( 'All Record Types', 'audiotheme-i18n' ),
			'parent_item'                => __( 'Parent Record Type', 'audiotheme-i18n' ),
			'parent_item_colon'          => __( 'Parent Record Type:', 'audiotheme-i18n' ),
			'edit_item'                  => __( 'Edit Record Type', 'audiotheme-i18n' ),
			'view_item'                  => __( 'View Record Type', 'audiotheme-i18n' ),
			'update_item'                => __( 'Update Record Type', 'audiotheme-i18n' ),
			'add_new_item'               => __( 'Add New Record Type', 'audiotheme-i18n' ),
			'new_item_name'              => __( 'New Record Type Name', 'audiotheme-i18n' ),
			'separate_items_with_commas' => __( 'Separate record types with commas', 'audiotheme-i18n' ),
			'add_or_remove_items'        => __( 'Add or remove record types', 'audiotheme-i18n' ),
			'choose_from_most_used'      => __( 'Choose from most used record types', 'audiotheme-i18n' )
		),
		'public'                         => true,
		'query_var'                      => true,
		'rewrite'                        => array( 'slug' => 'records/type', 'with_front' => false ),
		'show_ui'                        => true,
		'show_in_nav_menus'              => true
	) );
	
	register_taxonomy( 'audiotheme_video_type', 'audiotheme_video', array(
		'args'                           => array( 'orderby' => 'term_order' ),
		'hierarchical'                   => true,
		'labels'                         => array(
			'name'                       => _x( 'Video Types', 'taxonomy general name', 'audiotheme-i18n' ),
			'singular_name'              => _x( 'Video Type', 'taxonomy singular name', 'audiotheme-i18n' ),
			'search_items'               => __( 'Search Video Types', 'audiotheme-i18n' ),
			'popular_items'              => __( 'Popular Video Types', 'audiotheme-i18n' ),
			'all_items'                  => __( 'All Video Types', 'audiotheme-i18n' ),
			'parent_item'                => __( 'Parent Video Type', 'audiotheme-i18n' ),
			'parent_item_colon'          => __( 'Parent Video Type:', 'audiotheme-i18n' ),
			'edit_item'                  => __( 'Edit Video Type', 'audiotheme-i18n' ),
			'view_item'                  => __( 'View Video Type', 'audiotheme-i18n' ),
			'update_item'                => __( 'Update Video Type', 'audiotheme-i18n' ),
			'add_new_item'               => __( 'Add New Video Type', 'audiotheme-i18n' ),
			'new_item_name'              => __( 'New Video Type Name', 'audiotheme-i18n' ),
			'separate_items_with_commas' => __( 'Separate video types with commas', 'audiotheme-i18n' ),
			'add_or_remove_items'        => __( 'Add or remove video types', 'audiotheme-i18n' ),
			'choose_from_most_used'      => __( 'Choose from most used video types', 'audiotheme-i18n' )
		),
		'public'                         => true,
		'query_var'                      => true,
		'rewrite'                        => array( 'slug' => 'records/type', 'with_front' => false ),
		'show_ui'                        => true,
		'show_in_nav_menus'              => true
	) );
	
}

/**
 * Register Scripts
 *
 * @since 1.0
 */
function audiotheme_register_scripts() {
	// Related: http://core.trac.wordpress.org/ticket/18909
	wp_register_style( 'jquery-ui-theme-smoothness', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/themes/smoothness/jquery-ui.css' );
	wp_register_style( 'jquery-ui-theme-audiotheme', AUDIOTHEME_URI . 'includes/css/jquery-ui-audiotheme.css', array( 'jquery-ui-theme-smoothness' ) );
	
	wp_register_style( 'audiotheme-admin', AUDIOTHEME_URI . 'admin/css/audiotheme-admin.css' );
}
?>