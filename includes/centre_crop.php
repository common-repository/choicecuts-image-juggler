<?php


// import parameters
$img = $_GET['img'];
$size = $_GET['size'];

require_once 'ThumbLib.inc.php';
try {
    $thumb = PhpThumbFactory::create( $img );
	$thumb->adaptiveResize( $size, $size );
	$thumb->show();
}
catch (Exception $e) {
    echo "Image Juggler dropped the ball...";
}


?>