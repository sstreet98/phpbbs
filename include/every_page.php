<?php
include_once($_SERVER['DOCUMENT_ROOT']."/../include/globals.php");

global $COOKIE, $USER;
// User Cookie Authentication
try
{
    $COOKIE = new Cookie();
    $COOKIE -> validate();

    $USER = User::getByID( $COOKIE->id() );
    if(!$USER)
    {
        $COOKIE->logout();
        throw new AuthException("User was not in table");
    }
    $USER->updateLastlogin();
    $USER->lastpage = $_SERVER['REQUEST_URI'];
    $USER->save();
}
catch( AuthException $e )
{
    if( $GUEST ) 
    {
        $USER = User::createGuest();
    }
    else
    {
        header("Location: /login.php?orig_uri=".$_SERVER['REQUEST_URI']);
        exit;
    }
}