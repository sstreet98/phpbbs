<?php
$GUEST =  TRUE;
include("../include/every_page.php");

if( $COOKIE ) {
    $COOKIE->logout();
}

header("Location: /");