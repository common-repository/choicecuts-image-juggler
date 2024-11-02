<?php
/*
Plugin Name: ChoiceCuts Image Juggler
Plugin URI: http://www.workwithchoicecuts.com
Version: 0.8.3.2, 12/10/2010
Author: http://www.workwithchoicecuts.com
Description: ChoiceCuts Image Juggler makes all image handling a piece of cake. Stripping images, resizing to Landscape, resizing to Portrait and creating Thumbnails are all handled. There is also a couple of lightboxes integrated so as to present your images in their original glory. This is all made possible through the use of the Thumbnail.inc.php library, which can be found at http://trac.gxdlabs.com/projects/phpthumb/wiki/Legacy.


REQUIREMENTS OF USAGE
* Check out the readme.txt file for full details of the server requirements

* All images must be on the same server and domain as the plugin
* Any usage would appreciate feedback, good/bad/ugly. This plugin is totally free, we only ask that you help us make this better for everybody.
* If you are aware of any documentation on developing admin interface Wordpress plugin please send it our way.


FEATURES
Automatically detects which version of PHP your server is running and selects the appropriate Thumbnail library version

Resize to required Width
Resize to required Width with proportional reduction of height to a specified minimum, excess height cropped
Resize to required Height
Resize to required Height with proportional reduction of width, excess width cropped
Resize first image to required Width
Resize first image to required Height
Create square thumbnail from first image by proportionally reducing image to specified size and cropping excess
Create square thumbnail from passed image_path by proportionally reducing image to specified minimum

Remove all images from passed string/content, optionally apply 'the_content' filter

Automatically resize any images outside set width values by add_filter on the_content
Automatically resize all images to set width by add_filter on the_content
Automatically resize and crop all images to set size by add_filter on the_content

Link any resizing to the lightbox  or link to alternate URL
Integrated image lightboxes, jQuery lightBox & pirobox
Integrated image lightBox, with oversized image handling


TODO
Resize remote images
Make filters available by template/category
Pass parameter options into Filter functions
Integrate image captions into lightbox

*/

include 'cc_image_juggler_options.php';

// -------------------------------------------------------------------------- PLUGIN CONSTANTS & SETTINGS

if ( ! defined( 'WP_CONTENT_URL' ) ) define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) ) define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) ) define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) ) define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );


// Select your preferred lightbox. 1: jQuery lightbox 2: jQuery pirobox 3: jQuery fancybox. Any other value >> default = no lightbox
define('LIGHTBOX_CHOICE', get_option('ccImj_preferredLightbox'));

// Max lightbox popup height
define('LIGHTBOX_MAX_HEIGHT', 500);

// Max lightbox popup width
define('LIGHTBOX_MAX_WIDTH', 900);




switch ( LIGHTBOX_CHOICE ) {
	case 1: // jQuery Lightbox
		$lbDirectory = 'lightbox';
		define('LIGHTBOX', TRUE);
		break;
	case 2: // jQuery piroBox
		$lbDirectory = 'pirobox';
		define('LIGHTBOX', TRUE);
		break;
	case 3: // jQuery fancyBox
		$lbDirectory = 'fancybox';
		define('LIGHTBOX', TRUE);
		break;
	default:
		$lbDirectory = NULL;
		define('LIGHTBOX', FALSE);
		break;
}


define('PLUGIN_PATH', WP_PLUGIN_URL .'/choicecuts-image-juggler/includes/');
define('LIGHTBOX_PATH', WP_PLUGIN_URL .'/choicecuts-image-juggler/includes/lightbox/'.$lbDirectory.'/');

function ccImj_addOptionsPage()
{
	add_options_page('ChoiceCuts Image Juggler - Options', 'CC-ImageJuggler', 8, 'ccimj-options', 'ccImj_options_page');
}
add_action('admin_menu', 'ccImj_addOptionsPage');



