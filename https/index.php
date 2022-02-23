<?php 
$GUEST =  TRUE;
include("../include/every_page.php");

$TEMPLATEVARS = array();
$TEMPLATEVARS['titleprefix'] = "The Digital Post";
$TEMPLATEVARS['title'] = "Main Page (https)";


if( !$USER->isGuest() ) {
    $areainfo = array();
    foreach( MsgArea::getListBySecurity( $USER->security ) as $msgarea ){
        $areainfo[] = array( 'area' => $msgarea, 'count' => count($msgarea->base->getHeaders()) );
    }
    $TEMPLATEVARS['areainfo'] = $areainfo;
}

template("index");
