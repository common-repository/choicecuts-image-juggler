=== Plugin Name ===
Contributors: ChoiceCuts
Author link: http://www.workwithchoicecuts.com
Tags: images, posts, content, resize, extraction, automatic, lightbox
Requires at least: 2.5
Tested up to: 3.0.1
Version: 0.8.3.2, 11/10/2010

ChoiceCuts Image Juggler resizes images, generates thumbnails, adds lightboxes and enhances image presentation in WordPress. This can be done either with the wp-admin enabled filters or through the Image Juggler library functions as theme developer tools.


== Description ==

ChoiceCuts Image Juggler makes all image handling a piece of cake. Stripping images, resizing to Landscape, resizing to Portrait and creating Thumbnails are all handled. A couple of lightboxes can easily be slipped into the process so as present your images in their original glory. Using the new admin screen it is now very easy to activate site wide image juggling. There are several options to choose from.

For WordPress users who are not afraid to do some template customisation, the library of functions is still available to do some great things with images, very simply.

The core aim of this plugin is to make the life of someone who regularly uses Wordpress a lot easier, in so far as to remove the need to do any image resizing while at the same time giving theme and template developers a tool to go way beyond the restricted, native image resize options of Wordpress. Just upload post images at full size, anywhere within the content of your post, then the plugin will take care of everything else!

The plugin uses the fantastic PHP Thumbnail library by http://phpthumb.gxdlabs.com.

Plug-in Homepage: www.workwithchoicecuts.com


== Installation ==