// -------------------------------------------------------------------------- INTERNAL PLUGIN UTILITIES
	
	
	/**
	* ccImj_lightBox: insert the required code into the tempalte header so that a chosen lightbox can be integrated
	*/
	function ccImj_lightBox()
	{
		$str = '<script src="http://code.jquery.com/jquery-1.4.2.min.js" type="text/javascript"></script>';
		switch ( LIGHTBOX_CHOICE ) {
			case 1:
				$str .= "
					<link rel='stylesheet' type='text/css' href='".LIGHTBOX_PATH."jquery.lightbox.css' />  
					<script type='text/javascript' src='".LIGHTBOX_PATH."jquery.lightbox.js'></script>
				
				    <script type='text/javascript'>
						jQuery(function() { jQuery('a.ccImj-lightbox').lightBox({
							imageLoading: '".LIGHTBOX_PATH."images/lightbox-ico-loading.gif',
							imageBtnClose: '".LIGHTBOX_PATH."images/lightbox-btn-close.gif',
							imageBtnPrev: '".LIGHTBOX_PATH."images/lightbox-btn-prev.gif',
							imageBtnNext: '".LIGHTBOX_PATH."images/lightbox-btn-next.gif',
						}); });
				    </script>
					";
				break;
			case 2:
				$str .= "
					<link rel='stylesheet' type='text/css' href='".LIGHTBOX_PATH."css_pirobox/pirobox_w.css' />  
					<script type='text/javascript' src='".LIGHTBOX_PATH."piroBox.js'></script>

					<script type='text/javascript'>
					jQuery(document).ready(function(){  
						jQuery('.thumbs').piroBox({  
							mySpeed: 500,   
							bg_alpha: 0.7,  
							pathLoader : 'url(".LIGHTBOX_PATH."css_pirobox/ajax-loader_w.gif) center center no-repeat;',   
							gallery : '.pirobox li a',   
							gallery_li : '.pirobox li',  
							single : '.single a',
							next_class : '.next',
							previous_class : '.previous'
						});   
					});  
					</script>
					";
				break;
			case 3:
				$str .= "
					<link rel='stylesheet' href='".LIGHTBOX_PATH."jquery.fancybox-1.3.0.css' type='text/css' media='screen'>
					<script type='text/javascript' src=".LIGHTBOX_PATH."jquery.fancybox-1.3.0.pack.js'></script>

				    <script type='text/javascript'>
						jQuery(document).ready(function(){  
							jQuery(function() { jQuery('a.ccImj-lightbox').fancybox(); });
						});  
				    </script>
					";
				break;
		}
		
		echo $str;
	}
	if ( LIGHTBOX ) {
		add_action('wp_head', 'ccImj_lightBox');
	}	


	/**
	* ccImj_maxDims - compensates for the jQuery lightBox which doesn't restrict the size of the original large image. Such that very large original images may be larger than the users screen.
	* @param img_name: string - filename path
	* @return img_path: string - image resize url
	*/
	function ccImj_maxDims( $img_path, $width, $height )
	{
		list($img_width, $img_height, $img_type, $img_attr) = getimagesize( $img_path );
		
		if ($img_width > $img_height) {
			if ($img_width > $width) {
				return ccImj_resizeWidthURL($img_path, $width, $height);
			}
		}
		else {
			if ($img_height > $height) {
				return ccImj_resizeHeightURL($img_path, $width);
			}
		}
		return $img_path;
	}
	

	/**
	* ccImj_addLink - depending on the passed parameters, adds a link around resized image html_partial froma  variety of options. including integrated lightbox option
	* @param img_name: string - filename path
	* @return img_path: string - image resize url
	*/
	function ccImj_addLink( $html_partial, $link, $img_path=NULL )
	{
		if ( $link != FALSE ) {
			if ($link === TRUE) {
				$lightbox = "class='ccImj-lightbox'";
				switch ( LIGHTBOX_CHOICE ) {
					case 1: $html_partial = "<a href='".ccImj_maxDims( $img_path, LIGHTBOX_MAX_WIDTH, LIGHTBOX_MAX_HEIGHT )."' $lightbox>".$html_partial."</a>";
						break;
					case 2: $html_partial = "<span class='single thumbs_all'><a href='$img_path' $lightbox>".$html_partial."</a></span>"; 
						break;
					case 3: $html_partial = "<a href='$img_path' $lightbox>".$html_partial."</a>"; 
						break;
				}
				return $html_partial;
			}
			else {
				return $html_partial = "<a href='$link'>".$html_partial."</a>";
			}
		}
		else {
			return $html_partial;
		}
	}


	/**
	* ccImj_isLocal - checks whether a passed file path is local
	* @param file_path: string - filename path
	* @return result: boolean
	*/
	function ccImj_isLocal( $filepath )
	{
		$pos = strpos( $filepath, get_bloginfo('url') );
		
		if ($pos === false) {
		    return FALSE;
		}
		else {
			return TRUE;
		}
	}


	/**
	* ccImj_abs2rel - translate an absolute image file path to a relative version of the same path, relative to the address of the scripts completing the actual image resizing
	* @param file_path: string - filename path
	* @return result: boolean
	*/	
	function ccImj_abs2rel( $filepath )
	{
		$path = get_bloginfo('url').'/wp-content/';
		$relativePath = str_replace($path, '../../../', $filepath); // clip unwanted absolute path from image filename, and replace with relative pathname
		
		return $relativePath;
	}



