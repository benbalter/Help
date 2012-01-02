<?php
/**
 * The template for displaying comments on answers.
 *
 */

//note: plugin uses setup_postdata() so global $id should contain the answer/question ID 
global $id;
$comments = get_comments( array( 'post_id' => $id ) );
 
?>
<div id="comments">

	<?php if ( !empty( $comments ) ) : ?>
		<ol class="commentlist">
			<?php
				/* Loop through and list the comments. Tell wp_list_comments()
				 * to use twentyeleven_comment() to format the comments.
				 * If you want to overload this in a child theme then you can
				 * define twentyeleven_comment() and that will be used instead.
				 * See twentyeleven_comment() in twentyeleven/functions.php for more.
				 */
				wp_list_comments( array( 'callback' => 'hh_help_comment' ), $comments );
			?>
		</ol>
	
	<?php endif; ?>
	
	<?php comment_form(); ?>

</div><!-- #comments -->
