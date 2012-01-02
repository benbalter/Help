<?php

/**
 * Redirect root URL to /questions with 301 header
 */
function hh_help_home_redirect() {

	if ( !is_home() )
		return;
		
	if ( get_query_var( 'json' ) )
		return;

	wp_redirect( home_url( '/questions/' ), '301' );
	exit();

}

add_action( 'template_redirect', 'hh_help_home_redirect', 999 );

/**
 * Remove 404 class from body of ask-question page 
 */
function hh_help_no_404( $classes ) {

	if ( !get_query_var( 'qa_ask' ) )
		return $classes;

	$error = array_search( 'error404', $classes );		
	unset ( $classes[ $error ] );
	
	return $classes;
}

add_filter( 'body_class', 'hh_help_no_404' );

/**
 * Add missing close div tags to end of body
 */
function hh_help_wrapper_footer() { ?>
			</div><!-- #content -->
		</div><!-- #primary -->
<?php 
	//sometimes get_sidebar is called twice for some reason, so remove the hook just in case
	remove_action( 'get_sidebar', 'hh_help_wrapper_footer' );
	remove_action( 'get_footer', 'hh_help_wrapper_footer' );
}

add_action( 'get_sidebar', 'hh_help_wrapper_footer' );
add_action( 'get_footer', 'hh_help_wrapper_footer' );

/**
 * Plugin does not natively support comments on answers, 
 * hook into get_usermeta to inject comments.php template below each answer
 */
function hh_help_answer_comments( $type, $obj, $key, $single ) {
	
	//only hook into the specific meta
	if ( $key != '_qa_rep' )
		return;
	
	//remove filter to prevent recursion
	remove_filter( 'get_user_metadata', 'hh_help_answer_comments' );
	echo number_format_i18n( get_usermeta( $obj, $key, $single ) );
	add_filter( 'get_user_metadata', 'hh_help_answer_comments', 10, 4 );
	 
	//close the author meta box
	echo '</div></div></div>';

	//comment template
	include( 'comments.php' );

	//open a bunch of divs because the plugin's still gonna output the reputation and close the author box
	echo '<div><div><div style="display:none;">';
	
}

/**
 * Comment callback
 */
function hh_help_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case 'pingback' :
		case 'trackback' :
	?>
	<li class="post pingback">
		<p><?php _e( 'Pingback:', 'twentyeleven' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __( 'Edit', 'twentyeleven' ), '<span class="edit-link">', '</span>' ); ?></p>
	<?php
			break;
		default :
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
		<article id="comment-<?php comment_ID(); ?>" class="comment">

			<div class="comment-content"><?php comment_text(); ?></div>

			<footer class="comment-meta">
				<div class="comment-author vcard">
					<?php
						$avatar_size = 30;

						echo get_avatar( $comment, $avatar_size );

						/* translators: 1: comment author, 2: date and time */
						printf( __( '&mdash; %1$s on %2$s', 'twentyeleven' ),
							sprintf( '<span class="fn">%s</span>', get_comment_author_link() ),
							sprintf( '<a href="%1$s"><time pubdate datetime="%2$s">%3$s</time></a>',
								esc_url( get_comment_link( $comment->comment_ID ) ),
								get_comment_time( 'c' ),
								/* translators: 1: date, 2: time */
								sprintf( __( '%1$s at %2$s', 'twentyeleven' ), get_comment_date(), get_comment_time() )
							)
						);
					?>

					<?php edit_comment_link( __( 'Edit', 'twentyeleven' ), '<span class="edit-link">', '</span>' ); ?>
				</div><!-- .comment-author .vcard -->

				<?php if ( $comment->comment_approved == '0' ) : ?>
					<em class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'twentyeleven' ); ?></em>
					<br />
				<?php endif; ?>

			</footer>

		</article><!-- #comment-## -->

	<?php
			break;
	endswitch;
}

/**
 * Enqueue custom js and jquery.cookie dependency
 */
