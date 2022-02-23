<?php
include("../include/globals.php");

$retVal = array();

$msgs = array();

$starttime = new DateTime();
$msgbase = new Msgbase("/data/ftn/msgbase/nc/fidotest");
$endtime = new DateTime();
$retVal['msgbaseTime'] = ($starttime->diff($endtime))->f * 1000;

$starttime = new DateTime();
$headers = $msgbase->getHeaders();
$endtime = new DateTime();
$retVal['headersTime'] = ($starttime->diff($endtime))->f * 1000;

$starttime = new DateTime();
foreach( $headers as $hdr ){
    if( $msg = $msgbase->getMessage( $hdr['num'] ) ){
        $msgs[] = $msg;
    }
}
$retVal['msgs'] = $msgs;

$endtime = new DateTime();
$retVal['totalMessageTime'] = ($starttime->diff($endtime))->f * 1000;
$retVal['avgMessageTime'] = $retVal['totalMessageTime'] / count($msgs);



ob_clean();
header('Content-type: application/json');
echo json_encode( $retVal, JSON_THROW_ON_ERROR );
