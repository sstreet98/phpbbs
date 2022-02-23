<?php

include("include/MsgBase.php");

$base = new MsgBase( "/data/ftn/msgbase/fidonews", "UTF8" );
$msg = $base->getMessage( 70 );
	
foreach( $msg['body'] as $bl ){
	print $bl[1]."\n";
}