// -------------------------------------------------------------------------- IMAGE EXRACTION UTILITIES
	
	/**
	* ccImj_getFirstImg: extract the first image from passed $content, return image file path only
	* @param content: string - post content
	* @return img_array: array | img_array: boolean
	*/
	function ccImj_getFirstImg( $content )
	{
		$numOfImages = preg_match_all("/<img[^<>]+>/", $content, $img_array);
		
		if($numOfImages >= 1) {
		
			$image_url = $img_array[0][0];
			preg_match('@<(img|image)[^>]*src="([^"]*)"[^>]*>@Usi', $image_url, $matches);
			if (is_link( $matches[2] )) {
				$image_filepath = readlink( $matches[2]);
			}
			else {
				$image_filepath = $matches[2];
			}
		}
		else {
			if (get_option('ccImj_useDefaultImage')) {
				$image_filepath = get_option('ccImj_defaultImage');
			}
			else {
				$image_filepath = NULL;
			}
		}

		return $image_filepath;
	}


	/**
	* ccImj_getAllImg: extract all images from passed $content, returns a multi-dimensional array. The returned array contains an element for each image found. Within each element is an associative array as follows: ['tag'] - full html img tag. ['url'] - image file path only.
	* E.g. [0] => ('tag' =>'<img src="filename.jpg">' 'url' => 'uploads/filename.jpg'), [1] => ( 'tag' => '<img src="other_filename.png">', 'url' => 'uploads/other_filename.gif')
	* @param content: string - post content
	* @return img_array: array | img_array: boolean
	*/
	function ccImj_getAllImg( $content )
	{
		$numOfImages = preg_match_all("/<img[^<>]+>/", $content, $raw_images);
		
		if($numOfImages >= 1) {
		
			$img_array = Array();
			foreach($raw_images[0] AS $key => $value) {
			
				$img = Array();
				$img['tag'] = $value;
				preg_match('@<(img|image)[^>]*src="([^"]*)"[^>]*>@Usi', $value, $matches);
				if (is_link( $matches[2] )) {
					$img['url'] = readlink( $matches[2] );
				}
				else {
					$img['url'] = $matches[2];
				}

				$img_array[] = $img;
			}
			return $img_array;
		}
		else {
			return FALSE;
		}
	}


	/**
	* ccImj_noImg: Remove all images from the passed parameter, typically post content
	* @param content: string - post content
	* @return content_noImg: string
	*/
	function ccImj_noImg($content, $apply_filter=FALSE)
	{
		$content_noImg = preg_replace("/\<img(.*)\/\>/", "", $content);
		
		if ( $apply_filter ) {
			$content_noImg = apply_filters( 'the_content', $content_noImg );
		}
		
		return $content_noImg;
	}



	function ccImj_applyPlugin($img_url, $plugin) {
		switch ($plugin) {
			case 'reflect': $response = "<img src='".PLUGIN_PATH."reflect.php?img=".$img_url."'>"; break; 
			case 'greyscale': $response = "<img src='".PLUGIN_PATH."grey.php?img=".$img_url."'>"; break; 
			default: $response = $img_url; break;
		}
		return $response;
	}
	
	
	
