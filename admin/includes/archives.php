<?php
/**
 * Post type archives admin functionality.
 *
 * This method allows for archive titles, descriptions, and even post type
 * slugs to be easily changed via a familiar interface. It also allows
 * archives to be easily added to nav menus without using a custom link
 * (they stay updated!).
 *
 * @package AudioTheme_Framework
 * @subpackage Archives
 *
 * @since 1.0.0
 */

/**
 * Setup archive posts for post types that have support.
 *
 * @since 1.0.0
 */
function audiotheme_archives_init_admin() {
	$archives = array();

	$post_types = array( 'audiotheme_gig', 'audiotheme_record' );

	// Add an archive if one doesn't exist for whitelisted post types.
	foreach ( $post_types as $post_type ) {
		$id = audiotheme_archives_create_archive( $post_type );
		if ( $id ) {
			$archives[ $post_type ] = $id;
		}
	}

	audiotheme_archives_save_active_archives( $archives );

	add_action( 'parent_file', 'audiotheme_archives_parent_file' );
	add_filter( 'post_updated_messages', 'audiotheme_archives_post_updated_messages' );

	// Make archive links appear last.
	add_action( 'admin_menu', 'audiotheme_archives_admin_menu', 100 );
	add_action( 'add_meta_boxes_audiotheme_archive', 'audiotheme_archives_add_meta_boxes' );
}

/**
 * Add submenu items for archives under the post type menu item.
 *
 * Ensures the user has the capability to edit pages in general as well
 * as the individual page before displaying the submenu item.
 *
 * @since 1.0.0
 */
function audiotheme_archives_admin_menu() {
	$archives = get_audiotheme_archive_ids();

	if ( empty( $archives ) ) {
		return;
	}

	// Verify the user can edit audiotheme_archive posts.
	$archive_type_object = get_post_type_object( 'audiotheme_archive' );
	if ( ! current_user_can( $archive_type_object->cap->edit_posts ) ) {
		return;
	}

	foreach ( $archives as $post_type => $archive_id ) {
		// Verify the user can edit the particular audiotheme_archive post in question.
		if ( ! current_user_can( $archive_type_object->cap->edit_post, $archive_id ) ) {
			continue;
		}

		$parent_slug = ( 'audiotheme_gig' == $post_type ) ? 'audiotheme-gigs' : 'edit.php?post_type=' . $post_type;

		// Add the submenu item.
		add_submenu_page(
			$parent_slug,
			$archive_type_object->labels->singular_name,
			$archive_type_object->labels->singular_name,
			$archive_type_object->cap->edit_posts,
			add_query_arg( array( 'post' => $archive_id, 'action' => 'edit' ), 'post.php' ),
			null
		);
	}
}

/**
 * Replace the submit meta box to remove unnecessary fields.
 *
 * @since 1.0.0
 *
 * @param WP_Post $post Post object.
 */
function audiotheme_archives_add_meta_boxes( $post ) {
	remove_meta_box( 'submitdiv', 'audiotheme_archive', 'side' );
	add_meta_box( 'submitdiv', __( 'Update', 'audiotheme-i18n' ), 'audiotheme_post_submit_meta_box', 'audiotheme_archive', 'side', 'high', array(
		'force_delete'      => false,
		'show_publish_date' => false,
		'show_statuses'     => false,
		'show_visibility'   => false,
	) );
}

/**
 * Highlight the corresponding top level and submenu items when editing an
 * archive page.
 *
 * @since 1.0.0
 *
 * @param string $parent_file A parent file identifier.
 * @return string
 */
function audiotheme_archives_parent_file( $parent_file ) {
	global $post, $submenu_file;

	if ( $post && 'audiotheme_archive' == get_current_screen()->id && $post_type = is_audiotheme_post_type_archive_id( $post->ID ) ) {
		$parent_file = 'edit.php?post_type=' . $post_type;
		$submenu_file = add_query_arg( array( 'post' => $post->ID, 'action' => 'edit' ), 'post.php' );

		// The Gigs list has a custom slug.
		if ( 'audiotheme_gig' == $post_type ) {
			$parent_file = 'audiotheme-gigs';
		}
	}

	return $parent_file;
}

