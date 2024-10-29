<?php
/*
 * Plugin Name: BBP Participants Trash
 * Plugin URI: http://www.codepixlabs.com/
 * Description:  Allows participants to trash their replies & topics
 * Author: Codepixlabs
 * Author URI: http://www.codepixlabs.com/plugins/bbp-participants-trash
 * Version: 1.0.0
 */

/*Customize the BBPress roles to allow Participants to trash topics**/
add_filter( 'bbp_get_caps_for_role', 'BBP_add_role_caps_filter', 5, 2 );


function BBP_add_role_caps_filter( $caps, $role ){
    // Only filter for roles we are interested in!
    if( $role == 'bbp_participant' ) {

		$caps = array(
            // Primary caps
			'spectate'              => true,
			'participate'           => true,
			'view_trash'            => true,

			// Forum caps
			'read_private_forums'   => true,

			// Topic caps
			'publish_topics'        => true,
			'edit_topics'           => true,
			'delete_topics'         => true,

			// Reply caps
			'publish_replies'       => true,
			'edit_replies'          => true,
			'delete_replies'        => true,

			// Topic tag caps
			'assign_topic_tags'     => true
        );

	}

	return $caps;

    
}


/*Fixes an issue that only allows mods to trash topics.
bbpress.trac.wordpress.org/changeset/5852
bbpress.trac.wordpress.org/ticket/2685*/

add_filter( 'bbp_map_reply_meta_caps', 'BBP_tweak_trash_meta_caps', 11, 4 );
add_filter( 'bbp_map_topic_meta_caps', 'BBP_tweak_trash_meta_caps', 11, 4 );

// tweak for replies
function BBP_tweak_trash_meta_caps( $caps, $cap, $user_id, $args ){

	// apply only to delete_reply and delete_topic
	if ( $cap == "delete_reply" || $cap == "delete_topic" ){
		// Get the post
		$_post = get_post( $args[0] );
		if ( !empty( $_post ) ) {

			// Get caps for post type object
			$post_type = get_post_type_object( $_post->post_type );
			$caps      = array();

			// Add 'do_not_allow' cap if user is spam or deleted
			if ( bbp_is_user_inactive( $user_id ) ) {
				$caps[] = 'do_not_allow';

			// Moderators can always edit forum content
			} elseif ( user_can( $user_id, 'moderate' ) ) {
				$caps[] = 'moderate';

			// User is author so allow edit if not in admin
            } elseif ( ! is_admin() && ( (int) $user_id === (int) $_post->post_author ) ) {
                $caps[] = $post_type->cap->delete_posts;

			// Unknown so map to delete_others_posts
			} else {
				$caps[] = $post_type->cap->delete_others_posts;
			}
		}

	}
	// return the capabilities
	return $caps;
}