// -------------------------------------------------------------------------- IMAGE MANIPULATION PREPARATION

	/**
	* ccImj_resizeWidthURL: Resize image to specified width, pass min_height to ensure consistent image dimensions. Return only the image resize URL, without embedding any HTML tags
	* @param img_name: string - filename path
	* @param width: integer - specify image width. default = NULL
	* @return url: string
	*/
	function ccImj_resizeWidthURL( $img_path, $width=NULL, $min_height=NULL )
	{
		$hor_dim = NULL;
		$vert_dim = NULL;
		$html_partial = NULL;
		
		$script = 'width.php';
		$tag_dims = "width='$width'";
		// $img_name = ccImj_abs2rel( $img_path );
		$img_name = $img_path;

		
		if (! is_null($width) && is_int($width)) {
			$hor_dim = "&width=$width";
		}
	
		if (! is_null($min_height) && is_int($min_height)) {
			$vert_dim = "&min_height=$min_height";
			$script = 'width_proportional.php';
		}
		
		$url .= PLUGIN_PATH."$script?img=$img_name$hor_dim$vert_dim";

		return $url;
	}


	/**
	* ccImj_resizeHeightURL: Resize image to specified height. Return only the image resize URL, without embedding any HTML tags
	* @param img_name: string - filename path
	* @param height: integer - specify image height. default = NULL
	* @return url: string
	*/
	function ccImj_resizeHeightURL( $img_path, $height=NULL )
	{
		$vrt_dim = NULL;
		$html_partial = NULL;
		// $img_name = ccImj_abs2rel( $img_path );		
		$img_name = $img_path;
	
		if (! is_null($height) && is_int($height)) {
		
			// calculate new width to provide requested height resize
			list($img_width, $img_height, $type, $attr) = getimagesize( $img_path );
			if ($img_width > $img_height) {
				$ratio = $img_width / $img_height;
			}
			else {
				$ratio = 1;
			}
			$aspect = $ratio * $height;
			
			$vrt_dim = "&height=$height&aspect=$aspect";
		}
		
		$url .= PLUGIN_PATH."height.php?img=$img_name$vrt_dim";

		return $url;
	}
	

	/**
	* ccImj_resizeWidth: Resize image to specified width. Return full HTML IMG tag for resized image
	* @param img_name: string - filename path
	* @param width: integer - specify image width. default = NULL
	* @param min-height: integer - minimum height allowable by proportion image resize. default = NULL
	* @param extraTags: string - HTML tags as plain text. default = NULL
	* @return html_partial: string
	*/
	function ccImj_resizeWidth( $img_path, $width=NULL, $extraTags=NULL )
	{
		$hor_dim = NULL;
		$vert_dim = NULL;
		$html_partial = NULL;
		
		$script = 'width.php';
		$tag_dims = "width='$width'";
		// $img_name = ccImj_abs2rel( $img_path );		
		$img_name = $img_path;

		if (! is_null($width)) {
			$hor_dim = "&width=$width";
		}
	
		$html_partial .= "<img src='".PLUGIN_PATH."$script?img=$img_name$hor_dim' $tag_dims $extraTags>";

		return $html_partial;
	}

	
	/**
	* ccImj_resizeHeight: Resize image to specified height. Return full HTML IMG tag for resized image
	* @param img_name: string - filename path
	* @param height: integer - specify image height. default = NULL
	* @param extraTags: string - HTML tags as plain text. default = NULL
	* @return html_partial: string	
	*/
	function ccImj_resizeHeight( $img_path, $height=NULL, $extraTags=NULL )
	{
		$vrt_dim = NULL;
		$html_partial = NULL;
		// $img_name = ccImj_abs2rel( $img_path );		
		$img_name = $img_path;
	
		if (! is_null($height) && is_int($height)) {
		
			// calculate new width to provide requested height resize
			list($img_width, $img_height, $type, $attr) = getimagesize( $img_path );
			if ($img_width > $img_height) {
				$ratio = $img_width / $img_height;
			}
			else {
				$ratio = 1;
			}
			$aspect = $ratio * $height;
			
			$vrt_dim = "&height=$height&aspect=$aspect";
		}
		
		$html_partial .= "<img src='".PLUGIN_PATH."height.php?img=$img_name$vrt_dim' height='$height' $extraTags>";

		return $html_partial;
	}
	
	
	/**
	* ccImj_cropLandscape: Proportionally resize image to specified width, then crop excess height as required
	* @param img_path: string - filename path
	* @param width: integer - image dimension
	* @param height: integer - image dimension
	* @param extraTags: string containing additional, unprocessed HTML tags
	* @return html_partial: string	
	*/
	function ccImj_cropLandscape( $img_path, $width, $height, $extraTags=NULL )
	{
		$hor_dim = NULL;
		$vrt_dim = NULL;
		$html_partial = NULL;
		// $img_name = ccImj_abs2rel( $img_path );		
		$img_name = $img_path;
		
		if (is_int($width)) {
			$hor_dim = "&width=$width";
		}
		
		if (is_int($height)) {
			$vrt_dim = "&min_height=$height";
		}
		
		$html_partial .= "<img src='".PLUGIN_PATH."crop_landscape.php?img=$img_name$hor_dim$vrt_dim' width='$width' height='$height' $extraTags>";
		
		return $html_partial;
	}
	
	
	/**
	* ccImj_cropPortrait: Proportionally resize image to specified height, then crop excess width as required
	* @param img_path: string - filename path
	* @param height: integer - image dimension
	* @param width: integer - image dimension
	* @param extraTags: string containing additional, unprocessed HTML tags
	* @return html_partial: string	
	*/
	function ccImj_cropPortrait( $img_path, $height, $width, $extraTags=NULL )
	{
		$hor_dim = NULL;
		$vrt_dim = NULL;
		$html_partial = NULL;
		// $img_name = ccImj_abs2rel( $img_path );		
		$img_name = $img_path;
		
		if (is_int($height)) {
			$vrt_dim = "&height=$height";
		}
		
		if (is_int($width)) {
			$hor_dim = "&width=$width";
		}
		
		$html_partial .= "<img src='".PLUGIN_PATH."crop_portrait.php?img=$img_name$hor_dim$vrt_dim' width='$width' height='$height' $extraTags>";
		
		return $html_partial;
	}	

	
	/**
	* ccImj_cropSquareCtr: Resize image to specified size, then crop a square from the centre
	* @param img_path: string - filename path
	* @param size: string - thumbnail dimension
	* @param extraTags: string containing additional, unprocessed HTML tags
	* @return html_partial: string
	*/
	function ccImj_cropSquareCtr( $img_path, $size, $extraTags=NULL )
	{
		$img_dim = NULL;
		$html_partial = NULL;
		// $img_name = ccImj_abs2rel( $img_path );		
		$img_name = $img_path;
		
		if (! is_null($size) && is_int($size)) {
			$img_dim = "&size=$size";
		}

		$html_partial .= "<img src='".PLUGIN_PATH."centre_crop.php?img=$img_name$img_dim' width='$size' height='$size' $extraTags>";

		return $html_partial;
	}
	
	

