<?php
/**
 * Retrieve a gig by its ID
 *
 * @since 1.0
 */
function get_audiotheme_gig( $gig_id ) {
	$post = get_post( $gig_id );
	
	$post->gig_datetime = get_post_meta( $gig_id, 'gig_datetime', true );
	$post->gig_time = 'TBA'; // TODO: should be empty
	
	// determine the gig time
	$gig_time = get_post_meta( $post->ID, 'gig_time', true );
	$t = date_parse( $gig_time );
	if ( empty( $t['errors'] ) ) {
		$post->gig_time = mysql2date( get_option( 'time_format' ), $post->gig_datetime );
	}
	
	$post->venue = NULL;
	$venue_id = get_post_meta( $gig_id, 'venue_id', true );
	if ( $venue_id ) {
		$post->venue = get_audiotheme_venue( $venue_id );
	}
	
	return $post;
}

/**
 * Get the admin panel URL for gigs
 *
 * @since 1.0
 */
function get_audiotheme_gig_admin_url( $args = '' ) {
	$admin_url = admin_url( 'admin.php?page=gigs' );
	
	if ( ! empty( $args ) ) {
		if ( is_array( $args ) ) {
			$admin_url = add_query_arg( $args, $admin_url );
		} else {
			$admin_url = ( 0 !== strpos( $args, '&' ) ) ? '&' . $admin_url : $admin_url;
		}
	}
	
	return $admin_url;
}


/**
 * Update a gig's venue and the gig count for any modified venues
 *
 * The venue name is stored for sorting purposes on the All Gigs admin screen. The venue_id
 * should always be used for retrieving any venue information, including the venue name.
 *
 * @since 1.0
 */
function set_audiotheme_gig_venue( $gig_id, $venue_name ) {
	$old_venue_id = get_post_meta( $gig_id, 'venue_id', true );
	$venue_name = trim( stripslashes( $venue_name ) );
	
	if ( empty( $venue_name ) ) {
		update_post_meta( $gig_id, 'venue', '' ); // meta entry has to be present for sorting functionality to work
		delete_post_meta( $gig_id, 'venue_id' );
	} else {
		$new_venue = get_audiotheme_venue_by( 'name', $venue_name );
		
		if ( ! $new_venue ) {
			$new_venue = array(
				'name' => $venue_name,
				'gig_count' => 1
			);
			
			$venue_id = save_audiotheme_venue( $new_venue );
			if ( $venue_id ) {
				update_post_meta( $gig_id, 'venue', $venue_name );
				update_post_meta( $gig_id, 'venue_id', $venue_id );
			}
		} else {
			update_post_meta( $gig_id, 'venue', $new_venue->name );
			update_post_meta( $gig_id, 'venue_id', $new_venue->ID );
			
			update_audiotheme_venue_gig_count( $new_venue->ID );
		}
	}
	
	if ( $old_venue ) {
		update_audiotheme_venue_gig_count( $old_venue_id );
	}
}

/**
 * Retrieve a venue by its ID
 *
 * @since 1.0
 */
function get_audiotheme_venue( $venue_id ) {
	$post = get_post( $venue_id );
	
	$defaults = get_default_audiotheme_venue_properties();
	$properties = wp_parse_args( (array) get_post_custom( $venue_id ), $defaults );
	
	foreach( $properties as $key => $prop ) {
		if ( ! array_key_exists( $key, $defaults ) ) {
			unset( $properties[ $key ] );
		} elseif ( isset( $prop[0] ) ) {
			$properties[ $key ] = maybe_unserialize( $prop[0] );
		}
	}
	
	$venue['ID'] = $post->ID;
	$venue['name'] = $post->post_title;
	$venue = (object) wp_parse_args( $venue, $properties );
	
	return $venue;
}

/**
 * Retrieve a venue by a property
 *
 * The only field currently supported is the venue name.
 *
 * @since 1.0
 */
function get_audiotheme_venue_by( $field, $value ) {
	global $wpdb;
	
	$field = 'name';
	
	$venue_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='audiotheme_venue' AND post_title=%s", $value ) );
	if ( ! $venue_id )
		return false;
	
	$venue = get_audiotheme_venue( $venue_id );
	
	return $venue;
}

/**
 * Get the default venue object properties
 *
 * Useful for whitelisting data in other API methods.
 *
 * @since 1.0
 */
function get_default_audiotheme_venue_properties() {
	$args = array(
		'ID' => 0,
		'name' => '',
		'address' => '',
		'city' => '',
		'state' => '',
		'postal_code' => '',
		'country' => '',
		'website' => '',
		'phone' => '',
		'contact_name' => '',
		'contact_phone' => '',
		'contact_email' => '',
		'notes' => ''
	);
	
	return $args;
}

/**
 * Retrieve a single gig by it's ID
 *
 * @since 1.0
 */
function get_audiotheme_venue_admin_url( $args = '' ) {
	$admin_url = admin_url( 'admin.php?page=venue' );
	
	if ( ! empty( $args ) ) {
		if ( is_array( $args ) ) {
			$admin_url = add_query_arg( $args, $admin_url );
		} else {
			$admin_url = ( 0 !== strpos( $args, '&' ) ) ? '&' . $admin_url : $admin_url;
		}
	}
	
	return $admin_url;
}

/**
 * Get the admin panel URL for viewing all venues
 *
 * @since 1.0
 */