/**
 * Archive update messages.
 *
 * @see /wp-admin/edit-form-advanced.php
 *
 * @param array $messages The array of post update messages.
 * @return array An array with new CPT update messages.
 */
function audiotheme_archives_post_updated_messages( $messages ) {
	global $post;

	$messages['audiotheme_archive'] = array(
		0  => '', // Unused. Messages start at index 1.
		1  => sprintf( __( 'Archive updated. <a href="%s">View Archive</a>', 'audiotheme-i18n' ), esc_url( get_permalink( $post->ID ) ) ),
		2  => __( 'Custom field updated.', 'audiotheme-i18n' ),
		3  => __( 'Custom field deleted.', 'audiotheme-i18n' ),
		4  => __( 'Archive updated.', 'audiotheme-i18n' ),
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'Archive restored to revision from %s', 'audiotheme-i18n' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6  => sprintf( __( 'Archive published. <a href="%s">View Archive</a>', 'audiotheme-i18n' ), esc_url( get_permalink( $post->ID ) ) ),
		7  => __( 'Archive saved.', 'audiotheme-i18n' ),
		8  => sprintf( __( 'Archive submitted. <a target="_blank" href="%s">Preview Archive</a>', 'audiotheme-i18n' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) ),
		9  => sprintf( __( 'Archive scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Archive</a>', 'audiotheme-i18n' ),
		      // translators: Publish box date format, see http://php.net/date
		      date_i18n( __( 'M j, Y @ G:i', 'audiotheme-i18n' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post->ID ) ) ),
		10 => sprintf( __( 'Archive draft updated. <a target="_blank" href="%s">Preview Archive</a>', 'audiotheme-i18n' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) ),
	);

	return $messages;
}

/**
 * Create an archive post for a post type if one doesn't exist.
 *
 * The post type's plural label is used for the post title and the defined
 * rewrite slug is used for the postname.
 *
 * @since 1.0.0
 *
 * @param string $post_type_name Post type slug.
 * @return int Post ID.
 */
function audiotheme_archives_create_archive( $post_type ) {
	$archive_id = get_audiotheme_post_type_archive( $post_type );
	if ( $archive_id ) {
		return $archive_id;
	}

	// Search the inactive option before creating a new page.
	$inactive = get_option( 'audiotheme_archives_inactive' );
	if ( $inactive && isset( $inactive[ $post_type ] ) && get_post( $inactive[ $post_type ] ) ) {
		return $inactive[ $post_type ];
	}

	// Otherwise, create a new archive post.
	$post_type_object = get_post_type_object( $post_type );

	$post = array(
		'post_title'  => $post_type_object->labels->name,
		'post_name'   => get_audiotheme_post_type_archive_slug( $post_type ),
		'post_type'   => 'audiotheme_archive',
		'post_status' => 'publish',
	);

	return wp_insert_post( $post );
}

/**
 * Retrieve a post type's archive slug.
 *
 * Checks the 'has_archive' and 'with_front' args in order to build the
 * slug.
 *
 * @since 1.0.0
 *
 * @param string $post_type Post type name.
 * @return string Archive slug.
 */
function get_audiotheme_post_type_archive_slug( $post_type ) {
	global $wp_rewrite;

	$post_type_object = get_post_type_object( $post_type );

	$slug = ( false !== $post_type_object->rewrite ) ? $post_type_object->rewrite['slug'] : $post_type_object->name;

	if ( $post_type_object->has_archive ) {
		$slug = ( true === $post_type_object->has_archive ) ? $post_type_object->rewrite['slug'] : $post_type_object->has_archive;

		if ( $post_type_object->rewrite['with_front'] ) {
			$slug = substr( $wp_rewrite->front, 1 ) . $slug;
		} else {
			$slug = $wp_rewrite->root . $slug;
		}
	}

	return $slug;
}