// -------------------------------------------------------------------------- GET FIRST FUNCTIONS


	/**
	* ccImj_firstLandscape: Extract the first image from the passed content, resize image to specified width, crop the excess if required and wrap it in a hyperlink if desired
	* @param content: string - post content
	* @param width: integer - width dimension
	* @param link: boolean/string - lighbox integrate or hyperlink URL
	* @param return: boolean - output conditional
	* @param extraTags: string - open to include any additional HTML attribute tags
	* @return html_partial: string. send to output or returned as variable depending on @param: return
	*/
	function ccImj_firstLandscape( $content, $width=NULL, $link=FALSE, $return=FALSE, $extraTags=NULL )
	{
		$img_path = ccImj_getFirstImg( $content );
		if (! is_null($img_path)) {
			$html_partial = ccImj_resizeWidth( $img_path, $width, $extraTags );
			$html_partial = ccImj_addLink( $html_partial, $link, $img_path );
		}
		else {
			$html_partial = NULL;
		}
		
		if ( ! $return ) { echo $html_partial; } else {	return $html_partial; }
	}

	
	/**
	* ccImj_firstPortrait: Extract the first image from the passed content, resize image to specified height, crop the excess if required and wrap it in a hyperlink if desired
	* @param content: string - post content
	* @param height: integer - height dimension
	* @param link: boolean/string - lighbox integrate or hyperlink URL
	* @param return: boolean - output conditional
	* @param extraTags: string - open to include any additional HTML attribute tags
	* @return html_partial: string. send to output or returned as variable depending on @param: return
	*/
	function ccImj_firstPortrait( $content, $height=NULL, $link=FALSE, $return=FALSE, $extraTags=NULL )
	{
		$img_path = ccImj_getFirstImg( $content );
		if (! is_null($img_path)) {
			$html_partial = ccImj_resizeHeight( $img_path, $height, $extraTags );
			$html_partial = ccImj_addLink( $html_partial, $link, $img_path );
		}
		else {
			$html_partial = NULL;
		}
		
		if ( ! $return ) { echo $html_partial; } else {	return $html_partial; }
	}

	
	/**
	* ccImj_firstThumb: Extract the first image from the passed content, then crop a square from the centre and wrap it in a hyperlink if desired
	* @param content: string - post content
	* @param size: integer - thumbnail dimension
	* @param link: boolean/string - lighbox integrate or hyperlink URL
	* @param return: boolean - output conditional
	* @param extraTags: string - open to include any additional HTML attribute tags
	* @return html_partial: string. send to output or returned as variable depending on @param: return
	*/	
	function ccImj_firstThumb( $content, $size, $link=FALSE, $return=FALSE, $extraTags=NULL )
	{
		$img_path = ccImj_getFirstImg( $content );
		if (! is_null($img_path)) {
			$html_partial = ccImj_cropSquareCtr( $img_path, $size, $extraTags );
			$html_partial = ccImj_addLink( $html_partial, $link, $img_path );
		}
		else {
			$html_partial = NULL;
		}
		
		if ( ! $return ) { echo $html_partial; } else {	return $html_partial; }
	}

	

