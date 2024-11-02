<?php
/*
Author: ChoiceCuts
Author URI: http://www.workwithchoicecuts.com
Description: ChoiceCuts Image Juggler Options
*/

/*

// Select your preferred lightbox. 1: jQuery lightbox 2: jQuery pirobox. Any other value >> default = no lightbox
define('LIGHTBOX_CHOICE', 0);

// Max lightbox popup height
define('LIGHTBOX_MAX_HEIGHT', 500);

// Max lightbox popup width
define('LIGHTBOX_MAX_WIDTH', 900);

*/

// mt_options_page() displays the page content for the Test Options submenu
function ccImj_options_page() {

add_option('ccImj_filterFunction', 'none');
add_option('ccImj_filterLightbox', FALSE);
add_option('ccImj_filterSettings', NULL);

add_option('ccImj_useDefaultImage', TRUE);
add_option('ccImj_defaultImage', WP_PLUGIN_DIR.'/cc_image_juggler/includes/default_image.gif', '', 'yes');


/*check form submission and update options*/
if (isSet($_POST['Submit'])) {

	if (isset($_POST['ccImj_filterSetting'])) {
		update_option('ccImj_filterFunction', $_POST['ccImj_filterSetting']);

		$ccImj_filterSettings = Array();
		$ccImj_filterSettings['min_width'] = (! empty($_POST['ccImj_flexiSetting_minWidth'])) ? $_POST['ccImj_flexiSetting_minWidth'] : 100;
		$ccImj_filterSettings['max_width'] = (! empty($_POST['ccImj_flexiSetting_maxWidth'])) ? $_POST['ccImj_flexiSetting_maxWidth'] : 600;
		$ccImj_filterSettings['width'] = (! empty($_POST['ccImj_flexiSetting_width'])) ? $_POST['ccImj_flexiSetting_width'] : 100;
		$ccImj_filterSettings['size'] = (! empty($_POST['ccImj_flexiSetting_size'])) ? $_POST['ccImj_flexiSetting_size'] : 50;

		$filterOptions = serialize($ccImj_filterSettings);
		update_option('ccImj_filterSettings', $filterOptions);
	}
	
	if (isset($_POST['ccImj_filterLightbox'])) {
		update_option('ccImj_filterLightbox', TRUE);
		update_option('ccImj_preferredLightbox', $_POST['ccImj_preferredLightbox']);
	}
	else {
		update_option('ccImj_filterLightbox', FALSE);
		update_option('ccImj_preferredLightbox', 0);
	}
	
	if (isset($_POST['ccImj_useDefaultSetting'])) {
		update_option('ccImj_useDefaultImage', TRUE);
	}
	else {
		update_option('ccImj_useDefaultImage', FALSE);
	}
	
	if (isset($_POST['ccImj_defaultImage'])) {
		update_option('ccImj_defaultImage', $_POST['ccImj_defaultImage']);
	}
}

$ccImj_filterFunction = (get_option('ccImj_filterFunction') == NULL) ? 'none' : get_option('ccImj_filterFunction');
$ccImj_filterLightbox = get_option('ccImj_filterLightbox');
$ccImj_preferredLightbox = get_option('ccImj_preferredLightbox');
$ccImj_useDefault = get_option('ccImj_useDefault');
$ccImj_defaultImage = get_option('ccImj_defaultImage');

$filterOptions = get_option('ccImj_filterSettings');
$ccImj_filterSettings = @ unserialize( $filterOptions );
$ccImj_filterSettings['min_width'] = (! empty($ccImj_filterSettings['min_width'])) ? $ccImj_filterSettings['min_width'] : 100;
$ccImj_filterSettings['max_width'] = (! empty($ccImj_filterSettings['max_width'])) ? $ccImj_filterSettings['max_width'] : 600;
$ccImj_filterSettings['width'] = (! empty($ccImj_filterSettings['width'])) ? $ccImj_filterSettings['width'] : 100;
$ccImj_filterSettings['size'] = (! empty($ccImj_filterSettings['size'])) ? $ccImj_filterSettings['size'] : 50;

?>

<div class="wrap">
	<h2><?php _e('ChoiceCuts Image Juggler Options', 'wpqc') ?></h2>
	
<?php /*
	<form name="form1" method="post" action="<?php echo get_option('siteurl') . '/wp-admin/admin.php?page=cc_image_juggler/cc_image_juggler_options.php' ?>&amp;updated=true"> 
*/ ?>

	<form name="form1" method="post" action="">
		
		<?php wp_nonce_field('update-options'); ?>
		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="ccImj_filterSetting,ccImj_flexiSetting_width,ccImj_flexiSetting_minWidth,ccImj_flexiSetting_maxWidth,ccImj_flexiSetting_size,ccImj_filterLightbox,ccImj_preferredLightbox,ccImj_useDefault" />


		<h4><?php _e('Enable universal image resize filter', 'wpqc') ?></h4>

		<p><input name="ccImj_filterSetting" type="radio" value="none" <?= ($ccImj_filterFunction == 'none') ? 'checked=checked' : NULL ; ?> />  No Filter</p>
		<p>
			<input name="ccImj_filterSetting" type="radio" value="width" <?= ($ccImj_filterFunction == 'width') ? 'checked=checked' : NULL ; ?> />  Max Width only<br/>
			<blockquote>
				Width: <input name="ccImj_flexiSetting_width" type="text" value="<?= $ccImj_filterSettings['width']; ?>" />
			</blockquote>
		</p>
		
		<p><input name="ccImj_filterSetting" type="radio" value="flexi" <?= ($ccImj_filterFunction == 'flexi') ? 'checked=checked' : NULL ; ?> />  Min &amp; Max Width<br/>
			<blockquote>
				Min: <input name="ccImj_flexiSetting_minWidth" type="text"  value="<?= $ccImj_filterSettings['min_width']; ?>" />  Max: <input name="ccImj_flexiSetting_maxWidth" type="text" value="<?= $ccImj_filterSettings['max_width']; ?>" />
			</blockquote>
		</p>
		
		<p><input name="ccImj_filterSetting" type="radio" value="crop" <?= ($ccImj_filterFunction == 'crop') ? 'checked=checked' : NULL ; ?> />  Crop Width
			<blockquote>
				Size: <input name="ccImj_flexiSetting_size" type="text" value="<?= $ccImj_filterSettings['size']; ?>" />
			</blockquote>
		</p>

		<p><input name="ccImj_filterLightbox" type="checkbox" <?php echo ($ccImj_filterLightbox==TRUE)? "checked='checked'" : NULL; ?>/> <?php _e('Integrate lightbox with universal resize', 'wpqc') ?></p>


		<h4><?php _e('General Settings', 'wpqc') ?></h4>
			
		<p><blockquote>
				Preferred LightBox: <select name="ccImj_preferredLightbox" type="text" value="<?= $ccImj_preferredLightbox; ?>" />
					<option value='0' <?php echo ($ccImj_preferredLightbox==0)? "selected='selected'" : NULL; ?>>none</option>
					<option value='1' <?php echo ($ccImj_preferredLightbox==1)? "selected='selected'" : NULL; ?>>jQuery lightBox</option>
					<option value='2' <?php echo ($ccImj_preferredLightbox==2)? "selected='selected'" : NULL; ?>>piroBox</option>
					<!-- <option value='3' <?php echo ($ccImj_preferredLightbox==3)? "selected='selected'" : NULL; ?>>fancyBox</option>  -->
				</select>
			</blockquote>
		</p>
		<p><input name="ccImj_useDefault" type="checkbox" <?= ($ccImj_useDefault == TRUE) ? 'checked=checked' : NULL ; ?> /> <?php _e('Use Default Image', 'wpqc') ?></p>
		<p><input name="ccImj_defaultImage" type="text" id="ccImj_defaultImage" value="<?= stripslashes($ccImj_defaultImage); ?>" width='350px'/> <?php _e('Default Image Path', 'wpqc') ?></p>

		<p class="submit"><input type="submit" name="Submit" value="<?php _e('Update Options', 'wpqc') ?> &raquo;" /></p>
	</form>
</div>

<?php } ?>
