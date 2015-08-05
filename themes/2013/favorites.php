<?php global $sunshine; load_template(SUNSHINE_PATH.'themes/2013/header.php'); ?>

<h1><?php _e('Favorites', 'sunshine'); ?></h1>
<?php echo apply_filters('the_content', $post->post_content); ?>

<div id="sunshine-action-menu" class="sunshine-clearfix">
	<?php sunshine_action_menu(); ?>
</div>
<div id="sunshine-gallery-images">
<?php 
if (!empty($sunshine->favorites)) {
	echo '<ul class="sunshine-col-'.$sunshine->options['columns'].'">';
	foreach ($sunshine->favorites as $favorite_id) {
		$thumb = wp_get_attachment_image_src($favorite_id, 'thumbnail');
		$url = get_attachment_link($favorite_id);
?>
	<li id="sunshine-image-<?php echo $favorite_id; ?>">
		<?php
		$thumb = wp_get_attachment_image_src($favorite_id, 'thumbnail');
		$image_html = '<a href="'.get_permalink($favorite_id).'"><img src="'.$thumb[0].'" alt="" class="sunshine-image-thumb" /></a>';
		echo apply_filters('sunshine_favorites_image_html', $image_html, $favorite_id, $thumb);
		?>
		<div class="sunshine-image-menu-container">
			<?php sunshine_image_menu($favorite_id); ?>
		</div>
	</li>
<?php
	}
	echo '</ul>';
} else {
	echo '<p>'.__('You have no images marked as a favorite', 'sunshine').'</p>';
}
?>
</div>

<script>
jQuery(document).ready(function() {	
	jQuery('#sunshine-gallery-images li:nth-child(<?php echo $sunshine->options['columns']; ?>n+1), #sunshine-gallery-images li:first-child').addClass('first');
});
</script>

<?php load_template(SUNSHINE_PATH.'themes/2013/footer.php'); ?>