// -------------------------------------------------------------------------- RESIZE & CROP PASSED IMAGE FUNCTIONS


	/**
	* ccImj_linkedLandscape: Resize image to specified width and height, then wrap it in a hyperlink if desired
	* @param img_path: string - filename path
	* @param width: integer - width dimension
	* @param min_height: integer - minimum height dimension
	* @param link: boolean/string - lighbox integrate or hyperlink URL
	* @param return: boolean - output conditional
	* @param extraTags: string - open to include any additional HTML attribute tags
	* @return html_partial: string. send to output or returned as variable depending on @param: return
	*/
	function ccImj_linkedLandscape( $img_path, $width, $min_height=NULL, $link=FALSE, $return=FALSE, $extraTags=NULL )
	{
		if ( $min_height != NULL ) {
			$html_partial = ccImj_cropLandscape( $img_path, $width, $min_height, $extraTags );
		}
		else {
			$html_partial = ccImj_resizeWidth( $img_path, $width, $extraTags );
		}
		$html_partial = ccImj_addLink( $html_partial, $link, $img_path );
		
		if ( ! $return ) { echo $html_partial; } else {	return $html_partial; }
	}
	
	
	/**
	* ccImj_linkedPortrait: Resize image to specified height and width, then wrap it in a hyperlink if desired
	* @param img_path: string - filename path
	* @param height: integer - height dimension
	* @param min_width: integer - minimum width dimension
	* @param link: boolean/string - lighbox integrate or hyperlink URL
	* @param return: boolean - output conditional
	* @param extraTags: string - open to include any additional HTML attribute tags
	* @return html_partial: string. send to output or returned as variable depending on @param: return
	*/
	function ccImj_linkedPortrait( $img_path, $height, $min_width=NULL, $link=FALSE, $return=FALSE, $extraTags=NULL )
	{
		if ( $min_width != NULL ) {
			$html_partial = ccImj_cropPortrait( $img_path, $height, $min_width, $extraTags );
		}
		else {
			$html_partial = ccImj_resizeHeight( $img_path, $height, $extraTags );
		}
		$html_partial = ccImj_addLink( $html_partial, $link, $img_path );
		
		if ( ! $return ) { echo $html_partial; } else {	return $html_partial; }
	}
	
	
	/**
	* ccImj_linkedThumb: Resize image to specified size, then crop a square from the centre and wrap it in a hyperlink if desired
	* @param img_path: string - filename path
	* @param size: integer - thumbnail dimension
	* @param link: boolean/string - lighbox integrate or hyperlink URL
	* @param return: boolean - output conditional
	* @param extraTags: string - open to include any additional HTML attribute tags
	* @return html_partial: string. send to output or returned as variable depending on @param: return
	*/
	function ccImj_linkedThumb( $img_path, $size, $link=FALSE, $return=FALSE, $extraTags=NULL )
	{
		$html_partial = ccImj_cropSquareCtr( $img_path, $size, $extraTags );
		$html_partial = ccImj_addLink( $html_partial, $link, $img_path );
		
		if ( ! $return ) { echo $html_partial; } else {	return $html_partial; }
	}

	

