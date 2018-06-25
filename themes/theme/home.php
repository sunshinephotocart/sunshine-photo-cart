<?php global $sunshine; ?>
<div id="sunshine" class="sunshine-clearfix <?php sunshine_classes(); ?>">

	<?php do_action('sunshine_before_content'); ?>

	<div id="sunshine-main" class="sunshine-clearfix">		

		<div id="sunshine-gallery-list">
			<?php
			$galleries = sunshine_get_galleries();
			if ($galleries->have_posts()) {
			?>
			<ul class="sunshine-gallery-list sunshine-col-<?php echo $sunshine->options['columns']; ?>">
			<?php while ( $galleries->have_posts() ) : $galleries->the_post(); ?>
				<li class="<?php sunshine_gallery_class(); ?>">
					<a href="<?php the_permalink(); ?>"><?php sunshine_featured_image(); ?></a><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
				</li>
			<?php endwhile; wp_reset_postdata(); ?>
			</ul>
			<?php } else { ?>
				<p><?php _e('Sorry, no galleries have been setup yet', 'sunshine'); ?></p>
			<?php } ?>
		</div>

	</div>

	<?php do_action('sunshine_after_content'); ?>

</div>
