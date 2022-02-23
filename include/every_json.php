<?php
// Global includes for every JSON request page

include_once($_SERVER['DOCUMENT_ROOT'] . "/../include/DB.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/../include/User.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/../include/MsgArea.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/../include/Cookie.php");

function exception_json( $e ) { 
    json_error( array ( "error" => "Unhandled Exception: ".get_class($e)." :: ".$e->getMessage(), "exceptioncode" => $e->getCode(), "file" => $e->getFile(), "line" => $e->getLine() ));
}
set_exception_handler( 'exception_json') ;

function json_error( $error ) {
    ob_clean();
    if(is_array($error)){
        echo json_encode( $error );
    } else {
        echo json_encode( array( "error" => $error ));
    }
    exit;
}

// All response from these requests will be JSON
header('Content-type: application/json');

// Begin buffering page here
ob_start();

global $COOKIE, $USER;
// USER COOKIE Authentication
try
{
    $COOKIE = new COOKIE();
    $COOKIE -> validate();

    $USER = User::getByID( $COOKIE->id() );
    if(!$USER)
    {
        $COOKIE->logout();
        throw new AuthException("USER was not in table");
    }
    $USER->updateLastlogin();
    $USER->lastpage = $_SERVER['REQUEST_URI'];
    $USER->save();
}
catch( AuthException $e )
{
    throw $e;
    if( $GUEST ) 
    {
        $USER = User::createGuest();
    }
    else
    {
        echo json_encode( array( "error" => "Action requires prior authorization") ); 
        exit;
    }
}
