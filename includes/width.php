<?php

// import parameters
$img = $_GET['img'];
$size = $_GET['width'];


require_once 'ThumbLib.inc.php';
try {
	$thumb = PhpThumbFactory::create( $img );
}
catch (Exception $e)
{
	// handle error here however you'd like
}

$thumb->resize($size, $size);
$thumb->show();

?>
