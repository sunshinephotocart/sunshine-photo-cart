<?php global $sunshine; ?>
<!DOCTYPE html>
<html>
<head>
	<title><?php wp_title(); ?></title>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0;" />
	<?php wp_head(); ?>
	<script>
		jQuery(document).ready(function($){
			$('#sunshine-mobile-menu').click(function(){
				$('#sunshine-mobile-menu i').toggleClass('fa-close');
				$('#sunshine-mobile-menu i').toggleClass('fa-bars');
				$('#sunshine-main-menu-container').toggleClass('open');
			});
		});
	</script>
</head>
<body <?php body_class(); ?> id="sunshine">

<header id="sunshine-header">

	<div id="sunshine-logo">
		<h1><a href="<?php bloginfo('url'); ?>"><?php sunshine_logo(); ?></a></h1>
	</div>

	<div id="sunshine-main-menu-container">
		<a href="#" id="sunshine-mobile-menu"><i class="fa fa-bars"></i> Menu</a>
		<?php sunshine_main_menu(); ?>
	</div>

	<?php if (!empty($sunshine->options['2013_gallery_password_box'])) { ?>
	<div id="sunshine-gallery-password-form">
		<label for="sunshine_gallery_password"><?php _e('Enter gallery password', 'sunshine'); ?></label>
		<?php sunshine_gallery_password_form(); ?>
	</div>
	<?php } ?>

	<?php if (!empty($sunshine->options['2013_search_box'])) { ?>
	<div id="sunshine-search-form">
		<label for="sunshine_search"><?php _e('Search', 'sunshine'); ?></label>
		<?php sunshine_search(); ?>
	</div>
	<?php } ?>

</header>

<div id="sunshine-main" class="sunshine-clearfix <?php sunshine_classes(); ?>">

	<?php do_action('sunshine_before_content'); ?>