function hh_help_enqueue() {

	$suffix = ( WP_DEBUG ) ? '.dev' : '';
	
	wp_enqueue_script( 'hh_help', get_stylesheet_directory_uri() . '/js/help' . $suffix . '.js', array( 'jquery' ), filemtime( dirname( __FILE__ ) . '/js/help' . $suffix . '.js' ), true );
	
	
}

add_action( 'wp_enqueue_scripts', 'hh_help_enqueue' );

/**
 * Prevent ask page from 404ing
 */
function hh_help_no_ask_404( $title ) {
	
	if ( !get_query_var( 'qa_ask' ) )
		return $title;
		
	return "Ask a Question | " . get_bloginfo( 'name' );
	
}

add_filter( 'wp_title', 'hh_help_no_ask_404' );

/**
 * Whenever home url is queried, return /questions/
 */
function hh_home_url_filter( $output, $show ) {

	return ( $show == 'url' ) ? $output . '/questions' : $output;

}

add_filter( 'bloginfo_url', 'hh_home_url_filter', 10, 2 );

/**
 * Corrects formatting of comment permalinks
 * By default, permalinks would be in the format of http://{question}/#answer-{answerID}#comment-{commentID}
 * This removes the answer anchor
 */
function hh_help_comment_link_filter( $link ) {
	
	//only one #, no need to do anything
	if ( substr_count( $link, '#' ) <= 1 )
		return $link;
	
	$parts = explode( '#', $link );
	
	return $parts[0] . '#' . $parts[2];
}

add_filter( 'get_comment_link', 'hh_help_comment_link_filter' );

/**
 * Adds meta tags to head of homepage
 */
function hh_help_home_meta() {

	if ( get_query_var('post_type') != 'question' || get_query_var( 'qa_unanswered' ) )
		return;

	?>
	<!--<meta name="description" content="Hacks/Hackers Help is an interactive forum where journalists ('hacks') and technologists ('hackers') figure out what's possible in news and media and how to make it happen." />-->
	<meta name="keywords" content="hacks, hackers, help, q&a, news, journalism, journalists, technologists, organization, news, information, future, network" />
	<?php
}

add_action( 'wp_head', 'hh_help_home_meta' );

/**
 * Adds comma separated to the tags: label on the ask-question page
 * Done this way so we get future updates of the plugin
 */
function hh_help_add_comma_separated( $trans, $text ) {
	
	if ( $text != 'Tags:' )
		return $trans; 

	$trans .= '<br /><span class="help-text">(comma separated)</span>';

	return $trans;

}

/**
 * Add filter to gettext only on ask question page
 * Filter is run a lot, so only run when we need to
 */
function hh_help_add_comma_separated_filter() {

	if ( !get_query_var( 'qa_ask' ) )
		return;
		
	add_filter( 'gettext', 'hh_help_add_comma_separated', 10, 2 );
	
}

add_action( 'wp_head', 'hh_help_add_comma_separated_filter' );

/**
 * Rewrites feed to questions post type by default
 */
function hh_help_feed_link_filter( $feed ) {
	
	if ( strpos( $feed, '/questions' ) !== false || strpos( $feed, 'comments' ) !== false )
		return $feed;
		
	return str_replace( '/feed', '/questions/feed', $feed );
	
}

add_filter( 'feed_link', 'hh_help_feed_link_filter' );

/**
 * Prevent the QA plugin from paginating answers and killing our SEO
 */
function hh_help_dont_paginate_answers( $q ) {
	
	//make sure this is the answer query
	if ( 	$q->get( 'post_type' ) != 'answer' ||
			$q->get( 'orderby' ) != 'qa_score' ||
			$q->get( 'posts_per_page' ) != QA_ANSWERS_PER_PAGE )
		return $q;
		
	$q->set( 'posts_per_page', -1 );
	$q->set( 'is_paged', false );
	$q->set( 'max_num_pages', 1 );
		
	return $q;
	
}