// -------------------------------------------------------------------------- FILTER ALL FUNCTIONS



	/**
	* ccImj_flexiWidth: Resize all images, to be within specified width sizes, in every post. if image is larger than max_width resize and present original via lightbox, or if smaller than min_width resize up to min_width.
	* @param content: string
	* @return content: string. modified post content
	*/
	function ccImj_flexiWidth( $content )
	{
		$filterSettings = unserialize(get_option('ccImj_filterSettings'));
		
		$min_width = $filterSettings['min_width'];
		$max_width = $filterSettings['max_width'];
		
		$images = ccImj_getAllImg( $content );
		if ($images != FALSE) {
		
			foreach($images AS $image) {
			
				// if ( ccImj_isLocal( $image['url'] )) {
				
					list($img_width, $img_height, $img_type, $img_attr) = getimagesize( $image['url'] );
					
					if ($img_width > $max_width) {
						$html_partial = ccImj_resizeWidth( $image['url'], $max_width );
					}
					elseif ($img_width < $min_width) {
						$html_partial = ccImj_resizeWidth( $image['url'], $min_width );
					}
					else {
						$html_partial = $image['tag'];
					}
					
					$lbCheck = get_option('ccImj_filterLightbox');
					if ($lbCheck && LIGHTBOX) {
						$html_partial = ccImj_addLink( $html_partial, TRUE, $image['url'] );
					}
					$content = str_replace($image['tag'], $html_partial, $content);
				// }
			}
		}
		
		return $content;
	}
	
	
	/**
	* ccImj_allWidth: Resize all images, to specified width, in every post to specified height and wrap it in a hyperlink if desired
	* @param content: string
	* @return content: string. modified post content
	*/
	function ccImj_allWidth( $content )
	{
		$filterSettings = unserialize(get_option('ccImj_filterSettings'));
		$width = $filterSettings['width'];
		
		$images = ccImj_getAllImg( $content );
		if ($images != FALSE) {
		
			foreach($images AS $image) {
			
				list($img_width, $img_height, $img_type, $img_attr) = getimagesize( $image['url'] );
				
				if ($img_width > $width) {
				
					// if ( ccImj_isLocal( $image['url'] )) {
					
						$html_partial = ccImj_resizeWidth( $image['url'], $width );

						$lbCheck = get_option('ccImj_filterLightbox');
						if ($lbCheck && LIGHTBOX) {
							$html_partial = ccImj_addLink( $html_partial, TRUE, $image['url'] );
						}
						
						$content = str_replace($image['tag'], $html_partial, $content);
					// }
				}
			}
		}
		
		return $content;
	}


	/**
	* ccImj_allCrop: Resize all images, to specified square thumbnail size, in every post to specified height and wrap it in a hyperlink if desired
	* @param content: string
	* @return content: string. modified post content
	*/
	function ccImj_allCrop( $content )
	{
		$filterSettings = unserialize(get_option('ccImj_filterSettings'));
		$size = $filterSettings['size'];
		
		$images = ccImj_getAllImg( $content );
		if ($images != FALSE) {
		
			foreach($images AS $image) {
			
				list($img_width, $img_height, $img_type, $img_attr) = getimagesize( $image['url'] );
				
				if ($img_width > $width) {
				
					// if ( ccImj_isLocal( $image['url'] )) {
					
						$html_partial = ccImj_cropSquareCtr( $image['url'], $size );

						$lbCheck = get_option('ccImj_filterLightbox');
						if ($lbCheck && LIGHTBOX) {
							$html_partial = ccImj_addLink( $html_partial, TRUE, $image['url'] );
						}
						
						$content = str_replace($image['tag'], $html_partial, $content);
					// }
				}
			}
		}
		
		return $content;
	}


	$ccImj_filterFunction = get_option('ccImj_filterFunction');
	switch ($ccImj_filterFunction) {
		case 'flexi': add_filter('the_content', 'ccImj_flexiWidth', 1, 1); break;
		case 'width': add_filter('the_content', 'ccImj_allWidth', 1, 1); break;
		case 'crop': add_filter('the_content', 'ccImj_allCrop', 1, 1); break;
	}


?>