1. Upload `plugin-name.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. If you intend to use the integrated lightBox, place `<?php wp_head(); ?>` in your template header

The PHP Thumbnail Library library requires a few particulars to work and to operate efficiently. Please take the time to review the PHP Thumbnail Library documentation extracts below, or view the full documentation at http://phpthumb.gxdlabs.com/.

* Server Config. Essentials
The minimum requirements for the library to function as as follows:

    - PHP 5.2.x or greater
    - GD 2.0 or greater 


* Performance Optimisation
Working with images is a memory and CPU-intensive operation. If you plan on working with large images (something taken in high quality on a recent digital camera), you'll very likely need to increase PHP's memory limit in your php.ini file. Try at least half the available RAM on the server (within reason of course). Usually 128-256 MB works well for large images. While this may seem like a lot of memory, keep in mind that it will only be in use while resizing the images, and is freed up when manipulations complete.

* Installing a caching plug-in is an advisable step if there is any posibility of your website receiving any sharp increase in traffic. In fairness, it is a good idea to install a caching plug-in anyway. Try WP-SuperCache which does a great job.


== Frequently Asked Questions ==

If you need a question answered please email support[at]choicecuts.ie. We will really try and get back to you as quick as we can, we have put a lot of time into this plugin and love it dearly, so any feedback is greatly appreciated.


== Screenshots ==

www.choicecuts.com/music


== Changelog ==

= 0.8.3.2 =
Bug fix Thumbnail config file

= 0.8.3.1 =
Open up the image addressing to facilitate symlinks

= 0.8.3 =
Open up the image addressing to facilitate absolute paths

= 0.8.2 =
Fix a bug in the admin screen occuring from WP3.0

= 0.8.1 = 
Alter plugin directory structure to make it compatable with automatic WP download/install process

= 0.8 =
Update to PHP Thumbnail v.3
Fix numerous bugs with the admin screen

= 0.7.1 =
Update the layout of the admin screen

= 0.7 =
Added an admin screen to activate image filters. No longer have to use the toolbox within your template development.

= 0.6.1 =
Add a parameter to ccImj_noImg to offer apply_filter the_content as part of the function
Adjust 3rd parameter, Height, on ccImj_linkedLandscape. If passed as NULL image resize width with proportional height, rather than only offering set width/height
Adjust 3rd parameter, Width, on ccImj_linkedPortrait. If passed as NULL image resize height with proportional width, rather than only offering set width/height
Remove the NULL default from the Size parameter on ccImj_linkedThumb
Remove the NULL default from the Size parameter on ccImj_firstThumb
Remove the NULL default from the Size parameter on ccImj_cropSquareCtr
Add the FILTER_LIGHTBOX constant, so lightbox integration can be controlled seperately for the FILTER FUNCTIONS
Alter ccImj_linkedPortrait parameter, width, to min_width
Fixed a logic error in ccImj_flexiWidth (thanx Lough)

= 0.5.2 =
Addition of ccImj_flexiWidth function, within FILTER FUNCTION section
Add a $images returned conditional to prevent error within all FILTER FUNCTIONs
Fix bug in ccImj_getAllImg, ['url'] was not being returned properly (thanx Stewart)
Amendments made to readme.txt to improve documentation quality

= 0.5.1 =
Removal of min_height from ccImj_firstLandscape parameter list to be consistent with ccImj_firstPortrait. Function will now return a consistent width but variable, proportional height, dependant on the source image

= 0.5 =
* Initial public release, let the mayhem begin...




== Usage Exmaples ==

SHOW JUST THE FIRST IMAGE AS A LANDSCAPE BANNER
- Get the first image from the current Post and output it as a 480px x 100 px and remove all other images from Post Content.

<< php code start >>
	the_post();
	$postContent = get_the_content();
	ccImj_firstLandscape( $postContent, 480, 100 );
	echo ccImj_noImg( $postContent );
<< php code end >>


CREATE A THUMBNAIL FOR EACH OF 10 MOST RECENT POSTS IN THE BLOG CATEGORY
- Get all posts from the 'Blog' category and output them as 60px square thumbnails, as well as the post title and post excerpt.

<< php code start >>
	query_posts( "category_name=Blog&showposts=10&orderby=date&order=DESC" );
	while(have_posts()) {
		the_post();
		$postContent = get_the_content();
		ccImj_firstThumb( $postContent, 60, get_permalink() );
		the_title();
		the_excerpt();
	}	
<< php code end >>


== Full Plugin API ==

N.B. All functions are technical documented within the plugin code. Read through file cc_image_juggler.php within the plugin install folder for more information.

// -------------------------------------------------------------------------- IMAGE EXRACTION UTILITIES
	
ccImj_getFirstImg
- extract the first image from passed $content, return image file path only
<< php code start >>
	$image = ccImj_getFirstImg( $postContent ); ?>
<< php code end >>

ccImj_getAllImg
- extract all images from passed $content, returns a multi-dimensional array. The returned array contains an element for each image found. Within each element is an associative array as follows: ['tag'] - full html img tag. ['url'] - image file path only.
<< php code start >>
	$images = ccImj_getAllImg( $postContent ); ?>
<< php code end >>

ccImj_noImg
- remove all images from the passed parameter, typically post content
<< php code start >>
	$contentWithoutImages = ccImj_noImg( $postContent ); ?>
<< php code end >>


// -------------------------------------------------------------------------- IMAGE MANIPULATION PREPARATION

ccImj_resizeWidthURL
- resize image to specified width, pass min_height to ensure consistent image dimensions. Return only the image resize URL, without embedding any HTML tags
<< php code start >>
	$resizeUrl = ccImj_resizeWidthURL( $img_path, 480, 120 ); ?>
<< php code end >>

ccImj_resizeHeightURL
- resize image to specified height. Return only the image resize URL, without embedding any HTML tags
<< php code start >>
	$resizeUrl = ccImj_resizeHeightURL( $img_path, 300 ); ?>
<< php code end >>

ccImj_resizeWidth
- resize image to specified width. Return full HTML IMG tag for resized image
<< php code start >>
	$image_html = ccImj_resizeWidth( $img_path, 480, 'class="special-image-style" rel="ajax-link-code-13"' ); ?>
<< php code end >>

ccImj_resizeHeight
- resize image to specified height. Return full HTML IMG tag for resized image
<< php code start >>
	$image_html = ccImj_resizeHeight( $img_path, 300, 'class="special-image-style"' ); ?>
<< php code end >>

ccImj_cropLandscape
- proportionally resize image to specified width, then crop excess height as required
<< php code start >>
	$image_html = ccImj_cropLandscape( $img_path, 300, 240 ); ?>
<< php code end >>

ccImj_cropPortrait
- proportionally resize image to specified height, then crop excess width as required
<< php code start >>
	$image_html = ccImj_cropPortrait( $img_path, 300, 240, 'class="special-image-style" alt="Your Blog"' ); ?>
<< php code end >>

ccImj_cropSquareCtr
- resize image to specified size, then crop a square from the centre
<< php code start >>
	$image_html = ccImj_cropSquareCtr( $img_path, 60, 'class="special-image-style"' )
<< php code end >>
	

// -------------------------------------------------------------------------- 'GET FIRST' FUNCTIONS

ccImj_firstLandscape
- extract the first image from the passed content, resize image to specified width, crop the excess if required and wrap it in a hyperlink if desired
<< php code start >>
	ccImj_firstLandscape( $postContent, 480, TRUE, FALSE, 'class="special-image-style"' ); ?>
<< php code end >>

ccImj_firstPortrait
- extract the first image from the passed content, resize image to specified height, crop the excess if required and wrap it in a hyperlink if desired
<< php code start >>
	ccImj_firstPortrait( $postContent, 600, TRUE, FALSE, 'class="special-image-style"' ); ?>
<< php code end >>

ccImj_firstThumb
- extract the first image from the passed content, then crop a square from the centre and wrap it in a hyperlink if desired
<< php code start >>
	$image_html = ccImj_firstThumb( $content, 130, FALSE, TRUE, 'class="thumbnail-image-style"' ); ?>
<< php code end >>
	

// -------------------------------------------------------------------------- RESIZE & CROP PASSED IMAGE FUNCTIONS

ccImj_linkedLandscape
- resize image to specified width and height, then wrap it in a hyperlink if desired
<< php code start >>
	ccImj_linkedLandscape( $img_path, 600, 200, FALSE, FALSE, 'class="mega-banner"' ); ?>
<< php code end >>

ccImj_linkedPortrait
- resize image to specified height and width, then wrap it in a hyperlink if desired
<< php code start >>
	$your_variable = ccImj_linkedPortrait( $img_path, 400, 180, TRUE, TRUE, 'class="thumbnail-image-style"' ); ?>
<< php code end >>

ccImj_linkedThumb
- resize image to specified size, then crop a square from the centre and wrap it in a hyperlink if desired
<< php code start >>
	ccImj_linkedThumb( $img_path, 200, 'http://www.workwithchoicecuts.com', FALSE, 'class="thumbnail-image-style"' )
<< php code end >>

	
// -------------------------------------------------------------------------- FILTER ALL FUNCTIONS

ccImj_flexiWidth
- resize all images, to be within specified width sizes, in every post. if image is larger than max_width resize and present original via lightbox, or if smaller than min_width resize up to min_width.
** To use this function edit the constant 'FILTER_FLEXI_WIDTH' value to be TRUE. Find this at the top of the plugin code.

ccImj_allWidth
- resize all images, to specified width, and wrap it in a hyperlink if desired
** To use this function edit the constant 'FILTER_ALL_WIDTH' value to be TRUE. Find this at the top of the plugin code.

ccImj_allCrop
- resize all images, to specified square thumbnail size,  in every post to specified height and wrap it in a hyperlink if desired
** To use this function edit the constant 'FILTER_ALL_CROP' value to be TRUE. Find this at the top of the plugin code.


