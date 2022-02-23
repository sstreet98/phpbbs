<?php
include("../include/every_page.php");

if (!$_REQUEST['area']) {
    error_log("No area passed to msgarea.php, aborting");
    $TEMPLATEVARS['error_msg'] = "This request requires parameters";
    template("error");
    exit;
}

$area = MsgArea::getByName($_REQUEST['area']);
if (!$area || $area->readsec > $USER->security) {
    error_log("Invalid Area Requested [" . $_REQUEST['area'] . "]");
    $TEMPLATEVARS['error_msg'] = "This is not a valid area";
    template("error");
    exit;
}

$num = $_REQUEST['num'];
if (!$num) {
    error_log("Message number not in request", 4);
    $TEMPLATEVARS['error_msg'] = "Message number not in request";
    template("error");
    exit;
}

$msg = $area->base->getMessage($num);
if (!$msg) {
    error_log("Invalid Message number requested", 4);
    $TEMPLATEVARS['error_msg'] = "This is not a valid message number";
    template("error");
    exit;
}
if ($area->sysopsec <= $USER->security || $msg['attrib'] & MsgArea::ATTRIB_PRIVATE == 0 || $msg['fromname'] == $USER->name || $msg['toname'] == $USER->name) {
    $TEMPLATEVARS['area'] = $area;
    $TEMPLATEVARS['message'] =  $msg;
    template("message");
} else {
    error_log("User attempted to read a message that is marked private", 4);
    $TEMPLATEVARS['error_msg'] = "This is not avaliable to you";
    template("error");
    exit;
}
