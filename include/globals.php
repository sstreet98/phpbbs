<?php
// Global includes for every page

include_once($_SERVER['DOCUMENT_ROOT'] . "/../include/DB.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/../include/User.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/../include/MsgArea.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/../include/Cookie.php");

global $TEMPLATEVARS;
$TEMPLATEVARS['titleprefix'] = "The Digital Post";

function template( $tmplname ){
   global $TEMPLATEVARS;
   include( $_SERVER['DOCUMENT_ROOT']."/../templates/".$tmplname.".php");
}

function exception_page( $e ) {
    global $TEMPLATEVARS;

    ob_end_clean();
    $TEMPLATEVARS['error_msg'] = "Unexpected Exception";
    $TEMPLATEVARS['exception'] = $e;
    template( 'error' );
    exit;
}
set_exception_handler( 'exception_page');


// Begin buffering page here
ob_start();

?>