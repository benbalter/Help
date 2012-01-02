<?php 
//load standard sidebar, but not on single-question
global $template;
if ( basename( $template ) != 'single-question.php' )
	include( get_template_directory() . '/sidebar.php' ); 
?>