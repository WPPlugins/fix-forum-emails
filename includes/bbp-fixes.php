<?php

/**
 * Sends notification emails for new replies to subscribed topics.
 *
 * This is almost identical to the native BBP function;  changes
 * indicated in the code below.
 *
 * This is a workaround for the issues in the following BBPress tickets:
 * https://bbpress.trac.wordpress.org/ticket/2722
 * https://bbpress.trac.wordpress.org/ticket/2865
 * Props to @thebrandonallen for the original patch code, which can
 * be found in ticket 2865 above.
 */
function flizfix_bbp_notify_topic_subscribers_2_5_8( $reply_id = 0, $topic_id = 0, $forum_id = 0, $anonymous_data = false, $reply_author = 0 ) {

	// Bail if subscriptions are turned off
	if ( !bbp_is_subscriptions_active() ) {
		return false;
	}

	/** Validation ************************************************************/

	$reply_id = bbp_get_reply_id( $reply_id );
	$topic_id = bbp_get_topic_id( $topic_id );
	$forum_id = bbp_get_forum_id( $forum_id );

	/** Topic *****************************************************************/

	// Bail if topic is not published
	if ( !bbp_is_topic_published( $topic_id ) ) {
		return false;
	}

	/** Reply *****************************************************************/

	// Bail if reply is not published
	if ( !bbp_is_reply_published( $reply_id ) ) {
		return false;
	}

	// Poster name
	$reply_author_name = bbp_get_reply_author_display_name( $reply_id );

	/** Mail ******************************************************************/

	// Remove filters from reply content and topic title to prevent content
	// from being encoded with HTML entities, wrapped in paragraph tags, etc...
	remove_all_filters( 'bbp_get_reply_content' );
	// FLIZ CHANGED FROM THE ORIGINAL
	//	remove_all_filters( 'bbp_get_topic_title'   );
	remove_all_filters( 'the_title'   );
	// END FLIZ CHANGED FROM THE ORIGINAL

	// Strip tags from text and setup mail data
	// FLIZ CHANGED FROM THE ORIGINAL
	//	$topic_title   = strip_tags( bbp_get_topic_title( $topic_id ) );
	//	$reply_content = strip_tags( bbp_get_reply_content( $reply_id ) );
	$topic_title   = wp_specialchars_decode( 
				strip_tags( bbp_get_topic_title( $topic_id ) ), ENT_QUOTES ); 
	$reply_content = wp_specialchars_decode( strip_tags( 
				bbp_get_reply_content( $reply_id ) ), ENT_QUOTES );
	// END FLIZ CHANGED FROM THE ORIGINAL
	$reply_url     = bbp_get_reply_url( $reply_id );
	$blog_name     = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

	// For plugins to filter messages per reply/topic/user
	$message = sprintf( __( '%1$s wrote:

%2$s

Post Link: %3$s

-----------

You are receiving this email because you subscribed to a forum topic.

Login and visit the topic to unsubscribe from these emails.', 'bbpress' ),

		$reply_author_name,
		$reply_content,
		$reply_url
	);

	$message = apply_filters( 'bbp_subscription_mail_message', $message, $reply_id, $topic_id );
	if ( empty( $message ) ) {
		return;
	}

	// For plugins to filter titles per reply/topic/user
	$subject = apply_filters( 'bbp_subscription_mail_title', '[' . $blog_name . '] ' . $topic_title, $reply_id, $topic_id );
	if ( empty( $subject ) ) {
		return;
	}

	/** Users *****************************************************************/

	// Get the noreply@ address
	$no_reply   = bbp_get_do_not_reply_address();

	// Setup "From" email address
	$from_email = apply_filters( 'bbp_subscription_from_email', $no_reply );

	// Setup the From header
	$headers = array( 'From: ' . get_bloginfo( 'name' ) . ' <' . $from_email . '>' );

	// Get topic subscribers and bail if empty
	$user_ids = bbp_get_topic_subscribers( $topic_id, true );

	// Dedicated filter to manipulate user ID's to send emails to
	$user_ids = apply_filters( 'bbp_topic_subscription_user_ids', $user_ids );
	if ( empty( $user_ids ) ) {
		return false;
	}

	// Loop through users
	foreach ( (array) $user_ids as $user_id ) {

		// Don't send notifications to the person who made the post
		if ( !empty( $reply_author ) && (int) $user_id === (int) $reply_author ) {
			continue;
		}

		// Get email address of subscribed user
		$headers[] = 'Bcc: ' . get_userdata( $user_id )->user_email;
	}

	/** Send it ***************************************************************/

	// Custom headers
	$headers  = apply_filters( 'bbp_subscription_mail_headers', $headers  );
 	$to_email = apply_filters( 'bbp_subscription_to_email',     $no_reply );

	do_action( 'bbp_pre_notify_subscribers', $reply_id, $topic_id, $user_ids );

	// Send notification email
	wp_mail( $to_email, $subject, $message, $headers );

	do_action( 'bbp_post_notify_subscribers', $reply_id, $topic_id, $user_ids );

	return true;
}




/**
 * Sends notification emails for new topics to subscribed forums
 *
 * This is almost identical to the native BBP function;  changes
 * indicated in the code below.
 *
 * This is a workaround for the issues in the following BBPress tickets:
 * https://bbpress.trac.wordpress.org/ticket/2722
 * https://bbpress.trac.wordpress.org/ticket/2865
 * Props to @thebrandonallen for the original patch code, which can
 * be found in ticket 2865 above.
 */
function flizfix_bbp_notify_forum_subscribers_2_5_8( $topic_id = 0, $forum_id = 0, $anonymous_data = false, $topic_author = 0 ) {

	// Bail if subscriptions are turned off
	if ( !bbp_is_subscriptions_active() ) {
		return false;
	}

	/** Validation ************************************************************/

	$topic_id = bbp_get_topic_id( $topic_id );
	$forum_id = bbp_get_forum_id( $forum_id );

	/**
	 * Necessary for backwards compatibility
	 *
	 * @see https://bbpress.trac.wordpress.org/ticket/2620
	 */
	$user_id  = 0;

	/** Topic *****************************************************************/

	// Bail if topic is not published
	if ( ! bbp_is_topic_published( $topic_id ) ) {
		return false;
	}

	// Poster name
	$topic_author_name = bbp_get_topic_author_display_name( $topic_id );

	/** Mail ******************************************************************/

	// Remove filters from reply content and topic title to prevent content
	// from being encoded with HTML entities, wrapped in paragraph tags, etc...
	remove_all_filters( 'bbp_get_topic_content' );
	// FLIZ CHANGED FROM THE ORIGINAL
	//	remove_all_filters( 'bbp_get_topic_title'   );
	remove_all_filters( 'the_title'   );
	// END FLIZ CHANGED FROM THE ORIGINAL

	// Strip tags from text and setup mail data
	// FLIZ CHANGED FROM THE ORIGINAL
	//	$topic_title   = strip_tags( bbp_get_topic_title( $topic_id ) );
	//	$topic_content = strip_tags( bbp_get_topic_content( $topic_id ) );
	$topic_title   = wp_specialchars_decode( 
			strip_tags( bbp_get_topic_title( $topic_id ) ), ENT_QUOTES ); 
	$topic_content = wp_specialchars_decode( strip_tags( 
			bbp_get_topic_content( $topic_id ) ), ENT_QUOTES ); 
	// END FLIZ CHANGED FROM THE ORIGINAL
	$topic_url     = get_permalink( $topic_id );
	$blog_name     = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

	// For plugins to filter messages per reply/topic/user
	$message = sprintf( __( '%1$s wrote:

%2$s

Topic Link: %3$s

-----------

You are receiving this email because you subscribed to a forum.

Login and visit the topic to unsubscribe from these emails.', 'bbpress' ),

		$topic_author_name,
		$topic_content,
		$topic_url
	);

	$message = apply_filters( 'bbp_forum_subscription_mail_message', $message, $topic_id, $forum_id, $user_id );
	if ( empty( $message ) ) {
		return;
	}

	// For plugins to filter titles per reply/topic/user
	$subject = apply_filters( 'bbp_forum_subscription_mail_title', '[' . $blog_name . '] ' . $topic_title, $topic_id, $forum_id, $user_id );
	if ( empty( $subject ) ) {
		return;
	}

	/** User ******************************************************************/

	// Get the noreply@ address
	$no_reply   = bbp_get_do_not_reply_address();

	// Setup "From" email address
	$from_email = apply_filters( 'bbp_subscription_from_email', $no_reply );

	// Setup the From header
	$headers = array( 'From: ' . get_bloginfo( 'name' ) . ' <' . $from_email . '>' );

	// Get topic subscribers and bail if empty
	$user_ids = bbp_get_forum_subscribers( $forum_id, true );

	// Dedicated filter to manipulate user ID's to send emails to
	$user_ids = apply_filters( 'bbp_forum_subscription_user_ids', $user_ids );
	if ( empty( $user_ids ) ) {
		return false;
	}

	// Loop through users
	foreach ( (array) $user_ids as $user_id ) {

		// Don't send notifications to the person who made the post
		if ( !empty( $topic_author ) && (int) $user_id === (int) $topic_author ) {
			continue;
		}

		// Get email address of subscribed user
		$headers[] = 'Bcc: ' . get_userdata( $user_id )->user_email;
	}

	/** Send it ***************************************************************/

	// Custom headers
	$headers  = apply_filters( 'bbp_subscription_mail_headers', $headers  );
	$to_email = apply_filters( 'bbp_subscription_to_email',     $no_reply );

	do_action( 'bbp_pre_notify_forum_subscribers', $topic_id, $forum_id, $user_ids );

	// Send notification email
	wp_mail( $to_email, $subject, $message, $headers );

	do_action( 'bbp_post_notify_forum_subscribers', $topic_id, $forum_id, $user_ids );

	return true;
}
