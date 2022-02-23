<?php
// non-pretty login page **THIS NEEDS WORK**

include_once("../include/globals.php");

@$name     = $_POST['name'];
@$password = $_POST['password'];
@$uri      = $_REQUEST['orig_uri'];
if( $uri == "" )
{
    $uri="/";
}

try
{
    if( $COOKIE ) {
        $COOKIE->logout();
    }
    if( $_SERVER['REQUEST_METHOD'] == "POST")
    {
        $USER = User::getByEmail( $name );
        if( !$USER )
        {
            $USER = User::getByName( $name );
        } 
        if( !$USER )
        {
            throw new AuthException("No such user");
        }

        if( $USER->matchPassword( $password ) == true )
        {
            $COOKIE = new Cookie( $USER->id );
            $COOKIE->set();
            header("Location: ".$uri);
            exit;    
        } else {
            $USER = false;
        }

        throw new AuthException("No Such user/password");
    }
}
catch ( AuthException $e ){
    global $TEMPLATEVARS;
    $TEMPLATEVARS['title'] = "Login";
    $TEMPLATEVARS['orig_uri'] = $uri;
    $TEMPLATEVARS['exception'] = $e;
    template("login");
    exit; 
}


global $TEMPLATEVARS;
$TEMPLATEVARS['title'] = "Login";
$TEMPLATEVARS['orig_uri'] = $uri;
template("login");
exit;