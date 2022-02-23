<?php
include($_SERVER['DOCUMENT_ROOT'] . "/../include/every_json.php");
global $COOKIE, $USER;

$retVal = array();

if (!array_key_exists('area', $_REQUEST)) {
    //return message area list available to user
    $retVal['areas'] = array();
    foreach (MsgArea::getListBySecurity($USER->security) as $msgarea) {

        $retVal['areas'][] = array($msgarea->name, $msgarea->desc, $msgarea->base->getMessageCount());
    }
} else if (!array_key_exists('num', $_REQUEST)) {
    //have area, return list of headers in area
    try {
        $msgarea = MsgArea::getByName($_REQUEST['area']);
        if (!$msgarea) {
            json_error("Unknown area");
        }
        if ($msgarea->readsec <= $USER->security) {
            $retVal['activeMsgs'] = $msgarea->base->getMessageCount();
            $retVal['headers'] = array();
            foreach ($msgarea->base->getHeaders() as $hdr) {
                //Verify that message is readable by this user
                if ($msgarea->sysopsec <= $USER->security || $hdr['attrib'] & MsgArea::ATTRIB_PRIVATE == 0 || $hdr['toName'] == $USER->name || $hdr['fromName'] == $USER->name) {
                    $retVal['headers'][] = $hdr;
                }
            }
        } else {
            json_error("Unauthorized area");
        }
    } catch (MsgAreaException $e) {
        json_error($e->getMessage());
    }
} else {
    try {
        $msgarea = MsgArea::getByName($_REQUEST['area']);
        if (!$msgarea) {
            json_error("Unknown area");
        }
        $msg = $msgarea->base->getMessage($_REQUEST['num']);
        if ($msgarea->sysopsec <= $USER->security || $msg['attrib'] & MsgArea::ATTRIB_PRIVATE == 0 || $msg['toName'] == $USER->name || $msg['fromName'] == $USER->name) {
            $retVal['message'] = $msg;
        } else {
            json_error("Message is not accessable");
        }
    } catch (RuntimeException $e) {
        json_error($e->getMessage());
    }
}

echo json_encode($retVal, JSON_THROW_ON_ERROR);
