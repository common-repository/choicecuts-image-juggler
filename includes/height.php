<?php

$img = $_GET['img'];
$height = $_GET['height'];
$width = $_GET['width'];

// calculate new width to provide requested height resize
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
catch (Exception $e) {
//	echo "<img src='' alt='' height='$height' width='$width'>";
}


$thumb->resize($aspect, $height);
$thumb->show();

?>
