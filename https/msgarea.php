<?php
global $TEMPLATEVARS;
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

$hdrs = $area->base->getHeaders();
$toYou = 0;
$toYouUnread = 0;
$visible = 0;
$firstMsgNum = 0;
$lastMsgNum = 0;

foreach ($hdrs as $hdr) {
    if (
        $hdr['attrib'] && MsgArea::ATTRIB_PRIVATE == 0  ||
        $area->sysopsec <= $USER->security ||
        $hdr['fromName'] == $USER->name ||
        $hdr['toName'] == $USER->name
    ) {
        ++$visible;
        if ($hdr['toName'] == $USER->name) {
            ++$toYou;
            if ($hdr['attrib'] && MsgArea::ATTRIB_READ == 0) {
                ++$toYouUnread;
            }
        }
    }
}

if (count($hdrs) > 0) {
    $firstMsgNum = $hdrs[0]['num'];
    $lastMsgNum = $hdrs[count($hdrs) - 1]['num'];
}

$TEMPLATEVARS['area'] = $area;
$TEMPLATEVARS['hdrs'] = $hdrs;
$TEMPLATEVARS['visible'] = $visible;
$TEMPLATEVARS['toYou'] = $toYou;
$TEMPLATEVARS['toYouUnread'] = $toYouUnread;
$TEMPLATEVARS['firstMsgNum'] = $firstMsgNum;
$TEMPLATEVARS['lastMsgNum'] = $lastMsgNum;

template("msgarea");
