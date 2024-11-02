<?php

$img = $_GET['img'];
$width = $_GET['width']; //  Primary dimension
$height = $_GET['min_height'];

list($img_width, $img_height, $img_type, $img_attr) = getimagesize( $img );
if ($img_height > $img_width) {
	$ratio = $img_height / $img_width;
}
elseif ($img_width > $img_height) {
	$ratio = $img_width / $img_height;
}
else {
	$ratio = 1;
}

$aspect = $ratio * $width;



require_once 'ThumbLib.inc.php';
try {
	$thumb = PhpThumbFactory::create( $img );
}
catch (Exception $e)
{
	// handle error here however you'd like
}

$thumb->resize($aspect, $aspect);
$thumb->cropFromCenter($width, $height);
$thumb->show();

?>