function get_audiotheme_venues_admin_url( $args = '' ) {
	$admin_url = admin_url( 'admin.php?page=venues' );
	
	if ( ! empty( $args ) ) {
		if ( is_array( $args ) ) {
			$admin_url = add_query_arg( $args, $admin_url );
		} else {
			$admin_url = ( 0 !== strpos( $args, '&' ) ) ? '&' . $admin_url : $admin_url;
		}
	}
	
	return $admin_url;
}

/**
 * Get the admin panel URL for adding/editing a venue
 *
 * @since 1.0
 */
function get_audiotheme_venue_edit_link( $admin_url, $post_id ) {
	if ( 'audiotheme_venue' == get_post_type( $post_id ) ) {
		$args = array(
			'action' => 'edit',
			'venue_id' => $post_id
		);
		
		$admin_url = get_audiotheme_venue_admin_url( $args );
	}
	
	return $admin_url;
}

/**
 * Return a unique venue name
 *
 * @since 1.0
 */
function get_unique_audiotheme_venue_name( $name, $venue_id=0 ) {
	global $wpdb;
	
	$suffix = 2;
	while ( $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_title=%s AND post_type='audiotheme_venue' AND ID!=%d", $name, $venue_id ) ) ) {
		$name.= ' ' . $suffix;
	}
	
	return $name;
}

/**
 * Save a venue
 *
 * Accepts an array of properties, whitelists them and then saves. Will update values if the ID isn't 0.
 * Sets all post meta fields upon initial save, even if empty.
 *
 * @since 1.0
 */
function save_audiotheme_venue( $data ) {
	global $wpdb;
	
	$action = 'update';
	$current_user = wp_get_current_user();
	$defaults = get_default_audiotheme_venue_properties();
	
	// new venue
	if ( empty( $data['ID'] ) ) {
		$action = 'insert';
		$data = wp_parse_args( $data, $defaults );
	} else {
		$current_venue = get_audiotheme_venue( $data['ID'] );
	}
	
	// copy gig count before cleaning the data array
	$gig_count = ( isset( $data['gig_count'] ) && is_numeric( $data['gig_count'] ) ) ? absint( $data['gig_count'] ) : 0;
	
	// remove properties that aren't whitelisted
	$data = array_intersect_key( $data, $defaults );
	
	// map the 'name' property to the 'post_title' field
	if ( isset( $data['name'] ) && ! empty( $data['name'] ) ) {
		$post_title = get_unique_audiotheme_venue_name( $data['name'], $data['ID'] );
		
		if ( ! isset( $current_venue ) || $post_title != $current_venue->name ) {
			$venue['post_title'] = $post_title;
			$venue['post_name'] = '';
		}
	}
	
	// insert the post container
	if ( 'insert' == $action ) {
		$venue['post_author'] = $current_user->ID;
		$venue['post_status'] = 'publish';
		$venue['post_type'] = 'audiotheme_venue';
		
		$venue_id = wp_insert_post( $venue );
	} else {
		$venue_id = absint( $data['ID'] );
		
		if ( ! empty( $venue['post_title'] ) ) {
			$venue['ID'] = $venue_id;
			wp_update_post( $venue );
			
			// update the gig metadta, too
			$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->postmeta pm, $wpdb->postmeta pm2
				SET pm2.meta_value=%s
				WHERE pm.meta_key='venue_id' AND pm.meta_value=%d AND pm.post_id=pm2.post_id AND pm2.meta_key='venue'",
				$venue['post_title'],
				$venue_id ) );
		}
	}
	
	
	// set the venue title as the venue ID if the name argument was empty
	if ( isset( $data['name'] ) && empty( $data['name'] ) ) {
		wp_update_post( array(
			'ID' => $venue_id,
			'post_title' => get_unique_audiotheme_venue_name( $venue_id, $venue_id ),
			'post_name' => '' )
		);
	}
	
	
	// save additional properties to post meta
	if ( $venue_id ) {
		unset( $data['ID'] );
		unset( $data['name'] );
		
		foreach ( $data as $key => $val ) {
			update_post_meta( $venue_id, $key, $val );
		}
		
		// update gig count
		update_audiotheme_venue_gig_count( $venue_id, $gig_count );
		
		return $venue_id;
	} else {
		return false;
	}
}

/**
 * Delete a venue
 *
 * @since 1.0
 */
function delete_audiotheme_venue( $venue_id ) {
	global $wpdb;
	
	// delete meta relationships
	$venue = get_audiotheme_venue( $venue_id );
	$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE ( meta_key='venue' AND meta_value=%s ) OR ( meta_key='venue_id' AND meta_value=%d )", $venue->name, $venue->ID ) );
	
	wp_delete_post( $venue_id, true );
}

/**
 * Update the number of gigs at a particular venue
 *
 * @since 1.0
 */
function update_audiotheme_venue_gig_count( $venue_id, $count = 0 ) {
	global $wpdb;
	
	if ( ! $count ) {
		$sql = $wpdb->prepare( "SELECT count( * )
			FROM $wpdb->posts p
			INNER JOIN $wpdb->postmeta pm ON p.ID=pm.post_id
			WHERE p.post_type='audiotheme_gig' AND pm.meta_key='venue_id' AND pm.meta_value=%d",
			$venue_id );
		$count = $wpdb->get_var( $sql );
	}
	
	update_post_meta( $venue_id, 'gig_count', absint( $count ) );
}
?>