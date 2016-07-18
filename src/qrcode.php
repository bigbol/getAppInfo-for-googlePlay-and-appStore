<?php
include 'phpqrcode.class.php'; 
$errorCorrectionLevel = 'Q';
$matrixPointSize = 10;
QRcode::png('http://www.cnblogs.com/txw1958/', './qrimg.png', $errorCorrectionLevel, $matrixPointSize, 2);
if(file_exists('./qrimg.png')){
	echo 'OK';
		return true;
	}
	return false;
