<?php
require_once("../include/globals.php");
$COOKIE = new Cookie( 1 );
$COOKIE->set();
echo "Cookie: [";
print_r($COOKIE);
echo "]<br>";
