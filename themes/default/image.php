<?php load_template(SUNSHINE_PATH.'themes/default/header.php'); ?>

<div id="sunshine-next-prev">
	<span id="sunshine-prev"><?php sunshine_adjacent_image_link( true, '', '&laquo;' ); ?></span>
	<span id="sunshine-next"><?php sunshine_adjacent_image_link( false, '', '&raquo;' ); ?></span>
</div>
<h1>
	<?php echo get_the_title(SunshineFrontend::$current_image->ID); ?>
	<span><?php _e('Return to', 'sunshine'); ?> <a href="<?php echo get_permalink(SunshineFrontend::$current_image->post_parent); if (SunshineSession::instance()->current_gallery_page) { echo '?pagination='.SunshineSession::instance()->current_gallery_page[1]; } ?>"><?php echo get_the_title(SunshineFrontend::$current_gallery->ID); ?></a></span>
</h1>
<div id="sunshine-action-menu" class="sunshine-clearfix">
	<?php sunshine_action_menu(); ?>
</div>
<div id="sunshine-image">
	<?php sunshine_image(); ?>
</div>
<div id="sunshine-add-form">
	<?php sunshine_add_to_cart_form(); ?>	
</div>

<?php if (comments_open(SunshineFrontend::$current_image->ID) && get_post_meta(SunshineFrontend::$current_image->post_parent, 'sunshine_gallery_image_comments', true)) { ?>
<div id="sunshine-image-comments">
	<?php
	$comments = get_comments('post_id='.SunshineFrontend::$current_image->ID.'&order=ASC');
	if ($comments) {
	?>
	<ol>
	<?php 
	wp_list_comments('type=comment&avatar_size=0', $comments); 
	?>
	</ol>
	<?php 
	}
	comment_form(
		array(
			'comment_notes_before' => '',
			'comment_notes_after' => '',
			'logged_in_as' => '',
			'id_form' => 'sunshine-order-comment',
			'id_submit' => 'sunshine-submit',
			'title_reply' => 'Add Comment'
		),
		SunshineFrontend::$current_image->ID
	); 
	?>
</div>
<?php } ?>

<?php load_template(SUNSHINE_PATH.'themes/default/footer.php'); ?>
